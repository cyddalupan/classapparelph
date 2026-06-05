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
    /* Add Product sizes modal */
    #addProductModal .size-input-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    #addProductModal .size-input-group label {
        min-width: 48px;
        font-weight: 600;
    }
    #addProductModal .size-input-group input[type="number"] {
        width: 80px;
        text-align: center;
    }
    #addProductModal .roster-row td {
        vertical-align: middle;
    }
    #addProductModal .roster-row select {
        width: 100px;
    }
    #addProductExcelDropZone {
        border: 2px dashed #ccc;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all .2s;
    }
    #addProductExcelDropZone:hover {
        border-color: #0d6efd;
        background: #f0f7ff;
    }
    #addProductExcelDropZone.has-file {
        border-color: #198754;
        background: #f0fff4;
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
            @if($canEdit)
            <button type="button" class="btn btn-success" onclick="openAddProductModal()">
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-scrollable">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title fw-bold">➕ Add Fullsublimation Product</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <!-- Sale Context (read-only) -->
    <div class="bg-light p-3 rounded mb-3">
        <div class="row g-2 small">
            <div class="col-md-4"><span class="text-muted">Order:</span> <strong>{{ $sale->sales_number }}</strong></div>
            <div class="col-md-4"><span class="text-muted">Customer:</span> <strong>{{ $sale->customer_name }}</strong></div>
            <div class="col-md-4"><span class="text-muted">Department:</span> <strong>{{ $sale->department_name ?? 'CLASS' }}</strong></div>
        </div>
    </div>

    <!-- Product Name -->
    <div class="mb-3">
        <label class="form-label fw-semibold">Product Name</label>
        <input type="text" class="form-control" id="ap_productName" placeholder="e.g. CLASS A JERSEY SHORT" required>
    </div>

    <!-- Mode toggle: By Size / Per Person -->
    <ul class="nav nav-tabs mb-3" id="apSizeTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="ap-by-size-tab" data-bs-toggle="tab" data-bs-target="#ap-by-size" type="button" role="tab">By Size</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="ap-per-person-tab" data-bs-toggle="tab" data-bs-target="#ap-per-person" type="button" role="tab">Per Person (Roster)</button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- By Size -->
        <div class="tab-pane fade show active" id="ap-by-size" role="tabpanel">
            <label class="form-label fw-semibold mb-2">Quantities by Size</label>
            <div class="row g-2" id="apSizeGrid">
                @php $apSizes = ['XS','S','M','L','XL','2XL','3XL','4XL','5XL','6XL','7XL','8XL']; @endphp
                @foreach($apSizes as $sz)
                <div class="col-4 col-md-3 col-lg-2">
                    <div class="size-input-group">
                        <label>{{ $sz }}</label>
                        <input type="number" class="form-control form-control-sm ap-size-qty" data-size="{{ $sz }}" min="0" value="0" onchange="ap_calcTotal()" oninput="ap_calcTotal()">
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Per Person (Roster) -->
        <div class="tab-pane fade" id="ap-per-person" role="tabpanel">
            <div class="mb-3">
                <label class="form-label fw-semibold">Person Names &amp; Sizes</label>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="apRosterTable">
                        <thead><tr><th>#</th><th>Person Name</th><th>Size</th><th></th></tr></thead>
                        <tbody id="apRosterBody">
                            <tr class="roster-row">
                                <td class="text-center">1</td>
                                <td><input type="text" class="form-control form-control-sm" name="person_name[]" placeholder="Name"></td>
                                <td>
                                    <select class="form-select form-select-sm ap-roster-size">
                                        @foreach($apSizes as $sz)
                                        <option value="{{ $sz }}">{{ $sz }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); ap_calcTotal();">✕</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="ap_addRosterRow()">+ Add Person</button>
                </div>
            </div>

            <!-- Excel Upload -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Upload Excel (optional)</label>
                <div id="addProductExcelDropZone">
                    <i class="fas fa-file-excel fa-2x text-success mb-2"></i>
                    <p class="mb-1 small">Drag &amp; drop or click to upload an Excel file<br><span class="text-muted">Columns: Person Name, Size</span></p>
                    <input type="file" id="apExcelFile" accept=".xlsx,.xls" style="display:none" onchange="ap_handleExcel(event)">
                </div>
            </div>
        </div>
    </div>

    <!-- Unit Price & Total -->
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Unit Price (₱)</label>
            <input type="number" class="form-control" id="ap_unitPrice" min="0" step="0.01" value="0" onchange="ap_calcTotal()" oninput="ap_calcTotal()">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div>
                <div class="text-muted small">Total Qty</div>
                <div class="fs-4 fw-bold" id="ap_totalQty">0</div>
            </div>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div>
                <div class="text-muted small">Total Price</div>
                <div class="fs-4 fw-bold text-success" id="ap_totalPrice">₱0.00</div>
            </div>
        </div>
    </div>

    <div id="ap_errorMsg" class="text-danger small mt-2" style="display:none;"></div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-success" id="ap_submitBtn" onclick="ap_submit()">
        <i class="fas fa-check"></i> Add to Order
    </button>
