@extends('layouts.app')

@section('title', 'Delay Review — ' . $sale->sales_number)

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <a href="{{ url()->previous() ?: route('sales.prototype.list') }}" class="btn btn-outline-secondary btn-sm mb-2"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <h2 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Delay Review</h2>
            <div class="text-muted small mt-1">{{ $sale->sales_number }}</div>
        </div>
        <span class="badge bg-danger" style="font-size:0.85rem;">⚠️ DELAYED — {{ $sale->delayed_at ? \Carbon\Carbon::parse($sale->delayed_at)->format('M d, Y h:i A') : '—' }}</span>
    </div>

    <div class="row g-3">
        <!-- Left: Feedback + Project info -->
        <div class="col-lg-7">
            <!-- DELAY FEEDBACK -->
            <div class="card shadow-sm mb-3" style="border-left:4px solid #dc3545;">
                <div class="card-header bg-white fw-bold"><i class="fas fa-comment-dots me-2 text-danger"></i>Agent Feedback on Delay</div>
                <div class="card-body">
                    @if($sale->delay_feedback)
                        <div class="p-3 rounded" style="background:#fff5f5;border:1px solid #f5c6cb;">
                            <p class="mb-0" style="white-space:pre-wrap;">{{ $sale->delay_feedback }}</p>
                            @if($sale->delay_feedback_updated_at)
                                <div class="text-muted small mt-2"><i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($sale->delay_feedback_updated_at)->format('M d, Y h:i A') }}</div>
                            @endif
                        </div>
                    @else
                        <p class="text-muted mb-0"><i class="fas fa-info-circle me-1"></i>Walang feedback na iniwan ang agent sa delay na ito.</p>
                    @endif
                </div>
            </div>

            <!-- PROJECT INFO -->
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-info-circle me-2"></i>Project Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Customer</div>
                            <div class="fw-bold">{{ $sale->customer_name ?: '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Sales Agent</div>
                            <div class="fw-bold">{{ $sale->sales_agent_name ?: '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Department</div>
                            <div class="fw-bold">{{ $sale->department_name ?: '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Status</div>
                            <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $sale->status)) }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Estimated Completion</div>
                            <div class="fw-bold">{{ $sale->estimated_completion_date ? \Carbon\Carbon::parse($sale->estimated_completion_date)->format('M d, Y') : '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Rescheduled Date</div>
                            <div class="fw-bold">{{ $sale->rescheduled_date ? \Carbon\Carbon::parse($sale->rescheduled_date)->format('M d, Y') : '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Total Amount</div>
                            <div class="fw-bold">₱{{ number_format($sale->total_amount ?? 0, 2) }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Deposit Paid</div>
                            <div class="fw-bold text-success">₱{{ number_format($sale->deposit_paid ?? 0, 2) }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Balance Due</div>
                            <div class="fw-bold text-danger">₱{{ number_format($sale->balance_due ?? 0, 2) }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Priority</div>
                            <div class="fw-bold">{{ $sale->priority ? 'Prio ' . $sale->priority : '—' }}</div>
                        </div>
                        @if($sale->customer_notes)
                        <div class="col-12">
                            <div class="text-muted small">Customer Notes</div>
                            <div class="fw-bold" style="white-space:pre-wrap;">{{ $sale->customer_notes }}</div>
                        </div>
                        @endif
                        @if($sale->internal_notes)
                        <div class="col-12">
                            <div class="text-muted small">Internal Notes</div>
                            <div class="fw-bold" style="white-space:pre-wrap;">{{ $sale->internal_notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Mockup + Order items -->
        <div class="col-lg-5">
            @if($mainMockupUrl)
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-bold"><i class="fas fa-image me-2"></i>Mockup</div>
                <div class="card-body text-center">
                    <img src="{{ $mainMockupUrl }}" alt="mockup" class="img-fluid rounded" style="max-height:240px;object-fit:contain;" onerror="this.style.display='none'">
                </div>
            </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-box me-2"></i>Order Items ({{ count($items) }})</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" style="max-height:320px;overflow-y:auto;">
                        @forelse($items as $item)
                            <li class="list-group-item py-2">
                                @php
                                    $sf = is_array($item) ? ($item['sublimationForm'] ?? null) : null;
                                    $garmentName = $sf && is_array($sf) && !empty($sf['garment']['name']) ? $sf['garment']['name'] : (is_array($item) ? ($item['name'] ?? 'Item') : $item);
                                    $qty = is_array($item) ? (int)($item['quantity'] ?? $item['qty'] ?? 1) : 1;
                                @endphp
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold small">{{ $garmentName }}</span>
                                    <span class="badge bg-primary">×{{ $qty }}</span>
                                </div>
                                @if($sf && is_array($sf) && !empty($sf['fabric']['name']))
                                    <div class="text-muted small mt-1">{{ $sf['fabric']['name'] }}</div>
                                @endif
                            </li>
                        @empty
                            <li class="list-group-item text-muted small">No items found.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('sales.prototype.show', $sale->id) }}" class="btn btn-primary w-100"><i class="fas fa-external-link-alt me-1"></i> Open Full Order</a>
            </div>
        </div>
    </div>
</div>
@endsection
