@extends('layouts.app')

@section('content')
<div class="container-fluid mb-4">
    <style>
        .dashboard-metrics .card {
            border: none;
            background: linear-gradient(135deg, #f3e8ff, #e0e7ff);
            color: #312e81;
            box-shadow: 0 12px 30px rgba(148, 163, 184, 0.45);
            position: relative;
            overflow: hidden;
        }

        .dashboard-metrics .card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.65), transparent 60%);
            opacity: 0.9;
            pointer-events: none;
        }

        .dashboard-metrics .metric-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            opacity: 0.85;
        }

        .dashboard-metrics .metric-value {
            font-size: 1.6rem;
        }

        .dashboard-metrics .metric-badge {
            font-size: 0.7rem;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            background: rgba(129, 140, 248, 0.9);
            color: #f9fafb;
        }

        .dashboard-section-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 8px 24px rgba(148, 163, 184, 0.4);
            background: linear-gradient(135deg, #f9fafb, #eef2ff);
        }

        .dashboard-section-card .card-header {
            border-bottom: none;
            background: transparent;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Dashboard</h4>
    </div>

    <div class="row g-3 mb-4 dashboard-metrics">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="metric-label text-uppercase">Total Orders</div>
                        <span class="metric-badge">LIVE</span>
                    </div>
                    <div class="metric-value fw-semibold">{{ $totalOrders }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="metric-label text-uppercase">Total Revenue</div>
                        <span class="metric-badge">₹</span>
                    </div>
                    <div class="metric-value fw-semibold">₹{{ number_format($totalRevenue, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="metric-label text-uppercase">Shipments with Waybill</div>
                        <span class="metric-badge">CX</span>
                    </div>
                    <div class="metric-value fw-semibold">{{ $shipmentsTotal }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="metric-label text-uppercase">Shipment Success Rate</div>
                        <span class="metric-badge">SLA</span>
                    </div>
                    <div class="metric-value fw-semibold">
                        @if(!is_null($shipmentSuccessRate))
                            {{ $shipmentSuccessRate }}%
                        @else
                            <span class="text-muted small">No shipments yet</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card dashboard-section-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;">
                            <i class="fa-solid fa-layer-group"></i>
                        </span>
                        <h6 class="mb-0">Orders by Status</h6>
                    </div>
                    <small class="text-muted small">Placed / Draft / Cancelled</small>
                </div>
                <div class="card-body">
                    @php
                        $statuses = ['placed' => 'success', 'draft' => 'secondary', 'cancelled' => 'danger'];
                    @endphp
                    @forelse($statuses as $status => $color)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-{{ $color }} text-uppercase">{{ $status }}</span>
                            <span class="fw-semibold">{{ $statusBreakdown[$status] ?? 0 }}</span>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No orders yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card dashboard-section-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;">
                            <i class="fa-solid fa-star"></i>
                        </span>
                        <h6 class="mb-0">Top Products (by Orders)</h6>
                    </div>
                    <small class="text-muted small">Top 5 bestsellers</small>
                </div>
                <div class="card-body">
                    @if($topProducts->isEmpty())
                        <p class="text-muted small mb-0">No product orders yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                <tr class="small text-uppercase text-muted">
                                    <th>Product</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($topProducts as $product)
                                    <tr>
                                        <td>{{ $product->product_name }}</td>
                                        <td class="text-end">{{ $product->orders_count }}</td>
                                        <td class="text-end">{{ $product->total_quantity }}</td>
                                        <td class="text-end">₹{{ number_format($product->revenue, 2) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-lg-7">
            <div class="card dashboard-section-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Statistics</h6>
                    <small class="text-muted">Revenue & Orders (Last 7 days)</small>
                </div>
                <div class="card-body">
                    @if($dailyRevenue->isEmpty())
                        <p class="text-muted small mb-0">No revenue data yet.</p>
                    @else
                        <canvas id="revenueChart" style="max-height: 260px;"></canvas>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card dashboard-section-card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Recent Orders</h6>
                </div>
                <div class="card-body">
                    @if($recentOrders->isEmpty())
                        <p class="text-muted small mb-0">No orders yet.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($recentOrders as $order)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <div class="fw-semibold small">{{ $order->order_number }}</div>
                                        <div class="text-muted small">{{ $order->customer_name }}</div>
                                        <div class="text-muted small">{{ $order->created_at->format('d M, Y H:i') }}</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $order->status === 'placed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'secondary') }} text-uppercase mb-1">
                                            {{ $order->status }}
                                        </span>
                                        <div class="small fw-semibold">₹{{ number_format($order->total_amount, 2) }}</div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('revenueChart');
        if (!ctx) return;

        const labels = @json($dailyRevenue->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->reverse()->values());
        const revenueData = @json($dailyRevenue->pluck('total')->reverse()->values());

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: revenueData,
                        borderColor: 'rgba(59,130,246,1)',
                        backgroundColor: 'rgba(59,130,246,0.15)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                    },
                    y: {
                        grid: { color: 'rgba(148,163,184,0.3)' },
                        ticks: {
                            callback: function (value) {
                                return '₹' + value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
