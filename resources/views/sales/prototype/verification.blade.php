@extends('layouts.app')

@section('title', 'Payment Verification')

@section('styles')
<style>
    .payment-card { transition: all 0.2s; border-left: 4px solid #dee2e6; }
    .payment-card.pending { border-left-color: #ffc107; }
    .payment-card.verified { border-left-color: #198754; }
    .payment-card.rejected { border-left-color: #dc3545; }
    .payment-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); transform: translateX(2px); }
    .status-badge { font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 50px; }
    .audit-entry { padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0; }
    .audit-entry:last-child { border-bottom: none; }
    .audit-time { font-size: 0.75rem; color: #999; }
    .modal-xl-custom { max-width: 900px; }
    .verification-sidebar { position: sticky; top: 80px; }
    .section-title { font-weight: 600; color: #333; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e9ecef; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Payment Verification</h1>
            <p class="text-muted mb-0">Verify, re-tag, or manage payment records</p>
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
                            @foreach($pendingPayments as $sale)
                                <div class="list-group-item payment-card {{ $sale->payment_status }}" data-sale-id="{{ $sale->id }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1 me-3">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <a href="{{ route('sales.prototype.show', $sale->id) }}" class="fw-semibold text-decoration-none">
                                                    {{ $sale->sales_number }}
                                                </a>
                                                @if($sale->payment_status === 'pending')
                                                    <span class="badge bg-warning status-badge">Pending</span>
                                                @elseif($sale->payment_status === 'rejected')
                                                    <span class="badge bg-danger status-badge">Rejected</span>
                                                @endif
                                                @if($sale->verify_requested_at)
                                                    <span class="badge bg-info status-badge"><i class="fas fa-exclamation-circle"></i> Verify Requested</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted">
                                                <strong>{{ $sale->customer_name }}</strong> &middot;
                                                ₱{{ number_format($sale->total_amount, 2) }}
                                                @if($sale->deposit_paid > 0)
                                                    &middot; Paid: ₱{{ number_format($sale->deposit_paid, 2) }}
                                                @endif
                                            </div>
                                            <div class="small mt-1">
                                                @if($sale->account_name)
                                                    <span class="badge bg-light text-dark me-1">
                                                        <i class="fas fa-user"></i> {{ $sale->account_name }}
                                                    </span>
                                                @endif
                                                @if($sale->reference_number)
                                                    <span class="badge bg-light text-dark me-1">
                                                        <i class="fas fa-hashtag"></i> {{ $sale->reference_number }}
                                                    </span>
                                                @endif
                                                @if($sale->payment_date)
                                                    <span class="badge bg-light text-dark me-1">
                                                        <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($sale->payment_date)->format('M d, Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($sale->requested_by_name)
                                                <div class="small text-info mt-1">
                                                    <i class="fas fa-question-circle"></i> Requested by {{ $sale->requested_by_name }}
                                                    @if($sale->verify_requested_at)
                                                        on {{ \Carbon\Carbon::parse($sale->verify_requested_at)->format('M d, g:i A') }}
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <button class="btn btn-sm btn-success" onclick="verifySale({{ $sale->id }}, 'verify')" title="Verify Payment">
                                                <i class="fas fa-check"></i> Verify
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="verifySale({{ $sale->id }}, 'reject')" title="Reject Payment">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="showTagModal({{ $sale->id }}, {{ $sale->payment_account_id ?? 'null' }})" title="Re-tag Account">
                                                <i class="fas fa-tag"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="showEditModal({{ $sale->id }})" title="Edit Ref/Date">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @if($sale->payment_screenshot_path)
                                        <div class="mt-2">
                                            <a href="{{ $sale->payment_screenshot_path }}" target="_blank" class="small text-primary">
                                                <i class="fas fa-image"></i> View Screenshot
                                            </a>
                                        </div>
                                    @endif
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
                            @foreach($verifiedPayments as $sale)
                                <div class="list-group-item payment-card verified">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <a href="{{ route('sales.prototype.show', $sale->id) }}" class="fw-semibold text-decoration-none">
                                                    {{ $sale->sales_number }}
                                                </a>
                                                <span class="badge bg-success status-badge">Verified</span>
                                            </div>
                                            <div class="small text-muted">
                                                {{ $sale->customer_name }} &middot; ₱{{ number_format($sale->total_amount, 2) }}
                                            </div>
                                            <div class="small text-muted mt-1">
                                                @if($sale->account_name)
                                                    <span class="badge bg-light text-dark"><i class="fas fa-user"></i> {{ $sale->account_name }}</span>
                                                @endif
                                                @if($sale->verified_by_name)
                                                    Verified by {{ $sale->verified_by_name }}
                                                    @if($sale->verified_at)
                                                        &middot; {{ \Carbon\Carbon::parse($sale->verified_at)->format('M d, g:i A') }}
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-info" onclick="showAuditLogs({{ $sale->id }})">
                                            <i class="fas fa-history"></i> Log
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="verification-sidebar">
                <!-- Quick Stats -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="section-title">Quick Stats</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Pending</span>
                            <span class="badge bg-warning">{{ $pendingPayments->where('payment_status', 'pending')->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Rejected</span>
                            <span class="badge bg-danger">{{ $pendingPayments->where('payment_status', 'rejected')->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Verified (30d)</span>
                            <span class="badge bg-success">{{ $verifiedPayments->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Verify Requested</span>
                            <span class="badge bg-info">{{ $pendingPayments->where('verify_requested_at')->count() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Accounts Summary -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="section-title">Payment Accounts</h6>
                        @foreach($accounts as $account)
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-semibold">{{ $account->name }}</span>
                                    @if($account->user)
                                        <small class="text-muted">{{ $account->user->name }}</small>
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    @if($account->provider)
                                        {{ $account->provider }}
                                    @endif
                                    @if($account->account_number)
                                        &middot; {{ $account->account_number }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Verification Remark Modal -->
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verifyModalTitle">Verify Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Remark (optional)</label>
                    <textarea class="form-control" id="verifyRemark" rows="2" placeholder="Any notes about this verification..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmVerifyBtn" onclick="confirmVerify()">
                    <i class="fas fa-check"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Re-tag Modal -->
<div class="modal fade" id="tagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Re-tag Payment Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">New Account *</label>
                    <select class="form-select" id="newAccountId">
                        <option value="">Select account...</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">
                                {{ $account->name }}
                                @if($account->user) ({{ $account->user->name }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason for re-tagging</label>
                    <textarea class="form-control" id="tagRemark" rows="2" placeholder="Why are you changing the account?"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmTag()">
                    <i class="fas fa-tag"></i> Re-tag
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Reference/Date Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Reference Number</label>
                    <input type="text" class="form-control" id="editRefNumber" placeholder="e.g., GCash ref">
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Date</label>
                    <input type="date" class="form-control" id="editPaymentDate">
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason for edit</label>
                    <textarea class="form-control" id="editRemark" rows="2" placeholder="Why are you changing these details?"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmEdit()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Audit Log Modal -->
<div class="modal fade" id="auditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-history me-2"></i> Audit Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="auditLogBody">
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading audit logs...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentSaleId = null;
let currentAction = null;

function verifySale(saleId, action) {
    currentSaleId = saleId;
    currentAction = action;
    document.getElementById('verifyModalTitle').textContent = action === 'verify' ? 'Verify Payment' : 'Reject Payment';
    document.getElementById('verifyRemark').value = '';
    document.getElementById('confirmVerifyBtn').className = action === 'verify' ? 'btn btn-success' : 'btn btn-danger';
    document.getElementById('confirmVerifyBtn').innerHTML = action === 'verify'
        ? '<i class="fas fa-check"></i> Confirm Verify'
        : '<i class="fas fa-times"></i> Confirm Reject';
    var modal = new bootstrap.Modal(document.getElementById('verifyModal'));
    modal.show();
}

function confirmVerify() {
    var remark = document.getElementById('verifyRemark').value;
    fetch('{{ url("sales/prototype") }}/' + currentSaleId + '/verify-payment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ action: currentAction, remark: remark })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(e => { alert('Request failed: ' + e); });
}

function showTagModal(saleId, currentAccountId) {
    currentSaleId = saleId;
    document.getElementById('newAccountId').value = currentAccountId || '';
    document.getElementById('tagRemark').value = '';
    var modal = new bootstrap.Modal(document.getElementById('tagModal'));
    modal.show();
}

function confirmTag() {
    var newAccountId = document.getElementById('newAccountId').value;
    var remark = document.getElementById('tagRemark').value;
    if (!newAccountId) { alert('Please select a new account.'); return; }

    fetch('{{ url("sales/prototype") }}/' + currentSaleId + '/verify-payment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ action: 're_tag', new_account_id: newAccountId, remark: remark })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { location.reload(); }
        else { alert('Error: ' + (data.error || 'Unknown error')); }
    })
    .catch(e => { alert('Request failed: ' + e); });
}

function showEditModal(saleId) {
    currentSaleId = saleId;
    document.getElementById('editRefNumber').value = '';
    document.getElementById('editPaymentDate').value = '';
    document.getElementById('editRemark').value = '';
    var modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}

function confirmEdit() {
    var newRef = document.getElementById('editRefNumber').value;
    var newDate = document.getElementById('editPaymentDate').value;
    var remark = document.getElementById('editRemark').value;
    if (!newRef && !newDate) { alert('Please enter a new reference number or payment date.'); return; }

    fetch('{{ url("sales/prototype") }}/' + currentSaleId + '/verify-payment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ action: 'edit_ref', new_reference_number: newRef, new_payment_date: newDate, remark: remark })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { location.reload(); }
        else { alert('Error: ' + (data.error || 'Unknown error')); }
    })
    .catch(e => { alert('Request failed: ' + e); });
}

function showAuditLogs(saleId) {
    document.getElementById('auditLogBody').innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading...</p></div>';
    var modal = new bootstrap.Modal(document.getElementById('auditModal'));
    modal.show();

    fetch('{{ url("sales/audit-logs") }}/' + saleId)
    .then(r => r.json())
    .then(logs => {
        if (logs.length === 0) {
            document.getElementById('auditLogBody').innerHTML = '<div class="text-center py-4 text-muted">No audit logs found.</div>';
            return;
        }
        var html = '';
        logs.forEach(function(log) {
            var actionBadge = '';
            if (log.action === 'verified') actionBadge = '<span class="badge bg-success">Verified</span>';
            else if (log.action === 'rejected') actionBadge = '<span class="badge bg-danger">Rejected</span>';
            else if (log.action === 're_tagged') actionBadge = '<span class="badge bg-warning text-dark">Re-tagged</span>';
            else if (log.action === 'edited_ref') actionBadge = '<span class="badge bg-info">Edited</span>';
            else if (log.action === 'requested_verify') actionBadge = '<span class="badge bg-primary">Requested</span>';
            else actionBadge = '<span class="badge bg-secondary">' + log.action + '</span>';

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
    .catch(e => {
        document.getElementById('auditLogBody').innerHTML = '<div class="text-center py-4 text-danger">Failed to load audit logs.</div>';
    });
}
</script>
@endsection
