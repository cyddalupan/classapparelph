@extends('layouts.app')

@section('title', 'Cash Flow')

@section('styles')
<style>
    .stat-card { transition: all 0.2s; border-radius: 12px; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .flow-item { padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0; }
    .flow-item:last-child { border-bottom: none; }
    .amount-positive { color: #198754; font-weight: 600; }
    .account-avatar {
        width: 42px; height: 42px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 1.1rem; flex-shrink: 0;
    }
    .bg-gcash { background: linear-gradient(135deg, #00c6fb, #005bea); }
    .bg-bank_transfer { background: linear-gradient(135deg, #667eea, #764ba2); }
    .bg-cash { background: linear-gradient(135deg, #11998e, #38ef7d); }
    .bg-paymaya { background: linear-gradient(135deg, #f857a6, #ff5858); }
    .bg-credit_card { background: linear-gradient(135deg, #f2994a, #f2c94c); }
    .bg-other { background: linear-gradient(135deg, #6c757d, #495057); }
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

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-2">
            <h6 class="mb-0"><i class="fas fa-filter me-2 text-primary"></i> Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('sales.cash-flow') }}" class="row g-2 align-items-end">
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Account</label>
                    <select name="account_id" class="form-select">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ $accountId == $account->id ? 'selected' : '' }}>
                                {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Sales Agent</label>
                    <select name="agent_id" class="form-select">
                        <option value="">All Agents</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ $agentId == $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Payment Method</label>
                    <select name="method" class="form-select">
                        <option value="">All Methods</option>
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm }}" {{ $method == $pm ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $pm)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Customer / Sales # / Ref #" value="{{ $search }}">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="{{ route('sales.cash-flow') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </a>
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
                $amount = $totals ? $totals->total_deposit : 0;

                $saleTotals = $accountSaleTotals->get($account->id);
                $saleCount = $saleTotals ? $saleTotals->sale_count : 0;
                // Net collected = verified payments minus completed refunds
                $refundTotal = $accountRefundTotals->get($account->id)->total_refunded ?? 0;
                $netAmount = max($amount - $refundTotal, 0);

                $pendingCount = ($pendingCounts->get($account->id)->pending_count ?? 0) + ($pendingDepositCounts->get($account->id)->pending_count ?? 0);
                $isActive = $accountId == $account->id;
            @endphp
            <div class="col-xl-3 col-lg-4 col-md-6">
                <a href="{{ route('sales.cash-flow', ['account_id' => $account->id]) }}" class="text-decoration-none">
                    <div class="card stat-card h-100 {{ $isActive ? 'border-primary shadow' : 'border-0' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="account-avatar bg-{{ $account->provider ?: 'other' }}">
                                        <i class="fas {{ $account->provider == 'gcash' ? 'fa-mobile-alt' : ($account->provider == 'bank_transfer' ? 'fa-university' : ($account->provider == 'cash' ? 'fa-money-bill-wave' : 'fa-wallet')) }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-dark fw-bold">{{ $account->name }}</h6>
                                        <small class="text-muted">
                                            @if($account->provider) {{ ucfirst(str_replace('_', ' ', $account->provider)) }} @endif
                                            @if($account->account_number) &middot; {{ $account->account_number }} @endif
                                        </small>
                                    </div>
                                </div>
                                @if($pendingCount > 0)
                                    <span class="badge bg-warning text-dark" title="{{ $pendingCount }} pending verification">
                                        <i class="fas fa-clock"></i> {{ $pendingCount }}
                                    </span>
                                @endif
                            </div>

                            <div class="rounded-3 p-3 mb-3" style="background: linear-gradient(135deg, #f8f9fa, #e9f7ef);">
                                <div class="small text-muted text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Collected</div>
                                <div class="amount-positive fs-4 fw-bold">₱{{ number_format($netAmount, 2) }}</div>
                            </div>

                            <div class="d-flex justify-content-between pt-2 border-top">
                                <span class="text-muted small"><i class="fas fa-receipt me-1"></i> Sales</span>
                                <span class="fw-semibold">{{ $saleCount }}</span>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="text-muted small"><i class="fas fa-coins me-1"></i> Payments</span>
                                <span class="fw-semibold">{{ $count }}</span>
                            </div>
                            @if($refundTotal > 0)
                            <div class="d-flex justify-content-between mt-1">
                                <span class="text-muted small"><i class="fas fa-undo me-1"></i> Refunded</span>
                                <span class="text-danger small fw-semibold">− ₱{{ number_format($refundTotal, 2) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </a>
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
                                    <th>Type</th>
                                    <th>Amount</th>
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
                                        <td>
                                            @php
                                                $payType = match($sale->payment_type) {
                                                    'down_payment' => ['Down Payment', 'info'],
                                                    'additional' => ['Additional', 'primary'],
                                                    'fullpayment', 'full_payment' => ['Full Payment', 'success'],
                                                    default => [ucwords(str_replace('_', ' ', $sale->payment_type ?? 'Payment')), 'secondary'],
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $payType[1] }}">{{ $payType[0] }}</span>
                                        </td>
                                        <td class="fw-semibold">₱{{ number_format($sale->amount, 2) }}</td>
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
