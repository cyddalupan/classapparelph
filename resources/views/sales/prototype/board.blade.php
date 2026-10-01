@extends('layouts.app')

@section('title', 'Board Member')

@section('page-title', 'Board Member')

@push('styles')
<style>
    .board-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 60%, #7c3aed 100%);
        border-radius: 14px;
        padding: 18px 24px;
        box-shadow: 0 4px 16px rgba(76, 29, 149, 0.25);
        margin-bottom: 18px;
    }
    .board-header h4 { margin: 0; color: #fff; font-weight: 800; display: flex; align-items: center; gap: 10px; }
    .board-header .board-sub { color: rgba(255,255,255,.78); font-size: 13px; margin-top: 3px; }
    .board-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; margin-bottom: 18px; }
    .board-tile { background: #fff; border: 1px solid #eef0f3; border-radius: 12px; padding: 14px 16px; box-shadow: 0 1px 4px rgba(16,24,40,.05); }
    .board-tile .t-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; display: flex; align-items: center; gap: 6px; }
    .board-tile .t-value { font-size: 22px; font-weight: 800; margin-top: 6px; color: #111827; }
    .board-tile .t-sub { font-size: 12px; color: #6b7280; margin-top: 2px; }
    .board-tile.receivable .t-value { color: #dc2626; }
    .board-tile.collected .t-value { color: #059669; }
    .board-tile.sales .t-value { color: #4c1d95; }
    .board-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
    .board-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; border: 1px solid #e5e7eb; background: #fff; color: #374151; text-decoration: none; }
    .board-chip:hover { border-color: #7c3aed; color: #7c3aed; }
    .board-chip.active { background: #7c3aed; border-color: #7c3aed; color: #fff; }
    .board-chip .cnt { font-size: 11px; opacity: .8; font-weight: 600; }
    .board-table { font-size: 13px; }
    .board-table th { white-space: nowrap; background: #f8f9fa; font-size: 11px; text-transform: uppercase; letter-spacing: .03em; color: #6b7280; }
    .board-table td { vertical-align: middle; }
    .board-money { font-variant-numeric: tabular-nums; white-space: nowrap; }
    .board-balance-zero { color: #9ca3af; }
    .board-state { font-size: 10px; font-weight: 800; padding: 3px 9px; border-radius: 10px; text-transform: uppercase; letter-spacing: .03em; }
    .board-state.paid { background: #d1fae5; color: #065f46; }
    .board-state.partial { background: #fef3c7; color: #92400e; }
    .board-state.unpaid { background: #fee2e2; color: #991b1b; }
    .board-dept-pill { font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 10px; background: #ede9fe; color: #5b21b6; }
    .board-stage { font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 10px; background: #e0f2fe; color: #075985; white-space: nowrap; }
</style>
@endpush

@section('content')
<div class="board-header">
    <div>
        <h4><i class="fas fa-crown"></i> Board Member</h4>
        <div class="board-sub">Sales overview — lahat ng department, sama-sama. Pending at hindi pa bayad, at magkano pa ang sisingilin.</div>
    </div>
</div>

@include('sales.prototype._board-tabs')

{{-- Summary tiles --}}
<div class="board-tiles">
    <div class="board-tile sales">
        <div class="t-label"><i class="fas fa-chart-line"></i> Total Sales</div>
        <div class="t-value">₱{{ number_format($totals['sales'], 2) }}</div>
        <div class="t-sub">{{ $totals['count'] }} project(s)</div>
    </div>
    <div class="board-tile collected">
        <div class="t-label"><i class="fas fa-hand-holding-usd"></i> Total Collected</div>
        <div class="t-value">₱{{ number_format($totals['collected'], 2) }}</div>
        <div class="t-sub">Verified payments (net of refunds)</div>
    </div>
    <div class="board-tile receivable">
        <div class="t-label"><i class="fas fa-file-invoice-dollar"></i> Total Receivable</div>
        <div class="t-value">₱{{ number_format($totals['receivable'], 2) }}</div>
        <div class="t-sub">Balanse pang sisingilin</div>
    </div>
    <div class="board-tile">
        <div class="t-label"><i class="fas fa-hourglass-half"></i> Pending Verification</div>
        <div class="t-value">₱{{ number_format($pendingVerify['amount'], 2) }}</div>
        <div class="t-sub">{{ $pendingVerify['count'] }} payment(s) — hindi pa na-verify</div>
    </div>
    <div class="board-tile">
        <div class="t-label"><i class="fas fa-clock"></i> Payment Status</div>
        <div class="t-value" style="font-size:15px;">{{ $stateStats['unpaid'] }} unpaid · {{ $stateStats['partial'] }} partial · {{ $stateStats['paid'] }} paid</div>
        <div class="t-sub">Batay sa verified payments</div>
    </div>
</div>

{{-- Filters --}}
<div class="board-chips">
    <a href="{{ route('sales.prototype.board', array_merge(request()->except('department'), [])) }}" class="board-chip {{ !request('department') ? 'active' : '' }}">
        <i class="fas fa-layer-group"></i> All Departments
        <span class="cnt">({{ $totals['count'] }})</span>
    </a>
    @foreach($departments as $d)
        @php $ds = $deptStats[$d->id] ?? null; @endphp
        <a href="{{ route('sales.prototype.board', array_merge(request()->except('department'), ['department' => $d->id])) }}"
           class="board-chip {{ (string) request('department') === (string) $d->id ? 'active' : '' }}">
            {{ $d->name }}
            <span class="cnt">({{ $ds['count'] ?? 0 }})</span>
        </a>
    @endforeach
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('sales.prototype.board') }}" class="row g-2 align-items-center">
            @if(request('department'))<input type="hidden" name="department" value="{{ request('department') }}">@endif
            <div class="col-auto">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search customer / agent / sales no." style="min-width:240px;">
            </div>
            <div class="col-auto">
                <select name="agent" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All agents</option>
                    @foreach($agentOptions as $a)
                        <option value="{{ $a }}" {{ request('agent') === $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="stage" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach($stageOptions as $s)
                        <option value="{{ $s }}" {{ request('stage') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" title="From date">
            </div>
            <div class="col-auto">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" title="To date">
            </div>
            <div class="col-auto">
                <select name="payment_state" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All payment states</option>
                    <option value="unpaid" {{ request('payment_state') === 'unpaid' ? 'selected' : '' }}>Unpaid (wala pang bayad)</option>
                    <option value="partial" {{ request('payment_state') === 'partial' ? 'selected' : '' }}>Partial (may natitira)</option>
                    <option value="paid" {{ request('payment_state') === 'paid' ? 'selected' : '' }}>Fully Paid</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="archived" class="form-select form-select-sm" onchange="this.form.submit()" title="Archive filter">
                    <option value="all" {{ $archiveFilter === 'all' ? 'selected' : '' }}>All (active + archived)</option>
                    <option value="active" {{ $archiveFilter === 'active' ? 'selected' : '' }}>Active only</option>
                    <option value="archived" {{ $archiveFilter === 'archived' ? 'selected' : '' }}>Archived only</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="cols" class="form-select form-select-sm" onchange="this.form.submit()" title="Columns to show">
                    <option value="compact" {{ $showPaid ? '' : 'selected' }}>Columns: Compact</option>
                    <option value="all" {{ $showPaid ? 'selected' : '' }}>Columns: All (with Paid)</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('sales.prototype.board') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover board-table mb-0">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Project / Sale #</th>
                        <th>Customer</th>
                        <th>Department</th>
                        <th>Production Status</th>
                        <th>Date</th>
                        <th class="text-end">Total</th>
                        @if($showPaid)<th class="text-end">Paid</th>@endif
                        <th class="text-end">Balance</th>
                        <th class="text-end">Days Unpaid</th>
                        <th>State</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td>
                                @if($r['agent_user'] || $r['agent'])
                                    <x-user-chip :user="$r['agent_user']" :name="$r['agent']" :size="18" />
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('sales.prototype.show', $r['id']) }}" style="font-weight:700;color:#4c1d95;text-decoration:none;" title="Tingnan ang sale">
                                    {{ $r['sales_number'] ?: ('#'.$r['id']) }}
                                </a>
                                @if($r['archived'])<span class="badge bg-secondary" style="font-size:9px;">Archived</span>@endif
                            </td>
                            <td title="{{ $r['customer'] }}">{{ $r['customer_first'] ?: $r['customer'] }}</td>
                            <td><span class="board-dept-pill">{{ $r['department'] }}</span></td>
                            <td><span class="board-stage">{{ $r['stage'] ?: '—' }}</span></td>
                            <td class="board-money">{{ optional($r['created_at'])->format('M d, Y') }}</td>
                            <td class="text-end board-money">₱{{ number_format($r['total'], 2) }}</td>
                            @if($showPaid)<td class="text-end board-money">₱{{ number_format($r['paid'], 2) }}</td>@endif
                            <td class="text-end board-money {{ $r['balance'] <= 0 ? 'board-balance-zero' : '' }}" style="{{ $r['balance'] > 0 ? 'color:#dc2626;font-weight:800;' : '' }}">
                                ₱{{ number_format($r['balance'], 2) }}
                            </td>
                            <td class="text-end board-money">{{ $r['days_unpaid'] === null ? '—' : $r['days_unpaid'] . 'd' }}</td>
                            <td><span class="board-state {{ $r['state'] }}">{{ $r['state'] }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showPaid ? 11 : 10 }}" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Walang sale na tumutugma sa filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($rows))
                <tfoot>
                    <tr style="background:#f8f9fa;font-weight:800;">
                        <td colspan="6" class="text-end">TOTAL ({{ $totals['count'] }})</td>
                        <td class="text-end board-money">₱{{ number_format($totals['sales'], 2) }}</td>
                        @if($showPaid)<td class="text-end board-money">₱{{ number_format($totals['collected'], 2) }}</td>@endif
                        <td class="text-end board-money" style="color:#dc2626;">₱{{ number_format($totals['receivable'], 2) }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
