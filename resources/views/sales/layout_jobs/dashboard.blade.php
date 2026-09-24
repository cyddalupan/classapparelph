@extends('layouts.app')

@section('title', 'Layout Dashboard')

@push('styles')
<style>
    .lj-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #2563eb 100%);
        border-radius: 16px; padding: 20px 26px; color: #fff; position: relative; overflow: hidden;
        box-shadow: 0 8px 24px rgba(30, 58, 138, .25);
    }
    .lj-hero::after { content: "📊"; position: absolute; right: 18px; bottom: -18px; font-size: 84px; opacity: .16; transform: rotate(-8deg); }
    .lj-hero h4 { font-weight: 800; letter-spacing: .3px; }
    .lj-hero .sub { opacity: .92; font-size: 12.5px; }
    .lj-stat { background: #fff; border: 1px solid #eef0f4; border-radius: 14px; padding: 12px 16px; box-shadow: 0 2px 10px rgba(17,24,39,.05); height: 100%; }
    .lj-stat .val { font-size: 22px; font-weight: 800; line-height: 1.1; color: #111827; }
    .lj-stat .lbl { font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
    .lj-card { background: #fff; border: 1px solid #eef0f4; border-radius: 14px; box-shadow: 0 2px 10px rgba(17,24,39,.04); overflow: hidden; }
    .lj-muted { color: #6b7280; font-size: .8rem; }
    .lj-amount { font-size: 14px; font-weight: 800; color: #111827; white-space: nowrap; }
    .lj-money { color: #047857; }
    .lj-money-amber { color: #b45309; }
    .lj-money-blue { color: #1d4ed8; }
    .lj-badge { font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 20px; letter-spacing: .3px; white-space: nowrap; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="lj-hero mb-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h4 class="mb-0">📊 Layout Dashboard</h4>
                <div class="sub">Kita at bayad ng mga layout-doer (GA) — nakamagkano na, nabigay na, at pwede pa makuha.</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('sales.layout-jobs.all') }}" class="btn btn-outline-light btn-sm"><i class="fas fa-list"></i> Layout Job List (All)</a>
                <a href="{{ route('sales.layout-jobs') }}" class="btn btn-outline-light btn-sm"><i class="fas fa-user"></i> My Layout Jobs</a>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="lj-card mb-3">
        <form method="GET" action="{{ route('sales.layout-jobs.dashboard') }}" class="p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="lj-muted fw-bold">Layout Doer (GA)</label>
                    <select name="ga" class="form-select form-select-sm">
                        <option value="">Lahat ng GA</option>
                        @foreach($gaUsers as $g)
                        <option value="{{ $g->id }}" {{ (string)$gaFilter === (string)$g->id ? 'selected' : '' }}>{{ $g->name }}{{ $g->position ? ' · ' . $g->position : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="lj-muted fw-bold">Period</label>
                    <select name="period" id="periodSel" class="form-select form-select-sm" onchange="document.getElementById('customRange').style.display = (this.value==='custom') ? 'flex' : 'none';">
                        <option value="all" {{ $period === 'all' ? 'selected' : '' }}>All time</option>
                        <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This month</option>
                        <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Last month</option>
                        <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>This year</option>
                        <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom range</option>
                    </select>
                </div>
                <div class="col-md-4" id="customRange" style="display: {{ $period === 'custom' ? 'flex' : 'none' }}; gap: 6px;">
                    <div class="flex-fill">
                        <label class="lj-muted fw-bold">From</label>
                        <input type="date" name="from" value="{{ $fromIn }}" class="form-control form-control-sm">
                    </div>
                    <div class="flex-fill">
                        <label class="lj-muted fw-bold">To</label>
                        <input type="date" name="to" value="{{ $toIn }}" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Apply</button>
                    <a href="{{ route('sales.layout-jobs.dashboard') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </div>
            @if($dFrom || $dTo)
            <div class="lj-muted mt-2">Period na sinusundan: <strong>{{ $dFrom ?: '—' }}</strong> hanggang <strong>{{ $dTo ?: '—' }}</strong>. Ang <em>Available</em> ay kasalukuyang balanse (all-time), hindi period-based.</div>
            @endif
        </form>
    </div>

    {{-- Totals --}}
    <div class="row g-2 mb-3">
        <div class="col-md-3"><div class="lj-stat"><div class="lbl">Total Earned (Kita)</div><div class="val lj-money">₱{{ number_format($totals['earned'], 2) }}</div><div class="lj-muted">{{ $totals['jobs'] }} tapos na layout job</div></div></div>
        <div class="col-md-3"><div class="lj-stat"><div class="lbl">Nabigay Na</div><div class="val lj-money-blue">₱{{ number_format($totals['given'], 2) }}</div><div class="lj-muted">paid + verified payout</div></div></div>
        <div class="col-md-3"><div class="lj-stat"><div class="lbl">Pending Request</div><div class="val lj-money-amber">₱{{ number_format($totals['pending'], 2) }}</div><div class="lj-muted">hiniling pa lang, di pa nabayaran</div></div></div>
        <div class="col-md-3"><div class="lj-stat"><div class="lbl">Available (Pwede Pa Makuha)</div><div class="val">₱{{ number_format($totals['available'], 2) }}</div><div class="lj-muted">kasalukuyang balanse</div></div></div>
    </div>

    {{-- Per-GA table --}}
    <div class="lj-card">
        <div class="p-3 pb-0"><h6 class="mb-1">Per Layout Doer</h6><div class="lj-muted mb-2">Earned = done + naka-link sa sales + (bayad verified | libre na may amount). Available = earned (all-time) − reserved (requested/paid/verified).</div></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Layout Doer</th>
                        <th class="text-center">Jobs Done</th>
                        <th class="text-end">Earned</th>
                        <th class="text-end">Nabigay Na</th>
                        <th class="text-end">Pending</th>
                        <th class="text-end">Available</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $r)
                    <tr>
                        <td>
                            <strong>{{ $r->name }}</strong>
                            @if($r->position)<span class="lj-badge ms-1">{{ $r->position }}</span>@endif
                        </td>
                        <td class="text-center">{{ $r->jobs }}</td>
                        <td class="text-end lj-amount lj-money">₱{{ number_format($r->earned, 2) }}</td>
                        <td class="text-end lj-amount lj-money-blue">₱{{ number_format($r->given, 2) }}</td>
                        <td class="text-end lj-amount lj-money-amber">₱{{ number_format($r->pending, 2) }}</td>
                        <td class="text-end lj-amount">₱{{ number_format($r->available, 2) }}</td>
                        <td class="text-end"><a href="{{ route('sales.layout-jobs.all', ['ga' => $r->id]) }}" class="btn btn-outline-primary btn-sm" style="font-size:11.5px;">Tingnan jobs</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center lj-muted py-4">Walang layout doer na tugma sa filter.</td></tr>
                @endforelse
                </tbody>
                @if(count($rows))
                <tfoot class="table-light">
                    <tr>
                        <th>TOTAL</th>
                        <th class="text-center">{{ $totals['jobs'] }}</th>
                        <th class="text-end lj-money">₱{{ number_format($totals['earned'], 2) }}</th>
                        <th class="text-end lj-money-blue">₱{{ number_format($totals['given'], 2) }}</th>
                        <th class="text-end lj-money-amber">₱{{ number_format($totals['pending'], 2) }}</th>
                        <th class="text-end">₱{{ number_format($totals['available'], 2) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
