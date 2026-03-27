@extends('layouts.app')

@section('content')
<div class="container-fluid mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Coupons</h4>
            <p class="text-muted mb-0 small">Create discount codes to use on orders.</p>
        </div>
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-ticket-simple me-1"></i> New Coupon
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 mb-3">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($coupons->isEmpty())
                <p class="text-muted small mb-0 p-3">No coupons yet. Click “New Coupon” to create your first one.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th>Code</th>
                            <th>Details</th>
                            <th>Target</th>
                            <th>Usage</th>
                            <th>Validity</th>
                            <th>Active</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($coupons as $coupon)
                            <tr>
                                <td>
                                    <span class="badge bg-dark text-uppercase">{{ $coupon->code }}</span>
                                    @if($coupon->name)
                                        <div class="small mt-1">{{ $coupon->name }}</div>
                                    @endif
                                </td>
                                <td class="small">
                                    @php
                                        $label = 'All customers';
                                        if ($coupon->target_audience === 'high_spender') {
                                            $label = 'Spent > ₹' . number_format($coupon->target_value ?? 0, 0);
                                        } elseif ($coupon->target_audience === 'frequent_visitor') {
                                            $label = 'Visited ≥ ' . (int)($coupon->target_value ?? 0) . ' times';
                                        } elseif ($coupon->target_audience === 'new_user') {
                                            $label = 'New registrations';
                                        } elseif ($coupon->target_audience === 'first_order') {
                                            $label = 'First order only';
                                        }
                                    @endphp
                                    <span class="badge bg-light text-dark border">{{ $label }}</span>
                                </td>
                                <td class="small">
                                    <div>
                                        @if($coupon->discount_type === 'percent')
                                            {{ $coupon->discount_value }}% off
                                        @else
                                            ₹{{ number_format($coupon->discount_value, 2) }} off
                                        @endif
                                        @if($coupon->min_order_amount)
                                            <span class="text-muted">on orders above ₹{{ number_format($coupon->min_order_amount, 2) }}</span>
                                        @endif
                                    </div>
                                    @if($coupon->description)
                                        <div class="text-muted">{{ $coupon->description }}</div>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($coupon->max_uses)
                                        {{ $coupon->used_count }} / {{ $coupon->max_uses }}
                                    @else
                                        {{ $coupon->used_count }} used
                                    @endif
                                </td>
                                <td class="small">
                                    @if($coupon->starts_at || $coupon->expires_at)
                                        @if($coupon->starts_at)
                                            <div>From: {{ $coupon->starts_at->format('d M, Y') }}</div>
                                        @endif
                                        @if($coupon->expires_at)
                                            <div>Until: {{ $coupon->expires_at->format('d M, Y') }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">No expiry</span>
                                    @endif
                                </td>
                                <td>
                                    @if($coupon->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button"
                                                class="btn btn-outline-primary"
                                                onclick='openCouponRecipientsModal({{ $coupon->id }}, @json($coupon->code))'>
                                            Customers
                                        </button>
                                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-outline-secondary">Edit</a>
                                        <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Delete this coupon?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $coupons->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Coupon recipients modal -->
<div class="modal fade" id="couponRecipientsModal" tabindex="-1" aria-labelledby="couponRecipientsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="couponRecipientsModalLabel">Eligible Customers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-2">These customers match the rules for this coupon based on their past orders and activity. You can switch between coupon rules and all customers, and add more filters.</p>

                <div class="border rounded p-2 mb-3 bg-light small">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Show</label>
                            <select id="couponRecipientsMode" class="form-select form-select-sm">
                                <option value="coupon" selected>Coupon target only</option>
                                <option value="all">All customers</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Min orders</label>
                            <input type="number" min="0" id="couponFilterMinOrders" class="form-control form-control-sm" placeholder="e.g. 1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Min spent (₹)</label>
                            <input type="number" min="0" step="0.01" id="couponFilterMinSpent" class="form-control form-control-sm" placeholder="1000">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">From date</label>
                            <input type="date" id="couponFilterFromDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">To date</label>
                            <input type="date" id="couponFilterToDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-1">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="couponFilterOnlyDelivered">
                                <label class="form-check-label" for="couponFilterOnlyDelivered">
                                    Delivered
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetCouponRecipientsFilters()">Reset</button>
                        <button type="button" class="btn btn-sm btn-primary" onclick="loadCouponRecipients()">Apply filters</button>
                    </div>
                </div>
                <div class="table-responsive border rounded">
                    <table class="table table-sm mb-0">
                        <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th class="text-end">Orders</th>
                            <th class="text-end">Total Spent</th>
                            <th class="text-end">Allow</th>
                        </tr>
                        </thead>
                        <tbody id="couponRecipientsBody">
                        <tr>
                            <td colspan="6" class="text-muted small">Loading customers...</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <p id="couponRecipientsSummary" class="small mt-2 text-muted"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveCouponRecipients()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    const couponRecipientsBase = '{{ url('/admin/coupons') }}';
    let currentCouponId = null;

    async function openCouponRecipientsModal(couponId, code) {
        const modalEl = document.getElementById('couponRecipientsModal');
        const title = document.getElementById('couponRecipientsModalLabel');
        const tbody = document.getElementById('couponRecipientsBody');
        const summary = document.getElementById('couponRecipientsSummary');
        currentCouponId = couponId;

        if (title) {
            title.textContent = 'Eligible Customers for ' + code;
        }
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-muted small">Loading customers...</td></tr>';
        }
        if (summary) {
            summary.textContent = '';
        }

        if (window.bootstrap && bootstrap.Modal) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        } else if (modalEl) {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            modalEl.removeAttribute('aria-hidden');
        }

        try {
            await loadCouponRecipients();
        } catch (e) {
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-danger small">Error loading customers.</td></tr>';
            }
        }

        // manual close support if Bootstrap JS is not available
        if (!(window.bootstrap && bootstrap.Modal) && modalEl) {
            const closeButtons = modalEl.querySelectorAll('[data-bs-dismiss="modal"]');
            closeButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    modalEl.classList.remove('show');
                    modalEl.style.display = 'none';
                    modalEl.setAttribute('aria-hidden', 'true');
                });
            });
        }
    }

    async function loadCouponRecipients() {
        const tbody = document.getElementById('couponRecipientsBody');
        const summary = document.getElementById('couponRecipientsSummary');
        if (!currentCouponId) return;

        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-muted small">Loading customers...</td></tr>';
        }
        if (summary) {
            summary.textContent = '';
        }

        const modeEl = document.getElementById('couponRecipientsMode');
        const minOrdersEl = document.getElementById('couponFilterMinOrders');
        const minSpentEl = document.getElementById('couponFilterMinSpent');
        const fromDateEl = document.getElementById('couponFilterFromDate');
        const toDateEl = document.getElementById('couponFilterToDate');
        const onlyDeliveredEl = document.getElementById('couponFilterOnlyDelivered');

        const params = new URLSearchParams();
        if (modeEl && modeEl.value) params.set('mode', modeEl.value);
        if (minOrdersEl && minOrdersEl.value) params.set('min_orders', minOrdersEl.value);
        if (minSpentEl && minSpentEl.value) params.set('min_spent', minSpentEl.value);
        if (fromDateEl && fromDateEl.value) params.set('from_date', fromDateEl.value);
        if (toDateEl && toDateEl.value) params.set('to_date', toDateEl.value);
        if (onlyDeliveredEl && onlyDeliveredEl.checked) params.set('only_delivered', '1');

        try {
            const url = `${couponRecipientsBase}/${currentCouponId}/recipients?${params.toString()}`;
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const json = await response.json();

            if (!response.ok || !json.success) {
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-danger small">Failed to load customers.</td></tr>';
                }
                return;
            }

            const rows = Array.isArray(json.data) ? json.data : [];
            if (!rows.length) {
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-muted small">No customers match this coupon yet.</td></tr>';
                }
                if (summary) {
                    summary.textContent = 'No eligible customers found based on current rules.';
                }
                return;
            }

            if (tbody) {
                tbody.innerHTML = rows.map(r => {
                    const orders = r.orders_count ?? 0;
                    const spent = (r.total_spent ?? 0).toFixed(2);
                    const phone = (r.phone || '').trim();
                    const name = (r.name || 'Customer').replace(/"/g, '&quot;');
                    const email = (r.email || '').replace(/"/g, '&quot;');
                    const allowed = r.allowed !== false;
                    const rowClass = allowed ? '' : 'table-secondary';
                    const checkedAttr = allowed ? 'checked' : '';
                    return `
                        <tr class="${rowClass}" data-phone="${phone}" data-email="${email}" data-name="${name}">
                            <td class="small">${name}</td>
                            <td class="small">${phone}</td>
                            <td class="small">${email}</td>
                            <td class="small text-end">${orders}</td>
                            <td class="small text-end">&#8377;${spent}</td>
                            <td class="small text-end">
                                <input type="checkbox" class="form-check-input" ${checkedAttr}
                                       onchange="toggleCouponRecipientAllowance(this)">
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            if (summary) {
                summary.textContent = `Found ${rows.length} customers who fit this coupon's target audience.`;
            }
        } catch (e) {
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-danger small">Error loading customers.</td></tr>';
            }
        }
    }

    function resetCouponRecipientsFilters() {
        const ids = [
            'couponRecipientsMode',
            'couponFilterMinOrders',
            'couponFilterMinSpent',
            'couponFilterFromDate',
            'couponFilterToDate',
            'couponFilterOnlyDelivered',
        ];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            if (el.tagName === 'SELECT') {
                el.value = 'coupon';
            } else if (el.type === 'checkbox') {
                el.checked = false;
            } else {
                el.value = '';
            }
        });
        loadCouponRecipients();
    }

    function toggleCouponRecipientAllowance(checkbox) {
        const row = checkbox.closest('tr');
        if (!row) return;

        // Just visually indicate disabled customers; do not persist.
        if (!checkbox.checked) {
            row.classList.add('table-secondary');
        } else {
            row.classList.remove('table-secondary');
        }
    }

    async function saveCouponRecipients() {
        if (!currentCouponId) return;

        const rows = document.querySelectorAll('#couponRecipientsBody tr');
        const allPhones = [];
        const allowedPhones = [];

        rows.forEach(row => {
            const phone = (row.getAttribute('data-phone') || '').trim();
            if (!phone) return;
            allPhones.push(phone);
            const cb = row.querySelector('input[type=\"checkbox\"]');
            if (cb && cb.checked) {
                allowedPhones.push(phone);
            }
        });

        const tokenMeta = document.querySelector('meta[name=\"csrf-token\"]');
        const csrf = tokenMeta ? tokenMeta.getAttribute('content') : null;

        try {
            const response = await fetch(`${couponRecipientsBase}/${currentCouponId}/recipients/save`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                },
                body: JSON.stringify({
                    all_phones: allPhones,
                    allowed_phones: allowedPhones,
                }),
            });

            if (response.ok) {
                alert('Recipients saved for this coupon.');
            } else {
                alert('Could not save recipients.');
            }
        } catch (e) {
            alert('Error while saving recipients.');
        }
    }
</script>
@endsection
