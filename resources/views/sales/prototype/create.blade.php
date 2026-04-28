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
    
    <form id="prototypeSaleForm" action="{{ route('sales.prototype.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Step 1: Customer Information -->
        <div class="form-section">
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
                
                <!-- Customer Save Button -->
                <div class="mb-3">
                    <button type="button" class="btn btn-success" id="saveCustomerBtn">
                        <i class="fas fa-save me-2"></i> Save Customer & Continue
                    </button>
                    <small class="text-muted ms-2">Customer will be saved immediately and you can continue to next steps</small>
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
                            <h5 class="card-title mb-2">Cutting Services</h5>
                            <p class="text-muted small mb-3">Fabric cutting, pattern making</p>
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
                
                <!-- Department Breakdown -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Department Breakdown</h5>
                                <div id="departmentBreakdown">
                                    <p class="text-muted mb-0">No items assigned to departments yet.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Order Summary</h5>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span id="subtotalDisplay">₱0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax (12%):</span>
                                    <span id="taxDisplay">₱0.00</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total:</span>
                                    <span id="totalDisplay">₱0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hidden fields for form submission -->
            <input type="hidden" name="department_id" id="auto_department_id" value="">
            <input type="hidden" name="items_json" id="items_json" value="">
            <input type="hidden" name="subtotal" id="subtotal" value="0">
            <input type="hidden" name="tax" id="tax" value="0">
            <input type="hidden" name="total_amount" id="total_amount" value="0">
        </div>
        

        
        <!-- Step 4: Payment & Verification -->
        <div class="form-section">
            <h3 class="section-title">Step 4: Payment & Verification</h3>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label mb-3">Payment Method *</label>
                    <div class="row" id="paymentMethodSelection">
                        <div class="col-md-6 mb-3">
                            <div class="payment-method-card" data-method="gcash">
                                <i class="fas fa-mobile-alt fa-2x text-success mb-3"></i>
                                <h6>GCash</h6>
                                <input type="radio" class="btn-check" name="payment_method" id="pm_gcash" value="gcash" required>
                                <label class="btn btn-outline-success btn-sm mt-2" for="pm_gcash">Select</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="payment-method-card" data-method="bank_transfer">
                                <i class="fas fa-university fa-2x text-primary mb-3"></i>
                                <h6>Bank Transfer</h6>
                                <input type="radio" class="btn-check" name="payment_method" id="pm_bank" value="bank_transfer" required>
                                <label class="btn btn-outline-primary btn-sm mt-2" for="pm_bank">Select</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="payment-method-card" data-method="cash">
                                <i class="fas fa-money-bill-wave fa-2x text-success mb-3"></i>
                                <h6>Cash</h6>
                                <input type="radio" class="btn-check" name="payment_method" id="pm_cash" value="cash" required>
                                <label class="btn btn-outline-success btn-sm mt-2" for="pm_cash">Select</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="payment-method-card" data-method="credit_card">
                                <i class="fas fa-credit-card fa-2x text-warning mb-3"></i>
                                <h6>Credit Card</h6>
                                <input type="radio" class="btn-check" name="payment_method" id="pm_cc" value="credit_card" required>
                                <label class="btn btn-outline-warning btn-sm mt-2" for="pm_cc">Select</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="payment_owner" class="form-label">Payment Account Owner *</label>
                        <select class="form-control" id="payment_owner" name="payment_owner" required>
                            <option value="">Select account owner</option>
                            <option value="company">Company Account</option>
                            <option value="owner_personal">Owner Personal Account</option>
                            <option value="sales_agent">Sales Agent Account</option>
                            <option value="department_head">Department Head Account</option>
                        </select>
                        <small class="text-muted">Who owns the payment account where the customer will send payment?</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deposit_paid" class="form-label">Deposit Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="deposit_paid" name="deposit_paid" step="0.01" min="0" value="0">
                        </div>
                        <small class="text-muted">Amount paid as deposit (if any)</small>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Payment Screenshot (Optional)</label>
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h5>Drag & drop payment screenshot here</h5>
                    <p class="text-muted">or click to browse files</p>
                    <input type="file" id="payment_screenshot" name="payment_screenshot" accept="image/*" class="d-none">
                    <div id="fileName" class="mt-2 text-success"></div>
                </div>
            </div>
        </div>
        
        <!-- Step 5: Mockup & Images -->
        <div class="form-section">
            <h3 class="section-title">Step 5: Mockup & Reference Images</h3>
            
            <div class="mb-3">
                <label class="form-label">Mockup Images</label>
                <div class="upload-area" id="mockupUploadArea">
                    <div class="upload-icon">
                        <i class="fas fa-image"></i>
                    </div>
                    <h5>Upload mockup images</h5>
                    <p class="text-muted">Design files, mockups, or reference images</p>
                    <input type="file" id="mockup_images" name="mockup_images[]" accept="image/*" multiple class="d-none">
                    <div id="mockupFileNames" class="mt-2 text-success"></div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Reference Images</label>
                <div class="upload-area" id="referenceUploadArea">
                    <div class="upload-icon">
                        <i class="fas fa-camera"></i>
                    </div>
                    <h5>Upload reference photos</h5>
                    <p class="text-muted">Photos of samples, materials, or inspiration</p>
                    <input type="file" id="reference_images" name="reference_images[]" accept="image/*" multiple class="d-none">
                    <div id="referenceFileNames" class="mt-2 text-success"></div>
                </div>
            </div>
        </div>
        
        <!-- Step 6: Internal Notes -->
        <div class="form-section">
            <h3 class="section-title">Step 6: Internal Notes</h3>
            
            <div class="mb-3">
                <label for="internal_notes" class="form-label">Internal Notes</label>
                <textarea class="form-control" id="internal_notes" name="internal_notes" rows="4" placeholder="Any internal notes for the team..."></textarea>
            </div>
            
            <div class="mb-3">
                <label for="estimated_completion_date" class="            <div class="mb-3">
                <label for="estimated_completion_date" class="form-label">Estimated Completion Date</label>
                <input type="date" class="form-control" id="estimated_completion_date" name="estimated_completion_date">
            </div>
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
                                        <button type="button" class="btn btn-sm btn-outline-success" id="addProductRow">
                                            <i class="fas fa-plus-circle me-1"></i> Add Another Product
                                        </button>
                                    </div>
                                    <div class="form-text">Add multiple products in one go. Products loaded from database based on category.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Assign to Department *</label>
                                    <select class="form-control" id="departmentSelect" required>
                                        <option value="">-- Select Department --</option>
                                        <option value="iprint">iPrint Department</option>
                                        <option value="consol">Consol Department</option>
                                        <option value="cinco">Cinco Department</option>
                                        <option value="class">Class Department</option>
                                        <option value="mto">Made to Order Department</option>
                                        <option value="other">Other Department</option>
                                    </select>
                                    <div class="form-text">Select which department will handle these items</div>
                                </div>
                                
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
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
            'cutting': 'Cutting Services',
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
    
    // Load products
    loadProductsFromDatabase(productType);
    
    // Show modal
    const modalEl = document.getElementById('productModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
};

