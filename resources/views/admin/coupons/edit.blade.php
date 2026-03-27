@extends('layouts.app')

@section('content')
<div class="container-fluid mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Coupon</h4>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Back to Coupons</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Coupon Code</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code', $coupon->code) }}">
                        @error('code')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Name (internal)</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $coupon->name) }}">
                        @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Discount Type</label>
                        <select name="discount_type" class="form-select">
                            <option value="fixed" {{ old('discount_type', $coupon->discount_type) === 'fixed' ? 'selected' : '' }}>Fixed amount</option>
                            <option value="percent" {{ old('discount_type', $coupon->discount_type) === 'percent' ? 'selected' : '' }}>Percentage</option>
                        </select>
                        @error('discount_type')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Discount Value</label>
                        <input type="number" name="discount_value" step="0.01" min="0.01" class="form-control" value="{{ old('discount_value', $coupon->discount_value) }}">
                        @error('discount_value')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Minimum Order Amount</label>
                        <input type="number" name="min_order_amount" step="0.01" min="0" class="form-control" value="{{ old('min_order_amount', $coupon->min_order_amount) }}">
                        @error('min_order_amount')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max Uses (optional)</label>
                        <input type="number" name="max_uses" min="1" class="form-control" value="{{ old('max_uses', $coupon->max_uses) }}">
                        @error('max_uses')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Starts At</label>
                        <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d\TH:i')) }}">
                        @error('starts_at')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Expires At</label>
                        <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at', optional($coupon->expires_at)->format('Y-m-d\TH:i')) }}">
                        @error('expires_at')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Target Audience</label>
                        <select name="target_audience" class="form-select">
                            <option value="all" {{ old('target_audience', $coupon->target_audience ?? 'all') === 'all' ? 'selected' : '' }}>All customers</option>
                            <option value="high_spender" {{ old('target_audience', $coupon->target_audience) === 'high_spender' ? 'selected' : '' }}>Customers who spent more than amount</option>
                            <option value="frequent_visitor" {{ old('target_audience', $coupon->target_audience) === 'frequent_visitor' ? 'selected' : '' }}>Customers who visited site many times</option>
                            <option value="new_user" {{ old('target_audience', $coupon->target_audience) === 'new_user' ? 'selected' : '' }}>New registrations</option>
                            <option value="first_order" {{ old('target_audience', $coupon->target_audience) === 'first_order' ? 'selected' : '' }}>First order only</option>
                        </select>
                        @error('target_audience')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Target Value (amount / visits)</label>
                        <input type="number" name="target_value" step="0.01" min="0" class="form-control" value="{{ old('target_value', $coupon->target_value) }}">
                        @error('target_value')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                        @error('is_active')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description (optional)</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description', $coupon->description) }}</textarea>
                        @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Update Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
