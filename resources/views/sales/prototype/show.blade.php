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
    .ref-image:hover, .payment-img:hover {
        opacity: 0.85;
    }
    .detail-title {
        font-weight: 600;
        font-size: 18px;
        color: #333;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #667eea;
    }
    /* Fullsublimation modal styles in partial */
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
            @if($canEdit)
            <button type="button" class="btn btn-success" onclick="openSubAddProductModal()">
                <i class="fas fa-plus-circle"></i> Add Product
            </button>
            <a href="{{ route('sales.prototype.edit-items', $sale->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endif
            <a href="{{ route('sales.prototype.print-slip', $sale->id) }}" target="_blank" class="btn btn-success">
                <i class="fas fa-print"></i> Print Slip
            </a>
            <a href="{{ url('/sales/prototype/kanban/' . ($sale->department_code ?? '')) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

@if(isset($relatedSales) && $relatedSales->count() > 0)
    <div class="alert alert-info mb-3">
        <div class="d-flex align-items-start">
            <i class="fas fa-layer-group me-3 fa-lg mt-1"></i>
            <div class="flex-grow-1">
                <strong>Multi-Department Transaction</strong><br>
                <small>This sale is part of a group with {{ $relatedSales->count() }} other department sale(s):
                @foreach($relatedSales as $rs)
                    <a href="{{ route('sales.prototype.show', $rs->id) }}" class="alert-link me-2">
                        <span class="badge bg-{{ $rs->department_code ?? 'secondary' }}">{{ $rs->department_name }} ({{ $rs->sales_number }})</span>
                    </a>
                @endforeach
                </small>
                <div class="mt-2 p-2 bg-white bg-opacity-25 rounded small">
                    <strong>Overall Transaction Total:</strong>
                    <span class="ms-2">₱{{ number_format($overallGroupTotal, 2) }}</span>
                    <span class="mx-2 text-muted">|</span>
                    <strong>Deposit:</strong>
                    <span class="ms-2">₱{{ number_format($overallGroupDeposit, 2) }}</span>
                    <span class="mx-2 text-muted">|</span>
                    <strong>Balance Due:</strong>
                    <span class="ms-2">₱{{ number_format($overallGroupBalance, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
@endif

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
                    @if(($sale->marketplace ?? false))
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
                        @php
                            $itemTotal = $item['totalPrice'] ?? $item['total_price'] ?? $item['price'] ?? 0;
                            $itemName = $item['name'] ?? $item['product_name'] ?? 'Item #' . ($loop->index + 1);
                            $itemNotes = $item['notes'] ?? '';
                            $subItems = $item['subItems'] ?? [];
                            $printing = $item['printing'] ?? null;
                            $refImages = $item['referenceImages'] ?? [];
                        @endphp
                        <div class="p-3 mb-2 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong>{{ $itemName }}</strong>
                                    @if(isset($item['department']))
                                        <span class="badge bg-secondary">{{ $item['department'] }}</span>
                                    @endif
                                </div>
                                <div class="fw-bold text-nowrap">₱{{ number_format($itemTotal, 2) }}</div>
                            </div>

                            @if(!empty($subItems))
                                <div class="mb-2">
                                    @foreach($subItems as $si)
                                        @php
                                            $brand = $si['brand'] ?? $si['product_brand'] ?? '';
                                            $size = $si['size'] ?? $si['type'] ?? $si['product_size'] ?? '';
                                            $color = $si['color'] ?? $si['product_color'] ?? '';
                                            $qty = $si['qty'] ?? $si['quantity'] ?? 1;
                                            $unitPrice = $si['price'] ?? $si['unit_price'] ?? 0;
                                            $parts = [];
                                            if ($brand) $parts[] = $brand;
                                            if ($size) $parts[] = $size;
                                            if ($color) $parts[] = $color;
                                            $parts[] = '×' . $qty;
                                            if ($unitPrice > 0) $parts[] = '₱' . number_format($unitPrice, 2);
                                        @endphp
                                        <div class="small text-muted">{{ implode(' • ', $parts) }}</div>
                                    @endforeach
                                </div>
                            @endif

                            @if($printing)
                                <div class="small mb-2 p-2 bg-white rounded">
                                    <div class="fw-semibold mb-1">🖨️ Print Details</div>
                                    @if(isset($printing['printType']))
                                        <div><span class="text-muted">Type:</span> {{ $printing['printType'] }}</div>
                                    @endif
                                    @if(!empty($printing['printSizes'] ?? []))
                                        <div><span class="text-muted">Sizes:</span> {{ is_array($printing['printSizes']) ? implode(', ', $printing['printSizes']) : $printing['printSizes'] }}</div>
                                    @endif
                                    <div><span class="text-muted">Qty:</span> {{ $printing['printQty'] ?? 'N/A' }}</div>
                                    @if(($printing['printSubtotal'] ?? 0) > 0)
                                        <div><span class="text-muted">Print Subtotal:</span> ₱{{ number_format($printing['printSubtotal'], 2) }}</div>
                                    @endif
                                    @if($printing['isSpecialPrice'] ?? false)
                                        <div class="text-warning">⭐ Special Price: {{ $printing['specialReason'] ?? '' }}</div>
                                    @endif
                                </div>
                            @endif

                            @if($itemNotes)
                                <div class="mt-2 small"><span class="text-muted">📝 Notes:</span> {{ $itemNotes }}</div>
                            @endif

                            @if(!empty($refImages))
                                <div class="mt-2">
                                    <div class="small text-muted mb-1">🖼️ Reference Images ({{ count($refImages) }})</div>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($refImages as $rimg)
                                            @php $src = $rimg['dataUrl'] ?? $rimg['url'] ?? $rimg['src'] ?? ''; @endphp
                                            @if($src)
                                                <img src="{{ $src }}" alt="{{ $rimg['name'] ?? 'Image' }}" style="max-width:100px;max-height:80px;border-radius:4px;cursor:pointer;" class="border ref-image" onclick="openLightbox(this.src)">
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">No items found.</p>
                @endif
            </div>

            <!-- Notes -->
            @if(($sale->reason ?? false))
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
                <div class="mb-3">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progressPercent }}%;"></div>
                    </div>
                    <small class="text-muted">{{ $progressPercent }}% complete</small>
                </div>
                <div>
                    <div class="info-label">Created</div>
                    <div>{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y g:i A') }}</div>
                </div>
                @if(($sale->date_needed ?? false))
                <div class="mt-2">
                    <div class="info-label">Date Needed</div>
                    <div>{{ \Carbon\Carbon::parse($sale->date_needed)->format('M d, Y') }}</div>
                </div>
                @endif
                
            </div>

            <!-- Pending Changes -->
            @if(isset($pendingChanges) && $pendingChanges->count() > 0)
            <div class="detail-section border-start border-4 border-warning">
                <h5 class="detail-title">
                    <i class="fas fa-clock text-warning me-2"></i>Pending Changes
                </h5>
                @foreach($pendingChanges as $change)
                <div class="mb-3 p-3 bg-light rounded">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-warning text-dark">Awaiting Approval</span>
                            <small class="text-muted ms-2">{{ \Carbon\Carbon::parse($change->created_at)->diffForHumans() }}</small>
                        </div>
                        <small class="text-muted">by {{ \App\Models\User::find($change->submitted_by)?->name ?? 'Unknown' }}</small>
                    </div>
                    
                    <p class="mb-2">{{ $change->change_summary }}</p>
                    
                    <div class="row text-center g-2 mb-2">
                        <div class="col-4">
                            <small class="text-muted d-block">Current Total</small>
                            <strong>₱{{ number_format($change->total_before, 2) }}</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">New Total</small>
                            <strong>₱{{ number_format($change->total_after, 2) }}</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Difference</small>
                            <strong class="{{ $change->total_after >= $change->total_before ? 'text-success' : 'text-danger' }}">
                                {{ $change->total_after >= $change->total_before ? '+' : '-' }}₱{{ number_format(abs($change->total_after - $change->total_before), 2) }}
                            </strong>
                        </div>
                    </div>
                    
                    @if($isManager)
                    <div class="d-flex gap-2">
                        <button class="btn btn-success btn-sm" onclick="approveChange({{ $change->id }})">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="showRejectModal({{ $change->id }})">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            <!-- Payment Info -->
            <div class="detail-section">
                <h5 class="detail-title"><i class="fas fa-credit-card me-2"></i>Payment</h5>
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
                @php $bal = ($sale->total_amount ?? 0) - ($sale->deposit_paid ?? 0); @endphp
                @if($bal > 0)
                <div class="mb-2">
                    <div class="info-label">Balance Due</div>
                    <div class="info-value text-danger fw-bold">₱{{ number_format($bal, 2) }}</div>
                </div>
                @endif

                @if($bal > 0)
                <div class="mt-3 mb-3">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#payBalanceModal">
                        <i class="fas fa-credit-card me-2"></i>Pay Balance
                    </button>
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
                    <img src="{{ $sale->payment_screenshot_path }}" alt="Payment Screenshot" class="img-fluid rounded mt-1 payment-img" style="max-height:200px;cursor:pointer;" onclick="openLightbox(this.src)">
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
                                @elseif($log->action === 'additional_payment')
                                    <span class="badge bg-info">Additional Payment</span>
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
<!-- Pay Balance Modal -->
<div class="modal fade" id="payBalanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('sales.prototype.agent.payment.store', $sale->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i>Pay Balance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @php $remaining = ($sale->total_amount ?? 0) - ($sale->deposit_paid ?? 0); @endphp
                @if(isset($relatedSales) && $relatedSales->count() > 0)
    <div class="alert alert-info mb-3">
        <div class="d-flex align-items-start">
            <i class="fas fa-layer-group me-3 fa-lg mt-1"></i>
            <div class="flex-grow-1">
                <strong>Multi-Department Transaction</strong><br>
                <small>This sale is part of a group with {{ $relatedSales->count() }} other department sale(s):
                @foreach($relatedSales as $rs)
                    <a href="{{ route('sales.prototype.show', $rs->id) }}" class="alert-link me-2">
                        <span class="badge bg-{{ $rs->department_code ?? 'secondary' }}">{{ $rs->department_name }} ({{ $rs->sales_number }})</span>
                    </a>
                @endforeach
                </small>
                <div class="mt-2 p-2 bg-white bg-opacity-25 rounded small">
                    <strong>Overall Transaction Total:</strong>
                    <span class="ms-2">₱{{ number_format($overallGroupTotal, 2) }}</span>
                    <span class="mx-2 text-muted">|</span>
                    <strong>Deposit:</strong>
                    <span class="ms-2">₱{{ number_format($overallGroupDeposit, 2) }}</span>
                    <span class="mx-2 text-muted">|</span>
                    <strong>Balance Due:</strong>
                    <span class="ms-2">₱{{ number_format($overallGroupBalance, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
@endif

    <div class="row g-3">
                        <!-- Summary -->
                        <div class="col-12">
                            <div class="bg-light p-3 rounded d-flex justify-content-around text-center">
                                <div>
                                    <small class="text-muted d-block">Total</small>
                                    <strong>₱{{ number_format($sale->total_amount ?? 0, 2) }}</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Paid</small>
                                    <strong class="text-success">₱{{ number_format($sale->deposit_paid ?? 0, 2) }}</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Balance</small>
                                    <strong class="text-danger">₱{{ number_format($remaining, 2) }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6">
                            <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                            <input type="number" name="payment_amount" class="form-control" step="0.01" min="0.01" max="{{ $remaining }}" value="{{ $remaining }}" required>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Method -->
                        <div class="col-md-6">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required onchange="toggleBalanceRefFields()">
                                <option value="">Select...</option>
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="check">Check</option>
                            </select>
                        </div>

                        <!-- Account -->
                        <div class="col-md-6">
                            <label class="form-label">Payment Account <span class="text-danger">*</span></label>
                            <select name="payment_account_id" class="form-select" required>
                                <option value="">Select account...</option>
                                @foreach(\App\Models\PaymentAccount::where('is_active', true)->get() as $acct)
                                    <option value="{{ $acct->id }}">{{ $acct->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Reference (shown for non-cash) -->
                        <div class="col-md-6" id="balanceRefGroup" style="display:none;">
                            <label class="form-label">Reference Number</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Transaction ref number">
                        </div>

                        <!-- Screenshot (shown for non-cash) -->
                        <div class="col-12" id="balanceScreenshotGroup" style="display:none;">
                            <label class="form-label">Payment Screenshot / Proof</label>
                            <input type="file" name="payment_screenshot" class="form-control" accept="image/*">
                        </div>

                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-1"></i>Submit Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Comment Section -->
<div class="detail-section mt-4">
    <h5 class="detail-title"><i class="fas fa-comments me-2"></i>Comments</h5>
    
    <div id="commentsContainer">
        <div class="text-center text-muted py-3" id="commentsLoading">
            <i class="fas fa-spinner fa-spin"></i> Loading comments...
        </div>
    </div>
    
    @if($isManager)
    <div class="mt-3">
        <form id="commentForm" method="POST" action="{{ route('sales.prototype.add-comment', $sale->id) }}">
            @csrf
            <div class="mb-2">
                <textarea name="comment" class="form-control" rows="2" placeholder="Add a comment... (visible to everyone)" required maxlength="1000"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-paper-plane"></i> Post Comment
            </button>
        </form>
    </div>
    @else
    <div class="alert alert-info mt-3 mb-0 py-2">
        <small><i class="fas fa-info-circle"></i> Only managers can add comments here.</small>
    </div>
    @endif
</div>

<!-- Audit History -->
<div class="detail-section mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h5 class="detail-title mb-0" style="border-bottom: none; padding-bottom: 0;">
            <i class="fas fa-history me-2"></i>Audit History
        </h5>
        <button class="btn btn-outline-info btn-sm" onclick="toggleAuditLog()">
            <i class="fas fa-chevron-down"></i> View History
        </button>
    </div>
    <div id="auditLogContainer" style="display: none;">
        <p class="text-muted text-center py-3">Loading...</p>
    </div>
</div>
</div>

@include('partials.sublimation-show-modal')

@endsection

@push('scripts')
<script>
    function toggleAuditLog() {
        var container = document.getElementById('auditLogContainer');
        if (container.style.display !== 'none') {
            container.style.display = 'none';
            return;
        }
        container.style.display = 'block';
        
        fetch('{{ route("sales.prototype.audit-history", $sale->id) }}')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var html = '';
                if (data.logs && data.logs.length > 0) {
                    data.logs.forEach(function(log) {
                        var date = new Date(log.created_at);
                        html += '<div class="d-flex gap-3 mb-3 pb-2 border-bottom">';
                        html += '<div class="text-center" style="min-width: 60px;">';
                        html += '<div class="small fw-bold">' + ('0' + date.getDate()).slice(-2) + '/' + ('0' + (date.getMonth()+1)).slice(-2) + '</div>';
                        html += '<div class="small text-muted">' + ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2) + '</div>';
                        html += '</div>';
                        html += '<div class="flex-grow-1">';
                        html += '<div><strong>' + (log.user_name || 'System') + '</strong> <span class="badge bg-secondary text-uppercase" style="font-size: 0.65rem;">' + log.action.replace(/_/g, ' ') + '</span></div>';
                        html += '<div class="text-muted small">' + log.description + '</div>';
                        html += '</div></div>';
                    });
                } else {
                    html = '<p class="text-muted text-center py-3">No audit history yet.</p>';
                }
                container.innerHTML = html;
            })
            .catch(function() {
                container.innerHTML = '<p class="text-danger text-center py-3">Failed to load history.</p>';
            });
    }
    
    window.toggleBalanceRefFields = function() {
        var method = document.querySelector('[name="payment_method"]').value;
        var show = method !== '' && method !== 'cash';
        document.getElementById('balanceRefGroup').style.display = show ? 'block' : 'none';
        document.getElementById('balanceScreenshotGroup').style.display = show ? 'block' : 'none';
    };

    function showToast(msg, type) {
        type = type || 'info';
        var existing = document.querySelector('.toast-notification-' + type);
        if (existing) existing.remove();
        
        var toast = document.createElement('div');
        toast.className = 'toast-notification-' + type;
        toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;padding:12px 20px;border-radius:8px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.15);max-width:400px;';
        
        var colors = { success: '#d4edda,#155724', danger: '#f8d7da,#721c24', warning: '#fff3cd,#856404', info: '#d1ecf1,#0c5460' };
        var c = colors[type] || colors.info;
        toast.style.background = c.split(',')[0];
        toast.style.color = c.split(',')[1];
        toast.innerHTML = msg;
        
        document.body.appendChild(toast);
        setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(function() { toast.remove(); }, 300); }, 4000);
    }
    
    // ---------- Comment Form Handler ----------
    document.addEventListener('DOMContentLoaded', function() {
        var commentForm = document.getElementById('commentForm');
        if (commentForm) {
            commentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var btn = this.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
                
                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        loadComments();
                        commentForm.querySelector('textarea').value = '';
                        showToast('Comment posted.', 'success');
                    } else {
                        showToast(data.message || 'Failed to post.', 'danger');
                    }
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Post Comment';
                })
                .catch(function() {
                    showToast('Error posting comment.', 'danger');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Post Comment';
                });
            });
        }
        
        loadComments();
        
        // Check for change_submitted success
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('change_submitted') === '1') {
            showToast('Change request submitted for manager approval.', 'success');
        }
    });
    
    // ---------- Load Comments ----------
    function loadComments() {
        fetch('{{ route("sales.prototype.audit-history", $sale->id) }}')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var html = '';
                if (data.logs && data.logs.length > 0) {
                    var commentLogs = data.logs.filter(function(l) { return l.action === 'comment_added'; });
                    if (commentLogs.length > 0) {
                        commentLogs.forEach(function(log) {
                            var date = new Date(log.created_at);
                            var dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
                            html += '<div class="d-flex gap-3 mb-3 pb-2 border-start border-primary ps-3">';
                            html += '<div class="flex-grow-1">';
                            html += '<div class="d-flex justify-content-between"><strong>' + (log.user_name || 'Manager') + '</strong> <small class="text-muted">' + dateStr + '</small></div>';
                            html += '<div class="mt-1">' + log.description.replace('Manager added a comment: ', '') + '</div>';
                            html += '</div></div>';
                        });
                    } else {
                        html = '<p class="text-muted text-center py-2">No comments yet.</p>';
                    }
                } else {
                    html = '<p class="text-muted text-center py-2">No comments yet.</p>';
                }
                document.getElementById('commentsContainer').innerHTML = html;
            })
            .catch(function() {
                document.getElementById('commentsContainer').innerHTML = '<p class="text-muted text-center py-2">Could not load comments.</p>';
            });
    }
    
    // ---------- Approve / Reject Change ----------
    function approveChange(changeId) {
        if (!confirm('Approve this change request?')) return;
        
        fetch('/sales/prototype/change/' + changeId + '/approve', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showToast('Change approved! Reloading...', 'success');
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                showToast(data.message || 'Failed to approve.', 'danger');
            }
        })
        .catch(function() { showToast('Error approving change.', 'danger'); });
    }
    
    function showRejectModal(changeId) {
        var reason = prompt('Enter reason for rejection:');
        if (!reason || reason.length < 5) {
            alert('Please enter a reason (at least 5 characters).');
            return;
        }
        
        fetch('/sales/prototype/change/' + changeId + '/reject', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showToast('Change rejected. Reloading...', 'warning');
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                showToast(data.message || 'Failed to reject.', 'danger');
            }
        })
        .catch(function() { showToast('Error rejecting change.', 'danger'); });
    }
    
    window.openLightbox = function(src) {
        var old = document.getElementById('imageLightbox');
        if (old) old.remove();
        
        var overlay = document.createElement('div');
        overlay.id = 'imageLightbox';
        overlay.style.cssText = 'display:flex!important;align-items:center;justify-content:center;position:fixed;top:0;left:0;width:100%;height:100%;z-index:100000;background:rgba(0,0,0,0.85);cursor:zoom-out;';
        
        var closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = 'position:absolute;top:15px;right:25px;font-size:32px;color:white;background:none;border:none;cursor:pointer;z-index:100001;';
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closeLightbox();
        });
        
        var imgContainer = document.createElement('div');
        imgContainer.style.cssText = 'display:flex;align-items:center;justify-content:center;height:100%;padding:40px;';
        
        var img = document.createElement('img');
        img.id = 'lightboxImage';
        img.style.cssText = 'max-width:100%;max-height:90vh;object-fit:contain;border-radius:8px;';
        img.alt = '';
        
        imgContainer.appendChild(img);
        overlay.appendChild(closeBtn);
        overlay.appendChild(imgContainer);
        
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeLightbox();
            }
        });
        
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
        img.src = src;
    };
    window.closeLightbox = function() {
        var overlay = document.getElementById('imageLightbox');
        if (overlay) {
            overlay.remove();
            document.body.style.overflow = '';
        }
    };
</script>
@endpush
