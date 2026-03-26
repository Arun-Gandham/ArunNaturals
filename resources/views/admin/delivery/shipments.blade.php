@extends('layouts.app')

@section('content')
<div class="container-fluid mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Shipments</h4>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Track Shipment</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small">Waybill Number</label>
                        <input type="text" id="trackWaybill" class="form-control form-control-sm" placeholder="Enter waybill">
                    </div>
                    <button type="button" id="trackBtn" class="btn btn-primary btn-sm">Track</button>
                    <div id="trackMessage" class="small mt-2 text-muted"></div>
                    <div id="trackResult" class="mt-3 small"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Recent Shipments</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th>Order</th>
                                <th>Waybill</th>
                                <th>Status</th>
                                <th>Customer</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($shipments as $order)
                                <tr>
                                    <td class="small">{{ $order->order_number }}</td>
                                    <td class="small">{{ $order->delhivery_waybill }}</td>
                                    <td>
                                        <span class="badge bg-{{ $order->status === 'placed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'secondary') }} text-uppercase small">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="small">{{ $order->customer_name }}</td>
                                    <td class="text-end small">₹{{ number_format($order->total_amount, 2) }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-outline-primary btn-sm" data-waybill="{{ $order->delhivery_waybill }}" data-action="track-row">Track</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-muted small text-center py-3">No shipments with waybill yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const apiBase = '{{ url('/api/delhivery') }}';
        const trackWaybillInput = document.getElementById('trackWaybill');
        const trackBtn = document.getElementById('trackBtn');
        const trackMsg = document.getElementById('trackMessage');
        const trackResult = document.getElementById('trackResult');

        async function track(waybill) {
            const trimmed = (waybill || '').trim();
            if (!trimmed) {
                trackMsg.textContent = 'Enter a waybill number.';
                trackResult.innerHTML = '';
                return;
            }

            trackMsg.textContent = 'Tracking...';
            trackResult.innerHTML = '';

            try {
                const response = await fetch(`${apiBase}/track`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ waybill: trimmed }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    trackMsg.textContent = data.message || 'Failed to fetch tracking details.';
                    return;
                }

                trackMsg.textContent = '';
                const info = data.data || {};

                trackResult.innerHTML = `<pre class="small bg-light border rounded p-2">${JSON.stringify(info, null, 2)}</pre>`;
            } catch (e) {
                trackMsg.textContent = 'Error while tracking shipment.';
            }
        }

        if (trackBtn) {
            trackBtn.addEventListener('click', () => track(trackWaybillInput.value));
        }

        document.querySelectorAll('button[data-action="track-row"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const wb = btn.getAttribute('data-waybill');
                trackWaybillInput.value = wb;
                track(wb);
            });
        });
    });
</script>
@endsection

