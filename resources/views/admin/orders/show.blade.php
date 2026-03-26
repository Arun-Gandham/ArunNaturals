@extends('layouts.app')

@section('content')
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Order Details</h4>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Back to Orders</a>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Order #{{ $order->order_number }}</h5>
                <small class="text-muted">Placed on {{ $order->created_at }}</small>
            </div>
            <span class="badge bg-{{ $order->status === 'placed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'secondary') }} text-uppercase">
                {{ $order->status }}
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6>Customer & Address</h6>
                    <p class="mb-1"><strong>Name:</strong> {{ $order->customer_name }}</p>
                    @if($order->customer_phone)
                        <p class="mb-1"><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                    @endif
                    @if($order->customer_email)
                        <p class="mb-1"><strong>Email:</strong> {{ $order->customer_email }}</p>
                    @endif
                    <p class="mb-1">
                        <strong>Address:</strong>
                        {{ $order->address_line1 }}
                        @if($order->address_line2)
                            , {{ $order->address_line2 }}
                        @endif
                        , {{ $order->city }}
                        @if($order->state)
                            , {{ $order->state }}
                        @endif
                        - {{ $order->pincode }}
                    </p>
                    @if($order->notes)
                        <p class="mb-1"><strong>Notes:</strong> {{ $order->notes }}</p>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <h6>Amounts & Shipping</h6>
                    <p class="mb-1"><strong>Subtotal:</strong> ₹{{ number_format($order->subtotal, 2) }}</p>
                    <p class="mb-1"><strong>Shipping Cost:</strong> ₹{{ number_format($order->shipping_cost, 2) }}</p>
                    <p class="mb-1"><strong>Total Amount:</strong> ₹{{ number_format($order->total_amount, 2) }}</p>
                    <p class="mb-1"><strong>Current Status:</strong> {{ ucfirst($order->status) }}</p>
                    <p class="mb-1"><strong>Last Updated:</strong> {{ $order->updated_at }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Order Items</h5>
        </div>
        <div class="card-body">
            @if($order->items->count())
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Line Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($order->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->sku }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                <td>₹{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mb-0 text-muted">No items found for this order.</p>
            @endif
        </div>
    </div>
</div>
@endsection

