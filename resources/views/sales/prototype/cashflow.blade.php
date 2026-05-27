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
                                            <a href="{{ route('sales.prototype.show', $sale->id) }}" class="text-decoration-none">
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
@endsection
