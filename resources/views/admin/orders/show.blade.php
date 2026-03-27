@extends('layouts.app')

@section('content')
<style>
    .order-detail-page {
        font-size: 0.9rem;
    }

    .order-detail-page .card-header {
        background: #f9fafb;
    }

    .order-detail-page .order-header-icon {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #6366f1, #a855f7);
        color: #ffffff;
        margin-right: 0.75rem;
    }

    .order-detail-page .mini-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #9ca3af;
    }

    .order-detail-page .info-row {
        display: flex;
        align-items: flex-start;
        margin-bottom: 0.25rem;
    }

    .order-detail-page .info-row i {
        width: 18px;
        margin-right: 0.4rem;
        color: #6b7280;
    }

    .order-detail-page .amount-badge {
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        font-size: 0.8rem;
        background: #eef2ff;
        color: #4f46e5;
    }
</style>
<div class="container mb-5 order-detail-page">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Order Details</h4>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Back to Orders</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger py-2 mb-3">
            {{ session('error') }}
        </div>
    @elseif(session('success'))
        <div class="alert alert-success py-2 mb-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="order-header-icon">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <div>
                    <div class="mini-label">Order</div>
                    <h5 class="mb-0">#{{ $order->order_number }}</h5>
                    <small class="text-muted">
                        <i class="fa-regular fa-clock me-1"></i>
                        Placed on {{ $order->created_at }}
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-{{ $order->status === 'placed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'secondary') }} text-uppercase d-inline-flex align-items-center gap-1">
                    <i class="fa-solid fa-circle-dot"></i>
                    {{ $order->status }}
                </span>
                <div class="btn-group btn-group-sm" role="group" aria-label="Order actions">
                    @if($order->delhivery_waybill)
                        <button type="button" class="btn btn-outline-primary" id="trackOrderBtn" title="Track shipment">
                            <i class="fa-solid fa-location-arrow"></i>
                        </button>
                        <a href="{{ route('admin.orders.label', $order) }}" class="btn btn-outline-secondary" title="Download label">
                            <i class="fa-solid fa-print"></i>
                        </a>
                    @endif
                    <button type="button" class="btn btn-outline-primary" id="updateShippingBtn" title="Update shipping cost">
                        <i class="fa-solid fa-indian-rupee-sign"></i>
                    </button>
                    <button type="button" class="btn btn-outline-success" id="createPickupBtn" title="Create pickup">
                        <i class="fa-solid fa-truck"></i>
                    </button>
                    @if($order->status !== 'cancelled')
                        <button type="button" class="btn btn-outline-danger" id="cancelOrderBtn" title="Cancel order">
                            <i class="fa-regular fa-circle-xmark"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            <div class="mini-label">Customer</div>
                            <h6 class="mb-0"><i class="fa-regular fa-user me-1"></i>{{ $order->customer_name }}</h6>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleAddressEditBtn">
                            <i class="fa-regular fa-pen-to-square me-1"></i> Edit
                        </button>
                    </div>

                    <div id="addressViewBlock">
                        @if($order->customer_phone)
                            <div class="info-row">
                                <i class="fa-solid fa-phone"></i>
                                <div>{{ $order->customer_phone }}</div>
                            </div>
                        @endif
                        @if($order->customer_email)
                            <div class="info-row">
                                <i class="fa-regular fa-envelope"></i>
                                <div>{{ $order->customer_email }}</div>
                            </div>
                        @endif
                        <div class="info-row">
                            <i class="fa-solid fa-location-dot"></i>
                            <div>
                                {{ $order->address_line1 }}
                                @if($order->address_line2)
                                    , {{ $order->address_line2 }}
                                @endif
                                , {{ $order->city }}
                                @if($order->state)
                                    , {{ $order->state }}
                                @endif
                                - {{ $order->pincode }}
                            </div>
                        </div>
                        @if($order->notes)
                            <div class="info-row">
                                <i class="fa-regular fa-note-sticky"></i>
                                <div>{{ $order->notes }}</div>
                            </div>
                        @endif
                    </div>

                    <div id="addressEditContainer" class="border rounded p-2 mt-2 d-none">
                        <div class="mb-2">
                            <label class="form-label form-label-sm mb-0">Name</label>
                            <input type="text" class="form-control form-control-sm" id="addrCustomerName" value="{{ $order->customer_name }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label form-label-sm mb-0">Phone</label>
                            <input type="text" class="form-control form-control-sm" id="addrCustomerPhone" value="{{ $order->customer_phone }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label form-label-sm mb-0">Email</label>
                            <input type="email" class="form-control form-control-sm" id="addrCustomerEmail" value="{{ $order->customer_email }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label form-label-sm mb-0">Address Line 1</label>
                            <input type="text" class="form-control form-control-sm" id="addrLine1" value="{{ $order->address_line1 }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label form-label-sm mb-0">Address Line 2</label>
                            <input type="text" class="form-control form-control-sm" id="addrLine2" value="{{ $order->address_line2 }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label form-label-sm mb-0">City</label>
                            <input type="text" class="form-control form-control-sm" id="addrCity" value="{{ $order->city }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label form-label-sm mb-0">State</label>
                            <input type="text" class="form-control form-control-sm" id="addrState" value="{{ $order->state }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label form-label-sm mb-0">Pincode</label>
                            <input type="text" class="form-control form-control-sm" id="addrPincode" value="{{ $order->pincode }}" maxlength="6">
                        </div>
                        <div class="mb-2">
                            <label class="form-label form-label-sm mb-0">Notes</label>
                            <textarea class="form-control form-control-sm" id="addrNotes" rows="2">{{ $order->notes }}</textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="cancelAddressEditBtn">Cancel</button>
                            <button type="button" class="btn btn-sm btn-primary" id="saveAddressBtn">Save Address</button>
                        </div>
                        <small id="addressUpdateMessage" class="d-block mt-1 text-muted"></small>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <h6>Amounts & Shipping</h6>
                    <p class="mb-1"><strong>Subtotal:</strong> ₹{{ number_format($order->subtotal, 2) }}</p>
                    <p class="mb-1">
                        <strong>Shipping Cost:</strong>
                        ₹<span id="shippingCostDisplay">{{ number_format($order->shipping_cost, 2) }}</span>
                    </p>
                    <p class="mb-1"><strong>Total Amount:</strong> ₹{{ number_format($order->total_amount, 2) }}</p>
                    <p class="mb-1"><strong>Current Status:</strong> {{ ucfirst($order->status) }}</p>
                    <p class="mb-1"><strong>Last Updated:</strong> {{ $order->updated_at }}</p>

                    <div id="shippingEditContainer" class="mt-2 d-none">
                        <div class="input-group input-group-sm" style="max-width: 260px;">
                            <span class="input-group-text">Shipping (₹)</span>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control"
                                id="shippingCostInput"
                                value="{{ $order->shipping_cost }}"
                            >
                            <button class="btn btn-primary" type="button" id="saveShippingBtn">Save</button>
                        </div>
                        <small id="shippingUpdateMessage" class="d-block mt-1 text-muted"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($order->delhivery_waybill)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-route"></i>
                    Shipment Tracking
                </h6>
                <small class="text-muted">Waybill: {{ $order->delhivery_waybill }}</small>
            </div>
            <div class="card-body">
                <div id="trackingMessage" class="small text-muted mb-2"></div>
                <div id="trackingEta" class="small fw-semibold mb-2"></div>
                <div id="trackingTimeline" class="small"></div>
            </div>
        </div>
    @endif

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

    <div id="orderActionsMessage" class="mt-2"></div>
</div>

<script>
    const adminApiBase = '{{ url('/api/admin') }}';
    const delhiveryApiBase = '{{ url('/api/delhivery') }}';
    const orderId = {{ $order->id }};
    const delhiveryWaybill = @json($order->delhivery_waybill);

    function setOrderMessage(message, type = 'info') {
        const el = document.getElementById('orderActionsMessage');
        el.innerHTML = message
            ? `<div class="alert alert-${type} py-1 mb-0">${message}</div>`
            : '';
    }

    document.addEventListener('DOMContentLoaded', () => {
        const updateShippingBtn = document.getElementById('updateShippingBtn');
        const trackOrderBtn = document.getElementById('trackOrderBtn');
        const createPickupBtn = document.getElementById('createPickupBtn');
        const cancelOrderBtn = document.getElementById('cancelOrderBtn');
        const shippingEditContainer = document.getElementById('shippingEditContainer');
        const shippingCostInput = document.getElementById('shippingCostInput');
        const saveShippingBtn = document.getElementById('saveShippingBtn');
        const shippingCostDisplay = document.getElementById('shippingCostDisplay');
        const shippingUpdateMessage = document.getElementById('shippingUpdateMessage');

        const toggleAddressEditBtn = document.getElementById('toggleAddressEditBtn');
        const addressEditContainer = document.getElementById('addressEditContainer');
        const addressViewBlock = document.getElementById('addressViewBlock');
        const cancelAddressEditBtn = document.getElementById('cancelAddressEditBtn');
        const saveAddressBtn = document.getElementById('saveAddressBtn');
        const addressUpdateMessage = document.getElementById('addressUpdateMessage');

        const addrCustomerName = document.getElementById('addrCustomerName');
        const addrCustomerPhone = document.getElementById('addrCustomerPhone');
        const addrCustomerEmail = document.getElementById('addrCustomerEmail');
        const addrLine1 = document.getElementById('addrLine1');
        const addrLine2 = document.getElementById('addrLine2');
        const addrCity = document.getElementById('addrCity');
        const addrState = document.getElementById('addrState');
        const addrPincode = document.getElementById('addrPincode');
        const addrNotes = document.getElementById('addrNotes');
        const trackingMessage = document.getElementById('trackingMessage');
        const trackingEta = document.getElementById('trackingEta');
        const trackingTimeline = document.getElementById('trackingTimeline');

        async function trackShipment() {
            if (!delhiveryWaybill) {
                if (trackingMessage) {
                    trackingMessage.textContent = 'No Delhivery waybill available for this order.';
                }
                return;
            }

            if (trackingMessage) {
                trackingMessage.textContent = 'Fetching latest tracking details...';
            }
            if (trackingEta) trackingEta.textContent = '';
            if (trackingTimeline) trackingTimeline.innerHTML = '';

            try {
                const response = await fetch(`${delhiveryApiBase}/track`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ waybill: delhiveryWaybill }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    if (trackingMessage) {
                        trackingMessage.textContent = data.message || 'Failed to fetch tracking details.';
                    }
                    return;
                }

                if (trackingMessage) {
                    trackingMessage.textContent = '';
                }

                const info = data.data || {};
                let shipment = null;

                if (Array.isArray(info.ShipmentData) && info.ShipmentData.length) {
                    shipment = info.ShipmentData[0].Shipment || null;
                } else if (info.Shipment) {
                    shipment = info.Shipment;
                }

                let eta = null;
                if (shipment) {
                    eta = shipment.EDD
                        || shipment.ExpectedDeliveryDate
                        || shipment.expected_delivery_date
                        || null;
                }

                if (trackingEta) {
                    trackingEta.textContent = eta
                        ? `Estimated delivery: ${eta}`
                        : '';
                }

                let scans = [];
                if (shipment && Array.isArray(shipment.Scans)) {
                    scans = shipment.Scans;
                } else if (shipment && Array.isArray(shipment.scans)) {
                    scans = shipment.scans;
                }

                if (trackingTimeline) {
                    if (!scans.length) {
                        trackingTimeline.innerHTML = '<p class="text-muted mb-0">Tracking data is not available yet.</p>';
                    } else {
                        const items = scans.map(scan => {
                            const status = scan.Scan || scan.scan || scan.ScanType || '';
                            const loc = scan.ScannedLocation || scan.scannedLocation || scan.Scanned_Office || '';
                            const time = scan.ScanDateTime || scan.scanDateTime || scan.ScannedDate || scan.Scanned_time || '';
                            const remarks = scan.Remarks || scan.remarks || '';

                            return `
                                <div class="d-flex mb-2">
                                    <div class="me-2 text-success">
                                        <i class="fa-solid fa-circle-dot"></i>
                                    </div>
                                    <div>
                                        <div><strong>${status || 'Update'}</strong></div>
                                        <div class="text-muted">${time}${loc ? ' · ' + loc : ''}</div>
                                        ${remarks ? `<div class="text-muted">${remarks}</div>` : ''}
                                    </div>
                                </div>
                            `;
                        }).join('');

                        trackingTimeline.innerHTML = items;
                    }
                }
            } catch (e) {
                if (trackingMessage) {
                    trackingMessage.textContent = 'Error while fetching tracking details.';
                }
            }
        }

        if (updateShippingBtn) {
            updateShippingBtn.addEventListener('click', () => {
                shippingEditContainer.classList.toggle('d-none');
                shippingUpdateMessage.textContent = '';
            });
        }

        if (trackOrderBtn) {
            trackOrderBtn.addEventListener('click', () => {
                trackShipment();
            });
        }

        if (toggleAddressEditBtn) {
            toggleAddressEditBtn.addEventListener('click', () => {
                addressEditContainer.classList.toggle('d-none');
                addressViewBlock.classList.toggle('d-none');
                addressUpdateMessage.textContent = '';
            });
        }

        if (cancelAddressEditBtn) {
            cancelAddressEditBtn.addEventListener('click', () => {
                addressEditContainer.classList.add('d-none');
                addressViewBlock.classList.remove('d-none');
                addressUpdateMessage.textContent = '';
            });
        }

        if (saveAddressBtn) {
            saveAddressBtn.addEventListener('click', async () => {
                const name = (addrCustomerName.value || '').trim();
                const phone = (addrCustomerPhone.value || '').trim();
                const email = (addrCustomerEmail.value || '').trim();
                const line1 = (addrLine1.value || '').trim();
                const line2 = (addrLine2.value || '').trim();
                const city = (addrCity.value || '').trim();
                const state = (addrState.value || '').trim();
                const pincode = (addrPincode.value || '').trim();
                const notes = (addrNotes.value || '').trim();

                if (!name || !line1 || !city || pincode.length !== 6) {
                    addressUpdateMessage.textContent = 'Name, address line 1, city and 6-digit pincode are required.';
                    return;
                }

                addressUpdateMessage.textContent = 'Updating address...';

                try {
                    const response = await fetch(`${adminApiBase}/orders/${orderId}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            customer_name: name,
                            customer_phone: phone || null,
                            customer_email: email || null,
                            address_line1: line1,
                            address_line2: line2 || null,
                            city,
                            state: state || null,
                            pincode,
                            notes: notes || null,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        addressUpdateMessage.textContent = data.message || 'Failed to update address.';
                        return;
                    }

                    addressUpdateMessage.textContent = 'Address updated.';
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } catch (e) {
                    addressUpdateMessage.textContent = 'Error updating address.';
                }
            });
        }

        if (saveShippingBtn) {
            saveShippingBtn.addEventListener('click', async () => {
                const value = parseFloat(shippingCostInput.value || '0');
                if (isNaN(value) || value < 0) {
                    shippingUpdateMessage.textContent = 'Enter a valid non-negative shipping cost.';
                    return;
                }

                shippingUpdateMessage.textContent = 'Updating shipping cost...';

                try {
                    const response = await fetch(`${adminApiBase}/orders/${orderId}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ shipping_cost: value }),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        shippingUpdateMessage.textContent = data.message || 'Failed to update shipping cost.';
                        return;
                    }

                    shippingUpdateMessage.textContent = 'Shipping cost updated.';
                    if (shippingCostDisplay) {
                        shippingCostDisplay.textContent = (value).toFixed(2);
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } catch (e) {
                    shippingUpdateMessage.textContent = 'Error updating shipping cost.';
                }
            });
        }

        if (cancelOrderBtn) {
            cancelOrderBtn.addEventListener('click', async () => {
                if (!confirm('Are you sure you want to cancel this order?')) {
                    return;
                }

                setOrderMessage('Cancelling order...', 'info');

                try {
                    const response = await fetch(`${adminApiBase}/orders/${orderId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                    });
                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        setOrderMessage(data.message || 'Failed to cancel order.', 'danger');
                        return;
                    }

                    setOrderMessage('Order cancelled.', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } catch (e) {
                    setOrderMessage('Error cancelling order.', 'danger');
                }
            });
        }

        if (createPickupBtn) {
            createPickupBtn.addEventListener('click', async () => {
                if (!delhiveryWaybill) {
                    setOrderMessage('No Delhivery waybill set for this order.', 'warning');
                    return;
                }

                setOrderMessage('Requesting pickup...', 'info');

                try {
                    const response = await fetch(`${delhiveryApiBase}/pickup`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            expected_package_count: 1
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        setOrderMessage(data.message || 'Failed to create pickup request.', 'danger');
                        return;
                    }

                    setOrderMessage('Pickup request sent successfully.', 'success');
                } catch (e) {
                    setOrderMessage('Error creating pickup request.', 'danger');
                }
            });
        }
    });
</script>
@endsection
