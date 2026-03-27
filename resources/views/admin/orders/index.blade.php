@extends('layouts.app')

@section('content')
<style>
    .orders-compact {
        font-size: 1rem;
    }

    .orders-compact .card-header,
    .orders-compact .card-body {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }

    .orders-compact .form-control,
    .orders-compact .form-select,
    .orders-compact .btn {
        font-size: 1rem;
        padding-top: 0.2rem;
        padding-bottom: 0.2rem;
        height: auto;
    }

    .orders-compact table th,
    .orders-compact table td {
        padding-top: 0.4rem;
        padding-bottom: 0.4rem;
    }
</style>
<div class="container-fluid mb-5 orders-compact">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Orders</h4>
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">Create Order</a>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <form id="ordersFilterForm" class="row g-3 align-items-end small">
                <div class="col-md-3">
                    <label for="search" class="form-label mb-1">Search (Order ID, AWB, Customer)</label>
                    <input type="text" id="search" name="search" class="form-control" placeholder="ORD‑..., AWB, name, phone">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label mb-1">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">All</option>
                        <option value="placed">Placed</option>
                        <option value="draft">Draft</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="from_date" class="form-label mb-1">From Date</label>
                    <input type="date" id="from_date" name="from_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="to_date" class="form-label mb-1">To Date</label>
                    <input type="date" id="to_date" name="to_date" class="form-control">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Search</button>
                    <button type="button" id="resetFiltersBtn" class="btn btn-outline-secondary flex-grow-1">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Order List</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" type="button" id="refreshOrdersBtn">Refresh</button>
                <button class="btn btn-sm btn-primary" type="button" id="downloadSelectedLabelsBtn">
                    Download Selected Labels
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle small" id="ordersTable">
                    <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th style="width: 36px;">
                            <input type="checkbox" id="selectAllOrders">
                        </th>
                        <th>Order ID and AWB</th>
                        <th>Manifested Date</th>
                        <th>Status</th>
                        <th>Pickup and Delivery Address</th>
                        <th>Transport Mode</th>
                        <th>Last Update</th>
                        <th>Payment Mode</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="ordersMessage" class="p-3"></div>
            <div id="ordersPagination" class="px-3 pb-3 d-flex justify-content-between align-items-center small"></div>
        </div>
    </div>
</div>

