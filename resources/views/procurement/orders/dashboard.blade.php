@extends('layouts.app')

@section('title', 'Procurement Analytics Dashboard')

@section('content')
<div class="container-fluid px-4 py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="fas fa-chart-pie me-2 text-primary"></i> Procurement Analytics
            </h4>
            <small class="text-muted">Performance overview and cost analysis</small>
        </div>
        <div>
            <a href="{{ route('procurement.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Orders
            </a>
        </div>
    </div>

    {{-- Date Filter Form --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('procurement.dashboard') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-medium"><i class="fas fa-calendar me-1"></i> From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-medium"><i class="fas fa-calendar me-1"></i> To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
                </div>
                <div class="col-md-2 d-grid">
                    <a href="{{ route('procurement.dashboard') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
                <div class="col-md-2">
                    @if($isAdmin || $isProcurement)
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar-alt me-1"></i> Quick Select
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('procurement.dashboard', ['date_from' => now()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}">Today</a></li>
                            <li><a class="dropdown-item" href="{{ route('procurement.dashboard', ['date_from' => now()->subDays(7)->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="{{ route('procurement.dashboard', ['date_from' => now()->subMonth()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="{{ route('procurement.dashboard', ['date_from' => '2026-05-16', 'date_to' => '2026-05-26']) }}">Sample Data (May 16-26)</a></li>
                        </ul>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body text-center py-2">
                    <h3 class="mb-0 fw-bold text-primary">{{ $totalOrders }}</h3>
                    <small class="text-muted">Total Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #198754 !important;">
                <div class="card-body text-center py-2">
                    <h3 class="mb-0 fw-bold text-success">{{ $totalItemsOrdered }}</h3>
                    <small class="text-muted">Total Items Ordered</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body text-center py-2">
                    <h3 class="mb-0 fw-bold text-danger">₱{{ number_format($totalCost, 2) }}</h3>
                    <small class="text-muted">Total Cost (Ordered)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #6f42c1 !important;">
                <div class="card-body text-center py-2">
                    <h3 class="mb-0 fw-bold" style="color:#6f42c3">
                        ₱{{ number_format($totalOrderedCost, 2) }}
                    </h3>
                    <small class="text-muted">Completed Cost</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Extra Summary Row: Overpayment & Under-delivery --}}
    @if($totalOrderedCost > 0)
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center py-2">
                    <small class="text-muted">Ordered vs Actual</small>
                    <div class="d-flex justify-content-center gap-3 mt-1">
                        <span class="fw-bold text-primary">₱{{ number_format($totalOrderedCost, 2) }}</span>
                        <span class="text-muted">→</span>
                        <span class="fw-bold text-success">₱{{ number_format($totalActualCost, 2) }}</span>
                    </div>
                    @php $savings = $totalOrderedCost - $totalActualCost; @endphp
                    <span class="badge bg-{{ $savings >= 0 ? 'success' : 'danger' }}">
                        {{ $savings >= 0 ? 'Savings' : 'Loss' }}: ₱{{ number_format(abs($savings), 2) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center py-2">
                    <small class="text-muted">Overpayment Total</small>
                    <h4 class="mb-0 fw-bold text-danger">₱{{ number_format($overpaymentTotal, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center py-2">
                    <small class="text-muted">Under-Delivery Count</small>
                    <h4 class="mb-0 fw-bold text-warning">{{ $underDeliveryCount }}</h4>
                    <small class="text-muted">line items</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Charts Row 1: Line + Bar --}}
    <div class="row g-3 mb-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-chart-line me-1 text-primary"></i> Daily Orders Created</h6>
                </div>
                <div class="card-body">
                    <canvas id="dailyTrendChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-1 text-success"></i> Orders by Status</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusPieChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 2: Dept Bar + Top Items Bar --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-building me-1 text-info"></i> Orders by Department (Cost)</h6>
                </div>
                <div class="card-body">
                    <canvas id="deptBarChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-box me-1 text-warning"></i> Top 10 Items by Quantity</h6>
                </div>
                <div class="card-body">
                    <canvas id="topItemsChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Analysis Tables --}}
    @if(count($costVarianceItems) > 0)
    <div class="card border-0 shadow-sm mb-4 border-start border-4 border-purple" style="border-left-color: #6f42c1 !important;">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold" style="color:#6f42c3">
                <i class="fas fa-clipboard-check me-1"></i> Cost Variance Analysis
                <span class="badge bg-purple ms-2" style="background:#6f42c1">{{ count($costVarianceItems) }}</span>
            </h6>
            <small class="text-muted">Ordered vs Actual Cost - Discrepancies</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            @if($isAdmin || $isProcurement)
                            <th>Department</th>
                            <th>Supplier</th>
                            @endif
                            <th>Item</th>
                            <th class="text-center">Ordered</th>
                            <th class="text-center">Verified</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Ordered Cost</th>
                            <th class="text-end">Actual Cost</th>
                            <th class="text-end">Difference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalDiff = 0; $totalOrdCost = 0; $totalActCost = 0; @endphp
                        @foreach($costVarianceItems as $cv)
                        @php
                            $totalDiff += $cv['difference'];
                            $totalOrdCost += $cv['ordered_cost'];
                            $totalActCost += $cv['actual_cost'];
                        @endphp
                        <tr>
                            <td><a href="{{ route('procurement.orders.show', App\Models\ProcurementOrder::where('order_number', $cv['order_number'])->first()->id ?? 0) }}" class="text-decoration-none">{{ $cv['order_number'] }}</a></td>
                            @if($isAdmin || $isProcurement)
                            <td>{{ $cv['department_name'] }}</td>
                            <td>
                                @if($isAdmin || $isProcurement)
                                {{ $cv['supplier_name'] }}
                                @else
                                Supplier
                                @endif
                            </td>
                            @endif
                            <td>{{ $cv['item_name'] }}</td>
                            <td class="text-center">{{ $cv['ordered_qty'] }}</td>
                            <td class="text-center">{{ $cv['verified_qty'] }}</td>
                            <td class="text-end">₱{{ number_format($cv['unit_price'], 2) }}</td>
                            <td class="text-end">₱{{ number_format($cv['ordered_cost'], 2) }}</td>
                            <td class="text-end">₱{{ number_format($cv['actual_cost'], 2) }}</td>
                            <td class="text-end fw-bold {{ $cv['difference'] > 0 ? 'text-danger' : ($cv['difference'] < 0 ? 'text-success' : '') }}">
                                @if($cv['difference'] > 0)
                                    ⬆️ ₱{{ number_format($cv['difference'], 2) }}
                                @elseif($cv['difference'] < 0)
                                    ⬇️ ₱{{ number_format(abs($cv['difference']), 2) }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="{{ $isAdmin || $isProcurement ? 6 : 4 }}" class="text-end fw-bold">Total:</td>
                            <td class="text-end fw-bold">₱{{ number_format($totalOrdCost, 2) }}</td>
                            <td class="text-end fw-bold">₱{{ number_format($totalActCost, 2) }}</td>
                            <td class="text-end fw-bold {{ $totalDiff > 0 ? 'text-danger' : ($totalDiff < 0 ? 'text-success' : '') }}">
                                ₱{{ number_format(abs($totalDiff), 2) }}
                                <br><small>{{ $totalDiff > 0 ? '⬆️ Overpayment' : ($totalDiff < 0 ? '⬇️ Underpayment' : '✓ Exact') }}</small>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-2 text-muted small">
            <i class="fas fa-info-circle me-1"></i>
            Difference = Ordered Cost − Actual Cost. Positive = Overpayment, Negative = Underpayment.
        </div>
    </div>
    @endif

    {{-- Procurement Activity --}}
    @if(count($activityByDay) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-day me-1 text-primary"></i> Procurement Activity</h6>
            <small class="text-muted">{{ count($activityByDay) }} day(s)</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th class="text-center">Orders</th>
                            <th class="text-center">Items Ordered</th>
                            <th class="text-end">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activityByDay as $day)
                        <tr>
                            <td><strong>{{ \Carbon\Carbon::parse($day['date'])->format('M d, Y') }}</strong></td>
                            <td class="text-center">{{ $day['order_count'] }}</td>
                            <td class="text-center">{{ $day['item_count'] }}</td>
                            <td class="text-end fw-bold">₱{{ number_format($day['total_cost'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Color palette
    const colors = [
        '#0d6efd', '#198754', '#dc3545', '#ffc107', '#6f42c1',
        '#0dcaf0', '#fd7e14', '#20c997', '#e83e8c', '#6610f2',
        '#17a2b8', '#6c757d'
    ];

    // 1. Daily Trend Line Chart
    const dailyCtx = document.getElementById('dailyTrendChart');
    if (dailyCtx) {
        const dailyLabels = {!! json_encode($dailyTrendData->pluck('date')) !!};
        const dailyCounts = {!! json_encode($dailyTrendData->pluck('order_count')) !!};
        const dailyCosts = {!! json_encode($dailyTrendData->pluck('cost')) !!};

        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [
                    {
                        label: 'Orders',
                        data: dailyCounts,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Cost (₱)',
                        data: dailyCosts,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, title: { display: true, text: 'Orders' } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Cost (₱)' } }
                }
            }
        });
    }

    // 2. Status Pie Chart
    const statusCtx = document.getElementById('statusPieChart');
    if (statusCtx) {
        const statusLabels = {!! json_encode($ordersByStatus->pluck('status')->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))) !!};
        const statusData = {!! json_encode($ordersByStatus->pluck('total')) !!};

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: colors.slice(0, statusLabels.length),
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 }, padding: 8 } }
                }
            }
        });
    }

    // 3. Department Bar Chart
    const deptCtx = document.getElementById('deptBarChart');
    if (deptCtx) {
        const deptLabels = {!! json_encode($ordersByDept->pluck('department_name')) !!};
        const deptCosts = {!! json_encode($ordersByDept->pluck('cost')) !!};
        const deptCounts = {!! json_encode($ordersByDept->pluck('count')) !!};

        new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [
                    {
                        label: 'Cost (₱)',
                        data: deptCosts,
                        backgroundColor: 'rgba(13, 110, 253, 0.7)',
                        borderColor: '#0d6efd',
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Orders',
                        data: deptCounts,
                        backgroundColor: 'rgba(25, 135, 84, 0.7)',
                        borderColor: '#198754',
                        borderWidth: 1,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Cost (₱)' } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Orders' }, ticks: { stepSize: 1, precision: 0 } }
                }
            }
        });
    }

    // 4. Top Items Bar Chart
    const topCtx = document.getElementById('topItemsChart');
    if (topCtx) {
        const topLabels = {!! json_encode($topItemsByQty->pluck('item_name')) !!};
        const topData = {!! json_encode($topItemsByQty->pluck('total_qty')) !!};

        new Chart(topCtx, {
            type: 'bar',
            data: {
                labels: topLabels,
                datasets: [{
                    label: 'Quantity Ordered',
                    data: topData,
                    backgroundColor: topData.map((_, i) => colors[i % colors.length]),
                    borderWidth: 1,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
                }
            }
        });
    }
});
</script>
@endpush
