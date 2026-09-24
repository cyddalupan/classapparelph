@extends('layouts.app')

@section('title', 'Production Dashboard')
@section('page-title', 'Production Dashboard')

@section('content')
<style>
    .prod-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: .75rem; }
    .prod-header h2 { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: .5rem; }
    .prod-header h2 i { color: #6f42c1; }
    .prod-header .actions { display: flex; gap: .5rem; flex-wrap: wrap; }
    .action-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .5rem .95rem; border-radius: 10px; font-size: .82rem; font-weight: 600; text-decoration: none; border: 1px solid #e2e8f0; background: #fff; color: #475569; transition: all .15s ease; }
    .action-btn:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    .action-btn.primary { background: #6f42c1; border-color: #6f42c1; color: #fff; }
    .action-btn.primary:hover { background: #5a32a3; border-color: #5a32a3; }
    .action-btn.danger { color: #dc2626; border-color: #fecaca; }
    .action-btn.danger:hover { background: #fef2f2; }
    .action-btn.warn { color: #b45309; border-color: #fde68a; }
    .action-btn.warn:hover { background: #fffbeb; }
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .9rem; margin-bottom: 1.25rem; }
    .kpi-card { background: #fff; border-radius: 14px; padding: 1rem 1.1rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); border: 1px solid #f1f5f9; }
    .kpi-icon { width: 40px; height: 40px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: 1rem; margin-bottom: .55rem; }
    .kpi-label { font-size: .72rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; }
    .kpi-value { font-size: 1.4rem; font-weight: 800; color: #1e293b; line-height: 1.15; }
    .kpi-value a { color: inherit; text-decoration: none; }
    .kpi-value a:hover { text-decoration: underline; }
    .kpi-sub { font-size: .75rem; color: #64748b; }
    .card { background: #fff; border-radius: 14px; padding: 1.15rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); border: 1px solid #f1f5f9; margin-bottom: 1.15rem; }
    .card-title { font-size: .95rem; font-weight: 700; color: #1e293b; margin-bottom: .9rem; display: flex; align-items: center; gap: .45rem; }
    .card-title i { color: #6f42c1; }
    .kanban-bar { display: flex; height: 34px; border-radius: 9px; overflow: hidden; margin-bottom: .9rem; }
    .kanban-seg { display: flex; align-items: center; justify-content: center; font-size: .7rem; font-weight: 700; color: #fff; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding: 0 .3rem; }
    .kanban-seg.empty { background: #f1f5f9 !important; color: #94a3b8; }
    .kanban-legend { display: flex; flex-wrap: wrap; gap: .5rem 1rem; }
    .legend-item { display: flex; align-items: center; gap: .4rem; font-size: .78rem; color: #475569; }
    .legend-dot { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }
    .stage-chip { display: inline-flex; align-items: center; gap: .35rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 999px; padding: .28rem .7rem; font-size: .78rem; font-weight: 600; color: #334155; margin: 0 .4rem .4rem 0; }
    .stage-chip .n { background: #6f42c1; color: #fff; border-radius: 999px; font-size: .68rem; font-weight: 700; padding: .08rem .42rem; }
    .due-row { display: flex; align-items: center; gap: .75rem; padding: .55rem 0; border-bottom: 1px solid #f1f5f9; font-size: .83rem; }
    .due-row:last-child { border-bottom: 0; }
    .due-badge { font-size: .7rem; font-weight: 700; padding: .22rem .55rem; border-radius: 999px; white-space: nowrap; }
    .due-now { background: #dc2626; color: #fff; }
    .due-today { background: #f59e0b; color: #fff; }
    .due-soon { background: #fef3c7; color: #b45309; }
    .due-later { background: #e2e8f0; color: #475569; }
    .sale-link { font-weight: 700; color: #6f42c1; text-decoration: none; }
    .sale-link:hover { text-decoration: underline; }
    .mini-table { width: 100%; font-size: .82rem; }
    .mini-table thead th { font-size: .7rem; text-transform: uppercase; letter-spacing: .03em; color: #94a3b8; font-weight: 700; border-bottom: 1px solid #e2e8f0; padding: .45rem .5rem; }
    .mini-table tbody td { padding: .55rem .5rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .mini-table tbody tr:last-child td { border-bottom: 0; }
    .badge-soft { font-size: .7rem; font-weight: 700; padding: .22rem .55rem; border-radius: 999px; white-space: nowrap; }
    .b-new { background: #e2e8f0; color: #475569; }
    .b-design { background: #ede9fe; color: #6f42c1; }
    .b-prod { background: #dbeafe; color: #2563eb; }
    .b-qc { background: #fef3c7; color: #b45309; }
    .b-ready { background: #d1fae5; color: #059669; }
    .b-delivered { background: #ccfbf1; color: #0d9488; }
    .b-completed { background: #dcfce7; color: #15803d; }
    .b-sample { background: #fee2e2; color: #dc2626; }
    .b-prio { background: #fef08a; color: #854d0e; }
    .b-delayed { background: #fee2e2; color: #dc2626; }
    .empty-mini { text-align: center; color: #94a3b8; padding: 1.5rem 0; font-size: .85rem; }
    .count-pill { display: inline-flex; align-items: center; justify-content: center; min-width: 22px; height: 22px; border-radius: 999px; background: #6f42c1; color: #fff; font-size: .72rem; font-weight: 700; padding: 0 .45rem; margin-left: .35rem; }
    .count-pill.danger { background: #dc2626; }
    .count-pill.orange { background: #f59e0b; }
    .count-pill.green { background: #059669; }
    .filter-card { background: #fff; border-radius: 14px; padding: .9rem 1.1rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); border: 1px solid #f1f5f9; margin-bottom: 1.15rem; display: flex; flex-wrap: wrap; gap: .7rem; align-items: flex-end; }
    .filter-card label { font-size: .7rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .03em; margin-bottom: .2rem; display: block; }
    .filter-card .form-control, .filter-card .form-select { font-size: .83rem; border-radius: 9px; }
    .chart-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.15rem; margin-bottom: 1.15rem; }
    @media (max-width: 991px) { .chart-grid { grid-template-columns: 1fr; } }
    .chart-card { background: #fff; border-radius: 14px; padding: 1.15rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); border: 1px solid #f1f5f9; }
    .chart-card .card-title { margin-bottom: .6rem; }
    .chart-wrap { position: relative; height: 260px; }

    /* ---- PRODUCTION DASHBOARD: MOBILE view (additive — desktop/tablet >=768px untouched) ---- */
    @media (max-width: 767.98px) {
        /* Reclaim stacked page paddings so cards use the full phone width */
        .content-area:has(.prod-header),
        .page-content:has(> .prod-header) { padding-left: 8px; padding-right: 8px; }

        /* Header: title on top, action buttons 2-up full width */
        .prod-header { align-items: flex-start; }
        .prod-header h2 { font-size: 1.1rem; }
        .prod-header .actions { width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
        .prod-header .actions .action-btn { width: 100%; justify-content: center; }

        /* Filter bar: stack every field full width */
        .filter-card { flex-direction: column; align-items: stretch; gap: .6rem; }
        .filter-card > div, .filter-card > .d-flex { flex: 1 1 100% !important; width: 100%; min-width: 0 !important; }
        .filter-card .form-control, .filter-card .form-select { width: 100%; }
        .filter-card .d-flex .action-btn { flex: 1 1 0; justify-content: center; }

        /* KPI cards: tighter, 2-up */
        .kpi-grid { grid-template-columns: 1fr 1fr; gap: .7rem; }
        .kpi-card { padding: .8rem .85rem; }
        .kpi-value { font-size: 1.2rem; }

        /* Recent Orders table -> stacked cards (labels from data-label) */
        .mini-table { width: 100%; min-width: 0; display: block; }
        .mini-table thead { display: none; }
        .mini-table tbody { display: block; width: 100%; }
        .mini-table tbody tr { display: block; border: 1px solid #e2e8f0; border-radius: 10px; padding: .55rem .65rem; margin-bottom: .55rem; }
        .mini-table tbody tr:last-child { margin-bottom: 0; }
        .mini-table tbody td { display: block; border: 0; padding: .12rem 0; text-align: left !important; }
        .mini-table tbody td::before { content: attr(data-label); display: inline-block; min-width: 74px; font-size: .68rem; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; color: #94a3b8; }
        .mini-table tbody td:first-child { font-size: .95rem; margin-bottom: .2rem; }

        /* Analytics tables keep their horizontal scroll, just constrained */
        .chart-card .table-responsive { max-width: 100%; }
        .chart-wrap { height: 220px; }
    }
</style>

<div class="prod-header">
    <h2><i class="fas fa-tachometer-alt"></i> Production Overview</h2>
    <div class="actions">
        <a href="{{ route('sales.prototype.list') }}" class="action-btn"><i class="fas fa-list"></i> Manager List</a>
        <a href="{{ route('sales.prototype.kanban', $isProdManager ? 'class' : null) }}" class="action-btn"><i class="fas fa-columns"></i> Kanban</a>
        <a href="{{ route('sales.prototype.calendar') }}" class="action-btn"><i class="fas fa-calendar-alt"></i> Calendar</a>
        <a href="{{ route('sales.prototype.delays') }}" class="action-btn danger"><i class="fas fa-exclamation-triangle"></i> Delay List</a>
        <a href="{{ route('sales.prototype.backjobs') }}" class="action-btn warn"><i class="fas fa-tools"></i> Backjobs</a>
    </div>
</div>

<!-- FILTER BAR -->
<form method="GET" action="{{ route('production.tracking') }}" class="filter-card">
    <div style="min-width:170px;">
        <label>Date From</label>
        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}">
    </div>
    <div style="min-width:170px;">
        <label>Date To</label>
        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}">
    </div>
    <div style="min-width:180px;">
        <label>Kanban Status</label>
        <select name="kanban" class="form-select form-select-sm">
            <option value="">All Statuses</option>
            @foreach($kanbanLabels as $key => $label)
                <option value="{{ $key }}" {{ ($filters['kanban'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div style="min-width:200px;flex:1;">
        <label>Search</label>
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Order #, customer, agent..." value="{{ $filters['search'] ?? '' }}">
    </div>
    <div>
        <label>Archived</label>
        <div class="d-flex gap-1">
            <button type="submit" name="archived" value="all" class="action-btn {{ ($filters['archived'] ?? 'all') === 'all' ? 'primary' : '' }}" title="Kasama ang archived (default)"><i class="fas fa-layer-group"></i> All</button>
            <button type="submit" name="archived" value="active" class="action-btn {{ ($filters['archived'] ?? '') === 'active' ? 'primary' : '' }}" title="Active lang — walang archived"><i class="fas fa-circle-check"></i> Active</button>
            <button type="submit" name="archived" value="archived" class="action-btn {{ ($filters['archived'] ?? '') === 'archived' ? 'primary' : '' }}" title="Archived lang"><i class="fas fa-box-archive"></i> Archived</button>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="action-btn primary"><i class="fas fa-filter"></i> Apply</button>
        <a href="{{ route('production.tracking') }}" class="action-btn"><i class="fas fa-undo"></i> Reset</a>
    </div>
</form>

<!-- KPI GRID -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#ede9fe;color:#6f42c1;"><i class="fas fa-box"></i></div>
        <div class="kpi-label">Total Orders</div>
        <div class="kpi-value"><a href="{{ route('sales.prototype.list') }}">{{ number_format($totalOrders) }}</a></div>
        <div class="kpi-sub">Kasama ang archived</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#ecfdf5;color:#059669;"><i class="fas fa-peso-sign"></i></div>
        <div class="kpi-label">Total Revenue</div>
        <div class="kpi-value">₱{{ number_format($totalRevenue, 2) }}</div>
        <div class="kpi-sub">Kasama ang archived</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#f1f5f9;color:#475569;"><i class="fas fa-box-archive"></i></div>
        <div class="kpi-label">Archived</div>
        <div class="kpi-value">{{ number_format($archivedCount) }}</div>
        <div class="kpi-sub">Filed away (kasama sa totals)</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-columns"></i></div>
        <div class="kpi-label">In Kanban</div>
        <div class="kpi-value"><a href="{{ route('sales.prototype.kanban', $isProdManager ? 'class' : null) }}">{{ number_format($kanbanTotal) }}</a></div>
        <div class="kpi-sub">Across all stages</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#ecfdf5;color:#059669;"><i class="fas fa-sack-dollar"></i></div>
        <div class="kpi-label">Total Paid</div>
        <div class="kpi-value" style="color:#059669;">₱{{ number_format($totalPaidAmount, 2) }}</div>
        <div class="kpi-sub">Verified payments (net of refunds)</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fef3c7;color:#b45309;"><i class="fas fa-hand-holding-dollar"></i></div>
        <div class="kpi-label">Pa Sisingilin</div>
        <div class="kpi-value" style="color:#b45309;">₱{{ number_format($totalCollectible, 2) }}</div>
        <div class="kpi-sub">{{ number_format($collectibleOrders) }} order(s) may balance pa</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-clock"></i></div>
        <div class="kpi-label">Pending Verification</div>
        <div class="kpi-value"><a href="{{ route('sales.verification') }}">₱{{ number_format($pendingVerificationAmount, 2) }}</a></div>
        <div class="kpi-sub">Naghihintay ng verify</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fef3c7;color:#b45309;"><i class="fas fa-flag"></i></div>
        <div class="kpi-label">Priority</div>
        <div class="kpi-value">{{ number_format($prioCount) }}</div>
        <div class="kpi-sub">Orders with priority</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="kpi-label">Delayed</div>
        <div class="kpi-value"><a href="{{ route('sales.prototype.delays') }}" style="color:{{ $delayedCount ? '#dc2626' : 'inherit' }};">{{ number_format($delayedCount) }}</a></div>
        <div class="kpi-sub">Marked delayed</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#dcfce7;color:#15803d;"><i class="fas fa-truck-fast"></i></div>
        <div class="kpi-label">Released Today</div>
        <div class="kpi-value" style="color:#15803d;">{{ number_format($releasedToday) }}</div>
        <div class="kpi-sub">{{ number_format($releasedPcsToday) }} pcs ngayon · {{ number_format($releasedYesterday) }} order kahapon</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#e0e7ff;color:#4f46e5;"><i class="fas fa-clock"></i></div>
        <div class="kpi-label">With Due Date</div>
        <div class="kpi-value"><a href="{{ route('sales.prototype.calendar') }}">{{ number_format($dueCount) }}</a></div>
        <div class="kpi-sub">{{ $overdueDue->count() }} overdue · {{ $upcomingDue->count() }} upcoming</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#dcfce7;color:#15803d;"><i class="fas fa-comment-dots"></i></div>
        <div class="kpi-label">Open Feedback</div>
        <div class="kpi-value">{{ number_format($openFeedbackCount) }}</div>
        <div class="kpi-sub">Production feedbacks</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fef2f2;color:#dc2626;"><i class="fas fa-tools"></i></div>
        <div class="kpi-label">Active Backjobs</div>
        <div class="kpi-value"><a href="{{ route('sales.prototype.backjobs') }}" style="color:{{ $backjobCount ? '#dc2626' : 'inherit' }};">{{ number_format($backjobCount) }}</a></div>
        <div class="kpi-sub">Pending comments</div>
    </div>
    @if($pendingChanges || $pendingAddons)
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fef3c7;color:#b45309;"><i class="fas fa-clipboard-list"></i></div>
        <div class="kpi-label">Pending Requests</div>
        <div class="kpi-value">{{ number_format($pendingChanges + $pendingAddons) }}</div>
        <div class="kpi-sub">{{ $pendingChanges }} changes · {{ $pendingAddons }} add-ons</div>
    </div>
    @endif
</div>

<!-- CHARTS -->
<div class="chart-grid">
    <div class="chart-card">
        <div class="card-title"><i class="fas fa-chart-line"></i> Orders &amp; Revenue Trend <span style="font-size:.72rem;color:#94a3b8;font-weight:600;">(last 14 days)</span></div>
        <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
    </div>
    <div class="chart-card">
        <div class="card-title"><i class="fas fa-chart-pie"></i> Kanban Distribution</div>
        <div class="chart-wrap"><canvas id="kanbanPieChart"></canvas></div>
    </div>
    <div class="chart-card">
        <div class="card-title"><i class="fas fa-chart-bar"></i> Production Stages</div>
        <div class="chart-wrap"><canvas id="stageBarChart"></canvas></div>
    </div>
    <div class="chart-card">
        <div class="card-title"><i class="fas fa-chart-line"></i> Daily Revenue (₱)</div>
        <div class="chart-wrap"><canvas id="revenueChart"></canvas></div>
    </div>
    <div class="chart-card">
        <div class="card-title"><i class="fas fa-truck-fast"></i> Released per Day <span style="font-size:.72rem;color:#94a3b8;font-weight:600;">(DISPATCH · last 14 days)</span></div>
        <div class="chart-wrap"><canvas id="releasedChart"></canvas></div>
        <div style="font-size:.75rem;color:#64748b;text-align:center;margin-top:.4rem;">
            Total: <b>{{ number_format($releasedTotal14) }}</b> order · <b>{{ number_format($releasedPcsTotal14) }}</b> pcs · Avg <b>{{ $releasedAvgPerDay }}</b> order/day — "QA pababa = hindi pa counted"
        </div>
    </div>
</div>

<!-- STAGE TIMING (Andrew 2026-09-18) -->
@php
    $st = $stageTiming['stages'] ?? [];
    $slow = $stageTiming['slowestStage'] ?? null;
    $slStages = array_values(array_filter($st, fn ($x) => ($x['count'] ?? 0) > 0));
    usort($slStages, fn ($a, $b) => ($b['avg_hours'] ?? 0) <=> ($a['avg_hours'] ?? 0));
    $maxAvg = $slStages[0]['avg_hours'] ?? 1;
    $maxAvg = $maxAvg > 0 ? $maxAvg : 1;
@endphp
<div class="chart-card" style="margin-bottom:1.25rem;">
    <div class="card-title">
        <i class="fas fa-stopwatch"></i> Average Time per Production Stage
        <span style="font-size:.72rem;color:#94a3b8;font-weight:600;">
            (mula sa GA tagging logs @if(!empty($stageTiming['logFrom'])) · {{ \Illuminate\Support\Carbon::parse($stageTiming['logFrom'])->format('M j') }}–{{ \Illuminate\Support\Carbon::parse($stageTiming['logTo'])->format('M j') }} @endif)
        </span>
    </div>

    @if($slow)
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:.6rem .85rem;margin-bottom:.9rem;font-size:.83rem;color:#991b1b;">
            <i class="fas fa-hourglass-half"></i>
            Pinakamatagal: <b>{{ $st[$slow]['label'] ?? $slow }}</b> — avg <b>{{ \App\Services\ProductionStageTimingService::human($st[$slow]['avg_hours'] ?? null) }}</b>
            @if(($st[$slow]['count'] ?? 0) > 0) <span style="color:#b91c1c;">({{ $st[$slow]['count'] }} sample)</span>@endif
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-sm mb-0" style="font-size:.8rem;">
            <thead>
                <tr>
                    <th>Stage</th>
                    <th class="text-center">Avg</th>
                    <th class="text-center">Median</th>
                    <th class="text-center">Samples</th>
                    <th class="text-center">Pinakamatagal</th>
                    <th class="text-center">Ongoing</th>
                    <th style="width:120px;">Relatibong tagal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($st as $key => $s)
                <tr>
                    <td class="fw-semibold">
                        @if($key === $slow) <i class="fas fa-exclamation-triangle" style="color:#dc2626;"></i> @endif
                        {{ $s['label'] }}
                    </td>
                    <td class="text-center fw-semibold" style="color:{{ $key === $slow ? '#dc2626' : '#1e293b' }};">
                        {{ \App\Services\ProductionStageTimingService::human($s['avg_hours']) }}
                    </td>
                    <td class="text-center text-muted">{{ \App\Services\ProductionStageTimingService::human($s['median_hours']) }}</td>
                    <td class="text-center">{{ $s['count'] }}</td>
                    <td class="text-center text-muted">{{ \App\Services\ProductionStageTimingService::human($s['max_hours']) }}</td>
                    <td class="text-center">
                        @if(($s['ongoing'] ?? 0) > 0)
                            <span class="badge" style="background:#fef3c7;color:#b45309;">{{ $s['ongoing'] }}</span>
                            <span style="font-size:.68rem;color:#94a3b8;">({{ \App\Services\ProductionStageTimingService::human($s['longest_ongoing_hours']) }})</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>
                    <td>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar" style="width:{{ min(100, (($s['avg_hours'] ?? 0) / $maxAvg) * 100) }}%;background:{{ $key === $slow ? '#dc2626' : '#3b82f6' }};"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(!empty($stageTiming['ongoing']))
    <div style="margin-top:1rem;border-top:1px dashed #e2e8f0;padding-top:.85rem;">
        <div style="font-size:.74rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem;">
            <i class="fas fa-hourglass-start"></i> Kasalukuyang tumatagal (nakabinbin ngayon)
        </div>
        @foreach(array_slice($stageTiming['ongoing'], 0, 5) as $o)
            <div style="display:flex;justify-content:space-between;align-items:center;gap:.6rem;padding:.35rem 0;border-bottom:1px solid #f8fafc;font-size:.8rem;">
                <span>
                    <a href="{{ route('sales.prototype.show', $o['sale']) }}" target="_blank" rel="noopener" class="fw-semibold" style="text-decoration:none;">Sale #{{ $o['sale'] }}</a>
                    <span class="text-muted">· {{ $st[$o['stage']]['label'] ?? $o['stage'] }}</span>
                </span>
                <span class="badge" style="background:#fee2e2;color:#b91c1c;">{{ \App\Services\ProductionStageTimingService::human($o['hours']) }}</span>
            </div>
        @endforeach
    </div>
    @endif

    <div style="font-size:.7rem;color:#94a3b8;margin-top:.7rem;">
        <i class="fas fa-info-circle"></i>
        Bilang mula nang i-tag ang isang stage hanggang malipat sa susunod (o ma-complete). Hihinto sa dispatch.
        <br><i class="fas fa-clock"></i>
        Ang <b>Sewing</b> at <b>QA</b> ay nagsisimula pang mag-log ngayong <b>Sep 18, 2026</b> — bubuo ang average habang tumatagal (may datos na agad ang For Sample / For Format / Printing mula Sep 3).
    </div>
</div>

<!-- PRODUCTS -->
<div class="chart-grid">
    <div class="chart-card">
        <div class="card-title"><i class="fas fa-tshirt"></i> Top 10 Products by Pieces</div>
        <div class="chart-wrap" style="height:300px;"><canvas id="productChart"></canvas></div>
    </div>
    <div class="chart-card">
        <div class="card-title"><i class="fas fa-layer-group"></i> Product Performance — Volume per Item</div>
        @if(count($productMap) > 0)
        <div class="table-responsive" style="max-height:300px;overflow-y:auto;">
            <table class="table table-sm mb-0" style="font-size:.78rem;">
                <thead class="sticky-top bg-white">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th class="text-center">Pcs</th>
                        <th class="text-center">Orders</th>
                        <th class="text-end">Revenue</th>
                        <th style="width:110px;">Share</th>
                    </tr>
                </thead>
                <tbody>
                    @php $pRank = 0; $pShareTotal = $totalProductPcs > 0 ? $totalProductPcs : 1; @endphp
                    @foreach($productMap as $pName => $pData)
                    @php $pRank++; $pShare = ($pData['qty'] / $pShareTotal) * 100; @endphp
                    <tr>
                        <td class="text-muted">{{ $pRank }}</td>
                        <td class="fw-semibold">{{ $pName }}@if(!empty($pData['projects']))<div style="font-size:.68rem;color:#94a3b8;font-weight:400;">📁 {{ implode(' • ', array_slice($pData['projects'], 0, 2)) }}</div>@endif</td>
                        <td class="text-center fw-semibold">{{ number_format($pData['qty']) }}</td>
                        <td class="text-center">{{ number_format($pData['orders']) }}</td>
                        <td class="text-end">₱{{ number_format($pData['revenue'], 2) }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <div class="progress flex-grow-1" style="height:8px;">
                                    <div class="progress-bar" style="width:{{ min($pShare, 100) }}%;background:{{ $pRank === 1 ? '#6f42c1' : ($pRank === 2 ? '#3b82f6' : ($pRank === 3 ? '#f59e0b' : '#cbd5e1')) }};"></div>
                                </div>
                                <span style="font-size:.68rem;color:#64748b;min-width:36px;text-align:right;">{{ number_format($pShare, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center py-4 mb-0">No product data yet</p>
        @endif
    </div>
</div>

<div class="row g-3">
    <!-- KANBAN -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-title"><i class="fas fa-columns"></i> Kanban Status <span class="count-pill">{{ number_format($kanbanTotal) }}</span></div>
            @php
                $colors = ['new' => '#94a3b8', 'sample_approval' => '#f43f5e', 'design' => '#8b5cf6', 'production' => '#3b82f6', 'quality_check' => '#f59e0b', 'ready_for_delivery' => '#10b981', 'delivered' => '#14b8a6', 'completed' => '#22c55e'];
            @endphp
            <div class="kanban-bar">
                @foreach($kanbanCounts as $key => $cnt)
                    @if($cnt > 0)
                        <div class="kanban-seg" style="background:{{ $colors[$key] ?? '#94a3b8' }}; width:{{ $kanbanTotal ? round($cnt / $kanbanTotal * 100, 2) : 0 }}%;" title="{{ $kanbanLabels[$key] }}: {{ $cnt }}">{{ $cnt }}</div>
                    @endif
                @endforeach
                @if($kanbanTotal == 0)
                    <div class="kanban-seg empty" style="width:100%;">No active orders</div>
                @endif
            </div>
            <div class="kanban-legend">
                @foreach($kanbanCounts as $key => $cnt)
                    @if($cnt > 0)
                        <div class="legend-item"><span class="legend-dot" style="background:{{ $colors[$key] ?? '#94a3b8' }};"></span> {{ $kanbanLabels[$key] }} ({{ $cnt }})</div>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-title"><i class="fas fa-tasks"></i> Production Stages</div>
            @forelse($stageCounts as $label => $cnt)
                <span class="stage-chip"><span class="n">{{ $cnt }}</span> {{ $label }}</span>
            @empty
                <div class="empty-mini">No production stages set yet</div>
            @endforelse
        </div>

        <div class="card">
            <div class="card-title"><i class="fas fa-list-ul"></i> Recent Orders <span class="count-pill">{{ number_format($totalOrders) }}</span></div>
            @if($recentSales->count())
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSales as $sale)
                            <tr>
                                <td>
                                    <a class="sale-link" href="{{ route('sales.prototype.show', $sale->id) }}">{{ $sale->sales_number }}</a>
                                    <div style="font-size:.7rem;color:#94a3b8;">{{ $sale->created_at ? $sale->created_at->format('M d, Y') : '' }}</div>
                                </td>
                                <td data-label="Customer">{{ $sale->customer_name }}</td>
                                <td data-label="Status">
                                    @if($sale->is_delayed)
                                        <span class="badge-soft b-delayed">Delayed</span>
                                    @endif
                                    @if($sale->priority > 0)
                                        <span class="badge-soft b-prio">Prio {{ $sale->priority }}</span>
                                    @endif
                                    <span class="badge-soft b-{{ $sale->kanban_status }}">{{ $kanbanLabels[$sale->kanban_status] ?? ucfirst($sale->kanban_status) }}</span>
                                </td>
                                <td data-label="Amount">₱{{ number_format($sale->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-mini">No orders yet</div>
            @endif
        </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-title"><i class="fas fa-calendar-alt"></i> Upcoming Due Dates
                @if($dueCount) <span class="count-pill orange">{{ $upcomingDue->count() }}</span> @endif
                <a href="{{ route('sales.prototype.calendar') }}" class="action-btn" style="margin-left:auto;padding:.3rem .6rem;font-size:.75rem;"><i class="fas fa-external-link-alt"></i> Calendar</a>
            </div>
            @forelse($upcomingDue as $due)
                @php
                    $diff = $due->needed_by->diffInDays(now()->startOfDay(), false);
                    $badge = $diff < 0 ? 'due-now' : ($diff == 0 ? 'due-today' : ($diff <= 2 ? 'due-soon' : 'due-later'));
                    $label = $diff < 0 ? 'OVERDUE' : ($diff == 0 ? 'TODAY' : ($diff == 1 ? 'TOMORROW' : 'M '.$due->needed_by->format('M d')));
                @endphp
                <div class="due-row">
                    <span class="due-badge {{ $badge }}">{{ $label }}</span>
                    <div style="flex:1;min-width:0;">
                        <a class="sale-link" href="{{ route('sales.prototype.show', $due->id) }}">{{ $due->sales_number }}</a>
                        <div style="font-size:.75rem;color:#64748b;">{{ $due->customer_name }} · {{ $due->needed_by->format('M d, g:i A') }}</div>
                    </div>
                    @if($due->priority > 0) <span class="badge-soft b-prio">P{{ $due->priority }}</span> @endif
                    @if($due->is_delayed) <span class="badge-soft b-delayed">Delayed</span> @endif
                </div>
            @empty
                <div class="empty-mini">No upcoming due dates</div>
            @endforelse
        </div>

        @if($overdueDue->count())
            <div class="card" style="border-color:#fecaca;">
                <div class="card-title" style="color:#dc2626;"><i class="fas fa-exclamation-circle"></i> Overdue Orders <span class="count-pill danger">{{ $overdueDue->count() }}</span></div>
                @foreach($overdueDue->take(6) as $due)
                    <div class="due-row">
                        <span class="due-badge due-now">OVERDUE</span>
                        <div style="flex:1;min-width:0;">
                            <a class="sale-link" href="{{ route('sales.prototype.show', $due->id) }}">{{ $due->sales_number }}</a>
                            <div style="font-size:.75rem;color:#64748b;">{{ $due->customer_name }} · was {{ $due->needed_by->format('M d, g:i A') }}</div>
                        </div>
                        @if($due->is_delayed) <span class="badge-soft b-delayed">Delayed</span> @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div class="card">
            <div class="card-title"><i class="fas fa-bell"></i> Needs Attention
                <span class="count-pill {{ ($delayedCount + $openFeedbackCount + $backjobCount) ? 'danger' : 'green' }}">{{ number_format($delayedCount + $openFeedbackCount + $backjobCount) }}</span>
            </div>
            <div class="due-row">
                <span class="badge-soft b-delayed" style="font-size:.78rem;">{{ $delayedCount }}</span>
                <span style="flex:1;">Delayed orders</span>
                <a href="{{ route('sales.prototype.delays') }}" class="sale-link">View</a>
            </div>
            <div class="due-row">
                <span class="badge-soft b-qc" style="font-size:.78rem;">{{ $openFeedbackCount }}</span>
                <span style="flex:1;">Open feedbacks</span>
                <span style="font-size:.75rem;color:#94a3b8;">Production</span>
            </div>
            <div class="due-row">
                <span class="badge-soft b-delayed" style="font-size:.78rem;">{{ $backjobCount }}</span>
                <span style="flex:1;">Active backjobs</span>
                <a href="{{ route('sales.prototype.backjobs') }}" class="sale-link">View</a>
            </div>
            @if($pendingChanges || $pendingAddons)
            <div class="due-row">
                <span class="badge-soft b-qc" style="font-size:.78rem;">{{ $pendingChanges + $pendingAddons }}</span>
                <span style="flex:1;">Pending change/add-on requests</span>
                <a href="{{ route('sales.prototype.list') }}" class="sale-link">View</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') return;

        const gridColor = 'rgba(148,163,184,.18)';
        const fontStyle = { family: 'Inter, sans-serif', size: 11 };

        // 1. Orders & Revenue trend (bar + line)
        const trendEl = document.getElementById('trendChart');
        if (trendEl) {
            new Chart(trendEl, {
                type: 'bar',
                data: {
                    labels: @json($trendLabels),
                    datasets: [
                        {
                            label: 'Orders',
                            data: @json($trendOrders),
                            backgroundColor: 'rgba(111,66,193,.75)',
                            borderRadius: 4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Revenue (₱)',
                            data: @json($trendRevenue),
                            type: 'line',
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16,185,129,.12)',
                            fill: true,
                            tension: .35,
                            pointRadius: 2.5,
                            borderWidth: 2,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, boxHeight: 10, font: fontStyle } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: fontStyle, maxRotation: 45 } },
                        y: { position: 'left', grid: { color: gridColor }, ticks: { font: fontStyle, precision: 0 } },
                        y1: { position: 'right', grid: { display: false }, ticks: { font: fontStyle, callback: v => '₱' + (v / 1000).toFixed(1) + 'k' } }
                    }
                }
            });
        }

        // 2. Kanban pie
        const pieEl = document.getElementById('kanbanPieChart');
        if (pieEl) {
            new Chart(pieEl, {
                type: 'doughnut',
                data: {
                    labels: @json($pieLabels),
                    datasets: [{ data: @json($pieValues), backgroundColor: @json($pieColors), borderWidth: 2, borderColor: '#fff' }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 10, boxHeight: 10, font: fontStyle, padding: 8 } }
                    }
                }
            });
        }

        // 3. Production stages bar
        const stageEl = document.getElementById('stageBarChart');
        if (stageEl) {
            new Chart(stageEl, {
                type: 'bar',
                data: {
                    labels: @json($stageLabels),
                    datasets: [{
                        label: 'Orders',
                        data: @json($stageValues),
                        backgroundColor: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#f43f5e', '#14b8a6', '#94a3b8', '#22c55e'],
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { font: fontStyle, precision: 0 } },
                        y: { grid: { display: false }, ticks: { font: fontStyle } }
                    }
                }
            });
        }

        // 4. Revenue area chart
        const revEl = document.getElementById('revenueChart');
        if (revEl) {
            new Chart(revEl, {
                type: 'line',
                data: {
                    labels: @json($trendLabels),
                    datasets: [{
                        label: 'Revenue',
                        data: @json($trendRevenue),
                        borderColor: '#6f42c1',
                        backgroundColor: ctx => {
                            const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 260);
                            g.addColorStop(0, 'rgba(111,66,193,.35)');
                            g.addColorStop(1, 'rgba(111,66,193,.02)');
                            return g;
                        },
                        fill: true,
                        tension: .4,
                        pointRadius: 3,
                        pointBackgroundColor: '#6f42c1',
                        borderWidth: 2.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: fontStyle, maxRotation: 45 } },
                        y: { grid: { color: gridColor }, ticks: { font: fontStyle, callback: v => '₱' + (v / 1000).toFixed(1) + 'k' } }
                    }
                }
            });
        }

        // 5. Released per day (DISPATCH) — Andrew 2026-09-23
        const relEl = document.getElementById('releasedChart');
        if (relEl) {
            new Chart(relEl, {
                type: 'bar',
                data: {
                    labels: @json($releasedLabels),
                    datasets: [
                        {
                            label: 'Orders (project)',
                            data: @json($releasedCounts),
                            backgroundColor: 'rgba(16,185,129,.8)',
                            borderRadius: 4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Pieces (pcs)',
                            data: @json($releasedPcsCounts),
                            type: 'line',
                            borderColor: '#6f42c1',
                            backgroundColor: 'rgba(111,66,193,.12)',
                            fill: false,
                            tension: .35,
                            pointRadius: 2.5,
                            borderWidth: 2,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, boxHeight: 10, font: fontStyle } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: fontStyle, maxRotation: 45 } },
                        y: { position: 'left', grid: { color: gridColor }, ticks: { font: fontStyle, precision: 0 }, beginAtZero: true, title: { display: true, text: 'Orders', font: fontStyle } },
                        y1: { position: 'right', grid: { display: false }, ticks: { font: fontStyle, precision: 0 }, beginAtZero: true, title: { display: true, text: 'Pieces', font: fontStyle } }
                    }
                }
            });
        }

        // 6. Top 10 products by pieces (horizontal bar)
        const prodEl = document.getElementById('productChart');
        if (prodEl) {
            new Chart(prodEl, {
                type: 'bar',
                data: {
                    labels: @json($topProductNames),
                    datasets: [{
                        label: 'Pieces',
                        data: @json($topProductPcs),
                        backgroundColor: ['#6f42c1', '#3b82f6', '#10b981', '#f59e0b', '#f43f5e', '#14b8a6', '#8b5cf6', '#22c55e', '#94a3b8', '#ef4444'],
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ctx.parsed.x + ' pcs' } }
                    },
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { font: fontStyle, precision: 0 } },
                        y: { grid: { display: false }, ticks: { font: { family: 'Inter, sans-serif', size: 11 } } }
                    }
                }
            });
        }
    });
</script>
@endpush
