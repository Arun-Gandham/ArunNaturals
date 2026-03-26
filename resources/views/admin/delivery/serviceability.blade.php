@extends('layouts.app')

@section('content')
<div class="container-fluid mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Serviceability</h4>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Pincode</label>
                    <input type="text" id="svcPincode" class="form-control form-control-sm" maxlength="6" placeholder="Enter 6-digit pincode">
                </div>
                <div class="col-md-2">
                    <button type="button" id="svcCheckBtn" class="btn btn-primary btn-sm">Check</button>
                </div>
            </div>

            <div id="svcMessage" class="small mt-2 text-muted"></div>
            <div id="svcResult" class="mt-3 small"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const apiBase = '{{ url('/api/delhivery') }}';
        const pinInput = document.getElementById('svcPincode');
        const checkBtn = document.getElementById('svcCheckBtn');
        const msg = document.getElementById('svcMessage');
        const result = document.getElementById('svcResult');

        async function checkPincode() {
            const pin = (pinInput.value || '').trim();
            if (pin.length !== 6) {
                msg.textContent = 'Pincode must be 6 digits.';
                result.innerHTML = '';
                return;
            }

            msg.textContent = 'Checking serviceability...';
            result.innerHTML = '';

            try {
                const response = await fetch(`${apiBase}/pincode/${pin}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    msg.textContent = data.message || 'Service not available.';
                    result.innerHTML = '';
                    return;
                }

                msg.textContent = 'Service available.';
                result.innerHTML = `<pre class="small bg-light border rounded p-2">${JSON.stringify(data.data, null, 2)}</pre>`;
            } catch (e) {
                msg.textContent = 'Error while checking serviceability.';
            }
        }

        if (checkBtn) {
            checkBtn.addEventListener('click', checkPincode);
        }
    });
</script>
@endsection

