<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,user',
        ]);
        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);
        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function updateUserRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,user',
        ]);
        $user = \App\Models\User::findOrFail($id);
        $user->role = $request->role;
        $user->save();
        return redirect()->route('admin.users.index')->with('success', 'User role updated.');
    }

    public function insights()
    {
        $insights = \App\Models\PageVisit::select('url')
            ->selectRaw('count(*) as visits')
            ->groupBy('url')
            ->orderByDesc('visits')
            ->limit(10)
            ->get();
        return view('admin.insights', compact('insights'));
    }

    public function users()
    {
        $users = \App\Models\User::all();
        return view('admin.users.index', compact('users'));
    }

    public function dashboard()
    {
        $totalOrders = Order::count();

        $totalRevenue = Order::where('status', '!=', 'cancelled')
            ->sum('total_amount');

        $statusBreakdown = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $topProducts = OrderItem::select(
                'product_name',
                DB::raw('COUNT(DISTINCT order_id) as orders_count'),
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(line_total) as revenue')
            )
            ->groupBy('product_name')
            ->orderByDesc('orders_count')
            ->limit(5)
            ->get();

        $shipmentsTotal = Order::whereNotNull('delhivery_waybill')->count();
        $shipmentsPlaced = Order::whereNotNull('delhivery_waybill')
            ->where('status', 'placed')
            ->count();
        $shipmentSuccessRate = $shipmentsTotal > 0
            ? round(($shipmentsPlaced / $shipmentsTotal) * 100, 1)
            : null;

        $dailyRevenue = Order::where('status', '!=', 'cancelled')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('date')
            ->limit(7)
            ->get();

        $recentOrders = Order::latest()
            ->limit(5)
            ->get(['order_number', 'customer_name', 'status', 'total_amount', 'created_at']);

        return view('admin.dashboard', compact(
            'totalOrders',
            'totalRevenue',
            'statusBreakdown',
            'topProducts',
            'shipmentsTotal',
            'shipmentsPlaced',
            'shipmentSuccessRate',
            'dailyRevenue',
            'recentOrders'
        ));
    }

    public function settings()
    {
        $settings = SiteSetting::first();

        return view('admin.settings', compact('settings'));
    }

    public function settingsUpdate(Request $request)
    {
        $data = $request->validate([
            'site_name'        => ['required', 'string', 'max:255'],
            'tagline'          => ['nullable', 'string', 'max:255'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords'    => ['nullable', 'string'],
            'favicon_url'      => ['nullable', 'string', 'max:255'],
            'logo_url'         => ['nullable', 'string', 'max:255'],
            'facebook_url'     => ['nullable', 'string', 'max:255'],
            'instagram_url'    => ['nullable', 'string', 'max:255'],
            'twitter_url'      => ['nullable', 'string', 'max:255'],
        ]);

        $settings = SiteSetting::first();

        if (! $settings) {
            $settings = SiteSetting::create($data);
        } else {
            $settings->update($data);
        }

        cache()->forget('site_settings');

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Settings updated successfully.');
    }

    public function deliveryShipments()
    {
        $shipments = Order::whereNotNull('delhivery_waybill')
            ->latest()
            ->limit(25)
            ->get();

        return view('admin.delivery.shipments', compact('shipments'));
    }

    public function deliveryPickups()
    {
        return view('admin.delivery.pickups');
    }

    public function deliveryServiceability()
    {
        return view('admin.delivery.serviceability');
    }

    public function orders()
    {
        $orders = Order::latest()->get();
        return view('admin.orders.index', compact('orders'));
    }

    public function ordersCreate()
    {
        $products = \App\Models\Product::select('id', 'name', 'sku', 'price')
            ->orderBy('name')
            ->get();

        return view('admin.orders.create', compact('products'));
    }

    public function orderShow(Order $order)
    {
        $order->load('items');

        return view('admin.orders.show', compact('order'));
    }

    public function orderLabel(Order $order)
    {
        if (! $order->delhivery_waybill) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->with('error', 'Delhivery waybill is not available for this order.');
        }

        $baseUrl = rtrim((string) config('services.delhivery.base_url'), '/');
        $token   = (string) config('services.delhivery.token');

        if (! $baseUrl || ! $token) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->with('error', 'Delhivery configuration is missing. Please check env settings.');
        }

        try {
            // Build label URL exactly like the working curl:
            // GET {base}/api/p/packing_slip?wbns=...&pdf=true&pdf_size=4R
            $labelBase = rtrim($baseUrl, '/');
            if (Str::endsWith($labelBase, '/api')) {
                $labelBase = substr($labelBase, 0, -4);
            }
            $labelUrl = $labelBase . '/api/p/packing_slip';

            $response = Http::withHeaders([
                    'Authorization' => 'Token ' . $token,
                    'Content-Type'  => 'application/json',
                ])
                ->timeout(60)
                ->get($labelUrl, [
                    'wbns'      => $order->delhivery_waybill,
                    'pdf'       => 'true',
                    'pdf_size'  => '4R',
                ]);

            if ($response->failed()) {
                $status = $response->status();

                // Try to extract a meaningful message from Delhivery response
                $json   = null;
                $reason = null;
                try {
                    $json = $response->json();
                } catch (\Throwable $ignore) {
                    $json = null;
                }

                if (is_array($json)) {
                    $reason = $json['rmk']
                        ?? $json['Error']
                        ?? $json['error']
                        ?? $json['message']
                        ?? null;
                }

                if (! $reason) {
                    $body = (string) $response->body();
                    $reason = $body ? Str::limit(trim(strip_tags($body)), 150) : null;
                }

                Log::warning('Failed to download Delhivery shipping label', [
                    'order_id' => $order->id,
                    'status'   => $status,
                    'reason'   => $reason,
                ]);

                $message = 'Could not download shipping label from Delhivery.';
                if ($reason) {
                    $message .= ' Reason: ' . $reason . ' (HTTP ' . $status . ')';
                } else {
                    $message .= ' HTTP status: ' . $status . '.';
                }

                return redirect()
                    ->route('admin.orders.show', $order)
                    ->with('error', $message);
            }

            // Delhivery often returns JSON with a pdf_download_link instead of raw PDF.
            $json = null;
            try {
                $json = $response->json();
            } catch (\Throwable $ignore) {
                $json = null;
            }

            $downloadUrl = null;
            if (is_array($json)) {
                $downloadUrl = $json['packages'][0]['pdf_download_link']
                    ?? $json['pdf_download_link']
                    ?? null;
            }

            if ($downloadUrl) {
                // Fetch the actual PDF from the provided download URL
                try {
                    $pdfResponse = Http::timeout(60)->get($downloadUrl);

                    if ($pdfResponse->failed()) {
                        Log::warning('Failed to fetch Delhivery PDF from download URL', [
                            'order_id' => $order->id,
                            'status'   => $pdfResponse->status(),
                        ]);

                        return redirect()
                            ->route('admin.orders.show', $order)
                            ->with('error', 'Delhivery returned a label link, but the PDF could not be fetched.');
                    }

                    $pdfBody        = (string) $pdfResponse->body();
                    $pdfContentType = (string) $pdfResponse->header('Content-Type', '');

                    $isPdfType = Str::contains(strtolower($pdfContentType), 'pdf');
                    $isPdfBody = Str::startsWith($pdfBody, '%PDF');

                    // Consider it a valid PDF if either content type OR body looks like PDF.
                    if (! ($isPdfType || $isPdfBody)) {
                        $snippet = $pdfBody ? Str::limit(trim(strip_tags($pdfBody)), 150) : null;

                        Log::warning('Downloaded Delhivery label link did not return a valid PDF', [
                            'order_id'     => $order->id,
                            'content_type' => $pdfContentType,
                            'snippet'      => $snippet,
                        ]);

                        $message = 'Delhivery label download link did not return a valid PDF.';
                        if ($snippet) {
                            $message .= ' Reason: ' . $snippet;
                        }

                        return redirect()
                            ->route('admin.orders.show', $order)
                            ->with('error', $message);
                    }

                    $filename = $order->order_number . '-label.pdf';

                    return response($pdfBody, 200, [
                        'Content-Type'        => $pdfContentType ?: 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Error while fetching Delhivery label from download URL', [
                        'order_id' => $order->id,
                        'message'  => $e->getMessage(),
                    ]);

                    return redirect()
                        ->route('admin.orders.show', $order)
                        ->with('error', 'Unexpected error while fetching label PDF from Delhivery link: ' . $e->getMessage());
                }
            }

            // Fallback: try to treat the original response as a direct PDF
            $body        = (string) $response->body();
            $contentType = (string) $response->header('Content-Type', '');

            $isPdfType = Str::contains(strtolower($contentType), 'pdf');
            $isPdfBody = Str::startsWith($body, '%PDF');

            // Same relaxed check for direct PDF responses.
            if (! ($isPdfType || $isPdfBody)) {
                $snippet = $body ? Str::limit(trim(strip_tags($body)), 150) : null;

                Log::warning('Delhivery label response is not a valid PDF', [
                    'order_id'     => $order->id,
                    'content_type' => $contentType,
                    'snippet'      => $snippet,
                ]);

                $message = 'Delhivery did not return a valid PDF label.';
                if ($snippet) {
                    $message .= ' Reason: ' . $snippet;
                }

                return redirect()
                    ->route('admin.orders.show', $order)
                    ->with('error', $message);
            }

            $filename = $order->order_number . '-label.pdf';

            return response($body, 200, [
                'Content-Type'        => $contentType ?: 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error while downloading Delhivery shipping label', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.orders.show', $order)
                ->with('error', 'Unexpected error while downloading shipping label: ' . $e->getMessage());
        }
    }

    public function bulkLabels(Request $request)
    {
        $orderIds = $request->input('order_ids', []);
        if (! is_array($orderIds) || empty($orderIds)) {
            return redirect()
                ->back()
                ->with('error', 'Please select at least one order to download labels.');
        }

        $orders = Order::whereIn('id', $orderIds)->get();
        $waybills = $orders->pluck('delhivery_waybill')->filter()->unique()->values()->all();

        if (empty($waybills)) {
            return redirect()
                ->back()
                ->with('error', 'Selected orders do not have Delhivery waybills yet.');
        }

        $baseUrl = rtrim((string) config('services.delhivery.base_url'), '/');
        $token   = (string) config('services.delhivery.token');

        if (! $baseUrl || ! $token) {
            return redirect()
                ->back()
                ->with('error', 'Delhivery configuration is missing. Please check env settings.');
        }

        try {
            $labelBase = rtrim($baseUrl, '/');
            if (Str::endsWith($labelBase, '/api')) {
                $labelBase = substr($labelBase, 0, -4);
            }
            $labelUrl = $labelBase . '/api/p/packing_slip';

            $response = Http::withHeaders([
                    'Authorization' => 'Token ' . $token,
                    'Content-Type'  => 'application/json',
                ])
                ->timeout(60)
                ->get($labelUrl, [
                    'wbns'     => implode(',', $waybills),
                    'pdf'      => 'true',
                    'pdf_size' => '4R',
                ]);

            if ($response->failed()) {
                $status = $response->status();
                $json   = null;
                $reason = null;
                try {
                    $json = $response->json();
                } catch (\Throwable $ignore) {
                    $json = null;
                }

                if (is_array($json)) {
                    $reason = $json['rmk']
                        ?? $json['Error']
                        ?? $json['error']
                        ?? $json['message']
                        ?? null;
                }

                if (! $reason) {
                    $body = (string) $response->body();
                    $reason = $body ? Str::limit(trim(strip_tags($body)), 150) : null;
                }

                Log::warning('Failed to download bulk Delhivery shipping labels', [
                    'order_ids' => $orderIds,
                    'status'    => $status,
                    'reason'    => $reason,
                ]);

                $message = 'Could not download bulk shipping labels from Delhivery.';
                if ($reason) {
                    $message .= ' Reason: ' . $reason . ' (HTTP ' . $status . ')';
                } else {
                    $message .= ' HTTP status: ' . $status . '.';
                }

                return redirect()
                    ->back()
                    ->with('error', $message);
            }

            $json = null;
            try {
                $json = $response->json();
            } catch (\Throwable $ignore) {
                $json = null;
            }

            $downloadUrl = null;
            if (is_array($json)) {
                $downloadUrl = $json['packages'][0]['pdf_download_link']
                    ?? $json['pdf_download_link']
                    ?? null;
            }

            if ($downloadUrl) {
                $pdfResponse = Http::timeout(60)->get($downloadUrl);

                if ($pdfResponse->failed()) {
                    Log::warning('Failed to fetch Delhivery bulk PDF from download URL', [
                        'order_ids' => $orderIds,
                        'status'    => $pdfResponse->status(),
                    ]);

                    return redirect()
                        ->back()
                        ->with('error', 'Delhivery returned a bulk label link, but the PDF could not be fetched.');
                }

                $pdfBody        = (string) $pdfResponse->body();
                $pdfContentType = (string) $pdfResponse->header('Content-Type', '');

                $isPdfType = Str::contains(strtolower($pdfContentType), 'pdf');
                $isPdfBody = Str::startsWith($pdfBody, '%PDF');

                if (! ($isPdfType || $isPdfBody)) {
                    $snippet = $pdfBody ? Str::limit(trim(strip_tags($pdfBody)), 150) : null;

                    Log::warning('Downloaded Delhivery bulk label link did not return a valid PDF', [
                        'order_ids'    => $orderIds,
                        'content_type' => $pdfContentType,
                        'snippet'      => $snippet,
                    ]);

                    $message = 'Delhivery bulk label download link did not return a valid PDF.';
                    if ($snippet) {
                        $message .= ' Reason: ' . $snippet;
                    }

                    return redirect()
                        ->back()
                        ->with('error', $message);
                }

                $filename = 'bulk-labels-' . now()->format('Ymd-His') . '.pdf';

                return response($pdfBody, 200, [
                    'Content-Type'        => $pdfContentType ?: 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);
            }

            // Fallback: try direct PDF
            $body        = (string) $response->body();
            $contentType = (string) $response->header('Content-Type', '');

            $isPdfType = Str::contains(strtolower($contentType), 'pdf');
            $isPdfBody = Str::startsWith($body, '%PDF');

            if (! ($isPdfType || $isPdfBody)) {
                $snippet = $body ? Str::limit(trim(strip_tags($body)), 150) : null;

                Log::warning('Delhivery bulk label response is not a valid PDF', [
                    'order_ids'   => $orderIds,
                    'content_type'=> $contentType,
                    'snippet'     => $snippet,
                ]);

                $message = 'Delhivery bulk label API did not return a valid PDF.';
                if ($snippet) {
                    $message .= ' Reason: ' . $snippet;
                }

                return redirect()
                    ->back()
                    ->with('error', $message);
            }

            $filename = 'bulk-labels-' . now()->format('Ymd-His') . '.pdf';

            return response($body, 200, [
                'Content-Type'        => $contentType ?: 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error while downloading bulk Delhivery shipping labels', [
                'order_ids' => $orderIds,
                'message'   => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Unexpected error while downloading bulk shipping labels: ' . $e->getMessage());
        }
    }
}
