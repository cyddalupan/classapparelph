@extends('layouts.app')

@section('title', 'Create New Sale - Prototype')

@push('styles')
<style>
    .form-section {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .section-title {
        color: #667eea;
        border-bottom: 2px solid #667eea;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }
    
    .service-item {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        background: #f9f9f9;
    }
    
    .upload-area {
        border: 2px dashed #667eea;
        border-radius: 10px;
        padding: 3rem;
        text-align: center;
        background: #f8f9ff;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .upload-area:hover {
        background: #eef1ff;
        border-color: #764ba2;
    }
    
    .upload-area.has-screenshot {
        border-style: solid;
        border-color: #28a745;
        padding: 1rem;
    }
    
    .upload-area.missing-screenshot {
        border-color: #dc3545;
        animation: shake 0.5s;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .upload-icon {
        font-size: 3rem;
        color: #667eea;
        margin-bottom: 1rem;
    }
    
    .payment-method-card {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .payment-method-card:hover {
        border-color: #667eea;
        background: #f8f9ff;
    }
    
    .payment-method-card.selected {
        border-color: #667eea;
        background: #eef1ff;
    }
    
    .department-card {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .department-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .department-card.selected {
        border-color: #667eea;
        background: #eef1ff;
    }
    
    /* Product Box Styles */
    .product-box {
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .product-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border-color: #667eea;
    }
    
    .product-icon {
        color: #667eea;
    }
    
    .department-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .badge-iprint {
        background-color: rgba(102, 126, 234, 0.1);
        color: #667eea;
        border: 1px solid rgba(102, 126, 234, 0.3);
    }
    
    .badge-consol {
        background-color: rgba(23, 162, 184, 0.1);
        color: #17a2b8;
        border: 1px solid rgba(23, 162, 184, 0.3);
    }
    
    .badge-cinco {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }
    
    .badge-class {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }
    
    .badge-mto {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }
    
    .badge-other {
        background-color: rgba(108, 117, 125, 0.1);
        color: #6c757d;
        border: 1px solid rgba(108, 117, 125, 0.3);
    }
    
    /* Selected Items Styles */
    .selected-item-card {
        border-left: 4px solid #667eea;
        border-radius: 8px;
        margin-bottom: 1rem;
        background: white;
    }
    
    .item-department-badge {
        font-size: 0.75rem;
        padding: 2px 8px;
    }
    
    /* Modal Styles */
    .product-modal .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .product-modal .modal-title i {
        margin-right: 10px;
    }
    
    /* Garment modal styles */
    #garmentModalBody .print-size-checkbox.selected {
        background-color: #e7f3ff;
        border-color: #0d6efd !important;
    }
    #garmentModalBody .product-row .product-select {
        font-size: 0.85rem;
    }
    #garmentModalBody .card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
    #garmentModalBody .card-title {
        font-size: 0.95rem;
        font-weight: 600;
    }
    #referenceDropZone:hover {
        border-color: #0d6efd !important;
        background-color: #f0f7ff;
    }
    #sublimationModalBody .card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
    #sublimationModalBody .card-title {
        font-size: 0.95rem;
        font-weight: 600;
    }
    #sublimationModalBody .form-text {
        font-size: 0.8rem;
    }
    #sublimation_rosterTable th {
        position: sticky;
        top: 0;
        z-index: 1;
    }
    #sublimation_rosterBody tr.roster-row td {
        vertical-align: middle;
    }
    #sublimation_rosterBody tr.roster-row:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Create New Sale (Prototype)</h1>
            <p class="text-muted">Test the new sales system with multi-department workflow</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('sales.prototype') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Prototype
            </a>
        </div>
    </div>
    
    <form id="prototypeSaleForm" action="{{ route('sales.prototype.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        
        <!-- Step 1: Customer Information -->
        <div class="form-section">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <h3 class="section-title">Step 1: Customer Information</h3>
            
            <!-- Smart Customer Detection -->
            <div class="alert alert-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-lightbulb me-3 fa-2x"></i>
                    <div>
                        <h5 class="mb-1">Smart Customer Detection</h5>
                        <p class="mb-0">Enter phone number to check if customer exists. System will auto-fill if found.</p>
                    </div>
                </div>
            </div>
            
            <!-- Customer Search -->
            <div class="mb-4">
                <label class="form-label">Find Existing Customer</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="customerSearch" placeholder="Search by phone, name, or email...">
                    <button class="btn btn-outline-secondary" type="button" id="searchCustomerBtn">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
                <div id="customerSearchResults" class="mt-2" style="display: none;">
                    <!-- Results will appear here -->
                </div>
            </div>
            
            <div class="alert alert-success mb-4" id="existingCustomerAlert" style="display: none;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-check-circle me-2"></i>
                        <strong id="existingCustomerName"></strong> - Existing customer found!
                        <div class="small mt-1" id="existingCustomerDetails"></div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-success" id="useExistingCustomer">
                            <i class="fas fa-user-check me-1"></i> Use This Customer
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="createNewCustomer">
                            <i class="fas fa-user-plus me-1"></i> Create New
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Customer Form Fields -->
            <div id="customerFormFields">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="customer_name" class="form-label">Customer Name *</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="customer_phone" class="form-label">Phone Number *</label>
                        <div class="input-group">
                            <span class="input-group-text">+63</span>
                            <input type="tel" class="form-control" id="customer_phone" name="customer_phone" 
                                   placeholder="9123456789" required
                                   data-customer-detection="true">
                        </div>
                        <small class="text-muted">Enter phone number to check for existing customer</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="customer_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="customer_email" name="customer_email">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="marketplace" class="form-label">Marketplace / Source *</label>
                        <select class="form-control" id="marketplace" name="marketplace" required>
                            <option value="">Select source</option>
                            @foreach($marketplaceOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="customer_address" class="form-label">Location / Address</label>
                        <textarea class="form-control" id="customer_address" name="customer_address" rows="2" placeholder="City, Province, or Full Address"></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="customer_company" class="form-label">Company (Optional)</label>
                        <input type="text" class="form-control" id="customer_company" name="customer_company" placeholder="Company name if applicable">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="customer_notes" class="form-label">Customer Notes</label>
                    <textarea class="form-control" id="customer_notes" name="customer_notes" rows="3" placeholder="Any special instructions or preferences from the customer..."></textarea>
                </div>
                

            </div>
            
            <!-- Customer LTV Display (when existing customer selected) -->
            <div class="card mt-3" id="customerLTVCard" style="display: none;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Customer Lifetime Value</h5>
                </div>
                <div class="card-body">
                    <div class="row" id="customerLTVDetails">
                        <!-- LTV details will be populated here -->
                    </div>
                </div>
            </div>
            
            <!-- Hidden customer_id field -->
            <input type="hidden" id="customer_id" name="customer_id" value="">
        </div>
        
        <!-- Step 2: Product Selection (Select Department in Modal) -->
        <div class="form-section">
            <h3 class="section-title">Step 2: Select Products & Services</h3>
            <p class="text-muted mb-4">Choose products from our database. Select department for each item in the modal.</p>
            
            <!-- Smart Department Assignment Alert -->
            <div class="alert alert-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-robot me-3 fa-2x"></i>
                    <div>
                        <h5 class="mb-1">Smart Department Assignment</h5>
                        <p class="mb-0">Select department for each item in the modal. Managers will be notified automatically.</p>
                    </div>
                </div>
            </div>
            
            <!-- 6 Product Boxes -->
            <div class="row g-4" id="productBoxesContainer">
                <!-- Box 1: Garment Printing -->
                <div class="col-md-6 col-lg-4">
                    <div class="product-box card h-100" data-product-type="garment" data-department="iprint">
                        <div class="card-body text-center">
                            <div class="product-icon mb-3">
                                <i class="fas fa-tshirt fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title mb-2">Garment Printing</h5>
                            <p class="text-muted small mb-3">DTF, Sublimation, Heat Transfer</p>
                            <button type="button" class="btn btn-outline-primary w-100" onclick="openProductModal('garment')">
                                <i class="fas fa-plus-circle me-2"></i> Add Items
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Box 2: Tarpaulin -->
                <div class="col-md-6 col-lg-4">
                    <div class="product-box card h-100" data-product-type="tarpaulin" data-department="consol">
                        <div class="card-body text-center">
                            <div class="product-icon mb-3">
                                <i class="fas fa-image fa-3x text-info"></i>
                            </div>
                            <h5 class="card-title mb-2">Tarpaulin Printing</h5>
                            <p class="text-muted small mb-3">Banners, Signages, Tarps</p>
                            <button type="button" class="btn btn-outline-info w-100" onclick="openProductModal('tarpaulin')">
                                <i class="fas fa-plus-circle me-2"></i> Add Items
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Box 3: Embroidery -->
                <div class="col-md-6 col-lg-4">
                    <div class="product-box card h-100" data-product-type="embroidery" data-department="cinco">
                        <div class="card-body text-center">
                            <div class="product-icon mb-3">
                                <i class="fas fa-thread fa-3x text-warning"></i>
                            </div>
                            <h5 class="card-title mb-2">Embroidery</h5>
                            <p class="text-muted small mb-3">Custom embroidery services</p>
                            <button type="button" class="btn btn-outline-warning w-100" onclick="openProductModal('embroidery')">
                                <i class="fas fa-plus-circle me-2"></i> Add Items
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Box 4: Cutting -->
                <div class="col-md-6 col-lg-4">
                    <div class="product-box card h-100" data-product-type="cutting" data-department="class">
                        <div class="card-body text-center">
                            <div class="product-icon mb-3">
                                <i class="fas fa-cut fa-3x text-success"></i>
                            </div>
                            <h5 class="card-title mb-2">Fullsublimation Printing</h5>
                            <p class="text-muted small mb-3">Full sublimation printing</p>
                            <button type="button" class="btn btn-outline-success w-100" onclick="openProductModal('cutting')">
                                <i class="fas fa-plus-circle me-2"></i> Add Items
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Box 5: Sewing -->
                <div class="col-md-6 col-lg-4">
                    <div class="product-box card h-100" data-product-type="sewing" data-department="class">
                        <div class="card-body text-center">
                            <div class="product-icon mb-3">
                                <i class="fas fa-sewing-machine fa-3x text-success"></i>
                            </div>
                            <h5 class="card-title mb-2">Sewing Services</h5>
                            <p class="text-muted small mb-3">Garment assembly, stitching</p>
                            <button type="button" class="btn btn-outline-success w-100" onclick="openProductModal('sewing')">
                                <i class="fas fa-plus-circle me-2"></i> Add Items
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Box 6: Design -->
                <div class="col-md-6 col-lg-4">
                    <div class="product-box card h-100" data-product-type="design" data-department="iprint">
                        <div class="card-body text-center">
                            <div class="product-icon mb-3">
                                <i class="fas fa-pencil-ruler fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title mb-2">Design Services</h5>
                            <p class="text-muted small mb-3">Graphic design, mockups</p>
                            <button type="button" class="btn btn-outline-primary w-100" onclick="openProductModal('design')">
                                <i class="fas fa-plus-circle me-2"></i> Add Items
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Selected Items Display -->
            <div class="mt-5">
                <h5 class="mb-3">Selected Items</h5>
                <div id="selectedItemsContainer" class="mb-4">
                    <div class="alert alert-light text-center py-4">
                        <i class="fas fa-shopping-cart fa-2x text-muted mb-3"></i>
                        <p class="mb-0">No items added yet. Click on a product box above to add items.</p>
                    </div>
                </div>
                
                <!-- Hidden fields for form submission (kept for backend) -->
            </div>
            
            <!-- Hidden fields for form submission -->
            <input type="hidden" name="auto_department_id" id="auto_department_id" value="">
            <input type="hidden" name="items_json" id="items_json" value="">
            <input type="hidden" name="subtotal" id="subtotal" value="0">
            <input type="hidden" name="tax" id="tax" value="0">
            <input type="hidden" name="total_amount" id="total_amount" value="0">
        </div>
        

        
        <!-- Step 4: Payment & Verification -->
        <div class="form-section">
            <h3 class="section-title">Step 3: Payment & Verification</h3>
            
            <!-- Payment Summary -->
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Payment Summary</h5>
                </div>
                <div class="card-body" id="paymentSummaryContainer">
                    <div id="paymentItemsSummary">
                        <p class="text-muted mb-0">Add items to cart to see payment summary</p>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6 text-muted">Total Items:</div>
                        <div class="col-6 text-end" id="paymentTotalItems">0</div>
                    </div>
                    <div class="row">
                        <div class="col-6 text-muted">Total Quantity:</div>
                        <div class="col-6 text-end" id="paymentTotalQty">0</div>
                    </div>
                    <div class="row fw-bold fs-5 mt-2">
                        <div class="col-6">Grand Total:</div>
                        <div class="col-6 text-end text-primary" id="paymentGrandTotal">₱0.00</div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-12" id="paymentDetailsSection">
                    <!-- Payment Type & Amount -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Payment Type *</strong></label>
                        <div class="d-flex gap-2" id="paymentTypeGroup">
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="payment_type" id="pt_down" value="downpayment" autocomplete="off">
                                <label class="btn btn-outline-warning w-100 py-2" for="pt_down">
                                    <i class="fas fa-hand-holding-usd d-block mb-1"></i>
                                    <span>Down Payment</span>
                                    <small class="d-block text-muted" id="pt_down_suggest">Suggested: ₱0.00 (50%)</small>
                                </label>
                            </div>
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="payment_type" id="pt_full" value="fullpayment" autocomplete="off">
                                <label class="btn btn-outline-success w-100 py-2" for="pt_full">
                                    <i class="fas fa-check-circle d-block mb-1"></i>
                                    <span>Full Payment</span>
                                    <small class="d-block text-muted" id="pt_full_suggest">₱0.00</small>
                                </label>
                            </div>
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="payment_type" id="pt_po" value="po" autocomplete="off">
                                <label class="btn btn-outline-secondary w-100 py-2" for="pt_po">
                                    <i class="fas fa-file-invoice d-block mb-1"></i>
                                    <span>P.O.</span>
                                    <small class="d-block text-muted">No payment now</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div id="amountInputSection" class="mb-3">
                        <label for="payment_amount" class="form-label">Payment Amount *</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="payment_amount" name="payment_amount" step="0.01" min="0" value="0" disabled>
                        </div>
                        <small class="text-muted" id="paymentAmountHint"></small>
                    </div>
                    
                    <div class="mb-3" id="poSection" style="display:none;">
                        <label for="po_reference" class="form-label">P.O. Reference # *</label>
                        <input type="text" class="form-control" id="po_reference" name="po_reference" placeholder="Enter P.O. number">
                    </div>

                    <!-- Hidden payment_method auto-detected from Payment Received By selection -->
                    <input type="hidden" name="payment_method" id="payment_method_input" value="cash">
                    
                    <div class="mb-3" id="paymentOwnerSection">
                        <label for="payment_account_id" class="form-label">Payment Received By *</label>
                        <select class="form-control" id="payment_account_id" name="payment_account_id">
                            <option value="">Select account...</option>
                            @foreach($paymentAccounts as $account)
                                <option value="{{ $account->id }}" data-user="{{ $account->user_id }}">
                                    {{ $account->name }} @if($account->user)
                                        ({{ $account->user->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Who will receive/verify this payment?</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_date" class="form-label">Payment Date</label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}">
                            <small class="text-muted">When was this paid?</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="reference_number" class="form-label">Reference Number</label>
                            <input type="text" class="form-control" id="reference_number" name="reference_number" placeholder="GCash ref / Bank ref / OR #">
                            <small class="text-muted">For reconciliation</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Payment Screenshot <span class="text-danger">*</span></label>
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h5>Drag & drop payment screenshot here</h5>
                    <p class="text-muted">or click to browse files, or <kbd>Ctrl+V</kbd> to paste</p>
                    <input type="file" id="payment_screenshot" name="payment_screenshot" accept="image/*" required class="d-none">
                    <div id="fileName" class="mt-2 text-success"></div>
                    <div id="paymentPreview" class="mt-2" style="display:none;">
                        <img id="paymentPreviewImg" src="" alt="Payment Screenshot Preview" class="img-thumbnail" style="max-height:200px;width:auto;">
                        <button type="button" class="btn btn-sm btn-outline-danger mt-1" id="removePaymentScreenshot">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
            

            
            <!-- Hidden fields -->
            <input type="hidden" name="payment_type" id="payment_type_hidden" value="">
            <input type="hidden" name="deposit_paid" id="deposit_paid_hidden" value="0">
            <input type="hidden" name="payment_owner" id="payment_owner_hidden" value="">
        </div>
        
        <!-- Submit Button -->
        <div class="form-section text-center">
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-save me-2"></i> Create Sale & Send to KANBAN
            </button>
            <p class="text-muted mt-3">This sale will be created and automatically added to the appropriate department's KANBAN board.</p>
        </div>
        <!-- Product Selection Modal -->
        <div class="modal fade product-modal" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">
                            <i class="fas fa-box"></i> Select Product
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Generic (non-garment) modal body -->
                        <div id="genericModalBody">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- FILTER SECTION -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0"><i class="fas fa-filter me-1"></i> Filter Products</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <label class="form-label small mb-1">Brand</label>
                                                <input type="text" class="form-control form-control-sm" id="filterBrand" list="brandOptions" placeholder="Type or select brand...">
                                                <datalist id="brandOptions">
                                                    <!-- Brand options will be loaded dynamically -->
                                                </datalist>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small mb-1">Type</label>
                                                <input type="text" class="form-control form-control-sm" id="filterType" list="typeOptions" placeholder="Type or select type...">
                                                <datalist id="typeOptions">
                                                    <!-- Type options will be loaded dynamically -->
                                                </datalist>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small mb-1">Color</label>
                                                <input type="text" class="form-control form-control-sm" id="filterColor" list="colorOptions" placeholder="Type or select color...">
                                                <datalist id="colorOptions">
                                                    <!-- Color options will be loaded dynamically -->
                                                </datalist>
                                            </div>
                                        </div>
                                        <div class="mt-2 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="applyFilters()">
                                                <i class="fas fa-filter me-1"></i> Apply Filters
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                                                <i class="fas fa-redo me-1"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- END FILTER SECTION -->
                                
                                <!-- MULTIPLE PRODUCT ROWS SECTION -->
                                <div class="mb-3">
                                    <label class="form-label">Products *</label>
                                    <div id="productRowsContainer">
                                        <!-- Product Row Template (Hidden) -->
                                        <div class="product-row-template d-none">
                                            <div class="row g-2 mb-2 align-items-center">
                                                <div class="col-md-6">
                                                    <select class="form-control product-select" name="product_id[]">
                                                        <option value="">Select a product</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="number" class="form-control product-quantity" name="quantity[]" min="1" value="1" placeholder="Qty">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Remove this product">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- First Product Row -->
                                        <div class="product-row">
                                            <div class="row g-2 mb-2 align-items-center">
                                                <div class="col-md-6">
                                                    <select class="form-control product-select" required>
                                                        <option value="">Select a product</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="number" class="form-control product-quantity" name="quantity[]" min="1" value="1" placeholder="Qty" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Remove this product">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-success" id="addProductRow">
                                            <i class="fas fa-plus-circle me-1"></i> Add Another Product
                                        </button>
                                    </div>
                                    <div class="form-text">Add multiple products in one go. Products loaded from database based on category.</div>
                                </div>
                                
                                <!-- Department auto-assigned from product box selection (no manual pick) -->
                                
                                <!-- Unit Price Display (Read-only) -->
                                <div class="mb-3">
                                    <label class="form-label">Unit Price (₱)</label>
                                    <input type="text" class="form-control" id="productPriceDisplay" readonly value="₱0.00">
                                    <div class="form-text">Price auto-filled when product is selected. Same price applies to all quantities.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Notes (Optional)</label>
                                    <textarea class="form-control" id="productNotes" rows="3" placeholder="Add special instructions, colors, sizes, etc."></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">Order Summary</h6>
                                        
                                        <!-- Products Breakdown -->
                                        <div id="productsBreakdown" class="mb-3">
                                            <div class="text-muted small mb-2">No products selected yet</div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Quantity:</span>
                                            <span id="totalQuantityDisplay">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Unit Price:</span>
                                            <span id="priceDisplay">₱0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Discount:</span>
                                            <span id="discountDisplay" class="text-muted small">No bulk discount applied</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>Total Amount:</span>
                                            <span id="itemTotalDisplay">₱0.00</span>
                                        </div>
                                        
                                        <button type="button" class="btn btn-primary w-100 mt-4" id="addItemBtn">
                                            <i class="fas fa-cart-plus me-2"></i> Add to Cart
                                        </button>
                                        
                                        <div class="alert alert-info mt-3 small">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Select the department that will handle this item. Managers will be notified automatically.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        
                        <!-- Garment-specific modal body (hidden by default) -->
                        <div id="garmentModalBody" style="display:none;">
                            <div class="row">
                                <!-- LEFT COLUMN: Product Selection -->
                                <div class="col-md-5">
                                    <!-- MULTIPLE PRODUCT ROWS SECTION -->
                                    <!-- GARMENT FILTER SECTION -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-light py-2">
                                            <h6 class="mb-0"><i class="fas fa-filter me-1"></i> Filter Products</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-1">Brand</label>
                                                    <input type="text" class="form-control form-control-sm" id="garment_filterBrand" list="garment_brandOptions" placeholder="Type or select brand...">
                                                    <datalist id="garment_brandOptions"></datalist>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-1">Size</label>
                                                    <input type="text" class="form-control form-control-sm" id="garment_filterType" list="garment_typeOptions" placeholder="Type or select size...">
                                                    <datalist id="garment_typeOptions"></datalist>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-1">Color</label>
                                                    <input type="text" class="form-control form-control-sm" id="garment_filterColor" list="garment_colorOptions" placeholder="Type or select color...">
                                                    <datalist id="garment_colorOptions"></datalist>
                                                </div>
                                            </div>
                                            <div class="mt-2 text-end">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="garment_applyFilters()">
                                                    <i class="fas fa-filter me-1"></i> Apply Filters
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="garment_resetFilters()">
                                                    <i class="fas fa-redo me-1"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Products *</label>
                                        <div id="garment_productRowsContainer">
                                            <!-- Product Row Template (Hidden) -->
                                            <div class="product-row-template d-none">
                                                <div class="row g-2 mb-2 align-items-center">
                                                    <div class="col-md-6">
                                                        <select class="form-control product-select" required>
                                                            <option value="">Select a product</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="number" class="form-control product-quantity" min="1" value="1" placeholder="Qty" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Remove this product">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- First Product Row -->
                                            <div class="product-row">
                                                <div class="row g-2 mb-2 align-items-center">
                                                    <div class="col-md-6">
                                                        <select class="form-control product-select" required>
                                                            <option value="">Select a product</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="number" class="form-control product-quantity" min="1" value="1" placeholder="Qty" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Remove this product">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-success" id="garment_addProductRowBtn">
                                                <i class="fas fa-plus-circle me-1"></i> Add Another Product
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Department auto-assigned from product box selection (no manual pick) -->
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Date Needed <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="garment_dateNeeded" required>
                                        <small class="text-muted">Target completion / delivery date</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Notes (Optional)</label>
                                        <textarea class="form-control" id="garment_productNotes" rows="3" placeholder="Add special instructions..."></textarea>
                                    </div>
                                    
                                    <!-- Reference Images Section -->
                                    <div class="mb-3">
                                        <label class="form-label">Reference Images (Optional)</label>
                                        <p class="small text-muted mb-2">Upload design reference images for the printer.</p>
                                        <div id="referenceDropZone" style="border: 2px dashed #ccc; border-radius: 8px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s;"
                                             onclick="garment_triggerReferenceFilePicker()"
                                             ondrop="garment_onReferenceDrop(event)"
                                             ondragover="garment_onReferenceDragOver(event)"
                                             ondragleave="garment_onReferenceDragLeave(event)"
                                             onpaste="garment_onReferencePaste(event)">
                                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                            <p class="mb-1 small">Drag & drop images here, click to browse, or paste (Ctrl+V)</p>
                                            <p class="mb-0 small text-muted">Supports: JPG, PNG, GIF</p>
                                        </div>
                                        <input type="file" id="referenceFilePicker" accept="image/*" multiple style="display:none" onchange="garment_onReferenceFilePickerChange(event)">
                                        <div id="referencePreviewsGallery" class="d-flex flex-wrap gap-2 mt-2"></div>
                                    </div>
                                </div>
                                
                                <!-- MIDDLE COLUMN: Printing Options -->
                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="fas fa-print me-2"></i>Printing Options</h6>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Print Type *</label>
                                                <select class="form-control" id="garment_printTypeSelect" onchange="garment_loadPrintSizes(this.value)">
                                                    <option value="">-- Select Print Type --</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Print Sizes</label>
                                                <div id="garment_printSizesContainer" class="small" style="display:none;">
                                                    <div id="garment_printSizesList"></div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3" id="garment_printQuantitySection" style="display:none;">
                                                <label class="form-label">Print Quantity</label>
                                                <input type="number" class="form-control" id="garment_printQuantityInput" min="1" value="1" onchange="garment_updatePrintSummary()">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- RIGHT COLUMN: Order Summary -->
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="fas fa-shopping-cart me-2"></i>Order Summary</h6>
                                            
                                            <!-- Products Breakdown -->
                                            <div id="garment_productsBreakdown" class="mb-3">
                                                <div class="text-muted small mb-2">No products selected yet</div>
                                            </div>
                                            
                                            <hr>
                                            
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Total Quantity:</span>
                                                <span id="garment_totalQtyDisplay">0</span>
                                            </div>
                                            <div class="d-flex justify-content-between fw-bold mb-2">
                                                <span>Total Amount:</span>
                                                <span id="garment_totalAmountDisplay">₱0.00</span>
                                            </div>
                                            
                                            <!-- Print Summary Sidebar -->
                                            <div id="garment_printSummarySidebar" style="display:none;">
                                                <hr>
                                                <div id="garment_printSizesBreakdown" class="small mb-2"></div>
                                                <div class="d-flex justify-content-between small">
                                                    <span>Print Cost/ea:</span>
                                                    <span id="garment_printCostPerItemDisplay">₱0.00</span>
                                                </div>
                                                <div class="d-flex justify-content-between small">
                                                    <span>Qty:</span>
                                                    <span id="garment_printQtyDisplay">0</span>
                                                </div>
                                                <div class="d-flex justify-content-between small">
                                                    <span>Print Subtotal:</span>
                                                    <span id="garment_printSubtotalDisplay">₱0.00</span>
                                                </div>
                                                <div class="d-flex justify-content-between small text-danger" id="garment_comboDiscountRow" style="display:none;">
                                                    <span>Combo Discount:</span>
                                                    <span id="garment_comboDiscountDisplay">-₱0.00</span>
                                                </div>
                                                <div class="d-flex justify-content-between small text-danger" id="garment_bulkDiscountRow" style="display:none;">
                                                    <span>Bulk Discount:</span>
                                                    <span id="garment_bulkDiscountDisplay">-₱0.00</span>
                                                </div>
                                                <hr class="my-1">
                                                <div class="d-flex justify-content-between fw-bold small">
                                                    <span>Print Total:</span>
                                                    <span id="garment_printTotalDisplay">₱0.00</span>
                                                </div>
                                                <hr>
                                                <div class="d-flex justify-content-between fw-bold">
                                                    <span>Grand Total:</span>
                                                    <span id="garment_grandTotalDisplay">₱0.00</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Special Price Section -->
                                            <div class="mt-3 p-2 bg-light rounded" id="garment_specialPriceSection" style="display:none;">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="fw-bold small">💵 SPECIAL PRICE ACTIVE</span>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="garment_clearSpecialPrice()">✕ Clear</button>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="small text-muted">Set Print Total:</label>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control" id="garment_specialPrintTotal" min="0" step="0.01" placeholder="0.00" oninput="garment_onSpecialPriceChange()">
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="small text-muted">Reason *</label>
                                                    <input type="text" class="form-control form-control-sm" id="garment_specialPriceReason" placeholder="Why special price?" required>
                                                </div>
                                            </div>
                                            
                                            <button type="button" class="btn btn-outline-warning btn-sm w-100 mt-2" id="garment_specialPriceBtn" onclick="garment_toggleSpecialPrice()">
                                                💰 Special Price
                                            </button>
                                            
                                            <button type="button" class="btn btn-primary w-100 mt-2" id="garment_addItemBtn">
                                                <i class="fas fa-cart-plus me-2"></i> Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SUBLIMATION MODAL BODY - CUSTOMER FORM (hidden by default) -->
                        <div id="sublimationModalBody" style="display:none;">
                            <ul class="nav nav-tabs mb-3" id="sublimationTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="order-details-tab" data-bs-toggle="tab" data-bs-target="#order-details" type="button" role="tab">Order Details</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button" role="tab">Specifications</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="sizes-tab" data-bs-toggle="tab" data-bs-target="#sizes" type="button" role="tab">Sizes</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pricing-tab-sub" data-bs-toggle="tab" data-bs-target="#pricing-tab-body" type="button" role="tab">Pricing</button>
                                </li>
                            </ul>

                            <div class="tab-content" id="sublimationTabContent">
                                <!-- TAB 1: ORDER DETAILS -->
                                <div class="tab-pane fade show active" id="order-details" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Project Name *</label>
                                                    <input type="text" class="form-control" id="sublimation_projectName" placeholder="e.g. DANNA" required oninput="sublimation_autoGenerateDescription()">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Description *</label>
                                                    <input type="text" class="form-control" id="sublimation_description" placeholder="Auto-generated from selections" readonly style="background:#f0f0f0;cursor:default;">
                                                    <div class="form-text small" id="sublimation_descHelp">Auto-generated from Garment + Fabric + Parts selection.</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Fabric *</label>
                                                    <select class="form-control" id="sublimation_fabricSelect" onchange="sublimation_calculateTotal(); sublimation_autoGenerateDescription()">
                                                        <option value="">-- Select Fabric --</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Designer *</label>
                                                    <input type="text" class="form-control" id="sublimation_designer" placeholder="e.g. ALLAN - CONSOL" required>
                                                </div>
                                                <!-- Department auto-assigned; PIC field removed per business req -->
                                                <div class="col-md-4">
                                                    <label class="form-label">Date Needed *</label>
                                                    <input type="date" class="form-control" id="sublimation_dateNeeded" required>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label">Notes / Special Instructions</label>
                                                <textarea class="form-control" id="sublimation_notes" rows="2" placeholder="Additional notes for this order..."></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="card border-primary">
                                                <div class="card-header bg-primary text-white small py-2">
                                                    <i class="fas fa-image me-1"></i> Mock-up Design
                                                </div>
                                                <div class="card-body py-2 text-center">
                                                    <div id="mockupPreviewArea" class="mb-2" style="min-height:140px;display:flex;align-items:center;justify-content:center;border:2px dashed #ddd;border-radius:8px;cursor:pointer;background:#fafafa;overflow:hidden;" onclick="document.getElementById('mockupUpload').click()">
                                                        <div id="mockupPlaceholder">
                                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted"></i>
                                                            <p class="text-muted small mb-0 mt-1">Click to upload mock-up image</p>
                                                        </div>
                                                        <img id="mockupPreview" src="" style="max-height:180px;display:none;max-width:100%;object-fit:contain;">
                                                    </div>
                                                    <input type="file" id="mockupUpload" accept="image/*" style="display:none" onchange="sublimation_previewMockup(event)">
                                                    <div id="mockupFileInfo" class="small" style="display:none;">
                                                        <span id="mockupFileName" class="text-muted"></span>
                                                        <button type="button" class="btn btn-sm btn-outline-danger ms-2 py-0 px-1" onclick="sublimation_removeMockup()" title="Remove"><i class="fas fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 2: SPECIFICATIONS -->
                                <div class="tab-pane fade" id="specs" role="tabpanel">
                                    <!-- Garment Type + Additional Parts at top for immediate visibility -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Garment Type *</label>
                                            <select class="form-control" id="sublimation_garmentSelect" onchange="sublimation_renderSpecs(); sublimation_calculateTotal(); sublimation_autoGenerateDescription()">
                                                <option value="">-- Select Garment --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Additional Parts</label>
                                            <div id="sublimation_partsContainer" class="border rounded p-2 bg-light" style="max-height:80px;overflow-y:auto;">
                                                <p class="text-muted small mb-0">Select Garment type first...</p>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div id="sublimation_specsFields">
                                        <p class="text-muted small mb-0">Select a garment type to see specifications.</p>
                                    </div>
                                </div>

                                <!-- TAB 3: SIZES -->
                                <div class="tab-pane fade" id="sizes" role="tabpanel">
                                    <div class="btn-group btn-group-sm mb-3 w-100" role="group">
                                        <input type="radio" class="btn-check" name="sublimationSizeMode" id="sizeModeByQty" value="by-qty" checked onchange="sublimation_toggleSizeMode()">
                                        <label class="btn btn-outline-primary" for="sizeModeByQty"><i class="fas fa-list-ol me-1"></i>By Size</label>
                                        <input type="radio" class="btn-check" name="sublimationSizeMode" id="sizeModeRoster" value="roster" onchange="sublimation_toggleSizeMode()">
                                        <label class="btn btn-outline-primary" for="sizeModeRoster"><i class="fas fa-users me-1"></i>Per Person (Names)</label>
                                    </div>

                                    <!-- MODE 1: BY SIZE (default) -->
                                    <div id="sublimationSizeByQty">
                                        <div class="alert alert-info py-2 small">
                                            <i class="fas fa-info-circle me-1"></i> Enter the quantity per size. Total will be auto-calculated.
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-4 col-6">
                                                <label class="form-label small">XS</label>
                                                <input type="number" class="form-control form-control-sm size-qty" data-size="XS" min="0" value="0" oninput="sublimation_calculateTotal()">
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <label class="form-label small">Small</label>
                                                <input type="number" class="form-control form-control-sm size-qty" data-size="SMALL" min="0" value="0" oninput="sublimation_calculateTotal()">
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <label class="form-label small">Medium</label>
                                                <input type="number" class="form-control form-control-sm size-qty" data-size="MEDIUM" min="0" value="0" oninput="sublimation_calculateTotal()">
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <label class="form-label small">Large</label>
                                                <input type="number" class="form-control form-control-sm size-qty" data-size="LARGE" min="0" value="0" oninput="sublimation_calculateTotal()">
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <label class="form-label small">XLARGE</label>
                                                <input type="number" class="form-control form-control-sm size-qty" data-size="XLARGE" min="0" value="0" oninput="sublimation_calculateTotal()">
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <label class="form-label small">2XLARGE</label>
                                                <input type="number" class="form-control form-control-sm size-qty" data-size="2XLARGE" min="0" value="0" oninput="sublimation_calculateTotal()">
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <label class="form-label small">3XLARGE</label>
                                                <input type="number" class="form-control form-control-sm size-qty" data-size="3XLARGE" min="0" value="0" oninput="sublimation_calculateTotal()">
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <label class="form-label small">4XLARGE</label>
                                                <input type="number" class="form-control form-control-sm size-qty" data-size="4XLARGE" min="0" value="0" oninput="sublimation_calculateTotal()">
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <label class="form-label small">5XLARGE</label>
                                                <input type="number" class="form-control form-control-sm size-qty" data-size="5XLARGE" min="0" value="0" oninput="sublimation_calculateTotal()">
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <label class="form-label small">6XLARGE</label>
                                                <input type="number" class="form-control form-control-sm size-qty" data-size="6XLARGE" min="0" value="0" oninput="sublimation_calculateTotal()">
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <label class="form-label small">7XLARGE</label>
                                                <input type="number" class="form-control form-control-sm size-qty" data-size="7XLARGE" min="0" value="0" oninput="sublimation_calculateTotal()">
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <label class="form-label small">8XLARGE</label>
                                                <input type="number" class="form-control form-control-sm size-qty" data-size="8XLARGE" min="0" value="0" oninput="sublimation_calculateTotal()">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- MODE 2: PER PERSON (roster) -->
                                    <div id="sublimationSizeRoster" style="display:none;">
                                        <div class="alert alert-success py-2 small">
                                            <i class="fas fa-users me-1"></i> Add each person's name and their assigned size. Total = number of people.
                                        </div>

                                        <!-- Quick Add Row -->
                                        <div class="row g-2 mb-2 align-items-end">
                                            <div class="col-2">
                                                <label class="form-label small mb-0">#</label>
                                                <input type="text" class="form-control form-control-sm" id="roster_newNumber" placeholder="No.">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label small mb-0">Name *</label>
                                                <input type="text" class="form-control form-control-sm" id="roster_newName" placeholder="Person's name">
                                            </div>
                                            <div class="col-3">
                                                <label class="form-label small mb-0">Size *</label>
                                                <select class="form-control form-control-sm" id="roster_newSize">
                                                    <option value="">--</option>
                                                    <option value="XS">XS</option>
                                                    <option value="S">S</option>
                                                    <option value="M">M</option>
                                                    <option value="L">L</option>
                                                    <option value="XL">XL</option>
                                                    <option value="2XL">2XL</option>
                                                    <option value="3XL">3XL</option>
                                                    <option value="4XL">4XL</option>
                                                    <option value="5XL">5XL</option>
                                                    <option value="6XL">6XL</option>
                                                    <option value="7XL">7XL</option>
                                                    <option value="8XL">8XL</option>
                                                </select>
                                            </div>
                                            <div class="col-3">
                                                <label class="form-label small mb-0">&nbsp;</label>
                                                <button type="button" class="btn btn-success btn-sm w-100" onclick="sublimation_addRosterRow()">
                                                    <i class="fas fa-plus"></i> Add
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Bulk Paste + Excel Upload -->
                                        <div class="mb-2">
                                            <div class="d-flex gap-2 flex-wrap">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="sublimation_toggleBulkPaste()">
                                                    <i class="fas fa-paste me-1"></i>Bulk Paste List
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('sublimation_excelInput').click()">
                                                    <i class="fas fa-file-excel me-1"></i>Upload Excel
                                                </button>
                                                <input type="file" id="sublimation_excelInput" accept=".xlsx,.xls,.csv" style="display:none" onchange="sublimation_uploadExcel(event)">
                                            </div>
                                            <div id="sublimationBulkPasteArea" style="display:none;" class="mt-2">
                                                <label class="form-label small">Paste one per line: <code>Name,Size</code> or <code>Number,Name,Size</code></label>
                                                <textarea class="form-control form-control-sm" id="sublimation_bulkPasteInput" rows="4" placeholder="e.g.&#10;MON,XL&#10;KOKO,L&#10;MANEX,XL&#10;JUN,L&#10;GERBIE,M"></textarea>
                                                <button type="button" class="btn btn-info btn-sm mt-1" onclick="sublimation_bulkPaste()">
                                                    <i class="fas fa-upload me-1"></i>Import List
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Roster Table -->
                                        <div class="table-responsive" style="max-height:300px;overflow-y:auto;">
                                            <table class="table table-sm table-bordered mb-0" id="sublimation_rosterTable">
                                                <thead class="table-light">
                                                    <tr id="sublimation_rosterHeader">
                                                        <th style="width:50px">#</th>
                                                        <th>Name</th>
                                                        <th style="width:80px">Size</th>
                                                        <th style="width:40px">Qty</th>
                                                        <th style="width:40px"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="sublimation_rosterBody">
                                                    <tr id="sublimation_rosterEmpty">
                                                        <td colspan="5" class="text-center text-muted small py-3">
                                                            <i class="fas fa-user-plus me-1"></i> Add names above to build your roster.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-1 text-end">
                                            <small class="text-muted">
                                                <span id="sublimation_rosterCount">0</span> people
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Column Mapping Modal -->
                                    <div class="modal fade" id="sublimation_columnMapModal" tabindex="-1">
                                        <div class="modal-dialog modal-sm">
                                            <div class="modal-content">
                                                <div class="modal-header py-2">
                                                    <h6 class="modal-title"><i class="fas fa-columns me-1"></i>Map Columns</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body py-2 small">
                                                    <p class="mb-2 text-muted">Match your Excel columns to roster fields:</p>
                                                    <div class="mb-2">
                                                        <label class="form-label mb-0">→ <strong>Name</strong> column</label>
                                                        <select class="form-select form-select-sm" id="mapCol_name">
                                                            <option value="">-- skip --</option>
                                                        </select>
                                                        <div class="form-text mt-0">Person's name or identifier</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label mb-0">→ <strong>Number</strong> column</label>
                                                        <select class="form-select form-select-sm" id="mapCol_number">
                                                            <option value="">-- skip --</option>
                                                        </select>
                                                        <div class="form-text mt-0">Jersey number / row number</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label mb-0">→ <strong>Size</strong> column</label>
                                                        <select class="form-select form-select-sm" id="mapCol_size">
                                                            <option value="">-- skip --</option>
                                                        </select>
                                                        <div class="form-text mt-0">XS, S, M, L, XL, 2XL, etc.</div>
                                                    </div>
                                                    </div>
                                                    <small class="text-muted" id="sublimation_mapPreview">Loading preview...</small>
                                                </div>
                                                <div class="modal-footer py-1">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="button" class="btn btn-sm btn-success" onclick="sublimation_applyMapping()">
                                                        <i class="fas fa-check me-1"></i>Import
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 4: PRICING -->
                                <div class="tab-pane fade" id="pricing-tab-body" role="tabpanel">
                                    <div class="card border-success mt-3">
                                        <div class="card-header bg-success text-white small py-2">
                                            <i class="fas fa-file-invoice me-1"></i> Full Price Breakdown
                                        </div>
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between small">
                                                <span id="pricingTab_garmentLabel">Garment:</span>
                                                <strong><span id="pricingTab_garmentPrice">₱0.00</span></strong>
                                            </div>
                                            <div class="d-flex justify-content-between small">
                                                <span id="pricingTab_fabricLabel">Fabric:</span>
                                                <strong><span id="pricingTab_fabricPrice">₱0.00</span></strong>
                                            </div>
                                            <div id="pricingTab_partsBreakdown"></div>
                                            <div id="pricingTab_sizeBreakdown"></div>
                                            <hr class="my-1">
                                            <div class="d-flex justify-content-between">
                                                <span class="fw-bold">Per Unit (base):</span>
                                                <strong class="fs-6"><span id="pricingTab_unitPrice">₱0.00</span></strong>
                                            </div>
                                            <div class="d-flex justify-content-between small">
                                                <span>Total Qty (from Sizes tab):</span>
                                                <strong><span id="pricingTab_totalQty">0</span></strong>
                                            </div>
                                            <hr class="my-1">
                                            <div class="d-flex justify-content-between fw-bold fs-5" style="color:#198754;">
                                                <span>TOTAL:</span>
                                                <span id="pricingTab_grandTotal">₱0.00</span>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-success w-100 mt-3" id="sublimation_addItemBtn">
                                        <i class="fas fa-check-circle me-2"></i> Confirm &amp; Add to Cart
                                    </button>
                                    <button type="button" class="btn btn-outline-info w-100 mt-2" id="sublimation_printOrderSlip">
                                        <i class="fas fa-print me-2"></i> Print Order Slip
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Print Order Slip Template (hidden, populated via JS) -->
<div id="sublimation_printSlip" style="display:none;">
    <style>
        @media print {
            @page { size: A4 landscape; margin: 12mm 15mm; }
            body * { visibility: hidden; }
            #sublimation_printSlip, #sublimation_printSlip * { visibility: visible; }
            #sublimation_printSlip { position: absolute; left: 0; top: 0; width: 100%; max-width: 277mm; display: block !important; }
            .no-print { display: none !important; }
        }
        .print-slip { font-family: 'Courier New', monospace; font-size: 10pt; color: #000; width: 100%; }
        .print-slip h1 { font-size: 16pt; margin: 0; text-align: center; }
        .print-slip h2 { font-size: 13pt; margin: 0; text-align: center; }
        .print-slip table { width: 100%; border-collapse: collapse; }
        .print-slip td, .print-slip th { border: 1px solid #000; padding: 3px 5px; text-align: left; font-size: 9pt; vertical-align: top; }
        .print-slip .no-border td, .print-slip .no-border { border: none; }
        .print-slip .field-label { font-weight: bold; background: #f0f0f0; width: 1%; white-space: nowrap; }
        .print-slip .mockup-box { border: 2px dashed #999; display: flex; align-items: center; justify-content: center; text-align: center; color: #999; font-size: 9pt; overflow: hidden; width:100%; aspect-ratio: 4 / 3; max-height:300px; }
        .print-slip .mockup-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .print-slip .roster-table th { background: #e0e0e0; font-weight: bold; text-align: center; }
        .print-slip .roster-table td { text-align: center; }
        .print-slip .section-title { font-weight: bold; font-size: 11pt; margin-top: 4px; margin-bottom: 2px; }
        .print-slip .divider { border-top: 2px solid #000; margin: 1px 0; }
        /* ⬇️ Compress upper 3-column area — keep text size, reduce padding/margins */
        #ps_upperInfo > tbody > tr > td { padding: 1px 3px !important; }
        #ps_upperInfo table td, #ps_upperInfo table th { padding: 1px 3px !important; font-size: 9pt; }
        #ps_upperInfo .section-title { margin-top: 1px; margin-bottom: 1px; font-size: 11pt; }
        #ps_upperInfo .field-label { padding: 1px 3px !important; }
    </style>
    <div id="printSlipContent" class="print-slip">
        <!-- Title -->
        <h1>CUSTOMER FORM SPECIFICATIONS</h1>

        <div class="divider"></div>

        <!-- Top: 2 columns — Info (33%) | Parts (67%) -->
        <table id="ps_upperInfo"><tr>
            <td style="width:33%;vertical-align:top;" class="no-border">
                <table style="width:100%;"><tr><td class="field-label" style="width:100px;">PROJECT:</td><td id="ps_projectName"></td></tr></table>
                <table style="width:100%;"><tr><td class="field-label" style="width:100px;">DESCRIPTION:</td><td id="ps_description"></td></tr></table>
                <table style="width:100%;"><tr><td class="field-label" style="width:100px;">FABRIC:</td><td id="ps_fabric"></td></tr></table>
                <table style="width:100%;"><tr><td class="field-label" style="width:100px;">DESIGNER:</td><td id="ps_designer"></td></tr></table>
                <table style="width:100%;"><tr><td class="field-label" style="width:100px;">QTY:</td><td id="ps_qty"></td></tr></table>
                <table style="width:100%;"><tr><td class="field-label" style="width:100px;">DATE NEEDED:</td><td id="ps_dateNeeded"></td></tr></table>
            </td>
            <td style="width:67%;vertical-align:top;">
                <div id="ps_partsContainer" style="width:100%;">
                    <table id="ps_leftPartsCol" style="width:49%;float:left;">
                        <tr><th style="width:100px;">Part</th><th>Color/Details</th></tr>
                    </table>
                    <table id="ps_rightPartsCol" style="width:49%;float:right;">
                        <tr><th style="width:100px;">Part</th><th>Color/Details</th></tr>
                    </table>
                    <div style="clear:both;"></div>
                </div>
                <!-- Hidden collectors for JS compatibility (populated first, then split into visible columns) -->
                <div style="display:none;">
                <table id="ps_specsParts">
                    <tr><th style="width:100px;">Part</th><th>Color/Details</th></tr>
                </table>
                <table id="ps_specsOthers">
                    <tr><th style="width:100px;">Item</th><th>Details</th></tr>
                </table>
                </div>
            </td>
        </tr></table>

        <div class="divider"></div>

        <!-- Bottom: Mock-up (30%) | Name List (70%) -->
        <table><tr>
            <td style="width:30%;vertical-align:top" class="no-border">
                <div class="section-title">MOCK UP</div>
                <div id="ps_mockupBox" class="mockup-box" style="margin:0 auto;">
                    <span id="ps_mockupPlaceholder">MOCK UP HERE</span>
                    <img id="ps_mockupImg" src="" style="display:none;">
                </div>
            </td>
            <td style="width:70%;vertical-align:top" class="no-border">
                <div class="section-title">NAME LIST</div>
                <table class="roster-table" id="ps_rosterTable">
                    <thead></thead>
                    <tbody id="ps_rosterBody"></tbody>
                </table>
            </td>
        </tr></table>

        <div style="margin-top:4px;font-size:9pt;text-align:right;border-top:1px solid #000;padding-top:2px;" id="ps_note"></div>
    </div>
</div>

<!-- Production Calendar Section -->
<div class="container-fluid mt-4" id="productionCalendarSection">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0" style="color: #667eea;">
            <i class="fas fa-calendar-alt me-2"></i>Production Calendar
        </h4>
        <a href="{{ route('sales.prototype.calendar') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-expand me-1"></i> Full View
        </a>
    </div>
    
    <div class="calendar-wrapper" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); overflow: hidden;">
        <div class="calendar-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.8rem 1.2rem;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="font-size:1rem;"><i class="fas fa-calendar-alt me-2"></i>Weekly View</h5>
                <div class="d-flex align-items-center gap-2" style="font-size:0.85rem;">
                    <button class="btn btn-sm btn-outline-light" style="font-size:0.8rem;padding:0.2rem 0.5rem;" onclick="inlineChangeWeek(-1)"><i class="fas fa-chevron-left"></i></button>
                    <span class="fw-bold" id="inlineWeekLabel" style="font-size:0.85rem;min-width:160px;text-align:center;">This Week</span>
                    <button class="btn btn-sm btn-outline-light" style="font-size:0.8rem;padding:0.2rem 0.5rem;" onclick="inlineChangeWeek(1)"><i class="fas fa-chevron-right"></i></button>
                    <button class="btn btn-sm btn-outline-light ms-1" style="font-size:0.8rem;padding:0.2rem 0.5rem;" onclick="inlineGoToToday()"><i class="fas fa-calendar-day"></i></button>
                </div>
            </div>
        </div>
        
        <!-- Department Tabs -->
        <div style="padding: 0.5rem 1.2rem; border-bottom: 1px solid #eef1ff; display: flex; gap: 0.4rem; flex-wrap: wrap; background:#fafbff;">
            <button class="inline-dept-tab active-all" data-dept="all" onclick="inlineFilterDept('all')"
                style="padding:0.25rem 0.8rem;border-radius:15px;border:2px solid #6c757d;background:#6c757d;color:white;font-weight:600;font-size:0.75rem;cursor:pointer;">All</button>
            <button class="inline-dept-tab" data-dept="iPrint" onclick="inlineFilterDept('iPrint')"
                style="padding:0.25rem 0.8rem;border-radius:15px;border:2px solid #0d6efd;background:transparent;color:#0d6efd;font-weight:600;font-size:0.75rem;cursor:pointer;">iPrint</button>
            <button class="inline-dept-tab" data-dept="Consol" onclick="inlineFilterDept('Consol')"
                style="padding:0.25rem 0.8rem;border-radius:15px;border:2px solid #198754;background:transparent;color:#198754;font-weight:600;font-size:0.75rem;cursor:pointer;">Consol</button>
            <button class="inline-dept-tab" data-dept="Class" onclick="inlineFilterDept('Class')"
                style="padding:0.25rem 0.8rem;border-radius:15px;border:2px solid #6f42c1;background:transparent;color:#6f42c1;font-weight:600;font-size:0.75rem;cursor:pointer;">Class</button>
            <button class="inline-dept-tab" data-dept="Cinco" onclick="inlineFilterDept('Cinco')"
                style="padding:0.25rem 0.8rem;border-radius:15px;border:2px solid #dc3545;background:transparent;color:#dc3545;font-weight:600;font-size:0.75rem;cursor:pointer;">Cinco</button>
            <button class="inline-dept-tab" data-dept="MTO" onclick="inlineFilterDept('MTO')"
                style="padding:0.25rem 0.8rem;border-radius:15px;border:2px solid #fd7e14;background:transparent;color:#fd7e14;font-weight:600;font-size:0.75rem;cursor:pointer;">MTO</button>
        </div>
        
        <!-- Calendar Grid Container -->
        <div id="inlineCalendarContainer" style="padding: 0.8rem 1.2rem; max-height: 480px; overflow-y: auto;">
            <div class="text-center py-4" style="color:#999;">
                <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                <p style="font-size:0.85rem;">Loading calendar...</p>
            </div>
        </div>
    </div>
</div>

<style>
/* Inline calendar grid */
.inline-week-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    margin-bottom: 1rem;
}
.inline-day-header {
    text-align: center;
    font-weight: 700;
    font-size: 0.65rem;
    color: #999;
    text-transform: uppercase;
    padding: 0.25rem 0;
    border-bottom: 2px solid #eef1ff;
}
.inline-day-cell {
    min-height: 65px;
    background: #fafbff;
    border-radius: 6px;
    padding: 0.2rem;
    border: 1px solid #e8ecf5;
}
.inline-day-cell.today { border-color: #667eea; box-shadow: 0 0 0 2px rgba(102,126,234,0.12); }
.inline-day-cell .day-num {
    font-size: 0.65rem;
    font-weight: 700;
    color: #888;
    padding: 0.1rem 0.2rem;
    margin-bottom: 0.1rem;
}
.inline-day-cell.today .day-num {
    background: #667eea; color: white; border-radius: 50%;
    width: 18px; height: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.6rem;
}
.inline-proj {
    font-size: 0.6rem;
    padding: 0.15rem 0.25rem;
    margin-bottom: 0.15rem;
    border-radius: 3px;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
}
.inline-proj:hover { opacity: 0.85; }
.inline-month-label {
    text-align: center;
    font-size: 0.85rem;
    font-weight: 700;
    color: #666;
    margin-bottom: 0.3rem;
}
</style>

<script>
// ===== INLINE CALENDAR (weekly grid) =====
let inlineDate = new Date();
let inlineDept = 'all';
const inlineDC = {'iPrint':'#0d6efd','Consol':'#198754','Cinco':'#dc3545','Class':'#6f42c1','MTO':'#fd7e14','Other':'#6c757d','iprint':'#0d6efd','consol':'#198754','cinco':'#dc3545','class':'#6f42c1','mto':'#fd7e14','other':'#6c757d'};
const inlineMN = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const inlineDN = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

function inlineMon(d) {
    const date = new Date(d);
    const day = date.getDay();
    date.setDate(date.getDate() - (day === 0 ? 6 : day - 1));
    date.setHours(0,0,0,0);
    return date;
}
function inlineSun(m) { const d = new Date(m); d.setDate(d.getDate()+6); return d; }

function inlineChangeWeek(delta) { inlineDate.setDate(inlineDate.getDate()+(delta*7)); inlineLoadCal(); }
function inlineGoToToday() { inlineDate = new Date(); inlineLoadCal(); }

function inlineFilterDept(dept) {
    inlineDept = dept;
    document.querySelectorAll('.inline-dept-tab').forEach(t => {
        t.style.background = 'transparent'; t.style.color = t.dataset.dept === 'all' ? '#6c757d' : (inlineDC[t.dataset.dept] || '#0d6efd');
    });
    const a = document.querySelector(`.inline-dept-tab[data-dept="${dept}"]`);
    a.style.background = dept === 'all' ? '#6c757d' : (inlineDC[dept] || '#6c757d');
    a.style.color = 'white';
    inlineLoadCal();
}

function inlineRenderWeek(monday, projects) {
    const today = new Date().toISOString().split('T')[0];
    const monthSet = new Set();
    for (let i=0; i<7; i++) { const d=new Date(monday); d.setDate(d.getDate()+i); monthSet.add(inlineMN[d.getMonth()]); }
    const monthLabel = [...monthSet].join(' - ');

    let html = `<div class="inline-month-label">${monthLabel} ${monday.getFullYear()}</div><div class="inline-week-grid">`;
    inlineDN.forEach(n => { html+=`<div class="inline-day-header">${n}</div>`; });

    for (let i=0; i<7; i++) {
        const d = new Date(monday); d.setDate(d.getDate()+i);
        const dateStr = d.toISOString().split('T')[0];
        const isToday = dateStr === today;
        const dayProjects = projects.filter(p => {
            const pd = p.date_needed || p.estimated_completion_date || p.created_at;
            if (!pd) return false;
            return (pd.split(' ')[0]) === dateStr;
        });

        html += `<div class="inline-day-cell ${isToday?'today':''}" data-d="${dateStr}">`;
        html += `<div class="day-num">${d.getDate()}</div>`;

        dayProjects.slice(0, 3).forEach(p => {
            const dept = p.department_name || 'other';
            const color = inlineDC[dept] || '#6c757d';
            const name = (p.customer_name || '').substring(0, 10);
            const amt = parseFloat(p.total_amount||0);
            html += `<div class="inline-proj" style="background:${color}15;border-left:2px solid ${color};color:#333;" title="${p.customer_name||'Unknown'} - ₱${amt.toLocaleString('en-PH',{minimumFractionDigits:2})}" onclick="window.open('/sales/prototype/${p.id}/details','_blank')">${name}</div>`;
        });
        if (dayProjects.length > 3) {
            html += `<div style="font-size:0.55rem;color:#667eea;text-align:center;">+${dayProjects.length-3}</div>`;
        }

        html += '</div>';
    }
    html += '</div>';
    return html;
}

function inlineLoadCal() {
    const monday = inlineMon(inlineDate);
    document.getElementById('inlineWeekLabel').textContent =
        inlineMN[monday.getMonth()]+' '+monday.getDate()+' - '+
        inlineMN[inlineSun(monday).getMonth()]+' '+inlineSun(monday).getDate();

    const container = document.getElementById('inlineCalendarContainer');
    container.innerHTML = '<div class="text-center py-4" style="color:#999;"><i class="fas fa-spinner fa-spin fa-2x mb-2"></i><p style="font-size:0.85rem;">Loading calendar...</p></div>';

    const weeks = [];
    for (let i=-1; i<=1; i++) { const w=new Date(monday); w.setDate(w.getDate()+(i*7)); weeks.push(w); }
    const end = inlineSun(weeks[weeks.length-1]);

    fetch('/sales/prototype/calendar-data', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||''},
        body:JSON.stringify({start_date:weeks[0].toISOString().split('T')[0],end_date:end.toISOString().split('T')[0],department:inlineDept})
    })
    .then(r=>r.json())
    .then(data => {
        const projects = data.projects || [];
        let html = '';
        weeks.forEach(w => { html += inlineRenderWeek(w, projects); });
        container.innerHTML = html;
    })
    .catch(() => { container.innerHTML = '<div class="alert alert-danger py-2" style="font-size:0.85rem;">Failed to load calendar.</div>'; });
}

document.addEventListener('DOMContentLoaded', () => { setTimeout(inlineLoadCal, 500); });
</script>

@endsection
@push('scripts')
<script>
// ============================================
// PRODUCT BOXES SYSTEM - CONNECTED TO DATABASE
// ============================================

// Global variables - MUST BE DECLARED AT TOP
let selectedItems = [];
let currentProductType = '';
let currentDepartment = '';

// Open product modal - GLOBAL FUNCTION for onclick handlers
var garment_initialized = false;

window.openProductModal = function(productType) {
    console.log('Opening modal for:', productType);
    currentProductType = productType;
    // Reset department - user will select in modal
    currentDepartment = '';
    
    // Set modal title
    const modalTitle = document.getElementById('productModalLabel');
    if (modalTitle) {
        const titles = {
            'garment': 'Garment Printing',
            'tarpaulin': 'Tarpaulin Printing', 
            'embroidery': 'Embroidery',
            'cutting': 'Fullsublimation Printing',
            'sewing': 'Sewing Services',
            'design': 'Design Services'
        };
        const icons = {
            'garment': 'fa-tshirt',
            'tarpaulin': 'fa-image',
            'embroidery': 'fa-thread',
            'cutting': 'fa-cut',
            'sewing': 'fa-sewing-machine',
            'design': 'fa-pencil-ruler'
        };
        modalTitle.innerHTML = `<i class="fas ${icons[productType]}"></i> ${titles[productType]}`;
    }
    
    // Toggle modal body based on product type
    const genericBody = document.getElementById('genericModalBody');
    const garmentBody = document.getElementById('garmentModalBody');
    const sublimationBody = document.getElementById('sublimationModalBody');
    const modalDialog = document.querySelector('#productModal .modal-dialog');
    
    if (productType === 'garment') {
        // Show garment body, hide others
        if (genericBody) genericBody.style.display = 'none';
        if (garmentBody) garmentBody.style.display = 'block';
        if (sublimationBody) sublimationBody.style.display = 'none';
        
        // Use wider modal for garment
        if (modalDialog) {
            modalDialog.classList.remove('modal-lg');
            modalDialog.classList.add('modal-xl');
        }
        
        // Initialize garment modal
        if (typeof garment_openModal === 'function') {
            garment_openModal(productType);
        }
    } else if (productType === 'cutting') {
        // Show sublimation body, hide others
        if (genericBody) genericBody.style.display = 'none';
        if (garmentBody) garmentBody.style.display = 'none';
        if (sublimationBody) sublimationBody.style.display = 'block';
        
        // Use wider modal for sublimation
        if (modalDialog) {
            modalDialog.classList.remove('modal-lg');
            modalDialog.classList.add('modal-xl');
        }
        
        // Initialize sublimation modal
        if (typeof sublimation_openModal === 'function') {
            sublimation_openModal();
        }
    } else {
        // Show generic body, hide others
        if (genericBody) genericBody.style.display = 'block';
        if (garmentBody) garmentBody.style.display = 'none';
        if (sublimationBody) sublimationBody.style.display = 'none';
        
        // Revert to normal width
        if (modalDialog) {
            modalDialog.classList.remove('modal-xl');
            modalDialog.classList.add('modal-lg');
        }
        
        // Load products for non-garment
        loadProductsFromDatabase(productType);
    }
    
    // Show modal
    const modalEl = document.getElementById('productModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
};

// Global product option builder
window.garment_buildProductOption = function(p) {
    var productSize = p.size;
    if (!productSize && p.description) {
        var sizeMatch = p.description.match(/Size:\s*([^,]+)/i);
        if (sizeMatch) productSize = sizeMatch[1].trim();
    }
    var displayText = '';
    if (productSize) displayText += 'Size: ' + productSize;
    if (p.color) displayText += ' ' + p.color;
    if (p.brand) displayText += ' ' + p.brand;
    var shirtType = p.shirt_type;
    if (!shirtType && p.description) {
        var typeMatch = p.description.match(/Type:\s*([^,]+)/i);
        if (typeMatch) shirtType = typeMatch[1].trim();
    }
    if (shirtType) displayText += ' ' + shirtType;
    displayText += ' - \u20B1' + parseFloat(p.base_price).toFixed(2);
    return displayText;
};

// Load products - GLOBAL
window.loadProductsFromDatabase = function(productType) {
    // For garment, populate the multiple product selects (in garment modal body)
    // For other types, populate the single product select (in generic modal body)
    
    // Build display text for a product option
    function buildProductOption(p) {
        let productSize = p.size;
        if (!productSize && p.description) {
            const sizeMatch = p.description.match(/Size:\s*([^,]+)/i);
            if (sizeMatch) productSize = sizeMatch[1].trim();
        }
        let displayText = '';
        if (productSize) displayText += 'Size: ' + productSize;
        if (p.color) displayText += ' ' + p.color;
        if (p.brand) displayText += ' ' + p.brand;
        let shirtType = p.shirt_type;
        if (!shirtType && p.description) {
            const typeMatch = p.description.match(/Type:\s*([^,]+)/i);
            if (typeMatch) shirtType = typeMatch[1].trim();
        }
        if (shirtType) displayText += ' ' + shirtType;
        displayText += ' - \u20B1' + parseFloat(p.base_price).toFixed(2);
        return { displayText: displayText, productSize: productSize, shirtType: shirtType };
    }
    
    // Populate a single select element with products
    function populateSelect(selectEl, products) {
        if (!selectEl) return;
        selectEl.innerHTML = '<option value="">Select product</option>';
        if (products.length === 0) {
            selectEl.innerHTML = '<option value="">No products found.</option>';
            return;
        }
        products.forEach(function(p) {
            var info = buildProductOption(p);
            var opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = info.displayText;
            opt.dataset.price = p.base_price;
            opt.dataset.productName = p.name;
            opt.dataset.brand = p.brand || '';
            opt.dataset.size = info.productSize || '';
            opt.dataset.color = p.color || '';
            opt.dataset.volumeDiscounts = JSON.stringify(p.volume_discounts);
            opt.dataset.productData = JSON.stringify(p);
            selectEl.appendChild(opt);
        });
    }
    
    // Determine which selects to populate
    var selects = [];
    if (productType === 'garment') {
        // Populate all garment product rows
        selects = document.querySelectorAll('#garment_productRowsContainer .product-select');
    } else {
        // Populate the single product select in generic body
        var singleSelect = document.getElementById('productSelect');
        if (singleSelect) selects = [singleSelect];
    }
    
    if (selects.length === 0) return;
    
    // Show loading
    selects.forEach(function(s) { s.innerHTML = '<option value="">Loading products...</option>'; });
    
    // Fetch products from API
    fetch('/api/products-for-box/' + productType)
        .then(function(response) { return response.json(); })
        .then(function(products) {
            selects.forEach(function(s) { populateSelect(s, products); });
            
            if (productType === 'garment') {
                // Reset qty and department for garment
                var qtyInputs = document.querySelectorAll('#garment_productRowsContainer .product-quantity');
                qtyInputs.forEach(function(el) { el.value = '1'; });
                /* Department auto-assigned; no reset needed */
                var notes = document.getElementById('garment_productNotes');
                if (notes) notes.value = '';
                // Recalculate garment summary
                garment_updatePrintSummary();
            } else {
                // Reset form for non-garment
                const qty = document.getElementById('productQuantity');
                const price = document.getElementById('productPrice');
                const notes = document.getElementById('productNotes');
                if (qty) qty.value = '1';
                if (price) price.value = '';
                if (notes) notes.value = '';
                /* Department auto-assigned; no manual select needed */
                
                // Auto-fill price on select
                if (selects[0]) {
                    selects[0].addEventListener('change', function() {
                        const opt = this.options[this.selectedIndex];
                        if (opt.value && opt.dataset.price && price) {
                            price.value = opt.dataset.price;
                            calculateItemTotal();
                        }
                    });
                }
                calculateItemTotal();
            }
        })
        .catch(function(error) {
            console.error('Error loading products:', error);
            selects.forEach(function(s) {
                s.innerHTML = '<option value="">Error loading products. Please try again.</option>';
            });
        });
};

// Get demo products - GLOBAL
window.getDemoProducts = function(productType) {
    const products = {
        'garment': [
            { id: 1, name: 'DTF Printing (Small)', price: 150.00 },
            { id: 2, name: 'DTF Printing (Medium)', price: 200.00 },
            { id: 3, name: 'DTF Printing (Large)', price: 250.00 },
            { id: 4, name: 'Sublimation Printing', price: 300.00 },
            { id: 5, name: 'Heat Transfer', price: 180.00 }
        ],
        'tarpaulin': [
            { id: 6, name: 'Tarpaulin 3x6 ft', price: 450.00 },
            { id: 7, name: 'Tarpaulin 4x8 ft', price: 650.00 },
            { id: 8, name: 'Banner 2x5 ft', price: 350.00 },
            { id: 9, name: 'Signage 1x2 ft', price: 250.00 }
        ],
        'embroidery': [
            { id: 10, name: 'Small Logo Embroidery', price: 120.00 },
            { id: 11, name: 'Medium Logo Embroidery', price: 200.00 },
            { id: 12, name: 'Large Design Embroidery', price: 350.00 },
            { id: 13, name: 'Name Embroidery', price: 80.00 }
        ],
        'cutting': [
            { id: 14, name: 'Fabric Cutting (per yard)', price: 50.00 },
            { id: 15, name: 'Pattern Making', price: 200.00 },
            { id: 16, name: 'Bulk Cutting (10+ yards)', price: 40.00 }
        ],
        'sewing': [
            { id: 17, name: 'Basic Sewing (per hour)', price: 150.00 },
            { id: 18, name: 'Garment Assembly', price: 250.00 },
            { id: 19, name: 'Alterations', price: 100.00 }
        ],
        'design': [
            { id: 20, name: 'Graphic Design (Basic)', price: 300.00 },
            { id: 21, name: 'Mockup Creation', price: 500.00 },
            { id: 22, name: 'Complete Design Package', price: 800.00 }
        ]
    };
    return products[productType] || [];
};

// Calculate item total - GLOBAL
window.calculateItemTotal = function() {
    const qty = document.getElementById('productQuantity');
    const price = document.getElementById('productPrice');
    const totalDisplay = document.getElementById('itemTotalDisplay');
    const qtyDisplay = document.getElementById('quantityDisplay');
    const priceDisplay = document.getElementById('priceDisplay');
    const discountDisplay = document.getElementById('discountDisplay');
    const select = document.getElementById('productSelect');
    
    if (!qty || !price || !totalDisplay) return;
    
    const quantity = parseFloat(qty.value) || 0;
    let unitPrice = parseFloat(price.value) || 0;
    let discountPercent = 0;
    let discountAmount = 0;
    
    // Check for bulk discounts if product is selected
    if (select && select.value) {
        const opt = select.options[select.selectedIndex];
        const volumeDiscounts = opt.dataset.volumeDiscounts ? JSON.parse(opt.dataset.volumeDiscounts) : [];
        
        // Find applicable discount
        for (const discount of volumeDiscounts) {
            if (quantity >= discount.min_quantity) {
                // Use discounted price
                unitPrice = discount.price_per_unit;
                const basePrice = parseFloat(opt.dataset.price) || unitPrice;
                discountPercent = ((basePrice - unitPrice) / basePrice) * 100;
                discountAmount = (basePrice - unitPrice) * quantity;
                break;
            }
        }
    }
    
    const total = quantity * unitPrice;
    
    totalDisplay.textContent = '₱' + total.toFixed(2);
    if (qtyDisplay) qtyDisplay.textContent = quantity;
    if (priceDisplay) priceDisplay.textContent = '₱' + unitPrice.toFixed(2);
    
    // Show discount if applicable
    if (discountDisplay) {
        if (discountPercent > 0) {
            discountDisplay.textContent = `Bulk discount: ${discountPercent.toFixed(1)}% (Save: ₱${discountAmount.toFixed(2)})`;
            discountDisplay.className = 'text-success small';
        } else {
            discountDisplay.textContent = 'No bulk discount applied';
            discountDisplay.className = 'text-muted small';
        }
    }
};

// Add item to cart - GLOBAL (UPDATED FOR MULTIPLE PRODUCTS)
window.addItemToCart = function() {
    var notes = document.getElementById('productNotes');
    
    // Get all product rows
    var rows = document.querySelectorAll('.product-row');
    var cartItems = [];
    
    // Department auto-assigned by product type
    var deptMap = {'garment':'iprint','tarpaulin':'consol','embroidery':'cinco','cutting':'class','sewing':'class','design':'iprint'};
    var department = deptMap[currentProductType] || 'class';
    
    // Process each product row
    let hasValidProducts = false;
    let validationError = false;
    
    rows.forEach((row, index) => {
        if (validationError) return;
        
        const select = row.querySelector('.product-select');
        const qtyInput = row.querySelector('.product-quantity');
        
        if (select && qtyInput) {
            const selectedOption = select.options[select.selectedIndex];
            const quantity = parseInt(qtyInput.value) || 0;
            const price = selectedOption && selectedOption.value ? parseFloat(selectedOption.dataset.price) : 0;
            
            // Validate this row
            if (!select.value) {
                alert(`Please select a product for row ${index + 1}`);
                select.focus();
                validationError = true;
                return;
            }
            
            if (quantity <= 0) {
                alert(`Please enter a valid quantity for row ${index + 1}`);
                qtyInput.focus();
                validationError = true;
                return;
            }
            
            if (price <= 0) {
                alert(`Invalid price for product in row ${index + 1}`);
                validationError = true;
                return;
            }
            
            // Build product name
            let productName = selectedOption.dataset.productName || 'Unknown Product';
            const brand = selectedOption.dataset.brand || '';
            const size = selectedOption.dataset.size || '';
            const color = selectedOption.dataset.color || '';
            
            if (brand) productName += ` (${brand})`;
            if (size) productName += ` ${size}`;
            if (color) productName += ` ${color}`;
            
            // Create cart item
            const cartItem = {
                id: Date.now() + index, // Unique ID for cart item
                productId: select.value,
                productType: currentProductType,
                department: department,
                name: productName,
                quantity: quantity,
                unitPrice: price,
                totalPrice: quantity * price,
                notes: notes ? notes.value.trim() : '',
                brand: brand,
                size: size,
                color: color,
                rowIndex: index + 1,
                timestamp: new Date().toISOString()
            };
            
            cartItems.push(cartItem);
            hasValidProducts = true;
        }
    });
    
    if (validationError) return;
    
    if (!hasValidProducts) {
        alert('Please add at least one product');
        return;
    }
    
    // Add all items to cart
    cartItems.forEach(item => {
        selectedItems.push(item);
    });
    
    updateSelectedItemsDisplay();
    updateOrderSummary();
    
    // Reset form (keep first row)
    initializeProductRows();
    if (notes) notes.value = '';
    
    // Close modal
    const modalEl = document.getElementById('productModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
    
    // Show success message
    showToast(`Successfully added ${cartItems.length} product(s) to cart!`, 'success');
};

// Update selected items display - GLOBAL
window.updateSelectedItemsDisplay = function() {
    const container = document.getElementById('selectedItemsContainer');
    if (!container) return;
    
    if (selectedItems.length === 0) {
        container.innerHTML = `
            <div class="alert alert-light text-center py-4">
                <i class="fas fa-shopping-cart fa-2x text-muted mb-3"></i>
                <p class="mb-0">No items added yet. Click on a product box above to add items.</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="row g-3">';
    
    selectedItems.forEach((item, index) => {
        const deptNames = { 'iprint': 'iPrint', 'consol': 'Consol', 'cinco': 'Cinco', 'class': 'Class', 'mto': 'Made to Order', 'other': 'Other' };
        const deptColors = { 'iprint': 'primary', 'consol': 'info', 'cinco': 'warning', 'class': 'success', 'mto': 'danger', 'other': 'secondary' };
        
        if (item.productType === 'garment' && item.subItems) {
            // Garment card with sub-items breakdown
            html += `
            <div class="col-md-6 col-lg-4">
                <div class="selected-item-card card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">${item.name}</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <div class="mb-2">
                            <span class="badge bg-${deptColors[item.department]} item-department-badge">
                                ${deptNames[item.department]}
                            </span>
                            <span class="badge bg-light text-dark item-department-badge ms-1">
                                garment
                            </span>
                        </div>
                        
                        <div class="mb-2 small">` +
                            item.subItems.map(function(si) {
                                return '<div class="d-flex justify-content-between py-1 border-bottom">' +
                                    '<span>' + si.shortName + ' <span class="text-muted">x' + si.quantity + '</span></span>' +
                                    '<span>\u20B1' + si.totalPrice.toFixed(2) + '</span>' +
                                '</div>';
                            }).join('') +
                        `</div>
                        
                        <div class="d-flex justify-content-between mb-1 fw-bold">
                            <span>Products Total:</span>
                            <span>\u20B1${item.totalProductPrice.toFixed(2)}</span>
                        </div>` +
                        (item.printing ? `
                        <hr class="my-2">
                        <div class="small">
                            <div class="d-flex justify-content-between">
                                <span>Print (${item.printing.printType}):</span>
                                <span>${item.printing.printSizes.join(', ')}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Print Qty:</span>
                                <span>${item.printing.printQty}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Print Cost/ea:</span>
                                <span>\u20B1${item.printing.printCostPerItem.toFixed(2)}</span>
                            </div>` +
                            (item.printing.isSpecialPrice ? `<div class="d-flex justify-content-between text-warning fw-bold">
                                <span>💵 SPECIAL PRICE</span>
                                <span>\u20B1${item.printing.specialTotal.toFixed(2)}</span>
                            </div>
                            <div class="text-muted small mb-1">
                                <em>${item.printing.specialReason || 'No reason given'}</em>
                            </div>` : '') +
                            (!item.printing.isSpecialPrice && item.printing.comboDiscount ? `<div class="d-flex justify-content-between text-success">
                                <span>Combo Discount:</span>
                                <span>-\u20B1${item.printing.comboDiscount.toFixed(2)}</span>
                            </div>` : '') +
                            (!item.printing.isSpecialPrice && item.printing.bulkDiscount ? `<div class="d-flex justify-content-between text-success">
                                <span>Bulk Discount:</span>
                                <span>-\u20B1${item.printing.bulkDiscount.toFixed(2)}</span>
                            </div>` : '') +
                            `<div class="d-flex justify-content-between fw-bold">
                                <span>Print Total:</span>
                                <span>\u20B1${item.printing.printSubtotal.toFixed(2)}</span>
                            </div>
                        </div>` : '') +
                        (item.notes ? `<hr class="my-2"><p class="mb-0 small text-muted"><i class="fas fa-sticky-note me-1"></i> ${item.notes}</p>` : '') +
                        `<hr><div class="d-flex justify-content-between fw-bold">
                            <span>Grand Total:</span>
                            <span>\u20B1${(item.totalProductPrice + (item.printing ? item.printing.printSubtotal : 0)).toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            </div>`;
        } else {
            // Non-garment: standard display
            html += `
            <div class="col-md-6 col-lg-4">
                <div class="selected-item-card card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">${item.name}</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <div class="mb-2">
                            <span class="badge bg-${deptColors[item.department]} item-department-badge">
                                ${deptNames[item.department]}
                            </span>
                            <span class="badge bg-light text-dark item-department-badge ms-1">
                                ${item.productType}
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Quantity:</span>
                            <span>${item.quantity}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Unit Price:</span>
                            <span>\u20B1${item.unitPrice.toFixed(2)}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total:</span>
                            <span class="fw-bold">\u20B1${item.totalPrice.toFixed(2)}</span>
                        </div>
                        
                        ${item.notes ? `<p class="mb-0 small text-muted"><i class="fas fa-sticky-note me-1"></i> ${item.notes}</p>` : ''}
                    </div>
                </div>
            </div>
        `;
        }
    });
    
    html += '</div>';
    container.innerHTML = html;
    updateOrderSummary();
};

// Remove item - GLOBAL
window.removeItem = function(index) {
    if (confirm('Remove this item from cart?')) {
        selectedItems.splice(index, 1);
        updateSelectedItemsDisplay();
        updateOrderSummary();
        showToast('Item removed from cart', 'warning');
    }
};

// Update order summary - GLOBAL
window.__updateOrderSummaryCore = function() {
    let subtotal = 0;
    
    selectedItems.forEach(item => {
        var itemTotal = item.totalPrice || 0;
        // Include printing cost for garment items
        if (item.productType === 'garment' && item.printing) {
            itemTotal = (item.totalProductPrice || 0) + (item.printing.printSubtotal || 0);
        }
        subtotal += itemTotal;
    });
    
    const total = subtotal;
    
    // Update hidden inputs
    const subtotalInput = document.getElementById('subtotal');
    const taxInput = document.getElementById('tax');
    const totalInput = document.getElementById('total_amount');
    
    if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);
    if (taxInput) taxInput.value = '0.00';
    if (totalInput) totalInput.value = total.toFixed(2);
    
    // Update department breakdown

    
    // Update deposit max
    const depositInput = document.getElementById('deposit_paid');
    if (depositInput) {
        depositInput.max = total;
        if (parseFloat(depositInput.value) > total) {
            depositInput.value = total;
        }
    }
    
    // Update Payment Summary
    updatePaymentSummary();
};

window.updatePaymentSummary = function() {
    var container = document.getElementById('paymentItemsSummary');
    var totalItemsEl = document.getElementById('paymentTotalItems');
    var totalQtyEl = document.getElementById('paymentTotalQty');
    var grandTotalEl = document.getElementById('paymentGrandTotal');
    if (!container) return;
    
    var totalItems = selectedItems.length;
    var totalQty = 0;
    var grandTotal = 0;
    var html = '';
    
    selectedItems.forEach(function(item, idx) {
        var deptColors = {'Class Apparel':'primary','Consol Printing':'info','Cinco':'success','MTO':'warning','Other':'secondary'};
        var deptNames = {'Class Apparel':'Class Apparel','Consol Printing':'Consol','Cinco':'Cinco','MTO':'MTO','Other':'Other'};
        
        // Count qty and compute total
        var itemQty = 0;
        var itemTotal = item.totalPrice || 0;
        
        if (item.productType === 'garment' && item.subItems) {
            itemQty = item.totalQty || 0;
            itemTotal = (item.totalProductPrice || 0) + (item.printing ? item.printing.printSubtotal || 0 : 0);
        } else {
            itemQty = item.qty || 1;
            itemTotal = item.totalPrice || 0;
            // For generic items with qty * price
            if (item.qty && item.unitPrice) {
                itemTotal = item.qty * item.unitPrice;
            }
        }
        
        totalQty += itemQty;
        grandTotal += itemTotal;
        
        // Build summary row
        html += '<div class="d-flex justify-content-between align-items-center mb-1 payment-item-row">';
        html += '    <div class="text-truncate me-2"><small>' + (item.name || 'Item ' + (idx+1)) + '</small></div>';
        html += '    <div class="text-end"><small>x' + itemQty + ' <strong>₱' + itemTotal.toFixed(2) + '</strong></small></div>';
        html += '</div>';
    });
    
    if (!html) {
        html = '<p class="text-muted mb-0">Add items to cart to see payment summary</p>';
    }
    
    container.innerHTML = html;
    if (totalItemsEl) totalItemsEl.textContent = totalItems;
    if (totalQtyEl) totalQtyEl.textContent = totalQty;
    if (grandTotalEl) grandTotalEl.textContent = '₱' + grandTotal.toFixed(2);
    
    // Update payment type suggestions
    updatePaymentSuggestions(grandTotal);
};

window.updatePaymentSuggestions = function(grandTotal) {
    var downSuggest = document.getElementById('pt_down_suggest');
    var fullSuggest = document.getElementById('pt_full_suggest');
    
    if (downSuggest) downSuggest.textContent = 'Suggested: ₱' + (grandTotal * 0.5).toFixed(2) + ' (50%)';
    if (fullSuggest) fullSuggest.textContent = '₱' + grandTotal.toFixed(2);
    
    // If down payment is selected, update suggested amount
    var downRadio = document.getElementById('pt_down');
    if (downRadio && downRadio.checked) {
        var amtInput = document.getElementById('payment_amount');
        if (amtInput) {
            amtInput.value = (grandTotal * 0.5).toFixed(2);
            document.getElementById('paymentAmountHint').textContent = 'Suggested 50% down payment';
        }
    }
    if (downRadio && document.getElementById('pt_full') && document.getElementById('pt_full').checked) {
        var amtInput = document.getElementById('payment_amount');
        if (amtInput) amtInput.value = grandTotal.toFixed(2);
    }
};

// Payment type selection handlers
document.addEventListener('DOMContentLoaded', function() {
    // Auto-detect payment method from Payment Received By dropdown
    var paymentAccountSelect = document.getElementById('payment_account_id');
    var paymentMethodInput = document.getElementById('payment_method_input');
    if (paymentAccountSelect && paymentMethodInput) {
        paymentAccountSelect.addEventListener('change', function() {
            var selectedText = this.options[this.selectedIndex]?.text?.toLowerCase() || '';
            if (selectedText.includes('cash')) {
                paymentMethodInput.value = 'cash';
            } else {
                paymentMethodInput.value = 'online';
            }
        });
    }
    
    // Listen for payment type radio changes
    document.querySelectorAll('input[name="payment_type"]').forEach(function(radio) {
        radio.addEventListener('change', function(e) {
            var grandTotal = parseFloat(document.getElementById('paymentGrandTotal').textContent.replace(/[₱,]/g, '')) || 0;
            var amtInput = document.getElementById('payment_amount');
            var amountSection = document.getElementById('amountInputSection');
            var poSection = document.getElementById('poSection');
            var poRef = document.getElementById('po_reference');
            var hiddenType = document.getElementById('payment_type_hidden');
            var hiddenAmt = document.getElementById('deposit_paid_hidden');
            
            if (this.value === 'downpayment') {
                amtInput.disabled = false;
                amtInput.value = (grandTotal * 0.5).toFixed(2);
                document.getElementById('paymentAmountHint').textContent = 'Suggested 50% down payment. You can change this amount.';
                amountSection.style.display = 'block';
                poSection.style.display = 'none';
                if (poRef) poRef.disabled = true;
                if (hiddenType) hiddenType.value = 'downpayment';
            } else if (this.value === 'fullpayment') {
                amtInput.disabled = false;
                amtInput.value = grandTotal.toFixed(2);
                document.getElementById('paymentAmountHint').textContent = 'Full payment amount';
                amountSection.style.display = 'block';
                poSection.style.display = 'none';
                if (poRef) poRef.disabled = true;
                if (hiddenType) hiddenType.value = 'fullpayment';
            } else if (this.value === 'po') {
                amtInput.disabled = true;
                amtInput.value = 0;
                document.getElementById('paymentAmountHint').textContent = 'Purchase Order — no payment required';
                amountSection.style.display = 'block';
                poSection.style.display = 'block';
                if (poRef) poRef.disabled = false;
                if (hiddenType) hiddenType.value = 'po';
            }
            if (hiddenAmt) hiddenAmt.value = amtInput.value || 0;
        });
    });
    
    // Update hidden amount when user types
    var amtInput = document.getElementById('payment_amount');
    if (amtInput) {
        amtInput.addEventListener('input', function() {
            var hiddenAmt = document.getElementById('deposit_paid_hidden');
            if (hiddenAmt) hiddenAmt.value = this.value || 0;
        });
    }
    
    // Upload area click handler
    var uploadArea = document.getElementById('uploadArea');
    var fileInput = document.getElementById('payment_screenshot');
    if (uploadArea && fileInput) {
        uploadArea.addEventListener('click', function() {
            fileInput.click();
        });
        fileInput.addEventListener('change', function() {
            var fileNameEl = document.getElementById('fileName');
            if (fileNameEl && this.files.length > 0) {
                fileNameEl.textContent = '✅ ' + this.files[0].name;
            }
            showPaymentPreview(this);
            var ua = document.getElementById('uploadArea');
            if (ua) ua.classList.add('has-screenshot');
        });
        
        // Paste handler for payment screenshot
        document.addEventListener('paste', function(e) {
            var items = e.clipboardData && e.clipboardData.items;
            if (!items) return;
            for (var i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') === 0) {
                    var blob = items[i].getAsFile();
                    if (blob) {
                        // Convert to File and set it
                        var fileName = 'pasted-payment-' + Date.now() + '.png';
                        var pastedFile = new File([blob], fileName, { type: blob.type });
                        
                        // Create a DataTransfer to set the file input
                        var dt = new DataTransfer();
                        dt.items.add(pastedFile);
                        var fileInput = document.getElementById('payment_screenshot');
                        if (fileInput) {
                            fileInput.files = dt.files;
                            // Trigger change event
                            var event = new Event('change', { bubbles: true });
                            fileInput.dispatchEvent(event);
                        }
                        
                        // Show preview on upload area
                        var uploadArea = document.getElementById('uploadArea');
                        var fileNameEl = document.getElementById('fileName');
                        if (fileNameEl) {
                            fileNameEl.textContent = '✅ Pasted image (' + fileName + ')';
                        }
                        if (uploadArea) {
                            uploadArea.style.backgroundImage = '';
                            uploadArea.style.backgroundSize = 'cover';
                            uploadArea.style.backgroundPosition = 'center';
                        }
                        
                        showPaymentPreviewFile(pastedFile);
                        var ua = document.getElementById('uploadArea');
                        if (ua) ua.classList.add('has-screenshot');
                        
                        showToast('Payment screenshot pasted!', 'success');
                        break;
                    }
                }
            }
        });
    }
});

// Update department breakdown - GLOBAL
// Department Breakdown function removed (visual panel removed per user request)

// Payment screenshot preview helpers
function showPaymentPreview(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('paymentPreview');
            var previewImg = document.getElementById('paymentPreviewImg');
            if (preview && previewImg) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
                previewImg.classList.remove('border-danger');
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function showPaymentPreviewFile(file) {
    var reader = new FileReader();
    reader.onload = function(e) {
        var preview = document.getElementById('paymentPreview');
        var previewImg = document.getElementById('paymentPreviewImg');
        if (preview && previewImg) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
            previewImg.classList.remove('border-danger');
        }
    }
    reader.readAsDataURL(file);
}

// Remove payment screenshot
document.getElementById('removePaymentScreenshot')?.addEventListener('click', function() {
    var preview = document.getElementById('paymentPreview');
    var previewImg = document.getElementById('paymentPreviewImg');
    var fileNameEl = document.getElementById('fileName');
    var fileInput = document.getElementById('payment_screenshot');
    var uploadArea = document.getElementById('uploadArea');
    if (preview) preview.style.display = 'none';
    if (previewImg) previewImg.src = '';
    if (fileNameEl) fileNameEl.textContent = '';
    if (fileInput) fileInput.value = '';
    if (uploadArea) {
        uploadArea.style.backgroundImage = '';
        uploadArea.classList.remove('has-screenshot');
    }
});

// Show toast - GLOBAL
window.showToast = function(message, type) {
    if (type === undefined) type = 'info';
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    const toastId = 'toast-' + Date.now();
    const typeClasses = {
        'success': 'bg-success text-white',
        'warning': 'bg-warning text-dark',
        'error': 'bg-danger text-white',
        'info': 'bg-info text-white'
    };
    
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast ${typeClasses[type] || typeClasses['info']} border-0`;
    toast.style.minWidth = '300px';
    toast.style.marginBottom = '10px';
    
    toast.innerHTML = `
        <div class="toast-body">
            <div class="d-flex justify-content-between align-items-center">
                <span>${message}</span>
                <button type="button" class="btn-close btn-close-white" onclick="document.getElementById('${toastId}').remove()"></button>
            </div>
        </div>
    `;
    
    container.appendChild(toast);
    setTimeout(() => {
        if (document.getElementById(toastId)) {
            document.getElementById(toastId).remove();
        }
    }, 3000);
};

// ============================================
// INITIALIZE EVENT LISTENERS WHEN DOM IS READY
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing product system...');
    
    // Initialize product event listeners
    const qtyInput = document.getElementById('productQuantity');
    const priceInput = document.getElementById('productPrice');
    const addBtn = document.getElementById('addItemBtn');
    
    if (qtyInput) {
        qtyInput.addEventListener('input', calculateItemTotal);
    }
    
    if (priceInput) {
        priceInput.addEventListener('input', calculateItemTotal);
    }
    
    if (addBtn) {
        addBtn.addEventListener('click', addItemToCart);
    }
    
    // Garment add to cart button
    const garmentAddBtn = document.getElementById('garment_addItemBtn');
    if (garmentAddBtn) {
        garmentAddBtn.addEventListener('click', garment_addItemToCart);
    }
    
    // Initialize garment product row functions
    const productRowsContainer = document.getElementById('productRowsContainer');
    if (productRowsContainer) {
        // Add product row button
        const addRowBtn = document.getElementById('addProductRow');
        if (addRowBtn) {
            addRowBtn.addEventListener('click', function() {
                var container = document.getElementById('productRowsContainer');
                var template = container.querySelector('.product-row-template');
                if (!template) return;
                var newRow = template.cloneNode(true);
                newRow.classList.remove('product-row-template', 'd-none');
                newRow.classList.add('product-row');
                var select = newRow.querySelector('.product-select');
                var qty = newRow.querySelector('.product-quantity');
                if (select) {
                    select.innerHTML = '<option value="">Select a product</option>';
                }
                if (qty) qty.value = '1';
                var removeBtn = newRow.querySelector('.remove-row');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        newRow.remove();
                        garment_updatePrintSummary();
                    });
                }
                container.appendChild(newRow);
                garment_updatePrintSummary();
            });
        }
        // Product row remove buttons (existing)
        productRowsContainer.querySelectorAll('.remove-row').forEach(function(btn) {
            btn.addEventListener('click', function(ev) {
                var row = this.closest('.product-row');
                if (row) {
                    row.remove();
                    garment_updatePrintSummary();
                }
            });
        });
    }
    
    // Initialize existing customer system (keep existing code)
    let currentCustomerId = null;
    
    const customerPhone = document.getElementById('customer_phone');
    if (customerPhone) {
        customerPhone.addEventListener('blur', function() {
            const phone = this.value.trim();
            if (phone.length >= 10) {
                checkExistingCustomer(phone);
            }
        });
    }
    
    // Customer search
    const searchBtn = document.getElementById('searchCustomerBtn');
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            const searchTerm = document.getElementById('customerSearch').value.trim();
            if (searchTerm.length >= 3) {
                searchCustomers(searchTerm);
            } else {
                alert('Please enter at least 3 characters to search');
            }
        });
    }
    
    // Use existing customer
    const useExistingBtn = document.getElementById('useExistingCustomer');
    if (useExistingBtn) {
        useExistingBtn.addEventListener('click', function() {
            document.getElementById('customerForm').style.display = 'none';
            document.getElementById('existingCustomerSection').style.display = 'block';
        });
    }
    
    // Create new customer
    const createNewBtn = document.getElementById('createNewCustomer');
    if (createNewBtn) {
        createNewBtn.addEventListener('click', function() {
            document.getElementById('existingCustomerSection').style.display = 'none';
            document.getElementById('customerForm').style.display = 'block';
        });
    }
    
    // Save customer (removed — button no longer exists)
    
    // Form submission
    const form = document.getElementById('prototypeSaleForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validate items
            if (selectedItems.length === 0) {
                e.preventDefault();
                alert('Please add at least one item to the cart');
                return;
            }
            
            // Validate payment screenshot
            var psInput = document.getElementById('payment_screenshot');
            if (!psInput || !psInput.files || psInput.files.length === 0) {
                e.preventDefault();
                var preview = document.getElementById('paymentPreview');
                var previewImg = document.getElementById('paymentPreviewImg');
                if (previewImg) previewImg.classList.add('border-danger');
                if (preview) preview.style.display = 'block';
                showToast('Please upload or paste a payment screenshot', 'error');
                document.getElementById('uploadArea')?.scrollIntoView({behavior: 'smooth', block: 'center'});
                return;
            }
            
            // Convert items to JSON
            const itemsJsonInput = document.getElementById('items_json');
            if (itemsJsonInput) {
                itemsJsonInput.value = JSON.stringify(selectedItems);
            }
            
            // Determine primary department based on selected items
            const deptTotals = {};
            selectedItems.forEach(item => {
                if (!deptTotals[item.department]) deptTotals[item.department] = 0;
                deptTotals[item.department] += item.totalPrice;
            });
            
            let primaryDept = '';
            let maxTotal = 0;
            for (const [dept, total] of Object.entries(deptTotals)) {
                if (total > maxTotal) {
                    maxTotal = total;
                    primaryDept = dept;
                }
            }
            
            const autoDeptInput = document.getElementById('auto_department_id');
            if (autoDeptInput) {
                autoDeptInput.value = primaryDept || 'iprint';
            }
            
            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Creating Sale...';
                submitBtn.disabled = true;
            }
        });
    }
    
    // Initial calculation
    calculateItemTotal();
    
    console.log('Product system initialized successfully');
});
    
    // Customer search
    document.getElementById('searchCustomerBtn').addEventListener('click', function() {
        const searchTerm = document.getElementById('customerSearch').value.trim();
        if (searchTerm.length >= 3) {
            searchCustomers(searchTerm);
        } else {
            alert('Please enter at least 3 characters to search');
        }
    });
    
    // Use existing customer
    document.getElementById('useExistingCustomer').addEventListener('click', function() {
        if (currentCustomerId) {
            loadCustomerDetails(currentCustomerId);
        }
    });
    
    // Create new customer
    document.getElementById('createNewCustomer').addEventListener('click', function() {
        document.getElementById('existingCustomerAlert').style.display = 'none';
        document.getElementById('customerLTVCard').style.display = 'none';
        clearCustomerForm();
        document.getElementById('customer_phone').focus();
    });
    
    // Save customer button (removed — button no longer exists)
    
    // Check for existing customer
    function checkExistingCustomer(phone) {
        fetch(`/api/customers/check?phone=${encodeURIComponent(phone)}`)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    showExistingCustomerAlert(data.customer);
                    currentCustomerId = data.customer.id;
                } else {
                    currentCustomerId = null;
                    document.getElementById('existingCustomerAlert').style.display = 'none';
                    document.getElementById('customerLTVCard').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error checking customer:', error);
            });
    }
    
    // Search customers
    function searchCustomers(searchTerm) {
        fetch(`/api/customers/search?q=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => {
                const resultsDiv = document.getElementById('customerSearchResults');
                if (data.customers.length > 0) {
                    let html = '<div class="list-group">';
                    data.customers.forEach(customer => {
                        html += `
                        <a href="#" class="list-group-item list-group-item-action" data-customer-id="${customer.id}">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${customer.name} ${customer.customer_tier === 'gold' ? '🥇' : customer.customer_tier === 'silver' ? '🥈' : customer.customer_tier === 'platinum' ? '💎' : '🥉'}</h6>
                                <small>${customer.total_orders} orders</small>
                            </div>
                            <p class="mb-1">${customer.phone} | ${customer.email || 'No email'}</p>
                            <small>Total spent: ₱${parseFloat(customer.total_spent).toLocaleString('en-PH')}</small>
                        </a>`;
                    });
                    html += '</div>';
                    resultsDiv.innerHTML = html;
                    resultsDiv.style.display = 'block';
                    
                    // Add click handlers
                    document.querySelectorAll('[data-customer-id]').forEach(item => {
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            const customerId = this.getAttribute('data-customer-id');
                            loadCustomerDetails(customerId);
                            resultsDiv.style.display = 'none';
                            document.getElementById('customerSearch').value = '';
                        });
                    });
                } else {
                    resultsDiv.innerHTML = '<div class="alert alert-warning">No customers found</div>';
                    resultsDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error searching customers:', error);
            });
    }
    
    // Show existing customer alert
    function showExistingCustomerAlert(customer) {
        document.getElementById('existingCustomerName').textContent = customer.name;
        document.getElementById('existingCustomerDetails').innerHTML = `
            Phone: ${customer.phone} | 
            ${customer.total_orders} orders | 
            Total: ₱${parseFloat(customer.total_spent).toLocaleString('en-PH')}
        `;
        document.getElementById('existingCustomerAlert').style.display = 'block';
        
        // Show LTV card
        showCustomerLTV(customer);
    }
    
    // Load customer details into form
    function loadCustomerDetails(customerId) {
        fetch(`/api/customers/${customerId}`)
            .then(response => response.json())
            .then(customer => {
                // Fill form fields
                document.getElementById('customer_name').value = customer.name;
                document.getElementById('customer_phone').value = customer.phone.replace('+63', '');
                document.getElementById('customer_email').value = customer.email || '';
                document.getElementById('marketplace').value = customer.marketplace || '';
                document.getElementById('customer_address').value = customer.location || '';
                document.getElementById('customer_company').value = customer.company || '';
                
                // Hide alert and show LTV
                document.getElementById('existingCustomerAlert').style.display = 'none';
                showCustomerLTV(customer);
                currentCustomerId = customer.id;
            })
            .catch(error => {
                console.error('Error loading customer:', error);
            });
    }
    
    // Show customer LTV
    function showCustomerLTV(customer) {
        const ltvDiv = document.getElementById('customerLTVDetails');
        const tierIcon = customer.customer_tier === 'gold' ? '🥇' : 
                        customer.customer_tier === 'silver' ? '🥈' : 
                        customer.customer_tier === 'platinum' ? '💎' : '🥉';
        
        ltvDiv.innerHTML = `
            <div class="col-md-3 text-center">
                <div class="display-6">${tierIcon}</div>
                <div class="fw-bold">${customer.customer_tier.toUpperCase()}</div>
                <div class="small text-muted">Customer Tier</div>
            </div>
            <div class="col-md-3 text-center">
                <div class="display-6 fw-bold text-primary">${customer.total_orders}</div>
                <div class="fw-bold">Total Orders</div>
                <div class="small text-muted">Since ${new Date(customer.first_order_date).toLocaleDateString('en-PH')}</div>
            </div>
            <div class="col-md-3 text-center">
                <div class="display-6 fw-bold text-success">₱${parseFloat(customer.total_spent).toLocaleString('en-PH')}</div>
                <div class="fw-bold">Lifetime Value</div>
                <div class="small text-muted">Avg: ₱${parseFloat(customer.average_order_value).toLocaleString('en-PH')}</div>
            </div>
            <div class="col-md-3 text-center">
                <div class="display-6 fw-bold text-info">${customer.getDaysSinceLastOrder || 'N/A'}</div>
                <div class="fw-bold">Days Since Last</div>
                <div class="small text-muted">Last: ${new Date(customer.last_order_date).toLocaleDateString('en-PH')}</div>
            </div>
        `;
        
        document.getElementById('customerLTVCard').style.display = 'block';
    }
    
    // Save customer to database
    function saveCustomer() {
        const customerData = {
            name: document.getElementById('customer_name').value.trim(),
            phone: document.getElementById('customer_phone').value.trim(),
            email: document.getElementById('customer_email').value.trim(),
            marketplace: document.getElementById('marketplace').value,
            location: document.getElementById('customer_address').value.trim(),
            company: document.getElementById('customer_company').value.trim(),
            notes: document.getElementById('customer_notes').value.trim(),
            _token: '{{ csrf_token() }}'
        };
        
        // Validation
        if (!customerData.name || !customerData.phone) {
            alert('Please fill in at least Customer Name and Phone Number');
            return;
        }
        
        // Format phone number
        if (!customerData.phone.startsWith('+63')) {
            customerData.phone = '+63' + customerData.phone.replace(/^0/, '');
        }
        
        // Show loading
        const saveBtn = document.getElementById('saveCustomerBtn');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Saving...';
        saveBtn.disabled = true;
        
        // Send to server
        fetch('/api/customers/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(customerData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Set customer ID
                document.getElementById('customer_id').value = data.customer.id;
                currentCustomerId = data.customer.id;
                
                // Show success message
                alert(`✅ Customer saved successfully!\nID: ${data.customer.id}\nYou can now continue to next steps.`);
                
                // Enable next steps
                enableNextSteps();
                
                // Show customer LTV if it's a repeat customer
                if (data.customer.total_orders > 0) {
                    showCustomerLTV(data.customer);
                }
            } else {
                alert('❌ Error saving customer: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Network error saving customer');
        })
        .finally(() => {
            // Restore button
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    }
    
    // Enable next steps after customer is saved
    function enableNextSteps() {
        // Enable department selection
        document.querySelectorAll('.department-card').forEach(card => {
            card.style.opacity = '1';
            card.style.pointerEvents = 'auto';
        });
        
        // Scroll to next section
        document.querySelector('.form-section:nth-child(2)').scrollIntoView({ behavior: 'smooth' });
        
        // Show success indicator
        const customerSection = document.querySelector('.form-section:nth-child(1)');
        customerSection.classList.add('completed');
        
        // Button state handled automatically (save button removed from UI)
    }
    
    // Clear customer form
    function clearCustomerForm() {
        document.getElementById('customer_name').value = '';
        document.getElementById('customer_phone').value = '';
        document.getElementById('customer_email').value = '';
        document.getElementById('marketplace').value = '';
        document.getElementById('customer_address').value = '';
        document.getElementById('customer_company').value = '';
        document.getElementById('customer_notes').value = '';
        document.getElementById('customer_id').value = '';
        currentCustomerId = null;
    }
    
    // Department selection
    document.querySelectorAll('.department-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.department-card').forEach(function(c) {
                c.classList.remove('selected');
            });
            this.classList.add('selected');
            
            const deptName = this.querySelector('h5').textContent;
            document.getElementById('department_name').value = deptName;
        });
    });
    
    // Payment method selection
    document.querySelectorAll('.payment-method-card').forEach(function(card) {
        card.addEventListener('click', function() {
            document.querySelectorAll('.payment-method-card').forEach(function(c) {
                c.classList.remove('selected');
            });
            this.classList.add('selected');
        });
    });
    

    
    // ======================
    // FILTER SYSTEM FUNCTIONS
    // ======================
    
    // Load filter options when modal opens
    window.loadFilterOptions = function(productType) {
        // Fetch unique filter values from API
        fetch(`/api/filter-options/${productType}`)
            .then(response => response.json())
            .then(data => {
                // Populate Brand datalist
                const brandDatalist = document.getElementById('brandOptions');
                if (brandDatalist && data.brands) {
                    brandDatalist.innerHTML = '';
                    data.brands.forEach(brand => {
                        const opt = document.createElement('option');
                        opt.value = brand;
                        brandDatalist.appendChild(opt);
                    });
                }
                
                // Populate Type datalist
                const typeDatalist = document.getElementById('typeOptions');
                if (typeDatalist && data.types) {
                    typeDatalist.innerHTML = '';
                    data.types.forEach(type => {
                        const opt = document.createElement('option');
                        opt.value = type;
                        typeDatalist.appendChild(opt);
                    });
                }
                
                // Populate Color datalist
                const colorDatalist = document.getElementById('colorOptions');
                if (colorDatalist && data.colors) {
                    colorDatalist.innerHTML = '';
                    data.colors.forEach(color => {
                        const opt = document.createElement('option');
                        opt.value = color;
                        colorDatalist.appendChild(opt);
                    });
                }
                
                // Add keyboard shortcuts (Enter to apply filters)
                document.getElementById('filterBrand').addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        applyFilters();
                    }
                });
                
                document.getElementById('filterType').addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        applyFilters();
                    }
                });
                
                document.getElementById('filterColor').addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        applyFilters();
                    }
                });
            })
            .catch(error => {
                console.error('Error loading filter options:', error);
            });
    };
    
    // Apply filters and reload products (UPDATED FOR MULTIPLE DROPDOWNS)
    window.applyFilters = function() {
        const brand = document.getElementById('filterBrand')?.value.trim() || '';
        const type = document.getElementById('filterType')?.value.trim() || '';
        const color = document.getElementById('filterColor')?.value.trim() || '';
        
        // Show loading indicator on ALL dropdowns
        const allDropdowns = document.querySelectorAll('.product-select');
        allDropdowns.forEach(dropdown => {
            dropdown.innerHTML = '<option value="">Applying filters...</option>';
        });
        
        // Reload products with filters for ALL dropdowns
        loadProductsWithFilters(currentProductType, brand, type, color);
    };
    
    // Reset all filters (UPDATED FOR MULTIPLE DROPDOWNS)
    window.resetFilters = function() {
        document.getElementById('filterBrand').value = '';
        document.getElementById('filterType').value = '';
        document.getElementById('filterColor').value = '';
        
        // Show loading indicator in ALL dropdowns
        const allDropdowns = document.querySelectorAll('.product-select');
        allDropdowns.forEach(dropdown => {
            dropdown.innerHTML = '<option value="">Resetting filters...</option>';
        });
        
        // Reload products without filters for ALL dropdowns
        loadProductsFromDatabase(currentProductType);
    };
    
    // Load products with filter parameters (UPDATED FOR MULTIPLE DROPDOWNS)
    window.loadProductsWithFilters = function(productType, brand, type, color) {
        // Get ALL product dropdowns
        const allDropdowns = document.querySelectorAll('.product-select');
        if (allDropdowns.length === 0) return;
        
        // Show loading in ALL dropdowns
        allDropdowns.forEach(dropdown => {
            dropdown.innerHTML = '<option value="">Loading filtered products...</option>';
        });
        
        // Build query parameters
        const params = new URLSearchParams();
        if (brand) params.append('brand', brand);
        if (type) params.append('type', type);
        if (color) params.append('color', color);
        
        // Fetch filtered products
        fetch(`/api/products-for-box/${productType}?${params.toString()}`)
            .then(response => response.json())
            .then(products => {
                // Update ALL dropdowns
                allDropdowns.forEach(dropdown => {
                    dropdown.innerHTML = '<option value="">Select product</option>';
                    
                    if (products.length === 0) {
                        dropdown.innerHTML = '<option value="">No products match your filters. Try different filters.</option>';
                        return;
                    }
                    
                    products.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.id;
                        
                        // Extract size from description if size column is null
                        let productSize = p.size;
                        if (!productSize && p.description) {
                            // Try to extract size from description
                            const sizeMatch = p.description.match(/Size:\s*([^,]+)/i);
                            if (sizeMatch) {
                                productSize = sizeMatch[1].trim();
                            }
                        }
                        
                        // Build display text with NEW FORMAT: Size: M RED BLACKHORSE ROUNDNECK
                        let displayText = '';
                        
                        if (productSize) {
                            displayText += `Size: ${productSize}`;
                        }
                        
                        if (p.color) {
                            displayText += ` ${p.color}`;
                        }
                        
                        if (p.brand) {
                            displayText += ` ${p.brand}`;
                        }
                        
                        // Try to extract shirt type from description
                        let shirtType = p.shirt_type;
                        if (!shirtType && p.description) {
                            const typeMatch = p.description.match(/Type:\s*([^,]+)/i);
                            if (typeMatch) {
                                shirtType = typeMatch[1].trim();
                            }
                        }
                        
                        if (shirtType) {
                            displayText += ` ${shirtType}`;
                        }
                        
                        // Add price at the end
                        displayText += ` - ₱${parseFloat(p.base_price).toFixed(2)}`;
                        
                        opt.textContent = displayText;
                        opt.dataset.price = p.base_price;
                        opt.dataset.productName = p.name;
                        opt.dataset.brand = p.brand || '';
                        opt.dataset.size = productSize || '';
                        opt.dataset.color = p.color || '';
                        opt.dataset.volumeDiscounts = JSON.stringify(p.volume_discounts);
                        opt.dataset.productData = JSON.stringify(p);
                        dropdown.appendChild(opt);
                    });
                });
                
                // Update order summary
                updateOrderSummary();
            })
            .catch(error => {
                console.error('Error loading filtered products:', error);
                allDropdowns.forEach(dropdown => {
                    dropdown.innerHTML = '<option value="">Error loading products. Please try again.</option>';
                });
            });
    };
    
    // Garment-specific filter functions
    window.garment_loadFilterOptions = function(productType) {
        fetch('/api/filter-options/' + productType)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                ['brand', 'type', 'color'].forEach(function(type) {
                    var datalist = document.getElementById('garment_' + type + 'Options');
                    var field = document.getElementById('garment_filter' + type.charAt(0).toUpperCase() + type.slice(1));
                    if (datalist) {
                        datalist.innerHTML = '';
                        var items = data[type + 's'] || data[type] || [];
                        if (items.length) {
                            items.forEach(function(item) {
                                var opt = document.createElement('option');
                                opt.value = item;
                                datalist.appendChild(opt);
                            });
                            if (field) field.placeholder = 'Type or select ' + type + '...';
                        }
                    }
                });
            })
            .catch(function(err) {
                console.error('Error loading garment filter options:', err);
            });
    };
    
    window.garment_applyFilters = function() {
        var brand = document.getElementById('garment_filterBrand')?.value.trim() || '';
        var type = document.getElementById('garment_filterType')?.value.trim() || '';
        var color = document.getElementById('garment_filterColor')?.value.trim() || '';
        
        var selects = document.querySelectorAll('#garment_productRowsContainer .product-select');
        selects.forEach(function(dropdown) {
            dropdown.innerHTML = '<option value="">Applying filters...</option>';
        });
        
        var params = new URLSearchParams();
        if (brand) params.set('brand', brand);
        if (type) params.set('type', type);
        if (color) params.set('color', color);
        
        fetch('/api/products-for-box/' + currentProductType + '?' + params.toString())
            .then(function(r) { return r.json(); })
            .then(function(products) {
                selects.forEach(function(dropdown) {
                    dropdown.innerHTML = '<option value="">Select product</option>';
                    products.forEach(function(p) {
                        var opt = document.createElement('option');
                        opt.value = p.id;
                        opt.textContent = window.garment_buildProductOption(p);
                        opt.dataset.price = p.base_price;
                        opt.dataset.brand = p.brand || '';
                        var pSize = p.size;
                        if (!pSize && p.description) {
                            var sizeM = p.description.match(/Size:\s*([^,]+)/i);
                            if (sizeM) pSize = sizeM[1].trim();
                        }
                        opt.dataset.size = pSize || '';
                        opt.dataset.color = p.color || '';
                        opt.dataset.productName = p.name || '';
                        opt.dataset.productData = JSON.stringify(p);
                        dropdown.appendChild(opt);
                    });
                    if (products.length === 0) {
                        dropdown.innerHTML = '<option value="">No products match your filters</option>';
                    }
                });
                garment_updatePrintSummary();
            })
            .catch(function() {
                selects.forEach(function(d) { d.innerHTML = '<option value="">Error loading</option>'; });
            });
    };
    
    window.garment_resetFilters = function() {
        document.getElementById('garment_filterBrand').value = '';
        document.getElementById('garment_filterType').value = '';
        document.getElementById('garment_filterColor').value = '';
        
        var selects = document.querySelectorAll('#garment_productRowsContainer .product-select');
        selects.forEach(function(dropdown) {
            dropdown.innerHTML = '<option value="">Resetting filters...</option>';
        });
        
        fetch('/api/products-for-box/' + currentProductType)
            .then(function(r) { return r.json(); })
            .then(function(products) {
                selects.forEach(function(dropdown) {
                    dropdown.innerHTML = '<option value="">Select product</option>';
                    products.forEach(function(p) {
                        var opt = document.createElement('option');
                        opt.value = p.id;
                        opt.textContent = window.garment_buildProductOption(p);
                        opt.dataset.price = p.base_price;
                        opt.dataset.brand = p.brand || '';
                        var pSize = p.size;
                        if (!pSize && p.description) {
                            var sizeM = p.description.match(/Size:\s*([^,]+)/i);
                            if (sizeM) pSize = sizeM[1].trim();
                        }
                        opt.dataset.size = pSize || '';
                        opt.dataset.color = p.color || '';
                        opt.dataset.productName = p.name || '';
                        opt.dataset.productData = JSON.stringify(p);
                        dropdown.appendChild(opt);
                    });
                    if (products.length === 0) {
                        dropdown.innerHTML = '<option value="">No products available</option>';
                    }
                });
                garment_updatePrintSummary();
            })
            .catch(function() {
                selects.forEach(function(d) { d.innerHTML = '<option value="">Error loading</option>'; });
            });
    };
    
    // Update openProductModal to load filter options (only for non-garment)
    const originalOpenProductModal = window.openProductModal;
    window.openProductModal = function(productType) {
        originalOpenProductModal(productType);
        
        // Only do non-garment setup for non-garment types
        if (productType !== 'garment' && productType !== 'cutting') {
            loadFilterOptions(productType);
            initializeProductRows();
        }
    };
    
    // ======================
    // MULTIPLE PRODUCT ROWS SYSTEM
    // ======================
    
    // Initialize product rows
    window.initializeProductRows = function() {
        // Clear existing rows (except first)
        const container = document.getElementById('productRowsContainer');
        const firstRow = container.querySelector('.product-row:first-child');
        if (firstRow) {
            container.innerHTML = '';
            container.appendChild(firstRow);
        }
        
        // Reset first row
        const firstSelect = container.querySelector('.product-select');
        const firstQty = container.querySelector('.product-quantity');
        if (firstSelect) firstSelect.innerHTML = '<option value="">Select a product</option>';
        if (firstQty) firstQty.value = '1';
        
        // Load products into first dropdown
        loadProductsIntoDropdown(firstSelect);
        
        // Add event listeners
        setupProductRowEvents();
        
        // Update summary
        updateOrderSummary();
    };
    
    // Load products into a specific dropdown
    window.loadProductsIntoDropdown = function(dropdownElement) {
        if (!dropdownElement || !currentProductType) return;
        
        dropdownElement.innerHTML = '<option value="">Loading products...</option>';
        
        fetch(`/api/products-for-box/${currentProductType}`)
            .then(response => response.json())
            .then(products => {
                dropdownElement.innerHTML = '<option value="">Select product</option>';
                
                if (products.length === 0) {
                    dropdownElement.innerHTML = '<option value="">No products found</option>';
                    return;
                }
                
                products.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    
                    // Extract size from description if size column is null
                    let productSize = p.size;
                    if (!productSize && p.description) {
                        // Try to extract size from description
                        const sizeMatch = p.description.match(/Size:\s*([^,]+)/i);
                        if (sizeMatch) {
                            productSize = sizeMatch[1].trim();
                        }
                    }
                    
                    // Build display text with NEW FORMAT: Size: M RED BLACKHORSE ROUNDNECK
                    let displayText = '';
                    
                    if (productSize) {
                        displayText += `Size: ${productSize}`;
                    }
                    
                    if (p.color) {
                        displayText += ` ${p.color}`;
                    }
                    
                    if (p.brand) {
                        displayText += ` ${p.brand}`;
                    }
                    
                    // Try to extract shirt type from description
                    let shirtType = p.shirt_type;
                    if (!shirtType && p.description) {
                        const typeMatch = p.description.match(/Type:\s*([^,]+)/i);
                        if (typeMatch) {
                            shirtType = typeMatch[1].trim();
                        }
                    }
                    
                    if (shirtType) {
                        displayText += ` ${shirtType}`;
                    }
                    
                    // Add price at the end
                    displayText += ` - ₱${parseFloat(p.base_price).toFixed(2)}`;
                    
                    opt.textContent = displayText;
                    opt.dataset.price = p.base_price;
                    opt.dataset.productName = p.name;
                    opt.dataset.brand = p.brand || '';
                    opt.dataset.size = productSize || '';
                    opt.dataset.color = p.color || '';
                    dropdownElement.appendChild(opt);
                });
            })
            .catch(error => {
                console.error('Error loading products:', error);
                dropdownElement.innerHTML = '<option value="">Error loading products</option>';
            });
    };
    
    // Setup event listeners for product rows
    window.setupProductRowEvents = function() {
        // Add row button
        const addBtn = document.getElementById('addProductRow');
        if (addBtn) {
            addBtn.addEventListener('click', addProductRow);
        }
        
        // Product selection change
        document.querySelectorAll('.product-select').forEach(select => {
            select.addEventListener('change', function() {
                updateProductPriceDisplay(this);
                updateOrderSummary();
            });
        });
        
        // Quantity change
        document.querySelectorAll('.product-quantity').forEach(input => {
            input.addEventListener('input', updateOrderSummary);
        });
        
        // Remove row buttons
        document.querySelectorAll('.remove-row').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('.product-row');
                if (row && document.querySelectorAll('.product-row').length > 1) {
                    row.remove();
                    updateOrderSummary();
                }
            });
        });
    };
    
    // Add new product row
    window.addProductRow = function() {
        const container = document.getElementById('productRowsContainer');
        const template = container.querySelector('.product-row-template');
        
        if (!template) return;
        
        // Clone template
        const newRow = template.cloneNode(true);
        newRow.classList.remove('d-none', 'product-row-template');
        newRow.classList.add('product-row');
        
        // Add to container
        container.appendChild(newRow);
        
        // Load products into new dropdown
        const newSelect = newRow.querySelector('.product-select');
        if (newSelect) {
            loadProductsIntoDropdown(newSelect);
            
            // Setup events for new row
            newSelect.addEventListener('change', function() {
                updateProductPriceDisplay(this);
                updateOrderSummary();
            });
        }
        
        const newQty = newRow.querySelector('.product-quantity');
        if (newQty) {
            newQty.addEventListener('input', updateOrderSummary);
        }
        
        const removeBtn = newRow.querySelector('.remove-row');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                const row = this.closest('.product-row');
                if (row && document.querySelectorAll('.product-row').length > 1) {
                    row.remove();
                    updateOrderSummary();
                }
            });
        }
        
        updateOrderSummary();
    };
    
    // Update product price display
    window.updateProductPriceDisplay = function(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const price = selectedOption && selectedOption.value ? selectedOption.dataset.price : 0;
        
        // Update price display (show first selected product's price)
        const priceDisplay = document.getElementById('productPriceDisplay');
        if (priceDisplay) {
            priceDisplay.value = price ? `₱${parseFloat(price).toFixed(2)}` : '₱0.00';
        }
    };
    
    // Update order summary
    window.updateOrderSummary = function() {
        if (typeof window.__updateOrderSummaryCore === 'function') {
            window.__updateOrderSummaryCore();
        } else {
            // Fallback: compute generic product totals
            const rows = document.querySelectorAll('.product-row');
            let totalQuantity = 0;
            let totalAmount = 0;
            const products = [];
            
            rows.forEach(row => {
                const select = row.querySelector('.product-select');
                const qtyInput = row.querySelector('.product-quantity');
                
                if (select && qtyInput) {
                    const selectedOption = select.options[select.selectedIndex];
                    const quantity = parseInt(qtyInput.value) || 0;
                    const price = selectedOption && selectedOption.value ? parseFloat(selectedOption.dataset.price) : 0;
                    
                    if (selectedOption && selectedOption.value && quantity > 0) {
                        const productName = selectedOption.dataset.productName || 'Unknown Product';
                        const brand = selectedOption.dataset.brand || '';
                        const size = selectedOption.dataset.size || '';
                        const color = selectedOption.dataset.color || '';
                        const itemTotal = price * quantity;
                        
                        products.push({
                            name: productName,
                            brand: brand,
                            size: size,
                            color: color,
                            quantity: quantity,
                            price: price,
                            total: itemTotal
                        });
                        
                        totalQuantity += quantity;
                        totalAmount += itemTotal;
                    }
                }
            });
            
            document.getElementById('totalQuantityDisplay').textContent = totalQuantity;
            document.getElementById('itemTotalDisplay').textContent = '₱' + totalAmount.toFixed(2);
            
            const breakdownContainer = document.getElementById('productsBreakdown');
            if (breakdownContainer) {
                if (products.length === 0) {
                    breakdownContainer.innerHTML = '<div class="text-muted small mb-2">No products selected yet</div>';
                } else {
                    let html = '<div class="small">';
                    products.forEach((p, index) => {
                        let displayName = p.name;
                        if (p.brand) displayName += ' (' + p.brand + ')';
                        if (p.size) displayName += ' ' + p.size;
                        if (p.color) displayName += ' ' + p.color;
                        
                        html += '<div class="d-flex justify-content-between mb-1">';
                        html += '<span class="text-truncate" title="' + displayName + '">' + displayName + '</span>';
                        html += '<span>' + p.quantity + ' × ₱' + p.price.toFixed(2) + '</span>';
                        html += '</div>';
                    });
                    html += '</div>';
                    breakdownContainer.innerHTML = html;
                }
            }
            
            const firstRow = document.querySelector('.product-row:first-child');
            if (firstRow) {
                const firstSelect = firstRow.querySelector('.product-select');
                if (firstSelect) {
                    if (typeof updateProductPriceDisplay === 'function') {
                        updateProductPriceDisplay(firstSelect);
                    }
                }
            }
        }
    };

// ============================================
// GARMENT PRINTING FUNCTIONS
// ============================================

// Garment-specific variables
var garment_printingData = {};
var garment_selectedPrintSizes = [];
var garment_uploadedReferenceImages = [];
var garment_referenceImageCounter = 0;

// Open garment modal
window.garment_openModal = function(productType) {
    console.log('Opening garment modal for:', productType);
    garment_initializeProductRows();
    garment_loadFilterOptions(productType);
    loadProductsFromDatabase(productType);
    setTimeout(function() {
        garment_populatePrintTypes();
    }, 100);
};

// Garment-specific product rows system
window.garment_initializeProductRows = function() {
    const container = document.getElementById('garment_productRowsContainer');
    if (!container) return;
    
    // Reset to single row
    const firstRow = container.querySelector('.product-row:first-child');
    if (firstRow) {
        container.innerHTML = '';
        container.appendChild(firstRow);
    }
    const firstSelect = container.querySelector('.product-select');
    const firstQty = container.querySelector('.product-quantity');
    if (firstSelect) firstSelect.innerHTML = '<option value="">Select a product</option>';
    if (firstQty) firstQty.value = '1';
    
    garment_setupProductRowEvents();
};

window.garment_setupProductRowEvents = function() {
    const container = document.getElementById('garment_productRowsContainer');
    if (!container) return;
    
    // Add row button
    const addBtn = document.getElementById('garment_addProductRowBtn');
    if (addBtn) {
        addBtn.onclick = function() {
            garment_addProductRow();
        };
    }
    
    // Remove buttons
    container.querySelectorAll('.remove-row').forEach(function(btn) {
        btn.onclick = function() {
            var row = this.closest('.product-row');
            if (row && container.querySelectorAll('.product-row').length > 1) {
                row.remove();
                garment_updatePrintSummary();
            }
        };
    });
    
    // Select change listeners
    container.querySelectorAll('.product-select').forEach(function(sel) {
        sel.onchange = function() {
            garment_updatePrintSummary();
        };
    });
    
    // Quantity change listeners
    container.querySelectorAll('.product-quantity').forEach(function(qty) {
        qty.oninput = function() {
            garment_updatePrintSummary();
        };
    });
};

window.garment_addProductRow = function() {
    const container = document.getElementById('garment_productRowsContainer');
    const template = container.querySelector('.product-row-template');
    if (!container || !template) return;
    
    var newRow = template.cloneNode(true);
    newRow.classList.remove('product-row-template', 'd-none');
    newRow.classList.add('product-row');
    
    // Clear selections in new row
    var select = newRow.querySelector('.product-select');
    if (select) {
        select.selectedIndex = 0;
    }
    var qtyInput = newRow.querySelector('.product-quantity');
    if (qtyInput) qtyInput.value = '1';
    
    container.appendChild(newRow);
    
    // Reload products into the new select if available
    var currentType = window.currentProductType;
    if (currentType && select) {
        select.innerHTML = '<option value="">Loading products...</option>';
        fetch('/api/products-for-box/' + currentType)
            .then(function(r) { return r.json(); })
            .then(function(products) {
                select.innerHTML = '<option value="">Select product</option>';
                products.forEach(function(p) {
                    var opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = window.garment_buildProductOption(p);
                    opt.dataset.price = p.base_price;
                    var pSize = p.size;
                    if (!pSize && p.description) {
                        var sizeM = p.description.match(/Size:\s*([^,]+)/i);
                        if (sizeM) pSize = sizeM[1].trim();
                    }
                    opt.dataset.brand = p.brand || '';
                    opt.dataset.size = pSize || '';
                    opt.dataset.color = p.color || '';
                    opt.dataset.productName = p.name || '';
                    opt.dataset.productData = JSON.stringify(p);
                    select.appendChild(opt);
                });
                garment_updatePrintSummary();
            })
            .catch(function() {
                select.innerHTML = '<option value="">Error loading</option>';
            });
    }
    
    // Wire remove button
    var removeBtn = newRow.querySelector('.remove-row');
    if (removeBtn) {
        removeBtn.onclick = function() {
            if (container.querySelectorAll('.product-row').length > 1) {
                newRow.remove();
                garment_updatePrintSummary();
            }
        };
    }
    
    // Wire select/qty changes
    if (select) {
        select.onchange = function() { garment_updatePrintSummary(); };
    }
    if (qtyInput) {
        qtyInput.oninput = function() { garment_updatePrintSummary(); };
    }
};

// Populate print type dropdown
window.garment_populatePrintTypes = function() {
    var select = document.getElementById('garment_printTypeSelect');
    if (!select) return;
    select.innerHTML = '<option value="">-- Select Print Type --</option>';
    garment_hidePrintingSections();
    select.onchange = function() {
        garment_selectedPrintSizes = [];
        var val = this.value;
        if (val) {
            garment_loadPrintSizes(val);
        } else {
            garment_hidePrintingSections();
        }
    };
    fetch('/api/printing/options/dtf')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            garment_printingData = data;
            var types = data.print_types || [];
            types.forEach(function(t) {
                var opt = document.createElement('option');
                opt.value = t;
                var label = t.charAt(0).toUpperCase() + t.slice(1) + ' Print';
                opt.textContent = label;
                select.appendChild(opt);
            });
        })
        .catch(function(err) {
            console.error('Error loading print types:', err);
        });
};

// Load print sizes for selected print type
window.garment_loadPrintSizes = function(printType) {
    if (!printType) {
        garment_hidePrintingSections();
        return;
    }
    fetch('/api/printing/options/' + printType)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            garment_printingData = data;
            garment_renderPrintSizes(data.prices);
            var container = document.getElementById('garment_printSizesContainer');
            if (container) container.style.display = 'block';
            garment_selectedPrintSizes = [];
            garment_updatePrintSummary();
        })
        .catch(function(err) {
            console.error('Error loading print options:', err);
            var list = document.getElementById('garment_printSizesList');
            if (list) list.innerHTML = '<div class="text-danger small">Failed to load print options.</div>';
        });
};

// Render print size checkboxes
window.garment_renderPrintSizes = function(prices) {
    var container = document.getElementById('garment_printSizesList');
    if (!container) return;
    if (!prices || prices.length === 0) {
        container.innerHTML = '<div class="text-muted small">No print sizes available.</div>';
        return;
    }
    var html = '';
    prices.forEach(function(p) {
        html += '<label class="print-size-checkbox d-block mb-1 p-1 border rounded" style="cursor:pointer;">';
        html += '<input type="checkbox" value="' + p.id + '" onchange="garment_togglePrintSize(' + p.id + ', this)">';
        html += ' <span>' + p.name + '</span>';
        html += ' <span class="float-end text-muted">\u20B1' + p.price.toFixed(2) + '</span>';
        html += '</label>';
    });
    container.innerHTML = html;
};

// Toggle print size selection
window.garment_togglePrintSize = function(sizeId, checkbox) {
    var labelEl = checkbox.closest('label');
    if (checkbox.checked) {
        if (labelEl) labelEl.style.borderColor = '#0d6efd';
        if (garment_selectedPrintSizes.indexOf(sizeId) === -1) {
            garment_selectedPrintSizes.push(sizeId);
        }
    } else {
        if (labelEl) labelEl.style.borderColor = '#dee2e6';
        var idx = garment_selectedPrintSizes.indexOf(sizeId);
        if (idx !== -1) {
            garment_selectedPrintSizes.splice(idx, 1);
        }
    }
    garment_updatePrintSummary();
};

// Special Price functions
window.garment_hasSpecialPrice = false;
window.garment_toggleSpecialPrice = function() {
    var section = document.getElementById('garment_specialPriceSection');
    var btn = document.getElementById('garment_specialPriceBtn');
    if (!section) return;
    var showing = section.style.display !== 'none';
    if (showing) {
        section.style.display = 'none';
        btn.textContent = '💰 Special Price';
        btn.classList.remove('btn-warning');
        btn.classList.add('btn-outline-warning');
        garment_hasSpecialPrice = false;
        garment_updatePrintSummary();
    } else {
        section.style.display = 'block';
        btn.textContent = '💰 Cancel Special Price';
        btn.classList.remove('btn-outline-warning');
        btn.classList.add('btn-warning');
        // Pre-fill with current print total
        var printTotalEl = document.getElementById('garment_printTotalDisplay');
        var spInput = document.getElementById('garment_specialPrintTotal');
        if (printTotalEl && spInput) {
            var current = parseFloat(printTotalEl.textContent.replace(/[₱,]/g, '')) || 0;
            if (current > 0) spInput.value = current.toFixed(2);
        }
        garment_hasSpecialPrice = true;
        garment_updatePrintSummary();
    }
};

window.garment_clearSpecialPrice = function() {
    var section = document.getElementById('garment_specialPriceSection');
    var btn = document.getElementById('garment_specialPriceBtn');
    var spInput = document.getElementById('garment_specialPrintTotal');
    var reasonInput = document.getElementById('garment_specialPriceReason');
    if (section) section.style.display = 'none';
    if (btn) {
        btn.textContent = '💰 Special Price';
        btn.classList.remove('btn-warning');
        btn.classList.add('btn-outline-warning');
    }
    if (spInput) spInput.value = '';
    if (reasonInput) reasonInput.value = '';
    garment_hasSpecialPrice = false;
    garment_updatePrintSummary();
};

window.garment_onSpecialPriceChange = function() {
    garment_updatePrintSummary();
};

// Clear print selection
window.garment_clearPrintSelection = function() {
    garment_selectedPrintSizes = [];
    garment_printingData = {};
    var select = document.getElementById('garment_printTypeSelect');
    if (select) select.value = '';
    garment_hidePrintingSections();
};

// Hide printing sections
window.garment_hidePrintingSections = function() {
    var container = document.getElementById('garment_printSizesContainer');
    var sidebar = document.getElementById('garment_printSummarySidebar');
    var qtySection = document.getElementById('garment_printQuantitySection');
    if (container) container.style.display = 'none';
    if (sidebar) sidebar.style.display = 'none';
    if (qtySection) qtySection.style.display = 'none';
};

// Update print summary (combo, bulk, totals)
window.garment_updatePrintSummary = function() {
    var sidebar = document.getElementById('garment_printSummarySidebar');
    var qtySection = document.getElementById('garment_printQuantitySection');
    if (garment_selectedPrintSizes.length === 0) {
        if (sidebar) sidebar.style.display = 'none';
        if (qtySection) qtySection.style.display = 'none';
        return;
    }
    if (qtySection) qtySection.style.display = 'block';
    var prices = garment_printingData.prices || [];
    var combos = garment_printingData.combos || [];
    var bulkTiers = garment_printingData.bulk_tiers || [];
    var qtyInputEl = document.getElementById('garment_printQuantityInput');
    var totalQty = qtyInputEl ? parseInt(qtyInputEl.value) || 1 : 1;
    var printCostPerItem = 0;
    var selectedSizeDetails = [];
    garment_selectedPrintSizes.forEach(function(sizeId) {
        var found = null;
        for (var i = 0; i < prices.length; i++) {
            if (prices[i].id === sizeId) {
                found = prices[i];
                break;
            }
        }
        if (found) {
            printCostPerItem += found.price;
            selectedSizeDetails.push(found);
        }
    });
    // Store original print cost BEFORE any discounts (for special price display)
    var rawPrintCostPerItem = printCostPerItem;
    var comboDiscount = 0;
    var comboDetails = [];
    combos.forEach(function(c) {
        if (garment_selectedPrintSizes.indexOf(c.size1_id) !== -1 && 
            garment_selectedPrintSizes.indexOf(c.size2_id) !== -1) {
            comboDetails.push(c);
        }
    });
    if (comboDetails.length > 0) {
        comboDetails.sort(function(a, b) { return b.discount - a.discount; });
        var best = comboDetails[0];
        comboDiscount = best.discount;
        comboDetails = [best];
    }
    var printCostAfterCombo = printCostPerItem - comboDiscount;
    var subtotal = printCostAfterCombo * totalQty;
    var bulkDiscount = 0;
    var bulkLabel = '';
    for (var bi = bulkTiers.length - 1; bi >= 0; bi--) {
        var tier = bulkTiers[bi];
        if (totalQty >= tier.min && totalQty <= tier.max) {
            if (tier.type === 'percentage') {
                bulkDiscount = subtotal * (tier.percent / 100);
                bulkLabel = tier.label;
            } else if (tier.type === 'fixed') {
                bulkDiscount = tier.amount * totalQty;
                bulkLabel = tier.label;
            }
            break;
        }
    }
    var total = subtotal - bulkDiscount;
    
    // SPECIAL PRICE OVERRIDE
    var isSpecialPrice = window.garment_hasSpecialPrice || false;
    var specialTotal = 0;
    if (isSpecialPrice) {
        var spInput = document.getElementById('garment_specialPrintTotal');
        if (spInput) {
            specialTotal = parseFloat(spInput.value) || 0;
        }
    }
    
    // Render sizes breakdown
    var sizesHtml = '';
    selectedSizeDetails.forEach(function(s) {
        sizesHtml += '<div class="d-flex justify-content-between">';
        sizesHtml += '<span>' + s.name + '</span>';
        sizesHtml += '<span>₱' + s.price.toFixed(2) + '</span>';
        sizesHtml += '</div>';
    });
    var sizesBreakdown = document.getElementById('garment_printSizesBreakdown');
    if (sizesBreakdown) sizesBreakdown.innerHTML = sizesHtml;
    var costPerItemEl = document.getElementById('garment_printCostPerItemDisplay');
    var qtyDisplayEl = document.getElementById('garment_printQtyDisplay');
    var subtotalEl = document.getElementById('garment_printSubtotalDisplay');
    var comboRow = document.getElementById('garment_comboDiscountRow');
    var comboDisplay = document.getElementById('garment_comboDiscountDisplay');
    var bulkRow = document.getElementById('garment_bulkDiscountRow');
    var bulkDisplay = document.getElementById('garment_bulkDiscountDisplay');
    var printTotalEl = document.getElementById('garment_printTotalDisplay');
    var grandTotalEl = document.getElementById('garment_grandTotalDisplay');
    // Show original print cost (before any discounts)
    if (costPerItemEl) costPerItemEl.textContent = '₱' + rawPrintCostPerItem.toFixed(2);
    if (qtyDisplayEl) qtyDisplayEl.textContent = totalQty;
    if (subtotalEl) subtotalEl.textContent = '₱' + subtotal.toFixed(2);
    if (isSpecialPrice && specialTotal > 0) {
        // Special price: hide combo/bulk, show original qty/cost but use special total
        if (comboRow) comboRow.style.display = 'none';
        if (bulkRow) bulkRow.style.display = 'none';
        if (printTotalEl) printTotalEl.textContent = '₱' + specialTotal.toFixed(2) + ' (Special Price)';
    } else {
        // Normal: show combo/bulk discounts as before
        var displayCostPerItem = printCostAfterCombo;
        if (costPerItemEl) costPerItemEl.textContent = '₱' + displayCostPerItem.toFixed(2);
        if (comboDiscount > 0 && comboRow && comboDisplay) {
            comboRow.style.display = 'flex';
            comboDisplay.textContent = '-₱' + comboDiscount.toFixed(2);
        } else if (comboRow) {
            comboRow.style.display = 'none';
        }
        if (bulkDiscount > 0 && bulkRow && bulkDisplay) {
            bulkRow.style.display = 'flex';
            bulkDisplay.textContent = '-₱' + bulkDiscount.toFixed(2);
        } else if (bulkRow) {
            bulkRow.style.display = 'none';
        }
        if (printTotalEl) printTotalEl.textContent = '₱' + total.toFixed(2);
    }
    
    // Calculate grand total
    var productTotal = 0;
    var qtyTotal = 0;
    document.querySelectorAll('#garment_productRowsContainer .product-row').forEach(function(row) {
        var qty = parseInt(row.querySelector('.product-quantity').value) || 0;
        var opt = row.querySelector('.product-select option:checked');
        var price = opt && opt.dataset.price ? parseFloat(opt.dataset.price) : 0;
        qtyTotal += qty;
        productTotal += qty * price;
    });
    var grandTotal = productTotal + (isSpecialPrice && specialTotal > 0 ? specialTotal : total);
    if (grandTotalEl) grandTotalEl.textContent = '\u20B1' + grandTotal.toFixed(2);
    if (sidebar) sidebar.style.display = 'block';
    var qtyDisplay = document.getElementById('garment_totalQtyDisplay');
    var amtDisplay = document.getElementById('garment_totalAmountDisplay');
    if (qtyDisplay) qtyDisplay.textContent = qtyTotal;
    if (amtDisplay) amtDisplay.textContent = '\u20B1' + productTotal.toFixed(2);
    
    // Render products breakdown
    var breakdownEl = document.getElementById('garment_productsBreakdown');
    if (breakdownEl) {
        var rows = document.querySelectorAll('#garment_productRowsContainer .product-row');
        var hasProducts = false;
        var html = '';
        rows.forEach(function(row) {
            var sel = row.querySelector('.product-select');
            var qtyInput = row.querySelector('.product-quantity');
            if (!sel || !qtyInput) return;
            var opt = sel.options[sel.selectedIndex];
            var qty = parseInt(qtyInput.value) || 0;
            if (!opt || !opt.value || qty === 0) return;
            hasProducts = true;
            var price = opt.dataset.price ? parseFloat(opt.dataset.price) : 0;
            var subtotal = qty * price;
            var brand = opt.dataset.brand || '';
            var size = opt.dataset.size || '';
            var color = opt.dataset.color || '';
            var shortName = [brand, color, size].filter(Boolean).join(' ');
            if (!shortName) {
                var name = opt.textContent.split(' - ')[0] || 'Product';
                shortName = name;
            }
            html += '<div class="d-flex justify-content-between align-items-center mb-1 small">';
            html += '<span>' + shortName + ' <span class="text-muted">x' + qty + '</span></span>';
            html += '<span>\u20B1' + subtotal.toFixed(2) + '</span>';
            html += '</div>';
        });
        if (hasProducts) {
            breakdownEl.innerHTML = html;
        } else {
            breakdownEl.innerHTML = '<div class="text-muted small mb-2">No products selected yet</div>';
        }
    }
};

// Reference image handling
window.garment_triggerReferenceFilePicker = function() {
    var picker = document.getElementById('referenceFilePicker');
    if (picker) picker.click();
};
window.garment_onReferencePaste = function(e) {
    var items = (e.clipboardData || e.originalEvent.clipboardData).items;
    for (var i = 0; i < items.length; i++) {
        if (items[i].type.indexOf('image') === 0) {
            var file = items[i].getAsFile();
            if (file) garment_handleReferenceImage(file);
        }
    }
};
window.garment_onReferenceDragOver = function(e) {
    e.preventDefault();
    e.stopPropagation();
    var dz = document.getElementById('referenceDropZone');
    if (dz) dz.style.borderColor = '#0d6efd';
};
window.garment_onReferenceDragLeave = function(e) {
    e.preventDefault();
    e.stopPropagation();
    var dz = document.getElementById('referenceDropZone');
    if (dz) dz.style.borderColor = '#ccc';
};
window.garment_onReferenceDrop = function(e) {
    e.preventDefault();
    e.stopPropagation();
    var dz = document.getElementById('referenceDropZone');
    if (dz) dz.style.borderColor = '#ccc';
    var files = e.dataTransfer.files;
    for (var i = 0; i < files.length; i++) {
        if (files[i].type.indexOf('image') === 0) {
            garment_handleReferenceImage(files[i]);
        }
    }
};
window.garment_onReferenceFilePickerChange = function(e) {
    var files = e.target.files;
    for (var i = 0; i < files.length; i++) {
        garment_handleReferenceImage(files[i]);
    }
    e.target.value = '';
};
window.garment_handleReferenceImage = function(file) {
    var reader = new FileReader();
    reader.onload = function(e) {
        garment_referenceImageCounter++;
        garment_uploadedReferenceImages.push({
            id: garment_referenceImageCounter,
            dataUrl: e.target.result,
            name: file.name
        });
        garment_renderReferencePreviews();
    };
    reader.readAsDataURL(file);
};
window.garment_renderReferencePreviews = function() {
    var container = document.getElementById('referencePreviewsGallery');
    if (!container) return;
    if (garment_uploadedReferenceImages.length === 0) {
        container.innerHTML = '';
        return;
    }
    var html = '';
    garment_uploadedReferenceImages.forEach(function(img) {
        html += '<div style="position:relative;display:inline-block;width:80px;height:80px;border:1px solid #dee2e6;border-radius:6px;overflow:hidden;">';
        html += '<img src="' + img.dataUrl + '" style="width:100%;height:100%;object-fit:cover;">';
        html += '<button type="button" onclick="garment_removeReferenceImage(' + img.id + ')" style="position:absolute;top:2px;right:2px;background:rgba(0,0,0,0.6);color:white;border:none;border-radius:50%;width:20px;height:20px;font-size:12px;line-height:20px;text-align:center;padding:0;cursor:pointer;">&times;</button>';
        html += '</div>';
    });
    container.innerHTML = html;
};
window.garment_clearAllReferenceImages = function() {
    garment_uploadedReferenceImages = [];
    garment_referenceImageCounter = 0;
    garment_renderReferencePreviews();
};
window.garment_removeReferenceImage = function(id) {
    garment_uploadedReferenceImages = garment_uploadedReferenceImages.filter(function(img) { return img.id !== id; });
    garment_renderReferencePreviews();
};

// Garment add to cart
window.garment_addItemToCart = function() {
    // Department auto-assigned by product type
    var deptMap = {'garment':'iprint','tarpaulin':'consol','embroidery':'cinco','cutting':'class','sewing':'class','design':'iprint'};
    var department = deptMap['garment'] || 'iprint';
    var rows = document.querySelectorAll('#garment_productRowsContainer .product-row');
    var subItems = [];
    var validationError = false;
    var dateNeededEl = document.getElementById('garment_dateNeeded');
    if (dateNeededEl && !dateNeededEl.value) {
        alert('Please select a Date Needed for this garment order.');
        dateNeededEl.focus();
        return;
    }
    var notes = document.getElementById('garment_productNotes');
    var printTypeEl = document.getElementById('garment_printTypeSelect');
    var qtyInputPrint = document.getElementById('garment_printQuantityInput');
    rows.forEach(function(row, index) {
        if (validationError) return;
        var select = row.querySelector('.product-select');
        var qtyInput = row.querySelector('.product-quantity');
        if (!select || !qtyInput) return;
        var selectedOption = select.options[select.selectedIndex];
        var quantity = parseInt(qtyInput.value) || 0;
        var price = selectedOption && selectedOption.value ? parseFloat(selectedOption.dataset.price) : 0;
        if (!select.value) { alert('Please select a product for row ' + (index + 1)); select.focus(); validationError = true; return; }
        if (quantity <= 0) { alert('Please enter valid quantity for row ' + (index + 1)); qtyInput.focus(); validationError = true; return; }
        if (price <= 0) { alert('Invalid price for product in row ' + (index + 1)); validationError = true; return; }
        var brand = selectedOption.dataset.brand || '';
        var size = selectedOption.dataset.size || '';
        var color = selectedOption.dataset.color || '';
        var productName = selectedOption.dataset.productName || 'Unknown Product';
        var shortName = [brand, color, size].filter(Boolean).join(' ');
        if (!shortName) shortName = productName;
        subItems.push({
            productId: select.value,
            name: productName,
            shortName: shortName,
            brand: brand,
            size: size,
            color: color,
            quantity: quantity,
            unitPrice: price,
            totalPrice: quantity * price
        });
    });
    if (validationError) return;
    if (subItems.length === 0) { alert('Please add at least one product'); return; }
    // Compute combined totals
    var totalQty = 0;
    var totalProductPrice = 0;
    subItems.forEach(function(si) {
        totalQty += si.quantity;
        totalProductPrice += si.totalPrice;
    });
    // Build printing summary (mirroring garment_updatePrintSummary logic)
    var printDetails = null;
    if (garment_selectedPrintSizes && garment_selectedPrintSizes.length > 0) {
        var printTypeName = printTypeEl ? printTypeEl.options[printTypeEl.selectedIndex]?.text || printTypeEl.value : '';
        var printPrices = garment_printingData.prices || [];
        var combos = garment_printingData.combos || [];
        var selectedPrintNames = [];
        var printCostRaw = 0;
        garment_selectedPrintSizes.forEach(function(sizeId) {
            for (var pi = 0; pi < printPrices.length; pi++) {
                if (printPrices[pi].id === sizeId) {
                    selectedPrintNames.push(printPrices[pi].name);
                    printCostRaw += printPrices[pi].price;
                    break;
                }
            }
        });
        var printQty = qtyInputPrint ? parseInt(qtyInputPrint.value) || 0 : 0;
        // Combo discount (same logic as garment_updatePrintSummary)
        var comboDiscount = 0;
        combos.forEach(function(c) {
            if (garment_selectedPrintSizes.indexOf(c.size1_id) !== -1 &&
                garment_selectedPrintSizes.indexOf(c.size2_id) !== -1) {
                if (c.discount > comboDiscount) comboDiscount = c.discount;
            }
        });
        var printCostAfterCombo = printCostRaw - comboDiscount;
        var subtotalBeforeBulk = printCostAfterCombo * printQty;
        // Bulk discount
        var bulkTiers = garment_printingData.bulk_tiers || [];
        var bulkDiscount = 0;
        for (var bi = bulkTiers.length - 1; bi >= 0; bi--) {
            var tier = bulkTiers[bi];
            if (printQty >= tier.min && printQty <= tier.max) {
                if (tier.type === 'percentage') {
                    bulkDiscount = subtotalBeforeBulk * (tier.percent / 100);
                } else if (tier.type === 'fixed') {
                    bulkDiscount = tier.amount * printQty;
                }
                break;
            }
        }
        // Special price override
        var isSpecialPrice = window.garment_hasSpecialPrice || false;
        var specialTotal = 0;
        var specialReason = '';
        if (isSpecialPrice) {
            var spInput = document.getElementById('garment_specialPrintTotal');
            var reasonInput = document.getElementById('garment_specialPriceReason');
            if (spInput) specialTotal = parseFloat(spInput.value) || 0;
            if (reasonInput) specialReason = reasonInput.value.trim();
        }
        printDetails = {
            printType: printTypeName,
            printSizes: selectedPrintNames,
            printCostPerItem: isSpecialPrice && specialTotal > 0 ? (specialTotal / printQty) : printCostAfterCombo,
            printQty: printQty,
            printSubtotal: isSpecialPrice && specialTotal > 0 ? specialTotal : (subtotalBeforeBulk - bulkDiscount),
            comboDiscount: comboDiscount,
            bulkDiscount: bulkDiscount,
            isSpecialPrice: isSpecialPrice,
            specialTotal: isSpecialPrice ? specialTotal : 0,
            specialReason: specialReason
        };
    }
    // Create single garment order item
    var garmentItem = {
        id: Date.now(),
        productType: 'garment',
        department: department,
        name: 'Garment Order',
        totalQty: totalQty,
        totalProductPrice: totalProductPrice,
        quantity: totalQty,
        unitPrice: totalProductPrice,
        totalPrice: totalProductPrice,
        date_needed: document.getElementById('garment_dateNeeded')?.value || '',
        notes: notes ? notes.value.trim() : '',
        timestamp: new Date().toISOString(),
        subItems: subItems,
        printing: printDetails,
        referenceImages: garment_uploadedReferenceImages.map(function(img) {
            return { id: img.id, name: img.name, dataUrl: img.dataUrl };
        })
    };
    selectedItems.push(garmentItem);
    updateSelectedItemsDisplay();
    updateOrderSummary();
    garment_clearAllReferenceImages();
    garment_clearPrintSelection();
    if (typeof garment_initializeProductRows === 'function') garment_initializeProductRows();
    /* Department auto-assigned; no reset needed */
    var notesEl = document.getElementById('garment_productNotes');
    if (notesEl) notesEl.value = '';
    var modalEl = document.getElementById('productModal');
    if (modalEl) {
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
    showToast('Garment order added to cart!', 'success');
};

// ======================
// SUBLIMATION PRINTING MODAL
// ======================

// ======================
// SUBLIMATION PRINTING MODAL - CUSTOMER ORDER FORM
// ======================

// Helper: total qty from size inputs + roster
window.sublimation_getTotalQty = function() {
    // Sum size-by-qty inputs
    var sizeInputs = document.querySelectorAll('#sizes .size-qty, #sublimationModalBody .size-qty');
    var total = 0;
    sizeInputs.forEach(function(inp) {
        total += parseInt(inp.value) || 0;
    });
    // Sum roster rows (read roster-number value per row)
    var rosterRows = document.querySelectorAll('#sublimation_rosterBody tr.roster-row');
    rosterRows.forEach(function(row) {
        var qtyInput = row.querySelector('.roster-number');
        total += parseInt(qtyInput ? qtyInput.value : '') || 1;
    });
    return total;
};

// Auto-generate description from garment + fabric + parts selections
window.sublimation_autoGenerateDescription = function() {
    var descEl = document.getElementById('sublimation_description');
    if (!descEl) return;
    
    var parts = [];
    
    // Get garment name
    var garmentSelect = document.getElementById('sublimation_garmentSelect');
    if (garmentSelect && garmentSelect.selectedIndex > 0) {
        var garmentName = garmentSelect.options[garmentSelect.selectedIndex].textContent.split(' - ')[0];
        if (garmentName) parts.push(garmentName.toUpperCase());
    }
    
    // Get fabric name
    var fabricSelect = document.getElementById('sublimation_fabricSelect');
    if (fabricSelect && fabricSelect.selectedIndex > 0) {
        var fabricName = fabricSelect.options[fabricSelect.selectedIndex].textContent.split(' - ')[0];
        if (fabricName) parts.push(fabricName.toUpperCase());
    }
    
    // Get selected parts
    var checkboxes = document.querySelectorAll('.sublimation-part-checkbox:checked');
    var partNames = [];
    checkboxes.forEach(function(cb) {
        var label = cb.nextElementSibling;
        if (label) {
            var name = label.textContent.split(' (+')[0];
            if (name) partNames.push(name.toUpperCase());
        }
    });
    if (partNames.length > 0) {
        parts.push(partNames.join(' + '));
    }
    
    // Project name prefix if set
    var projEl = document.getElementById('sublimation_projectName');
    var prefix = projEl && projEl.value.trim() ? projEl.value.trim().toUpperCase() + ' - ' : '';
    
    descEl.value = prefix + (parts.length > 0 ? parts.join(' / ') : '');
};

// Sublimation open modal - loads pricing items from API and resets form
window.sublimation_openModal = function() {
    // Reset ALL form fields
    var fieldsToReset = [
        'sublimation_projectName', 'sublimation_description',
        'sublimation_designer', 'sublimation_dateNeeded',
        'sublimation_notes'
    ];
    fieldsToReset.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    /* Department auto-assigned; no manual select needed */
    
    // Reset spec fields (dynamic fields inside #sublimation_specsFields)
    var specInputs = document.querySelectorAll('#specs input, #specs select');
    specInputs.forEach(function(el) {
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
        else el.value = '';
    });
    
    // Uncheck all part checkboxes (auto-addon cleanup) & re-enable any disabled ones
    document.querySelectorAll('.sublimation-part-checkbox').forEach(function(cb) { cb.checked = false; cb.disabled = false; });
    
    // Reset size inputs
    var sizeInputs = document.querySelectorAll('#sizes .size-qty');
    sizeInputs.forEach(function(el) { el.value = '0'; });
    
    // Reset roster
    var rosterBody = document.getElementById('sublimation_rosterBody');
    if (rosterBody) {
        var rows = rosterBody.querySelectorAll('tr.roster-row');
        rows.forEach(function(r) { r.remove(); });
        var empty = document.getElementById('sublimation_rosterEmpty');
        if (empty) empty.style.display = '';
        var countEl = document.getElementById('sublimation_rosterCount');
        if (countEl) countEl.textContent = '0';
    }
    
    // Reset size mode to By Qty
    var byQtyRadio = document.getElementById('sizeModeByQty');
    if (byQtyRadio) { byQtyRadio.checked = true; sublimation_toggleSizeMode(); }
    
    // Reset garment/fabric selects
    var garmentSelect = document.getElementById('sublimation_garmentSelect');
    var fabricSelect = document.getElementById('sublimation_fabricSelect');
    if (garmentSelect) garmentSelect.innerHTML = '<option value="">Loading...</option>';
    if (fabricSelect) fabricSelect.innerHTML = '<option value="">Loading...</option>';
    document.getElementById('sublimation_partsContainer').innerHTML = '<p class="text-muted small mb-0">Select Garment type first...</p>';
    
    // Reset summary
    sublimation_calculateTotal();
    
    // Switch to first tab
    var firstTab = document.querySelector('#sublimationTab .nav-link');
    if (firstTab && bootstrap && bootstrap.Tab) {
        var tab = new bootstrap.Tab(firstTab);
        tab.show();
    }
    
    // Reset print order slip tracking
    window.sublimation_orderSlipViewed = false;
    var orderSlipBtn = document.getElementById('sublimation_printOrderSlip');
    if (orderSlipBtn) {
        orderSlipBtn.classList.remove('btn-success');
        orderSlipBtn.classList.add('btn-outline-info');
        orderSlipBtn.innerHTML = '<i class="fas fa-print me-2"></i> Print Order Slip';
    }

    // Fetch SublimationPrice records
    fetch('/api/sublimation-prices')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            // Store sizes pricing globally for calculateTotal
            window.sublimation_sizesData = {};
            if (data.sizes && data.sizes.length) {
                data.sizes.forEach(function(item) {
                    window.sublimation_sizesData[item.name.toUpperCase()] = parseFloat(item.price) || 0;
                });
            }
            // Populate garment select
            if (garmentSelect) {
                garmentSelect.innerHTML = '<option value="">-- Select Garment --</option>';
                var count = 0;
                data.garments.forEach(function(item) {
                    var opt = document.createElement('option');
                    opt.value = item.id;
                    opt.dataset.price = item.price;
                    opt.textContent = item.name;
                    garmentSelect.appendChild(opt);
                    count++;
                });
            }
            
            // Populate fabric select
            if (fabricSelect) {
                fabricSelect.innerHTML = '<option value="">-- Select Fabric --</option>';
                data.fabrics.forEach(function(item) {
                    var opt = document.createElement('option');
                    opt.value = item.id;
                    opt.dataset.price = item.price;
                    opt.textContent = item.name;
                    fabricSelect.appendChild(opt);
                });
            }
            
            // Populate parts (single-column checkboxes for Pricing tab)
            var partsContainer = document.getElementById('sublimation_partsContainer');
            if (partsContainer) {
                if (data.parts.length === 0) {
                    partsContainer.innerHTML = '<p class="text-muted small mb-0">No parts available.</p>';
                } else {
                    partsContainer.innerHTML = '';
                    data.parts.forEach(function(item) {
                        var div = document.createElement('div');
                        div.className = 'form-check form-check-inline';
                        var cb = document.createElement('input');
                        cb.type = 'checkbox';
                        cb.className = 'form-check-input sublimation-part-checkbox';
                        cb.dataset.price = item.price;
                        cb.value = item.id;
                        cb.onchange = function() { sublimation_calculateTotal(); sublimation_autoGenerateDescription(); };
                        var label = document.createElement('label');
                        label.className = 'form-check-label small';
                        label.textContent = item.name + ' (+\u20B1' + parseFloat(item.price).toFixed(2) + ')';
                        div.appendChild(cb);
                        div.appendChild(label);
                        partsContainer.appendChild(div);
                    });
                }
            }
            
            // Recalculate after load
            sublimation_calculateTotal();
            sublimation_autoGenerateDescription();
        })
        .catch(function() {
            if (garmentSelect) garmentSelect.innerHTML = '<option value="">-- Failed to load --</option>';
            if (fabricSelect) fabricSelect.innerHTML = '<option value="">-- Failed to load --</option>';
            document.getElementById('sublimation_partsContainer').innerHTML = '<p class="text-danger small mb-0">Failed to load.</p>';
        });
};

// =============================================
// GARMENT SPECIFICATION DEFINITIONS
// =============================================
// Each group matches garment names (case-insensitive) via nameKeywords.
// fields[] defines the specification fields shown for that garment type.
// autoAddon maps a selected option value -> part DB id to auto-check.
var garmentSpecMap = {
    tshirt: {
        nameKeywords: ['TSHIRT'],
        fields: [
            { id: 'spec_neckRibbingColor', label: 'Neck Ribbings Color', type: 'select', options: ['BLACK', 'WHITE', 'SUBILI PRINT'] },
            { id: 'spec_neckTape', label: 'Neck Tape', type: 'select', options: ['NO DESIGN (STANDARD)', 'WITH DESIGN (FROM CLIENT)'], autoAddon: { 'WITH DESIGN (FROM CLIENT)': 27 } },
            { id: 'spec_sizeLabel', label: 'Size Label', type: 'select', options: ['YES', 'NO'] },
            { id: 'spec_cuffs', label: 'Cuffs', type: 'select', options: ['SUBILI PRINT', 'KNITTED CUFFS', 'SELF FABRIC'], autoAddon: { 'KNITTED CUFFS': 36 } },
            { id: 'spec_slit', label: 'Slit', type: 'select', options: ['YES', 'NO'], autoAddon: { 'YES': 29 } },
            { id: 'spec_pocket', label: 'Pocket', type: 'select', options: ['NO', 'ONE POCKET', 'TWO POCKETS'], autoAddon: { 'ONE POCKET': 30, 'TWO POCKETS': 39 } },
            { id: 'spec_armsleeve', label: 'Armsleeve', type: 'select', options: ['SHORTSLEEVE', 'LONGSLEEVE'], autoAddon: { 'LONGSLEEVE': 28 } },
            { id: 'spec_shoulder', label: 'Shoulder', type: 'select', options: ['REGULAR', 'RAGLAN'], autoAddon: { 'RAGLAN': 33 } }
        ]
    },
    polo: {
        nameKeywords: ['POLO'],
        fields: [
            { id: 'spec_collar', label: 'Collar', type: 'select', options: ['SUBILI PRINT', 'KNITTED COLLAR'], autoAddon: { 'KNITTED COLLAR': 35 } },
            { id: 'spec_cuffs', label: 'Cuffs', type: 'select', options: ['SUBILI PRINT', 'KNITTED CUFFS', 'SELF FABRIC'], autoAddon: { 'KNITTED CUFFS': 36 } },
            { id: 'spec_neckTape', label: 'Neck Tape', type: 'select', options: ['NO DESIGN (STANDARD)', 'WITH DESIGN (FROM CLIENT)'], autoAddon: { 'WITH DESIGN (FROM CLIENT)': 27 } },
            { id: 'spec_sizeLabel', label: 'Size Label', type: 'select', options: ['YES', 'NO'] },
            { id: 'spec_slit', label: 'Slit', type: 'select', options: ['YES', 'NO'], autoAddon: { 'YES': 29 } },
            { id: 'spec_pocket', label: 'Pocket', type: 'select', options: ['NO', 'ONE POCKET', 'TWO POCKETS'], autoAddon: { 'ONE POCKET': 30, 'TWO POCKETS': 39 } },
            { id: 'spec_armsleeve', label: 'Armsleeve', type: 'select', options: ['SHORTSLEEVE', 'LONGSLEEVE'], autoAddon: { 'LONGSLEEVE': 28 } },
            { id: 'spec_shoulder', label: 'Shoulder', type: 'select', options: ['REGULAR', 'RAGLAN'], autoAddon: { 'RAGLAN': 33 } }
        ]
    },
    jersey: {
        nameKeywords: ['JERSEY UP', 'JERSEY UP AND DOWN'],
        fields: [
            { id: 'spec_neckRibbingColor', label: 'Neck Ribbings Color', type: 'select', options: ['BLACK', 'WHITE', 'SUBILI PRINT'] },
            { id: 'spec_neckTape', label: 'Neck Tape', type: 'select', options: ['NO DESIGN (STANDARD)', 'WITH DESIGN (FROM CLIENT)'], autoAddon: { 'WITH DESIGN (FROM CLIENT)': 27 } },
            { id: 'spec_sizeLabel', label: 'Size Label', type: 'select', options: ['YES', 'NO'] },
            { id: 'spec_slit', label: 'Slit', type: 'select', options: ['YES', 'NO'], autoAddon: { 'YES': 29 } },
            { id: 'spec_neckShape', label: 'Neck Shape', type: 'select', options: ['VNECK', 'ROUNDNECK'] },
            { id: 'spec_cutType', label: 'Type of Cut', type: 'select', options: ['NBA CUT', 'REGULAR'], autoAddon: { 'NBA CUT': 32 } }
        ]
    },
    'jersey-short': {
        nameKeywords: ['JERSEY SHORT'],
        fields: [
            { id: 'spec_inner', label: 'Inner', type: 'select', options: ['YES', 'NO'], autoAddon: { 'YES': 38 } },
            { id: 'spec_pocket', label: 'Pocket', type: 'select', options: ['NO', 'ONE POCKET', 'TWO POCKETS'], autoAddon: { 'ONE POCKET': 30, 'TWO POCKETS': 39 } }
        ]
    },
    hoodie: {
        nameKeywords: ['HOODIE'],
        fields: [
            { id: 'spec_neckRibbingColor', label: 'Neck Ribbings Color', type: 'select', options: ['BLACK', 'WHITE', 'SUBILI PRINT'] },
            { id: 'spec_neckTape', label: 'Neck Tape', type: 'select', options: ['NO DESIGN (STANDARD)', 'WITH DESIGN (FROM CLIENT)'], autoAddon: { 'WITH DESIGN (FROM CLIENT)': 27 } },
            { id: 'spec_sizeLabel', label: 'Size Label', type: 'select', options: ['YES', 'NO'] },
            { id: 'spec_cuffs', label: 'Cuffs', type: 'select', options: ['SUBILI PRINT', 'KNITTED CUFFS', 'SELF FABRIC'], autoAddon: { 'KNITTED CUFFS': 36 } },
            { id: 'spec_slit', label: 'Slit', type: 'select', options: ['YES', 'NO'], autoAddon: { 'YES': 29 } },
            { id: 'spec_pocket', label: 'Pocket', type: 'select', options: ['NO', 'ONE POCKET', 'TWO POCKETS'], autoAddon: { 'ONE POCKET': 30, 'TWO POCKETS': 39 } },
            { id: 'spec_armsleeve', label: 'Armsleeve', type: 'select', options: ['SHORTSLEEVE', 'LONGSLEEVE'], autoAddon: { 'LONGSLEEVE': 28 } },
            { id: 'spec_shoulder', label: 'Shoulder', type: 'select', options: ['REGULAR', 'RAGLAN'], autoAddon: { 'RAGLAN': 33 } }
        ]
    }
};

// =============================================
// RENDER DYNAMIC SPEC FIELDS
// =============================================
// Called when garment type changes. Renders the relevant spec fields
// and wires up auto-addon checkbox toggling.
window.sublimation_renderSpecs = function () {
    var container = document.getElementById('sublimation_specsFields');
    if (!container) return;

    var garmentSelect = document.getElementById('sublimation_garmentSelect');
    if (!garmentSelect || garmentSelect.selectedIndex <= 0) {
        container.innerHTML = '<p class="text-muted small mb-0">Select a garment type to see specifications.</p>';
        return;
    }

    var garmentName = garmentSelect.options[garmentSelect.selectedIndex].textContent;
    var upperName = garmentName.toUpperCase();

    // Find matching spec group
    var matchingGroup = null;
    var groupKey = null;
    for (var key in garmentSpecMap) {
        if (!garmentSpecMap.hasOwnProperty(key)) continue;
        var group = garmentSpecMap[key];
        for (var kwIdx = 0; kwIdx < group.nameKeywords.length; kwIdx++) {
            if (upperName.indexOf(group.nameKeywords[kwIdx].toUpperCase()) !== -1) {
                matchingGroup = group;
                groupKey = key;
                break;
            }
        }
        if (matchingGroup) break;
    }

    if (!matchingGroup) {
        container.innerHTML = '<p class="text-muted small mb-0">No specific fields for this garment type.</p>';
        return;
    }

    var fields = matchingGroup.fields;
    
    // Check if this is Jersey Up and Down (two-column layout)
    var isJerseyUpDown = (groupKey === 'jersey' && upperName.indexOf('UP AND DOWN') !== -1);
    
    var allFields = fields;
    
    // Helper: render one field row (grouped card style)
    function renderFieldRow(f, prefix) {
        var fid = (prefix || '') + f.id;
        var row = '<div class="d-flex align-items-center mb-2 p-2 rounded" style="background:#f8f9fa;border:1px solid #e9ecef;" id="specRow_' + fid + '">';
        row += '    <label class="form-label small fw-semibold mb-0 me-2" style="min-width:110px;white-space:nowrap;">' + f.label + '</label>';
        if (f.type === 'select') {
            row += '    <div class="flex-grow-1"><select class="form-control form-control-sm" id="' + fid + '">';
            row += '        <option value="">-- Select --</option>';
            for (var oo = 0; oo < f.options.length; oo++) {
                row += '        <option value="' + f.options[oo] + '">' + f.options[oo] + '</option>';
            }
            row += '    </select></div>';
        } else {
            row += '    <div class="flex-grow-1"><input type="text" class="form-control form-control-sm" id="' + fid + '" placeholder="Enter ' + f.label.toLowerCase() + '"></div>';
        }
        row += '</div>';
        return row;
    }
    
    // Helper: wire up auto-addon for a group of fields
    function wireAutoAddon(fieldList, prefix) {
        for (var w = 0; w < fieldList.length; w++) {
            var fd = fieldList[w];
            if (!fd.autoAddon) continue;
            var el = document.getElementById((prefix || '') + fd.id);
            if (!el) continue;
            (function (ff, pfx) {
                el.onchange = function () {
                    sublimation_handleSpecAddon(ff, this.value);
                    sublimation_calculateTotal();
                    sublimation_autoGenerateDescription();
                };
            })(fd, prefix || '');
        }
    }
    
    var html = '';
    
    if (isJerseyUpDown) {
        // Two-column layout: Jersey Up on left, Jersey Short on right
        html += '<div class="row">';
        
        // LEFT COLUMN: Jersey Up
        html += '<div class="col-md-6 border-end">';
        html += '<h6 class="fw-bold mb-3" style="color:#667eea;">JERSEY UP</h6>';
        for (var ui = 0; ui < fields.length; ui++) {
            html += renderFieldRow(fields[ui], '');
        }
        html += '<div class="mt-3">';
        html += renderFieldRow({ id: 'spec_armsleeve', label: 'Armsleeve', type: 'select', options: ['SHORTSLEEVE', 'LONGSLEEVE'], autoAddon: { 'LONGSLEEVE': 28 } }, '');
        html += renderFieldRow({ id: 'spec_shoulder', label: 'Shoulder', type: 'select', options: ['REGULAR', 'RAGLAN'], autoAddon: { 'RAGLAN': 33 } }, '');
        html += '</div>';
        html += '</div>';
        
        // RIGHT COLUMN: Jersey Short
        html += '<div class="col-md-6">';
        html += '<h6 class="fw-bold mb-3" style="color:#667eea;">JERSEY SHORT</h6>';
        var shortGroup = garmentSpecMap['jersey-short'];
        if (shortGroup) {
            for (var si = 0; si < shortGroup.fields.length; si++) {
                html += renderFieldRow(shortGroup.fields[si], 'short_');
            }
        }
        html += '</div>';
        
        html += '</div>';
        
        container.innerHTML = html;
        
        // Wire auto-addon for both columns (including armsleeve/shoulder)
        wireAutoAddon(fields, '');
        // Wire armsleeve/shoulder manually since they're inline objects
        var alField = { id: 'spec_armsleeve', label: 'Armsleeve', type: 'select', options: ['SHORTSLEEVE', 'LONGSLEEVE'], autoAddon: { 'LONGSLEEVE': 28 } };
        var shField = { id: 'spec_shoulder', label: 'Shoulder', type: 'select', options: ['REGULAR', 'RAGLAN'], autoAddon: { 'RAGLAN': 33 } };
        wireAutoAddon([alField, shField], '');
        if (shortGroup) wireAutoAddon(shortGroup.fields, 'short_');
        
    } else {
        // Normal single-column rendering
        for (var i = 0; i < fields.length; i++) {
            html += renderFieldRow(fields[i], '');
        }
        
        // Add polo conditional fields (button/zipper)
        if (groupKey === 'polo') {
            var isButton = upperName.indexOf('BUTTON') !== -1;
            if (isButton) {
                html += '<div class="d-flex align-items-center mb-2 p-2 rounded" style="background:#f8f9fa;border:1px solid #e9ecef;" id="specRow_spec_buttonColor">';
                html += '    <label class="form-label small fw-semibold mb-0 me-2" style="min-width:110px;white-space:nowrap;">Button Color</label>';
                html += '    <div class="flex-grow-1"><select class="form-control form-control-sm" id="spec_buttonColor"><option value="">-- Select --</option><option value="BLACK">BLACK</option><option value="WHITE">WHITE</option></select></div>';
                html += '</div>';
            } else {
                html += '<div class="d-flex align-items-center mb-2 p-2 rounded" style="background:#f8f9fa;border:1px solid #e9ecef;" id="specRow_spec_zipperColor">';
                html += '    <label class="form-label small fw-semibold mb-0 me-2" style="min-width:110px;white-space:nowrap;">Zipper Color</label>';
                html += '    <div class="flex-grow-1"><select class="form-control form-control-sm" id="spec_zipperColor"><option value="">-- Select --</option><option value="BLACK">BLACK</option><option value="WHITE">WHITE</option></select></div>';
                html += '</div>';
            }
        }
        
        container.innerHTML = html;
        
        // Wire up auto-addon change handlers
        wireAutoAddon(fields, '');
        
        // Also wire button/zipper
        var btnZipperIds = ['spec_buttonColor', 'spec_zipperColor'];
        for (var bz = 0; bz < btnZipperIds.length; bz++) {
            var bzEl = document.getElementById(btnZipperIds[bz]);
            if (bzEl) {
                bzEl.onchange = function () { sublimation_calculateTotal(); sublimation_autoGenerateDescription(); };
            }
        }
    }
    
    sublimation_calculateTotal();
    sublimation_autoGenerateDescription();
};

// =============================================
// AUTO-ADDON HANDLER
// =============================================
// Checks or unchecks a part checkbox based on spec selection.
window.sublimation_handleSpecAddon = function (field, selectedValue) {
    // Uncheck AND re-enable ALL part IDs mentioned in this field's autoAddon map
    for (var val in field.autoAddon) {
        if (!field.autoAddon.hasOwnProperty(val)) continue;
        var partId = field.autoAddon[val];
        var cb = document.querySelector('.sublimation-part-checkbox[value="' + partId + '"]');
        if (cb) {
            cb.checked = false;
            cb.disabled = false;
        }
    }

    // Check and LOCK the one matching the selected value
    if (selectedValue && field.autoAddon[selectedValue] !== undefined) {
        var targetPartId = field.autoAddon[selectedValue];
        var targetCb = document.querySelector('.sublimation-part-checkbox[value="' + targetPartId + '"]');
        if (targetCb) {
            targetCb.checked = true;
            targetCb.disabled = true; // Prevent manual uncheck
        }
    }
};

// Calculate total (quoted in both tabs)
window.sublimation_calculateTotal = function() {
    var garmentSelect = document.getElementById('sublimation_garmentSelect');
    var fabricSelect = document.getElementById('sublimation_fabricSelect');
    
    // Get prices
    var garmentPrice = 0;
    var garmentName = '';
    if (garmentSelect && garmentSelect.selectedIndex > 0) {
        var garmentOpt = garmentSelect.options[garmentSelect.selectedIndex];
        garmentPrice = parseFloat(garmentOpt.dataset.price) || 0;
        garmentName = garmentOpt.textContent.split(' - ')[0];
    }
    
    var fabricPrice = 0;
    var fabricName = '';
    if (fabricSelect && fabricSelect.selectedIndex > 0) {
        var fabricOpt = fabricSelect.options[fabricSelect.selectedIndex];
        fabricPrice = parseFloat(fabricOpt.dataset.price) || 0;
        fabricName = fabricOpt.textContent.split(' - ')[0];
    }
    
    // Get parts total
    var partsPrice = 0;
    var partsNames = [];
    var checkboxes = document.querySelectorAll('.sublimation-part-checkbox:checked');
    checkboxes.forEach(function(cb) {
        partsPrice += parseFloat(cb.dataset.price) || 0;
        var label = cb.nextElementSibling;
        if (label) partsNames.push(label.textContent.split(' (+')[0]);
    });
    
    // Get sizes pricing data (loaded from API)
    var sizesData = window.sublimation_sizesData || {};
    // Helper: flexible size name match (form uses 5XLARGE, pricing may use 5XL)
    function getSizePrice(name) {
        if (sizesData[name] !== undefined) return sizesData[name];
        // Try without ARGE suffix (e.g., 5XLARGE -> 5XL)
        if (name.endsWith('ARGE')) {
            var alt = name.slice(0, -4);
            if (sizesData[alt] !== undefined) return sizesData[alt];
        }
        // Try without LARGE suffix (e.g., XLARGE -> XL)
        if (name.endsWith('LARGE')) {
            var alt2 = name.slice(0, -5);
            if (sizesData[alt2] !== undefined) return sizesData[alt2];
        }
        return 0;
    }
    
    // Calculate per-size pricing with size markup
    var baseUnitPrice = garmentPrice + fabricPrice + partsPrice;
    var totalQty = 0;
    var grandTotal = 0;
    var sizePriceBreakdownItems = [];
    
    // By-size mode: iterate each size input
    var sizeInputs = document.querySelectorAll('#sizes .size-qty, #sublimationModalBody .size-qty');
    var rosterRows = document.querySelectorAll('#sublimation_rosterBody tr.roster-row');
    var hasRosterRows = rosterRows.length > 0;
    
    // Always count size-qty inputs (manual entries in By Sizes tab)
    sizeInputs.forEach(function(inp) {
        var qty = parseInt(inp.value) || 0;
        if (qty > 0) {
            var sizeName = (inp.dataset.size || '').toUpperCase();
            var sizePrice = getSizePrice(sizeName);
            var rowTotal = (baseUnitPrice + sizePrice) * qty;
            grandTotal += rowTotal;
            totalQty += qty;
            sizePriceBreakdownItems.push({ size: sizeName, qty: qty, sizePrice: sizePrice, rowTotal: rowTotal });
        }
    });
    
    // Roster mode: each row counts as 1 qty, use each row's selected size
    if (hasRosterRows) {
        var rosterRows = document.querySelectorAll('#sublimation_rosterBody tr.roster-row');
        rosterRows.forEach(function(row) {
        var sizeSelect = row.querySelector('.roster-size');
        if (sizeSelect) {
            var sizeName = (sizeSelect.value || '').toUpperCase();
            var sizePrice = getSizePrice(sizeName);
            // Read QTY from roster-number hidden input if available
            var qtyInput = row.querySelector('.roster-number');
            var rosterQty = qtyInput ? (parseInt(qtyInput.value) || 1) : 1;
            if (rosterQty < 1) rosterQty = 1;
            grandTotal += (baseUnitPrice + sizePrice) * rosterQty;
            totalQty += rosterQty;
            sizePriceBreakdownItems.push({ size: sizeName, qty: rosterQty, sizePrice: sizePrice, rowTotal: (baseUnitPrice + sizePrice) * rosterQty });
        } else {
            grandTotal += baseUnitPrice;
            totalQty += 1;
        }
    });
    }
    
    // Helper to format price
    function fmt(price) {
        if (price > 0) return '\u20B1' + price.toFixed(2);
        return '-\u20B10.00';
    }
    
    // Build size markup breakdown HTML
    var sizeBreakdownHTML = '';
    if (sizePriceBreakdownItems.length > 0) {
        var seenSizes = {};
        sizePriceBreakdownItems.forEach(function(si) {
            var key = si.size || '??';
            if (!seenSizes[key]) {
                seenSizes[key] = { totalQty: 0, sizePrice: si.sizePrice };
            }
            seenSizes[key].totalQty += si.qty;
        });
        for (var sz in seenSizes) {
            if (seenSizes[sz].sizePrice > 0) {
                sizeBreakdownHTML += '<div class="d-flex justify-content-between small" style="padding-left:12px;">' +
                    '<span>' + sz + ' (x' + seenSizes[sz].totalQty + ') × \u20B1' + seenSizes[sz].sizePrice.toFixed(2) + '</span>' +
                    '<span>\u20B1' + (seenSizes[sz].sizePrice * seenSizes[sz].totalQty).toFixed(2) + '</span></div>';
            }
        }
        if (sizeBreakdownHTML) {
            sizeBreakdownHTML = '<div class="d-flex justify-content-between small fw-bold"><span>Size Markup:</span><span></span></div>' + sizeBreakdownHTML;
        }
    }
    
    // Update order details tab summary
    var set = function(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val;
    };
    
    set('sublimation_garmentPriceDisplay', fmt(garmentPrice));
    set('sublimation_fabricPriceDisplay', fmt(fabricPrice));
    
    // Build individual parts breakdown for both price cards
    var partsBreakdownHTML = '';
    checkboxes.forEach(function(cb) {
        var label = cb.nextElementSibling;
        if (label) {
            var pn = label.textContent.split(' (+')[0];
            var pp = parseFloat(cb.dataset.price) || 0;
            partsBreakdownHTML += '<div class="d-flex justify-content-between small" style="padding-left:12px;">' +
                '<span>' + pn + '</span>' +
                '<span>\u20B1' + pp.toFixed(2) + '</span></div>';
        }
    });
    if (partsBreakdownHTML) {
        partsBreakdownHTML += '<hr class="my-1">';
        partsBreakdownHTML += '<div class="d-flex justify-content-between small fw-bold">' +
            '<span>Parts Total:</span>' +
            '<span>\u20B1' + partsPrice.toFixed(2) + '</span></div>';
    }
    var partsEl = document.getElementById('sublimation_partsBreakdown');
    if (partsEl) partsEl.innerHTML = partsBreakdownHTML + sizeBreakdownHTML;
    document.getElementById('pricingTab_partsBreakdown').innerHTML = partsBreakdownHTML;
    document.getElementById('pricingTab_sizeBreakdown').innerHTML = sizeBreakdownHTML;

    set('sublimation_unitPriceDisplay', '\u20B1' + baseUnitPrice.toFixed(2));
    set('sublimation_totalQtyDisplay', totalQty);
    set('sublimation_totalDisplay', '\u20B1' + grandTotal.toFixed(2));
    
    // Update pricing tab summary with names + prices
    var garmentEl = document.getElementById('sublimation_garmentSelect');
    var garmentName = garmentEl && garmentEl.selectedIndex > 0 ? garmentEl.options[garmentEl.selectedIndex].textContent : 'Garment';
    var fabricEl = document.getElementById('sublimation_fabricSelect');
    var fabricName = fabricEl && fabricEl.selectedIndex > 0 ? fabricEl.options[fabricEl.selectedIndex].textContent : 'Fabric';
    document.getElementById('pricingTab_garmentLabel').textContent = garmentName + ':';
    document.getElementById('pricingTab_fabricLabel').textContent = fabricName + ':';
    set('pricingTab_garmentPrice', fmt(garmentPrice));
    set('pricingTab_fabricPrice', fmt(fabricPrice));
    // Individual parts already set via sublimation_partsBreakdown / pricingTab_partsBreakdown above
    set('pricingTab_unitPrice', '\u20B1' + baseUnitPrice.toFixed(2));
    set('pricingTab_totalQty', totalQty);
    set('pricingTab_grandTotal', '\u20B1' + grandTotal.toFixed(2));
};

// Add to cart
window.sublimation_addItemToCart = function() {
    var projectName = document.getElementById('sublimation_projectName');
    var description = document.getElementById('sublimation_description');
    var garmentSelect = document.getElementById('sublimation_garmentSelect');
    var fabricSelect = document.getElementById('sublimation_fabricSelect');
    /* Department auto-assigned; sublimation uses Class dept */
    var deptSelect = null;
    var notes = document.getElementById('sublimation_notes');
    
    // Validate basics — ALL required fields
    if (!projectName || !projectName.value.trim()) {
        alert('Please enter a Project Name.');
        if (projectName) projectName.focus();
        var tab = document.querySelector('#sublimationTab .nav-link');
        if (tab && bootstrap && bootstrap.Tab) new bootstrap.Tab(tab).show();
        return;
    }
    
    if (!fabricSelect || fabricSelect.selectedIndex <= 0) {
        alert('Please select a Fabric type (Order Details tab).');
        var tab = document.querySelector('#sublimationTab .nav-link');
        if (tab && bootstrap && bootstrap.Tab) new bootstrap.Tab(tab).show();
        return;
    }
    
    if (!garmentSelect || garmentSelect.selectedIndex <= 0) {
        alert('Please select a Garment type (Specifications tab).');
        var specsTab = document.querySelector('#specs-tab');
        if (specsTab && bootstrap && bootstrap.Tab) new bootstrap.Tab(specsTab).show();
        return;
    }
    
    var designer = document.getElementById('sublimation_designer');
    if (!designer || !designer.value.trim()) {
        alert('Please enter a Designer name (Order Details tab).');
        var tab = document.querySelector('#sublimationTab .nav-link');
        if (tab && bootstrap && bootstrap.Tab) new bootstrap.Tab(tab).show();
        return;
    }
    
    var dateNeeded = document.getElementById('sublimation_dateNeeded');
    if (!dateNeeded || !dateNeeded.value.trim()) {
        alert('Please enter a Date Needed (Order Details tab).');
        var tab = document.querySelector('#sublimationTab .nav-link');
        if (tab && bootstrap && bootstrap.Tab) new bootstrap.Tab(tab).show();
        return;
    }
    
    // Validate mock-up image
    var mockupImg = document.getElementById('mockupPreview');
    if (!mockupImg || !mockupImg.src || mockupImg.style.display === 'none' || !mockupImg.src.trim()) {
        alert('Please upload a mock-up design image.');
        var tab = document.querySelector('#sublimationTab .nav-link');
        if (tab && bootstrap && bootstrap.Tab) new bootstrap.Tab(tab).show();
        return;
    }
    
    // Validate ALL visible specification fields
    var specContainer = document.getElementById('sublimation_specsFields');
    if (specContainer) {
        var specSelects = specContainer.querySelectorAll('select');
        var specInputs = specContainer.querySelectorAll('input[type="text"]');
        var hasEmptySpec = false;
        specSelects.forEach(function(sel) {
            if (sel.selectedIndex <= 0) hasEmptySpec = true;
        });
        specInputs.forEach(function(inp) {
            if (!inp.value.trim()) hasEmptySpec = true;
        });
        if (hasEmptySpec) {
            alert('Please fill in ALL specification fields (Specifications tab).');
            var specsTab = document.querySelector('#specs-tab');
            if (specsTab && bootstrap && bootstrap.Tab) new bootstrap.Tab(specsTab).show();
            return;
        }
    }
    
    // Validate Print Order Slip was viewed
    if (!window.sublimation_orderSlipViewed) {
        alert('Please click "Print Order Slip" first before confirming the order.');
        return;
    }
    
    // Department auto-assigned; sublimation uses Class department
    var department = 'class';
    
    // Validate sizes - at least one size must have qty > 0
    var totalQty = sublimation_getTotalQty();
    if (totalQty <= 0) {
        alert('Please enter at least 1 quantity in the Sizes tab.');
        var sizesTab = document.querySelector('#sizes-tab');
        if (sizesTab && bootstrap && bootstrap.Tab) new bootstrap.Tab(sizesTab).show();
        return;
    }
    
    // Get selected values
    var garmentOpt = garmentSelect.options[garmentSelect.selectedIndex];
    var garmentName = garmentOpt.textContent.split(' - ')[0];
    var garmentPrice = parseFloat(garmentOpt.dataset.price) || 0;
    
    var fabricOpt = fabricSelect.options[fabricSelect.selectedIndex];
    var fabricName = fabricOpt.textContent.split(' - ')[0];
    var fabricPrice = parseFloat(fabricOpt.dataset.price) || 0;
    
    // Get selected parts
    var partsPrice = 0;
    var partsList = [];
    var checkboxes = document.querySelectorAll('.sublimation-part-checkbox:checked');
    checkboxes.forEach(function(cb) {
        partsPrice += parseFloat(cb.dataset.price) || 0;
        var label = cb.nextElementSibling;
        if (label) partsList.push(label.textContent.split(' (+')[0]);
    });
    
    var baseUnitPrice = garmentPrice + fabricPrice + partsPrice;
    var sizesData = window.sublimation_sizesData || {};
    // Helper: flexible size name match (form uses 5XLARGE, pricing may use 5XL)
    function getSizeCartPrice(name) {
        if (sizesData[name] !== undefined) return sizesData[name];
        if (name.endsWith('ARGE')) {
            var alt = name.slice(0, -4);
            if (sizesData[alt] !== undefined) return sizesData[alt];
        }
        if (name.endsWith('LARGE')) {
            var alt2 = name.slice(0, -5);
            if (sizesData[alt2] !== undefined) return sizesData[alt2];
        }
        return 0;
    }
    var totalPrice = 0;
    
    // Collect size data (by-qty mode) + calculate per-size pricing
    var sizeData = [];
    var sizeInputs = document.querySelectorAll('#sizes .size-qty');
    sizeInputs.forEach(function(inp) {
        var q = parseInt(inp.value) || 0;
        if (q > 0) {
            var sizeName = (inp.dataset.size || '').toUpperCase();
            var sizePrice = getSizeCartPrice(sizeName);
            sizeData.push({ size: inp.dataset.size, quantity: q, price: sizePrice });
            totalPrice += (baseUnitPrice + sizePrice) * q;
        }
    });
    
    // Roster rows: use roster-number value (not just 1 per row)
    var rosterRows = document.querySelectorAll('#sublimation_rosterBody tr.roster-row');
    rosterRows.forEach(function(row) {
        var sizeSelect = row.querySelector('.roster-size');
        var qtyInput = row.querySelector('.roster-number');
        var rosterQty = parseInt(qtyInput ? qtyInput.value : '') || 1;
        if (rosterQty < 1) rosterQty = 1;
        if (sizeSelect) {
            var sizeName = (sizeSelect.value || '').toUpperCase();
            var sizePrice = getSizeCartPrice(sizeName);
            totalPrice += (baseUnitPrice + sizePrice) * rosterQty;
        } else {
            totalPrice += baseUnitPrice * rosterQty;
        }
    });
    
    // Collect roster data (per-person mode)
    // Collect roster data (per-person mode) - SAVE ALL COLUMNS
    var rosterData = [];
    var rosterRows = document.querySelectorAll('#sublimation_rosterBody tr.roster-row');
    rosterRows.forEach(function(row) {
        var numCell = row.querySelector('td:first-child');
        var nameInput = row.querySelector('.roster-name');
        var numInput = row.querySelector('.roster-number');
        var sizeInput = row.querySelector('.roster-size');
        
        // Collect ALL hidden inputs with their header names (for Excel import)
        // Use array of [header, value] pairs to preserve column order (object keys may reorder)
        var allHidden = row.querySelectorAll('input[type="hidden"]');
        var columns = [];
        allHidden.forEach(function(inp) {
            var header = inp.getAttribute('data-header') || '';
            var val = inp.value;
            if (header) columns.push([header, val]);
        });
        
        rosterData.push({
            number: numInput ? numInput.value : (numCell ? numCell.textContent.trim() : ''),
            name: nameInput ? nameInput.value : '',
            size: sizeInput ? sizeInput.value : '',
            columns: columns
        });
    });
    
    // Collect mockup image data URL
    var mockupImg = document.getElementById('mockupPreview');
    var mockupDataUrl = (mockupImg && mockupImg.src && mockupImg.style.display !== 'none') ? mockupImg.src : '';

    // Collect spec data dynamically from rendered fields
    var specData = {};
    var specFieldIds = ['spec_neckRibbingColor','spec_neckTape','spec_sizeLabel','spec_cuffs','spec_slit','spec_pocket','spec_collar','spec_neckShape','spec_cutType','spec_inner','spec_buttonColor','spec_zipperColor','spec_innerStr','spec_jersey','spec_defaultDesign','spec_armsleeve','spec_shoulder','short_spec_inner','short_spec_pocket','short_spec_armsleeve','short_spec_shoulder','short_spec_neckRibbingColor','short_spec_neckTape','short_spec_sizeLabel','short_spec_cuffs','short_spec_slit','short_spec_pocket','short_spec_collar','short_spec_neckShape','short_spec_cutType','short_spec_buttonColor','short_spec_zipperColor','short_spec_jersey','short_spec_defaultDesign'];
    specFieldIds.forEach(function (sid) {
        var el = document.getElementById(sid);
        if (el) {
            specData[sid.replace('spec_','')] = el.value ? el.value.trim() : '';
        }
    });
    
    function getVal(id) {
        var el = document.getElementById(id);
        if (!el) return '';
        return el.value ? el.value.trim() : '';
    }
    
    // Build the item name
    var itemName = 'FS: ' + (projectName.value.trim() || '?') + ' - ' + garmentName;
    if (fabricName) itemName += ' (' + fabricName + ')';
    if (partsList.length > 0) itemName += ' +' + partsList.join('+');
    
    // Create cart item
    var sublimationItem = {
        id: Date.now(),
        productType: 'cutting',
        department: department,
        name: itemName,
        quantity: totalQty,
        unitPrice: baseUnitPrice,
        totalPrice: totalPrice,
        date_needed: getVal('sublimation_dateNeeded'),
        notes: notes ? notes.value.trim() : '',
        timestamp: new Date().toISOString(),
        sublimationForm: {
            projectName: projectName.value.trim(),
            description: description.value.trim(),
            designer: getVal('sublimation_designer'),
            /* PIC field removed */
            dateNeeded: getVal('sublimation_dateNeeded'),
            garment: { name: garmentName, price: garmentPrice, priceId: garmentSelect.value },
            fabric: { name: fabricName, price: fabricPrice, priceId: fabricSelect.value },
            parts: partsList.map(function(pn, idx) {
                return { name: pn, price: parseFloat(checkboxes[idx]?.dataset.price) || 0 };
            }),
            sizes: sizeData,
            roster: rosterData,
            mockup: mockupDataUrl,
            specifications: specData
        }
    };
    
    selectedItems.push(sublimationItem);
    updateSelectedItemsDisplay();
    updateOrderSummary();
    
    // Reset form
    sublimation_openModal();
    
    // Close modal
    var modalEl = document.getElementById('productModal');
    if (modalEl) {
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
    
    showToast('FS order added to cart!', 'success');
};

// === ROSTER FUNCTIONS ===

// Toggle between By Size and Per Person modes
window.sublimation_toggleSizeMode = function() {
    var mode = document.querySelector('input[name="sublimationSizeMode"]:checked');
    if (!mode) return;
    var isRoster = mode.value === 'roster';
    document.getElementById('sublimationSizeByQty').style.display = isRoster ? 'none' : '';
    document.getElementById('sublimationSizeRoster').style.display = isRoster ? '' : 'none';
    sublimation_calculateTotal();
};

// Add a single roster row
window.sublimation_addRosterRow = function() {
    var nameEl = document.getElementById('roster_newName');
    var numEl = document.getElementById('roster_newNumber');
    var sizeEl = document.getElementById('roster_newSize');
    
    var name = nameEl ? nameEl.value.trim() : '';
    var number = numEl ? numEl.value.trim() : '';
    var size = sizeEl ? sizeEl.value : '';
    
    if (!name) {
        alert('Please enter a name.');
        if (nameEl) nameEl.focus();
        return;
    }
    if (!size) {
        alert('Please select a size.');
        if (sizeEl) sizeEl.focus();
        return;
    }
    
    var tbody = document.getElementById('sublimation_rosterBody');
    if (!tbody) return;
    
    // Hide empty state
    var empty = document.getElementById('sublimation_rosterEmpty');
    if (empty) empty.style.display = 'none';
    
    // Determine dynamic column positions from header row
    var headerRow = document.getElementById('sublimation_rosterHeader');
    var displayHeaders = [];
    if (headerRow) {
        var ths = headerRow.querySelectorAll('th');
        for (var i = 1; i < ths.length - 1; i++) { // skip #, skip last (empty Remove + Qty)
            var txt = (ths[i].textContent || '').trim().toUpperCase();
            displayHeaders.push(txt);
        }
        // The last th before the Remove button is Qty — remove it if present
        if (displayHeaders.length > 0 && displayHeaders[displayHeaders.length - 1] === 'QTY') {
            displayHeaders.pop();
        }
    }
    
    // If no dynamic headers found or table was reset, use default structure
    if (displayHeaders.length === 0) {
        displayHeaders = ['NAME', 'SIZE'];
    }
    
    // Find name column index and size column index
    var nameIdx = -1;
    var sizeIdx = -1;
    displayHeaders.forEach(function(h, i) {
        if (h.indexOf('NAME') >= 0 && nameIdx < 0) nameIdx = i;
        if (h.indexOf('SIZE') >= 0 && sizeIdx < 0) sizeIdx = i;
    });
    if (nameIdx < 0) nameIdx = 0; // default to first column
    if (sizeIdx < 0) sizeIdx = displayHeaders.length - 1; // default to last
    
    var row = document.createElement('tr');
    row.className = 'roster-row';
    var rowNum = tbody.querySelectorAll('tr.roster-row').length + 1;
    
    var cellsHtml = '<td class="text-center align-middle">' + rowNum + '</td>';
    displayHeaders.forEach(function(h, i) {
        cellsHtml += '<td class="align-middle">';
        if (i === nameIdx) {
            cellsHtml += _escHtml(name)
                + '<input type="hidden" class="roster-name" value="' + _escHtml(name) + '">'
                + '<input type="hidden" class="roster-number" value="' + _escHtml(number) + '">';
        } else if (i === sizeIdx) {
            cellsHtml += size + '<input type="hidden" class="roster-size" value="' + size + '">';
        } else {
            cellsHtml += '&nbsp;';
        }
        cellsHtml += '</td>';
    });
    cellsHtml += '<td class="text-center align-middle">1</td>'
        + '<td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="sublimation_removeRosterRow(this)" title="Remove"><i class="fas fa-times"></i></button></td>';
    
    row.innerHTML = cellsHtml;
    tbody.appendChild(row);
    
    // Clear inputs
    if (numEl) numEl.value = '';
    if (nameEl) nameEl.value = '';
    if (sizeEl) sizeEl.selectedIndex = 0;
    if (nameEl) nameEl.focus();
    
    // Update counts
    sublimation_updateRosterCount();
    sublimation_calculateTotal();
};

// Remove a roster row
window.sublimation_removeRosterRow = function(btn) {
    var row = btn.closest('tr');
    if (row) {
        row.remove();
        // Renumber
        var tbody = document.getElementById('sublimation_rosterBody');
        if (tbody) {
            var remaining = tbody.querySelectorAll('tr.roster-row');
            remaining.forEach(function(r, idx) {
                var numTd = r.querySelector('td:first-child');
                if (numTd) numTd.textContent = (idx + 1).toString();
            });
            if (remaining.length === 0) {
                var empty = document.getElementById('sublimation_rosterEmpty');
                if (empty) empty.style.display = '';
            }
        }
        sublimation_updateRosterCount();
        sublimation_calculateTotal();
    }
};

// Update roster count display
window.sublimation_updateRosterCount = function() {
    var countEl = document.getElementById('sublimation_rosterCount');
    var tbody = document.getElementById('sublimation_rosterBody');
    if (countEl && tbody) {
        countEl.textContent = tbody.querySelectorAll('tr.roster-row').length;
    }
};

// Toggle bulk paste textarea
window.sublimation_toggleBulkPaste = function() {
    var area = document.getElementById('sublimationBulkPasteArea');
    if (area) {
        area.style.display = area.style.display === 'none' ? '' : 'none';
    }
};

// Bulk paste: parse lines like "Name,Size" or "Number,Name,Size"
window.sublimation_bulkPaste = function() {
    var textarea = document.getElementById('sublimation_bulkPasteInput');
    if (!textarea || !textarea.value.trim()) {
        alert('Paste your list first.');
        return;
    }
    
    var lines = textarea.value.trim().split('\n');
    var added = 0;
    
    lines.forEach(function(line) {
        line = line.trim();
        if (!line) return;
        // Try Number,Name,Size or Name,Size
        var parts = line.split(',').map(function(s) { return s.trim(); });
        var name, number, size;
        
        if (parts.length === 3) {
            number = parts[0];
            name = parts[1];
            size = parts[2].toUpperCase();
        } else if (parts.length === 2) {
            number = '';
            name = parts[0];
            size = parts[1].toUpperCase();
        } else {
            return; // skip malformed
        }
        
        if (!name || !size) return;
        
        // Set fields and add
        var nameEl = document.getElementById('roster_newName');
        var numEl = document.getElementById('roster_newNumber');
        var sizeEl = document.getElementById('roster_newSize');
        if (nameEl) nameEl.value = name;
        if (numEl) numEl.value = number;
        if (sizeEl) { 
            // Try to match size, case-insensitive
            for (var i = 0; i < sizeEl.options.length; i++) {
                if (sizeEl.options[i].value.toUpperCase() === size) {
                    sizeEl.selectedIndex = i;
                    break;
                }
            }
        }
        sublimation_addRosterRow();
        added++;
    });
    
    // Clear textarea
    textarea.value = '';
    var area = document.getElementById('sublimationBulkPasteArea');
    if (area) area.style.display = 'none';
    
    showToast(added + ' person' + (added > 1 ? 's' : '') + ' added to roster!', 'success');
};

// Normalize size names from Excel to standard codes
function normalizeSize(raw) {
    raw = raw.toUpperCase().trim();
    // Handle "SMALL", "MEDIUM", "LARGE", "XLARGE", "2XLARGE", "5XLARGE" etc.
    var map = {
        'SMALL': 'S',
        'MEDIUM': 'M',
        'LARGE': 'L',
        'XLARGE': 'XL',
        'EXTRA LARGE': 'XL',
        'EXTRA-LARGE': 'XL',
        'EXTRA_SMALL': 'XS',
        'EXTRA SMALL': 'XS',
        'EXTRA-SMALL': 'XS',
        'XSMALL': 'XS',
        'X-SMALL': 'XS',
        'X LARGE': 'XL',
        'X-LARGE': 'XL',
        '2XLARGE': '2XL',
        '2X LARGE': '2XL',
        '2X-LARGE': '2XL',
        '3XLARGE': '3XL',
        '3X LARGE': '3XL',
        '3X-LARGE': '3XL',
        '4XLARGE': '4XL',
        '4X LARGE': '4XL',
        '4X-LARGE': '4XL',
        '5XLARGE': '5XL',
        '5X LARGE': '5XL',
        '5X-LARGE': '5XL',
        '6XLARGE': '6XL',
        '7XLARGE': '7XL',
        '8XLARGE': '8XL'
    };
    if (map[raw]) return map[raw];
    
    // Catch-alls: if ends with 'ARGE' or 'LARGE', strip the suffix
    if (raw.endsWith('ARGE')) return raw.slice(0, -4);  // XLARGE -> XL, 5XLARGE -> 5XL
    if (raw.endsWith('LARGE')) return raw.slice(0, -5); // X-LARGE -> X- (fallback)
    if (raw.endsWith('SMALL')) return raw.slice(0, -5) + 'S';  // XSMALL -> XS
    
    return raw;
}

// Upload Excel file → show column mapping dialog
var sublimation_excelData = null; // stores parsed rows for later
var sublimation_excelHeaders = null; // stores headers for column mapping

window.sublimation_uploadExcel = function(event) {
    var file = event.target.files[0];
    if (!file) return;
    
    function loadAndParse() {
        var reader = new FileReader();
        reader.onload = function(e) {
            try {
                var data = new Uint8Array(e.target.result);
                var workbook = XLSX.read(data, {type: 'array'});
                var firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                var json = XLSX.utils.sheet_to_json(firstSheet, {header: 1, defval: ''});
                
                if (json.length < 1) {
                    showToast('Excel file is empty.', 'warning');
                    return;
                }
                
                // First row = headers
                var headers = json[0].map(function(h) { return String(h).trim(); });
                sublimation_excelHeaders = headers;
                var rows = json.slice(1).filter(function(r) {
                    return r.some(function(cell) { return String(cell).trim() !== ''; });
                });
                
                if (rows.length < 1) {
                    showToast('No data rows found (header only).', 'warning');
                    return;
                }
                
                // Store for applyMapping
                sublimation_excelData = { headers: headers, rows: rows };
                
                // Auto-build roster from all Excel columns (no mapping dialog needed)
                sublimation_autoBuildFromExcel(headers, rows);
                
            } catch(err) {
                showToast('Error reading file: ' + err.message, 'danger');
            }
        };
        reader.readAsArrayBuffer(file);
    }
    
    // Load SheetJS if needed
    if (typeof XLSX === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js';
        script.onload = loadAndParse;
        script.onerror = function() {
            var script2 = document.createElement('script');
            script2.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
            script2.onload = loadAndParse;
            script2.onerror = function() {
                showToast('Failed to load Excel parser. Check internet connection.', 'danger');
            };
            document.head.appendChild(script2);
        };
        document.head.appendChild(script);
    } else {
        loadAndParse();
    }
};

// Mock-up image upload functions
window.sublimation_previewMockup = function(event) {
    var file = event.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var preview = document.getElementById('mockupPreview');
        var placeholder = document.getElementById('mockupPlaceholder');
        var fileInfo = document.getElementById('mockupFileInfo');
        var fileName = document.getElementById('mockupFileName');
        preview.src = e.target.result;
        preview.style.display = '';
        placeholder.style.display = 'none';
        fileName.textContent = file.name;
        fileInfo.style.display = '';
    };
    reader.readAsDataURL(file);
};

window.sublimation_removeMockup = function() {
    var preview = document.getElementById('mockupPreview');
    var placeholder = document.getElementById('mockupPlaceholder');
    var fileInfo = document.getElementById('mockupFileInfo');
    var upload = document.getElementById('mockupUpload');
    preview.src = '';
    preview.style.display = 'none';
    placeholder.style.display = '';
    fileInfo.style.display = 'none';
    upload.value = '';
};

// Format YYYY-MM-DD to DD/MM/YYYY
function formatDateDDMMYYYY(dateStr) {
    if (!dateStr) return '';
    var parts = dateStr.split('-');
    if (parts.length === 3) {
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }
    return dateStr;
}

// Print Order Slip (A4 landscape, excludes pricing)
window.sublimation_printOrderSlip = function() {
    // Collect form data
    function getVal(id) { var el = document.getElementById(id); return el ? el.value || '' : ''; }
    
    // Header fields
    document.getElementById('ps_projectName').textContent = getVal('sublimation_projectName');
    document.getElementById('ps_description').textContent = getVal('sublimation_description');
    var fabricEl = document.getElementById('sublimation_fabricSelect');
    var fabricText = fabricEl && fabricEl.selectedIndex > 0 ? fabricEl.options[fabricEl.selectedIndex].textContent : '';
    document.getElementById('ps_fabric').textContent = fabricText;
    document.getElementById('ps_designer').textContent = getVal('sublimation_designer');
    document.getElementById('ps_dateNeeded').textContent = formatDateDDMMYYYY(getVal('sublimation_dateNeeded'));
    
    // Total QTY
    var totalQty = 0;
    var totalSizeInputs = document.querySelectorAll('#sizes .size-qty');
    totalSizeInputs.forEach(function(inp) { totalQty += parseInt(inp.value) || 0; });
    var totalRosterRows = document.querySelectorAll('#sublimation_rosterBody tr.roster-row');
    totalRosterRows.forEach(function(row) {
        var qtyInput = row.querySelector('.roster-number');
        totalQty += qtyInput ? (parseInt(qtyInput.value) || 1) : 1;
    });
    document.getElementById('ps_qty').textContent = totalQty + ' PCS';
    
    // Mock-up image
    var mockupPreview = document.getElementById('mockupPreview');
    var mockupImg = document.getElementById('ps_mockupImg');
    var mockupPlaceholder = document.getElementById('ps_mockupPlaceholder');
    if (mockupPreview && mockupPreview.src && mockupPreview.style.display !== 'none') {
        mockupImg.src = mockupPreview.src;
        mockupImg.style.display = '';
        mockupPlaceholder.style.display = 'none';
    } else {
        mockupImg.style.display = 'none';
        mockupPlaceholder.style.display = '';
    }
    
    // Garment name for specs section header
    var garmentEl = document.getElementById('sublimation_garmentSelect');
    var garmentName = garmentEl && garmentEl.selectedIndex > 0 ? garmentEl.options[garmentEl.selectedIndex].textContent : '';
    
    // Parts / Colors table (left column) — main garment + parts
    var partsTbody = document.querySelector('#ps_specsParts tbody');
    if (!partsTbody) partsTbody = document.querySelector('#ps_specsParts');
    partsTbody.innerHTML = '<tr><th style="width:110px;">Part</th><th>Color/Details</th></tr>';
    
    // Garment type as first row
    partsTbody.innerHTML += '<tr><td>Garment</td><td>' + garmentName + '</td></tr>';
    
    // Collect spec field data for parts (front, back, sleeves, collar etc)
    var specPartsMap = {
        'neckRibbingColor': 'Neck Ribbing', 'neckTape': 'Neck Tape', 'cuffs': 'Cuffs',
        'slit': 'Slit', 'pocket': 'Pocket', 'collar': 'Collar', 'neckShape': 'Neck Shape',
        'cutType': 'Cut Type', 'inner': 'Inner', 'buttonColor': 'Button',
        'zipperColor': 'Zipper', 'innerStr': 'Inner String', 'jersey': 'Jersey',
        'defaultDesign': 'Design', 'armsleeve': 'Arm Sleeve', 'shoulder': 'Shoulder',
        'sizeLabel': 'Size Label'
    };
    
    var specOthersMap = {}; // specs that don't fit the main parts table
    
    for (var key in specPartsMap) {
        var label = specPartsMap[key];
        var el = document.getElementById('spec_' + key);
        var val = el ? (el.value || '').trim() : '';
        if (val) {
            partsTbody.innerHTML += '<tr><td>' + label + '</td><td>' + val + '</td></tr>';
        }
    }
    
    // Check short_spec variants (for shorts)
    var shortSpecKeys = ['inner','pocket','armsleeve','shoulder','neckRibbingColor','neckTape','sizeLabel','cuffs','slit','pocket','collar','neckShape','cutType','buttonColor','zipperColor','jersey','defaultDesign'];
    var existingLabels = Array.from(partsTbody.querySelectorAll('tr td:first-child')).map(function(td) {
        return td.textContent.trim().toLowerCase();
    });
    shortSpecKeys.forEach(function(key) {
        var el = document.getElementById('short_spec_' + key);
        var val = el ? (el.value || '').trim() : '';
        if (val) {
            var label = specPartsMap[key];
            if (label && existingLabels.indexOf(label.toLowerCase()) === -1) {
                partsTbody.innerHTML += '<tr><td>' + label + ' (Short)</td><td>' + val + '</td></tr>';
            }
        }
    });
    
    // Colors are already shown in DESCRIPTION field (left column), so skip adding a redundant "Colors" row here
    
    // Split parts rows into two columns (auto-detect: 10 rows → 5+5, 9 → 5+4, 8 → 4+4, etc.)
    var allPartsTrs = partsTbody.querySelectorAll('tr');
    var dataPartsRows = Array.from(allPartsTrs).slice(1); // skip header
    var splitMid = Math.ceil(dataPartsRows.length / 2);
    var splitHeader = '<tr><th style="width:110px;">Part</th><th>Color/Details</th></tr>';
    document.getElementById('ps_leftPartsCol').innerHTML = splitHeader + dataPartsRows.slice(0, splitMid).map(function(r) { return r.outerHTML; }).join('');
    document.getElementById('ps_rightPartsCol').innerHTML = splitHeader + dataPartsRows.slice(splitMid).map(function(r) { return r.outerHTML; }).join('');
    
    // Others table (right column)
    var othersTbody = document.querySelector('#ps_specsOthers tbody');
    if (!othersTbody) othersTbody = document.querySelector('#ps_specsOthers');
    othersTbody.innerHTML = '<tr><th style="width:120px;">Item</th><th>Details</th></tr>';
    
    // Populate others with specs not suitable for parts table
    var allSpecIds = ['spec_neckTape','spec_sizeLabel','spec_cuffs','spec_slit','spec_pocket','spec_collar','spec_neckShape','spec_cutType','spec_buttonColor','spec_zipperColor','spec_jersey','spec_defaultDesign','spec_innerStr'];
    allSpecIds.forEach(function(sid) {
        var el = document.getElementById(sid);
        if (el && el.value && el.value.trim()) {
            var labelText = sid.replace('spec_','').replace(/([A-Z])/g,' $1').replace(/^./, function(s){return s.toUpperCase();});
            // Skip if already in parts table
            var allPartsLabels = Array.from(partsTbody.querySelectorAll('tr td:first-child')).map(function(td) {
                return td.textContent.trim().toLowerCase();
            });
            var shortLabel = labelText.replace(' Neck','').replace(' Color','').trim().toLowerCase();
            var alreadyInParts = allPartsLabels.some(function(l) { return l.indexOf(shortLabel) >= 0; });
            if (!alreadyInParts) {
                othersTbody.innerHTML += '<tr><td>' + labelText + '</td><td>' + el.value.trim() + '</td></tr>';
            }
        }
    });
    
    // Also add parts that were checked in the parts checkboxes
    var checkboxes = document.querySelectorAll('.sublimation-part-checkbox:checked');
    if (checkboxes.length > 0) {
        othersTbody.innerHTML += '<tr><td>Parts Added</td><td>' + Array.from(checkboxes).map(function(cb) {
            var lbl = cb.nextElementSibling;
            return lbl ? lbl.textContent.split(' (+')[0] : '';
        }).filter(Boolean).join(', ') + '</td></tr>';
    }
    
    // Notes
    var notesVal = getVal('sublimation_notes');
    document.getElementById('ps_note').textContent = notesVal ? 'Note: ' + notesVal : '';
    
    // Roster table — DYNAMIC columns: reads directly from roster header + cells
    // (shows ALL Excel import columns, not just NAME/SIZE/QTY)
    var rosterBody = document.getElementById('ps_rosterBody');
    rosterBody.innerHTML = '';
    var rosterTable = document.getElementById('ps_rosterTable');
    var rosterRows = document.querySelectorAll('#sublimation_rosterBody tr.roster-row');
    if (rosterRows.length > 0) {
        // Read ALL headers from the roster table (skip first=# and last=remove)
        var headerRow = document.getElementById('sublimation_rosterHeader');
        var colHeaders = ['#'];
        if (headerRow) {
            var ths = headerRow.querySelectorAll('th');
            for (var ci = 1; ci < ths.length - 1; ci++) {
                colHeaders.push((ths[ci].textContent || '').trim());
            }
        } else {
            // Fallback if no header row
            colHeaders = ['#', 'NAME', 'SIZE', 'QTY'];
        }
        
        // Build dynamic thead in print slip table
        var thead = rosterTable.querySelector('thead');
        if (!thead) { thead = document.createElement('thead'); rosterTable.insertBefore(thead, rosterBody); }
        var hdrHtml = '<tr>';
        colHeaders.forEach(function(h) { hdrHtml += '<th>' + h + '</th>'; });
        hdrHtml += '</tr>';
        thead.innerHTML = hdrHtml;
        
        // Build data rows — read ALL td cells (skip first=row#, skip last=remove)
        var idx = 1;
        rosterRows.forEach(function(row) {
            var tds = row.querySelectorAll('td');
            if (tds.length < 2) return;
            var dataCells = [];
            for (var i = 1; i < tds.length - 1; i++) {
                dataCells.push(tds[i]);
            }
            
            // Check if any cell has data
            var hasData = dataCells.some(function(td) {
                return (td.textContent || '').trim() !== '';
            });
            if (!hasData) return;
            
            var rowHtml = '<tr><td>' + (idx++) + '</td>';
            for (var dc = 0; dc < dataCells.length; dc++) {
                var val = (dataCells[dc].textContent || '').trim();
                rowHtml += '<td style="text-align:left;">' + val + '</td>';
            }
            rowHtml += '</tr>';
            rosterBody.innerHTML += rowHtml;
        });
    } else {
        // Fallback: show sizes from by-qty inputs
        var thead = rosterTable.querySelector('thead');
        if (thead) thead.innerHTML = '';
        var sizeInputs = document.querySelectorAll('#sizes .size-qty');
        sizeInputs.forEach(function(inp) {
            var qty = parseInt(inp.value) || 0;
            if (qty > 0) {
                rosterBody.innerHTML += '<tr><td></td><td style="text-align:left;">' + (inp.dataset.size || '') + '</td><td></td><td>' + qty + '</td></tr>';
            }
        });
    }
    
    // Show and print
    var slip = document.getElementById('sublimation_printSlip');
    slip.style.display = 'block';
    window.print();
    slip.style.display = 'none';
    
    // Mark order slip as viewed (required before Confirm & Add to Cart)
    window.sublimation_orderSlipViewed = true;
    var btn = document.getElementById('sublimation_printOrderSlip');
    if (btn) {
        btn.classList.remove('btn-outline-info');
        btn.classList.add('btn-success');
        btn.innerHTML = '<i class="fas fa-check me-2"></i> Order Slip Printed';
    }
};

// Auto-build roster from ALL Excel columns (no manual mapping needed)
function sublimation_autoBuildFromExcel(headers, rows) {
    // Store for reference
    sublimation_excelHeaders = headers;
    sublimation_excelData = { headers: headers, rows: rows };
    
    var tbody = document.getElementById('sublimation_rosterBody');
    var theadRow = document.getElementById('sublimation_rosterHeader');
    
    // Auto-detect column roles from header text
    var nameCols = [];
    var sizeCol = -1;
    var numCol = -1;
    
    // Two-pass column detection: first find name + size, then qty (priority over #/number)
    headers.forEach(function(h, idx) {
        var hl = (h || '').toLowerCase().trim();
        if (hl.indexOf('name') >= 0 || hl === 'person' || hl === 'player' || hl === 'employee') {
            nameCols.push(idx);
        } else if (hl === 'size' || hl === 'sizes') {
            sizeCol = idx;
        }
    });
    // Second pass: QTY/QUANTITY/COUNT first, then #/NO/NUMBER/# as fallback
    headers.forEach(function(h, idx) {
        var hl = (h || '').toLowerCase().trim();
        if ((hl === 'qty' || hl === 'quantity' || hl === 'count') && numCol < 0) {
            numCol = idx;
        }
    });
    if (numCol < 0) {
        headers.forEach(function(h, idx) {
            var hl = (h || '').toLowerCase().trim();
            if ((hl === 'number' || hl === 'no' || hl === 'no.' || hl === '#' || hl === 'jersey') && numCol < 0) {
                numCol = idx;
            }
        });
    }
    
    // Build displayCols — ALL columns preserved, with cssClass for auto-detected roles
    var displayCols = [];
    headers.forEach(function(h, idx) {
        var cssClass = '';
        if (nameCols.indexOf(idx) >= 0) cssClass = 'roster-name';
        else if (idx === sizeCol) cssClass = 'roster-size';
        else if (idx === numCol) cssClass = 'roster-number';
        displayCols.push({header: h || '(Col ' + (idx + 1) + ')', colIdx: idx, cssClass: cssClass});
    });
    
    // Clear existing rows
    tbody.innerHTML = '';
    var empty = document.getElementById('sublimation_rosterEmpty');
    if (empty) empty.style.display = 'none';
    
    // Rebuild header with ALL Excel columns + fixed #, Qty, Remove
    var headerHtml = '<th style="width:50px">#</th>';
    displayCols.forEach(function(col) {
        headerHtml += '<th>' + _escHtml(col.header) + '</th>';
    });
    headerHtml += '<th style="width:40px"></th>';
    theadRow.innerHTML = headerHtml;
    
    var added = 0;
    rows.forEach(function(row) {
        // Skip empty rows
        var hasData = false;
        for (var ci = 0; ci < row.length; ci++) {
            if (row[ci] !== undefined && row[ci] !== null && String(row[ci]).trim() !== '') {
                hasData = true;
                break;
            }
        }
        if (!hasData) return;
        
        var tr = document.createElement('tr');
        tr.className = 'roster-row';
            
            var cellsHtml = '<td class="text-center align-middle">' + (added + 1) + '</td>';
            
            displayCols.forEach(function(col) {
                var rawVal = (col.colIdx >= 0 && row[col.colIdx] !== undefined && row[col.colIdx] !== null)
                    ? String(row[col.colIdx]).trim() : '';
                
                var displayVal = rawVal;
                if (col.cssClass === 'roster-size') {
                    displayVal = normalizeSize(displayVal.toUpperCase());
                } else if (col.cssClass === 'roster-number') {
                    // Clean up number display: strip trailing .0
                    var numParsed = parseFloat(displayVal);
                    if (!isNaN(numParsed) && String(numParsed) === displayVal) {
                        // If parseFloat roundtrip matches, show parsed version
                        displayVal = String(numParsed);
                    } else {
                        // Try removing trailing .0 manually
                        displayVal = displayVal.replace(/\.0+$/, '');
                    }
                }
                
                cellsHtml += '<td class="align-middle">' + _escHtml(displayVal);
                cellsHtml += '<input type="hidden" class="' + col.cssClass + '" data-header="' + _escHtml(col.header) + '" value="' + _escHtml(displayVal) + '">';
                cellsHtml += '</td>';
            });
            
            cellsHtml += '<td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="sublimation_removeRosterRow(this)" title="Remove"><i class="fas fa-times"></i></button></td>';
            
            tr.innerHTML = cellsHtml;
            tbody.appendChild(tr);
            added++;
    });
    
    if (added === 0 && empty) {
        tbody.appendChild(empty);
        empty.style.display = '';
        empty.querySelector('td').colSpan = displayCols.length + 2;
    }
    
    sublimation_updateRosterCount();
    sublimation_calculateTotal();
    
    // Switch to roster mode
    var sizeSelect = document.getElementById('sublimation_rosterSizeSelect');
    if (sizeSelect) sizeSelect.value = 'roster';
    if (typeof sublimation_toggleSizeMode === 'function') sublimation_toggleSizeMode();
    
    // Reset file input
    var input = document.getElementById('sublimation_excelInput');
    if (input) input.value = '';
    
    if (added > 0) {
        showToast(added + ' person' + (added > 1 ? 's' : '') + ' imported from Excel!', 'success');
    } else {
        showToast('No valid rows to import from Excel.', 'warning');
    }
}

// Escape HTML for roster display
function _escHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

// Wire up the Add to Cart button + roster keyboard shortcuts
document.addEventListener('DOMContentLoaded', function() {
    var addBtn = document.getElementById('sublimation_addItemBtn');
    if (addBtn) {
        addBtn.addEventListener('click', sublimation_addItemToCart);
    }
    
    // Print Order Slip button
    var printOrderSlipBtn = document.getElementById('sublimation_printOrderSlip');
    if (printOrderSlipBtn) {
        printOrderSlipBtn.addEventListener('click', sublimation_printOrderSlip);
    }
    
    // Enter key on roster fields adds person
    var rosterFields = ['roster_newName', 'roster_newNumber', 'roster_newSize'];
    rosterFields.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sublimation_addRosterRow();
                }
            });
        }
    });
    
    // Ctrl+Enter on bulk paste textarea triggers import
    var bulkInput = document.getElementById('sublimation_bulkPasteInput');
    if (bulkInput) {
        bulkInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.ctrlKey) {
                e.preventDefault();
                sublimation_bulkPaste();
            }
        });
    }
});

</script>
@endpush