<script>
    const apiBase = '{{ url('/api/admin') }}';
    const orderShowBase = '{{ url('/admin/orders') }}';
    const orderLabelBase = '{{ url('/admin/orders') }}';
    const bulkLabelsUrl = '{{ route('admin.orders.labels.bulk') }}';

    let currentPage = 1;
    let lastPage = 1;

    function showMessage(elementId, message, type = 'info') {
        const el = document.getElementById(elementId);
        el.innerHTML = message ? `<div class="alert alert-${type} py-1 mb-0">${message}</div>` : '';
    }

    function formatDateTime(value) {
        if (!value) return '';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return value;
        return d.toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' })
            + ' ' +
            d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
    }

    function buildQuery(params) {
        const searchParams = new URLSearchParams();
        Object.entries(params).forEach(([key, value]) => {
            if (value) {
                searchParams.append(key, value);
            }
        });
        return searchParams.toString();
    }

    async function loadOrders(page = 1) {
        const tbody = document.querySelector('#ordersTable tbody');
        tbody.innerHTML = '';
        showMessage('ordersMessage', 'Loading orders...', 'info');

        const search    = document.getElementById('search').value.trim();
        const status    = document.getElementById('status').value;
        const from_date = document.getElementById('from_date').value;
        const to_date   = document.getElementById('to_date').value;

        const query = buildQuery({ search, status, from_date, to_date, page });
        const url   = query ? `${apiBase}/orders?${query}` : `${apiBase}/orders`;

        try {
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await response.json();

            if (!response.ok || !data.success) {
                showMessage('ordersMessage', data.message || 'Failed to load orders.', 'danger');
                return;
            }

            const pageData = data.data;
            const orders = pageData.data || [];

            currentPage = pageData.current_page || 1;
            lastPage = pageData.last_page || 1;
            if (!orders.length) {
                showMessage('ordersMessage', 'No orders found.', 'secondary');
                document.getElementById('ordersPagination').innerHTML = '';
                return;
            }

            showMessage('ordersMessage', '');

            orders.forEach(order => {
                const tr = document.createElement('tr');
                const manifestedAt = formatDateTime(order.created_at);
                const updatedAt    = formatDateTime(order.updated_at);
                const statusClass  =
                    order.status === 'placed' ? 'success' :
                        order.status === 'cancelled' ? 'danger' : 'secondary';

                const pickupAddress = `Arun Natural Products (Kakinada - ${order.pincode || ''})`;
                const deliveryAddress = `${order.customer_name || ''}, ${(order.address_line1 || '')}${order.address_line2 ? ', ' + order.address_line2 : ''}, ${(order.city || '')} - ${(order.pincode || '')}`;

                tr.innerHTML = `
                    <td><input type="checkbox" class="order-select" value="${order.id}" ${order.delhivery_waybill ? '' : 'disabled'}></td>
                    <td>
                        <div class="fw-semibold">
                            <a href="${orderShowBase}/${order.id}" class="text-decoration-none">${order.order_number}</a>
                        </div>
                        <div class="text-muted small">${order.delhivery_waybill || '-'}</div>
                    </td>
                    <td>
                        <div>${manifestedAt || '-'}</div>
                    </td>
                    <td>
                        <span class="badge bg-${statusClass} text-uppercase">${order.status}</span>
                    </td>
                    <td>
                        <div class="small d-flex">
                            <div class="d-flex flex-column align-items-center me-2" style="min-width: 16px;">
                                <i class="bi bi-geo-alt-fill text-danger"></i>
                                <div class="flex-grow-1 border-start my-1" style="border-style: dashed !important; height: 16px;"></div>
                                <i class="bi bi-geo-alt text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">
                                    From:
                                    <span class="fw-normal text-muted">${pickupAddress}</span>
                                </div>
                                <div class="fw-semibold">
                                    To:
                                    <span class="fw-normal text-muted">${deliveryAddress}</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="small d-flex align-items-center gap-1">
                            <i class="bi bi-truck"></i>
                            <span>SURFACE</span>
                        </div>
                    </td>
                    <td>
                        <div class="small">${updatedAt || '-'}</div>
                    </td>
                    <td>
                        <div class="small">Prepaid</div>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            const paginationEl = document.getElementById('ordersPagination');
            const prevDisabled = currentPage <= 1 ? 'disabled' : '';
            const nextDisabled = currentPage >= lastPage ? 'disabled' : '';

            paginationEl.innerHTML = `
                <div>
                    Page <strong>${currentPage}</strong> of <strong>${lastPage}</strong>
                </div>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary" id="ordersPrevBtn" ${prevDisabled}>Previous</button>
                    <button type="button" class="btn btn-outline-secondary" id="ordersNextBtn" ${nextDisabled}>Next</button>
                </div>
            `;

            const prevBtn = document.getElementById('ordersPrevBtn');
            const nextBtn = document.getElementById('ordersNextBtn');

            if (prevBtn && !prevBtn.disabled) {
                prevBtn.addEventListener('click', () => {
                    if (currentPage > 1) {
                        loadOrders(currentPage - 1);
                    }
                });
            }

            if (nextBtn && !nextBtn.disabled) {
                nextBtn.addEventListener('click', () => {
                    if (currentPage < lastPage) {
                        loadOrders(currentPage + 1);
                    }
                });
            }
        } catch (e) {
            showMessage('ordersMessage', 'Error loading orders.', 'danger');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const refreshBtn = document.getElementById('refreshOrdersBtn');
        const downloadSelectedBtn = document.getElementById('downloadSelectedLabelsBtn');
        const selectAllCheckbox = document.getElementById('selectAllOrders');

        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => loadOrders());
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', () => {
                const checkboxes = document.querySelectorAll('.order-select');
                checkboxes.forEach(cb => {
                    if (!cb.disabled) {
                        cb.checked = selectAllCheckbox.checked;
                    }
                });
            });
        }

        if (downloadSelectedBtn) {
            downloadSelectedBtn.addEventListener('click', () => {
                const selected = Array.from(document.querySelectorAll('.order-select:checked'))
                    .map(cb => cb.value);

                if (!selected.length) {
                    alert('Please select at least one order with a waybill to download labels.');
                    return;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = bulkLabelsUrl;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                selected.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'order_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            });
        }

        document.getElementById('ordersFilterForm').addEventListener('submit', function (e) {
            e.preventDefault();
            loadOrders(1);
        });

        document.getElementById('resetFiltersBtn').addEventListener('click', function () {
            document.getElementById('search').value = '';
            document.getElementById('status').value = '';
            document.getElementById('from_date').value = '';
            document.getElementById('to_date').value = '';
            loadOrders(1);
        });

        loadOrders(1);
    });
</script>
@endsection
