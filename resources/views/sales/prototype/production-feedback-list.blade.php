@extends('layouts.app')

@section('page-title', 'Production Feedback')

@push('styles')
<style>
    .fb-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px; margin-bottom:12px; }
    .fb-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.06); }
    .stat-pill { padding:6px 14px; border-radius:20px; font-size:13px; font-weight:600; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1" style="font-weight:700;color:#1e293b;"><i class="fas fa-clipboard-check me-2" style="color:#d97706;"></i>Production Feedback</h4>
            <p class="text-muted mb-0" style="font-size:13px;">Lahat ng feedback na binigay mo sa mga sales agents — para ma-track ang production delays dulot ng kulang na impormasyon.</p>
        </div>
        <a href="{{ route('sales.prototype.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <!-- Status pills -->
    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('sales.prototype.production-feedback.list') }}" class="stat-pill text-decoration-none" style="background:{{ !request('status') ? '#1e293b' : '#f1f5f9' }};color:{{ !request('status') ? '#fff' : '#334155' }};">All ({{ array_sum($statusCounts) }})</a>
        @foreach(['open' => '#d97706', 'acknowledged' => '#2563eb', 'resolved' => '#059669'] as $st => $color)
        <a href="{{ route('sales.prototype.production-feedback.list', ['status' => $st]) }}" class="stat-pill text-decoration-none" style="background:{{ request('status') === $st ? $color : '#f1f5f9' }};color:{{ request('status') === $st ? '#fff' : '#334155' }};">{{ ucfirst($st) }} ({{ $statusCounts[$st] ?? 0 }})</a>
        @endforeach
    </div>

    <!-- Filters -->
    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <select name="agent_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All agents</option>
                @foreach($agents as $agent)
                <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All categories</option>
                @foreach(\App\Models\ProductionFeedback::CATEGORIES as $val => $label)
                <option value="{{ $val }}" {{ request('category') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </form>

    @forelse($feedbacks as $fb)
    <div class="fb-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <span class="badge" style="background:{{ $fb->status === 'resolved' ? '#059669' : ($fb->status === 'acknowledged' ? '#2563eb' : '#d97706') }};color:#fff;">{{ ucfirst($fb->status) }}</span>
                <span class="badge bg-secondary">{{ \App\Models\ProductionFeedback::CATEGORIES[$fb->category] ?? $fb->category }}</span>
                <strong class="ms-1">→ {{ $fb->toUser->name ?? 'Agent' }}</strong>
            </div>
            <small class="text-muted">{{ $fb->created_at->format('M d, Y h:i A') }}</small>
        </div>
        <div class="mt-2" style="font-size:14px;">{{ $fb->message }}</div>
        <div class="mt-2 d-flex justify-content-between align-items-center">
            <a href="{{ route('sales.prototype.show', $fb->sale_id) }}" class="small" style="color:#2563eb;text-decoration:none;">
                <i class="fas fa-external-link-alt me-1"></i>Sale #{{ $fb->sale->sales_number ?? $fb->sale_id }}
            </a>
            @if($fb->status !== 'resolved')
            <button class="btn btn-sm btn-outline-success" onclick="resolveFeedback({{ $fb->id }})">
                <i class="fas fa-check me-1"></i>Mark Resolved
            </button>
            @endif
        </div>
    </div>
    @empty
    <div class="text-center text-muted py-5">
        <i class="fas fa-clipboard-check" style="font-size:32px;color:#cbd5e1;"></i>
        <p class="mt-2 mb-0">Wala pang production feedback.</p>
    </div>
    @endforelse

    <div class="mt-3">
        {{ $feedbacks->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function resolveFeedback(feedbackId) {
    if (!confirm('Mark this feedback as resolved?')) return;
    fetch('{{ route('sales.prototype.production-feedback.status', 'FEEDBACK_ID') }}'.replace('FEEDBACK_ID', feedbackId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({status: 'resolved'})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { location.reload(); }
        else { alert(data.message || 'Failed to update.'); }
    })
    .catch(function() { alert('Request failed.'); });
}
</script>
@endpush
