@extends('layouts.app')

@section('title', 'Order #' . $sale->sales_number)

@push('styles')
<style>
    .detail-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .detail-title {
        font-weight: 600;
        font-size: 18px;
        color: #333;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #667eea;
    }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .info-label { color: #6c757d; font-size: 0.85rem; }
    .info-value { font-weight: 500; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Order #{{ $sale->sales_number }}</h1>
            <p class="text-muted mb-0">{{ $sale->customer_name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.prototype.edit', $sale->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ url('/sales/prototype/kanban/' . ($sale->department_code ?? '')) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <!-- Customer Info -->
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-user me-2"></i>Customer Information</h5>
                <div class="info-grid">
                    <div>
                        <div class="info-label">Customer Name</div>
                        <div class="info-value">{{ $sale->customer_name }}</div>
                    </div>
                    <div>
                        <div class="info-label">Sales Number</div>
                        <div class="info-value">{{ $sale->sales_number }}</div>
                    </div>
                    @if($sale->customer_phone)
                    <div>
                        <div class="info-label">Phone</div>
                        <div class="info-value">{{ $sale->customer_phone }}</div>
                    </div>
                    @endif
                    <div>
                        <div class="info-label">Sales Agent</div>
                        <div class="info-value">{{ $sale->sales_agent_name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="info-label">Department</div>
                        <div class="info-value">{{ $sale->department_name ?? 'N/A' }}</div>
                    </div>
                    @if($sale->marketplace)
                    <div>
                        <div class="info-label">Marketplace</div>
                        <div class="info-value">{{ $sale->marketplace }}</div>
                    </div>
                    @endif
                </div>
                @if($sale->customer_notes)
                    <div class="mt-3 p-3 bg-light rounded">
                        <div class="info-label">Customer Notes</div>
                        <p class="mb-0 mt-1">{{ $sale->customer_notes }}</p>
                    </div>
                @endif
                @if($sale->internal_notes)
                    <div class="mt-3 p-3 bg-light rounded">
                        <div class="info-label">Internal Notes</div>
                        <p class="mb-0 mt-1">{{ $sale->internal_notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Order Items -->
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-box me-2"></i>Order Items</h5>
                @if($services && count($services) > 0)
                    @foreach($services as $item)
                        <div class="p-3 mb-2 bg-light rounded">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $item['name'] ?? 'Item' }}</strong>
                                    @if(isset($item['qty'])) <span class="text-muted">× {{ $item['qty'] }}</span> @endif
                                </div>
                                @if(isset($item['price']))
                                    <div class="fw-semibold">₱{{ number_format($item['price'], 2) }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">No items found.</p>
                @endif
            </div>

            <!-- Notes -->
            @if($sale->reason)
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-sticky-note me-2"></i>Reason</h5>
                <p>{{ $sale->reason }}</p>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Status & Progress -->
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-tasks me-2"></i>Status</h5>
                <div class="mb-3">
                    <div class="info-label">Kanban Status</div>
                    <div class="mt-1">
                        <span class="badge bg-{{ $sale->kanban_status === 'delivered' || $sale->kanban_status === 'completed' ? 'success' : ($sale->kanban_status === 'cancelled' ? 'danger' : 'primary') }} fs-6">
                            {{ ucfirst($sale->kanban_status ?? 'New') }}
                        </span>
                    </div>
                </div>
                @if($kanbanItem)
                <div class="mb-3">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $kanbanItem->progress ?? 0 }}%;"></div>
                    </div>
                    <small class="text-muted">{{ $kanbanItem->progress ?? 0 }}% complete</small>
                </div>
                @endif
                <div>
                    <div class="info-label">Created</div>
                    <div>{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y g:i A') }}</div>
                </div>
                @if($sale->date_needed)
                <div class="mt-2">
                    <div class="info-label">Date Needed</div>
                    <div>{{ \Carbon\Carbon::parse($sale->date_needed)->format('M d, Y') }}</div>
                </div>
                @endif
            </div>

            <!-- Payment Info -->
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-credit-card me-2"></i>Payment</h5>
                <div class="mb-2">
                    <div class="info-label">Subtotal</div>
                    <div class="info-value">₱{{ number_format($sale->subtotal ?? 0, 2) }}</div>
                </div>
                @if(($sale->tax ?? 0) > 0)
                <div class="mb-2">
                    <div class="info-label">Tax</div>
                    <div class="info-value">₱{{ number_format($sale->tax, 2) }}</div>
                </div>
                @endif
                <div class="mb-2">
                    <div class="info-label">Total Amount</div>
                    <div class="info-value fw-bold fs-5">₱{{ number_format($sale->total_amount ?? 0, 2) }}</div>
                </div>
                @if(($sale->deposit_paid ?? 0) > 0)
                <div class="mb-2">
                    <div class="info-label">Deposit Paid</div>
                    <div class="info-value text-success">₱{{ number_format($sale->deposit_paid, 2) }}</div>
                </div>
                @endif
                @if(($sale->balance_due ?? 0) > 0)
                <div class="mb-2">
                    <div class="info-label">Balance Due</div>
                    <div class="info-value text-danger fw-bold">₱{{ number_format($sale->balance_due, 2) }}</div>
                </div>
                @endif
                <hr>
                <div class="mb-2">
                    <div class="info-label">Payment Method</div>
                    <div>{{ ucfirst($sale->payment_method ?? 'N/A') }}</div>
                </div>
                <div class="mb-2">
                    <div class="info-label">Paid By</div>
                    <div>{{ ucfirst($sale->payment_owner ?? 'N/A') }}</div>
                </div>
                @php
                    $paymentAccount = $sale->payment_account_id ? \App\Models\PaymentAccount::find($sale->payment_account_id) : null;
                @endphp
                @if($paymentAccount)
                <div class="mb-2">
                    <div class="info-label">Account</div>
                    <div>
                        <strong>{{ $paymentAccount->name }}</strong>
                        @if($paymentAccount->user)
                            <small class="text-muted">({{ $paymentAccount->user->name }})</small>
                        @endif
                    </div>
                </div>
                @endif
                <div class="mb-2">
                    <div class="info-label">Payment Status</div>
                    <div>
                        @if($sale->payment_status === 'verified')
                            <span class="badge bg-success fs-6">✅ Verified</span>
                            @if($sale->verified_at)
                                <small class="text-muted d-block mt-1">{{ \Carbon\Carbon::parse($sale->verified_at)->format('M d, g:i A') }}</small>
                            @endif
                        @elseif($sale->payment_status === 'rejected')
                            <span class="badge bg-danger fs-6">❌ Rejected</span>
                        @elseif($sale->payment_status === 'pending' && $sale->payment_account_id)
                            <span class="badge bg-warning text-dark fs-6">⏳ Pending Verification</span>
                        @else
                            <span class="badge bg-secondary fs-6">—</span>
                        @endif
                    </div>
                </div>
                @if($sale->reference_number)
                <div class="mb-2">
                    <div class="info-label">Reference Number</div>
                    <div>{{ $sale->reference_number }}</div>
                </div>
                @endif
                @if($sale->payment_date)
                <div class="mb-2">
                    <div class="info-label">Payment Date</div>
                    <div>{{ \Carbon\Carbon::parse($sale->payment_date)->format('M d, Y') }}</div>
                </div>
                @endif
                @if($sale->verify_requested_by)
                <div class="mb-2">
                    <div class="info-label">Verification Requested</div>
                    <div>
                        <span class="badge bg-info"><i class="fas fa-exclamation-circle"></i> Requested</span>
                        @if($sale->verify_requested_at)
                            <small class="text-muted d-block mt-1">{{ \Carbon\Carbon::parse($sale->verify_requested_at)->format('M d, g:i A') }}</small>
                        @endif
                    </div>
                </div>
                @endif
                @if($sale->payment_screenshot_path)
                <div class="mt-3">
                    <div class="info-label">Payment Screenshot</div>
                    <a href="{{ $sale->payment_screenshot_path }}" target="_blank">
                        <img src="{{ $sale->payment_screenshot_path }}" alt="Payment Screenshot" class="img-fluid rounded mt-1" style="max-height:200px;">
                    </a>
                </div>
                @endif
            </div>

            <!-- Audit Log -->
            @php
                $logs = $sale->payment_account_id ? \App\Models\PaymentAuditLog::with(['user', 'paymentAccount'])
                    ->where('prototype_sale_id', $sale->id)
                    ->orderBy('created_at', 'desc')
                    ->get() : collect();
            @endphp
            @if($logs->count() > 0)
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-history me-2"></i>Audit Log ({{ $logs->count() }})</h5>
                @foreach($logs as $log)
                    <div class="p-2 mb-1 bg-light rounded">
                        <div class="d-flex justify-content-between small">
                            <div>
                                @if($log->action === 'verified')
                                    <span class="badge bg-success">Verified</span>
                                @elseif($log->action === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @elseif($log->action === 're_tagged')
                                    <span class="badge bg-warning text-dark">Re-tagged</span>
                                @elseif($log->action === 'edited_ref')
                                    <span class="badge bg-info">Edited</span>
                                @elseif($log->action === 'requested_verify')
                                    <span class="badge bg-primary">Requested Verify</span>
                                @endif
                                <strong>{{ $log->user?->name ?? 'System' }}</strong>
                            </div>
                            <span class="text-muted">{{ $log->created_at->format('M d, g:i A') }}</span>
                        </div>
                        @if($log->paymentAccount)
                            <div class="small text-muted mt-1">Account: {{ $log->paymentAccount->name }}</div>
                        @endif
                        @if($log->old_value && $log->new_value)
                            <div class="small text-muted">
                                <span class="text-decoration-line-through">{{ $log->old_value }}</span> → <strong>{{ $log->new_value }}</strong>
                            </div>
                        @endif
                        @if($log->remarks)
                            <div class="small text-muted mt-1"><em>{{ $log->remarks }}</em></div>
                        @endif
                    </div>
                @endforeach
            </div>
            @endif

            <!-- Verified By -->
            @if($sale->verified_by)
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-user-check me-2"></i>Verified By</h5>
                <div class="info-value">{{ \App\Models\User::find($sale->verified_by)?->name ?? 'Unknown' }}</div>
                @if($sale->verified_at)
                    <div class="text-muted small">{{ \Carbon\Carbon::parse($sale->verified_at)->format('M d, Y g:i A') }}</div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
