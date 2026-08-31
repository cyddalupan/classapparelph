@extends('layouts.app')

@section('title', 'GA Dashboard')

@push('styles')
<style>
    .ga-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #6f42c1 100%);
        color: #fff;
        padding: 20px 24px;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(79, 70, 229, .25);
    }
    .ga-hero h4 { font-weight: 800; margin: 0; }
    .ga-hero .sub { font-size: 12.5px; opacity: .85; }
    .ga-stat {
        background: #fff;
        border-radius: 14px;
        padding: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        border: 1px solid #f0f0f5;
        height: 100%;
    }
    .ga-stat .ico {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 18px;
        flex-shrink: 0;
    }
    .ga-stat .val { font-size: 22px; font-weight: 800; line-height: 1.1; color: #111827; }
    .ga-stat .lbl { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; }
    .ga-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        border: 1px solid #f0f0f5;
        overflow: hidden;
    }
    .ga-card .card-head {
        padding: 14px 18px;
        border-bottom: 1px solid #f0f0f5;
        font-weight: 700;
        font-size: 14.5px;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ga-card .card-body { padding: 18px; }
    .ga-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 13px; color: #fff;
        flex-shrink: 0;
    }
    .stage-pill {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        color: #fff;
        margin: 2px;
    }
    .trend-bar {
        height: 8px;
        border-radius: 6px;
        background: linear-gradient(90deg, #6f42c1, #8e5bd8);
        min-width: 2px;
    }
    .empty-box {
        text-align: center;
        padding: 40px 20px;
        color: #6b7280;
    }
    .empty-box .ico { font-size: 40px; opacity: .35; margin-bottom: 8px; }
    table.ga-dash-table { font-size: 13px; margin: 0; }
    table.ga-dash-table th {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #6b7280;
        border-bottom: 2px solid #f0f0f5 !important;
        padding: 10px 12px;
        white-space: nowrap;
    }
    table.ga-dash-table td { padding: 10px 12px; border-bottom: 1px solid #f5f5fa; vertical-align: middle; }
    table.ga-dash-table tr:last-child td { border-bottom: none; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- ── Hero header ── --}}
    <div class="ga-hero mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-1">📊 GA Dashboard</h4>
            <div class="sub">Performance ng GA — <b>counted lang kapag na-tag ng SEWING</b> pataas (permanenteng naka-record)</div>
        </div>
        <div class="text-end">
            <a href="{{ route('sales.prototype.ga-order-list') }}" class="btn btn-sm btn-light fw-semibold" style="border-radius:10px;">
                <i class="fas fa-arrow-left"></i> GA Job List
            </a>
        </div>
    </div>

    {{-- ── Month/Year filter ── --}}
    <form method="GET" action="{{ route('sales.prototype.ga-dashboard') }}" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Buwan</label>
                <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Lahat</option>
                    @foreach(['01'=>'Enero','02'=>'Pebrero','03'=>'Marso','04'=>'Abril','05'=>'Mayo','06'=>'Hunyo','07'=>'Hulyo','08'=>'Agosto','09'=>'Setyembre','10'=>'Oktubre','11'=>'Nobyembre','12'=>'Disyembre'] as $m => $mname)
                        <option value="{{ $m }}" {{ (string)$month === $m ? 'selected' : '' }}>{{ $mname }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Taon</label>
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Lahat</option>
                    @for($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}" {{ (string)$year === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-md-2">
                <a href="{{ route('sales.prototype.ga-dashboard') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:9px;">Clear</a>
            </div>
        </div>
    </form>

    {{-- ── Summary stats ── --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="ga-stat">
                <div class="ico" style="background:linear-gradient(135deg,#6f42c1,#8e5bd8);"><i class="fas fa-check-double"></i></div>
                <div>
                    <div class="val">{{ $totalCounted }}</div>
                    <div class="lbl">Counted Jobs (SEWING+)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="ga-stat">
                <div class="ico" style="background:linear-gradient(135deg,#198754,#4cc98e);"><i class="fas fa-users"></i></div>
                <div>
                    <div class="val">{{ $gaUsers->count() }}</div>
                    <div class="lbl">GA ({{ auth()->user()->isManager() ? 'lahat' : 'ikaw' }})</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="ga-stat">
                <div class="ico" style="background:linear-gradient(135deg,#0d6efd,#5aa2f5);"><i class="fas fa-layer-group"></i></div>
                <div>
                    <div class="val">{{ collect($perGa)->sum('doneStages') }}</div>
                    <div class="lbl">Stages na DONE</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="ga-stat">
                <div class="ico" style="background:linear-gradient(135deg,#e8590c,#fd7e14);"><i class="fas fa-chart-line"></i></div>
                <div>
                    <div class="val">{{ $monthlyTrend->count() }}</div>
                    <div class="lbl">Buwan na may data</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="ga-stat">
                <div class="ico" style="background:linear-gradient(135deg,#0dcaf0,#5ad0f0);"><i class="fas fa-box-open"></i></div>
                <div>
                    <div class="val">{{ $totalPieces }}</div>
                    <div class="lbl">Total Piraso</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- ── Per-GA breakdown ── --}}
        <div class="col-lg-7">
            <div class="ga-card">
                <div class="card-head"><i class="fas fa-user-tie text-muted"></i> Per-GA Performance</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="ga-dash-table table">
                            <thead>
                                <tr>
                                    <th>GA</th>
                                    <th class="text-center">Jobs</th>
                                    <th class="text-center">Piraso</th>
                                    <th class="text-center">Stages DONE</th>
                                    <th>Per Stage (DONE)</th>
                                    <th class="text-center">Avg Oras</th>
                                    <th class="text-center">Oras/Piraso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($perGa as $row)
                                    @php $u = $row['user']; @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="ga-avatar" style="background:linear-gradient(135deg,#6f42c1,#8e5bd8);">{{ strtoupper(substr($u->name ?? 'GA', 0, 1)) }}</span>
                                                <div>
                                                    <div class="fw-bold">{{ $u->name }}</div>
                                                    <div class="small text-muted">{{ $u->position ?? 'GA' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold fs-6">{{ $row['jobs'] }}</td>
                                        <td class="text-center fw-bold">{{ $perGaPieces[$u->id] ?? 0 }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $row['doneStages'] }}</span>
                                        </td>
                                        <td>
                                            @foreach($row['stages'] as $st => $cnt)
                                                @if($cnt > 0)
                                                    <div class="mb-1">
                                                        <span class="stage-pill" style="background:{{ ['FOR SAMPLE'=>'#fd7e14','FOR APPROVAL'=>'#e8590c','FOR FORMAT'=>'#6f42c1','PRINTING'=>'#0d6efd'][$st] ?? '#6c757d' }};">{{ $cnt }} {{ $st }}</span>
                                                        <span class="small text-muted" style="font-size:11px;">
                                                            ({{ $row['stagePieces'][$st] ?? 0 }} pcs{{ !empty($row['stageMinsPerPiece'][$st]) ? ' · ' . $row['stageMinsPerPiece'][$st] . ' min/pc' : '' }})
                                                        </span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </td>
                                        <td class="text-center">
                                            {{ $row['avgHours'] > 0 ? $row['avgHours'] . ' hrs' : '—' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $row['overallMinsPerPiece'] > 0 ? $row['overallMinsPerPiece'] . ' min/pc' : '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="empty-box">
                                        <div class="ico"><i class="fas fa-inbox"></i></div>
                                        Wala pang counted jobs — mag-tag muna ng SEWING sa isang order para magsimula ang data.
                                    </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Monthly trend ── --}}
        <div class="col-lg-5">
            <div class="ga-card mb-3">
                <div class="card-head"><i class="fas fa-calendar-alt text-muted"></i> Monthly Trend (counted jobs)</div>
                <div class="card-body">
                    @if($monthlyTrend->isEmpty())
                        <div class="empty-box">
                            <div class="ico"><i class="fas fa-chart-bar"></i></div>
                            Wala pang data.
                        </div>
                    @else
                        <div style="position:relative;height:230px;"><canvas id="trendChart"></canvas></div>
                    @endif
                </div>
            </div>
        </div>
        {{-- ── Items Completed ── --}}
        <div class="col-lg-5">
            <div class="ga-card mb-3">
                <div class="card-head"><i class="fas fa-box-open text-muted"></i> Items na Na-process ({{ $totalPieces }} piraso)</div>
                <div class="card-body">
                    @if(empty($itemBreakdown))
                        <div class="empty-box">
                            <div class="ico"><i class="fas fa-box-open"></i></div>
                            Wala pang item data.
                        </div>
                    @else
                        <div style="position:relative;height:170px;"><canvas id="itemsChart"></canvas></div>
                        <div class="mt-2" id="itemsLegend">
                            @foreach($itemBreakdown as $iname => $iqty)
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="legend-dot" data-idx="{{ $loop->index }}"></span>
                                    <div class="flex-grow-1" style="font-size:12.5px;font-weight:600;color:#374151;">{{ $iname }}</div>
                                    <div style="font-weight:800;font-size:13px;">{{ $iqty }} pcs</div>
                                </div>
                                @php $variants = collect($itemDetail ?? [])->filter(fn($q, $n) => strtoupper(trim(explode(' ', trim($n))[0])) === $iname); @endphp
                                @if($variants->isNotEmpty())
                                    <div class="ps-4 mb-1" style="font-size:11.5px;color:#6b7280;">
                                        @foreach($variants as $vname => $vqty)
                                            <div class="d-flex justify-content-between">
                                                <span>{{ $vname }}</span>
                                                <span class="fw-bold">{{ $vqty }} pcs</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        {{-- ── Piraso per GA ── --}}
        <div class="col-lg-7">
            <div class="ga-card">
                <div class="card-head"><i class="fas fa-chart-simple text-muted"></i> Piraso per GA</div>
                <div class="card-body">
                    @if(empty($perGa))
                        <div class="empty-box">
                            <div class="ico"><i class="fas fa-chart-simple"></i></div>
                            Wala pang data.
                        </div>
                    @else
                        <div style="position:relative;height:{{ count($perGa) * 56 + 40 }}px;"><canvas id="gaPiecesChart"></canvas></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5"></div>
    </div>

    {{-- ── Recent counted jobs (Manager view only — wala na sa list ng GA, hindi na nila kailangan) ── --}}
    @if(auth()->user()->isManager())
    <div class="ga-card mt-3">
        <div class="card-head"><i class="fas fa-history text-muted"></i> Mga Huling Na-count (SEWING+ tag)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="ga-dash-table table">
                    <thead>
                        <tr>
                            <th>Sales #</th>
                            <th>Customer</th>
                            <th>Stage</th>
                            <th>Na-count noong</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCounted as $s)
                            <tr>
                                <td>
                                    <a href="{{ route('sales.prototype.show', $s->id) }}" class="fw-bold text-decoration-none">
                                        {{ $s->sales_number }}
                                    </a>
                                </td>
                                <td>{{ $s->customer_name }}</td>
                                <td><span class="badge bg-secondary">{{ $s->production_stage }}</span></td>
                                <td class="text-muted">{{ \Carbon\Carbon::parse($s->ga_counted_at)->format('M d, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="empty-box">
                                <div class="ico"><i class="fas fa-inbox"></i></div>
                                Wala pang na-count na job sa napiling filter.
                            </td></tr>
                        @endforelse
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
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.Chart) return;

        const colors = ['#6f42c1', '#fd7e14', '#0d6efd', '#198754', '#e8590c', '#0dcaf0', '#d63384', '#ffc107'];

        // ── Monthly trend bar chart ──
        const trendEl = document.getElementById('trendChart');
        if (trendEl) {
            const labels = @json($monthlyTrend->map(fn($m) => \Carbon\Carbon::createFromFormat('Y-m', $m->ym)->format('M Y'))->values());
            const data = @json($monthlyTrend->pluck('total')->values());
            new Chart(trendEl, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Counted jobs',
                        data: data,
                        backgroundColor: 'rgba(111, 66, 193, 0.75)',
                        borderColor: '#6f42c1',
                        borderWidth: 1,
                        borderRadius: 8,
                        maxBarThickness: 36
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // ── Items doughnut chart ──
        const itemsEl = document.getElementById('itemsChart');
        if (itemsEl) {
            const labels = @json(array_keys($itemBreakdown));
            const data = @json(array_values($itemBreakdown));
            new Chart(itemsEl, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors.slice(0, labels.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: { legend: { display: false } }
                }
            });
            // Kulayan ang legend dots para tugma sa chart
            document.querySelectorAll('#itemsLegend .legend-dot').forEach((dot, i) => {
                dot.style.cssText = 'display:inline-block;width:10px;height:10px;border-radius:3px;background:' + colors[i % colors.length] + ';margin-right:6px;flex-shrink:0;';
            });
        }

        // ── Piraso per GA horizontal bar ──
        const gaEl = document.getElementById('gaPiecesChart');
        if (gaEl) {
            const rows = @json(collect($perGa)->map(fn($row) => [
                'name' => $row['user']->name,
                'pieces' => $perGaPieces[$row['user']->id] ?? 0
            ])->values());
            new Chart(gaEl, {
                type: 'bar',
                data: {
                    labels: rows.map(r => r.name),
                    datasets: [{
                        label: 'Piraso',
                        data: rows.map(r => r.pieces),
                        backgroundColor: colors.slice(0, rows.length),
                        borderRadius: 8,
                        maxBarThickness: 26
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 } },
                        y: { grid: { display: false } }
                    }
                }
            });
        }
    });
</script>
@endpush
