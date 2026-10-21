@extends('layouts.app')

@section('title', 'Layout History')

@push('styles')
<style>
    .lh-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #2563eb 100%);
        border-radius: 16px; padding: 20px 26px; color: #fff;
        position: relative; overflow: hidden; box-shadow: 0 8px 24px rgba(30, 58, 138, .25);
    }
    .lh-hero::after {
        content: "🕘"; position: absolute; right: 18px; bottom: -18px;
        font-size: 84px; opacity: .16; transform: rotate(-8deg);
    }
    .lh-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; box-shadow: 0 1px 3px rgba(16,24,40,.06); }
    .lh-muted { color: #6b7280; font-size: 12px; }
    .lh-badge { font-size: 11px; padding: 3px 8px; border-radius: 999px; font-weight: 600; border: 1px solid transparent; }
    .lh-badge.job { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .lh-badge.payout { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
    .lh-badge.created { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .lh-badge.updated { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .lh-badge.verified { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .lh-badge.rejected { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
    .lh-badge.deleted { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
    .lh-change { font-size: 12px; }
    .lh-old { color: #b91c1c; text-decoration: line-through; }
    .lh-new { color: #047857; font-weight: 600; }
    .lh-field { color: #374151; font-weight: 600; }
    .lh-arrow { color: #9ca3af; font-size: 11px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="lh-hero mb-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h4 class="mb-0">🕘 Layout History <span class="lh-muted" style="color:#c7d2fe !important;">(audit trail)</span></h4>
                <div class="lh-muted" style="color:#dbeafe !important;">
                    Bawat galaw sa layout jobs at payouts — sino, kailan, at ano ang luma → bago. Transparent para sa board.
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('sales.layout-jobs.all') }}" class="btn btn-light btn-sm"><i class="fas fa-list"></i> Layout Jobs (All)</a>
                <a href="{{ route('sales.layout-jobs.payouts') }}" class="btn btn-warning btn-sm"><i class="fas fa-hand-holding-usd"></i> Payouts</a>
                <a href="{{ route('sales.layout-jobs.dashboard') }}" class="btn btn-outline-light btn-sm"><i class="fas fa-chart-line"></i> Dashboard</a>
            </div>
        </div>
    </div>

    <div class="lh-card mb-3">
        <form method="GET" class="row g-2 p-3 align-items-end">
            <div class="col-md-2">
                <label class="lh-muted">Job No / Payout</label>
                <input type="text" name="job_no" value="{{ request('job_no') }}" class="form-control form-control-sm" placeholder="LJ-2026-0106">
            </div>
            <div class="col-md-2">
                <label class="lh-muted">Uri</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Lahat</option>
                    <option value="job" {{ request('type') === 'job' ? 'selected' : '' }}>Layout Job</option>
                    <option value="payout" {{ request('type') === 'payout' ? 'selected' : '' }}>Payout</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="lh-muted">Galaw</label>
                <select name="event" class="form-select form-select-sm">
                    <option value="">Lahat</option>
                    @foreach($events as $ev)
                    <option value="{{ $ev }}" {{ request('event') === $ev ? 'selected' : '' }}>{{ $ev }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="lh-muted">Sino</label>
                <select name="user" class="form-select form-select-sm">
                    <option value="">Lahat</option>
                    @foreach($users as $usr)
                    <option value="{{ $usr->id }}" {{ (string) request('user') === (string) $usr->id ? 'selected' : '' }}>{{ $usr->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="lh-muted">Mula</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-1">
                <label class="lh-muted">Hanggang</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
            @if(request()->hasAny(['job_no', 'type', 'event', 'user', 'from', 'to']))
            <div class="col-12">
                <a href="{{ route('sales.layout-jobs.history') }}" class="lh-muted">✕ Alisin ang filter</a>
                <span class="lh-muted">· {{ $logs->total() }} tugma sa filter (kabuuang galaw sa system: {{ number_format($totalMoves) }})</span>
            </div>
            @endif
        </form>
    </div>

    <div class="lh-card">
        <div class="p-3 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="mb-1">Mga Galaw</h6>
                <div class="lh-muted">Pinakabago muna · {{ $logs->total() }} record</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:150px;">Kailan</th>
                        <th style="width:110px;">Uri</th>
                        <th style="width:140px;">Record</th>
                        <th style="width:120px;">Galaw</th>
                        <th>Sino</th>
                        <th>Ano ang nagbago</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="lh-muted">
                            {{ $log->created_at?->timezone('Asia/Manila')->format('M d, Y') }}<br>
                            <span style="font-size:11px;">{{ $log->created_at?->timezone('Asia/Manila')->format('g:i:s A') }}</span>
                        </td>
                        <td><span class="lh-badge {{ $log->subject_type }}">{{ $log->subject_type === 'payout' ? 'Payout' : 'Job' }}</span></td>
                        <td>
                            @if($log->subject_type === 'job' && $log->job_no)
                            <a href="{{ route('sales.layout-jobs.all', ['q' => $log->job_no]) }}" class="fw-bold text-decoration-none">{{ $log->job_no }}</a>
                            @elseif($log->subject_type === 'payout')
                            <span class="fw-bold">Payout #{{ $log->subject_id }}</span>
                            @else
                            <span class="lh-muted">#{{ $log->subject_id }}</span>
                            @endif
                        </td>
                        <td><span class="lh-badge {{ $log->event }}">{{ $log->eventLabel() }}</span></td>
                        <td>
                            {{ $log->user_name ?: '—' }}
                            @if($log->ip)
                            <div class="lh-muted" style="font-size:10.5px;">{{ $log->ip }}</div>
                            @endif
                        </td>
                        <td class="lh-change">
                            @if(!empty($log->changes))
                                @foreach($log->changes as $c)
                                <div>
                                    <span class="lh-field">{{ $c['label'] ?? $c['field'] ?? '' }}:</span>
                                    @if(($c['old'] ?? '—') !== '—')
                                    <span class="lh-old">{{ $c['old'] }}</span>
                                    <span class="lh-arrow">→</span>
                                    @endif
                                    <span class="lh-new">{{ $c['new'] ?? '—' }}</span>
                                </div>
                                @endforeach
                            @else
                                <span class="lh-muted">—</span>
                            @endif
                            @if(!empty($log->meta))
                            <div class="lh-muted" style="font-size:11px;">
                                @foreach($log->meta as $k => $v)
                                {{ $k }}: {{ is_scalar($v) ? $v : json_encode($v) }}@if(!$loop->last) · @endif
                                @endforeach
                            </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center lh-muted py-4">
                            Wala pang naka-log na galaw. Magsisimula itong mapuno sa susunod na pagbabago sa layout jobs at payouts.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="p-3">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
