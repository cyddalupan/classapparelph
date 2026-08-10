@extends('layouts.app')

@section('title', 'Refund Management')

@push('styles')
<style>
    .refund-card { transition: all 0.2s; border-left: 4px solid #dee2e6; }
    .refund-card.pending { border-left-color: #ffc107; }
    .refund-card.accepted { border-left-color: #6f42c1; }
    .refund-card.completed { border-left-color: #198754; }
    .refund-card.rejected { border-left-color: #dc3545; }
    .refund-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); transform: translateX(2px); }
    .section-title { font-weight: 600; color: #333; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e9ecef; }
    .filter-active { font-weight: 600; background: #e9ecef; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-undo-alt me-2"></i>Refund Management</h4>
            <p class="text-muted mb-0 small">Manage refund requests for prototype sales</p>
        </div>
        <div>
            <a href="{{ route('sales.prototype') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Sales
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sales.prototype.refunds') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>✅ Accepted</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>✅ Completed</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Reason</label>
                    <select name="reason" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ request('reason') == 'all' || !request('reason') ? 'selected' : '' }}>All Reasons</option>
                        <option value="reprocess_overpayment" {{ request('reason') == 'reprocess_overpayment' ? 'selected' : '' }}>Reprocess Overpayment</option>
                        <option value="cancellation" {{ request('reason') == 'cancellation' ? 'selected' : '' }}>Cancellation</option>
                        <option value="other" {{ request('reason') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <span class="text-muted small">{{ $refunds->total() }} refund(s) found</span>
                </div>
            </form>
        </div>
    </div>

    <!-- Refund List -->
    @forelse($refunds as $refund)
    <div class="card shadow-sm mb-3 refund-card {{ $refund->refund_status }}">
        <div class="card-body">
            <div class="row align-items-start">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <strong class="fs-6">{{ $refund->sales_number }}</strong>
                        <span class="badge bg-secondary">{{ $refund->customer_name }}</span>
                        @if($refund->refund_status === 'pending')
                            <span class="badge bg-warning text-dark">⏳ Pending</span>
                        @elseif($refund->refund_status === 'accepted')
                            <span class="badge" style="background: #6f42c1;">✅ Accepted</span>
                        @elseif($refund->refund_status === 'completed')
                            <span class="badge bg-success">✅ Completed</span>
                        @elseif($refund->refund_status === 'rejected')
                            <span class="badge bg-danger">❌ Rejected</span>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap gap-3 small text-muted">
                        <span><i class="fas fa-tag me-1"></i>{{ ucfirst(str_replace('_', ' ', $refund->refund_reason)) }}</span>
                        <span><i class="fas fa-user me-1"></i>Requested by: {{ $refund->requested_by_name }}</span>
                        @if($refund->accepted_by_name)
                            <span><i class="fas fa-hand-paper me-1"></i>Accepted by: {{ $refund->accepted_by_name }}</span>
                        @endif
                        <span><i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($refund->created_at)->format('M d, Y') }}</span>
                        @if($refund->refund_method)
                            <span><i class="fas fa-credit-card me-1"></i>{{ ucfirst($refund->refund_method) }}</span>
                        @endif
                    </div>
                    @if($refund->refund_reference)
                        <div class="mt-1 small text-success">
                            <i class="fas fa-hashtag me-1"></i><strong>Ref. #:</strong> {{ $refund->refund_reference }}
                        </div>
                    @endif
                    @if($refund->refund_proof_path)
                        <div class="mt-1">
                            <a href="{{ asset('storage/' . $refund->refund_proof_path) }}" target="_blank" class="small">
                                <i class="fas fa-image me-1"></i>View Proof Screenshot
                            </a>
                        </div>
                    @endif
                    @if($refund->reason_details)
                        <div class="mt-2 small text-muted bg-light p-2 rounded">
                            <i class="fas fa-comment me-1"></i>{{ $refund->reason_details }}
                        </div>
                    @endif
                    @if($refund->admin_notes)
                        <div class="mt-1 small text-info bg-info bg-opacity-10 p-2 rounded">
                            <i class="fas fa-sticky-note me-1"></i><strong>Admin:</strong> {{ $refund->admin_notes }}
                        </div>
                    @endif
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="amount-display mb-2 d-inline-block">
                        <div class="amount-label">Refund Amount</div>
                        <div class="amount-value" style="color: #dc3545;">-₱{{ number_format($refund->refund_amount, 2) }}</div>
                    </div>
                    <div class="mt-2">
                        @if($refund->refund_status === 'pending')
                            <form method="POST" action="{{ route('sales.prototype.process-refund', $refund->id) }}" class="d-inline ajax-refund-form">
                                @csrf
                                <input type="hidden" name="refund_action" value="accept">
                                <button type="submit" class="btn btn-sm btn-primary me-1">
                                    <i class="fas fa-hand-paper me-1"></i>Accept
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="openRejectRefund({{ $refund->id }})">
                                <i class="fas fa-times me-1"></i>Reject
                            </button>
                        @elseif($refund->refund_status === 'accepted')
                            @if($refund->accepted_by == auth()->id())
                                <button type="button" class="btn btn-sm btn-success" onclick="openCompleteRefund({{ $refund->id }}, {{ $refund->refund_amount }})">
                                    <i class="fas fa-check-circle me-1"></i>Mark Completed
                                </button>
                            @else
                                <span class="text-muted small">
                                    <i class="fas fa-lock me-1"></i>Accepted by {{ $refund->accepted_by_name ?? 'another manager' }}
                                </span>
                            @endif
                        @endif
                        <a href="{{ route('sales.prototype.show', $refund->prototype_sale_id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-external-link-alt me-1"></i>View Sale
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-undo-alt fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No refunds found</h5>
            <p class="text-muted small">Refunds will appear here when reprocess overpayments occur.</p>
        </div>
    </div>
    @endforelse

    <div class="mt-3">
        {{ $refunds->appends(request()->except('page'))->links() }}
    </div>
</div>

<!-- Complete Refund Modal (with proof) -->
<div class="modal fade" id="completeRefundModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form id="completeRefundForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="refund_action" value="complete">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-check-circle me-1 text-success"></i>Complete Refund</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Upload the proof of disbursement to complete this refund. Hindi pwedeng i-complete kung walang screenshot.</p>
                    <div class="mb-3">
                        <label class="form-label">Refund Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="text" name="refund_amount" class="form-control" id="completeRefundAmount" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Proof Screenshot <span class="text-danger">*</span></label>
                        <input type="file" name="refund_proof" class="form-control" accept="image/*" required>
                        <div class="form-text">Required. Upload screenshot ng GCash transfer, bank transaction, etc. (max 5MB)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference Number <span class="text-danger">*</span></label>
                        <input type="text" name="refund_reference" class="form-control" placeholder="e.g. GCash Ref #, Bank Transaction ID" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes <span class="text-danger">*</span></label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Enter notes about this refund" required></textarea>
                        <div class="form-text">Required. Describe the refund details.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check-circle me-1"></i>Confirm Refund Completed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Refund Modal -->
<div class="modal fade" id="rejectRefundModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="rejectRefundForm" method="POST">
                @csrf
                <input type="hidden" name="refund_action" value="reject">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-times-circle me-1 text-danger"></i>Reject Refund</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason for rejection</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Reject Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openCompleteRefund(refundId, amount) {
    var form = document.getElementById('completeRefundForm');
    form.action = '{{ url("sales/prototype/refund") }}/' + refundId + '/process';
    document.getElementById('completeRefundAmount').value = amount;
    var modal = new bootstrap.Modal(document.getElementById('completeRefundModal'));
    modal.show();
}

function openRejectRefund(refundId) {
    var form = document.getElementById('rejectRefundForm');
    form.action = '{{ url("sales/prototype/refund") }}/' + refundId + '/process';
    var modal = new bootstrap.Modal(document.getElementById('rejectRefundModal'));
    modal.show();
}

/* Handle refund action forms via AJAX */
document.addEventListener('DOMContentLoaded', function() {
    /* Accept forms */
    document.querySelectorAll('.ajax-refund-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error processing refund.');
                    btn.disabled = false;
                }
            })
            .catch(function() {
                alert('Network error. Please try again.');
                btn.disabled = false;
            });
        });
    });

    /* Complete form (with file upload) */
    var completeForm = document.getElementById('completeRefundForm');
    if (completeForm) {
        completeForm.addEventListener('submit', function(e) {
            e.preventDefault();

            /* Check file validation */
            var fileInput = this.querySelector('input[name="refund_proof"]');
            if (!fileInput.files || !fileInput.files[0]) {
                alert('Refund proof screenshot is required.');
                return;
            }

            var btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    var modalEl = document.getElementById('completeRefundModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    location.reload();
                } else {
                    alert(data.message || 'Error completing refund.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Confirm Refund Completed';
                }
            })
            .catch(function() {
                alert('Network error.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Confirm Refund Completed';
            });
        });
    }

    /* Reject form submit */
    var rejectForm = document.getElementById('rejectRefundForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Rejecting...';
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    var modalEl = document.getElementById('rejectRefundModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    location.reload();
                } else {
                    alert(data.message || 'Error rejecting refund.');
                    btn.disabled = false;
                    btn.innerHTML = 'Reject Refund';
                }
            })
            .catch(function() {
                alert('Network error.');
                btn.disabled = false;
                btn.innerHTML = 'Reject Refund';
            });
        });
    }
});
</script>
@endpush
