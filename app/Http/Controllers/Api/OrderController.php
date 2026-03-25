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
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct(
        private readonly DelhiveryServiceInterface $delhiveryService
    ) {
    }

    public function index(Request $request)
    {
        $query = Order::query()->with('items')->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return ApiResponse::success(
            $query->paginate($request->integer('per_page', 15)),
            'Orders fetched successfully.'
        );
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();

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
                'status'         => 'placed',
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

        return ApiResponse::success($order, 'Order created and placed successfully.', 201);
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
                $order->total_amount = $order->subtotal + $order->shipping_cost;

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
            $order->save();

            return $order->load('items');
        });

        return ApiResponse::success($order, 'Order updated successfully.');
    }

    public function destroy(Order $order)
    {
        if ($order->status === 'cancelled') {
            return ApiResponse::error('Order already cancelled.', 422);
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