</div>
</div>
</div>
</div>

<script>
// Add Product Modal - Sizes by default
var apSizes = ['XS','S','M','L','XL','2XL','3XL','4XL','5XL','6XL','7XL','8XL'];

function openAddProductModal() {
    // Reset
    document.getElementById('ap_productName').value = '';
    document.querySelectorAll('.ap-size-qty').forEach(function(el) { el.value = '0'; });
    document.getElementById('ap_unitPrice').value = '0';
    document.getElementById('ap_totalQty').textContent = '0';
    document.getElementById('ap_totalPrice').textContent = '₱0.00';
    document.getElementById('ap_errorMsg').style.display = 'none';
    document.getElementById('ap_submitBtn').disabled = false;
    document.getElementById('ap_submitBtn').innerHTML = '<i class="fas fa-check"></i> Add to Order';
    
    // Reset roster
    var body = document.getElementById('apRosterBody');
    body.innerHTML = '<tr class="roster-row"><td class="text-center">1</td><td><input type="text" class="form-control form-control-sm" name="person_name[]" placeholder="Name"></td><td><select class="form-select form-select-sm ap-roster-size">' + apSizes.map(function(s) { return '<option value="'+s+'">'+s+'</option>'; }).join('') + '</select></td><td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove(); ap_calcTotal();">✕</button></td></tr>';
    
    document.getElementById('apExcelFile').value = '';
    document.getElementById('addProductExcelDropZone').classList.remove('has-file');
    
    // Reset tabs to By Size
    document.querySelector('#ap-by-size-tab').click();
    
    var modal = new bootstrap.Modal(document.getElementById('addProductModal'));
    modal.show();
}

function ap_calcTotal() {
    // By Size qty
    var totalQty = 0;
    document.querySelectorAll('.ap-size-qty').forEach(function(el) {
        totalQty += parseInt(el.value) || 0;
    });
    
    // Roster qty
    document.querySelectorAll('#apRosterBody tr.roster-row').forEach(function(row) {
        var nameInput = row.querySelector('input[name=\'person_name[]\']');
        if (nameInput && nameInput.value.trim()) totalQty++;
    });
    
    var unitPrice = parseFloat(document.getElementById('ap_unitPrice').value) || 0;
    var totalPrice = totalQty * unitPrice;
    
    document.getElementById('ap_totalQty').textContent = totalQty;
    document.getElementById('ap_totalPrice').textContent = '₱' + totalPrice.toFixed(2);
}

function ap_addRosterRow() {
    var body = document.getElementById('apRosterBody');
    var idx = body.querySelectorAll('tr.roster-row').length + 1;
    var tr = document.createElement('tr');
    tr.className = 'roster-row';
    tr.innerHTML = '<td class="text-center">'+idx+'</td>'
        + '<td><input type="text" class="form-control form-control-sm" name="person_name[]" placeholder="Name" oninput="ap_calcTotal()"></td>'
        + '<td><select class="form-select form-select-sm ap-roster-size">' + apSizes.map(function(s) { return '<option value="'+s+'">'+s+'</option>'; }).join('') + '</select></td>'
        + '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove(); ap_calcTotal();">✕</button></td>';
    body.appendChild(tr);
    ap_calcTotal();
}

