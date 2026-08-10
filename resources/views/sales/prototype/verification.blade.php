@extends('layouts.app')

@section('title', 'Payment Verification')

@push('styles')
<style>
    .payment-card { transition: all 0.2s; border-left: 4px solid #dee2e6; }
    .payment-card.pending { border-left-color: #ffc107; }
    .payment-card.verified { border-left-color: #198754; }
    .payment-card.rejected { border-left-color: #dc3545; }
    .payment-card.down_payment_verified { border-left-color: #0dcaf0; }
    .payment-card.additional_payment_verified { border-left-color: #6f42c1; }
    .payment-card.full_payment_verified { border-left-color: #198754; }
    .payment-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); transform: translateX(2px); }
    .status-badge { font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 50px; }
    .audit-entry { padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0; }
    .audit-entry:last-child { border-bottom: none; }
    .audit-time { font-size: 0.75rem; color: #999; }
    .modal-xl-custom { max-width: 900px; }
    .verification-sidebar { position: sticky; top: 80px; }
    .section-title { font-weight: 600; color: #333; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e9ecef; }
    .amount-display { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.5rem 0.75rem; margin-top: 0.5rem; display: inline-block; }
    .amount-display .amount-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; color: #166534; }
    .amount-display .amount-value { font-size: 1.1rem; font-weight: 700; color: #15803d; }
    .payment-amount-row { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .payment-type-badge { font-size: 0.65rem; padding: 0.2rem 0.5rem; border-radius: 50px; text-transform: uppercase; letter-spacing: 0.03em; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Payment Verification</h1>
            <p class="text-muted mb-0">Verify, re-tag, or manage individual payment records</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.cash-flow') }}" class="btn btn-outline-success">
                <i class="fas fa-chart-line"></i> Cash Flow
            </a>
        </div>
    </div>

    <div class="row g-3">
        <!-- Pending Payments (Left column - wider) -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-clock text-warning me-2"></i> Pending Verification</h5>
                    <span class="badge bg-warning rounded-pill">{{ $pendingPayments->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($pendingPayments->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                            <p class="mb-0">All caught up! No pending payments.</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($pendingPayments as $payment)
                                <div class="list-group-item payment-card {{ $payment->payment_status }}"
                                     data-payment-id="{{ $payment->id }}"
                                     data-sale-id="{{ $payment->prototype_sale_id }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1 me-3">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <a href="{{ route('sales.prototype.show', $payment->prototype_sale_id) }}" class="fw-semibold text-decoration-none">
                                                    {{ $payment->customer_name ?? 'Sale #'.$payment->prototype_sale_id }}
                                                </a>
                                                @if($payment->payment_status === 'pending')
                                                    <span class="badge bg-warning status-badge">Pending</span>
                                                @elseif($payment->payment_status === 'rejected')
                                                    <span class="badge bg-danger status-badge">Rejected</span>
                                                @endif
                                                <span class="payment-type-badge badge bg-secondary">
                                                    @if($payment->payment_type === 'down_payment') Down Payment
                                                    @elseif($payment->payment_type === 'additional') Additional
                                                    @elseif($payment->payment_type === 'full_payment') Full Payment
                                                    @else {{ $payment->payment_type }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="fw-bold text-success">₱{{ number_format($payment->amount, 2) }}</span>
                                                @if($payment->total_amount)
                                                    <span class="small text-muted">of ₱{{ number_format($payment->total_amount, 2) }} total</span>
                                                @endif
                                            </div>
                                            <div class="small mt-1">
                                                @if($payment->account_name)
                                                    <span class="badge bg-light text-dark me-1">
                                                        <i class="fas fa-user"></i> {{ $payment->account_name }}
                                                    </span>
                                                @endif
                                                @if($payment->reference_number)
                                                    <span class="badge bg-light text-dark me-1">
                                                        <i class="fas fa-hashtag"></i> {{ $payment->reference_number }}
                                                    </span>
                                                @endif
                                                @if($payment->payment_date)
                                                    <span class="badge bg-light text-dark me-1">
                                                        <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="mt-2">
                                                @if($payment->screenshot_path)
                                                    <a href="#" onclick="window.openScreenshot('{{ $payment->screenshot_path }}');return false;" class="text-primary text-decoration-none" title="View Payment Screenshot">
                                                        <i class="fas fa-image me-1"></i> View Screenshot
                                                    </a>
                                                @endif
                                            </div>
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($payment->created_at)->format('M d, g:i A') }}
                                            </div>
                                        </div>
                                        <div class="text-end" style="min-width: 100px;">
                                            <button class="btn btn-sm btn-success w-100 mb-1" onclick="openVerifyModal({{ json_encode([
                                                'payment_id' => $payment->payment_id ?? null,
                                                'sale_id' => $payment->prototype_sale_id,
                                                'sales_number' => $payment->sales_number ?? '',
                                                'customer_name' => $payment->customer_name ?? '',
                                                'payment_type' => $payment->payment_type ?? '',
                                                'amount' => $payment->amount ?? 0,
                                                'reference_number' => $payment->reference_number ?? '',
                                                'payment_date' => $payment->payment_date ?? '',
                                                'account_name' => $payment->account_name ?? '',
                                            ]) }})" title="Verify Payment">
                                                <i class="fas fa-check"></i> Verify
                                            </button>
                                            <div class="btn-group w-100">
                                                <button class="btn btn-sm btn-outline-danger" onclick="verifyPayment({{ $payment->payment_id ?? 'null' }}, {{ $payment->prototype_sale_id }}, 'reject')" title="Reject Payment">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary" onclick="showTagModal({{ $payment->payment_id ?? 'null' }}, {{ $payment->prototype_sale_id }}, {{ $payment->payment_account_id ?? 'null' }})" title="Re-tag Account">
                                                    <i class="fas fa-tag"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" onclick="showEditModal({{ $payment->payment_id ?? 'null' }}, {{ $payment->prototype_sale_id }}, {{ $payment->amount ?? 'null' }})" title="Edit Ref/Date/Amount">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pending Rejections (awaiting second verifier) -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-shield text-danger me-2"></i> Pending Rejection Approval</h5>
                    <span class="badge bg-danger rounded-pill">{{ $pendingRejections->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($pendingRejections->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <small>No rejections awaiting a second verifier.</small>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($pendingRejections as $pr)
                                <div class="list-group-item payment-card reject_pending">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1 me-3">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <a href="{{ route('sales.prototype.show', $pr->sale_id) }}" class="fw-semibold text-decoration-none">
                                                    {{ $pr->customer_name ?? 'Sale #'.$pr->sale_id }}
                                                </a>
                                                <span class="badge bg-danger status-badge">Rejection Pending</span>
                                            </div>
                                            <div class="small text-muted">
                                                {{ $pr->sales_number ?? '' }} · ₱{{ number_format($pr->amount ?? $pr->deposit_paid ?? 0, 2) }} · {{ ucfirst(str_replace('_', ' ', $pr->payment_source ?? '')) }}
                                            </div>
                                            @if($pr->payment_method)
                                                <div class="small text-muted">Method: {{ ucfirst(str_replace('_', ' ', $pr->payment_method)) }}</div>
                                            @endif
                                            @if($pr->reference_number)
                                                <div class="small text-muted">Ref: {{ $pr->reference_number }}</div>
                                            @endif
                                            @if($pr->payment_date)
                                                <div class="small text-muted">Date: {{ \Carbon\Carbon::parse($pr->payment_date)->format('M d, Y') }}</div>
                                            @endif
                                            @if($pr->payment_screenshot_path)
                                                <div class="mt-2">
                                                    <img src="{{ $pr->payment_screenshot_path }}" alt="Payment screenshot"
                                                         onclick="window.openScreenshot('{{ $pr->payment_screenshot_path }}')"
                                                         style="max-height: 90px; max-width: 140px; border-radius: 6px; cursor: zoom-in; border: 1px solid #dee2e6; object-fit: cover;"
                                                         title="Click to view payment screenshot">
                                                </div>
                                            @endif
                                            <div class="small mt-1">
                                                <i class="fas fa-user-clock text-danger me-1"></i>
                                                Requested by <strong>{{ $pr->requester_name ?? 'Unknown' }}</strong>
                                                @if($pr->reject_requested_at)
                                                    · {{ \Carbon\Carbon::parse($pr->reject_requested_at)->format('M d, g:i A') }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-end" style="min-width: 120px;">
                                            <button class="btn btn-sm btn-outline-danger w-100 mb-1"
                                                    onclick="openConfirmRejectModal({{ json_encode([
                                                        'payment_id' => $pr->payment_id ?? null,
                                                        'sale_id' => $pr->sale_id,
                                                        'sales_number' => $pr->sales_number ?? '',
                                                        'customer_name' => $pr->customer_name ?? '',
                                                        'requester_name' => $pr->requester_name ?? '',
                                                    ]) }})"
                                                    title="Confirm Rejection (second verifier)">
                                                <i class="fas fa-check-double"></i> Confirm Reject
                                            </button>
                                            @if($pr->reject_requested_by == auth()->id())
                                                <button class="btn btn-sm btn-outline-secondary w-100"
                                                        onclick="cancelReject({{ $pr->payment_id ?? 'null' }}, {{ $pr->sale_id }})"
                                                        title="Cancel your rejection request">
                                                    <i class="fas fa-undo"></i> Cancel Request
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pending Edit Approval (change ref/amount/date, second verifier) -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-pen-alt text-info me-2"></i> Pending Edit Approval</h5>
                    <span class="badge bg-info rounded-pill">{{ $pendingEdits->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($pendingEdits->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <small>No edits awaiting a second verifier.</small>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($pendingEdits as $pe)
                                <div class="list-group-item payment-card edit_pending">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1 me-3">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <a href="{{ route('sales.prototype.show', $pe->sale_id) }}" class="fw-semibold text-decoration-none">
                                                    {{ $pe->customer_name ?? 'Sale #'.$pe->sale_id }}
                                                </a>
                                                <span class="badge bg-info status-badge">Edit Pending</span>
                                            </div>
                                            <div class="small text-muted">
                                                {{ $pe->sales_number ?? '' }} · ₱{{ number_format($pe->amount ?? $pe->deposit_paid ?? 0, 2) }} · {{ ucfirst(str_replace('_', ' ', $pe->payment_source ?? '')) }}
                                            </div>
                                            @if($pe->reference_number)
                                                <div class="small text-muted">Ref: {{ $pe->reference_number }}</div>
                                            @endif
                                            <div class="small mt-1 border-start border-info ps-2">
                                                @if($pe->pending_reference_number)
                                                    <div class="text-muted">Ref: <span class="text-decoration-line-through">{{ $pe->reference_number ?: '—' }}</span> → <strong class="text-info">{{ $pe->pending_reference_number }}</strong></div>
                                                @endif
                                                @if($pe->pending_payment_date)
                                                    <div class="text-muted">Date: <span class="text-decoration-line-through">{{ optional(\Carbon\Carbon::parse($pe->payment_date))->format('M d, Y') ?: '—' }}</span> → <strong class="text-info">{{ \Carbon\Carbon::parse($pe->pending_payment_date)->format('M d, Y') }}</strong></div>
                                                @endif
                                                @if($pe->pending_amount !== null)
                                                    <div class="text-muted">Amount: <span class="text-decoration-line-through">₱{{ number_format((float) $pe->amount ?? $pe->deposit_paid, 2) }}</span> → <strong class="text-info">₱{{ number_format((float) $pe->pending_amount, 2) }}</strong></div>
                                                @endif
                                            </div>
                                            @if($pe->payment_screenshot_path)
                                                <div class="mt-2">
                                                    <img src="{{ $pe->payment_screenshot_path }}" alt="Payment screenshot"
                                                         onclick="window.openScreenshot('{{ $pe->payment_screenshot_path }}')"
                                                         style="max-height: 90px; max-width: 140px; border-radius: 6px; cursor: zoom-in; border: 1px solid #dee2e6; object-fit: cover;"
                                                         title="Click to view payment screenshot">
                                                </div>
                                            @endif
                                            <div class="small mt-1">
                                                <i class="fas fa-user-clock text-info me-1"></i>
                                                Requested by <strong>{{ $pe->requester_name ?? 'Unknown' }}</strong>
                                                @if($pe->edit_requested_at)
                                                    · {{ \Carbon\Carbon::parse($pe->edit_requested_at)->format('M d, g:i A') }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-end" style="min-width: 120px;">
                                            <button class="btn btn-sm btn-outline-info w-100 mb-1"
                                                    onclick="openConfirmEditModal({{ json_encode([
                                                        'payment_id' => $pe->payment_id ?? null,
                                                        'sale_id' => $pe->sale_id,
                                                        'sales_number' => $pe->sales_number ?? '',
                                                        'customer_name' => $pe->customer_name ?? '',
                                                        'requester_name' => $pe->requester_name ?? '',
                                                    ]) }})"
                                                    title="Confirm Edit (second verifier)">
                                                <i class="fas fa-check-double"></i> Confirm Edit
                                            </button>
                                            @if($pe->edit_requested_by == auth()->id())
                                                <button class="btn btn-sm btn-outline-secondary w-100"
                                                        onclick="cancelEdit({{ $pe->payment_id ?? 'null' }}, {{ $pe->sale_id }})"
                                                        title="Cancel your edit request">
                                                    <i class="fas fa-undo"></i> Cancel Request
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Verified Payments (recent) -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-check-circle text-success me-2"></i> Recently Verified</h5>
                    <span class="badge bg-success rounded-pill">{{ $verifiedPayments->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($verifiedPayments->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <small>No verified payments yet.</small>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($verifiedPayments as $payment)
                                @php
                                    $statusLabel = match($payment->payment_status) {
                                        'verified' => 'Verified',
                                        'down_payment_verified' => 'Down Payment',
                                        'additional_payment_verified' => 'Additional Payment',
                                        'full_payment_verified' => 'Full Payment',
                                        default => $payment->payment_status,
                                    };
                                    $statusClass = match($payment->payment_status) {
                                        'verified' => 'success',
                                        'down_payment_verified' => 'info',
                                        'additional_payment_verified' => 'primary',
                                        'full_payment_verified' => 'success',
                                        default => 'secondary',
                                    };
                                @endphp
                                <div class="list-group-item payment-card {{ $payment->payment_status }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1 me-3">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <a href="{{ route('sales.prototype.show', $payment->prototype_sale_id) }}" class="fw-semibold text-decoration-none">
                                                    {{ $payment->customer_name ?? 'Sale #'.$payment->prototype_sale_id }}
                                                </a>
                                                <span class="badge bg-{{ $statusClass }} status-badge">{{ $statusLabel }}</span>
                                                <span class="payment-type-badge badge bg-secondary">
                                                    @if($payment->payment_type === 'down_payment') Down Payment
                                                    @elseif($payment->payment_type === 'additional') Additional
                                                    @elseif($payment->payment_type === 'full_payment') Full Payment
                                                    @else {{ $payment->payment_type }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="fw-bold text-success">₱{{ number_format($payment->amount, 2) }}</span>
                                                @if($payment->total_amount)
                                                    <span class="small text-muted">of ₱{{ number_format($payment->total_amount, 2) }} total</span>
                                                @endif
                                            </div>
                                            <div class="small mt-1">
                                                @if($payment->account_name)
                                                    <span class="badge bg-light text-dark"><i class="fas fa-user"></i> {{ $payment->account_name }}</span>
                                                @endif
                                                @if($payment->reference_number)
                                                    <span class="badge bg-light text-dark"><i class="fas fa-hashtag"></i> {{ $payment->reference_number }}</span>
                                                @endif
                                            </div>
                                            @if($payment->verified_by_name)
                                                <div class="small text-muted mt-1">
                                                    <i class="fas fa-check-circle text-success"></i> Verified by {{ $payment->verified_by_name }}
                                                    @if($payment->verified_at)
                                                        on {{ \Carbon\Carbon::parse($payment->verified_at)->format('M d, g:i A') }}
                                                    @endif
                                                </div>
                                            @endif
                                            @if($payment->screenshot_path)
                                                <a href="#" onclick="window.openScreenshot('{{ $payment->screenshot_path }}');return false;" class="text-primary text-decoration-none small" title="View Screenshot">
                                                    <i class="fas fa-image"></i> View Screenshot
                                                </a>
                                            @endif
                                        </div>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-secondary" onclick="showTagModal({{ $payment->id }}, {{ $payment->prototype_sale_id }}, {{ $payment->payment_account_id ?? 'null' }})" title="Re-tag Account">
                                                <i class="fas fa-tag"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="showEditModal({{ $payment->id }}, {{ $payment->prototype_sale_id }}, {{ $payment->amount ?? 'null' }})" title="Edit Ref/Date/Amount">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <div class="verification-sidebar">
                <!-- Stats Card -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="section-title">Today's Stats</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Pending</span>
                            <span class="fw-bold text-warning">{{ $pendingPayments->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Verified Today</span>
                            <span class="fw-bold text-success">{{ $verifiedPayments->count() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Audit Log -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-history me-1"></i> Recent Activity</h6>
                    </div>
                    <div class="card-body p-2" id="auditLogBody" style="max-height: 400px; overflow-y: auto;">
                        <div class="text-center py-4 text-muted"><small>Loading...</small></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RE-TAG MODAL -->
<div class="modal fade" id="tagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Re-tag Payment Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="tagForm">
                <div class="modal-body">
                    <input type="hidden" name="payment_id" id="tagPaymentId">
                    <input type="hidden" name="action" value="re_tag">
                    <div class="mb-3">
                        <label class="form-label">New Payment Account</label>
                        <select name="new_account_id" class="form-select" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" data-user="{{ $account->user_id }}">
                                    {{ $account->name }} @if($account->user) ({{ $account->user->name }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remark (optional)</label>
                        <textarea name="remark" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Re-tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT REF MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm">
                <div class="modal-body">
                    <input type="hidden" name="payment_id" id="editPaymentId">
                    <input type="hidden" name="action" value="edit_ref">
                    <div class="mb-3">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="new_reference_number" class="form-control" placeholder="Enter new reference #">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="new_payment_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (₱)</label>
                        <input type="number" name="new_amount" id="editNewAmount" class="form-control" step="0.01" min="0" placeholder="Enter new amount">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remark (optional)</label>
                        <textarea name="remark" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- VERIFY CONFIRMATION MODAL -->
<div class="modal fade" id="verifyConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header border-0 pb-2" style="background: linear-gradient(135deg, #198754 0%, #146c43 100%); border-radius: 16px 16px 0 0; color: #fff;">
                <h5 class="modal-title fw-bold"><i class="fas fa-shield-alt me-2"></i>Confirm Payment Verification</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="text-muted small mb-3">Please review the payment details below before verifying. Hindi na mababago agad ito pagkatapos i-confirm.</p>
                <div class="bg-light rounded-3 p-3">
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Sale Number</div>
                        <div class="col-7 fw-semibold" id="verifySaleNumber">—</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Customer</div>
                        <div class="col-7 fw-semibold" id="verifyCustomer">—</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Payment Type</div>
                        <div class="col-7 text-capitalize" id="verifyPaymentType">—</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Amount</div>
                        <div class="col-7 fw-bold text-success" id="verifyAmount">—</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Reference #</div>
                        <div class="col-7" id="verifyReference">—</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Payment Date</div>
                        <div class="col-7" id="verifyPaymentDate">—</div>
                    </div>
                    <div class="row">
                        <div class="col-5 text-muted small">Account</div>
                        <div class="col-7" id="verifyAccount">—</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="verifyConfirmBtn">
                    <i class="fas fa-check-circle me-1"></i> Confirm & Verify
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CONFIRM REJECTION MODAL (second verifier) -->
<div class="modal fade" id="confirmRejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header border-0 pb-2" style="background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%); border-radius: 16px 16px 0 0; color: #fff;">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-shield me-2"></i>Confirm Rejection</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="text-muted small mb-3">This rejection was requested by <strong id="crRequester">—</strong>. Ikaw ang pangalawang verifier — kailangan mong i-confirm para maging final ang rejection.</p>
                <div class="bg-light rounded-3 p-3">
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Customer</div>
                        <div class="col-7 fw-semibold" id="crCustomer">—</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Sale Number</div>
                        <div class="col-7" id="crSaleNumber">—</div>
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label">Confirmation reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="crReason" rows="2" placeholder="Enter your confirmation note"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="crConfirmBtn">
                    <i class="fas fa-check-double me-1"></i> Confirm Rejection
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CONFIRM EDIT MODAL (second verifier) -->
<div class="modal fade" id="confirmEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pen-alt text-info me-2"></i> Confirm Edit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">An edit was requested by <strong id="ceRequester">—</strong> for
                    <strong id="ceCustomer">—</strong> (<span id="ceSaleNumber">—</span>).</p>
                <p class="text-muted small">You are acting as the <strong>second verifier</strong>. Confirming will apply the requested reference/amount/date changes.</p>
                <label class="form-label">Confirmation reason <span class="text-danger">*</span></label>
                <textarea class="form-control" id="ceReason" rows="2" placeholder="Why are you confirming this edit?"></textarea>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" id="ceConfirmBtn">
                    <i class="fas fa-check-double me-1"></i> Confirm & Apply Edit
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// === VERIFY / REJECT ===
function verifyPayment(paymentId, saleId, action, remark) {
    if (action === 'reject') {
        remark = prompt('Reason for rejection (required):');
        if (remark === null) return;
        remark = remark.trim();
        if (!remark) {
            showToast('error', 'Rejection reason is required — hindi ma-reject kapag walang dahilan.');
            return;
        }
    }

    var btn = event.target.closest('button');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>'; }

    window.axios.post('/sales/prototype/' + saleId + '/verify-payment', {
            _token: document.querySelector('meta[name="csrf-token"]').content,
            payment_id: paymentId,
            action: action,
            remark: remark || ''
        })
        .then(function(res) {
            showToast('success', res.data.message || 'Done!');
            setTimeout(function() { location.reload(); }, 1000);
        })
        .catch(function(err) {
            var msg = err.response && err.response.data && err.response.data.error ? err.response.data.error : 'An error occurred';
            showToast('error', msg);
            if (btn) { btn.disabled = false; btn.innerHTML = action === 'verify' ? '<i class="fas fa-check"></i> Verify' : '<i class="fas fa-times"></i>'; }
        });
}

// Legacy verifySale function for old-format pages (delegates to verifyPayment)
window.verifySale = function(saleId, action) {
    verifyPayment(null, saleId, action);
};

// === VERIFY CONFIRMATION MODAL ===
var pendingVerify = null;

function openVerifyModal(details) {
    pendingVerify = { paymentId: details.payment_id, saleId: details.sale_id };

    document.getElementById('verifySaleNumber').textContent = details.sales_number || '—';
    document.getElementById('verifyCustomer').textContent = details.customer_name || '—';
    document.getElementById('verifyPaymentType').textContent = (details.payment_type || '—').replace(/_/g, ' ');
    document.getElementById('verifyAmount').textContent = '₱' + parseFloat(details.amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('verifyReference').textContent = details.reference_number || '—';
    document.getElementById('verifyPaymentDate').textContent = details.payment_date || '—';
    document.getElementById('verifyAccount').textContent = details.account_name || '—';

    bootstrap.Modal.getOrCreateInstance(document.getElementById('verifyConfirmModal')).show();
}

document.getElementById('verifyConfirmBtn').addEventListener('click', function() {
    if (!pendingVerify) return;

    // Validate that all payment detail labels are filled
    var labels = {
        'Sale Number': document.getElementById('verifySaleNumber').textContent,
        'Customer': document.getElementById('verifyCustomer').textContent,
        'Amount': document.getElementById('verifyAmount').textContent,
        'Account': document.getElementById('verifyAccount').textContent
    };
    var missing = [];
    Object.keys(labels).forEach(function(k) {
        if (!labels[k] || labels[k] === '—') missing.push(k);
    });
    if (missing.length) {
        showToast('error', 'Cannot verify — missing details: ' + missing.join(', ') + '. Please complete the payment info first.');
        return;
    }

    bootstrap.Modal.getInstance(document.getElementById('verifyConfirmModal')).hide();
    verifyPayment(pendingVerify.paymentId, pendingVerify.saleId, 'verify');
});

// === RE-TAG MODAL ===
function showTagModal(paymentId, saleId, currentAccountId) {
    document.getElementById('tagPaymentId').value = paymentId;
    document.getElementById('tagModal').setAttribute('data-sale-id', saleId);
    document.querySelector('#tagForm select[name="new_account_id"]').value = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('tagModal')).show();
}

document.getElementById('tagForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Require an account selection before proceeding
    var accountSel = document.querySelector('#tagForm select[name="new_account_id"]');
    if (!accountSel || !accountSel.value) {
        showToast('error', 'Please select an account to re-tag to.');
        return;
    }

    var paymentId = document.getElementById('tagPaymentId').value;
    var saleId = document.getElementById('tagModal').getAttribute('data-sale-id');

    if (!saleId) {
        var card = document.querySelector('[data-payment-id="' + paymentId + '"]');
        saleId = card ? card.getAttribute('data-sale-id') : null;
    }

    if (!saleId) {
        showToast('error', 'Could not determine sale ID. Please refresh and try again.');
        return;
    }

    var btn = this.querySelector('button[type="submit"]');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    var formData = new FormData(this);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    window.axios.post('/sales/prototype/' + saleId + '/verify-payment', formData)
        .then(function(res) {
            bootstrap.Modal.getInstance(document.getElementById('tagModal')).hide();
            showToast('success', res.data.message || 'Re-tagged!');
            setTimeout(function() { location.reload(); }, 1000);
        })
        .catch(function(err) {
            var msg = err.response && err.response.data && err.response.data.error ? err.response.data.error : 'An error occurred';
            showToast('error', msg);
            btn.disabled = false; btn.textContent = 'Re-tag';
        });
});

// === EDIT REF MODAL ===
function showEditModal(paymentId, saleId, currentAmount) {
    document.getElementById('editPaymentId').value = paymentId;
    document.getElementById('editModal').setAttribute('data-sale-id', saleId);
    document.getElementById('editForm').reset();
    if (currentAmount !== null && currentAmount !== undefined && currentAmount !== '') {
        document.getElementById('editNewAmount').value = currentAmount;
    }
    bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).show();
}

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Require at least one field to have content before proceeding
    var ref = document.querySelector('#editForm input[name="new_reference_number"]');
    var date = document.querySelector('#editForm input[name="new_payment_date"]');
    var amount = document.querySelector('#editForm input[name="new_amount"]');
    if ((!ref || !ref.value.trim()) && (!date || !date.value) && (!amount || !amount.value)) {
        showToast('error', 'Please fill in at least one field (Reference #, Payment Date, or Amount).');
        return;
    }
    if (amount && amount.value && parseFloat(amount.value) <= 0) {
        showToast('error', 'Amount must be greater than 0.');
        return;
    }

    var paymentId = document.getElementById('editPaymentId').value;
    var saleId = document.getElementById('editModal').getAttribute('data-sale-id');

    if (!saleId) {
        var card = document.querySelector('[data-payment-id="' + paymentId + '"]');
        saleId = card ? card.getAttribute('data-sale-id') : null;
    }

    if (!saleId) {
        showToast('error', 'Could not determine sale ID. Please refresh and try again.');
        return;
    }

    var btn = this.querySelector('button[type="submit"]');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    var formData = new FormData(this);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    window.axios.post('/sales/prototype/' + saleId + '/verify-payment', formData)
        .then(function(res) {
            bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
            showToast('success', res.data.message || 'Updated!');
            setTimeout(function() { location.reload(); }, 1000);
        })
        .catch(function(err) {
            var msg = err.response && err.response.data && err.response.data.error ? err.response.data.error : 'An error occurred';
            showToast('error', msg);
            btn.disabled = false; btn.textContent = 'Update';
        });
});

// === TOAST ===
function showToast(type, message) {
    var bg = type === 'success' ? 'bg-success' : 'bg-danger';
    var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    var toast = '<div class="toast align-items-center text-white ' + bg + ' border-0 show" role="alert">' +
        '<div class="d-flex"><div class="toast-body"><i class="fas ' + icon + ' me-2"></i>' + message + '</div>' +
        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
    var container = document.getElementById('toastContainer');
    container.insertAdjacentHTML('beforeend', toast);
    setTimeout(function() {
        var t = container.querySelector('.toast');
        if (t) t.remove();
    }, 5000);
}

// === CONFIRM REJECTION (second verifier) ===
var pendingReject = null;

function openConfirmRejectModal(details) {
    pendingReject = { paymentId: details.payment_id, saleId: details.sale_id };
    document.getElementById('crRequester').textContent = details.requester_name || 'Unknown';
    document.getElementById('crCustomer').textContent = details.customer_name || '—';
    document.getElementById('crSaleNumber').textContent = details.sales_number || '—';
    document.getElementById('crReason').value = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmRejectModal')).show();
}

document.getElementById('crConfirmBtn').addEventListener('click', function() {
    if (!pendingReject) return;
    var reason = document.getElementById('crReason').value.trim();
    if (!reason) {
        showToast('error', 'Confirmation reason is required.');
        return;
    }
    bootstrap.Modal.getInstance(document.getElementById('confirmRejectModal')).hide();
    confirmReject(pendingReject.paymentId, pendingReject.saleId, reason);
});

function confirmReject(paymentId, saleId, reason) {
    var btn = document.getElementById('crConfirmBtn');
    window.axios.post('/sales/prototype/' + saleId + '/verify-payment', {
            _token: document.querySelector('meta[name="csrf-token"]').content,
            payment_id: paymentId,
            action: 'confirm_reject',
            remark: reason
        })
        .then(function(res) {
            showToast('success', res.data.message || 'Rejection confirmed!');
            setTimeout(function() { location.reload(); }, 1000);
        })
        .catch(function(err) {
            var msg = err.response && err.response.data && err.response.data.error ? err.response.data.error : 'An error occurred';
            showToast('error', msg);
        });
}

function cancelReject(paymentId, saleId) {
    if (!confirm('Cancel this rejection request?')) return;
    window.axios.post('/sales/prototype/' + saleId + '/verify-payment', {
            _token: document.querySelector('meta[name="csrf-token"]').content,
            payment_id: paymentId,
            action: 'cancel_reject',
            remark: 'Request cancelled by requester'
        })
        .then(function(res) {
            showToast('success', res.data.message || 'Rejection request cancelled!');
            setTimeout(function() { location.reload(); }, 1000);
        })
        .catch(function(err) {
            var msg = err.response && err.response.data && err.response.data.error ? err.response.data.error : 'An error occurred';
            showToast('error', msg);
        });
}

// === CONFIRM EDIT (second verifier) ===
var pendingEdit = null;

function openConfirmEditModal(details) {
    pendingEdit = { paymentId: details.payment_id, saleId: details.sale_id };
    document.getElementById('ceRequester').textContent = details.requester_name || 'Unknown';
    document.getElementById('ceCustomer').textContent = details.customer_name || '—';
    document.getElementById('ceSaleNumber').textContent = details.sales_number || '—';
    document.getElementById('ceReason').value = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmEditModal')).show();
}

document.getElementById('ceConfirmBtn').addEventListener('click', function() {
    if (!pendingEdit) return;
    var reason = document.getElementById('ceReason').value.trim();
    if (!reason) {
        showToast('error', 'Confirmation reason is required.');
        return;
    }
    bootstrap.Modal.getInstance(document.getElementById('confirmEditModal')).hide();
    confirmEdit(pendingEdit.paymentId, pendingEdit.saleId, reason);
});

function confirmEdit(paymentId, saleId, reason) {
    window.axios.post('/sales/prototype/' + saleId + '/verify-payment', {
            _token: document.querySelector('meta[name="csrf-token"]').content,
            payment_id: paymentId,
            action: 'confirm_edit',
            remark: reason
        })
        .then(function(res) {
            showToast('success', res.data.message || 'Edit confirmed and applied!');
            setTimeout(function() { location.reload(); }, 1000);
        })
        .catch(function(err) {
            var msg = err.response && err.response.data && err.response.data.error ? err.response.data.error : 'An error occurred';
            showToast('error', msg);
        });
}

function cancelEdit(paymentId, saleId) {
    if (!confirm('Cancel this edit request?')) return;
    window.axios.post('/sales/prototype/' + saleId + '/verify-payment', {
            _token: document.querySelector('meta[name="csrf-token"]').content,
            payment_id: paymentId,
            action: 'cancel_edit',
            remark: 'Request cancelled by requester'
        })
        .then(function(res) {
            showToast('success', res.data.message || 'Edit request cancelled!');
            setTimeout(function() { location.reload(); }, 1000);
        })
        .catch(function(err) {
            var msg = err.response && err.response.data && err.response.data.error ? err.response.data.error : 'An error occurred';
            showToast('error', msg);
        });
}

// === AUDIT LOG SIDEBAR (stays the same, uses sale-level logs) ===
document.addEventListener('DOMContentLoaded', function() {
    // Load recent audit logs for the first pending payment (or recent activity)
    loadAuditLogs();
});

function loadAuditLogs() {
    window.axios.get('{{ route("sales.audit-logs") }}', { params: { limit: 20 } })
    .then(function(res) {
        var logs = res.data;
        var html = '';
        if (!logs.length) {
            html = '<div class="text-center py-4 text-muted"><small>No activity yet.</small></div>';
        }
        logs.forEach(function(log) {
            var actionBadge = '';
            if (['down_payment_verified','additional_payment_verified','full_payment_verified','verified'].includes(log.action))
                actionBadge = '<span class="badge bg-success">Verified</span>';
            else if (log.action === 'rejected')
                actionBadge = '<span class="badge bg-danger">Rejected</span>';
            else if (log.action === 're_tagged')
                actionBadge = '<span class="badge bg-secondary">Re-tagged</span>';
            else if (log.action === 'edited_ref')
                actionBadge = '<span class="badge bg-info">Edited Ref</span>';
            else
                actionBadge = '<span class="badge bg-secondary">' + log.action + '</span>';

            var details = '';
            if (log.old_value && log.new_value) {
                details = '<div class="small text-muted ms-4"><span class="text-decoration-line-through">' + log.old_value + '</span> → <strong>' + log.new_value + '</strong></div>';
            }
            if (log.remarks) {
                details += '<div class="small text-muted ms-4"><i class="fas fa-comment"></i> ' + log.remarks + '</div>';
            }
            if (log.payment_account) {
                details += '<div class="small text-muted ms-4"><i class="fas fa-user"></i> ' + log.payment_account.name + '</div>';
            }

            html += '<div class="audit-entry">' +
                '<div class="d-flex justify-content-between">' +
                '<div><strong>' + (log.user ? log.user.name : 'System') + '</strong> ' + actionBadge + '</div>' +
                '<div class="audit-time">' + new Date(log.created_at).toLocaleString() + '</div>' +
                '</div>' +
                details +
                '</div>';
        });
        document.getElementById('auditLogBody').innerHTML = html;
    })
    .catch(function(e) {
        document.getElementById('auditLogBody').innerHTML = '<div class="text-center py-4 text-danger">Failed to load audit logs.</div>';
    });
}

// === IMAGE LIGHTBOX ===
window.openScreenshot = function(src) {
    var old = document.getElementById('imageLightbox');
    if (old) old.remove();

    var overlay = document.createElement('div');
    overlay.id = 'imageLightbox';
    overlay.style.cssText = 'display:flex!important;align-items:center;justify-content:center;position:fixed;top:0;left:0;width:100%;height:100%;z-index:100000;background:rgba(0,0,0,0.85);cursor:zoom-out;';

    var closeBtn = document.createElement('button');
    closeBtn.innerHTML = '&times;';
    closeBtn.style.cssText = 'position:absolute;top:15px;right:25px;font-size:32px;color:white;background:none;border:none;cursor:pointer;z-index:100001;';
    closeBtn.onclick = function(ev) {
        ev.stopPropagation();
        var ol = document.getElementById('imageLightbox');
        if (ol) { ol.remove(); document.body.style.overflow = ''; }
    };

    var imgContainer = document.createElement('div');
    imgContainer.style.cssText = 'display:flex;align-items:center;justify-content:center;height:100%;padding:40px;';

    var img = document.createElement('img');
    img.id = 'lightboxImage';
    img.style.cssText = 'max-width:100%;max-height:90vh;object-fit:contain;border-radius:8px;';
    img.alt = '';

    imgContainer.appendChild(img);
    overlay.appendChild(closeBtn);
    overlay.appendChild(imgContainer);

    overlay.onclick = function(ev) {
        if (ev.target === overlay) {
            var ol = document.getElementById('imageLightbox');
            if (ol) { ol.remove(); document.body.style.overflow = ''; }
        }
    };

    img.src = src;
    if (img.complete) {
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
    } else {
        img.onload = function() {
            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden';
        };
        img.onerror = function() {
            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden';
        };
        setTimeout(function() {
            if (!overlay.parentNode) {
                document.body.appendChild(overlay);
                document.body.style.overflow = 'hidden';
            }
        }, 5000);
    }
};

// ESC key to close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var ol = document.getElementById('imageLightbox');
        if (ol) { ol.remove(); document.body.style.overflow = ''; }
    }
});
</script>
@endpush
