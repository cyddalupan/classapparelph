@extends('layouts.app')

@section('title', 'Damage Report ' . $report->report_no)

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
    .ev-img { max-height: 360px; object-fit: contain; }
    .dl-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .03em; }
    .dl-value { font-size: 14px; color: #111827; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="board-header">
    <div>
        <h4><i class="fas fa-crown"></i> Board Member</h4>
        <div class="board-sub">Damage Report — view-only. Hiwalay sa sales.</div>
    </div>
</div>

@include('sales.prototype._board-tabs')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="mb-1"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>{{ $report->report_no }}</h5>
        <p class="text-muted mb-0 small">Filed by {{ $report->reporter->name ?? 'Unknown' }} · {{ $report->created_at ? $report->created_at->format('M d, Y h:i A') : '—' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sales.prototype.board.damage') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to list
        </a>
        @if($report->sale)
            <a href="{{ route('sales.prototype.show', $report->sale_id) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-external-link-alt me-1"></i>{{ $report->sale->sales_number }}
            </a>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Report Details</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-sm-4"><div class="dl-label">Shop</div><div class="dl-value">{{ $report->shop->name ?? '—' }}</div></div>
                    <div class="col-sm-4"><div class="dl-label">Category</div><div class="dl-value">{{ \App\Models\DamageReport::CATEGORIES[$report->category] ?? $report->category }}</div></div>
                    <div class="col-sm-4"><div class="dl-label">Severity</div><div class="dl-value">{{ \App\Models\DamageReport::SEVERITIES[$report->severity] ?? $report->severity }}</div></div>
                    <div class="col-sm-4"><div class="dl-label">Status</div><div class="dl-value">{{ \App\Models\DamageReport::STATUSES[$report->status] ?? $report->status }}</div></div>
                    <div class="col-sm-4"><div class="dl-label">Quantity</div><div class="dl-value">{{ $report->quantity !== null ? $report->quantity . ' pc' : '—' }}</div></div>
                    <div class="col-sm-4"><div class="dl-label">Points</div><div class="dl-value">{{ $report->points }}</div></div>
                    <div class="col-sm-4"><div class="dl-label">Damage Amount</div><div class="dl-value">{{ $report->damage_amount !== null ? '₱'.number_format($report->damage_amount, 2) : '—' }}</div></div>
                    <div class="col-sm-4"><div class="dl-label">Involved Position</div><div class="dl-value">{{ $report->involved_position ?: '—' }}</div></div>
                    <div class="col-sm-4"><div class="dl-label">Involved Name</div><div class="dl-value">{{ $report->involved_name ?: '—' }}</div></div>
                </div>
                <div class="mb-3">
                    <div class="dl-label mb-1">Description</div>
                    <div style="white-space:pre-line;">{{ $report->description }}</div>
                </div>
                @if($report->review_notes)
                    <div class="mb-3">
                        <div class="dl-label mb-1">Review Notes</div>
                        <div style="white-space:pre-line;">{{ $report->review_notes }}</div>
                    </div>
                @endif
                @if($report->evidence_path)
                    <div>
                        <div class="dl-label mb-1">Evidence</div>
                        <a href="{{ $report->evidence_path }}" target="_blank">
                            <img src="{{ $report->evidence_path }}" alt="Damage evidence" class="ev-img img-fluid rounded border">
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Comments</div>
            <div class="card-body">
                @forelse($report->comments as $comment)
                    <div class="border-bottom pb-2 mb-2">
                        <strong>{{ $comment->user->name ?? 'Unknown' }}</strong>
                        <span class="text-muted small ms-2">{{ $comment->created_at ? $comment->created_at->format('M d, Y h:i A') : '' }}</span>
                        <div class="mt-1" style="white-space:pre-line;">{{ $comment->comment }}</div>
                    </div>
                @empty
                    <p class="text-muted mb-0 small">Wala pang comments.</p>
                @endforelse
                <p class="text-muted small mt-3 mb-0"><i class="fas fa-lock me-1"></i>View-only — hindi pwedeng mag-comment o mag-aksyon dito.</p>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">People</div>
            <div class="card-body">
                <div class="mb-2"><div class="dl-label">Reporter</div><div class="dl-value">{{ $report->reporter->name ?? '—' }}</div></div>
                <div class="mb-2"><div class="dl-label">Reviewer</div><div class="dl-value">{{ $report->reviewer->name ?? '—' }}</div></div>
                <div><div class="dl-label">Sale</div><div class="dl-value">
                    @if($report->sale)
                        <a href="{{ route('sales.prototype.show', $report->sale_id) }}">{{ $report->sale->sales_number }}</a>
                    @else — @endif
                </div></div>
            </div>
        </div>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Timeline</div>
            <div class="card-body small">
                <div class="mb-2"><span class="dl-label">Filed</span><br>{{ $report->created_at ? $report->created_at->format('M d, Y h:i A') : '—' }}</div>
                <div class="mb-2"><span class="dl-label">Acknowledged</span><br>{{ $report->acknowledged_at ? $report->acknowledged_at->format('M d, Y h:i A') : '—' }}</div>
                <div><span class="dl-label">Resolved</span><br>{{ $report->resolved_at ? $report->resolved_at->format('M d, Y h:i A') : '—' }}</div>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Accountable</div>
            <div class="card-body">
                @forelse($report->accountableUsers as $au)
                    <div class="mb-1 small"><i class="fas fa-user me-1"></i>{{ $au->user->name ?? '—' }}</div>
                @empty
                    <p class="text-muted mb-0 small">Walang naka-tag.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
