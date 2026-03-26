@extends('layouts.app')

@section('content')
<div class="container-fluid mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Pickups</h4>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Pickup Date</label>
                    <input type="date" id="pickupDate" class="form-control form-control-sm">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Pickup Time (HH:MM:SS)</label>
                    <input type="time" id="pickupTime" class="form-control form-control-sm" value="11:00">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Expected Package Count</label>
                    <input type="number" id="pickupCount" class="form-control form-control-sm" value="1" min="1">
                </div>
            </div>

            <div class="mt-3 d-flex justify-content-end">
                <button type="button" id="pickupSubmitBtn" class="btn btn-primary btn-sm">Create Pickup</button>
            </div>

            <div id="pickupMessage" class="small mt-2 text-muted"></div>
            <div id="pickupResult" class="mt-3 small"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const apiBase = '{{ url('/api/delhivery') }}';
        const pickupDateInput = document.getElementById('pickupDate');
        const pickupTimeInput = document.getElementById('pickupTime');
        const pickupCountInput = document.getElementById('pickupCount');
        const pickupMessage = document.getElementById('pickupMessage');
        const pickupResult = document.getElementById('pickupResult');
        const pickupBtn = document.getElementById('pickupSubmitBtn');

        if (pickupBtn) {
            pickupBtn.addEventListener('click', async () => {
                pickupMessage.textContent = 'Creating pickup...';
                pickupResult.innerHTML = '';

                const payload = {
                    pickup_date: pickupDateInput.value || null,
                    pickup_time: pickupTimeInput.value ? pickupTimeInput.value + ':00' : null,
                    expected_package_count: parseInt(pickupCountInput.value || '1', 10) || 1,
                };

                try {
                    const response = await fetch(`${apiBase}/pickup`, {
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
                        pickupMessage.textContent = data.message || 'Failed to create pickup.';
                        return;
                    }

                    pickupMessage.textContent = 'Pickup created successfully.';
                    pickupResult.innerHTML = `<pre class="small bg-light border rounded p-2">${JSON.stringify(data.data, null, 2)}</pre>`;
                } catch (e) {
                    pickupMessage.textContent = 'Error while creating pickup.';
                }
            });
        }
    });
</script>
@endsection

