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
                @if(($sale->date_needed ?? false))
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

@endsection

@push('scripts')
<script>
    window.toggleBalanceRefFields = function() {
        var method = document.querySelector('[name="payment_method"]').value;
        var show = method !== '' && method !== 'cash';
        document.getElementById('balanceRefGroup').style.display = show ? 'block' : 'none';
        document.getElementById('balanceScreenshotGroup').style.display = show ? 'block' : 'none';
    };

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
