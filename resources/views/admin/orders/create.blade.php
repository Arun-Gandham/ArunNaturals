@extends('layouts.app')

@section('content')
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Create Order</h4>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Back to Orders</a>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Customer & Address</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="pincode" class="form-label">Pincode</label>
                        <div class="input-group">
                            <input type="text" id="pincode" class="form-control" maxlength="6" placeholder="Enter 6 digit pincode">
                            <button class="btn btn-outline-secondary" type="button" id="checkAvailabilityBtn">Check Availability</button>
                        </div>
                        <div class="form-text" id="availabilityResult"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Customer Name</label>
                        <input type="text" id="customer_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" id="customer_phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="customer_email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address Line 1</label>
                        <input type="text" id="address_line1" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" id="address_line2" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City</label>
                        <input type="text" id="city" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">State</label>
                        <input type="text" id="state" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea id="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Items & Shipping</h5>
                </div>
                <div class="card-body">
                    <div id="itemsContainer" class="mb-3"></div>
                    <button class="btn btn-sm btn-outline-primary mb-3" type="button" id="addItemBtn">Add Item</button>

                    <hr>
                    <h6>Shipping Details</h6>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Charged Weight (grams)</label>
                            <input type="number" id="cgm" class="form-control" min="1" placeholder="e.g. 500">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Payment Type</label>
                            <select id="payment_type" class="form-select">
                                <option value="Pre-paid" selected>Pre-paid</option>
                                <option value="COD">COD</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">COD Amount (if COD)</label>
                            <input type="number" id="cod_amount" class="form-control" min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <button class="btn btn-outline-info w-100" type="button" id="calculateShippingBtn">
                                Get Shipping Cost
                            </button>
                        </div>
                    </div>
                    <div id="shippingSummary" class="mb-3"></div>

                    <div class="d-grid">
                        <button class="btn btn-primary" type="button" id="placeOrderBtn">Place Order</button>
                    </div>
                    <div class="mt-2" id="orderFormMessage"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const apiBase = '{{ url('/api/admin') }}';
    const delhiveryBase = '{{ url('/api/delhivery') }}';
    const originPincode = '{{ config('services.delhivery.origin_pin') }}';
    let currentShippingCost = 0;
    let isPincodeAvailable = false;

    function showMessage(elementId, message, type = 'info') {
        const el = document.getElementById(elementId);
        el.innerHTML = message ? `<div class="alert alert-${type} py-1 mb-0">${message}</div>` : '';
    }

    function setOrderFormEnabled(enabled) {
        const fields = [
            'customer_name',
            'customer_phone',
            'customer_email',
            'address_line1',
            'address_line2',
            'city',
            'state',
            'notes',
            'cgm',
            'payment_type',
            'cod_amount',
        ];

        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.disabled = !enabled;
            }
        });

        const addItemBtn = document.getElementById('addItemBtn');
        const calcBtn = document.getElementById('calculateShippingBtn');
        const placeBtn = document.getElementById('placeOrderBtn');

        [addItemBtn, calcBtn, placeBtn].forEach(btn => {
            if (btn) btn.disabled = !enabled;
        });

        // Existing item rows
        document.querySelectorAll('#itemsContainer input').forEach(input => {
            input.disabled = !enabled;
        });
    }

    async function checkAvailability() {
        const pincode = document.getElementById('pincode').value.trim();
        showMessage('availabilityResult', '');

        if (pincode.length !== 6) {
            showMessage('availabilityResult', 'Please enter a valid 6-digit pincode.', 'warning');
            return;
        }

        try {
            const response = await fetch(`${apiBase}/orders/check-availability`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ pincode })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                isPincodeAvailable = false;
                setOrderFormEnabled(false);
                showMessage('availabilityResult', data.message || 'Service not available.', 'danger');
                return;
            }

            isPincodeAvailable = true;
            setOrderFormEnabled(true);
            showMessage('availabilityResult', 'Service available for this pincode. You can now enter order & shipping details.', 'success');
        } catch (e) {
            isPincodeAvailable = false;
            setOrderFormEnabled(false);
            showMessage('availabilityResult', 'Error checking availability.', 'danger');
        }
    }

    function addItemRow(defaults = {}) {
        const container = document.getElementById('itemsContainer');
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 align-items-end';
        row.innerHTML = `
            <div class="col-4">
                <input type="text" class="form-control item-product-name" placeholder="Product" value="${defaults.product_name || ''}">
            </div>
            <div class="col-2">
                <input type="text" class="form-control item-sku" placeholder="SKU" value="${defaults.sku || ''}">
            </div>
            <div class="col-2">
                <input type="number" class="form-control item-qty" min="1" placeholder="Qty" value="${defaults.quantity || 1}">
            </div>
            <div class="col-2">
                <input type="number" class="form-control item-price" min="0" step="0.01" placeholder="Price" value="${defaults.unit_price || 0}">
            </div>
            <div class="col-2 text-end">
                <button class="btn btn-sm btn-outline-danger remove-item-btn" type="button">&times;</button>
            </div>
        `;
        container.appendChild(row);

        row.querySelector('.remove-item-btn').addEventListener('click', () => {
            row.remove();
        });

        // Respect current availability state
        if (!isPincodeAvailable) {
            row.querySelectorAll('input').forEach(input => input.disabled = true);
        }
    }

    async function calculateShipping() {
        if (!isPincodeAvailable) {
            showMessage('shippingSummary', 'First check service availability for this pincode.', 'warning');
            return;
        }

        const pincode = document.getElementById('pincode').value.trim();
        const cgmInput = document.getElementById('cgm');
        const paymentType = document.getElementById('payment_type').value;
        const codAmountInput = document.getElementById('cod_amount');

        showMessage('shippingSummary', '');

        if (!originPincode) {
            showMessage('shippingSummary', 'Origin pincode is not configured (DELHIVERY_ORIGIN_PIN).', 'warning');
            return;
        }

        if (pincode.length !== 6) {
            showMessage('shippingSummary', 'Enter a valid 6-digit destination pincode first.', 'warning');
            return;
        }

        const cgm = parseInt(cgmInput.value, 10);
        if (!cgm || cgm <= 0) {
            showMessage('shippingSummary', 'Enter a valid charged weight (grams).', 'warning');
            return;
        }

        const codAmount = paymentType === 'COD'
            ? parseFloat(codAmountInput.value || '0')
            : 0;

        try {
            const response = await fetch(`${delhiveryBase}/shipping-cost`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    d_pin: pincode,
                    o_pin: originPincode,
                    cgm,
                    pt: paymentType,
                    cod: codAmount,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                const msg = data.message || 'Failed to fetch shipping cost.';
                showMessage('shippingSummary', msg, 'danger');
                currentShippingCost = 0;
                return;
            }

            const payload = data.data || {};
            let amount = 0;

            if (Array.isArray(payload) && payload.length) {
                const first = payload[0];
                amount = first.total_amount
                    ?? first.sum_total
                    ?? first.total_charge
                    ?? 0;
            } else if (typeof payload === 'object' && payload !== null) {
                amount = payload.total_amount
                    ?? payload.sum_total
                    ?? payload.total_charge
                    ?? 0;
            }

            currentShippingCost = Number(amount) || 0;

            if (!currentShippingCost) {
                showMessage('shippingSummary', 'Shipping cost fetched, but no amount field was found in response.', 'info');
            } else {
                showMessage(
                    'shippingSummary',
                    `Estimated shipping cost: ₹${currentShippingCost.toFixed(2)} (mode: ${paymentType})`,
                    'success'
                );
            }
        } catch (e) {
            showMessage('shippingSummary', 'Error fetching shipping cost.', 'danger');
            currentShippingCost = 0;
        }
    }

    async function placeOrder() {
        if (!isPincodeAvailable) {
            showMessage('orderFormMessage', 'First check service availability for this pincode.', 'warning');
            return;
        }

        showMessage('orderFormMessage', '');

        const items = [];
        document.querySelectorAll('#itemsContainer .row').forEach(row => {
            items.push({
                product_name: row.querySelector('.item-product-name').value.trim(),
                sku: row.querySelector('.item-sku').value.trim(),
                quantity: parseInt(row.querySelector('.item-qty').value, 10) || 1,
                unit_price: parseFloat(row.querySelector('.item-price').value) || 0,
            });
        });

        const payload = {
            customer_name: document.getElementById('customer_name').value.trim(),
            customer_phone: document.getElementById('customer_phone').value.trim() || null,
            customer_email: document.getElementById('customer_email').value.trim() || null,
            address_line1: document.getElementById('address_line1').value.trim(),
            address_line2: document.getElementById('address_line2').value.trim() || null,
            city: document.getElementById('city').value.trim(),
            state: document.getElementById('state').value.trim() || null,
            pincode: document.getElementById('pincode').value.trim(),
            notes: document.getElementById('notes').value.trim() || null,
            shipping_cost: currentShippingCost,
            items,
        };

        try {
            const response = await fetch(`${apiBase}/orders`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                const msg = data.message || 'Failed to place order.';
                showMessage('orderFormMessage', msg, 'danger');
                return;
            }

            showMessage('orderFormMessage', 'Order placed successfully.', 'success');
        } catch (e) {
            showMessage('orderFormMessage', 'Error placing order.', 'danger');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('checkAvailabilityBtn').addEventListener('click', checkAvailability);
        document.getElementById('calculateShippingBtn').addEventListener('click', calculateShipping);
        document.getElementById('placeOrderBtn').addEventListener('click', placeOrder);
        document.getElementById('addItemBtn').addEventListener('click', () => addItemRow());

        addItemRow();
        setOrderFormEnabled(false);
    });
</script>
@endsection
