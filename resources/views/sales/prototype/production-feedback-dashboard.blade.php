@extends('layouts.app')

@section('title', 'Production Feedback Dashboard')

@push('styles')
<style>
    .pfd-hero {
        background: linear-gradient(135deg, #3b2505 0%, #92400e 55%, #d97706 100%);
        color: #fff;
        padding: 20px 24px;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(217, 119, 6, .25);
        position: relative;
        overflow: hidden;
    }
    .pfd-hero::before {
        content: '';
        position: absolute; top: -40px; right: -40px;
        width: 200px; height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,0.14) 0%, transparent 70%);
    }
    .pfd-hero h4 { font-weight: 800; margin: 0; display: flex; align-items: center; gap: 10px; }
    .pfd-hero .sub { font-size: 12.5px; opacity: .85; margin-top: 3px; }

    .pfd-kpi {
        background: #fff;
        border-radius: 14px;
        padding: 14px;
        display: flex; align-items: center; gap: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        border: 1px solid #f1f0f5;
        height: 100%;
    }
    .pfd-kpi .ico {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 17px; flex-shrink: 0;
    }
    .pfd-kpi .val { font-size: 22px; font-weight: 800; line-height: 1.1; color: #111827; }
    .pfd-kpi .lbl { font-size: 10.5px; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; }

    .pfd-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        border: 1px solid #f1f0f5;
        overflow: hidden;
        height: 100%;
    }
    .pfd-card .card-head {
        padding: 13px 18px;
        border-bottom: 1px solid #f1f0f5;
        font-weight: 700; font-size: 14px; color: #111827;
        display: flex; align-items: center; gap: 8px;
    }
    .pfd-card .card-body { padding: 16px 18px; }

    .pfd-cat-row { margin-bottom: 12px; }
    .pfd-cat-row .top { display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 4px; }
    .pfd-cat-row .top .n { font-weight: 800; }
    .pfd-track { background: #f1f5f9; border-radius: 20px; height: 9px; overflow: hidden; }
    .pfd-fill { height: 100%; border-radius: 20px; }

    table.pfd-table { font-size: 12.5px; margin: 0; width: 100%; border-collapse: collapse; }
    table.pfd-table th {
        font-size: 10.5px; text-transform: uppercase; letter-spacing: .4px;
        color: #6b7280; border-bottom: 2px solid #f1f0f5; padding: 9px 10px; white-space: nowrap; text-align: left;
    }
    table.pfd-table td { padding: 9px 10px; border-bottom: 1px solid #f6f6fa; vertical-align: middle; }
    table.pfd-table tr:last-child td { border-bottom: none; }
    .pfd-legend { list-style: none; margin: 12px 0 0; padding: 0; font-size: 12px; }
    .pfd-legend li { display: flex; align-items: center; gap: 7px; margin-bottom: 6px; }
    .pfd-legend .dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
    .pfd-legend .cnt { margin-left: auto; font-weight: 700; color: #475569; }
    .pfd-pill { display:inline-block; font-size:10.5px; font-weight:700; padding:2px 9px; border-radius:20px; color:#fff; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Hero --}}
    <div class="pfd-hero mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4><i class="fas fa-chart-pie"></i> Production Feedback Dashboard</h4>
            <div class="sub">Graphical stats ng feedback — kung ilan, anong pinakamadalas na issue, at sino ang madalas mabigyan.</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('sales.prototype.production-feedback.list') }}" class="btn btn-sm btn-light fw-semibold" style="border-radius:10px;">
                <i class="fas fa-list me-1"></i> Feedback List
            </a>
            @if(auth()->user()->isProdManager())
            <a href="{{ route('sales.prototype.list') }}" class="btn btn-sm text-white" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);border-radius:10px;">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            @endif
        </div>
    </div>

    {{-- Period filter --}}
    <div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
        <span class="text-muted small fw-semibold me-1">Panahon:</span>
        @foreach([7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days', 0 => 'All time'] as $d => $label)
        <a href="{{ route('sales.prototype.production-feedback.dashboard', ['days' => $d]) }}"
           class="stat-pill text-decoration-none"
           style="padding:6px 14px;border-radius:20px;font-size:12.5px;font-weight:600;background:{{ (int)$days === $d ? '#d97706' : '#f1f5f9' }};color:{{ (int)$days === $d ? '#fff' : '#334155' }};">{{ $label }}</a>
        @endforeach
    </div>

    {{-- KPI cards --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="pfd-kpi"><div class="ico" style="background:#334155;"><i class="fas fa-clipboard-list"></i></div>
                <div><div class="val">{{ $total }}</div><div class="lbl">Total feedback</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="pfd-kpi"><div class="ico" style="background:#d97706;"><i class="fas fa-hourglass-half"></i></div>
                <div><div class="val">{{ $open }}</div><div class="lbl">Open</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="pfd-kpi"><div class="ico" style="background:#2563eb;"><i class="fas fa-eye"></i></div>
                <div><div class="val">{{ $acknowledged }}</div><div class="lbl">Acknowledged</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="pfd-kpi"><div class="ico" style="background:#059669;"><i class="fas fa-check-double"></i></div>
                <div><div class="val">{{ $resolved }}</div><div class="lbl">Resolved</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="pfd-kpi"><div class="ico" style="background:#7c3aed;"><i class="fas fa-percent"></i></div>
                <div><div class="val">{{ $resolveRate }}%</div><div class="lbl">Resolve rate</div></div></div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="pfd-kpi"><div class="ico" style="background:#0891b2;"><i class="fas fa-stopwatch"></i></div>
                <div><div class="val">{{ $avgResolveHours !== null ? $avgResolveHours . 'h' : '—' }}</div><div class="lbl">Avg. resolve time</div></div></div>
        </div>
    </div>

    {{-- Most common issue + status donut --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="pfd-card">
                <div class="card-head"><i class="fas fa-triangle-exclamation text-warning"></i> Most common issue (feedback category)</div>
                <div class="card-body">
                    @if($topCategory && $topCategory->total > 0)
                    <div class="mb-3 p-2 px-3" style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;">
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#92400e;font-weight:700;">Pinakamadalas</div>
                        <div style="font-size:18px;font-weight:800;color:#78350f;">{{ $topCategory->label }}
                            <span class="badge bg-warning text-dark ms-1">{{ $topCategory->total }}×</span>
                        </div>
                    </div>
                    @endif
                    @php $catMax = max(1, (int) $categoryBreakdown->max('total')); @endphp
                    @foreach($categoryBreakdown as $cat)
                    @php $pct = $total > 0 ? round(($cat->total / $total) * 100) : 0; @endphp
                    <div class="pfd-cat-row">
                        <div class="top">
                            <span>{{ $cat->label }}</span>
                            <span class="n">{{ $cat->total }} <span class="text-muted fw-normal">({{ $pct }}%)</span></span>
                        </div>
                        <div class="pfd-track">
                            <div class="pfd-fill" style="width:{{ round(($cat->total / $catMax) * 100) }}%;background:linear-gradient(90deg,#fbbf24,#d97706);"></div>
                        </div>
                    </div>
                    @endforeach
                    @if($total === 0)
                    <div class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2" style="opacity:.4;"></i>Walang feedback sa panahong ito.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="pfd-card">
                <div class="card-head"><i class="fas fa-chart-pie text-primary"></i> Status breakdown</div>
                <div class="card-body">
                    <div style="height:210px;position:relative;"><canvas id="statusChart"></canvas></div>
                    <ul class="pfd-legend">
                        <li><span class="dot" style="background:#d97706;"></span> Open <span class="cnt">{{ $open }}</span></li>
                        <li><span class="dot" style="background:#2563eb;"></span> Acknowledged <span class="cnt">{{ $acknowledged }}</span></li>
                        <li><span class="dot" style="background:#059669;"></span> Resolved <span class="cnt">{{ $resolved }}</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Trend --}}
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="pfd-card">
                <div class="card-head"><i class="fas fa-chart-line text-success"></i> Trend — feedback received vs resolved</div>
                <div class="card-body">
                    <div style="height:250px;position:relative;"><canvas id="trendChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Agent leaderboard --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="pfd-card">
                <div class="card-head"><i class="fas fa-user-tag text-warning"></i> Feedback per agent / artist</div>
                <div class="card-body">
                    @if($agentStats->count())
                    <div style="height:{{ max(220, $agentStats->take(8)->count() * 42) }}px;position:relative;"><canvas id="agentChart"></canvas></div>
                    @else
                    <div class="text-center text-muted py-4">Walang data.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="pfd-card">
                <div class="card-head"><i class="fas fa-ranking-star text-primary"></i> Leaderboard</div>
                <div class="card-body p-0">
                    <div style="max-height:330px;overflow:auto;">
                        <table class="pfd-table">
                            <thead><tr><th>#</th><th>Agent</th><th class="text-center">Total</th><th class="text-center">Open</th><th class="text-center">Resolved</th><th>Rate</th></tr></thead>
                            <tbody>
                                @forelse($agentStats as $i => $s)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td><strong>{{ $s->name }}</strong></td>
                                    <td class="text-center fw-bold">{{ $s->total }}</td>
                                    <td class="text-center" style="color:#d97706;font-weight:700;">{{ $s->open }}</td>
                                    <td class="text-center" style="color:#059669;font-weight:700;">{{ $s->resolved }}</td>
                                    <td style="white-space:nowrap;">{{ $s->resolve_rate }}%</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">Walang data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Oldest open + recent --}}
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="pfd-card">
                <div class="card-head"><i class="fas fa-bell text-danger"></i> Pinakamatagal nang bukas (Open)</div>
                <div class="card-body p-0">
                    <table class="pfd-table">
                        <thead><tr><th>Sales #</th><th>Recipient</th><th>Category</th><th>Waiting</th></tr></thead>
                        <tbody>
                            @forelse($oldestOpen as $fb)
                            <tr style="cursor:pointer;" onclick="window.location.href='{{ route('sales.prototype.show', $fb->sale_id) }}'">
                                <td><strong>{{ $fb->sale->sales_number ?? ('Sale #' . $fb->sale_id) }}</strong></td>
                                <td>{{ $fb->toUser?->display_label ?? '—' }}</td>
                                <td><span class="pf-meta">{{ \App\Models\ProductionFeedback::CATEGORIES[$fb->category] ?? $fb->category }}</span></td>
                                <td style="color:#dc2626;font-weight:700;">{{ $fb->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Walang bukas na feedback. 🎉</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="pfd-card">
                <div class="card-head"><i class="fas fa-clock-rotate-left text-secondary"></i> Recent feedback</div>
                <div class="card-body p-0">
                    <table class="pfd-table">
                        <thead><tr><th>Sales #</th><th>Recipient</th><th>Category</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($recent as $fb)
                            <tr style="cursor:pointer;" onclick="window.location.href='{{ route('sales.prototype.show', $fb->sale_id) }}'">
                                <td><strong>{{ $fb->sale->sales_number ?? ('Sale #' . $fb->sale_id) }}</strong></td>
                                <td>{{ $fb->toUser?->display_label ?? '—' }}</td>
                                <td><span class="pf-meta">{{ \App\Models\ProductionFeedback::CATEGORIES[$fb->category] ?? $fb->category }}</span></td>
                                <td><span class="pfd-pill" style="background:{{ $fb->status === 'resolved' ? '#059669' : ($fb->status === 'acknowledged' ? '#2563eb' : '#d97706') }};">{{ ucfirst($fb->status) }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Walang feedback.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $agentChartRows = $agentStats->take(8)->map(function ($s) {
        return ['name' => $s->name, 'open' => $s->open, 'ack' => $s->acknowledged, 'res' => $s->resolved];
    })->values();
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.Chart) return;

    // Status doughnut
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Open', 'Acknowledged', 'Resolved'],
            datasets: [{ data: [{{ $open }}, {{ $acknowledged }}, {{ $resolved }}],
                backgroundColor: ['#d97706', '#2563eb', '#059669'], borderWidth: 2, borderColor: '#fff' }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { display: false } } }
    });

    // Trend line
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: @json($trend->pluck('label')),
            datasets: [
                { label: 'Received', data: @json($trend->pluck('created')), borderColor: '#d97706',
                  backgroundColor: 'rgba(217,119,6,0.12)', fill: true, tension: .35, borderWidth: 2, pointRadius: 2 },
                { label: 'Resolved', data: @json($trend->pluck('resolved')), borderColor: '#059669',
                  backgroundColor: 'rgba(5,150,105,0.10)', fill: true, tension: .35, borderWidth: 2, pointRadius: 2 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } }
        }
    });

    // Agent stacked horizontal bar
    @if($agentStats->count())
    (function () {
        var rows = @json($agentChartRows);
        new Chart(document.getElementById('agentChart'), {
            type: 'bar',
            data: {
                labels: rows.map(r => r.name),
                datasets: [
                    { label: 'Open', data: rows.map(r => r.open), backgroundColor: '#d97706', borderRadius: 4, maxBarThickness: 24, stack: 's' },
                    { label: 'Acknowledged', data: rows.map(r => r.ack), backgroundColor: '#2563eb', borderRadius: 4, maxBarThickness: 24, stack: 's' },
                    { label: 'Resolved', data: rows.map(r => r.res), backgroundColor: '#059669', borderRadius: 4, maxBarThickness: 24, stack: 's' }
                ]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { x: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }, y: { stacked: true, grid: { display: false } } }
            }
        });
    })();
    @endif
});
</script>
@endpush
