@extends('layouts.app')

@section('page-title', 'Sales Dashboard | Sales Team')

@section('content')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .page-header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
    .page-header h1 i { color: #3b82f6; }
    .page-subtitle { color: #64748b; margin: 0; font-size: 0.9rem; }
    .action-btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration: none; border: 1px solid #e2e8f0; background: #fff; color: #475569; transition: all .15s ease; }
    .action-btn:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    .action-btn.primary { background: #3b82f6; border-color: #3b82f6; color: #fff; }
    .action-btn.primary:hover { background: #2563eb; border-color: #2563eb; }
    .filter-card { background: #fff; border-radius: 14px; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); margin-bottom: 1.25rem; }
    .filter-card label { font-size: .75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .03em; margin-bottom: .25rem; }
    .filter-card select, .filter-card input { font-size: .85rem; }
    .kpi-card { background: #fff; border-radius: 14px; padding: 1.1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); height: 100%; }
    .kpi-icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; }
    .kpi-label { font-size: .75rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .03em; }
    .kpi-value { font-size: 1.45rem; font-weight: 800; color: #1e293b; line-height: 1.2; }
    .kpi-sub { font-size: .78rem; color: #64748b; }
    .chart-card { background: #fff; border-radius: 14px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); margin-bottom: 1.25rem; }
    .chart-card h6 { font-weight: 700; color: #1e293b; font-size: .95rem; }
    .product-table { font-size: .85rem; }
    .product-table thead th { font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; color: #94a3b8; font-weight: 700; border-bottom-width: 1px; }
    .product-table td, .product-table th { vertical-align: middle; padding: .6rem .75rem; }
    .rank-badge { width: 26px; height: 26px; border-radius: 50%; background: #f1f5f9; color: #475569; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700; }
    .rank-badge.top { background: #3b82f6; color: #fff; }
    .rank-badge.top2 { background: #8b5cf6; color: #fff; }
    .rank-badge.top3 { background: #f59e0b; color: #fff; }
    .opp-chip { display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 600; padding: .2rem .55rem; border-radius: 999px; }
    .opp-high { background: #ecfdf5; color: #059669; }
    .opp-med { background: #eff6ff; color: #2563eb; }
    .opp-low { background: #fef2f2; color: #dc2626; }
    .progress { height: 6px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
    .progress-bar { border-radius: 999px; }
    .badge-soft { font-size: .72rem; font-weight: 600; padding: .25rem .6rem; border-radius: 999px; }
</style>

<div class="page-header">
    <div>
        <h1><i class="fas fa-chart-line"></i> Sales Dashboard</h1>
        <p class="page-subtitle">Your sales performance — track, compare, and find products to push harder</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sales.prototype') }}" class="action-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <a href="{{ route('sales.prototype.create') }}" class="action-btn primary">
            <i class="fas fa-plus"></i> Add New Sale
        </a>
    </div>
</div>

<!-- FILTERS -->
<form method="GET" action="{{ route('sales.prototype.dashboard') }}" class="filter-card">
    <div class="row g-3 align-items-end">
        <div class="col-md-2">
            <label>Date From</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label>Date To</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label>Payment Status</label>
            <select name="payment_status" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach(['pending' => 'Pending', 'partial' => 'Partial', 'paid' => 'Paid', 'overpaid' => 'Overpaid'] as $val => $label)
                <option value="{{ $val }}" {{ ($filters['payment_status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Order Status</label>
            <select name="kanban_status" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach($statuses as $st)
                <option value="{{ $st }}" {{ ($filters['kanban_status'] ?? '') === $st ? 'selected' : '' }}>{{ $statusLabels[$st] ?? ucfirst($st) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Shop</label>
            <select name="department" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ ($filters['department'] ?? '') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Product</label>
            <input type="text" name="product" class="form-control form-control-sm" placeholder="e.g. Jersey" value="{{ $filters['product'] ?? '' }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="fas fa-filter me-1"></i> Apply</button>
            <a href="{{ route('sales.prototype.dashboard') }}" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="fas fa-undo"></i></a>
        </div>
    </div>
</form>

<!-- KPI CARDS -->
<div class="row g-3 mb-2">
    <div class="col-6 col-lg-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background:#eff6ff;color:#2563eb;"><i class="fas fa-file-invoice-dollar"></i></div>
            <div>
                <div class="kpi-label">Total Revenue</div>
                <div class="kpi-value">₱{{ number_format($totalRevenue, 2) }}</div>
                <div class="kpi-sub">{{ $totalOrders }} order{{ $totalOrders !== 1 ? 's' : '' }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background:#ecfdf5;color:#059669;"><i class="fas fa-hand-holding-usd"></i></div>
            <div>
                <div class="kpi-label">Collected</div>
                <div class="kpi-value">₱{{ number_format($totalCollected, 2) }}</div>
                <div class="kpi-sub">{{ $totalRevenue > 0 ? round(($totalCollected / $totalRevenue) * 100) : 0 }}% collected</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background:#fef2f2;color:#dc2626;"><i class="fas fa-exclamation-circle"></i></div>
            <div>
                <div class="kpi-label">Outstanding Balance</div>
                <div class="kpi-value" style="color:#dc2626;">₱{{ number_format($totalBalance, 2) }}</div>
                <div class="kpi-sub">Follow up to collect</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background:#fdf4ff;color:#9333ea;"><i class="fas fa-tshirt"></i></div>
            <div>
                <div class="kpi-label">Pieces Sold</div>
                <div class="kpi-value">{{ number_format($totalPieces) }}</div>
                <div class="kpi-sub">{{ $totalOrders > 0 ? number_format($totalPieces / $totalOrders, 1) : 0 }} pcs/order avg</div>
            </div>
        </div>
    </div>
</div>

<!-- CHARTS -->
<div class="row g-3 mt-1">
    <div class="col-lg-8">
        <div class="chart-card">
            <h6><i class="fas fa-chart-area me-2 text-primary"></i>Revenue & Orders Trend</h6>
            @if(count($trendLabels) >= 2)
            <div style="height:300px; position:relative;"><canvas id="trendChart"></canvas></div>
            @else
            <div class="text-center py-4">
                <p class="mb-1" style="font-size:1.6rem;">📈</p>
                <p class="text-muted mb-0">Kailangan ng at least 2 days na may sales para makita ang trend.</p>
                <small class="text-muted">Mag-add pa ng sales at babalik dito para makita ang pagtaas!</small>
            </div>
            @endif
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card">
            <h6><i class="fas fa-chart-pie me-2 text-success"></i>Revenue by Payment Status</h6>
            @if(count($paymentLabels) > 0)
            <div style="height:280px; position:relative;"><canvas id="paymentChart"></canvas></div>
            @else
            <p class="text-muted text-center py-4 mb-0">No data yet</p>
            @endif
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-4">
        <div class="chart-card">
            <h6><i class="fas fa-store me-2" style="color:#0ea5e9;"></i>Revenue by Shop</h6>
            @if(count($shopLabels) > 0)
            <div style="height:280px; position:relative;"><canvas id="shopChart"></canvas></div>
            @else
            <p class="text-muted text-center py-4 mb-0">No shop data yet</p>
            @endif
        </div>
    </div>
    <div class="col-lg-8">
        <div class="chart-card">
            <h6><i class="fas fa-chart-bar me-2" style="color:#f97316;"></i>Orders & Pieces by Shop</h6>
            @if(count($shopLabels) > 0)
            <div style="height:280px; position:relative;"><canvas id="shopOrdersChart"></canvas></div>
            @else
            <p class="text-muted text-center py-4 mb-0">No shop data yet</p>
            @endif
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-5">
        <div class="chart-card">
            <h6><i class="fas fa-trophy me-2" style="color:#f59e0b;"></i>Top 10 Products by Revenue</h6>
            @if(count($productLabels) > 0)
            <div style="height:320px; position:relative;"><canvas id="productChart"></canvas></div>
            @else
            <p class="text-muted text-center py-4 mb-0">No product data yet</p>
            @endif
        </div>
    </div>
    <div class="col-lg-7">
        <div class="chart-card">
            <h6><i class="fas fa-bullseye me-2" style="color:#dc2626;"></i>Product Performance — Saan ka pa pwede lumakas</h6>
            @if(count($productMap) > 0)
            @php
                $maxRevenue = max(array_column($productMap, 'revenue'));
                $rank = 0;
                $totalRevenueAll = array_sum(array_column($productMap, 'revenue'));
            @endphp
            <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                <table class="table table-hover product-table mb-0">
                    <thead class="sticky-top bg-white">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th class="text-center">Pcs</th>
                            <th class="text-center">Orders</th>
                            <th class="text-end">Revenue</th>
                            <th style="width:120px;">Share</th>
                            <th>Opportunity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productMap as $name => $p)
                        @php
                            $rank++;
                            $share = $totalRevenueAll > 0 ? ($p['revenue'] / $totalRevenueAll) * 100 : 0;
                            $avgPerPiece = $p['qty'] > 0 ? $p['revenue'] / $p['qty'] : 0;
                            // Opportunity heuristic: high revenue share = strong; low share = room to grow
                            if ($share >= 20) { $opp = 'Top performer — keep it up!'; $oppCls = 'opp-low'; $icon = '🔥'; }
                            elseif ($share >= 8) { $opp = 'Steady seller — pwede pa lumakas'; $oppCls = 'opp-med'; $icon = '📈'; }
                            else { $opp = 'Underrated — try pushing this'; $oppCls = 'opp-high'; $icon = '💡'; }
                            $badgeCls = $rank === 1 ? 'top' : ($rank === 2 ? 'top2' : ($rank === 3 ? 'top3' : ''));
                        @endphp
                        <tr>
                            <td><span class="rank-badge {{ $badgeCls }}">{{ $rank }}</span></td>
                            <td class="fw-semibold">{{ $name }}@if(!empty($p['projects']))<div style="font-size:.7rem;color:#94a3b8;font-weight:400;">📁 {{ implode(' • ', array_slice($p['projects'], 0, 2)) }}</div>@endif</td>
                            <td class="text-center">{{ number_format($p['qty']) }}</td>
                            <td class="text-center">{{ $p['orders'] }}</td>
                            <td class="text-end fw-semibold">₱{{ number_format($p['revenue'], 2) }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1">
                                        <div class="progress-bar" style="width:{{ min($share, 100) }}%;background:{{ $rank === 1 ? '#3b82f6' : ($rank === 2 ? '#8b5cf6' : ($rank === 3 ? '#f59e0b' : '#cbd5e1')) }};"></div>
                                    </div>
                                    <span style="font-size:.72rem;color:#64748b;min-width:38px;text-align:right;">{{ number_format($share, 1) }}%</span>
                                </div>
                            </td>
                            <td><span class="opp-chip {{ $oppCls }}">{{ $icon }} {{ $opp }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted text-center py-4 mb-0">No product data for this filter</p>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Trend chart
    @if(count($trendLabels) >= 2)
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx && window.Chart) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($trendLabels) !!},
                datasets: [
                    {
                        label: 'Revenue (₱)',
                        data: {!! json_encode($trendRevenue) !!},
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,.12)',
                        fill: true,
                        tension: .35,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Orders',
                        data: {!! json_encode($trendOrders) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,.08)',
                        fill: false,
                        tension: .35,
                        yAxisID: 'y1',
                        borderDash: [5,5]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top', labels: { boxWidth: 14, font: { size: 11 } } } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => '₱' + Number(v).toLocaleString(), font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                    y1: { beginAtZero: true, position: 'right', ticks: { precision: 0, font: { size: 10 } }, grid: { display: false } },
                    x: { ticks: { maxTicksLimit: 10, font: { size: 10 } }, grid: { display: false } }
                }
            }
        });
    }
    @endif

    // Payment status pie
    @if(count($paymentLabels) > 0)
    const payCtx = document.getElementById('paymentChart');
    if (payCtx && window.Chart) {
        new Chart(payCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_map('ucfirst', $paymentLabels)) !!},
                datasets: [{
                    data: {!! json_encode($paymentValues) !!},
                    backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#8b5cf6', '#ef4444', '#64748b'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ₱' + Number(ctx.raw).toLocaleString(undefined, {minimumFractionDigits: 2}) } }
                }
            }
        });
    }
    @endif

    // Shop revenue doughnut
    @if(count($shopLabels) > 0)
    const shopCtx = document.getElementById('shopChart');
    if (shopCtx && window.Chart) {
        new Chart(shopCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($shopLabels) !!},
                datasets: [{
                    data: {!! json_encode($shopRevenue) !!},
                    backgroundColor: ['#0ea5e9', '#8b5cf6', '#f59e0b', '#10b981', '#ef4444', '#ec4899', '#84cc16', '#06b6d4', '#f97316', '#6366f1'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ₱' + Number(ctx.raw).toLocaleString(undefined, {minimumFractionDigits: 2}) } }
                }
            }
        });
    }

    // Shop orders + pieces bar
    const shopOrdCtx = document.getElementById('shopOrdersChart');
    if (shopOrdCtx && window.Chart) {
        new Chart(shopOrdCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($shopLabels) !!},
                datasets: [
                    { label: 'Orders', data: {!! json_encode($shopOrders) !!}, backgroundColor: '#0ea5e9', borderRadius: 6, yAxisID: 'y' },
                    { label: 'Pieces', data: {!! json_encode($shopPieces) !!}, backgroundColor: '#f97316', borderRadius: 6, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 14, font: { size: 11 } } } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                    y1: { beginAtZero: true, position: 'right', ticks: { precision: 0, font: { size: 10 } }, grid: { display: false } },
                    x: { ticks: { font: { size: 10 } }, grid: { display: false } }
                }
            }
        });
    }
    @endif

    // Top products bar
    @if(count($productLabels) > 0)
    const prodCtx = document.getElementById('productChart');
    if (prodCtx && window.Chart) {
        new Chart(prodCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($topProducts)) !!},
                datasets: [{
                    label: 'Revenue (₱)',
                    data: {!! json_encode($productRevenue) !!},
                    backgroundColor: ['#3b82f6', '#8b5cf6', '#f59e0b', '#10b981', '#ef4444', '#06b6d4', '#ec4899', '#84cc16', '#f97316', '#6366f1'],
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ₱' + Number(ctx.raw).toLocaleString(undefined, {minimumFractionDigits: 2}) } } },
                scales: {
                    x: { beginAtZero: true, ticks: { callback: v => '₱' + Number(v).toLocaleString(), font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                    y: { ticks: { font: { size: 10 } }, grid: { display: false } }
                }
            }
        });
    }
    @endif
});
</script>
@endsection
