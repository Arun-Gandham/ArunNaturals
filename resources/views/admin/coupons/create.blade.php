@extends('layouts.app')

@section('content')
<div class="container-fluid mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Create Coupon</h4>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Back to Coupons</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.coupons.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Coupon Code</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="Leave empty to auto-generate">
                        @error('code')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Name (internal)</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Festival Offer">
                        @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Discount Type</label>
                        <select name="discount_type" class="form-select">
                            <option value="fixed" {{ old('discount_type') === 'percent' ? '' : 'selected' }}>Fixed amount</option>
                            <option value="percent" {{ old('discount_type') === 'percent' ? 'selected' : '' }}>Percentage</option>
                        </select>
                        @error('discount_type')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Discount Value</label>
                        <input type="number" name="discount_value" step="0.01" min="0.01" class="form-control" value="{{ old('discount_value') }}">
                        @error('discount_value')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Minimum Order Amount</label>
                        <input type="number" name="min_order_amount" step="0.01" min="0" class="form-control" value="{{ old('min_order_amount') }}">
                        @error('min_order_amount')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max Uses (optional)</label>
                        <input type="number" name="max_uses" min="1" class="form-control" value="{{ old('max_uses') }}">
                        @error('max_uses')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Starts At</label>
                        <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
                        @error('starts_at')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Expires At</label>
                        <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                        @error('expires_at')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Target Audience</label>
                        <select name="target_audience" class="form-select">
                            <option value="all" {{ old('target_audience', 'all') === 'all' ? 'selected' : '' }}>All customers</option>
                            <option value="high_spender" {{ old('target_audience') === 'high_spender' ? 'selected' : '' }}>Customers who spent more than amount</option>
                            <option value="frequent_visitor" {{ old('target_audience') === 'frequent_visitor' ? 'selected' : '' }}>Customers who visited site many times</option>
                            <option value="new_user" {{ old('target_audience') === 'new_user' ? 'selected' : '' }}>New registrations</option>
                            <option value="first_order" {{ old('target_audience') === 'first_order' ? 'selected' : '' }}>First order only</option>
                        </select>
                        @error('target_audience')<small class="text-danger">{{ $message }}</small>@enderror
                        <small class="text-muted d-block mt-1 small">Use this to create special coupons like “First order flat 10% off” or “₹100 off for customers who spent more than ₹1000”.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Target Value (amount / visits)</label>
                        <input type="number" name="target_value" step="0.01" min="0" class="form-control" value="{{ old('target_value') }}" placeholder="For example 1000 for ₹1000 spend, or 5 for 5+ visits">
                        @error('target_value')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description (optional)</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Short description of this offer...">{{ old('description') }}</textarea>
                        @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
