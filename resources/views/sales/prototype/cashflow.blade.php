@extends('layouts.app')

@section('title', 'Cash Flow')

@section('styles')
<style>
    .stat-card { transition: all 0.2s; border-radius: 12px; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .flow-item { padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0; }
    .flow-item:last-child { border-bottom: none; }
    .amount-positive { color: #198754; font-weight: 600; }
    .section-title { font-weight: 600; color: #333; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e9ecef; }
    .audit-entry { padding: 0.5rem 0; border-bottom: 1px solid #f8f9fa; font-size: 0.9rem; }
    .audit-entry:last-child { border-bottom: none; }
    .audit-time { font-size: 0.75rem; color: #adb5bd; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Cash Flow</h1>
            <p class="text-muted mb-0">Per-account payment breakdown</p>
        </div>
        <div>
            <a href="{{ route('sales.verification') }}" class="btn btn-outline-primary">
                <i class="fas fa-check-circle"></i> Verification
            </a>
        </div>
    </div>

    <!-- Account Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sales.cash-flow') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter by Account</label>
                    <select name="account_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ $accountId == $account->id ? 'selected' : '' }}>
                                {{ $account->name }}
                                @if($account->user) ({{ $account->user->name }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <!-- Per Account Stats -->
        @foreach($accounts as $account)
            @php
                $totals = $accountTotals->get($account->id);
                $count = $totals ? $totals->total_count : 0;
                $amount = $totals ? $totals->total_amount : 0;
                $deposit = $totals ? $totals->total_deposit : 0;
            @endphp
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card stat-card border-start border-4 border-success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="mb-0">{{ $account->name }}</h6>
                            @if($account->user)
                                <small class="text-muted">{{ $account->user->name }}</small>
                            @endif
                        </div>
                        <div class="small text-muted mb-2">
                            @if($account->provider) {{ $account->provider }} @endif
                            @if($account->account_number) &middot; {{ $account->account_number }} @endif
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Sales Count:</span>
                            <span class="fw-semibold">{{ $count }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Total Amount:</span>
                            <span class="amount-positive">₱{{ number_format($amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Deposits:</span>
                            <span>₱{{ number_format($deposit, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pending Rejections (awaiting second verifier) -->
    <div class="card shadow-sm mt-4">
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
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1 me-3">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <a href="{{ route('sales.prototype.show', $pr->sale_id) }}" class="fw-semibold text-decoration-none">
                                            {{ $pr->customer_name ?? 'Sale #'.$pr->sale_id }}
                                        </a>
                                        <span class="badge bg-danger">Rejection Pending</span>
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
                                                 onclick="viewScreenshot('{{ $pr->payment_screenshot_path }}')"
                                                 style="max-height: 90px; max-width: 140px; border-radius: 6px; cursor: zoom-in; border: 1px solid #dee2e6; object-fit: cover;"
                                                 title="Click to view payment screenshot">
                                        </div>
                                    @endif
                                    @if($pr->account_name)
                                        <div class="small text-muted mt-1">Account: {{ $pr->account_name }}</div>
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
    <div class="card shadow-sm mt-4">
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
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1 me-3">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <a href="{{ route('sales.prototype.show', $pe->sale_id) }}" class="fw-semibold text-decoration-none">
                                            {{ $pe->customer_name ?? 'Sale #'.$pe->sale_id }}
                                        </a>
                                        <span class="badge bg-info">Edit Pending</span>
                                    </div>
                                    <div class="small text-muted">
                                        {{ $pe->sales_number ?? '' }} · ₱{{ number_format($pe->amount ?? $pe->deposit_paid ?? 0, 2) }} · {{ ucfirst(str_replace('_', ' ', $pe->payment_source ?? '')) }}
                                    </div>
                                    <div class="small mt-1 border-start border-info ps-2">
                                        @if($pe->pending_reference_number)
                                            <div class="text-muted">Ref: <span class="text-decoration-line-through">{{ $pe->reference_number ?: '—' }}</span> → <strong class="text-info">{{ $pe->pending_reference_number }}</strong></div>
                                        @endif
                                        @if($pe->pending_payment_date)
                                            <div class="text-muted">Date: <span class="text-decoration-line-through">{{ optional(\Carbon\Carbon::parse($pe->payment_date))->format('M d, Y') ?: '—' }}</span> → <strong class="text-info">{{ \Carbon\Carbon::parse($pe->pending_payment_date)->format('M d, Y') }}</strong></div>
                                        @endif
                                        @if($pe->pending_amount !== null)
                                            <div class="text-muted">Amount: <span class="text-decoration-line-through">₱{{ number_format((float) ($pe->amount ?? $pe->deposit_paid), 2) }}</span> → <strong class="text-info">₱{{ number_format((float) $pe->pending_amount, 2) }}</strong></div>
                                        @endif
                                    </div>
                                    @if($pe->payment_screenshot_path)
                                        <div class="mt-2">
                                            <img src="{{ $pe->payment_screenshot_path }}" alt="Payment screenshot"
                                                 onclick="viewScreenshot('{{ $pe->payment_screenshot_path }}')"
                                                 style="max-height: 90px; max-width: 140px; border-radius: 6px; cursor: zoom-in; border: 1px solid #dee2e6; object-fit: cover;"
                                                 title="Click to view payment screenshot">
                                        </div>
                                    @endif
                                    @if($pe->account_name)
                                        <div class="small text-muted mt-1">Account: {{ $pe->account_name }}</div>
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

    <div class="row g-3 mt-2">
        <!-- Verified Payments List -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2 text-success"></i>
                        Verified Payments
                        @if($accountId)
                            @php $selectedAccount = $accounts->find($accountId); @endphp
                            @if($selectedAccount) - {{ $selectedAccount->name }} @endif
                        @endif
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($payments->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p class="mb-0">No verified payments found.</p>
                        </div>
                    @else
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sales #</th>
                                    <th>Customer</th>
                                    <th>Account</th>
                                    <th>Amount</th>
                                    <th>Deposit</th>
                                    <th>Verified By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $sale)
                                    <tr>
                                        <td>
                                            <a href="{{ route('sales.prototype.show', $sale->sale_id) }}" class="text-decoration-none">
                                                {{ $sale->sales_number }}
                                            </a>
                                        </td>
                                        <td>{{ $sale->customer_name }}</td>
                                        <td>
                                            @if($sale->account_name)
                                                <span class="badge bg-light text-dark">{{ $sale->account_name }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-semibold">₱{{ number_format($sale->total_amount, 2) }}</td>
                                        <td>₱{{ number_format($sale->deposit_paid, 2) }}</td>
                                        <td>
                                            @if($sale->verified_by_name)
                                                <small>{{ $sale->verified_by_name }}</small>
                                            @else
                                                <small class="text-muted">—</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sale->verified_at)
                                                <small>{{ \Carbon\Carbon::parse($sale->verified_at)->format('M d, Y') }}</small>
                                            @else
                                                <small class="text-muted">—</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-stream me-2 text-primary"></i> Recent Activity</h5>
                </div>
                <div class="card-body p-0">
                    @if($auditLogs->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <small>No recent activity.</small>
                        </div>
                    @else
                        <div class="p-3">
                            @foreach($auditLogs as $log)
                                <div class="audit-entry">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            @if($log->action === 'verified')
                                                <span class="badge bg-success">✓</span>
                                            @elseif($log->action === 'rejected')
                                                <span class="badge bg-danger">✗</span>
                                            @elseif($log->action === 're_tagged')
                                                <span class="badge bg-warning text-dark">↻</span>
                                            @elseif($log->action === 'edited_ref')
                                                <span class="badge bg-info">✎</span>
                                            @elseif($log->action === 'requested_verify')
                                                <span class="badge bg-primary">?</span>
                                            @endif
                                            <strong>{{ $log->user?->name ?? 'System' }}</strong>
                                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                        </div>
                                        <div class="audit-time">{{ $log->created_at->diffForHumans() }}</div>
                                    </div>
                                    @if($log->paymentAccount)
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-user"></i> {{ $log->paymentAccount->name }}
                                            @if($log->prototypeSale)
                                                &middot; <a href="{{ route('sales.prototype.show', $log->prototype_sale_id) }}">{{ $log->prototypeSale->sales_number ?? '#' . $log->prototype_sale_id }}</a>
                                            @endif
                                        </div>
                                    @endif
                                    @if($log->remarks)
                                        <div class="small text-muted mt-1"><i class="fas fa-comment"></i> {{ $log->remarks }}</div>
                                    @endif
                                    @if($log->old_value && $log->new_value)
                                        <div class="small text-muted mt-1">
                                            <span class="text-decoration-line-through">{{ $log->old_value }}</span> → <strong>{{ $log->new_value }}</strong>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CONFIRM REJECT MODAL (second verifier) -->
<div class="modal fade" id="confirmRejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-shield text-danger me-2"></i> Confirm Rejection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">A rejection was requested by <strong id="crRequester">—</strong> for
                    <strong id="crCustomer">—</strong> (<span id="crSaleNumber">—</span>).</p>
                <p class="text-muted small">You are acting as the <strong>second verifier</strong>. Confirming will permanently reject this payment.</p>
                <label class="form-label">Confirmation reason <span class="text-danger">*</span></label>
                <textarea id="crReason" class="form-control" rows="2" placeholder="Why are you confirming the rejection?"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="crConfirmBtn">
                    <i class="fas fa-check-double me-1"></i> Confirm & Reject
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
                <textarea id="ceReason" class="form-control" rows="2" placeholder="Why are you confirming this edit?"></textarea>
            </div>
            <div class="modal-footer">
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
        alert('Confirmation reason is required.');
        return;
    }
    bootstrap.Modal.getInstance(document.getElementById('confirmRejectModal')).hide();
    confirmReject(pendingReject.paymentId, pendingReject.saleId, reason);
});

function confirmReject(paymentId, saleId, reason) {
    var token = document.querySelector('meta[name="csrf-token"]').content;
    fetch('/sales/prototype/' + saleId + '/verify-payment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: JSON.stringify({ payment_id: paymentId, action: 'confirm_reject', remark: reason })
    })
    .then(function(res) { return res.json().then(function(d) { return { ok: res.ok, d: d }; }); })
    .then(function(r) {
        alert(r.ok ? (r.d.message || 'Rejection confirmed!') : (r.d.error || 'An error occurred'));
        if (r.ok) { location.reload(); }
    })
    .catch(function() { alert('An error occurred'); });
}

function cancelReject(paymentId, saleId) {
    if (!confirm('Cancel this rejection request?')) return;
    var token = document.querySelector('meta[name="csrf-token"]').content;
    fetch('/sales/prototype/' + saleId + '/verify-payment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: JSON.stringify({ payment_id: paymentId, action: 'cancel_reject', remark: 'Request cancelled by requester' })
    })
    .then(function(res) { return res.json().then(function(d) { return { ok: res.ok, d: d }; }); })
    .then(function(r) {
        alert(r.ok ? (r.d.message || 'Rejection request cancelled!') : (r.d.error || 'An error occurred'));
        if (r.ok) { location.reload(); }
    })
    .catch(function() { alert('An error occurred'); });
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
        alert('Confirmation reason is required.');
        return;
    }
    bootstrap.Modal.getInstance(document.getElementById('confirmEditModal')).hide();
    confirmEdit(pendingEdit.paymentId, pendingEdit.saleId, reason);
});

function confirmEdit(paymentId, saleId, reason) {
    var token = document.querySelector('meta[name="csrf-token"]').content;
    fetch('/sales/prototype/' + saleId + '/verify-payment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: JSON.stringify({ payment_id: paymentId, action: 'confirm_edit', remark: reason })
    })
    .then(function(res) { return res.json().then(function(d) { return { ok: res.ok, d: d }; }); })
    .then(function(r) {
        alert(r.ok ? (r.d.message || 'Edit confirmed and applied!') : (r.d.error || 'An error occurred'));
        if (r.ok) { location.reload(); }
    })
    .catch(function() { alert('An error occurred'); });
}

function cancelEdit(paymentId, saleId) {
    if (!confirm('Cancel this edit request?')) return;
    var token = document.querySelector('meta[name="csrf-token"]').content;
    fetch('/sales/prototype/' + saleId + '/verify-payment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: JSON.stringify({ payment_id: paymentId, action: 'cancel_edit', remark: 'Request cancelled by requester' })
    })
    .then(function(res) { return res.json().then(function(d) { return { ok: res.ok, d: d }; }); })
    .then(function(r) {
        alert(r.ok ? (r.d.message || 'Edit request cancelled!') : (r.d.error || 'An error occurred'));
        if (r.ok) { location.reload(); }
    })
    .catch(function() { alert('An error occurred'); });
}

function viewScreenshot(src) {
    var old = document.getElementById('imageLightbox');
    if (old) old.remove();

    var overlay = document.createElement('div');
    overlay.id = 'imageLightbox';
    overlay.style.cssText = 'display:flex!important;align-items:center;justify-content:center;position:fixed;top:0;left:0;width:100%;height:100%;z-index:100000;background:rgba(0,0,0,0.85);cursor:zoom-out;';

    var img = document.createElement('img');
    img.src = src;
    img.style.cssText = 'max-width:92%;max-height:92%;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.5);';

    overlay.appendChild(img);
    overlay.addEventListener('click', function() { overlay.remove(); });
    document.body.appendChild(overlay);
}
</script>
@endpush
