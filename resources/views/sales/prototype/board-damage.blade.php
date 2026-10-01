@extends('layouts.app')

@section('title', 'Board Member — Damage Report')

@section('page-title', 'Board Member')

@push('styles')
<style>
    .board-header {
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
        gap: 12px;
        background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 60%, #7c3aed 100%);
        border-radius: 14px; padding: 18px 24px;
        box-shadow: 0 4px 16px rgba(76, 29, 149, 0.25); margin-bottom: 18px;
    }
    .board-header h4 { color: #fff; margin: 0; font-weight: 800; }
    .board-header .board-sub { color: rgba(255,255,255,0.85); font-size: 12px; margin-top: 4px; }
    .board-tiles { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
    .board-tile { flex: 1 1 180px; background: #fff; border: 1px solid #ede9fe; border-radius: 12px; padding: 14px 16px; }
    .board-tile .t-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .03em; }
    .board-tile .t-value { font-size: 20px; font-weight: 800; color: #111827; margin-top: 4px; }
    .board-tile .t-sub { font-size: 11px; color: #9ca3af; margin-top: 2px; }
    .board-tile.dmg { border-color: #fecaca; } .board-tile.dmg .t-value { color: #b91c1c; }
    .dmg-badge { font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 10px; white-space: nowrap; }
    .dmg-minor { background: #e5e7eb; color: #374151; }
    .dmg-major { background: #ffedd5; color: #9a3412; }
    .dmg-critical { background: #fee2e2; color: #991b1b; }
    .dmg-status { font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 10px; }
    .board-money { font-variant-numeric: tabular-nums; }
</style>
@endpush

@section('content')
<div class="board-header">
    <div>
        <h4><i class="fas fa-crown"></i> Board Member</h4>
        <div class="board-sub">Damage Report — view-only. Hiwalay sa sales; hindi kasama sa sales totals.</div>
    </div>
</div>

@include('sales.prototype._board-tabs')

{{-- Summary tiles --}}
<div class="board-tiles">
    <div class="board-tile dmg">
        <div class="t-label"><i class="fas fa-exclamation-triangle"></i> Total Reports</div>
        <div class="t-value">{{ $dmgTotals['count'] }}</div>
        <div class="t-sub">{{ $dmgTotals['open'] }} open · {{ $dmgTotals['resolved'] }} resolved · {{ $dmgTotals['dismissed'] }} dismissed</div>
    </div>
    <div class="board-tile dmg">
        <div class="t-label"><i class="fas fa-clock"></i> Open Reports</div>
        <div class="t-value">{{ $dmgTotals['open'] }}</div>
        <div class="t-sub">Submitted / Under review / Issued / Acknowledged / Contested</div>
    </div>
    <div class="board-tile dmg">
        <div class="t-label"><i class="fas fa-peso-sign"></i> Total Damage Amount</div>
        <div class="t-value">₱{{ number_format($dmgTotals['amount'], 2) }}</div>
        <div class="t-sub">Kabuuan ng damage_amount</div>
    </div>
    <div class="board-tile dmg">
        <div class="t-label"><i class="fas fa-star-half-alt"></i> Total Points</div>
        <div class="t-value">{{ $dmgTotals['points'] }}</div>
        <div class="t-sub">Minor=1 · Major=2 · Critical=3</div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('sales.prototype.board.damage') }}" class="row g-2 align-items-center">
            <div class="col-auto">
                <input type="text" name="dmg_q" value="{{ request('dmg_q') }}" class="form-control form-control-sm" placeholder="Search report # / description" style="min-width:220px;">
            </div>
            <div class="col-auto">
                <select name="dmg_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">All statuses</option>
                    @foreach(\App\Models\DamageReport::STATUSES as $val => $label)
                        <option value="{{ $val }}" {{ request('dmg_status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="dmg_severity" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All severities</option>
                    @foreach(\App\Models\DamageReport::SEVERITIES as $val => $label)
                        <option value="{{ $val }}" {{ request('dmg_severity') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="dmg_shop" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All shops</option>
                    @foreach($shops as $s)
                        <option value="{{ $s->id }}" {{ (string) request('dmg_shop') === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="dmg_date_from" value="{{ request('dmg_date_from') }}" class="form-control form-control-sm" title="From date">
            </div>
            <div class="col-auto">
                <input type="date" name="dmg_date_to" value="{{ request('dmg_date_to') }}" class="form-control form-control-sm" title="To date">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('sales.prototype.board.damage') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Report #</th>
                        <th>Sale #</th>
                        <th>Shop</th>
                        <th>Category</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Points</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>
                                <a href="{{ route('sales.prototype.board.damage.show', $report->id) }}" style="font-weight:700;color:#4c1d95;text-decoration:none;">
                                    {{ $report->report_no }}
                                </a>
                            </td>
                            <td>
                                @if($report->sale)
                                    <a href="{{ route('sales.prototype.show', $report->sale_id) }}" style="text-decoration:none;" title="Tingnan ang sale">
                                        {{ $report->sale->sales_number ?: ('#'.$report->sale_id) }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $report->shop->name ?? '—' }}</td>
                            <td>{{ \App\Models\DamageReport::CATEGORIES[$report->category] ?? $report->category }}</td>
                            <td><span class="dmg-badge dmg-{{ $report->severity }}">{{ \App\Models\DamageReport::SEVERITIES[$report->severity] ?? $report->severity }}</span></td>
                            <td><span class="dmg-status badge bg-info">{{ \App\Models\DamageReport::STATUSES[$report->status] ?? $report->status }}</span></td>
                            <td class="text-end board-money">{{ $report->damage_amount !== null ? '₱'.number_format($report->damage_amount, 2) : '—' }}</td>
                            <td class="text-end board-money">{{ $report->points }}</td>
                            <td>{{ $report->created_at ? $report->created_at->format('M d, Y') : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Walang damage report na tumutugma sa filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($reports->hasPages())
        <div class="card-footer bg-white py-2">{{ $reports->links() }}</div>
    @endif
</div>
@endsection
