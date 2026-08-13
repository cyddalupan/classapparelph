@extends('layouts.app')

@section('title', 'Production Feedback')

@push('styles')
<style>
    .main-content, .content-area { min-width: 0; }
    .pipeline-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .pipeline-table th {
        background: #f8f9fa;
        padding: 10px 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    .pipeline-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }
    .pipeline-table tr:hover td {
        background: #f0f7ff;
    }
    .pipeline-table tr {
        cursor: pointer;
    }
    .stat-pill { padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 style="font-weight:700;color:#1e293b;"><i class="fas fa-clipboard-check me-2" style="color:#d97706;"></i>Production Feedback</h2>
            <p class="text-muted mb-0" style="font-size:13px;">
                @if($isManager)
                Lahat ng feedback na binigay sa mga sales agents — para ma-track ang production delays dulot ng kulang na impormasyon.
                @else
                Feedback mula sa manager tungkol sa production delays — i-check at i-resolve para magpatuloy ang production.
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ $isManager ? route('sales.prototype.dashboard') : route('sales.team.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Status pills -->
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <a href="{{ route('sales.prototype.production-feedback.list') }}" class="stat-pill text-decoration-none" style="background:{{ !request('status') ? '#1e293b' : '#f1f5f9' }};color:{{ !request('status') ? '#fff' : '#334155' }};">All ({{ array_sum($statusCounts) }})</a>
        @foreach(['open' => '#d97706', 'acknowledged' => '#2563eb', 'resolved' => '#059669'] as $st => $color)
        <a href="{{ route('sales.prototype.production-feedback.list', ['status' => $st]) }}" class="stat-pill text-decoration-none" style="background:{{ request('status') === $st ? $color : '#f1f5f9' }};color:{{ request('status') === $st ? '#fff' : '#334155' }};">{{ ucfirst($st) }} ({{ $statusCounts[$st] ?? 0 }})</a>
        @endforeach
    </div>

    <!-- Filters -->
    @if($isManager)
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
    @endif

    <!-- Table -->
    <div style="overflow-x:auto;">
        <table class="pipeline-table">
            <thead>
                <tr>
                    <th>Sales #</th>
                    <th>Mock Up</th>
                    <th>Category</th>
                    <th>Feedback</th>
                    <th>Status</th>
                    @if($isManager)
                    <th>Agent</th>
                    @endif
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feedbacks as $fb)
                    @php
                        $sale = $fb->sale;
                        // Mock up thumbnail (same logic as manager order list)
                        $mockups = $sale ? (is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? [])) : [];
                        $mainMockup = null;
                        foreach ((array)$mockups as $m) {
                            if (is_array($m) && !empty($m['is_main'])) { $mainMockup = $m; break; }
                        }
                        if (!$mainMockup && !empty($mockups)) $mainMockup = $mockups[0];
                        $firstMockupUrl = is_string($mainMockup) ? $mainMockup : ($mainMockup['url'] ?? '');
                    @endphp
                    <tr onclick="window.location.href='{{ route('sales.prototype.show', $fb->sale_id) }}'">
                        <td style="max-width:130px;white-space:nowrap;">
                            <strong style="font-size:12px;">{{ $sale->sales_number ?? ('Sale #' . $fb->sale_id) }}</strong>
                            @if($sale)
                            <div style="font-size:10px;color:#6c757d;">{{ $sale->customer_name ?: '—' }}</div>
                            @endif
                        </td>
                        <td>
                            @if($firstMockupUrl)
                                <img src="{{ $firstMockupUrl }}" alt="mockup" style="width:72px;height:auto;max-height:80px;object-fit:contain;border-radius:6px;cursor:pointer;" title="Click to open order" onerror="this.style.display='none'">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ \App\Models\ProductionFeedback::CATEGORIES[$fb->category] ?? $fb->category }}</span>
                        </td>
                        <td style="max-width:280px;">
                            <div style="font-size:12px;line-height:1.35;overflow:hidden;text-overflow:ellipsis;" title="{{ $fb->message }}">{{ \Illuminate\Support\Str::limit($fb->message, 80) }}</div>
                            <div style="font-size:10px;color:#94a3b8;margin-top:2px;">from {{ $fb->fromUser->name ?? 'Manager' }}</div>
                        </td>
                        <td>
                            <span class="badge" style="background:{{ $fb->status === 'resolved' ? '#059669' : ($fb->status === 'acknowledged' ? '#2563eb' : '#d97706') }};color:#fff;">{{ ucfirst($fb->status) }}</span>
                            @if($fb->resolved_at)
                            <div style="font-size:10px;color:#94a3b8;margin-top:2px;">{{ $fb->resolved_at->diffForHumans() }}</div>
                            @endif
                        </td>
                        @if($isManager)
                        <td style="white-space:nowrap;">{{ $fb->toUser->name ?? '—' }}</td>
                        @endif
                        <td style="white-space:nowrap;font-size:12px;">{{ $fb->created_at->format('M d, Y') }}<div style="font-size:10px;color:#94a3b8;">{{ $fb->created_at->format('h:i A') }}</div></td>
                        <td onclick="event.stopPropagation();">
                            @if($fb->status === 'open' && !$isManager)
                            <button class="btn btn-sm btn-outline-primary" onclick="updateFeedback({{ $fb->id }}, 'acknowledged')">Acknowledge</button>
                            @endif
                            @if($fb->status !== 'resolved')
                            <button class="btn btn-sm btn-outline-success" onclick="updateFeedback({{ $fb->id }}, 'resolved')">Resolve</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-clipboard-check" style="font-size:32px;color:#cbd5e1;"></i>
                            <p class="mt-2 mb-0">Wala pang production feedback.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $feedbacks->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateFeedback(feedbackId, status) {
    fetch('{{ route('sales.prototype.production-feedback.status', 'FEEDBACK_ID') }}'.replace('FEEDBACK_ID', feedbackId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({status: status})
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