// Load products - GLOBAL
window.loadProductsFromDatabase = function(productType) {
    const select = document.getElementById('productSelect');
    if (!select) return;
    
    select.innerHTML = '<option value="">Loading products...</option>';
    
    // Fetch real products from API
    fetch(`/api/products-for-box/${productType}`)
        .then(response => response.json())
        .then(products => {
            select.innerHTML = '<option value="">Select product</option>';
            
            if (products.length === 0) {
                select.innerHTML = '<option value="">No products found. Please add products to this category in Product Pricing.</option>';
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
                select.appendChild(opt);
            });
            
            // Reset form
            const qty = document.getElementById('productQuantity');
            const price = document.getElementById('productPrice');
            const notes = document.getElementById('productNotes');
            const deptSelect = document.getElementById('departmentSelect');
            if (qty) qty.value = '1';
            if (price) price.value = '';
            if (notes) notes.value = '';
            if (deptSelect) deptSelect.value = '';
            
            // Auto-fill price on select
            select.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if (opt.value && opt.dataset.price && price) {
                    price.value = opt.dataset.price;
                    calculateItemTotal();
                }
            });
            
            calculateItemTotal();
        })
        .catch(error => {
            console.error('Error loading products:', error);
            select.innerHTML = '<option value="">Error loading products. Please try again.</option>';
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
    const deptSelect = document.getElementById('departmentSelect');
    const notes = document.getElementById('productNotes');
    
    if (!deptSelect) return;
    
    // Get all product rows
    const rows = document.querySelectorAll('.product-row');
    const cartItems = [];
    
    // Validate department
    const department = deptSelect.value;
    if (!department) {
        alert('Please select a department');
        deptSelect.focus();
        return;
    }
    
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
    
    // Reset form (keep first row)
    initializeProductRows();
    if (deptSelect) deptSelect.value = '';
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
                            <span>₱${item.unitPrice.toFixed(2)}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total:</span>
                            <span class="fw-bold">₱${item.totalPrice.toFixed(2)}</span>
                        </div>
                        
                        ${item.notes ? `<p class="mb-0 small text-muted"><i class="fas fa-sticky-note me-1"></i> ${item.notes}</p>` : ''}
                    </div>
                </div>
            </div>
        `;
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
        showToast('Item removed from cart', 'warning');
    }
};

// Update order summary - GLOBAL
window.updateOrderSummary = function() {
    let subtotal = 0;
    const deptTotals = {};
    const deptCount = {};
    
    selectedItems.forEach(item => {
        subtotal += item.totalPrice;
        if (!deptTotals[item.department]) {
            deptTotals[item.department] = 0;
            deptCount[item.department] = 0;
        }
        deptTotals[item.department] += item.totalPrice;
        deptCount[item.department] += 1;
    });
    
    const tax = subtotal * 0.12;
    const total = subtotal + tax;
    
    // Update displays
    const subtotalDisplay = document.getElementById('subtotalDisplay');
    const taxDisplay = document.getElementById('taxDisplay');
    const totalDisplay = document.getElementById('totalDisplay');
    
    if (subtotalDisplay) subtotalDisplay.textContent = '₱' + subtotal.toFixed(2);
    if (taxDisplay) taxDisplay.textContent = '₱' + tax.toFixed(2);
    if (totalDisplay) totalDisplay.textContent = '₱' + total.toFixed(2);
    
    // Update hidden inputs
    const subtotalInput = document.getElementById('subtotal');
    const taxInput = document.getElementById('tax');
    const totalInput = document.getElementById('total_amount');
    
    if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);
    if (taxInput) taxInput.value = tax.toFixed(2);
    if (totalInput) totalInput.value = total.toFixed(2);
    
    // Update department breakdown
    updateDepartmentBreakdown(deptTotals, deptCount);
    
    // Update deposit max
    const depositInput = document.getElementById('deposit_paid');
    if (depositInput) {
        depositInput.max = total;
        if (parseFloat(depositInput.value) > total) {
            depositInput.value = total;
        }
    }
};

// Update department breakdown - GLOBAL
window.updateDepartmentBreakdown = function(departmentTotals, departmentCount) {
    const container = document.getElementById('departmentBreakdown');
    if (!container) return;
    
    if (Object.keys(departmentTotals).length === 0) {
        container.innerHTML = '<p class="text-muted mb-0">No items assigned to departments yet.</p>';
        return;
    }
    
    let html = '<div class="row g-3">';
    const deptNames = { 'iprint': 'iPrint', 'consol': 'Consol', 'cinco': 'Cinco', 'class': 'Class', 'mto': 'Made to Order', 'other': 'Other' };
    const deptColors = { 'iprint': 'primary', 'consol': 'info', 'cinco': 'warning', 'class': 'success', 'mto': 'danger', 'other': 'secondary' };
    
    for (const [deptCode, total] of Object.entries(departmentTotals)) {
        const count = departmentCount[deptCode] || 0;
        html += `
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="card-title mb-0">
                                <span class="badge bg-${deptColors[deptCode]} me-2">${deptNames[deptCode]}</span>
                                ${count} item${count !== 1 ? 's' : ''}
                            </h6>
                            <span class="fw-bold">₱${total.toFixed(2)}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-${deptColors[deptCode]}" 
                                 role="progressbar" 
                                 style="width: ${(total / departmentTotals[Object.keys(departmentTotals)[0]]) * 100}%" 
                                 aria-valuenow="${total}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="${Object.values(departmentTotals).reduce((a, b) => a + b, 0)}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    container.innerHTML = html;
};

// Show toast - GLOBAL
window.showToast = function(message, type = 'info') {
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
    
    // Save customer
    const saveCustomerBtn = document.getElementById('saveCustomerBtn');
    if (saveCustomerBtn) {
        saveCustomerBtn.addEventListener('click', function() {
            saveCustomer();
        });
    }
    
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
    
    // Save customer button
    document.getElementById('saveCustomerBtn').addEventListener('click', function() {
        saveCustomer();
    });
    
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
        
        // Update button text
        document.getElementById('saveCustomerBtn').innerHTML = '<i class="fas fa-check me-2"></i> Customer Saved';
        document.getElementById('saveCustomerBtn').classList.remove('btn-success');
        document.getElementById('saveCustomerBtn').classList.add('btn-outline-success');
        document.getElementById('saveCustomerBtn').disabled = true;
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
            document.querySelectorAll('.department-card').forEach(c => {
                c.classList.remove('selected');
            });
            this.classList.add('selected');
            
            const deptName = this.querySelector('h5').textContent;
            document.getElementById('department_name').value = deptName;
        });
    });
    
    // Payment method selection
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.payment-method-card').forEach(c => {
                c.classList.remove('selected');
            });
            this.classList.add('selected');
        });
    });
    
    // File upload handlers
    document.getElementById('uploadArea').addEventListener('click', function() {
        document.getElementById('payment_screenshot').click();
    });
    
    document.getElementById('payment_screenshot').addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            document.getElementById('fileName').textContent = 'Selected: ' + e.target.files[0].name;
        }
    });
    
    // Mockup images upload
    document.getElementById('mockupUploadArea').addEventListener('click', function() {
        document.getElementById('mockup_images').click();
    });
    
    document.getElementById('mockup_images').addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const fileNames = Array.from(e.target.files).map(f => f.name).join(', ');
            document.getElementById('mockupFileNames').textContent = 'Selected: ' + fileNames;
        }
    });
    
    // Reference images upload
    document.getElementById('referenceUploadArea').addEventListener('click', function() {
        document.getElementById('reference_images').click();
    });
    
    document.getElementById('reference_images').addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const fileNames = Array.from(e.target.files).map(f => f.name).join(', ');
            document.getElementById('referenceFileNames').textContent = 'Selected: ' + fileNames;
        }
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
    
    // Update openProductModal to load filter options
    const originalOpenProductModal = window.openProductModal;
    window.openProductModal = function(productType) {
        originalOpenProductModal(productType);
        loadFilterOptions(productType);
        
        // Initialize multiple product rows
        initializeProductRows();
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
        }
        
        // Setup events for new row
        newSelect.addEventListener('change', function() {
            updateProductPriceDisplay(this);
            updateOrderSummary();
        });
        
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
        const rows = document.querySelectorAll('.product-row');
        let totalQuantity = 0;
        let totalAmount = 0;
        const products = [];
        
        // Calculate totals
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
        
        // Update displays
        document.getElementById('totalQuantityDisplay').textContent = totalQuantity;
        document.getElementById('itemTotalDisplay').textContent = `₱${totalAmount.toFixed(2)}`;
        
        // Update products breakdown
        const breakdownContainer = document.getElementById('productsBreakdown');
        if (breakdownContainer) {
            if (products.length === 0) {
                breakdownContainer.innerHTML = '<div class="text-muted small mb-2">No products selected yet</div>';
            } else {
                let html = '<div class="small">';
                products.forEach((p, index) => {
                    let displayName = p.name;
                    if (p.brand) displayName += ` (${p.brand})`;
                    if (p.size) displayName += ` ${p.size}`;
                    if (p.color) displayName += ` ${p.color}`;
                    
                    html += `<div class="d-flex justify-content-between mb-1">`;
                    html += `<span class="text-truncate" title="${displayName}">${displayName}</span>`;
                    html += `<span>${p.quantity} × ₱${p.price.toFixed(2)}</span>`;
                    html += `</div>`;
                });
                html += '</div>';
                breakdownContainer.innerHTML = html;
            }
        }
        
        // Update unit price display (show first product's price)
        const firstRow = document.querySelector('.product-row:first-child');
        if (firstRow) {
            const firstSelect = firstRow.querySelector('.product-select');
            if (firstSelect) {
                updateProductPriceDisplay(firstSelect);
            }
        }
    };
    

</script>
@endpush
