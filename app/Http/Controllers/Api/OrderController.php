<?php

namespace App\Http\Controllers\Api;

use App\Contracts\DelhiveryServiceInterface;
use App\Exceptions\DelhiveryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\ApiResponse;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct(
        private readonly DelhiveryServiceInterface $delhiveryService
    ) {
    }

    public function index(Request $request)
    {
        $query = Order::query()->with('items');

        // Filter by status (e.g. placed, draft, cancelled)
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Free-text search: order no, waybill, customer name/phone/email
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('delhivery_waybill', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        // Date range filter based on created_at (manifested date equivalent)
        if ($from = $request->query('from_date')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('to_date')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Default sort: newest first (similar to Delhivery dashboard)
        $query->orderByDesc('created_at');

        $orders = $query->paginate($request->integer('per_page', 15));

        return ApiResponse::success($orders, 'Orders fetched successfully.');
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $createInDelhivery = array_key_exists('create_in_delhivery', $validated)
            ? (bool) $validated['create_in_delhivery']
            : true;

        try {
            $availability = $this->delhiveryService->checkPincode($validated['pincode']);
        } catch (DelhiveryException $e) {
            return ApiResponse::error(
                $e->getMessage(),
                $e->getCode() > 0 ? $e->getCode() : 422
            );
        }

        $deliveryCodes = $availability['data']['delivery_codes'] ?? [];

        if (empty($deliveryCodes)) {
            return ApiResponse::error(
                'Shipping not available for this pincode.',
                422,
                $availability['data']
            );
        }

        $order = DB::transaction(function () use ($validated) {
            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $subtotal += $item['unit_price'] * $item['quantity'];
            }

            $shippingCost = isset($validated['shipping_cost'])
                ? (float) $validated['shipping_cost']
                : 0.0;
            $total = $subtotal + $shippingCost;

            $order = Order::create([
                'order_number'   => Str::upper('ORD-' . Str::random(8)),
                'customer_name'  => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'address_line1'  => $validated['address_line1'],
                'address_line2'  => $validated['address_line2'] ?? null,
                'city'           => $validated['city'],
                'state'          => $validated['state'] ?? null,
                'pincode'        => $validated['pincode'],
                'estimated_delivery_date' => $validated['estimated_delivery_date'] ?? null,
                // Start as draft; mark as placed only after Delhivery shipment is created.
                'status'         => 'draft',
                'subtotal'       => $subtotal,
                'shipping_cost'  => $shippingCost,
                'total_amount'   => $total,
                'notes'          => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_name' => $item['product_name'],
                    'sku'          => $item['sku'] ?? null,
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                    'line_total'   => $item['unit_price'] * $item['quantity'],
                ]);
            }

            return $order->load('items');
        });

        $shipmentCreated = false;

        // Create shipment in Delhivery and store waybill, if requested
        if ($createInDelhivery) {
            try {
                // Build basic product summary for Delhivery (one shipment, multiple products)
                $productsDescParts = [];
                $totalQuantity     = 0;

                foreach ($validated['items'] as $item) {
                    $qty = (int) $item['quantity'];
                    $totalQuantity += $qty;
                    $productsDescParts[] = $item['product_name'] . ' x ' . $qty;
                }

                $productsDesc = implode(', ', $productsDescParts);

                $shipmentPayload = [
                    'shipments' => [[
                        'name'            => $order->customer_name,
                        'add'             => trim($order->address_line1 . ' ' . (string) $order->address_line2),
                        'pin'             => $order->pincode,
                        'city'            => $order->city,
                        'state'           => $order->state,
                        'country'         => 'India',
                        'phone'           => $order->customer_phone,
                        'order'           => $order->order_number,
                        'payment_mode'    => 'Prepaid', // matches default in UI; can be extended per-item later
                        'cod_amount'      => null,
                        'total_amount'    => $order->total_amount,
                        'waybill'         => $order->delhivery_waybill ?? '',
                        'products_desc'   => $productsDesc,
                        'quantity'        => $totalQuantity ?: 1,
                        // Use charged weight (grams) from request if provided
                        'weight'          => $validated['cgm'] ?? null,
                        'shipment_length' => $validated['box_length'] ?? null,
                        'shipment_width'  => $validated['box_width'] ?? null,
                        'shipment_height' => $validated['box_height'] ?? null,
                        'shipping_mode'   => 'Surface',
                    ]],
                    'pickup_location' => [
                        'name' => config('services.delhivery.pickup_location', 'warehouse_name'),
                    ],
                ];

                $createResult = $this->delhiveryService->createShipment($shipmentPayload);
                $data         = $createResult['data'] ?? [];

                Log::info('Delhivery shipment create response', [
                    'order_id' => $order->id,
                    'payload'  => $shipmentPayload,
                    'response' => $data,
                ]);

                // Try to extract waybill from typical response shapes
                $waybill = $data['packages'][0]['waybill'] ?? $data['waybill'] ?? null;

                if ($waybill) {
                    $order->delhivery_waybill = (string) $waybill;
                    $order->status            = 'placed';
                    $order->save();
                    $shipmentCreated = true;
                }
            } catch (DelhiveryException $e) {
                Log::error('Delhivery shipment creation failed', [
                    'order_id' => $order->id,
                    'message'  => $e->getMessage(),
                    'context'  => method_exists($e, 'getContext') ? $e->getContext() : null,
                ]);
            }
        }

        if ($createInDelhivery && ! $shipmentCreated) {
            // Make it clear this order is not yet scheduled with the delivery partner.
            $notes = trim((string) $order->notes);
            $suffix = 'Not scheduled with delivery partner (Delhivery shipment not created).';
            $order->notes = $notes ? ($notes . ' ' . $suffix) : $suffix;
            $order->save();
        }

        if (! $createInDelhivery) {
            $message = 'Order created without creating Delhivery shipment.';
        } elseif ($shipmentCreated) {
            $message = 'Order created and ready for shipping.';
        } else {
            $message = 'Order created but not yet scheduled with delivery partner.';
        }

        return ApiResponse::success($order->fresh('items'), $message, 201);
    }

    public function show(Order $order)
    {
        $order->load('items');

        return ApiResponse::success($order, 'Order details fetched successfully.');
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        if ($order->status === 'cancelled') {
            return ApiResponse::error('Cancelled orders cannot be edited.', 422);
        }

        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated, $order) {
            if (isset($validated['items'])) {
                $order->items()->delete();

                $subtotal = 0;
                foreach ($validated['items'] as $item) {
                    $subtotal += $item['unit_price'] * $item['quantity'];
                }

                $order->subtotal = $subtotal;

                foreach ($validated['items'] as $item) {
                    $order->items()->create([
                        'product_name' => $item['product_name'],
                        'sku'          => $item['sku'] ?? null,
                        'quantity'     => $item['quantity'],
                        'unit_price'   => $item['unit_price'],
                        'line_total'   => $item['unit_price'] * $item['quantity'],
                    ]);
                }
            }

            $order->fill(collect($validated)->except('items')->toArray());
            $order->total_amount = $order->subtotal + $order->shipping_cost;
            $order->save();

            return $order->load('items');
        });

        // If shipping attributes changed and we have a waybill, sync to Delhivery
        if ($order->delhivery_waybill && isset($validated['shipping_cost'])) {
            try {
                $payload = [
                    'waybill' => $order->delhivery_waybill,
                    // Map basic payment / amount fields; can be extended
                    'pt'      => 'Pre-paid',
                    'cod'     => 0,
                    'gm'      => null,
                ];
                $this->delhiveryService->updateShipment($payload);
            } catch (DelhiveryException $e) {
                // Ignore Delhivery error for now; order DB is already updated
            }
        }

        return ApiResponse::success($order, 'Order updated successfully.');
    }

    public function destroy(Order $order)
    {
        if ($order->status === 'cancelled') {
            return ApiResponse::error('Order already cancelled.', 422);
        }

        // Cancel in Delhivery first, if waybill exists
        if ($order->delhivery_waybill) {
            try {
                $this->delhiveryService->cancelShipment($order->delhivery_waybill);
            } catch (DelhiveryException $e) {
                // If Delhivery cancellation fails, we can choose to still cancel locally
                // or return an error. For now, cancel locally but this can be adjusted.
            }
        }

        $order->status = 'cancelled';
        $order->save();

        return ApiResponse::success($order, 'Order cancelled successfully.');
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'pincode' => ['required', 'digits:6'],
        ]);

        try {
            $result = $this->delhiveryService->checkPincode($request->string('pincode')->toString());

            $deliveryCodes = $result['data']['delivery_codes'] ?? [];

            if (empty($deliveryCodes)) {
                return ApiResponse::error(
                    'Shipping not available for this pincode.',
                    422,
                    $result['data']
                );
            }

            return ApiResponse::success(
                $result['data'],
                'Service available for this pincode.',
                200
            );
        } catch (DelhiveryException $e) {
            return ApiResponse::error(
                $e->getMessage(),
                $e->getCode() > 0 ? $e->getCode() : 500
            );
        }
    }
}