function ap_handleExcel(event) {
    var file = event.target.files[0];
    if (!file) return;
    
    document.getElementById('addProductExcelDropZone').classList.add('has-file');
    
    var reader = new FileReader();
    reader.onload = function(e) {
        try {
            var data = new Uint8Array(e.target.result);
            var workbook = XLSX.read(data, {type: 'array'});
            var sheet = workbook.Sheets[workbook.SheetNames[0]];
            var json = XLSX.utils.sheet_to_json(sheet, {header: 1});
            
            if (json.length < 2) {
                alert('Excel file has no data rows (header + at least 1 row).');
                return;
            }
            
            var body = document.getElementById('apRosterBody');
            body.innerHTML = '';
            var count = 0;
            for (var i = 1; i < json.length; i++) {
                var row = json[i];
                if (!row[0] || !row[0].toString().trim()) continue;
                count++;
                var name = row[0].toString().trim();
                var size = row[1] ? row[1].toString().trim() : 'M';
                
                var tr = document.createElement('tr');
                tr.className = 'roster-row';
                tr.innerHTML = '<td class="text-center">'+count+'</td>'
                    + '<td><input type="text" class="form-control form-control-sm" name="person_name[]" value="'+name.replace(/'/g,'&#39;')+'" oninput="ap_calcTotal()"></td>'
                    + '<td><select class="form-select form-select-sm ap-roster-size">' + apSizes.map(function(s) { return '<option value="'+s+'"'+(s===size?' selected':'')+'>'+s+'</option>'; }).join('') + '</select></td>'
                    + '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove(); ap_calcTotal();">✕</button></td>';
                body.appendChild(tr);
            }
            ap_calcTotal();
        } catch(err) {
            alert('Error reading Excel: ' + err.message);
        }
    };
    reader.readAsArrayBuffer(file);
}

// Click drop zone to open file picker
document.addEventListener('DOMContentLoaded', function() {
    var dropZone = document.getElementById('addProductExcelDropZone');
    if (dropZone) {
        dropZone.addEventListener('click', function() {
            document.getElementById('apExcelFile').click();
        });
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropZone.style.borderColor = '#0d6efd';
        });
        dropZone.addEventListener('dragleave', function() {
            dropZone.style.borderColor = '#ccc';
        });
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.style.borderColor = '#ccc';
            var files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('apExcelFile').files = files;
                ap_handleExcel({target: {files: files}});
            }
        });
    }
});

function ap_submit() {
    var productName = document.getElementById('ap_productName').value.trim();
    if (!productName) {
        document.getElementById('ap_errorMsg').textContent = 'Please enter a product name.';
        document.getElementById('ap_errorMsg').style.display = 'block';
        return;
    }
    
    // Collect sizes from By Size tab
    var sizes = [];
    document.querySelectorAll('.ap-size-qty').forEach(function(el) {
        var qty = parseInt(el.value) || 0;
        if (qty > 0) {
            sizes.push({size: el.dataset.size, qty: qty});
        }
    });
    
    // Collect from roster (Per Person mode)
    document.querySelectorAll('#apRosterBody tr.roster-row').forEach(function(row) {
        var nameInput = row.querySelector('input[name^=\'person_name\']');
        var sizeSelect = row.querySelector('.ap-roster-size');
        if (nameInput && nameInput.value.trim() && sizeSelect) {
            sizes.push({size: sizeSelect.value, qty: 1});
        }
    });
    
    if (sizes.length === 0) {
        document.getElementById('ap_errorMsg').textContent = 'Please enter at least one item quantity or a person name.';
        document.getElementById('ap_errorMsg').style.display = 'block';
        return;
    }
    
    var unitPrice = parseFloat(document.getElementById('ap_unitPrice').value) || 0;
    if (unitPrice <= 0) {
        document.getElementById('ap_errorMsg').textContent = 'Please enter a unit price.';
        document.getElementById('ap_errorMsg').style.display = 'block';
        return;
    }
    
    var btn = document.getElementById('ap_submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    
    fetch('{{ route("sales.prototype.add-product", $sale->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            product_name: productName,
            sizes: sizes,
            unit_price: unitPrice
        })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            var modal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
            modal.hide();
            showToast('Product added! Reloading...', 'success');
            setTimeout(function() { location.reload(); }, 1000);
        } else {
            document.getElementById('ap_errorMsg').textContent = data.message || 'Failed to add product.';
            document.getElementById('ap_errorMsg').style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Add to Order';
        }
    })
    .catch(function(err) {
        document.getElementById('ap_errorMsg').textContent = 'Error: ' + err.message;
        document.getElementById('ap_errorMsg').style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Add to Order';
    });
}
</script>

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
