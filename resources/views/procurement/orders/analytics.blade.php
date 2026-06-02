@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-chart-line me-2"></i>Procurement Analytics</h4>
            <small class="text-muted">{{ $dateFrom }} — {{ $dateTo }} · {{ $totalOrders }} orders</small>
        </div>
        <a href="{{ route('procurement.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Orders
        </a>
    </div>

    {{-- Date Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('procurement.analytics') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-medium"><i class="fas fa-calendar me-1"></i> From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from', $dateFrom) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-medium"><i class="fas fa-calendar me-1"></i> To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to', $dateTo) }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('procurement.analytics') }}" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-undo me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0 fw-bold text-primary">{{ $totalOrders }}</h3>
                    <small class="text-muted">Total Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #198754 !important;">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0 fw-bold text-success">{{ number_format($totalItems) }}</h3>
                    <small class="text-muted">Total Items</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0 fw-bold text-danger">₱{{ number_format($totalCost, 2) }}</h3>
                    <small class="text-muted">Total Cost</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #6f42c1 !important;">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0 fw-bold text-purple" style="color:#6f42c3">{{ $statusFlow['completed'] }}</h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 1 --}}
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2 text-primary"></i>Daily Orders Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="dailyTrendChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2 text-success"></i>Orders by Status</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusPieChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-building me-2 text-info"></i>Cost by Department</h6>
                </div>
                <div class="card-body">
                    <canvas id="deptChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-box me-2 text-warning"></i>Top Items by Quantity</h6>
                </div>
                <div class="card-body">
                    <canvas id="topItemsChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Cost Variance Table --}}
    @if(count($costVariance) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-balance-scale me-2 text-danger"></i>Cost Variance Analysis</h6>
            <span class="badge bg-warning">{{ count($costVariance) }} discrepancies</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Department</th>
                            <th class="text-end">Ordered Cost</th>
                            <th class="text-end">Actual Cost</th>
                            <th class="text-end">Difference</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($costVariance as $cv)
                        @php
                            $isOverpaid = $cv['difference'] > 0;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('procurement.orders.show', $cv['order']->id) }}" class="text-decoration-none">
                                    {{ $cv['order']->order_number }}
                                </a>
                            </td>
                            <td>{{ $cv['order']->department?->name ?? 'N/A' }}</td>
                            <td class="text-end">₱{{ number_format($cv['ordered_cost'], 2) }}</td>
                            <td class="text-end">₱{{ number_format($cv['actual_cost'], 2) }}</td>
                            <td class="text-end">
                                <span class="fw-bold {{ $isOverpaid ? 'text-danger' : 'text-success' }}">
                                    {{ $isOverpaid ? 'Overpaid' : 'Underpaid' }}
                                    ₱{{ number_format(abs($cv['difference']), 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $cv['order']->status === 'completed' ? 'success' : 'warning' }}">
                                    {{ $cv['order']->statusLabel() }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Supplier Breakdown --}}
    @if(count($supplierBreakdown) > 0 && $isProcurement)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fas fa-truck me-2 text-secondary"></i>Supplier Breakdown</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Supplier</th>
                            <th class="text-center">Orders</th>
                            <th class="text-end">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supplierBreakdown as $sb)
                        <tr>
                            <td>{{ $sb['name'] }}</td>
                            <td class="text-center">{{ $sb['count'] }}</td>
                            <td class="text-end">₱{{ number_format($sb['cost'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Status Flow Summary --}}
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-tasks me-2 text-primary"></i>Order Pipeline</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @php
                            $statusLabels = [
                                'draft' => ['color' => 'secondary', 'icon' => 'fa-pen'],
                                'for_approval' => ['color' => 'info', 'icon' => 'fa-check-circle'],
                                'for_procurement' => ['color' => 'primary', 'icon' => 'fa-shopping-cart'],
                                'ordered' => ['color' => 'warning', 'icon' => 'fa-truck'],
                                'ongoing' => ['color' => 'info', 'icon' => 'fa-cogs'],
                                'preparing' => ['color' => 'secondary', 'icon' => 'fa-box'],
                                'for_delivery' => ['color' => 'primary', 'icon' => 'fa-shipping-fast'],
                                'for_verification' => ['color' => 'warning', 'icon' => 'fa-clipboard-check'],
                                'completed' => ['color' => 'success', 'icon' => 'fa-check-double'],
                                'cancelled' => ['color' => 'danger', 'icon' => 'fa-times'],
                                'partial' => ['color' => 'warning', 'icon' => 'fa-exclamation-triangle'],
                                'delivered' => ['color' => 'info', 'icon' => 'fa-box-open'],
                            ];
                        @endphp
                        @foreach($statusFlow as $status => $count)
                            @if($count > 0)
                            <div class="col-md-3 col-6 mb-2">
                                <div class="d-flex align-items-center p-2 rounded bg-light">
                                    <i class="fas {{ $statusLabels[$status]['icon'] ?? 'fa-circle' }} text-{{ $statusLabels[$status]['color'] ?? 'secondary' }} me-2"></i>
                                    <div>
                                        <small class="d-block lh-1 fw-bold">{{ $count }}</small>
                                        <small class="text-muted" style="font-size:10px">{{ ucwords(str_replace('_', ' ', $status)) }}</small>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";

    // Colors
    const colors = {
        blue: '#0d6efd', green: '#198754', red: '#dc3545',
        yellow: '#ffc107', purple: '#6f42c1', cyan: '#0dcaf0',
        orange: '#fd7e14', pink: '#d63384', teal: '#20c997',
        gray: '#6c757d'
    };
    const allColors = Object.values(colors);

    // 1. Daily Trend Chart
    new Chart(document.getElementById('dailyTrendChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($dailyTrend)->pluck('date')) !!},
            datasets: [{
                label: 'Orders',
                data: {!! json_encode(collect($dailyTrend)->pluck('count')) !!},
                backgroundColor: 'rgba(13, 110, 253, 0.5)',
                borderColor: colors.blue,
                borderWidth: 1,
                order: 2,
                yAxisID: 'y'
            }, {
                label: 'Cost (₱)',
                data: {!! json_encode(collect($dailyTrend)->pluck('cost')) !!},
                type: 'line',
                borderColor: colors.red,
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 3,
                order: 1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 10 } } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, title: { display: true, text: 'Orders' } },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    grid: { display: false },
                    title: { display: true, text: 'Cost (₱)' },
                    ticks: { callback: v => '₱' + v.toLocaleString() }
                }
            }
        }
    });

    // 2. Status Pie Chart
    new Chart(document.getElementById('statusPieChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($statusCounts->keys()->map(fn($s) => ucwords(str_replace('_', ' ', $s)))->values()) !!},
            datasets: [{
                data: {!! json_encode($statusCounts->values()) !!},
                backgroundColor: [
                    colors.secondary, colors.blue, colors.cyan, colors.yellow,
                    colors.teal, colors.orange, colors.green, colors.purple,
                    colors.red, colors.pink
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 8, font: { size: 10 } }
                }
            }
        }
    });

    // 3. Department Cost Chart
    new Chart(document.getElementById('deptChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($deptCosts)->pluck('name')) !!},
            datasets: [{
                label: 'Cost',
                data: {!! json_encode(collect($deptCosts)->pluck('cost')) !!},
                backgroundColor: [
                    'rgba(13, 110, 253, 0.6)',
                    'rgba(25, 135, 84, 0.6)',
                    'rgba(255, 193, 7, 0.6)',
                    'rgba(111, 66, 193, 0.6)',
                    'rgba(220, 53, 69, 0.6)',
                    'rgba(13, 202, 240, 0.6)',
                    'rgba(253, 126, 20, 0.6)',
                ],
                borderColor: allColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => '₱' + v.toLocaleString() } }
            }
        }
    });

    // 4. Top Items Chart
    new Chart(document.getElementById('topItemsChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($allItems->pluck('name')) !!},
            datasets: [{
                label: 'Quantity',
                data: {!! json_encode($allItems->pluck('qty')) !!},
                backgroundColor: 'rgba(255, 193, 7, 0.6)',
                borderColor: colors.yellow,
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 50 } }
            }
        }
    });
});
</script>
@endpush
