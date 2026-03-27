<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponExclusion;
use App\Models\Order;
use App\Models\PageVisit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(15);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        if (empty($data['code'])) {
            $data['code'] = strtoupper(Str::random(8));
        } else {
            $data['code'] = strtoupper($data['code']);
        }

        Coupon::create($data);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully.');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $this->validatedData($request, $coupon->id);
        $data['code'] = strtoupper($data['code'] ?? $coupon->code);

        $coupon->update($data);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully.');
    }

    public function customers(Request $request, Coupon $coupon)
    {
        // Reuse the recipients logic to build the same customer list
        $response = $this->recipients($request, $coupon);
        $payload = $response->getData(true);
        $customers = collect($payload['data'] ?? []);

        return view('admin.coupons.customers', compact('coupon', 'customers'));
    }

    public function excludeRecipient(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'max:255'],
            'name'  => ['nullable', 'string', 'max:255'],
        ]);

        CouponExclusion::firstOrCreate(
            [
                'coupon_id'      => $coupon->id,
                'customer_phone' => $data['phone'],
            ],
            [
                'customer_email' => $data['email'] ?? null,
                'customer_name'  => $data['name'] ?? null,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function saveRecipients(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'all_phones'     => ['required', 'array'],
            'all_phones.*'   => ['string', 'max:30'],
            'allowed_phones' => ['nullable', 'array'],
            'allowed_phones.*' => ['string', 'max:30'],
        ]);

        $all = collect($data['all_phones'])->filter()->unique()->values();
        $allowed = collect($data['allowed_phones'] ?? [])->filter()->unique()->values();

        $toExclude = $all->diff($allowed)->values();
        $toAllow = $all->intersect($allowed)->values();

        if ($toAllow->isNotEmpty()) {
            CouponExclusion::where('coupon_id', $coupon->id)
                ->whereIn('customer_phone', $toAllow)
                ->delete();
        }

        foreach ($toExclude as $phone) {
            CouponExclusion::firstOrCreate([
                'coupon_id'      => $coupon->id,
                'customer_phone' => $phone,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function recipients(Request $request, Coupon $coupon)
    {
        $mode = $request->query('mode', 'coupon'); // coupon or all

        $query = Order::query()
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '');

        // Optional basic filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->query('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->query('to_date'));
        }
        if ($request->boolean('only_delivered')) {
            $query->where('status', 'delivered');
        }

        // Load exclusions once; we keep excluded customers visible in the list,
        // but mark them as not allowed instead of removing them.
        $excludedPhones = CouponExclusion::where('coupon_id', $coupon->id)
            ->pluck('customer_phone')
            ->filter()
            ->all();

        $audience = $mode === 'all' ? 'all' : ($coupon->target_audience ?? 'all');

        // Group strictly by mobile number so each phone appears only once.
        $baseQuery = $query->selectRaw('
                MIN(customer_name)   as customer_name,
                customer_phone,
                MIN(customer_email)  as customer_email,
                COUNT(*)             as orders_count,
                SUM(total_amount)    as total_spent,
                MIN(created_at)      as first_order_at
            ')
            ->groupBy('customer_phone');

        if ($audience === 'high_spender') {
            $minSpend = $coupon->target_value ?? 1000;
            $baseQuery->havingRaw('SUM(total_amount) >= ?', [$minSpend]);
        } elseif ($audience === 'first_order') {
            $baseQuery->having('orders_count', '=', 1);
        } elseif ($audience === 'new_user') {
            $days = (int)($coupon->target_value ?? 30);
            $cutoff = now()->subDays($days);
            $baseQuery->having('first_order_at', '>=', $cutoff);
        }

        // Apply extra numeric filters from UI
        if ($request->filled('min_orders')) {
            $baseQuery->having('orders_count', '>=', (int) $request->query('min_orders'));
        }
        if ($request->filled('min_spent')) {
            $baseQuery->havingRaw('SUM(total_amount) >= ?', [(float) $request->query('min_spent')]);
        }

        $rows = $baseQuery->get();

        if ($audience === 'frequent_visitor') {
            $minVisits = (int)($coupon->target_value ?? 5);
            $emails = $rows->pluck('customer_email')->filter()->unique()->all();
            if ($emails) {
                $users = User::whereIn('email', $emails)->get(['id', 'email']);
                $usersByEmail = $users->keyBy('email');
                $visitCounts = [];

                if ($users->isNotEmpty()) {
                    $visitCounts = PageVisit::selectRaw('user_id, COUNT(*) as visits')
                        ->whereIn('user_id', $users->pluck('id'))
                        ->groupBy('user_id')
                        ->pluck('visits', 'user_id')
                        ->toArray();
                }

                $rows = $rows->filter(function ($row) use ($usersByEmail, $visitCounts, $minVisits) {
                    $user = $usersByEmail[$row->customer_email] ?? null;
                    if (!$user) {
                        return false;
                    }
                    $visits = $visitCounts[$user->id] ?? 0;
                    return $visits >= $minVisits;
                })->values();
            } else {
                $rows = collect();
            }
        }

        return response()->json([
            'success' => true,
            'data' => $rows->map(function ($row) use ($excludedPhones) {
                $phone = $row->customer_phone;
                return [
                    'name'           => $row->customer_name,
                    'phone'          => $phone,
                    'email'          => $row->customer_email,
                    'orders_count'   => (int) $row->orders_count,
                    'total_spent'    => (float) $row->total_spent,
                    'first_order_at' => optional($row->first_order_at)->toDateTimeString(),
                    'allowed'        => !in_array($phone, $excludedPhones, true),
                ];
            }),
        ]);
    }

    protected function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = 'unique:coupons,code';
        if ($ignoreId) {
            $uniqueRule .= ',' . $ignoreId;
        }

        $data = $request->validate([
            'code'             => ['nullable', 'string', 'max:30', $uniqueRule],
            'name'             => ['nullable', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'discount_type'    => ['required', 'in:fixed,percent'],
            'discount_value'   => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'starts_at'        => ['nullable', 'date'],
            'expires_at'       => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active'        => ['nullable', 'boolean'],
            'target_audience'  => ['nullable', 'string', 'in:all,high_spender,frequent_visitor,new_user,first_order'],
            'target_value'     => ['nullable', 'numeric', 'min:0'],
        ]);

        if (!array_key_exists('target_audience', $data) || empty($data['target_audience'])) {
            $data['target_audience'] = 'all';
        }

        // Normalise checkbox
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
