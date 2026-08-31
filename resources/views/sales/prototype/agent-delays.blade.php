@extends('layouts.app')

@section('title', 'My Delays | Sales Team')

@section('content')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .page-header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
    .page-header h1 i { color: #dc2626; }
    .page-subtitle { color: #64748b; margin: 0; font-size: 0.9rem; }
    .action-btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration: none; border: 1px solid #e2e8f0; background: #fff; color: #475569; transition: all .15s ease; }
    .action-btn:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    .dl-card { background: #fff; border-radius: 14px; padding: 1.1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); margin-bottom: 1rem; border-left: 5px solid #adb5bd; }
    .dl-card.pending { border-left-color: #dc3545; }
    .dl-card.acknowledged { border-left-color: #f59e0b; }
    .dl-card.resolved { border-left-color: #22c55e; }
    .dl-card.dismissed { border-left-color: #6c757d; }
    .status-badge { display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 700; padding: .25rem .6rem; border-radius: 999px; color: #fff; }
    .fb-box { background: #fff5f5; border: 1px solid #f5c6cb; border-radius: 10px; padding: .6rem .8rem; font-size: .85rem; }
    .review-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; padding: .6rem .8rem; font-size: .85rem; }
    .muted-sm { font-size: .75rem; color: #94a3b8; }
</style>

<div class="container-fluid py-4">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-exclamation-triangle"></i> My Delays</h1>
            <p class="page-subtitle">Lahat ng delay na na-report mo — makikita mo dito kung acknowledged, resolved, o dismissed na ng manager.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.team.dashboard') }}" class="action-btn">
                <i class="fas fa-arrow-left"></i> Back to My Sales
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger py-2">{{ session('error') }}</div>
    @endif

    @php
        $pendingCount = $delays->whereNull('delay_review_status')->count();
        $ackCount = $delays->where('delay_review_status', 'acknowledged')->count();
        $resolvedCount = $delays->where('delay_review_status', 'resolved')->count();
        $dismissedCount = $delays->where('delay_review_status', 'dismissed')->count();
    @endphp

    <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="badge bg-danger" style="font-size:.8rem;">⏳ Pending: {{ $pendingCount }}</span>
        <span class="badge" style="background:#f59e0b;font-size:.8rem;">👀 Acknowledged: {{ $ackCount }}</span>
        <span class="badge bg-success" style="font-size:.8rem;">✅ Resolved: {{ $resolvedCount }}</span>
        <span class="badge bg-secondary" style="font-size:.8rem;">❌ Dismissed: {{ $dismissedCount }}</span>
    </div>

    @forelse($delays as $sale)
        @php
            $st = $sale->delay_review_status; // null | acknowledged | resolved | dismissed
            $cardCls = $st ?? 'pending';
            $badge = match ($st) {
                'acknowledged' => ['👀 Acknowledged', '#f59e0b'],
                'resolved' => ['✅ Resolved', '#22c55e'],
                'dismissed' => ['❌ Dismissed', '#6c757d'],
                default => ['⏳ Pending review', '#dc3545'],
            };
            $reviewerName = $sale->delay_reviewed_by ? ($reviewerNames[$sale->delay_reviewed_by] ?? 'Reviewer') : null;
        @endphp
        <div class="dl-card {{ $cardCls }}">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <div class="fw-bold">{{ $sale->sales_number }}</div>
                    <div class="muted-sm">{{ $sale->customer_name ?: '—' }} • {{ $sale->department_name ?: '—' }}</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="status-badge" style="background:{{ $badge[1] }};">{{ $badge[0] }}</span>
                    <a href="{{ route('sales.prototype.show', $sale->id) }}" class="action-btn" style="padding:4px 12px;font-size:.75rem;"><i class="fas fa-eye me-1"></i> View Order</a>
                </div>
            </div>
            <div class="mt-2 muted-sm">
                <i class="far fa-clock me-1"></i>Na-report: {{ $sale->delayed_at ? \Carbon\Carbon::parse($sale->delayed_at)->format('M d, Y h:i A') : '—' }}
            </div>
            <div class="mt-2">
                <div class="muted-sm mb-1"><i class="fas fa-comment-dots me-1"></i>Iyong feedback/reason:</div>
                <div class="fb-box">{{ $sale->delay_feedback ?: 'Walang feedback na iniwan.' }}</div>
            </div>
            @if($sale->delay_review_status)
            <div class="mt-2">
                <div class="muted-sm mb-1"><i class="fas fa-clipboard-check me-1"></i>Review ng manager:</div>
                <div class="review-box">
                    <div>{{ $sale->delay_review_notes ?: 'Walang notes na iniwan.' }}</div>
                    <div class="muted-sm mt-1">
                        — {{ $reviewerName }} @if($sale->delay_reviewed_at) • {{ \Carbon\Carbon::parse($sale->delay_reviewed_at)->format('M d, Y h:i A') }} @endif
                    </div>
                </div>
            </div>
            @else
            <div class="mt-2 review-box" style="background:#f8fafc;border-color:#e2e8f0;color:#64748b;">
                <i class="fas fa-hourglass-half me-1"></i>Hindi pa nire-review ng manager. Abangan ang notification.
            </div>
            @endif
        </div>
    @empty
        <div class="text-center py-5" style="color:#94a3b8;">
            <div style="font-size:2.5rem;">🎉</div>
            <p class="mb-0">Wala kang na-report na delay. Sana manatiling ganyan!</p>
        </div>
    @endforelse
</div>
@endsection
