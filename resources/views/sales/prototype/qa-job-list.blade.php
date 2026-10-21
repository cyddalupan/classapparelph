@extends('layouts.app')

@section('title', 'QA Job List')

@push('styles')
<style>
    .main-content, .content-area { min-width: 0; }

    .qa-hero {
        background: linear-gradient(135deg, #0f766e 0%, #0ea5a4 45%, #6f42c1 100%);
        border-radius: 16px;
        padding: 22px 26px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(14, 165, 164, .28);
    }
    .qa-hero h4 { font-weight: 800; letter-spacing: .2px; margin: 0 0 4px; }
    .qa-hero .sub { font-size: 12.5px; opacity: .92; }

    .qa-stat {
        background: #fff;
        border-radius: 14px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, .07);
        height: 100%;
    }
    .qa-stat .ico {
        width: 40px; height: 40px;
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 15px; flex: 0 0 auto;
    }
    .qa-stat .val { font-size: 20px; font-weight: 800; color: #111827; line-height: 1.1; }
    .qa-stat .lbl { font-size: 10.5px; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }

    .qa-table-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 3px 14px rgba(15, 23, 42, .08);
        overflow: hidden;
    }
    .qa-table-card table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .qa-table-card thead th {
        position: sticky; top: 0; z-index: 2;
        background: #f8fafc;
        font-size: 10.5px; font-weight: 800; text-transform: uppercase;
        letter-spacing: .6px; color: #475569;
        padding: 11px 12px; border-bottom: 1px solid #e2e8f0; white-space: nowrap;
    }
    .qa-table-card tbody td {
        padding: 10px 12px; border-bottom: 1px solid #eef2f7;
        font-size: 12.5px; color: #1f2937; vertical-align: middle;
    }
    .qa-table-card tbody tr:hover { background: #f9fbfd; }
    .qa-table-card tbody tr.qa-row { cursor: pointer; }
    .qa-table-card tbody tr.qa-row:hover { background: #eefaf8; }
    .qa-check-chip {
        display: inline-flex; align-items: center; gap: 5px;
        margin-top: 5px; padding: 3px 8px; border-radius: 8px;
        background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;
        font-size: 10.5px; font-weight: 800; white-space: nowrap;
    }
    .qa-check-chip.full { background: #dcfce7; border-color: #4ade80; color: #14532d; }
    .qa-check-chip.empty { background: #f8fafc; border-color: #e2e8f0; color: #94a3b8; }
    .qa-check-chip .bar { width: 42px; height: 5px; border-radius: 3px; background: #d1fae5; overflow: hidden; }
    .qa-check-chip .bar > i { display: block; height: 100%; background: #10b981; }
    .qa-sales-link { color: #0f766e; text-decoration: none; font-weight: 800; }
    .qa-sales-link:hover { text-decoration: underline; }
    .qa-open-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px; border-radius: 9px; font-size: 11px; font-weight: 700;
        background: #ecfdf5; color: #0f766e; border: 1px solid #a7f3d0; text-decoration: none;
        white-space: nowrap;
    }
    .qa-open-btn:hover { background: #0f766e; color: #fff; border-color: #0f766e; }

    .sales-no { font-weight: 800; color: #0f766e; font-size: 12.5px; }
    .sales-date { font-size: 10.5px; color: #94a3b8; }
    .mock-thumb { width: 46px; height: 46px; border-radius: 9px; object-fit: cover; border: 1px solid #e5e7eb; }
    .mock-empty {
        width: 46px; height: 46px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        background: #f1f5f9; color: #cbd5e1; font-size: 15px;
    }
    .stage-badge {
        display: inline-block; padding: 4px 10px; border-radius: 20px;
        font-size: 10.5px; font-weight: 800; color: #fff; letter-spacing: .4px;
        text-transform: uppercase; white-space: nowrap;
    }
    .qa-chip {
        display: inline-block; font-size: 10px; font-weight: 700;
        padding: 2px 8px; border-radius: 10px; margin-top: 3px;
    }
    .qa-chip.delayed { background: #fee2e2; color: #b91c1c; }
    .qa-chip.prio { background: #fef3c7; color: #92400e; }
    .qa-filterbar {
        background: #fff; border-radius: 14px; padding: 14px 16px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, .06);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    <div class="qa-hero mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4>🧵 QA Job List</h4>
            <div class="sub">Orders tagged <b>SEWING</b> / <b>QA</b> / <b>DISPATCH</b> — read-only view. Kapag na-retag sa ibang stage, wala na agad sa listahan.</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('sales.prototype.backjobs') }}" class="btn btn-sm fw-semibold" style="border-radius:10px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.35);color:#fff;">
                <i class="fas fa-tools me-1"></i> Backjob List
            </a>
        </div>
    </div>

    {{-- Stats --}}
    @php
        $stageMeta = [
            'SEWING'   => ['#0f766e', '🧵', 'Sewing'],
            'QA'       => ['#6f42c1', '🔍', 'QA'],
            'DISPATCH' => ['#0ea5e9', '🚚', 'Dispatch'],
        ];
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="qa-stat">
                <div class="ico" style="background:linear-gradient(135deg,#0f766e,#14b8a6);"><i class="fas fa-list"></i></div>
                <div>
                    <div class="val">{{ $sales->total() }}</div>
                    <div class="lbl">Total Jobs</div>
                </div>
            </div>
        </div>
        @foreach($stageMeta as $st => $meta)
        <div class="col-6 col-md-3">
            <div class="qa-stat">
                <div class="ico" style="background:{{ $meta[0] }};">{{ $meta[1] }}</div>
                <div>
                    <div class="val">{{ $stageTotals[$st] ?? 0 }}</div>
                    <div class="lbl">{{ $meta[2] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('sales.prototype.qa-job-list') }}" class="qa-filterbar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">🔍 Search</label>
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Sales # o customer...">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Stage</label>
                <select name="stage" class="form-select">
                    <option value="">All Stages</option>
                    @foreach($qaStages as $st)
                    <option value="{{ $st }}" {{ $stage === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Department</label>
                <select name="dept" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departmentLabels as $did => $dname)
                    <option value="{{ $did }}" {{ (string)$dept === (string)$did ? 'selected' : '' }}>{{ $dname }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">⏰ Due date</label>
                <select name="due" class="form-select">
                    <option value="">All due dates</option>
                    <option value="overdue" {{ $dueFilter === 'overdue' ? 'selected' : '' }}>🔴 Overdue</option>
                    <option value="today" {{ $dueFilter === 'today' ? 'selected' : '' }}>🟠 Due today</option>
                    <option value="soon" {{ $dueFilter === 'soon' ? 'selected' : '' }}>🟡 Due in 3 days</option>
                    <option value="week" {{ $dueFilter === 'week' ? 'selected' : '' }}>🔵 Due this week</option>
                    <option value="none" {{ $dueFilter === 'none' ? 'selected' : '' }}>⚪ No due date</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Date from</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Date to</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2 justify-content-md-end">
                <button type="submit" class="btn btn-sm px-3 fw-semibold" style="background:linear-gradient(135deg,#0f766e,#14b8a6);border:none;border-radius:9px;color:#fff;">Filter</button>
                <a href="{{ route('sales.prototype.qa-job-list') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:9px;">Reset</a>
            </div>
            <div class="col-12 d-flex gap-2 flex-wrap pt-1">
                <label class="btn btn-sm btn-outline-danger mb-0" style="border-radius:20px;">
                    <input type="checkbox" name="delayed" value="1" {{ $delayedOnly ? 'checked' : '' }} onchange="this.form.submit()" class="form-check-input me-1"> ⏰ Delayed only
                </label>
                <label class="btn btn-sm btn-outline-warning mb-0" style="border-radius:20px;">
                    <input type="checkbox" name="priority" value="1" {{ $priorityOnly ? 'checked' : '' }} onchange="this.form.submit()" class="form-check-input me-1"> ⭐ With priority
                </label>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="qa-table-card">
        <div class="table-responsive" style="max-height:calc(100vh - 330px);overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Sales #</th>
                        <th>Mock Up</th>
                        <th>Description</th>
                        <th class="text-center">Qty</th>
                        <th>Production Status</th>
                        <th>Due Date</th>
                        <th>Department</th>
                        <th>Customer</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        @php
                            $stageLabel = $sale->production_stage ?: ($statusToStage[$sale->kanban_status ?? 'new'] ?? 'HOLD');
                            $deptName = $departmentLabels[$sale->department_id] ?? 'Other';
                            $deptColor = $departmentColors[$sale->department_id] ?? '#6c757d';
                            $stageBg = $stageMeta[$stageLabel][0] ?? '#0f766e';

                            $mockups = is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? []);
                            $mainMockup = null;
                            foreach ((array) $mockups as $m) {
                                if (is_array($m) && !empty($m['is_main'])) { $mainMockup = $m; break; }
                            }
                            if (!$mainMockup && !empty($mockups)) { $mainMockup = is_array($mockups) ? reset($mockups) : null; }
                            $firstMockupUrl = is_string($mainMockup) ? $mainMockup : ($mainMockup['url'] ?? '');

                            $svcItems = is_string($sale->services) ? json_decode($sale->services, true) : ($sale->services ?? []);
                            $descParts = [];
                            $totalQty = 0;
                            foreach ((array) $svcItems as $svc) {
                                if (is_array($svc)) {
                                    $descParts[] = \App\Models\PrototypeSale::itemSpecSummary($svc);
                                    $totalQty += (int) ($svc['quantity'] ?? 1);
                                }
                            }
                            $description = $descParts ? implode(' + ', $descParts) : '—';

                            $effDue = $sale->rescheduled_date ?: $sale->estimated_completion_date;
                            $dueInfo = null;
                            if ($effDue) {
                                $dueD = \Carbon\Carbon::parse($effDue)->startOfDay();
                                $todayD = \Carbon\Carbon::now()->startOfDay();
                                $diffDays = (int) floor(($dueD->getTimestamp() - $todayD->getTimestamp()) / 86400);
                                if ($diffDays < 0)       { $dueInfo = ['#dc3545', '🔴 ' . abs($diffDays) . 'd overdue']; }
                                elseif ($diffDays === 0) { $dueInfo = ['#fd7e14', '🟠 Due today']; }
                                elseif ($diffDays <= 3)  { $dueInfo = ['#f59e0b', '🟡 ' . $diffDays . 'd left']; }
                                elseif ($diffDays <= 7)  { $dueInfo = ['#0ea5e9', '🔵 ' . $diffDays . 'd left']; }
                                else                     { $dueInfo = ['#10b981', '🟢 ' . $diffDays . 'd left']; }
                            }
                        @endphp
                        <tr class="qa-row" data-href="{{ route('sales.prototype.show', $sale->id) }}">
                            <td style="max-width:150px;">
                                <a href="{{ route('sales.prototype.show', $sale->id) }}" class="qa-sales-link" onclick="event.stopPropagation()">{{ $sale->sales_number }}</a>
                                <div class="sales-date">{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y') }}</div>
                                @if($sale->is_delayed)<span class="qa-chip delayed">⏰ Delayed</span>@endif
                                @if(!empty($sale->priority))<span class="qa-chip prio">⭐ Prio {{ $sale->priority }}</span>@endif
                            </td>
                            <td>
                                @if($firstMockupUrl)
                                    <img src="{{ $firstMockupUrl }}" alt="mockup" class="mock-thumb" onerror="this.outerHTML='<div class=&quot;mock-empty&quot;><i class=&quot;fas fa-image&quot;></i></div>'">
                                @else
                                    <div class="mock-empty"><i class="fas fa-image"></i></div>
                                @endif
                            </td>
                            <td style="max-width:280px;">
                                <div style="font-size:12px;line-height:1.4;" title="{{ $description }}">{{ \Illuminate\Support\Str::limit($description, 70) }}</div>
                            </td>
                            <td class="text-center" style="font-weight:700;">{{ $totalQty ?: '—' }}</td>
                            <td>
                                <span class="stage-badge" style="background:{{ $stageBg }};">{{ $stageLabel }}</span>
                                @php
                                    $pcc = $prodCheckCounts[$sale->id] ?? null;
                                    $qa2Done = (int) ($pcc['qa2']['done'] ?? 0);
                                    $qa2Total = (int) ($pcc['qa2']['total'] ?? 0);
                                    $qa2PcsDone = (int) ($pcc['qa2']['pcs_done'] ?? 0);
                                    $qa2PcsTotal = (int) ($pcc['qa2']['pcs_total'] ?? 0);
                                    $qa2Pct = $qa2Total > 0 ? (int) round(($qa2Done / $qa2Total) * 100) : 0;
                                    $qa2Class = $qa2Total === 0 ? 'empty' : ($qa2Done >= $qa2Total ? 'full' : '');
                                @endphp
                                @if($qa2Total > 0)
                                <div>
                                    <span class="qa-check-chip {{ $qa2Class }}" title="QA2 Production slip: {{ $qa2Done }} sa {{ $qa2Total }} checkbox ang naka-check · {{ number_format($qa2PcsDone) }}/{{ number_format($qa2PcsTotal) }} pcs">
                                        <i class="fas fa-check-square"></i> QA2 {{ $qa2Done }}/{{ $qa2Total }}
                                        <span class="bar"><i style="width:{{ $qa2Pct }}%;"></i></span>
                                        <span style="font-weight:700;opacity:.85;">{{ number_format($qa2PcsDone) }}/{{ number_format($qa2PcsTotal) }} pcs</span>
                                    </span>
                                </div>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">
                                @if($effDue)
                                    <div style="font-weight:600;font-size:12px;">{{ \Carbon\Carbon::parse($effDue)->format('M d, Y') }}</div>
                                    <span class="badge" style="background:{{ $dueInfo[0] }};font-size:10px;">{{ $dueInfo[1] }}</span>
                                @else
                                    <span class="text-muted" style="font-size:11px;">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background:{{ $deptColor }};font-size:10.5px;">{{ $deptName }}</span>
                            </td>
                            <td style="max-width:200px;">
                                <div style="font-weight:600;font-size:12px;">{{ $sale->customer_name ?: '—' }}</div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('sales.prototype.show', $sale->id) }}" class="qa-open-btn" onclick="event.stopPropagation()"><i class="fas fa-up-right-from-square"></i> Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-2x d-block mb-2" style="color:#cbd5e1;"></i>
                                Walang job na naka-tag na SEWING / QA / DISPATCH sa ngayon.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $sales->links() }}</div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('tr.qa-row[data-href]').forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('a, button, input, select, label')) return;
            window.location.href = row.dataset.href;
        });
    });
});
</script>
@endpush
@endsection
