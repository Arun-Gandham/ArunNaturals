@extends('layouts.app')

@section('content')
<div class="container-fluid mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Customers for coupon {{ $coupon->code }}</h4>
            <p class="text-muted mb-0 small">
                Showing customers who match this coupon's rules based on their orders and activity.
            </p>
        </div>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary btn-sm">
            Back to Coupons
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($customers->isEmpty())
                <p class="text-muted small mb-0 p-3">No customers match this coupon yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th class="text-end">Orders</th>
                            <th class="text-end">Total Spent (₹)</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td class="small">{{ $customer['name'] ?? 'Customer' }}</td>
                                <td class="small">{{ $customer['phone'] ?? '' }}</td>
                                <td class="small">{{ $customer['email'] ?? '' }}</td>
                                <td class="small text-end">{{ $customer['orders_count'] ?? 0 }}</td>
                                <td class="small text-end">
                                    ₹{{ number_format($customer['total_spent'] ?? 0, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

