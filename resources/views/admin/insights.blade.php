@extends('layouts.app')

@section('content')
@php
    $totalVisits = $insights->sum('visits');
    $topVisit = $insights->max('visits') ?: 1;
@endphp

<div class="container-fluid mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Traffic Insights</h4>
            <p class="text-muted mb-0 small">See which pages your users visit the most.</p>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-indigo-500 text-white d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#4f46e5;">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Total Visits (Top 10)</div>
                        <div class="fs-5 fw-bold">{{ number_format($totalVisits) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-emerald-500 text-white d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#10b981;">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Tracked Pages</div>
                        <div class="fs-5 fw-bold">{{ $insights->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-amber-500 text-white d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#f59e0b;">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Top Page Share</div>
                        <div class="fs-5 fw-bold">
                            @if($totalVisits)
                                {{ round(($topVisit / $totalVisits) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Most Visited Pages</h5>
            <span class="badge bg-light text-muted border small">
                <i class="fa-regular fa-clock me-1"></i>
                Last {{ $insights->count() }} entries
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th style="width: 60%;">Page URL</th>
                            <th style="width: 15%;">Visits</th>
                            <th>Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($insights as $row)
                            @php
                                $share = $totalVisits ? round(($row->visits / $totalVisits) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $row->url }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ number_format($row->visits) }}
                                    </span>
                                </td>
                                <td style="min-width: 180px;">
                                    <div class="d-flex align-items-center gap-2 small">
                                        <div class="flex-grow-1 bg-light rounded-pill" style="height:6px;">
                                            <div class="bg-primary rounded-pill" style="width: {{ $share }}%; height:6px;"></div>
                                        </div>
                                        <span class="text-muted">{{ $share }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">
                                    No page visit data available yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
