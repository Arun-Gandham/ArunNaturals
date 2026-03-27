<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappCampaign;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WhatsappCampaignController extends Controller
{
    public function index()
    {
        $campaigns = WhatsappCampaign::latest()->paginate(10);
        $products  = Product::orderBy('name')->get(['id', 'name', 'sku']);

        return view('admin.whatsapp_campaigns.index', compact('campaigns', 'products'));
    }

    public function create()
    {
        return view('admin.whatsapp_campaigns.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'message'   => ['required', 'string'],
            'offer_url' => ['nullable', 'string', 'max:255'],
        ]);

        $slugBase = Str::slug($data['name']);
        $slug = $slugBase;
        $i = 1;
        while (WhatsappCampaign::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $i++;
        }

        WhatsappCampaign::create([
            'name'      => $data['name'],
            'slug'      => $slug,
            'message'   => $data['message'],
            'offer_url' => $data['offer_url'] ?? null,
            'status'    => 'draft',
        ]);

        return redirect()
            ->route('admin.whatsapp.campaigns.index')
            ->with('success', 'WhatsApp campaign created. You can now share it with your customers.');
    }

    /**
     * Return a JSON list of recipients (name + phone) for a campaign,
     * based on filters like "all customers" or "customers who bought product X".
     */
    public function recipients(Request $request, WhatsappCampaign $campaign)
    {
        $filter    = $request->query('filter', 'all');
        $productId = $request->query('product_id');

        $query = Order::query()
            ->select('customer_name', 'customer_phone')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '');

        if ($filter === 'product' && $productId) {
            $product = Product::find($productId);

            if ($product && $product->sku) {
                $sku = $product->sku;
                $query->whereHas('items', function ($q) use ($sku) {
                    $q->where('sku', $sku);
                });
            }
        }

        $recipients = $query
            ->groupBy('customer_phone', 'customer_name')
            ->orderBy('customer_name')
            ->limit(500)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $recipients->map(function ($row) {
                return [
                    'name'  => $row->customer_name,
                    'phone' => $row->customer_phone,
                ];
            }),
        ]);
    }
}
