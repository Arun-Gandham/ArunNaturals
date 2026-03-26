@extends('layouts.app')

@section('content')
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Orders</h4>
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">Create Order</a>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Orders</h5>
            <button class="btn btn-sm btn-outline-secondary" type="button" id="refreshOrdersBtn">Refresh</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle" id="ordersTable">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Order No.</th>
                        <th>Customer</th>
                        <th>Pincode</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="ordersMessage" class="mt-2"></div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="orderDetailsContent">
                        <!-- Filled by JS -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const apiBase = '{{ url('/api/admin') }}';
    const orderShowBase = '{{ url('/admin/orders') }}';

    function showMessage(elementId, message, type = 'info') {
        const el = document.getElementById(elementId);
        el.innerHTML = message ? `<div class="alert alert-${type} py-1 mb-0">${message}</div>` : '';
    }

    async function loadOrders() {
        const tbody = document.querySelector('#ordersTable tbody');
        tbody.innerHTML = '';
        showMessage('ordersMessage', 'Loading orders...', 'info');

        try {
            const response = await fetch(`${apiBase}/orders`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                showMessage('ordersMessage', data.message || 'Failed to load orders.', 'danger');
                return;
            }

            const orders = data.data.data || data.data;
            if (!orders.length) {
                showMessage('ordersMessage', 'No orders found yet.', 'secondary');
                return;
            }

            showMessage('ordersMessage', '');
            orders.forEach((order, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${order.order_number}</td>
                    <td>${order.customer_name}</td>
                    <td>${order.pincode}</td>
                    <td><span class="badge bg-${order.status === 'placed' ? 'success' : order.status === 'cancelled' ? 'danger' : 'secondary'} text-uppercase">${order.status}</span></td>
                    <td>${order.total_amount}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" data-action="view" data-id="${order.id}" title="View details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            tbody.querySelectorAll('button[data-action="view"]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    window.location.href = `${orderShowBase}/${id}`;
                });
            });
        } catch (e) {
            showMessage('ordersMessage', 'Error loading orders.', 'danger');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('refreshOrdersBtn').addEventListener('click', loadOrders);
        loadOrders();
    });
</script>
@endsection
