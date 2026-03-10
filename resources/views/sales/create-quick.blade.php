@extends('layouts.app')

@section('title', 'Add Quick Sale')

@push('styles')
    <!-- Bootstrap CSS for modals -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="content-wrapper">
    <!-- Sales Transaction Header - MOVED TO TOP -->
    <div class="card mb-4">
        <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center">
                <div class="header-icon-wrapper me-3">
                    <div class="header-icon bg-gradient-primary">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <div>
                    <h2 class="mb-1">Sales Transaction</h2>
                    <p class="text-muted mb-0">Complete customer details and order information below</p>
                </div>
            </div>
            <div class="mt-3">
                <div class="btn-group" role="group">
                    <a href="{{ route('sales.products') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i> Back to Catalog
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-2"></i> Print Preview
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form Container -->
    <div class="row">
        <!-- Left Column: Form -->
        <div class="col-lg-8">
            <form id="quickSaleForm" action="{{ route('sales.quick-store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Customer Information Card - SPLIT INTO LEFT & RIGHT COLUMNS -->
                <div class="card mb-4">
                    <div class="card-header bg-light py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-circle text-primary me-2"></i>
                            Customer Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <!-- LEFT COLUMN: Customer, Marketplace, Location -->
                            <div style="flex: 1; min-width: 300px;">
                                <!-- Customer Name -->
                                <div class="mb-3">
                                    <label for="customer" class="form-label fw-semibold small">
                                        Customer <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" class="form-control form-control-sm" id="customer" name="customer" 
                                               placeholder="Enter customer name" required>
                                    </div>
                                </div>

                                <!-- Marketplace Dropdown -->
                                <div class="mb-3">
                                    <label for="marketplace" class="form-label fw-semibold small">
                                        Marketplace <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">
                                            <i class="fas fa-store"></i>
                                        </span>
                                        <select class="form-select form-select-sm" id="marketplace" name="marketplace" required>
                                            <option value="" selected disabled>Select marketplace</option>
                                            <option value="shopee">Shopee</option>
                                            <option value="lazada">Lazada</option>
                                            <option value="tiktok">TikTok Shop</option>
                                            <option value="facebook">Facebook Marketplace</option>
                                            <option value="instagram">Instagram</option>
                                            <option value="website">Website</option>
                                            <option value="walk-in">Walk-in</option>
                                            <option value="referral">Referral</option>
                                            <option value="corporate">Corporate</option>
                                            <option value="wholesale">Wholesale</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Location -->
                                <div class="mb-3">
                                    <label for="location" class="form-label fw-semibold small">
                                        Location <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </span>
                                        <input type="text" class="form-control form-control-sm" id="location" name="location" 
                                               placeholder="City/Province" required>
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT COLUMN: Contact Number, Email Address, Company -->
                            <div style="flex: 1; min-width: 300px;">
                                <!-- Contact Number -->
                                <div class="mb-3">
                                    <label for="contact_number" class="form-label fw-semibold small">
                                        Contact Number <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">
                                            <i class="fas fa-phone"></i>
                                        </span>
                                        <input type="tel" class="form-control form-control-sm" id="contact_number" name="contact_number" 
                                               placeholder="09XXXXXXXXX" required>
                                    </div>
                                </div>

                                <!-- Email Address -->
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold small">
                                        Email Address
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control form-control-sm" id="email" name="email" 
                                               placeholder="customer@email.com">
                                    </div>
                                </div>

                                <!-- Company -->
                                <div class="mb-3">
                                    <label for="company" class="form-label fw-semibold small">
                                        Company
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">
                                            <i class="fas fa-building"></i>
                                        </span>
                                        <input type="text" class="form-control form-control-sm" id="company" name="company" 
                                               placeholder="Company name (optional)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Details Card - SIMPLE CLICKABLE OPTIONS -->
                <div class="card mb-4">
                    <div class="card-header bg-light py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-shopping-cart text-success me-2"></i>
                            Order Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <p class="text-muted small mb-3">Select order type:</p>
                            <div class="row g-2">
                                <!-- iPrint Option -->
                                <div class="col-md-3 col-sm-6">
                                    <button type="button" class="btn btn-outline-primary w-100 py-2" data-order-type="iprint">
                                        <i class="fas fa-print fa-lg me-2"></i>
                                        <span class="small fw-bold">iPrint</span>
                                    </button>
                                </div>
                                
                                <!-- Consol Option -->
                                <div class="col-md-3 col-sm-6">
                                    <button type="button" class="btn btn-outline-success w-100 py-2" data-order-type="consol">
                                        <i class="fas fa-boxes fa-lg me-2"></i>
                                        <span class="small fw-bold">Consol</span>
                                    </button>
                                </div>
                                
                                <!-- Class Option -->
                                <div class="col-md-3 col-sm-6">
                                    <button type="button" class="btn btn-outline-info w-100 py-2" data-order-type="class">
                                        <i class="fas fa-graduation-cap fa-lg me-2"></i>
                                        <span class="small fw-bold">Class</span>
                                    </button>
                                </div>
                                
                                <!-- Cinco Option -->
                                <div class="col-md-3 col-sm-6">
                                    <button type="button" class="btn btn-outline-warning w-100 py-2" data-order-type="cinco">
                                        <i class="fas fa-star fa-lg me-2"></i>
                                        <span class="small fw-bold">Cinco</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Forms Container (Initially Hidden) -->
                        <div id="order-forms-container" style="display: none;">
                            <div class="card mt-3">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0" id="form-title">Order Details</h6>
                                    <button type="button" class="btn btn-sm btn-outline-secondary float-end" onclick="hideOrderForm()">Close</button>
                                </div>
                                <div class="card-body">
                                    <!-- iPrint Form -->
                                    <div id="iprint-form" style="display: none;">
                                        <h6 class="mb-3">iPrint Order Details</h6>
                                        
                                        <!-- Step 1: Choose Printing Type -->
                                        <div class="mb-4 p-3 border rounded bg-light">
                                            <h6 class="mb-3 text-primary">Step 1: Choose Printing Type</h6>
                                            <div class="row g-2">
                                                <div class="col-md-4 col-sm-6">
                                                    <button type="button" class="btn btn-outline-primary w-100 py-2 iprint-option-btn" data-option="dtf">
                                                        <i class="fas fa-print fa-lg me-2"></i>
                                                        <span class="small fw-bold">DTF</span>
                                                    </button>
                                                </div>
                                                <div class="col-md-4 col-sm-6">
                                                    <button type="button" class="btn btn-outline-success w-100 py-2 iprint-option-btn" data-option="lanyard">
                                                        <i class="fas fa-id-card fa-lg me-2"></i>
                                                        <span class="small fw-bold">Lanyard</span>
                                                    </button>
                                                </div>
                                                <div class="col-md-4 col-sm-6">
                                                    <button type="button" class="btn btn-outline-info w-100 py-2 iprint-option-btn" data-option="tarpaulin">
                                                        <i class="fas fa-image fa-lg me-2"></i>
                                                        <span class="small fw-bold">Tarpaulin</span>
                                                    </button>
                                                </div>
                                                <div class="col-md-4 col-sm-6">
                                                    <button type="button" class="btn btn-outline-warning w-100 py-2 iprint-option-btn" data-option="sublimation">
                                                        <i class="fas fa-fire fa-lg me-2"></i>
                                                        <span class="small fw-bold">Sublimation</span>
                                                    </button>
                                                </div>
                                                <div class="col-md-4 col-sm-6">
                                                    <button type="button" class="btn btn-outline-danger w-100 py-2 iprint-option-btn" data-option="embroidery">
                                                        <i class="fas fa-threads fa-lg me-2"></i>
                                                        <span class="small fw-bold">Embroidery</span>
                                                    </button>
                                                </div>
                                                <div class="col-md-4 col-sm-6">
                                                    <button type="button" class="btn btn-outline-secondary w-100 py-2 iprint-option-btn" data-option="other">
                                                        <i class="fas fa-boxes fa-lg me-2"></i>
                                                        <span class="small fw-bold">Other Items</span>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Selected Printing Types Table -->
                                            <div id="selected-printing-types-container" class="mt-4" style="display: none;">
                                                <h6 class="mb-3 text-secondary">Order Items:</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered table-hover" id="printing-types-table">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th width="5%">#</th>
                                                                <th width="20%">Printing Type</th>
                                                                <th width="15%">Status</th>
                                                                <th width="40%">Details</th>
                                                                <th width="20%">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="printing-types-tbody">
                                                            <!-- Rows will be added dynamically -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="mt-3 text-end">
                                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-another-printing-type-from-table">
                                                        <i class="fas fa-plus-circle me-1"></i> Add Another Item
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Step 2: Detailed Form (Hidden Initially) -->
                                        <div id="iprint-details-container" style="display: none;">
                                            <div class="mb-3 p-3 border rounded">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0 text-primary" id="iprint-selected-type">Selected: DTF Printing</h6>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="iprint-change-type">
                                                        <i class="fas fa-undo me-1"></i> Change Type
                                                    </button>
                                                </div>
                                                
                                                <!-- DTF Printing Form -->
                                                <div id="dtf-form" class="iprint-type-form" style="display: none;">
                                                    <div class="row g-3">
                                                        <!-- Print Size Section - Flexible Multiple Sizes -->
                                                        <div class="col-md-12">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <label class="form-label fw-semibold small">Print Sizes</label>
                                                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-print-size">
                                                                    <i class="fas fa-plus me-1"></i> Add Another Print Size
                                                                </button>
                                                            </div>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-hover table-bordered">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th width="5%" class="text-center">#</th>
                                                                            <th width="35%">Print Size</th>
                                                                            <th width="30%">Custom Size</th>
                                                                            <th width="20%">Quantity</th>
                                                                            <th width="10%" class="text-center">Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="print-sizes-container">
                                                                        <!-- First Print Size Row -->
                                                                        <tr class="print-size-row">
                                                                            <td class="align-middle text-center">1</td>
                                                                            <td>
                                                                                <select class="form-select form-select-sm print-size-select" name="dtf_print_size[]" required>
                                                                                    <option value="" selected disabled>Select print size</option>
                                                                                    <option value="8x10">8x10 inches</option>
                                                                                    <option value="10x12">10x12 inches</option>
                                                                                    <option value="12x14">12x14 inches</option>
                                                                                    <option value="14x16">14x16 inches</option>
                                                                                    <option value="16x20">16x20 inches</option>
                                                                                    <option value="custom">Custom Size</option>
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" class="form-control form-control-sm custom-size-input" name="dtf_custom_size[]" placeholder="e.g., 18x24 inches" style="display: none;">
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" class="form-control form-control-sm print-size-quantity" name="dtf_print_size_quantity[]" min="1" value="1" required>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <button type="button" class="btn btn-sm btn-outline-danger delete-print-size-btn" onclick="deletePrintSizeRow(this)" disabled title="Cannot delete the only print size">
                                                                                    <i class="fas fa-trash"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        <!-- Material Type and Brand -->
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold small">Material Type</label>
                                                            <select class="form-select form-select-sm" name="dtf_material_type" required>
                                                                <option value="" selected disabled>Select material type</option>
                                                                <option value="cotton">100% Cotton</option>
                                                                <option value="polyester">Polyester</option>
                                                                <option value="cotton_poly">Cotton-Poly Blend</option>
                                                                <option value="fleece">Fleece</option>
                                                                <option value="hoodie">Hoodie Material</option>
                                                                <option value="sweatshirt">Sweatshirt Material</option>
                                                                <option value="other">Other Material</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold small">Brand</label>
                                                            <select class="form-select form-select-sm" name="dtf_brand" required>
                                                                <option value="" selected disabled>Select brand</option>
                                                                <option value="gildan">Gildan</option>
                                                                <option value="hanes">Hanes</option>
                                                                <option value="fruit_of_the_loom">Fruit of the Loom</option>
                                                                <option value="champion">Champion</option>
                                                                <option value="under_armour">Under Armour</option>
                                                                <option value="nike">Nike</option>
                                                                <option value="adidas">Adidas</option>
                                                                <option value="local">Local Brand</option>
                                                                <option value="no_brand">No Brand/Plain</option>
                                                            </select>
                                                        </div>

                                                        <!-- Color -->
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold small">Color</label>
                                                            <select class="form-select form-select-sm" name="dtf_color" required>
                                                                <option value="" selected disabled>Select color</option>
                                                                <option value="white">White</option>
                                                                <option value="black">Black</option>
                                                                <option value="navy">Navy Blue</option>
                                                                <option value="red">Red</option>
                                                                <option value="royal_blue">Royal Blue</option>
                                                                <option value="gray">Gray</option>
                                                                <option value="maroon">Maroon</option>
                                                                <option value="green">Green</option>
                                                                <option value="yellow">Yellow</option>
                                                                <option value="pink">Pink</option>
                                                                <option value="custom">Custom Color</option>
                                                            </select>
                                                        </div>

                                                        <!-- Shirt Sizes Section - Flexible Multiple Sizes -->
                                                        <div class="col-md-6">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <label class="form-label fw-semibold small">Shirt Sizes</label>
                                                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-shirt-size">
                                                                    <i class="fas fa-plus me-1"></i> Add Another Size
                                                                </button>
                                                            </div>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-hover table-bordered">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th width="5%" class="text-center">#</th>
                                                                            <th width="65%">Size</th>
                                                                            <th width="20%">Quantity</th>
                                                                            <th width="10%" class="text-center">Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="shirt-sizes-container">
                                                                        <!-- First Shirt Size Row -->
                                                                        <tr class="shirt-size-row">
                                                                            <td class="align-middle text-center">1</td>
                                                                            <td>
                                                                                <select class="form-select form-select-sm shirt-size-select" name="dtf_shirt_size[]" required>
                                                                                    <option value="" selected disabled>Select size</option>
                                                                                    <option value="xs">XS (Extra Small)</option>
                                                                                    <option value="s">S (Small)</option>
                                                                                    <option value="m">M (Medium)</option>
                                                                                    <option value="l">L (Large)</option>
                                                                                    <option value="xl">XL (Extra Large)</option>
                                                                                    <option value="2xl">2XL (Double Extra Large)</option>
                                                                                    <option value="3xl">3XL (Triple Extra Large)</option>
                                                                                    <option value="4xl">4XL (4X Extra Large)</option>
                                                                                    <option value="5xl">5XL (5X Extra Large)</option>
                                                                                    <option value="6xl">6XL (6X Extra Large)</option>
                                                                                    <option value="7xl">7XL (7X Extra Large)</option>
                                                                                    <option value="8xl">8XL (8X Extra Large)</option>
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" class="form-control form-control-sm shirt-size-quantity" name="dtf_shirt_size_quantity[]" min="1" value="1" required>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <button type="button" class="btn btn-sm btn-outline-danger delete-shirt-size-btn" onclick="deleteShirtSizeRow(this)" disabled title="Cannot delete the only size">
                                                                                    <i class="fas fa-trash"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        <!-- Additional Notes -->
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold small">Additional Notes</label>
                                                            <textarea class="form-control form-control-sm" name="dtf_notes" rows="2" placeholder="Special instructions, design details, file information..."></textarea>
                                                        </div>

                                                        <!-- Attach Picture/Image -->
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold small">Attach Picture/Image</label>
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <!-- Upload Options Tabs -->
                                                                    <ul class="nav nav-tabs nav-tabs-sm mb-3" id="uploadTabs" role="tablist">
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link active" id="file-tab" data-bs-toggle="tab" data-bs-target="#file-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-upload me-1"></i> Upload Files
                                                                            </button>
                                                                        </li>
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link" id="paste-tab" data-bs-toggle="tab" data-bs-target="#paste-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-paste me-1"></i> Paste Screenshot
                                                                            </button>
                                                                        </li>
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link" id="camera-tab" data-bs-toggle="tab" data-bs-target="#camera-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-camera me-1"></i> Take Photo
                                                                            </button>
                                                                        </li>
                                                                    </ul>
                                                                    
                                                                    <div class="tab-content" id="uploadTabsContent">
                                                                        <!-- File Upload Tab -->
                                                                        <div class="tab-pane fade show active" id="file-tab-pane" role="tabpanel">
                                                                            <div class="mb-3">
                                                                                <input type="file" class="form-control form-control-sm" id="dtf-image-upload" name="dtf_image[]" accept="image/*,.pdf,.psd,.ai,.eps" multiple>
                                                                                <div class="form-text small">
                                                                                    Supported formats: JPG, PNG, GIF, PDF, PSD, AI, EPS. Max file size: 10MB per file.
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Paste Screenshot Tab -->
                                                                        <div class="tab-pane fade" id="paste-tab-pane" role="tabpanel">
                                                                            <div class="text-center">
                                                                                <div class="mb-3">
                                                                                    <div class="paste-area border-2 border-dashed rounded p-4" id="paste-area" style="border-color: #dee2e6; min-height: 150px; background-color: #f8f9fa;">
                                                                                        <i class="fas fa-paste fa-3x text-muted mb-3"></i>
                                                                                        <h6 class="mb-2">Paste Screenshot Here</h6>
                                                                                        <p class="small text-muted mb-3">
                                                                                            Take screenshot (Shift+Alt+S or any tool) then paste with Ctrl+V
                                                                                        </p>
                                                                                        <div class="paste-instructions small">
                                                                                            <div class="row">
                                                                                                <div class="col-6">
                                                                                                    <div class="mb-2">
                                                                                                        <i class="fas fa-desktop me-1"></i> Windows/Linux:<br>
                                                                                                        <code>Shift+Alt+S</code> then <code>Ctrl+V</code>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="col-6">
                                                                                                    <div class="mb-2">
                                                                                                        <i class="fas fa-apple me-1"></i> Mac:<br>
                                                                                                        <code>Shift+Cmd+4</code> then <code>Cmd+V</code>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                
                                                                                <!-- Pasted Image Preview -->
                                                                                <div id="pasted-image-container" class="d-none">
                                                                                    <h6 class="small">Pasted Screenshot:</h6>
                                                                                    <img id="pasted-image-preview" class="img-fluid rounded border mb-2" style="max-height: 200px;">
                                                                                    <div>
                                                                                        <button type="button" class="btn btn-sm btn-primary" id="use-pasted-image">
                                                                                            <i class="fas fa-check me-1"></i> Use This Screenshot
                                                                                        </button>
                                                                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-pasted-image">
                                                                                            <i class="fas fa-times me-1"></i> Clear
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Camera Tab -->
                                                                        <div class="tab-pane fade" id="camera-tab-pane" role="tabpanel">
                                                                            <div class="text-center">
                                                                                <!-- Camera Preview -->
                                                                                <div id="camera-preview-container" class="mb-3 d-none">
                                                                                    <video id="camera-preview" autoplay playsinline class="img-fluid rounded border" style="max-height: 200px;"></video>
                                                                                </div>
                                                                                
                                                                                <!-- Camera Controls -->
                                                                                <div class="btn-group" role="group">
                                                                                    <button type="button" class="btn btn-sm btn-outline-primary" id="start-camera">
                                                                                        <i class="fas fa-video me-1"></i> Start Camera
                                                                                    </button>
                                                                                    <button type="button" class="btn btn-sm btn-success d-none" id="capture-photo">
                                                                                        <i class="fas fa-camera me-1"></i> Capture Photo
                                                                                    </button>
                                                                                    <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="stop-camera">
                                                                                        <i class="fas fa-stop me-1"></i> Stop Camera
                                                                                    </button>
                                                                                </div>
                                                                                
                                                                                <!-- Captured Photo -->
                                                                                <div id="captured-photo-container" class="mt-3 d-none">
                                                                                    <h6 class="small">Captured Photo:</h6>
                                                                                    <canvas id="captured-photo" class="img-fluid rounded border mb-2" style="max-height: 150px;"></canvas>
                                                                                    <div>
                                                                                        <button type="button" class="btn btn-sm btn-primary" id="use-photo">
                                                                                            <i class="fas fa-check me-1"></i> Use This Photo
                                                                                        </button>
                                                                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="retake-photo">
                                                                                            <i class="fas fa-redo me-1"></i> Retake
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <!-- Image Preview Container -->
                                                                    <div id="image-preview-container" class="d-none mt-3">
                                                                        <h6 class="mb-2">Uploaded Images:</h6>
                                                                        <div id="image-previews" class="row g-2"></div>
                                                                    </div>
                                                                    
                                                                    <!-- Upload Progress -->
                                                                    <div id="upload-progress" class="progress d-none mt-2" style="height: 6px;">
                                                                        <div id="upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Total Quantity Display -->
                                                        <div class="col-md-12">
                                                            <div class="alert alert-info p-2">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="small fw-semibold mb-2">Print Sizes Summary:</div>
                                                                        <div id="print-size-breakdown" class="small mb-2">
                                                                            <div class="d-flex align-items-center mb-1">
                                                                                <span class="me-2">8x10:</span>
                                                                                <span class="badge bg-secondary">0</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="mt-2">
                                                                            <span class="small fw-semibold">Print Total:</span>
                                                                            <span class="badge bg-primary ms-2" id="print-total-quantity">1</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="small fw-semibold mb-2">Shirt Sizes Summary:</div>
                                                                        <div id="shirt-size-breakdown" class="small mb-2">
                                                                            <div class="d-flex align-items-center mb-1">
                                                                                <span class="me-2">XS:</span>
                                                                                <span class="badge bg-secondary">0</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="mt-2">
                                                                            <span class="small fw-semibold">Shirt Total:</span>
                                                                            <span class="badge bg-primary ms-2" id="shirt-total-quantity">1</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Lanyard Printing Form -->
                                                <div id="lanyard-form" class="iprint-type-form" style="display: none;">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold small">Lanyard Type</label>
                                                            <select class="form-select form-select-sm" name="lanyard_type">
                                                                <option value="" selected disabled>Select lanyard type</option>
                                                                <option value="standard">Standard Lanyard</option>
                                                                <option value="premium">Premium Lanyard</option>
                                                                <option value="safety">Safety Breakaway</option>
                                                                <option value="custom">Custom Shape</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold small">Width</label>
                                                            <select class="form-select form-select-sm" name="lanyard_width">
                                                                <option value="15mm">15mm (Standard)</option>
                                                                <option value="20mm">20mm (Wide)</option>
                                                                <option value="25mm">25mm (Extra Wide)</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold small">Quantity</label>
                                                            <input type="number" class="form-control form-control-sm" name="lanyard_quantity" min="50" value="100">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold small">Length</label>
                                                            <select class="form-select form-select-sm" name="lanyard_length">
                                                                <option value="36">36 inches</option>
                                                                <option value="40" selected>40 inches</option>
                                                                <option value="48">48 inches</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold small">Attachment</label>
                                                            <select class="form-select form-select-sm" name="lanyard_attachment">
                                                                <option value="j-hook">J-Hook</option>
                                                                <option value="bulldog">Bulldog Clip</option>
                                                                <option value="keyring">Keyring</option>
                                                                <option value="swivel">Swivel Hook</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold small">Print Details</label>
                                                            <textarea class="form-control form-control-sm" name="lanyard_notes" rows="2" placeholder="Logo details, text to print, color preferences..."></textarea>
                                                        </div>
                                                        
                                                        <!-- Attach Picture/Image -->
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold small">Attach Picture/Image</label>
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <!-- Upload Options Tabs -->
                                                                    <ul class="nav nav-tabs nav-tabs-sm mb-3" id="lanyard-uploadTabs" role="tablist">
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link active" id="lanyard-file-tab" data-bs-toggle="tab" data-bs-target="#lanyard-file-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-upload me-1"></i> Upload Files
                                                                            </button>
                                                                        </li>
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link" id="lanyard-paste-tab" data-bs-toggle="tab" data-bs-target="#lanyard-paste-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-paste me-1"></i> Paste Screenshot
                                                                            </button>
                                                                        </li>
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link" id="lanyard-camera-tab" data-bs-toggle="tab" data-bs-target="#lanyard-camera-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-camera me-1"></i> Take Photo
                                                                            </button>
                                                                        </li>
                                                                    </ul>
                                                                    
                                                                    <div class="tab-content" id="lanyard-uploadTabsContent">
                                                                        <!-- File Upload Tab -->
                                                                        <div class="tab-pane fade show active" id="lanyard-file-tab-pane" role="tabpanel">
                                                                            <div class="mb-3">
                                                                                <input type="file" class="form-control form-control-sm" id="lanyard-image-upload" name="lanyard_image[]" accept="image/*,.pdf,.psd,.ai,.eps" multiple>
                                                                                <div class="form-text small">
                                                                                    Supported formats: JPG, PNG, GIF, PDF, PSD, AI, EPS. Max file size: 10MB per file.
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Paste Screenshot Tab -->
                                                                        <div class="tab-pane fade" id="lanyard-paste-tab-pane" role="tabpanel">
                                                                            <div class="text-center">
                                                                                <div class="mb-3">
                                                                                    <div class="paste-area border-2 border-dashed rounded p-4" id="lanyard-paste-area" style="border-color: #dee2e6; min-height: 150px; background-color: #f8f9fa;">
                                                                                        <i class="fas fa-paste fa-3x text-muted mb-3"></i>
                                                                                        <h6 class="mb-2">Paste Screenshot Here</h6>
                                                                                        <p class="small text-muted mb-3">
                                                                                            Take screenshot (Shift+Alt+S or any tool) then paste with Ctrl+V
                                                                                        </p>
                                                                                        <div class="paste-instructions small">
                                                                                            <div class="row">
                                                                                                <div class="col-6">
                                                                                                    <div class="mb-2">
                                                                                                        <i class="fas fa-desktop me-1"></i> Windows/Linux:<br>
                                                                                                        <code>Shift+Alt+S</code> then <code>Ctrl+V</code>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="col-6">
                                                                                                    <div class="mb-2">
                                                                                                        <i class="fas fa-apple me-1"></i> Mac:<br>
                                                                                                        <code>Shift+Cmd+4</code> then <code>Cmd+V</code>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                
                                                                                <!-- Pasted Image Preview -->
                                                                                <div id="lanyard-pasted-image-container" class="d-none">
                                                                                    <h6 class="small">Pasted Screenshot:</h6>
                                                                                    <img id="lanyard-pasted-image-preview" class="img-fluid rounded border mb-2" style="max-height: 200px;">
                                                                                    <div>
                                                                                        <button type="button" class="btn btn-sm btn-primary" id="lanyard-use-pasted-image">
                                                                                            <i class="fas fa-check me-1"></i> Use This Screenshot
                                                                                        </button>
                                                                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="lanyard-clear-pasted-image">
                                                                                            <i class="fas fa-times me-1"></i> Clear
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Camera Tab -->
                                                                        <div class="tab-pane fade" id="lanyard-camera-tab-pane" role="tabpanel">
                                                                            <div class="text-center">
                                                                                <!-- Camera Preview -->
                                                                                <div id="lanyard-camera-preview-container" class="mb-3 d-none">
                                                                                    <video id="lanyard-camera-preview" autoplay playsinline class="img-fluid rounded border" style="max-height: 200px;"></video>
                                                                                </div>
                                                                                
                                                                                <!-- Camera Controls -->
                                                                                <div class="btn-group" role="group">
                                                                                    <button type="button" class="btn btn-sm btn-outline-primary" id="lanyard-start-camera">
                                                                                        <i class="fas fa-video me-1"></i> Start Camera
                                                                                    </button>
                                                                                    <button type="button" class="btn btn-sm btn-success d-none" id="lanyard-capture-photo">
                                                                                        <i class="fas fa-camera me-1"></i> Capture Photo
                                                                                    </button>
                                                                                    <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="lanyard-stop-camera">
                                                                                        <i class="fas fa-stop me-1"></i> Stop Camera
                                                                                    </button>
                                                                                </div>
                                                                                
                                                                                <!-- Captured Photo Preview -->
                                                                                <div id="lanyard-captured-photo-container" class="mt-3 d-none">
                                                                                    <h6 class="small">Captured Photo:</h6>
                                                                                    <img id="lanyard-captured-photo-preview" class="img-fluid rounded border mb-2" style="max-height: 200px;">
                                                                                    <div>
                                                                                        <button type="button" class="btn btn-sm btn-primary" id="lanyard-use-captured-photo">
                                                                                            <i class="fas fa-check me-1"></i> Use This Photo
                                                                                        </button>
                                                                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="lanyard-retake-photo">
                                                                                            <i class="fas fa-redo me-1"></i> Retake
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <!-- Uploaded Files Preview -->
                                                                    <div id="lanyard-uploaded-files-container" class="mt-3">
                                                                        <h6 class="small mb-2">Attached Files:</h6>
                                                                        <div id="lanyard-uploaded-files-list" class="mb-2">
                                                                            <div class="text-muted small">No files attached yet</div>
                                                                        </div>
                                                                        <div class="d-none" id="lanyard-uploaded-files-actions">
                                                                            <button type="button" class="btn btn-sm btn-outline-danger" id="lanyard-clear-all-files">
                                                                                <i class="fas fa-trash me-1"></i> Clear All Files
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Total Quantity Display -->
                                                        <div class="col-md-12">
                                                            <div class="alert alert-info p-2">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="small fw-semibold mb-2">Order Summary:</div>
                                                                        <div class="mt-2">
                                                                            <span class="small fw-semibold">Total Quantity:</span>
                                                                            <span class="badge bg-primary ms-2" id="lanyard-total-quantity">100</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Tarpaulin Printing Form -->
                                                <div id="tarpaulin-form" class="iprint-type-form" style="display: none;">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold small">Tarpaulin Type</label>
                                                            <select class="form-select form-select-sm" name="tarpaulin_type">
                                                                <option value="" selected disabled>Select tarpaulin type</option>
                                                                <option value="standard">Standard Tarpaulin</option>
                                                                <option value="mesh">Mesh Tarpaulin</option>
                                                                <option value="canvas">Canvas</option>
                                                                <option value="vinyl">Vinyl Banner</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold small">Thickness</label>
                                                            <select class="form-select form-select-sm" name="tarpaulin_thickness">
                                                                <option value="8oz">8 oz (Light)</option>
                                                                <option value="10oz" selected>10 oz (Standard)</option>
                                                                <option value="12oz">12 oz (Heavy)</option>
                                                                <option value="14oz">14 oz (Extra Heavy)</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold small">Width (inches)</label>
                                                            <input type="number" class="form-control form-control-sm" name="tarpaulin_width" min="1" value="36" step="1">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold small">Height (inches)</label>
                                                            <input type="number" class="form-control form-control-sm" name="tarpaulin_height" min="1" value="72" step="1">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold small">Quantity</label>
                                                            <input type="number" class="form-control form-control-sm" name="tarpaulin_quantity" min="1" value="1">
                                                        </div>
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold small">Print Specifications</label>
                                                            <textarea class="form-control form-control-sm" name="tarpaulin_notes" rows="2" placeholder="Design details, mounting requirements, grommet placement..."></textarea>
                                                        </div>
                                                        
                                                        <!-- Attach Picture/Image -->
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold small">Attach Picture/Image</label>
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <!-- Upload Options Tabs -->
                                                                    <ul class="nav nav-tabs nav-tabs-sm mb-3" id="tarpaulin-uploadTabs" role="tablist">
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link active" id="tarpaulin-file-tab" data-bs-toggle="tab" data-bs-target="#tarpaulin-file-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-upload me-1"></i> Upload Files
                                                                            </button>
                                                                        </li>
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link" id="tarpaulin-paste-tab" data-bs-toggle="tab" data-bs-target="#tarpaulin-paste-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-paste me-1"></i> Paste Screenshot
                                                                            </button>
                                                                        </li>
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link" id="tarpaulin-camera-tab" data-bs-toggle="tab" data-bs-target="#tarpaulin-camera-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-camera me-1"></i> Take Photo
                                                                            </button>
                                                                        </li>
                                                                    </ul>
                                                                    
                                                                    <div class="tab-content" id="tarpaulin-uploadTabsContent">
                                                                        <!-- File Upload Tab -->
                                                                        <div class="tab-pane fade show active" id="tarpaulin-file-tab-pane" role="tabpanel">
                                                                            <div class="mb-3">
                                                                                <input type="file" class="form-control form-control-sm" id="tarpaulin-image-upload" name="tarpaulin_image[]" accept="image/*,.pdf,.psd,.ai,.eps" multiple>
                                                                                <div class="form-text small">
                                                                                    Supported formats: JPG, PNG, GIF, PDF, PSD, AI, EPS. Max file size: 10MB per file.
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Paste Screenshot Tab -->
                                                                        <div class="tab-pane fade" id="tarpaulin-paste-tab-pane" role="tabpanel">
                                                                            <div class="text-center">
                                                                                <div class="mb-3">
                                                                                    <div class="paste-area border-2 border-dashed rounded p-4" id="tarpaulin-paste-area" style="border-color: #dee2e6; min-height: 150px; background-color: #f8f9fa;">
                                                                                        <i class="fas fa-paste fa-3x text-muted mb-3"></i>
                                                                                        <h6 class="mb-2">Paste Screenshot Here</h6>
                                                                                        <p class="small text-muted mb-3">
                                                                                            Take screenshot (Shift+Alt+S or any tool) then paste with Ctrl+V
                                                                                        </p>
                                                                                        <div class="paste-instructions small">
                                                                                            <div class="row">
                                                                                                <div class="col-6">
                                                                                                    <div class="mb-2">
                                                                                                        <i class="fas fa-desktop me-1"></i> Windows/Linux:<br>
                                                                                                        <span class="text-muted">Ctrl+Shift+S or Print Screen</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="col-6">
                                                                                                    <div class="mb-2">
                                                                                                        <i class="fas fa-apple me-1"></i> macOS:<br>
                                                                                                        <span class="text-muted">Cmd+Shift+4 or Cmd+Shift+5</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Camera Tab -->
                                                                        <div class="tab-pane fade" id="tarpaulin-camera-tab-pane" role="tabpanel">
                                                                            <div class="text-center">
                                                                                <div class="mb-3">
                                                                                    <div class="camera-preview border rounded p-3" id="tarpaulin-camera-preview" style="display: none;">
                                                                                        <video id="tarpaulin-camera-video" autoplay playsinline style="width: 100%; max-height: 300px;"></video>
                                                                                        <div class="mt-3">
                                                                                            <button type="button" class="btn btn-sm btn-primary" id="tarpaulin-capture-btn">
                                                                                                <i class="fas fa-camera me-1"></i> Capture Photo
                                                                                            </button>
                                                                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="tarpaulin-camera-retry-btn" style="display: none;">
                                                                                                <i class="fas fa-redo me-1"></i> Retry
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="camera-placeholder border-2 border-dashed rounded p-4" id="tarpaulin-camera-placeholder" style="border-color: #dee2e6; min-height: 150px; background-color: #f8f9fa;">
                                                                                        <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                                                                                        <h6 class="mb-2">Take a Photo</h6>
                                                                                        <p class="small text-muted mb-3">
                                                                                            Click below to start your camera
                                                                                        </p>
                                                                                        <button type="button" class="btn btn-sm btn-outline-primary" id="tarpaulin-start-camera-btn">
                                                                                            <i class="fas fa-video me-1"></i> Start Camera
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <!-- Image Preview Container -->
                                                                    <div id="tarpaulin-image-preview-container" class="d-none mt-3">
                                                                        <h6 class="mb-2">Uploaded Images:</h6>
                                                                        <div id="tarpaulin-image-previews" class="row g-2"></div>
                                                                    </div>
                                                                    
                                                                    <!-- Upload Progress -->
                                                                    <div id="tarpaulin-upload-progress" class="progress d-none mt-2" style="height: 6px;">
                                                                        <div id="tarpaulin-upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                                                    </div>
                                                                    
                                                                    <!-- Uploaded Files List -->
                                                                    <div class="mt-3">
                                                                        <div id="tarpaulin-uploaded-files-list" class="mb-2">
                                                                            <div class="text-muted small">No files attached yet</div>
                                                                        </div>
                                                                        <div class="d-none" id="tarpaulin-uploaded-files-actions">
                                                                            <button type="button" class="btn btn-sm btn-outline-danger" id="tarpaulin-clear-all-files">
                                                                                <i class="fas fa-trash me-1"></i> Clear All Files
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Total Quantity Display -->
                                                        <div class="col-md-12">
                                                            <div class="alert alert-info p-2">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="small fw-semibold mb-2">Order Summary:</div>
                                                                        <div class="mt-2">
                                                                            <span class="small fw-semibold">Total Quantity:</span>
                                                                            <span class="badge bg-primary ms-2" id="tarpaulin-total-quantity">1</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Sublimation Printing Form -->
                                                <div id="sublimation-form" class="iprint-type-form" style="display: none;">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold small">Sublimation Type</label>
                                                            <select class="form-select form-select-sm" name="sublimation_type">
                                                                <option value="" selected disabled>Select sublimation type</option>
                                                                <option value="garment">Garment Sublimation</option>
                                                                <option value="mug">Mug Sublimation</option>
                                                                <option value="plate">Plate Sublimation</option>
                                                                <option value="puzzle">Puzzle Sublimation</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold small">Item Type</label>
                                                            <select class="form-select form-select-sm" name="sublimation_item">
                                                                <option value="" selected disabled>Select item</option>
                                                                <option value="tshirt">T-Shirt</option>
                                                                <option value="mug">Coffee Mug</option>
                                                                <option value="plate">Ceramic Plate</option>
                                                                <option value="puzzle">Jigsaw Puzzle</option>
                                                                <option value="mousepad">Mouse Pad</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold small">Quantity</label>
                                                            <input type="number" class="form-control form-control-sm" name="sublimation_quantity" min="1" value="1">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold small">Print Size</label>
                                                            <select class="form-select form-select-sm" name="sublimation_size">
                                                                <option value="full">Full Print</option>
                                                                <option value="partial" selected>Partial Print</option>
                                                                <option value="custom">Custom Size</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold small">Material</label>
                                                            <select class="form-select form-select-sm" name="sublimation_material">
                                                                <option value="polyester">Polyester</option>
                                                                <option value="ceramic">Ceramic</option>
                                                                <option value="metal">Metal</option>
                                                                <option value="wood">Wood</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold small">Design Details</label>
                                                            <textarea class="form-control form-control-sm" name="sublimation_notes" rows="2" placeholder="Design file, color requirements, special effects..."></textarea>
                                                        </div>
                                                        
                                                        <!-- Attach Picture/Image -->
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold small">Attach Picture/Image</label>
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <!-- Upload Options Tabs -->
                                                                    <ul class="nav nav-tabs nav-tabs-sm mb-3" id="sublimation-uploadTabs" role="tablist">
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link active" id="sublimation-file-tab" data-bs-toggle="tab" data-bs-target="#sublimation-file-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-upload me-1"></i> Upload Files
                                                                            </button>
                                                                        </li>
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link" id="sublimation-paste-tab" data-bs-toggle="tab" data-bs-target="#sublimation-paste-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-paste me-1"></i> Paste Screenshot
                                                                            </button>
                                                                        </li>
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link" id="sublimation-camera-tab" data-bs-toggle="tab" data-bs-target="#sublimation-camera-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-camera me-1"></i> Take Photo
                                                                            </button>
                                                                        </li>
                                                                    </ul>
                                                                    
                                                                    <div class="tab-content" id="sublimation-uploadTabsContent">
                                                                        <!-- File Upload Tab -->
                                                                        <div class="tab-pane fade show active" id="sublimation-file-tab-pane" role="tabpanel">
                                                                            <div class="mb-3">
                                                                                <input type="file" class="form-control form-control-sm" id="sublimation-image-upload" name="sublimation_image[]" accept="image/*,.pdf,.psd,.ai,.eps" multiple>
                                                                                <div class="form-text small">
                                                                                    Supported formats: JPG, PNG, GIF, PDF, PSD, AI, EPS. Max file size: 10MB per file.
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Paste Screenshot Tab -->
                                                                        <div class="tab-pane fade" id="sublimation-paste-tab-pane" role="tabpanel">
                                                                            <div class="text-center">
                                                                                <div class="mb-3">
                                                                                    <div class="paste-area border-2 border-dashed rounded p-4" id="sublimation-paste-area" style="border-color: #dee2e6; min-height: 150px; background-color: #f8f9fa;">
                                                                                        <i class="fas fa-paste fa-3x text-muted mb-3"></i>
                                                                                        <h6 class="mb-2">Paste Screenshot Here</h6>
                                                                                        <p class="small text-muted mb-3">
                                                                                            Take screenshot (Shift+Alt+S or any tool) then paste with Ctrl+V
                                                                                        </p>
                                                                                        <div class="paste-instructions small">
                                                                                            <div class="row">
                                                                                                <div class="col-6">
                                                                                                    <div class="mb-2">
                                                                                                        <i class="fas fa-desktop me-1"></i> Windows/Linux:<br>
                                                                                                        <span class="text-muted">Ctrl+Shift+S or Print Screen</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="col-6">
                                                                                                    <div class="mb-2">
                                                                                                        <i class="fas fa-apple me-1"></i> macOS:<br>
                                                                                                        <span class="text-muted">Cmd+Shift+4 or Cmd+Shift+5</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Camera Tab -->
                                                                        <div class="tab-pane fade" id="sublimation-camera-tab-pane" role="tabpanel">
                                                                            <div class="text-center">
                                                                                <div class="mb-3">
                                                                                    <div class="camera-preview border rounded p-3" id="sublimation-camera-preview" style="display: none;">
                                                                                        <video id="sublimation-camera-video" autoplay playsinline style="width: 100%; max-height: 300px;"></video>
                                                                                        <div class="mt-3">
                                                                                            <button type="button" class="btn btn-sm btn-primary" id="sublimation-capture-btn">
                                                                                                <i class="fas fa-camera me-1"></i> Capture Photo
                                                                                            </button>
                                                                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="sublimation-camera-retry-btn" style="display: none;">
                                                                                                <i class="fas fa-redo me-1"></i> Retry
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="camera-placeholder border-2 border-dashed rounded p-4" id="sublimation-camera-placeholder" style="border-color: #dee2e6; min-height: 150px; background-color: #f8f9fa;">
                                                                                        <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                                                                                        <h6 class="mb-2">Take a Photo</h6>
                                                                                        <p class="small text-muted mb-3">
                                                                                            Click below to start your camera
                                                                                        </p>
                                                                                        <button type="button" class="btn btn-sm btn-outline-primary" id="sublimation-start-camera-btn">
                                                                                            <i class="fas fa-video me-1"></i> Start Camera
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <!-- Image Preview Container -->
                                                                    <div id="sublimation-image-preview-container" class="d-none mt-3">
                                                                        <h6 class="mb-2">Uploaded Images:</h6>
                                                                        <div id="sublimation-image-previews" class="row g-2"></div>
                                                                    </div>
                                                                    
                                                                    <!-- Upload Progress -->
                                                                    <div id="sublimation-upload-progress" class="progress d-none mt-2" style="height: 6px;">
                                                                        <div id="sublimation-upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                                                    </div>
                                                                    
                                                                    <!-- Uploaded Files List -->
                                                                    <div class="mt-3">
                                                                        <div id="sublimation-uploaded-files-list" class="mb-2">
                                                                            <div class="text-muted small">No files attached yet</div>
                                                                        </div>
                                                                        <div class="d-none" id="sublimation-uploaded-files-actions">
                                                                            <button type="button" class="btn btn-sm btn-outline-danger" id="sublimation-clear-all-files">
                                                                                <i class="fas fa-trash me-1"></i> Clear All Files
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Total Quantity Display -->
                                                        <div class="col-md-12">
                                                            <div class="alert alert-info p-2">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="small fw-semibold mb-2">Order Summary:</div>
                                                                        <div class="mt-2">
                                                                            <span class="small fw-semibold">Total Quantity:</span>
                                                                            <span class="badge bg-primary ms-2" id="sublimation-total-quantity">1</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Embroidery Printing Form -->
                                                <div id="embroidery-form" class="iprint-type-form" style="display: none;">
                                                    <div class="row g-3">
                                                        <!-- Print Size Section - Flexible Multiple Sizes -->
                                                        <div class="col-md-12">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <label class="form-label fw-semibold small">Print Sizes</label>
                                                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-embroidery-print-size">
                                                                    <i class="fas fa-plus me-1"></i> Add Another Print Size
                                                                </button>
                                                            </div>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-hover table-bordered">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th width="5%" class="text-center">#</th>
                                                                            <th width="35%">Print Size</th>
                                                                            <th width="30%">Custom Size</th>
                                                                            <th width="20%">Quantity</th>
                                                                            <th width="10%" class="text-center">Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="embroidery-shirt-sizes-container">
                                                                        <!-- First Shirt Size Row -->
                                                                        <tr class="embroidery-shirt-size-row">
                                                                            <td class="align-middle text-center">1</td>
                                                                            <td>
                                                                                <select class="form-select form-select-sm embroidery-shirt-size-select" name="embroidery_shirt_size[]" required>
                                                                                    <option value="" selected disabled>Select size</option>
                                                                                    <option value="xs">XS (Extra Small)</option>
                                                                                    <option value="s">S (Small)</option>
                                                                                    <option value="m">M (Medium)</option>
                                                                                    <option value="l">L (Large)</option>
                                                                                    <option value="xl">XL (Extra Large)</option>
                                                                                    <option value="2xl">2XL (Double Extra Large)</option>
                                                                                    <option value="3xl">3XL (Triple Extra Large)</option>
                                                                                    <option value="4xl">4XL (4X Extra Large)</option>
                                                                                    <option value="5xl">5XL (5X Extra Large)</option>
                                                                                    <option value="6xl">6XL (6X Extra Large)</option>
                                                                                    <option value="7xl">7XL (7X Extra Large)</option>
                                                                                    <option value="8xl">8XL (8X Extra Large)</option>
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" class="form-control form-control-sm embroidery-shirt-size-quantity" name="embroidery_shirt_size_quantity[]" min="1" value="1" required>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <button type="button" class="btn btn-sm btn-outline-danger delete-embroidery-shirt-size-btn" onclick="deleteEmbroideryShirtSizeRow(this)" disabled title="Cannot delete the only size">
                                                                                    <i class="fas fa-trash"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        <!-- Additional Notes -->
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold small">Additional Notes</label>
                                                            <textarea class="form-control form-control-sm" name="embroidery_notes" rows="2" placeholder="Special instructions, design details, file information..."></textarea>
                                                        </div>

                                                        <!-- Attach Picture/Image -->
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold small">Attach Picture/Image</label>
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <!-- Upload Options Tabs -->
                                                                    <ul class="nav nav-tabs nav-tabs-sm mb-3" id="embroidery-uploadTabs" role="tablist">
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link active" id="embroidery-file-tab" data-bs-toggle="tab" data-bs-target="#embroidery-file-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-upload me-1"></i> Upload Files
                                                                            </button>
                                                                        </li>
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link" id="embroidery-paste-tab" data-bs-toggle="tab" data-bs-target="#embroidery-paste-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-paste me-1"></i> Paste Screenshot
                                                                            </button>
                                                                        </li>
                                                                        <li class="nav-item" role="presentation">
                                                                            <button class="nav-link" id="embroidery-camera-tab" data-bs-toggle="tab" data-bs-target="#embroidery-camera-tab-pane" type="button" role="tab">
                                                                                <i class="fas fa-camera me-1"></i> Take Photo
                                                                            </button>
                                                                        </li>
                                                                    </ul>
                                                                    
                                                                    <div class="tab-content" id="embroidery-uploadTabsContent">
                                                                        <!-- File Upload Tab -->
                                                                        <div class="tab-pane fade show active" id="embroidery-file-tab-pane" role="tabpanel">
                                                                            <div class="mb-3">
                                                                                <input type="file" class="form-control form-control-sm" id="embroidery-image-upload" name="embroidery_image[]" accept="image/*,.pdf,.psd,.ai,.eps" multiple>
                                                                                <div class="form-text small">
                                                                                    Supported formats: JPG, PNG, GIF, PDF, PSD, AI, EPS. Max file size: 10MB per file.
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Paste Screenshot Tab -->
                                                                        <div class="tab-pane fade" id="embroidery-paste-tab-pane" role="tabpanel">
                                                                            <div class="text-center">
                                                                                <div class="mb-3">
                                                                                    <div class="paste-area border-2 border-dashed rounded p-4" id="embroidery-paste-area" style="border-color: #dee2e6; min-height: 150px; background-color: #f8f9fa;">
                                                                                        <i class="fas fa-paste fa-3x text-muted mb-3"></i>
                                                                                        <h6 class="mb-2">Paste Screenshot Here</h6>
                                                                                        <p class="small text-muted mb-3">
                                                                                            Take screenshot (Shift+Alt+S or any tool) then paste with Ctrl+V
                                                                                        </p>
                                                                                        <div class="paste-instructions small">
                                                                                            <div class="row">
                                                                                                <div class="col-6">
                                                                                                    <div class="mb-2">
                                                                                                        <i class="fas fa-desktop me-1"></i> Windows/Linux:<br>
                                                                                                        <span class="text-muted">Ctrl+Shift+S or Print Screen</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="col-6">
                                                                                                    <div class="mb-2">
                                                                                                        <i class="fas fa-apple me-1"></i> macOS:<br>
                                                                                                        <span class="text-muted">Cmd+Shift+4 or Cmd+Shift+5</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Camera Tab -->
                                                                        <div class="tab-pane fade" id="embroidery-camera-tab-pane" role="tabpanel">
                                                                            <div class="text-center">
                                                                                <div class="mb-3">
                                                                                    <div class="camera-preview border rounded p-3" id="embroidery-camera-preview" style="display: none;">
                                                                                        <video id="embroidery-camera-video" autoplay playsinline style="width: 100%; max-height: 300px;"></video>
                                                                                        <div class="mt-3">
                                                                                            <button type="button" class="btn btn-sm btn-primary" id="embroidery-capture-btn">
                                                                                                <i class="fas fa-camera me-1"></i> Capture Photo
                                                                                            </button>
                                                                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="embroidery-camera-retry-btn" style="display: none;">
                                                                                                <i class="fas fa-redo me-1"></i> Retry
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="camera-placeholder border-2 border-dashed rounded p-4" id="embroidery-camera-placeholder" style="border-color: #dee2e6; min-height: 150px; background-color: #f8f9fa;">
                                                                                        <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                                                                                        <h6 class="mb-2">Take a Photo</h6>
                                                                                        <p class="small text-muted mb-3">
                                                                                            Click below to start your camera
                                                                                        </p>
                                                                                        <button type="button" class="btn btn-sm btn-outline-primary" id="embroidery-start-camera-btn">
                                                                                            <i class="fas fa-video me-1"></i> Start Camera
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <!-- Image Preview Container -->
                                                                    <div id="embroidery-image-preview-container" class="d-none mt-3">
                                                                        <h6 class="mb-2">Uploaded Images:</h6>
                                                                        <div id="embroidery-image-previews" class="row g-2"></div>
                                                                    </div>
                                                                    
                                                                    <!-- Upload Progress -->
                                                                    <div id="embroidery-upload-progress" class="progress d-none mt-2" style="height: 6px;">
                                                                        <div id="embroidery-upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                                                    </div>
                                                                    
                                                                    <!-- Uploaded Files List -->
                                                                    <div class="mt-3">
                                                                        <div id="embroidery-uploaded-files-list" class="mb-2">
                                                                            <div class="text-muted small">No files attached yet</div>
                                                                        </div>
                                                                        <div class="d-none" id="embroidery-uploaded-files-actions">
                                                                            <button type="button" class="btn btn-sm btn-outline-danger" id="embroidery-clear-all-files">
                                                                                <i class="fas fa-trash me-1"></i> Clear All Files
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Total Quantity Display -->
                                                        <div class="col-md-12">
                                                            <div class="alert alert-info p-2">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="small fw-semibold mb-2">Order Summary:</div>
                                                                        <div class="mt-2">
                                                                            <span class="small fw-semibold">Total Quantity:</span>
                                                                            <span class="badge bg-primary ms-2" id="embroidery-total-quantity">1</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                
                                                <!-- Other Items Form -->
                                                <div id="other-form" class="iprint-type-form" style="display: none;">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold small">Item Category</label>
                                                            <select class="form-select form-select-sm" name="other_category">
                                                                <option value="" selected disabled>Select category</option>
                                                                <option value="promotional">Promotional Items</option>
                                                                <option value="office">Office Supplies</option>
                                                                <option value="event">Event Materials</option>
                                                                <option value="custom">Custom Products</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold small">Item Name</label>
                                                            <input type="text" class="form-control form-control-sm" name="other_item" placeholder="e.g., Pens, Mugs, Banners">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold small">Quantity</label>
                                                            <input type="number" class="form-control form-control-sm" name="other_quantity" min="1" value="1">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold small">Unit Price (₱)</label>
                                                            <input type="number" class="form-control form-control-sm" name="other_price" min="0" step="0.01" placeholder="0.00">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-semibold small">Material</label>
                                                            <input type="text" class="form-control form-control-sm" name="other_material" placeholder="e.g., Plastic, Metal, Paper">
                                                        </div>
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-semibold small">Item Description</label>
                                                            <textarea class="form-control form-control-sm" name="other_notes" rows="3" placeholder="Detailed description, specifications, customization requirements..."></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    

                                    
                                    <!-- Consol Form -->
                                    <div id="consol-form" style="display: none;">
                                        <h6 class="mb-3">Consol Order Details</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold small">Consolidation Type</label>
                                                <select class="form-select form-select-sm" name="consol_type">
                                                    <option value="" selected disabled>Select type</option>
                                                    <option value="local">Local Consolidation</option>
                                                    <option value="international">International Shipment</option>
                                                    <option value="warehouse">Warehouse Storage</option>
                                                    <option value="distribution">Distribution</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold small">Package Count</label>
                                                <input type="number" class="form-control form-control-sm" name="consol_packages" min="1" value="1">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Total Weight (kg)</label>
                                                <input type="number" class="form-control form-control-sm" name="consol_weight" min="0.1" step="0.1" value="1.0">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Volume (m³)</label>
                                                <input type="number" class="form-control form-control-sm" name="consol_volume" min="0.01" step="0.01" value="0.1">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Destination</label>
                                                <input type="text" class="form-control form-control-sm" name="consol_destination" placeholder="City/Country">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold small">Consolidation Notes</label>
                                                <textarea class="form-control form-control-sm" name="consol_notes" rows="2" placeholder="Package contents, handling instructions, etc."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Class Form -->
                                    <div id="class-form" style="display: none;">
                                        <h6 class="mb-3">Class Order Details</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold small">Class Type</label>
                                                <select class="form-select form-select-sm" name="class_type">
                                                    <option value="" selected disabled>Select class type</option>
                                                    <option value="training">Training/Workshop</option>
                                                    <option value="seminar">Seminar</option>
                                                    <option value="course">Course</option>
                                                    <option value="tutorial">Tutorial Session</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold small">Participant Count</label>
                                                <input type="number" class="form-control form-control-sm" name="class_participants" min="1" value="1">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Duration (hours)</label>
                                                <input type="number" class="form-control form-control-sm" name="class_duration" min="1" value="2">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Date</label>
                                                <input type="date" class="form-control form-control-sm" name="class_date">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Time</label>
                                                <input type="time" class="form-control form-control-sm" name="class_time">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold small">Class Requirements</label>
                                                <textarea class="form-control form-control-sm" name="class_requirements" rows="2" placeholder="Materials needed, topics to cover, etc."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Cinco Form -->
                                    <div id="cinco-form" style="display: none;">
                                        <h6 class="mb-3">Cinco Order Details</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold small">Cinco Package Type</label>
                                                <select class="form-select form-select-sm" name="cinco_type" required>
                                                    <option value="" selected disabled>Select package type</option>
                                                    <option value="basic">Basic Package</option>
                                                    <option value="premium">Premium Package</option>
                                                    <option value="deluxe">Deluxe Package</option>
                                                    <option value="custom">Custom Package</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold small">Service Category</label>
                                                <select class="form-select form-select-sm" name="cinco_category" required>
                                                    <option value="" selected disabled>Select category</option>
                                                    <option value="printing">Printing Services</option>
                                                    <option value="design">Design Services</option>
                                                    <option value="consultation">Consultation</option>
                                                    <option value="installation">Installation</option>
                                                    <option value="maintenance">Maintenance</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Quantity</label>
                                                <input type="number" class="form-control form-control-sm" name="cinco_quantity" min="1" value="1" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Duration (Hours)</label>
                                                <input type="number" class="form-control form-control-sm" name="cinco_duration" min="1" value="1" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Priority Level</label>
                                                <select class="form-select form-select-sm" name="cinco_priority" required>
                                                    <option value="standard">Standard</option>
                                                    <option value="express">Express</option>
                                                    <option value="urgent">Urgent</option>
                                                    <option value="vip">VIP</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold small">Start Date</label>
                                                <input type="date" class="form-control form-control-sm" name="cinco_start_date" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold small">End Date</label>
                                                <input type="date" class="form-control form-control-sm" name="cinco_end_date">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold small">Service Details</label>
                                                <textarea class="form-control form-control-sm" name="cinco_details" rows="3" placeholder="Specific requirements, deliverables, special instructions..." required></textarea>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold small">Additional Notes</label>
                                                <textarea class="form-control form-control-sm" name="cinco_notes" rows="2" placeholder="Any additional information or special requests..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden field to store selected order type -->
                        <input type="hidden" id="order_type" name="order_type" value="">
                    </div>
                </div>

                <!-- Payment Information Card - COMPACT -->
                <div class="card mb-4">
                    <div class="card-header bg-light py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-credit-card text-info me-2"></i>
                            Payment Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="mop" class="form-label fw-semibold small">
                                    Payment Method <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">
                                        <i class="fas fa-wallet"></i>
                                    </span>
                                    <select class="form-select form-select-sm" id="mop" name="mop" required>
                                        <option value="" selected disabled>Select payment method</option>
                                        <option value="cash">Cash</option>
                                        <option value="gcash">GCash</option>
                                        <option value="maya">Maya</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="debit_card">Debit Card</option>
                                        <option value="check">Check</option>
                                        <option value="cod">Cash on Delivery</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="boost" class="form-label fw-semibold small">
                                    Service Priority
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">
                                        <i class="fas fa-bolt"></i>
                                    </span>
                                    <select class="form-select form-select-sm" id="boost" name="boost">
                                        <option value="" selected>Standard Processing</option>
                                        <option value="urgent">Urgent Delivery (+15%)</option>
                                        <option value="priority">Priority Processing (+10%)</option>
                                        <option value="express">Express Service (+20%)</option>
                                        <option value="vip">VIP Treatment (+25%)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="down_payment" class="form-label fw-semibold small">
                                    Down Payment <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control form-control-sm text-end" id="down_payment" name="down_payment" 
                                           min="0" value="0" step="0.01" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">
                                    Payment Status
                                </label>
                                <div class="payment-status">
                                    <div class="form-check form-check-sm">
                                        <input class="form-check-input" type="checkbox" id="full_payment" name="full_payment">
                                        <label class="form-check-label small" for="full_payment">
                                            <i class="fas fa-check-circle text-success me-1"></i>
                                            Full Payment
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">
                                    Transaction Date
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">
                                        <i class="fas fa-calendar"></i>
                                    </span>
                                    <input type="text" class="form-control form-control-sm" value="{{ now()->format('M d, Y') }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="reset" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-eraser me-2"></i> Clear Form
                                </button>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary btn-sm px-4">
                                    <i class="fas fa-check-circle me-2"></i> Complete Transaction
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right Column: Summary & Calculations -->
        <div class="col-lg-4">
            <!-- Order Summary Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        Order Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="summary-item">
                        <div class="summary-label small">Subtotal</div>
                        <div class="summary-value small" id="summary-subtotal">₱500.00</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label small">Service Fee</div>
                        <div class="summary-value small" id="summary-service">₱0.00</div>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-item total">
                        <div class="summary-label small">Total Amount</div>
                        <div class="summary-value small" id="summary-total">₱500.00</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label small">Down Payment</div>
                        <div class="summary-value small text-success" id="summary-down">₱0.00</div>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-item balance">
                        <div class="summary-label small">Remaining Balance</div>
                        <div class="summary-value small" id="summary-balance">₱500.00</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('sales.products') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list-alt me-2"></i> Browse Products
                        </a>
                        <a href="{{ route('sales.pricing') }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-tags me-2"></i> View Pricing
                        </a>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="generateInvoice()">
                            <i class="fas fa-file-invoice me-2"></i> Generate Invoice
                        </button>
                    </div>
                </div>
            </div>

            <!-- Help & Guidelines Card -->
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="guidelines-list">
                        <li class="small">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Required:</strong> Customer, Marketplace, Location, Contact, Item, Quantity, Down Payment, Payment Method
                        </li>
                        <li class="small">
                            <i class="fas fa-calculator text-primary me-2"></i>
                            <strong>Auto-Calculated:</strong> Total and Balance
                        </li>
                        <li class="small">
                            <i class="fas fa-money-bill-wave text-success me-2"></i>
                            <strong>Payment:</strong> Check "Full Payment" to auto-fill
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
/* Compact Styling */
.header-icon-wrapper {
    position: relative;
}

.header-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1);
}

/* Progress Steps */
.steps {
    display: flex;
    justify-content: space-between;
    position: relative;
}

.steps::before {
    content: '';
    position: absolute;
    top: 18px;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #e9ecef;
    z-index: 1;
}

.step {
    position: relative;
    z-index: 2;
    text-align: center;
    flex: 1;
}

.step.active .step-number {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.step.active .step-label {
    color: #0d6efd;
    font-weight: 600;
}

.step-number {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: white;
    border: 2px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 6px;
    font-weight: 600;
    font-size: 14px;
    color: #6c757d;
}

.step-label {
    font-size: 12px;
    color: #6c757d;
}

/* Compact Form Controls */
.form-control-sm, .form-select-sm {
    border-radius: 6px;
    border: 1px solid #dee2e6;
    padding: 6px 10px;
    font-size: 14px;
    transition: all 0.2s;
}

.form-control-sm:focus, .form-select-sm:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

.input-group-sm .input-group-text {
    font-size: 14px;
    padding: 6px 10px;
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #495057;
}

/* Compact Summary Card */
.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f1f3f4;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item.total {
    border-top: 2px solid #dee2e6;
    border-bottom: 2px solid #dee2e6;
    margin: 6px 0;
    padding: 12px 0;
}

.summary-item.balance {
    font-size: 16px;
    font-weight: 600;
    color: #198754;
}

.summary-label {
    color: #5f6368;
    font-weight: 500;
}

.summary-value {
    font-weight: 600;
    color: #202124;
}

.summary-divider {
    height: 1px;
    background-color: #f1f3f4;
    margin: 6px 0;
}

/* Compact Payment Status */
.payment-status {
    background-color: #f8f9fa;
    border-radius: 6px;
    padding: 8px;
    border: 1px solid #dee2e6;
}

.form-check-sm .form-check-input {
    width: 1.1em;
    height: 1.1em;
    margin-top: 0.15em;
}

.form-check-sm .form-check-label {
    font-size: 14px;
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

/* Compact Guidelines List */
.guidelines-list {
    list-style: none;
    padding-left: 0;
    margin-bottom: 0;
}

.guidelines-list li {
    padding: 6px 0;
    border-bottom: 1px solid #f1f3f4;
}

.guidelines-list li:last-child {
    border-bottom: none;
}

/* Compact Card Styling */
.card {
    border-radius: 10px;
    border: 1px solid #e0e0e0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
}

.card-header {
    border-bottom: 1px solid #e0e0e0;
    background-color: #f8f9fa;
    padding: 12px 16px;
    border-radius: 10px 10px 0 0 !important;
}

.card-body {
    padding: 16px;
}

/* Compact Button Styling */
.btn-sm {
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0a58ca 0%, #084298 100%);
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(13, 110, 253, 0.2);
}

.btn-outline-secondary {
    border-color: #dee2e6;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #f8f9fa;
    border-color: #adb5bd;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .steps {
        flex-direction: column;
        gap: 12px;
    }
    
    .steps::before {
        display: none;
    }
    
    .step {
        text-align: left;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .step-number {
        margin: 0;
        flex-shrink: 0;
    }
    
    .header-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    /* Image Preview Styles */
    #image-preview-container .card {
        border: 1px solid #dee2e6;
        transition: transform 0.2s;
    }
    
    #image-preview-container .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    #image-preview-container img {
        background-color: #f8f9fa;
        padding: 5px;
    }
    
    #image-preview-container .btn-outline-danger {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    /* File upload custom styling */
    #dtf-image-upload {
        border: 2px dashed #dee2e6;
        padding: 1rem;
        background-color: #f8f9fa;
        transition: all 0.3s;
    }
    
    #dtf-image-upload:hover {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }
    
    #dtf-image-upload:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    /* Camera Tab Styles */
    .nav-tabs-sm .nav-link {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
    
    /* Lanyard Image Upload Styles */
    #lanyard-uploaded-files-container .d-flex {
        transition: all 0.2s;
    }
    
    #lanyard-uploaded-files-container .d-flex:hover {
        background-color: #f8f9fa;
    }
    
    #lanyard-uploaded-files-container img {
        object-fit: cover;
    }
    
    #lanyard-image-upload {
        border: 2px dashed #dee2e6;
        padding: 1rem;
        background-color: #f8f9fa;
        transition: all 0.3s;
    }
    
    #lanyard-image-upload:hover {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }
    
    #lanyard-image-upload:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    #lanyard-paste-area {
        cursor: pointer;
        transition: all 0.3s;
    }
    
    #lanyard-paste-area:hover {
        border-color: #0d6efd !important;
        background-color: #e7f1ff !important;
    }
    
    /* Tarpaulin Form Styles */
    #tarpaulin-uploaded-files-container img {
        object-fit: cover;
    }
    
    #tarpaulin-image-upload {
        border: 2px dashed #dee2e6;
        padding: 1rem;
        background-color: #f8f9fa;
        transition: all 0.3s;
    }
    
    #tarpaulin-image-upload:hover {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }
    
    #tarpaulin-image-upload:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    #tarpaulin-paste-area {
        cursor: pointer;
        transition: all 0.3s;
    }
    
    #tarpaulin-paste-area:hover {
        border-color: #0d6efd !important;
        background-color: #e7f1ff !important;
    }
    
    /* Sublimation Form Styles */
    #sublimation-uploaded-files-container img {
        object-fit: cover;
    }
    
    #sublimation-image-upload {
        border: 2px dashed #dee2e6;
        padding: 1rem;
        background-color: #f8f9fa;
        transition: all 0.3s;
    }
    
    #sublimation-image-upload:hover {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }
    
    #sublimation-image-upload:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    #sublimation-paste-area {
        cursor: pointer;
        transition: all 0.3s;
    }
    
    #sublimation-paste-area:hover {
        border-color: #0d6efd !important;
        background-color: #e7f1ff !important;
    }
    
    /* Embroidery Form Styles */
    #embroidery-uploaded-files-container img {
        object-fit: cover;
    }
    
    #embroidery-image-upload {
        border: 2px dashed #dee2e6;
        padding: 1rem;
        background-color: #f8f9fa;
        transition: all 0.3s;
    }
    
    #embroidery-image-upload:hover {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }
    
    #embroidery-image-upload:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    #embroidery-paste-area {
        cursor: pointer;
        transition: all 0.3s;
    }
    
    #embroidery-paste-area:hover {
        border-color: #0d6efd !important;
        background-color: #e7f1ff !important;
    }
    
    #lanyard-camera-preview {
        background-color: #000;
    }
    
    #lanyard-captured-photo-preview {
        background-color: #f8f9fa;
        padding: 5px;
    }
    

    
    #camera-preview {
        background-color: #000;
    }
    
    #captured-photo {
        background-color: #f8f9fa;
    }
    
    /* Camera button group */
    #camera-tab-pane .btn-group {
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    #camera-tab-pane .btn-group .btn {
        flex: 1;
        min-width: 120px;
    }
    
    /* Paste Screenshot Styles */
    .border-dashed {
        border-style: dashed !important;
    }
    
    #paste-area {
        cursor: pointer;
        transition: all 0.3s;
    }
    
    #paste-area:hover {
        border-color: #0d6efd !important;
        background-color: #e7f1ff !important;
        transform: translateY(-2px);
    }
    
    #paste-area:focus {
        outline: none;
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .paste-instructions code {
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.8rem;
        border: 1px solid #dee2e6;
    }
    
    /* Quantity breakdown styles */
    #print-size-breakdown, #shirt-size-breakdown {
        max-height: 120px;
        overflow-y: auto;
        padding-right: 5px;
    }
    
    #print-size-breakdown::-webkit-scrollbar,
    #shirt-size-breakdown::-webkit-scrollbar {
        width: 4px;
    }
    
    #print-size-breakdown::-webkit-scrollbar-track,
    #shirt-size-breakdown::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }
    
    #print-size-breakdown::-webkit-scrollbar-thumb,
    #shirt-size-breakdown::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 2px;
    }
    
    #print-size-breakdown::-webkit-scrollbar-thumb:hover,
    #shirt-size-breakdown::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Add Another Printing Type Button */
    #add-another-printing-type {
        transition: all 0.3s;
        border-width: 2px;
    }
    
    #add-another-printing-type:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    #add-another-printing-type:active {
        transform: translateY(0);
    }
    
    /* Active state for printing type buttons */
    .iprint-option-btn.active {
        border-width: 3px !important;
        font-weight: bold !important;
        transform: scale(0.98);
    }
    
    /* Table styling */
    #printing-types-table {
        font-size: 0.85rem;
    }
    
    #printing-types-table th {
        font-weight: 600;
        background-color: #f8f9fa;
    }
    
    #printing-types-table .btn-sm {
        padding: 0.15rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>
@endpush

@endsection
@push('scripts')
<!-- Bootstrap JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// MINIMAL VERSION - TESTING ONLY
console.log('=== MINIMAL JAVASCRIPT LOADED ===');

// Simple Order Form Functions
function showOrderForm(type) {
    console.log('showOrderForm called with type:', type);
    
    try {
        // Hide all forms first
        document.getElementById('iprint-form').style.display = 'none';
        document.getElementById('consol-form').style.display = 'none';
        document.getElementById('class-form').style.display = 'none';
        document.getElementById('cinco-form').style.display = 'none';
        
        // Show the selected form
        document.getElementById(type + '-form').style.display = 'block';
        
        // Show the forms container
        document.getElementById('order-forms-container').style.display = 'block';
        
        // Set hidden field
        document.getElementById('order_type').value = type;
        
        console.log('Form shown successfully:', type);
    } catch (error) {
        console.error('ERROR in showOrderForm:', error);
    }
}

// iPrint Option Selection Function
function showIprintOption(option) {
    console.log('Selected iPrint option:', option);
    
    try {
        // Check if there are any items of this type in the table
        const itemsOfType = document.querySelectorAll(`tr[data-instance-id^="${option}_"]`);
        
        if (itemsOfType.length > 0) {
            // There are items of this type, show the form
            // Hide all iPrint type forms
            const typeForms = document.querySelectorAll('.iprint-type-form');
            typeForms.forEach(form => {
                form.style.display = 'none';
            });
            
            // Show the selected iPrint type form
            const selectedForm = document.getElementById(option + '-form');
            if (selectedForm) {
                selectedForm.style.display = 'block';
            }
        } else {
            // No items of this type, just add to table but don't show form
            // Hide all iPrint type forms
            const typeForms = document.querySelectorAll('.iprint-type-form');
            typeForms.forEach(form => {
                form.style.display = 'none';
            });
            
            // Don't show any form since there are no items of this type
        }
        
        // Update selected type text
        const typeNames = {
            'dtf': 'DTF Printing',
            'lanyard': 'Lanyard Printing',
            'tarpaulin': 'Tarpaulin Printing',
            'sublimation': 'Sublimation Printing',
            'embroidery': 'Embroidery',
            'other': 'Other Items'
        };
        
        const selectedTypeElement = document.getElementById('iprint-selected-type');
        if (selectedTypeElement) {
            selectedTypeElement.textContent = 'Selected: ' + (typeNames[option] || option);
        }
        
        // Show the details container
        const detailsContainer = document.getElementById('iprint-details-container');
        if (detailsContainer) {
            detailsContainer.style.display = 'block';
        }
        
        // Set hidden field if it exists
        const iprintTypeField = document.getElementById('iprint_type');
        if (iprintTypeField) {
            iprintTypeField.value = option;
        }
        
        // Add selected printing type to the table
        const instanceId = addPrintingTypeToTable(option);
        
        // Setup form completion listeners for this instance
        if (instanceId) {
            setupFormCompletionListeners(option, instanceId);
        }
        
        console.log('iPrint option shown successfully:', option);
    } catch (error) {
        console.error('ERROR in showIprintOption:', error);
    }
}

// Add Printing Type to Table Function
function addPrintingTypeToTable(option) {
    let instanceId = null;
    const typeNames = {
        'dtf': 'DTF Printing',
        'lanyard': 'Lanyard Printing',
        'tarpaulin': 'Tarpaulin Printing',
        'sublimation': 'Sublimation Printing',
        'embroidery': 'Embroidery',
        'other': 'Other Items'
    };
    
    const displayName = typeNames[option] || option;
    const iconClasses = {
        'dtf': 'fas fa-print text-primary',
        'lanyard': 'fas fa-id-card text-success',
        'tarpaulin': 'fas fa-image text-info',
        'sublimation': 'fas fa-fire text-warning',
        'embroidery': 'fas fa-threads text-danger',
        'other': 'fas fa-boxes text-secondary'
    };
    
    const iconClass = iconClasses[option] || 'fas fa-box text-secondary';
    
    // Show the table container if hidden
    const tableContainer = document.getElementById('selected-printing-types-container');
    if (tableContainer) {
        tableContainer.style.display = 'block';
    }
    
    // Show the iprint-details-container if this is the first item
    const detailsContainer = document.getElementById('iprint-details-container');
    if (detailsContainer) {
        detailsContainer.style.display = 'block';
    }
    
    // Count existing rows for numbering
    const existingRows = document.querySelectorAll('#printing-types-tbody tr');
    let rowNumber = existingRows.length + 1;
    
    // Generate unique ID for this printing type instance
    instanceId = `${option}_${Date.now()}_${Math.floor(Math.random() * 1000)}`;
    
    // Add new row to table
    const tbody = document.getElementById('printing-types-tbody');
    if (tbody) {
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-instance-id', instanceId);
        newRow.innerHTML = `
            <td class="align-middle text-center">${rowNumber}</td>
            <td class="align-middle">
                <i class="${iconClass} me-2"></i>
                <span class="printing-type-name">${displayName}</span>
                <span class="badge bg-light text-dark ms-2 small">#${rowNumber}</span>
            </td>
            <td class="align-middle">
                <span class="badge bg-warning">Pending Details</span>
            </td>
            <td class="align-middle small">
                <div class="text-muted">Click "Edit Details" to fill out form</div>
            </td>
            <td class="align-middle">
                <button type="button" class="btn btn-sm btn-outline-primary me-1 edit-printing-type-btn" data-option="${option}" data-instance-id="${instanceId}">
                    <i class="fas fa-edit me-1"></i> Edit
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger remove-printing-type-btn">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </td>
        `;
        
        tbody.appendChild(newRow);
        
        // Add event listeners to the new buttons
        const editBtn = newRow.querySelector('.edit-printing-type-btn');
        const removeBtn = newRow.querySelector('.remove-printing-type-btn');
        
        if (editBtn) {
            editBtn.addEventListener('click', function() {
                const optionToEdit = this.getAttribute('data-option');
                const instanceId = this.getAttribute('data-instance-id');
                console.log('Edit printing type:', optionToEdit, 'Instance:', instanceId);
                
                // Show the corresponding form
                const typeForms = document.querySelectorAll('.iprint-type-form');
                typeForms.forEach(form => {
                    form.style.display = 'none';
                });
                
                const selectedForm = document.getElementById(optionToEdit + '-form');
                if (selectedForm) {
                    selectedForm.style.display = 'block';
                    
                    // Set instance ID on the form for tracking
                    selectedForm.setAttribute('data-current-instance', instanceId);
                    
                    // Setup form completion listeners for this instance
                    setupFormCompletionListeners(optionToEdit, instanceId);
                }
                
                // Update status in table
                const statusBadge = this.closest('tr').querySelector('.badge');
                if (statusBadge) {
                    statusBadge.className = 'badge bg-info';
                    statusBadge.textContent = 'Editing';
                }
                
                // Update details cell
                const detailsCell = this.closest('tr').querySelector('td:nth-child(4)');
                if (detailsCell) {
                    detailsCell.innerHTML = '<div class="text-info small"><i class="fas fa-edit me-1"></i> Currently editing form</div>';
                }
                
                // Update the "Selected: " text to show what's being edited
                const typeNames = {
                    'dtf': 'DTF Printing',
                    'lanyard': 'Lanyard Printing',
                    'tarpaulin': 'Tarpaulin Printing',
                    'sublimation': 'Sublimation Printing',
                    'embroidery': 'Embroidery',
                    'other': 'Other Items'
                };
                
                const selectedTypeElement = document.getElementById('iprint-selected-type');
                if (selectedTypeElement) {
                    selectedTypeElement.textContent = 'Selected: ' + (typeNames[optionToEdit] || optionToEdit);
                }
                
                // Scroll to the form
                selectedForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }
        
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                const row = this.closest('tr');
                const typeName = row.querySelector('.printing-type-name').textContent;
                const option = this.getAttribute('data-option');
                
                if (confirm(`Remove "${typeName}" from the order?`)) {
                    row.remove();
                    updatePrintingTypeTableNumbers();
                    
                    // Check if there are any remaining items of this printing type
                    const remainingItemsOfType = document.querySelectorAll(`tr[data-instance-id^="${option}_"]`);
                    
                    if (remainingItemsOfType.length === 0) {
                        // No more items of this type, hide the form
                        const form = document.getElementById(option + '-form');
                        if (form) {
                            form.style.display = 'none';
                        }
                        
                        // Also remove active class from the printing type button
                        const printingTypeBtn = document.querySelector(`.iprint-option-btn[data-option="${option}"]`);
                        if (printingTypeBtn) {
                            printingTypeBtn.classList.remove('active');
                        }
                    }
                    
                    // Hide table if empty
                    const remainingRows = document.querySelectorAll('#printing-types-tbody tr');
                    if (remainingRows.length === 0) {
                        const tableContainer = document.getElementById('selected-printing-types-container');
                        if (tableContainer) {
                            tableContainer.style.display = 'none';
                        }
                        
                        // Also hide the iprint-details-container when no items exist
                        const detailsContainer = document.getElementById('iprint-details-container');
                        if (detailsContainer) {
                            detailsContainer.style.display = 'none';
                        }
                        
                        // Reset the "Selected: " text
                        const selectedTypeElement = document.getElementById('iprint-selected-type');
                        if (selectedTypeElement) {
                            selectedTypeElement.textContent = 'Selected: DTF Printing'; // Default
                        }
                    }
                }
            });
        }
        
        console.log(`Added "${displayName}" to printing types table (Instance: ${instanceId})`);
    }
    
    return instanceId;
}

// Update row numbers in printing types table
function updatePrintingTypeTableNumbers() {
    const rows = document.querySelectorAll('#printing-types-tbody tr');
    rows.forEach((row, index) => {
        const numberCell = row.querySelector('td:first-child');
        if (numberCell) {
            numberCell.textContent = index + 1;
        }
        
        // Update the instance badge number
        const instanceBadge = row.querySelector('.printing-type-name + .badge');
        if (instanceBadge) {
            instanceBadge.textContent = `#${index + 1}`;
        }
    });
}

// Check if a form is complete and update table status
function checkFormCompletion(option, instanceId) {
    const form = document.getElementById(option + '-form');
    if (!form) return false;
    
    let isComplete = true;
    let missingFields = [];
    
    // Check required fields based on printing type
    switch(option) {
        case 'dtf':
            // Check print sizes
            const printSizeSelects = form.querySelectorAll('.print-size-select');
            const printSizeQuantities = form.querySelectorAll('.print-size-quantity');
            const materialType = form.querySelector('select[name="dtf_material_type"]');
            const brand = form.querySelector('select[name="dtf_brand"]');
            const color = form.querySelector('select[name="dtf_color"]');
            
            // Check at least one print size is selected with quantity
            let hasValidPrintSize = false;
            printSizeSelects.forEach((select, index) => {
                if (select.value && select.value !== '' && 
                    printSizeQuantities[index] && 
                    printSizeQuantities[index].value && 
                    parseInt(printSizeQuantities[index].value) > 0) {
                    hasValidPrintSize = true;
                }
            });
            
            if (!hasValidPrintSize) {
                isComplete = false;
                missingFields.push('Print Size with Quantity');
            }
            
            if (!materialType || !materialType.value || materialType.value === '') {
                isComplete = false;
                missingFields.push('Material Type');
            }
            
            if (!brand || !brand.value || brand.value === '') {
                isComplete = false;
                missingFields.push('Brand');
            }
            
            if (!color || !color.value || color.value === '') {
                isComplete = false;
                missingFields.push('Color');
            }
            break;
            
        case 'lanyard':
            // Check lanyard fields
            const lanyardType = form.querySelector('select[name="lanyard_type"]');
            const lanyardQuantity = form.querySelector('input[name="lanyard_quantity"]');
            
            if (!lanyardType || !lanyardType.value || lanyardType.value === '') {
                isComplete = false;
                missingFields.push('Lanyard Type');
            }
            
            if (!lanyardQuantity || !lanyardQuantity.value || parseInt(lanyardQuantity.value) <= 0) {
                isComplete = false;
                missingFields.push('Quantity');
            }
            break;
            
        case 'tarpaulin':
            // Check tarpaulin fields
            const tarpaulinSize = form.querySelector('select[name="tarpaulin_size"]');
            const tarpaulinQuantity = form.querySelector('input[name="tarpaulin_quantity"]');
            
            if (!tarpaulinSize || !tarpaulinSize.value || tarpaulinSize.value === '') {
                isComplete = false;
                missingFields.push('Tarpaulin Size');
            }
            
            if (!tarpaulinQuantity || !tarpaulinQuantity.value || parseInt(tarpaulinQuantity.value) <= 0) {
                isComplete = false;
                missingFields.push('Quantity');
            }
            break;
            
        case 'sublimation':
            // Check sublimation fields
            const sublimationItem = form.querySelector('select[name="sublimation_item"]');
            const sublimationQuantity = form.querySelector('input[name="sublimation_quantity"]');
            
            if (!sublimationItem || !sublimationItem.value || sublimationItem.value === '') {
                isComplete = false;
                missingFields.push('Item Type');
            }
            
            if (!sublimationQuantity || !sublimationQuantity.value || parseInt(sublimationQuantity.value) <= 0) {
                isComplete = false;
                missingFields.push('Quantity');
            }
            break;
            
        case 'embroidery':
            // Check embroidery fields
            const embroideryType = form.querySelector('select[name="embroidery_type"]');
            const embroideryQuantity = form.querySelector('input[name="embroidery_quantity"]');
            
            if (!embroideryType || !embroideryType.value || embroideryType.value === '') {
                isComplete = false;
                missingFields.push('Embroidery Type');
            }
            
            if (!embroideryQuantity || !embroideryQuantity.value || parseInt(embroideryQuantity.value) <= 0) {
                isComplete = false;
                missingFields.push('Quantity');
            }
            break;
            
        case 'other':
            // Check other items fields
            const otherItem = form.querySelector('input[name="other_item"]');
            const otherQuantity = form.querySelector('input[name="other_quantity"]');
            
            if (!otherItem || !otherItem.value || otherItem.value.trim() === '') {
                isComplete = false;
                missingFields.push('Item Description');
            }
            
            if (!otherQuantity || !otherQuantity.value || parseInt(otherQuantity.value) <= 0) {
                isComplete = false;
                missingFields.push('Quantity');
            }
            break;
    }
    
    // Find the corresponding table row and update status
    const tableRow = document.querySelector(`tr[data-instance-id="${instanceId}"]`);
    if (tableRow) {
        const statusBadge = tableRow.querySelector('.badge');
        const detailsCell = tableRow.querySelector('td:nth-child(4)');
        
        if (isComplete) {
            // Update to "Completed" status
            if (statusBadge) {
                statusBadge.className = 'badge bg-success';
                statusBadge.textContent = 'Completed';
            }
            
            if (detailsCell) {
                detailsCell.innerHTML = '<div class="text-success small"><i class="fas fa-check-circle me-1"></i> All required fields filled</div>';
            }
        } else {
            // Update to show missing fields
            if (statusBadge) {
                statusBadge.className = 'badge bg-warning';
                statusBadge.textContent = 'Incomplete';
            }
            
            if (detailsCell) {
                detailsCell.innerHTML = `<div class="text-warning small"><i class="fas fa-exclamation-triangle me-1"></i> Missing: ${missingFields.join(', ')}</div>`;
            }
        }
    }
    
    return isComplete;
}

// Check all existing forms for completion (for page load)
function checkAllFormsCompletion() {
    const tableRows = document.querySelectorAll('#printing-types-tbody tr');
    
    // Handle iprint-details-container visibility
    const detailsContainer = document.getElementById('iprint-details-container');
    if (tableRows.length === 0) {
        // No items, hide the details container
        if (detailsContainer) {
            detailsContainer.style.display = 'none';
        }
        
        // Also hide the table container
        const tableContainer = document.getElementById('selected-printing-types-container');
        if (tableContainer) {
            tableContainer.style.display = 'none';
        }
        
        // Reset the "Selected: " text
        const selectedTypeElement = document.getElementById('iprint-selected-type');
        if (selectedTypeElement) {
            selectedTypeElement.textContent = 'Selected: DTF Printing'; // Default
        }
    } else {
        // Items exist, show the details container
        if (detailsContainer) {
            detailsContainer.style.display = 'block';
        }
        
        // Show the table container
        const tableContainer = document.getElementById('selected-printing-types-container');
        if (tableContainer) {
            tableContainer.style.display = 'block';
        }
    }
    
    // First, hide all forms
    const typeForms = document.querySelectorAll('.iprint-type-form');
    typeForms.forEach(form => {
        form.style.display = 'none';
    });
    
    // Then check each row and show forms only for items that exist
    tableRows.forEach(row => {
        const instanceId = row.getAttribute('data-instance-id');
        const editButton = row.querySelector('.edit-printing-type-btn');
        
        if (instanceId && editButton) {
            const option = editButton.getAttribute('data-option');
            checkFormCompletion(option, instanceId);
        }
    });
    
    // Also remove active class from printing type buttons if no items of that type
    const printingTypeButtons = document.querySelectorAll('.iprint-option-btn');
    printingTypeButtons.forEach(button => {
        const option = button.getAttribute('data-option');
        const itemsOfType = document.querySelectorAll(`tr[data-instance-id^="${option}_"]`);
        
        if (itemsOfType.length === 0) {
            button.classList.remove('active');
        }
    });
}

// Setup form field change listeners for automatic status updates
function setupFormCompletionListeners(option, instanceId) {
    const form = document.getElementById(option + '-form');
    if (!form) return;
    
    // Set the instance ID on the form
    form.setAttribute('data-instance-id', instanceId);
    
    // Function to check completion on any field change
    const checkCompletion = () => {
        checkFormCompletion(option, instanceId);
    };
    
    // Add event listeners to all form fields
    const allInputs = form.querySelectorAll('input, select, textarea');
    allInputs.forEach(input => {
        // Remove existing listeners to avoid duplicates
        input.removeEventListener('change', checkCompletion);
        input.removeEventListener('input', checkCompletion);
        
        // Add new listeners
        input.addEventListener('change', checkCompletion);
        
        // For text inputs, also listen to input events for real-time updates
        if (input.type === 'text' || input.type === 'number') {
            input.addEventListener('input', checkCompletion);
        }
    });
    
    // Special handling for dynamic rows (print sizes, shirt sizes)
    if (option === 'dtf') {
        // Listen for print size changes
        const printSizeContainer = form.querySelector('#print-sizes-container');
        if (printSizeContainer) {
            // Use mutation observer to detect when rows are added/removed
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList') {
                        // Re-add event listeners to new elements
                        setTimeout(() => {
                            setupFormCompletionListeners(option, instanceId);
                            checkCompletion();
                        }, 100);
                    }
                });
            });
            
            observer.observe(printSizeContainer, { childList: true, subtree: true });
        }
    }
    
    // Initial check
    setTimeout(checkCompletion, 100);
}

// Change iPrint type function
function changeIprintType() {
    console.log('Change iPrint type clicked');
    
    try {
        // Hide all iPrint type forms
        const typeForms = document.querySelectorAll('.iprint-type-form');
        typeForms.forEach(form => {
            form.style.display = 'none';
        });
        
        // Hide the details container
        const detailsContainer = document.getElementById('iprint-details-container');
        if (detailsContainer) {
            detailsContainer.style.display = 'none';
        }
        
        // Clear the hidden field if it exists
        const iprintTypeField = document.getElementById('iprint_type');
        if (iprintTypeField) {
            iprintTypeField.value = '';
        }
        
        console.log('iPrint type selection reset');
    } catch (error) {
        console.error('ERROR in changeIprintType:', error);
    }
}

// Add Print Size Row function
function addPrintSizeRow() {
    console.log('Add print size row clicked');
    
    try {
        const container = document.getElementById('print-sizes-container');
        if (!container) {
            console.error('Print sizes container not found');
            return;
        }
        
        // Count existing rows
        const existingRows = container.querySelectorAll('.print-size-row');
        const rowCount = existingRows.length;
        const rowNumber = rowCount + 1;
        
        // Create new row HTML with delete button
        const newRow = document.createElement('tr');
        newRow.className = 'print-size-row';
        newRow.innerHTML = `
            <td class="align-middle text-center">${rowNumber}</td>
            <td>
                <select class="form-select form-select-sm print-size-select" name="dtf_print_size[]" required>
                    <option value="" selected disabled>Select print size</option>
                    <option value="8x10">8x10 inches</option>
                    <option value="10x12">10x12 inches</option>
                    <option value="12x14">12x14 inches</option>
                    <option value="14x16">14x16 inches</option>
                    <option value="16x20">16x20 inches</option>
                    <option value="custom">Custom Size</option>
                </select>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm custom-size-input" name="dtf_custom_size[]" placeholder="e.g., 18x24 inches" style="display: none;">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm print-size-quantity" name="dtf_print_size_quantity[]" min="1" value="1" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger delete-print-size-btn" onclick="deletePrintSizeRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        // Add the new row
        container.appendChild(newRow);
        console.log(`Added print size row ${rowNumber}`);
        
        // Add event listener for custom size selection
        const selectElement = newRow.querySelector('.print-size-select');
        const customInput = newRow.querySelector('.custom-size-input');
        
        if (selectElement && customInput) {
            selectElement.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customInput.style.display = 'block';
                    customInput.required = true;
                } else {
                    customInput.style.display = 'none';
                    customInput.required = false;
                    customInput.value = '';
                }
            });
        }
        
        // Update row numbers
        updatePrintSizeRowNumbers();
        
        // Update quantity totals
        calculateTotalQuantities();
        
    } catch (error) {
        console.error('ERROR in addPrintSizeRow:', error);
    }
}

// Delete Print Size Row function
function deletePrintSizeRow(button) {
    try {
        const row = button.closest('.print-size-row');
        if (row) {
            // Don't delete if it's the only row
            const allRows = document.querySelectorAll('.print-size-row');
            if (allRows.length <= 1) {
                alert('Cannot delete the only print size row. At least one print size is required.');
                return;
            }
            
            row.remove();
            console.log('Deleted print size row');
            
            // Update row numbers
            updatePrintSizeRowNumbers();
            
            // Update quantity totals
            calculateTotalQuantities();
        }
    } catch (error) {
        console.error('ERROR in deletePrintSizeRow:', error);
    }
}

// Update Print Size Row Numbers
function updatePrintSizeRowNumbers() {
    const rows = document.querySelectorAll('.print-size-row');
    rows.forEach((row, index) => {
        const numberCell = row.querySelector('td:first-child');
        if (numberCell) {
            numberCell.textContent = index + 1;
        }
    });
}

// Add Shirt Size Row function
function addShirtSizeRow() {
    console.log('Add shirt size row clicked');
    
    try {
        const container = document.getElementById('shirt-sizes-container');
        if (!container) {
            console.error('Shirt sizes container not found');
            return;
        }
        
        // Count existing rows
        const existingRows = container.querySelectorAll('.shirt-size-row');
        const rowCount = existingRows.length;
        const rowNumber = rowCount + 1;
        
        // Create new row HTML with delete button
        const newRow = document.createElement('tr');
        newRow.className = 'shirt-size-row';
        newRow.innerHTML = `
            <td class="align-middle text-center">${rowNumber}</td>
            <td>
                <select class="form-select form-select-sm shirt-size-select" name="dtf_shirt_size[]" required>
                    <option value="" selected disabled>Select size</option>
                    <option value="xs">XS (Extra Small)</option>
                    <option value="s">S (Small)</option>
                    <option value="m">M (Medium)</option>
                    <option value="l">L (Large)</option>
                    <option value="xl">XL (Extra Large)</option>
                    <option value="2xl">2XL (Double Extra Large)</option>
                    <option value="3xl">3XL (Triple Extra Large)</option>
                    <option value="4xl">4XL (4X Extra Large)</option>
                    <option value="5xl">5XL (5X Extra Large)</option>
                    <option value="6xl">6XL (6X Extra Large)</option>
                    <option value="7xl">7XL (7X Extra Large)</option>
                    <option value="8xl">8XL (8X Extra Large)</option>
                </select>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm shirt-size-quantity" name="dtf_shirt_size_quantity[]" min="1" value="1" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger delete-shirt-size-btn" onclick="deleteShirtSizeRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        // Add the new row
        container.appendChild(newRow);
        console.log(`Added shirt size row ${rowNumber}`);
        
        // Update row numbers
        updateShirtSizeRowNumbers();
        
        // Update quantity totals
        calculateTotalQuantities();
        
    } catch (error) {
        console.error('ERROR in addShirtSizeRow:', error);
    }
}

// Delete Shirt Size Row function
function deleteShirtSizeRow(button) {
    try {
        const row = button.closest('.shirt-size-row');
        if (row) {
            // Don't delete if it's the only row
            const allRows = document.querySelectorAll('.shirt-size-row');
            if (allRows.length <= 1) {
                alert('Cannot delete the only shirt size row. At least one size is required.');
                return;
            }
            
            row.remove();
            console.log('Deleted shirt size row');
            
            // Update row numbers
            updateShirtSizeRowNumbers();
            
            // Update quantity totals
            calculateTotalQuantities();
        }
    } catch (error) {
        console.error('ERROR in deleteShirtSizeRow:', error);
    }
}

// Update Shirt Size Row Numbers
function updateShirtSizeRowNumbers() {
    const rows = document.querySelectorAll('.shirt-size-row');
    rows.forEach((row, index) => {
        const numberCell = row.querySelector('td:first-child');
        if (numberCell) {
            numberCell.textContent = index + 1;
        }
    });
}

// Event delegation for order type buttons
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up event listeners');
    
    // Add click handlers for order type buttons
    const orderButtons = document.querySelectorAll('[data-order-type]');
    console.log('Found', orderButtons.length, 'order type buttons');
    
    orderButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderType = this.getAttribute('data-order-type');
            console.log('Button clicked:', orderType);
            showOrderForm(orderType);
        });
    });
    
    // Add click handlers for iPrint option buttons
    const iprintOptionButtons = document.querySelectorAll('.iprint-option-btn');
    console.log('Found', iprintOptionButtons.length, 'iPrint option buttons');
    
    iprintOptionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const option = this.getAttribute('data-option');
            console.log('iPrint option button clicked:', option);
            showIprintOption(option);
        });
    });
    
    // Add click handler for change type button
    const changeTypeButton = document.getElementById('iprint-change-type');
    if (changeTypeButton) {
        changeTypeButton.addEventListener('click', function() {
            console.log('Change type button clicked');
            changeIprintType();
        });
    }
    
    // Add click handler for add print size button
    const addPrintSizeButton = document.getElementById('add-print-size');
    if (addPrintSizeButton) {
        addPrintSizeButton.addEventListener('click', function() {
            console.log('Add print size button clicked');
            addPrintSizeRow();
        });
    }
    
    // Add click handler for add shirt size button
    const addShirtSizeButton = document.getElementById('add-shirt-size');
    if (addShirtSizeButton) {
        addShirtSizeButton.addEventListener('click', function() {
            console.log('Add shirt size button clicked');
            addShirtSizeRow();
        });
    }
    
    // Add click handler for add embroidery print size button
    const addEmbroideryPrintSizeButton = document.getElementById('add-embroidery-print-size');
    if (addEmbroideryPrintSizeButton) {
        addEmbroideryPrintSizeButton.addEventListener('click', function() {
            console.log('Add embroidery print size button clicked');
            addEmbroideryPrintSizeRow();
        });
    }
    
    // Add click handler for add embroidery shirt size button
    const addEmbroideryShirtSizeButton = document.getElementById('add-embroidery-shirt-size');
    if (addEmbroideryShirtSizeButton) {
        addEmbroideryShirtSizeButton.addEventListener('click', function() {
            console.log('Add embroidery shirt size button clicked');
            addEmbroideryShirtSizeRow();
        });
    }
    
    // Add event listener for custom size selection in initial print size row
    const initialPrintSizeSelect = document.querySelector('.print-size-select');
    const initialCustomSizeInput = document.querySelector('.custom-size-input');
    
    if (initialPrintSizeSelect && initialCustomSizeInput) {
        initialPrintSizeSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                initialCustomSizeInput.style.display = 'block';
                initialCustomSizeInput.required = true;
            } else {
                initialCustomSizeInput.style.display = 'none';
                initialCustomSizeInput.required = false;
                initialCustomSizeInput.value = '';
            }
        });
    }
    
    // Add event listeners for existing print size selects (for custom size handling)
    const existingPrintSizeSelects = document.querySelectorAll('.print-size-select');
    existingPrintSizeSelects.forEach(select => {
        const customInput = select.closest('.print-size-row').querySelector('.custom-size-input');
        if (customInput) {
            select.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customInput.style.display = 'block';
                    customInput.required = true;
                } else {
                    customInput.style.display = 'none';
                    customInput.required = false;
                    customInput.value = '';
                }
            });
        }
    });
    
    // Image Upload Preview Functionality
    const imageUploadInput = document.getElementById('dtf-image-upload');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const imagePreviews = document.getElementById('image-previews');
    const uploadProgress = document.getElementById('upload-progress');
    const uploadProgressBar = document.getElementById('upload-progress-bar');
    
    if (imageUploadInput) {
        imageUploadInput.addEventListener('change', function(event) {
            const files = event.target.files;
            
            if (files.length > 0) {
                // Show preview container
                imagePreviewContainer.classList.remove('d-none');
                imagePreviews.innerHTML = '';
                
                // Show upload progress
                uploadProgress.classList.remove('d-none');
                uploadProgressBar.style.width = '0%';
                
                // Process each file
                Array.from(files).forEach((file, index) => {
                    // Check file size (10MB max)
                    if (file.size > 10 * 1024 * 1024) {
                        alert(`File "${file.name}" exceeds 10MB limit. Please select a smaller file.`);
                        return;
                    }
                    
                    // Check file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf', 'image/vnd.adobe.photoshop', 'application/postscript', 'application/illustrator'];
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    const validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'psd', 'ai', 'eps'];
                    
                    if (!validTypes.includes(file.type) && !validExtensions.includes(fileExtension)) {
                        alert(`File "${file.name}" has an unsupported format. Please upload images, PDF, PSD, AI, or EPS files only.`);
                        return;
                    }
                    
                    // Create preview element
                    const previewCol = document.createElement('div');
                    previewCol.className = 'col-6 col-md-4 col-lg-3';
                    
                    const previewCard = document.createElement('div');
                    previewCard.className = 'card h-100';
                    
                    // Create preview based on file type
                    if (file.type.startsWith('image/') && !file.type.includes('photoshop') && !file.type.includes('illustrator')) {
                        // Regular image file
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewCard.innerHTML = `
                                <div class="card-body p-2 text-center">
                                    <img src="${e.target.result}" class="img-fluid rounded mb-2" style="max-height: 100px; object-fit: contain;" alt="${file.name}">
                                    <p class="small text-truncate mb-1" title="${file.name}">${file.name}</p>
                                    <p class="small text-muted">${(file.size / 1024).toFixed(1)} KB</p>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImagePreview(this)">
                                        <i class="fas fa-times"></i> Remove
                                    </button>
                                </div>
                            `;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // Document file (PDF, PSD, AI, EPS)
                        let iconClass = 'fas fa-file';
                        if (file.type === 'application/pdf') iconClass = 'fas fa-file-pdf';
                        else if (file.type.includes('photoshop')) iconClass = 'fas fa-file-image';
                        else if (file.type.includes('illustrator') || fileExtension === 'ai') iconClass = 'fas fa-paint-brush';
                        else if (fileExtension === 'eps') iconClass = 'fas fa-vector-square';
                        
                        previewCard.innerHTML = `
                            <div class="card-body p-2 text-center">
                                <div class="mb-2" style="font-size: 3rem;">
                                    <i class="${iconClass} text-primary"></i>
                                </div>
                                <p class="small text-truncate mb-1" title="${file.name}">${file.name}</p>
                                <p class="small text-muted">${(file.size / 1024).toFixed(1)} KB</p>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImagePreview(this)">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                        `;
                    }
                    
                    previewCol.appendChild(previewCard);
                    imagePreviews.appendChild(previewCol);
                    
                    // Update progress bar
                    const progress = ((index + 1) / files.length) * 100;
                    uploadProgressBar.style.width = `${progress}%`;
                });
                
                // Hide progress bar after a delay
                setTimeout(() => {
                    uploadProgress.classList.add('d-none');
                }, 1000);
            } else {
                // No files selected
                imagePreviewContainer.classList.add('d-none');
            }
        });
    }
    
    // Remove Image Preview Function
    window.removeImagePreview = function(button) {
        const previewCard = button.closest('.col-6');
        if (previewCard) {
            previewCard.remove();
            
            // Update file input
            const files = imageUploadInput.files;
            const dt = new DataTransfer();
            
            // Get filename from preview
            const fileName = previewCard.querySelector('.small').textContent;
            
            // Remove file from input
            Array.from(files).forEach(file => {
                if (file.name !== fileName) {
                    dt.items.add(file);
                }
            });
            
            imageUploadInput.files = dt.files;
            
            // Hide preview container if no files left
            if (imagePreviews.children.length === 0) {
                imagePreviewContainer.classList.add('d-none');
            }
        }
    };
    
    // Camera Functionality
    const startCameraButton = document.getElementById('start-camera');
    const capturePhotoButton = document.getElementById('capture-photo');
    const stopCameraButton = document.getElementById('stop-camera');
    const usePhotoButton = document.getElementById('use-photo');
    const retakePhotoButton = document.getElementById('retake-photo');
    const cameraPreview = document.getElementById('camera-preview');
    const cameraPreviewContainer = document.getElementById('camera-preview-container');
    const capturedPhotoContainer = document.getElementById('captured-photo-container');
    const capturedPhotoCanvas = document.getElementById('captured-photo');
    
    let stream = null;
    let capturedImageBlob = null;
    
    // Start Camera
    if (startCameraButton) {
        startCameraButton.addEventListener('click', async function() {
            try {
                // Request camera access
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'environment', // Prefer rear camera
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false 
                });
                
                // Show camera preview
                cameraPreview.srcObject = stream;
                cameraPreviewContainer.classList.remove('d-none');
                
                // Update button states
                startCameraButton.classList.add('d-none');
                capturePhotoButton.classList.remove('d-none');
                stopCameraButton.classList.remove('d-none');
                
                console.log('Camera started successfully');
            } catch (error) {
                console.error('Error accessing camera:', error);
                alert('Unable to access camera. Please check permissions and try again.');
            }
        });
    }
    
    // Capture Photo
    if (capturePhotoButton) {
        capturePhotoButton.addEventListener('click', function() {
            if (!stream) return;
            
            // Create canvas context
            const context = capturedPhotoCanvas.getContext('2d');
            
            // Set canvas dimensions to match video
            capturedPhotoCanvas.width = cameraPreview.videoWidth;
            capturedPhotoCanvas.height = cameraPreview.videoHeight;
            
            // Draw video frame to canvas
            context.drawImage(cameraPreview, 0, 0, capturedPhotoCanvas.width, capturedPhotoCanvas.height);
            
            // Show captured photo
            capturedPhotoContainer.classList.remove('d-none');
            cameraPreviewContainer.classList.add('d-none');
            
            // Convert canvas to blob
            capturedPhotoCanvas.toBlob(function(blob) {
                capturedImageBlob = blob;
            }, 'image/jpeg', 0.8);
            
            console.log('Photo captured');
        });
    }
    
    // Stop Camera
    if (stopCameraButton) {
        stopCameraButton.addEventListener('click', function() {
            if (stream) {
                // Stop all tracks
                stream.getTracks().forEach(track => track.stop());
                stream = null;
                
                // Reset UI
                cameraPreviewContainer.classList.add('d-none');
                startCameraButton.classList.remove('d-none');
                capturePhotoButton.classList.add('d-none');
                stopCameraButton.classList.add('d-none');
                capturedPhotoContainer.classList.add('d-none');
                
                console.log('Camera stopped');
            }
        });
    }
    
    // Use Photo
    if (usePhotoButton) {
        usePhotoButton.addEventListener('click', function() {
            if (!capturedImageBlob) return;
            
            // Create a File object from the blob
            const fileName = `camera_photo_${Date.now()}.jpg`;
            const file = new File([capturedImageBlob], fileName, { type: 'image/jpeg' });
            
            // Add to file input
            const dataTransfer = new DataTransfer();
            
            // Keep existing files
            if (imageUploadInput.files) {
                Array.from(imageUploadInput.files).forEach(existingFile => {
                    dataTransfer.items.add(existingFile);
                });
            }
            
            // Add new photo
            dataTransfer.items.add(file);
            imageUploadInput.files = dataTransfer.files;
            
            // Trigger change event to show preview
            imageUploadInput.dispatchEvent(new Event('change'));
            
            // Switch back to file tab
            document.getElementById('file-tab').click();
            
            // Stop camera
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            
            // Reset camera UI
            cameraPreviewContainer.classList.add('d-none');
            capturedPhotoContainer.classList.add('d-none');
            startCameraButton.classList.remove('d-none');
            capturePhotoButton.classList.add('d-none');
            stopCameraButton.classList.add('d-none');
            
            console.log('Photo added to upload list');
        });
    }
    
    // Retake Photo
    if (retakePhotoButton) {
        retakePhotoButton.addEventListener('click', function() {
            // Show camera preview again
            capturedPhotoContainer.classList.add('d-none');
            cameraPreviewContainer.classList.remove('d-none');
        });
    }
    
    // Clean up camera when leaving page
    window.addEventListener('beforeunload', function() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });
    
    // Clipboard Paste Functionality
    const pasteArea = document.getElementById('paste-area');
    const pastedImageContainer = document.getElementById('pasted-image-container');
    const pastedImagePreview = document.getElementById('pasted-image-preview');
    const usePastedImageButton = document.getElementById('use-pasted-image');
    const clearPastedImageButton = document.getElementById('clear-pasted-image');
    
    let pastedImageBlob = null;
    
    // Handle paste events on the entire document
    document.addEventListener('paste', function(event) {
        // Check if we're in the paste tab
        const pasteTab = document.getElementById('paste-tab');
        if (!pasteTab || !pasteTab.classList.contains('active')) {
            return; // Not in paste tab, ignore
        }
        
        const items = (event.clipboardData || window.clipboardData).items;
        
        // Look for image in clipboard
        for (let i = 0; i < items.length; i++) {
            if (items[i].type.indexOf('image') !== -1) {
                const blob = items[i].getAsFile();
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Show preview
                    pastedImagePreview.src = e.target.result;
                    pastedImageContainer.classList.remove('d-none');
                    pasteArea.classList.add('d-none');
                    
                    // Store blob for later use
                    pastedImageBlob = blob;
                    
                    console.log('Image pasted from clipboard');
                };
                
                reader.readAsDataURL(blob);
                event.preventDefault(); // Prevent default paste behavior
                break;
            }
        }
    });
    
    // Use Pasted Image
    if (usePastedImageButton) {
        usePastedImageButton.addEventListener('click', function() {
            if (!pastedImageBlob) return;
            
            // Create a File object from the blob
            const fileName = `screenshot_${Date.now()}.png`;
            const file = new File([pastedImageBlob], fileName, { type: 'image/png' });
            
            // Add to file input
            const dataTransfer = new DataTransfer();
            
            // Keep existing files
            if (imageUploadInput.files) {
                Array.from(imageUploadInput.files).forEach(existingFile => {
                    dataTransfer.items.add(existingFile);
                });
            }
            
            // Add new screenshot
            dataTransfer.items.add(file);
            imageUploadInput.files = dataTransfer.files;
            
            // Trigger change event to show preview
            imageUploadInput.dispatchEvent(new Event('change'));
            
            // Switch back to file tab
            document.getElementById('file-tab').click();
            
            // Reset paste UI
            resetPasteUI();
            
            console.log('Screenshot added to upload list');
        });
    }
    
    // Clear Pasted Image
    if (clearPastedImageButton) {
        clearPastedImageButton.addEventListener('click', function() {
            resetPasteUI();
        });
    }
    
    // Reset paste UI function
    function resetPasteUI() {
        pastedImageContainer.classList.add('d-none');
        pasteArea.classList.remove('d-none');
        pastedImageBlob = null;
        pastedImagePreview.src = '';
    }
    
    // Also handle paste when clicking on paste area
    if (pasteArea) {
        pasteArea.addEventListener('click', function() {
            // Focus on paste area
            this.focus();
            
            // Show paste hint
            const originalHTML = this.innerHTML;
            this.innerHTML = '<div class="text-primary"><i class="fas fa-hand-pointer me-1"></i> Press Ctrl+V to paste screenshot...</div>';
            
            setTimeout(() => {
                this.innerHTML = originalHTML;
            }, 2000);
        });
        
        // Make paste area focusable
        pasteArea.setAttribute('tabindex', '0');
    }
    
    // Quantity Calculation Functions
    function calculateTotalQuantities() {
        let printTotal = 0;
        let shirtTotal = 0;
        
        // Track quantities per print size
        const printSizeQuantities = {
            '8x10': 0,
            '10x12': 0,
            '12x14': 0,
            '14x16': 0,
            '16x20': 0,
            'custom': 0
        };
        
        // Track quantities per shirt size
        const shirtSizeQuantities = {
            'xs': 0,
            's': 0,
            'm': 0,
            'l': 0,
            'xl': 0,
            '2xl': 0,
            '3xl': 0,
            '4xl': 0,
            '5xl': 0,
            '6xl': 0,
            '7xl': 0,
            '8xl': 0
        };
        
        // Calculate print sizes totals and per-size quantities
        const printRows = document.querySelectorAll('.print-size-row');
        printRows.forEach(row => {
            const sizeSelect = row.querySelector('.print-size-select');
            const quantityInput = row.querySelector('.print-size-quantity');
            
            if (sizeSelect && quantityInput) {
                const size = sizeSelect.value;
                const quantity = parseInt(quantityInput.value) || 0;
                
                printTotal += quantity;
                
                if (size && printSizeQuantities.hasOwnProperty(size)) {
                    printSizeQuantities[size] += quantity;
                } else if (size === 'custom') {
                    printSizeQuantities.custom += quantity;
                }
            }
        });
        
        // Calculate shirt sizes totals and per-size quantities
        const shirtRows = document.querySelectorAll('.shirt-size-row');
        shirtRows.forEach(row => {
            const sizeSelect = row.querySelector('.shirt-size-select');
            const quantityInput = row.querySelector('.shirt-size-quantity');
            
            if (sizeSelect && quantityInput) {
                const size = sizeSelect.value;
                const quantity = parseInt(quantityInput.value) || 0;
                
                shirtTotal += quantity;
                
                if (size && shirtSizeQuantities.hasOwnProperty(size)) {
                    shirtSizeQuantities[size] += quantity;
                }
            }
        });
        
        // Update per-size breakdown for prints
        const printBreakdown = document.getElementById('print-size-breakdown');
        if (printBreakdown) {
            let html = '';
            Object.entries(printSizeQuantities).forEach(([size, qty]) => {
                if (qty > 0) {
                    const displaySize = size === 'custom' ? 'Custom' : `${size} inches`;
                    html += `
                        <div class="d-flex align-items-center mb-1">
                            <span class="me-2" style="min-width: 60px;">${displaySize}:</span>
                            <span class="badge bg-secondary">${qty}</span>
                        </div>`;
                }
            });
            
            // If no prints yet, show placeholder
            if (html === '') {
                html = '<div class="text-muted small">No print sizes added yet</div>';
            }
            
            printBreakdown.innerHTML = html;
        }
        
        // Update per-size breakdown for shirts
        const shirtBreakdown = document.getElementById('shirt-size-breakdown');
        if (shirtBreakdown) {
            let html = '';
            Object.entries(shirtSizeQuantities).forEach(([size, qty]) => {
                if (qty > 0) {
                    const displaySize = size.toUpperCase();
                    html += `
                        <div class="d-flex align-items-center mb-1">
                            <span class="me-2" style="min-width: 30px;">${displaySize}:</span>
                            <span class="badge bg-secondary">${qty}</span>
                        </div>`;
                }
            });
            
            // If no shirts yet, show placeholder
            if (html === '') {
                html = '<div class="text-muted small">No shirt sizes added yet</div>';
            }
            
            shirtBreakdown.innerHTML = html;
        }
        
        // Update total displays
        document.getElementById('print-total-quantity').textContent = printTotal;
        document.getElementById('shirt-total-quantity').textContent = shirtTotal;
        
        console.log('Quantities updated:', { 
            printTotal, 
            shirtTotal,
            printBreakdown: printSizeQuantities,
            shirtBreakdown: shirtSizeQuantities
        });
    }
    
    // Update quantity calculation when quantities or sizes change
    function setupQuantityListeners() {
        // Listen to all print size quantity inputs and size selects
        document.addEventListener('input', function(event) {
            if (event.target.classList.contains('print-size-quantity') || 
                event.target.classList.contains('shirt-size-quantity')) {
                calculateTotalQuantities();
            }
        });
        
        // Listen to size selection changes
        document.addEventListener('change', function(event) {
            if (event.target.classList.contains('print-size-select') || 
                event.target.classList.contains('shirt-size-select')) {
                calculateTotalQuantities();
            }
        });
        
        // Also recalculate when rows are added or deleted
        const observer = new MutationObserver(function(mutations) {
            let shouldRecalculate = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    // Check if print or shirt size rows were added/removed
                    const addedNodes = Array.from(mutation.addedNodes);
                    const removedNodes = Array.from(mutation.removedNodes);
                    
                    const allNodes = [...addedNodes, ...removedNodes];
                    allNodes.forEach(node => {
                        if (node.classList && 
                            (node.classList.contains('print-size-row') || 
                             node.classList.contains('shirt-size-row') ||
                             node.querySelector('.print-size-row') || 
                             node.querySelector('.shirt-size-row'))) {
                            shouldRecalculate = true;
                        }
                    });
                }
            });
            
            if (shouldRecalculate) {
                setTimeout(calculateTotalQuantities, 100); // Small delay to ensure DOM is updated
            }
        });
        
        // Observe the containers for changes
        const printContainer = document.getElementById('print-sizes-container');
        const shirtContainer = document.getElementById('shirt-sizes-container');
        
        if (printContainer) {
            observer.observe(printContainer, { childList: true, subtree: true });
        }
        
        if (shirtContainer) {
            observer.observe(shirtContainer, { childList: true, subtree: true });
        }
        
        // Initial calculation
        calculateTotalQuantities();
    }
    
    // Initialize quantity listeners
    setupQuantityListeners();
    
    // Add Another Printing Type from Table Button
    const addAnotherPrintingTypeFromTableBtn = document.getElementById('add-another-printing-type-from-table');
    if (addAnotherPrintingTypeFromTableBtn) {
        addAnotherPrintingTypeFromTableBtn.addEventListener('click', function() {
            console.log('Add another printing type from table clicked');
            
            // Show a selection dialog for adding another printing type
            const printingTypes = [
                { id: 'dtf', name: 'DTF Printing', icon: 'fas fa-print' },
                { id: 'lanyard', name: 'Lanyard Printing', icon: 'fas fa-id-card' },
                { id: 'tarpaulin', name: 'Tarpaulin Printing', icon: 'fas fa-image' },
                { id: 'sublimation', name: 'Sublimation Printing', icon: 'fas fa-fire' },
                { id: 'embroidery', name: 'Embroidery', icon: 'fas fa-threads' },
                { id: 'other', name: 'Other Items', icon: 'fas fa-boxes' }
            ];
            
            // Create a simple selection modal
            let selectionHtml = '<div class="mb-3"><strong>Select Printing Type to Add:</strong></div>';
            selectionHtml += '<div class="row g-2">';
            
            printingTypes.forEach(type => {
                selectionHtml += `
                    <div class="col-md-6 col-sm-6">
                        <button type="button" 
                                class="btn btn-outline-secondary w-100 py-2 mb-2 add-printing-type-selection-btn" 
                                data-option="${type.id}"
                                style="text-align: left;">
                            <i class="${type.icon} me-2"></i>
                            ${type.name}
                        </button>
                    </div>
                `;
            });
            
            selectionHtml += '</div>';
            
            // Create modal
            const modalHtml = `
                <div class="modal fade" id="printingTypeSelectionModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Another Printing Type</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${selectionHtml}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('printingTypeSelectionModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('printingTypeSelectionModal'));
            modal.show();
            
            // Add event listeners to selection buttons
            setTimeout(() => {
                const selectionButtons = document.querySelectorAll('.add-printing-type-selection-btn');
                selectionButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const option = this.getAttribute('data-option');
                        
                        // Add the selected printing type to table
                        const instanceId = addPrintingTypeToTable(option);
                        
                        // Setup form completion listeners for this instance
                        if (instanceId) {
                            setupFormCompletionListeners(option, instanceId);
                        }
                        
                        // Close modal
                        modal.hide();
                        
                        // Show success message
                        const typeNames = {
                            'dtf': 'DTF Printing',
                            'lanyard': 'Lanyard Printing',
                            'tarpaulin': 'Tarpaulin Printing',
                            'sublimation': 'Sublimation Printing',
                            'embroidery': 'Embroidery',
                            'other': 'Other Items'
                        };
                        
                        const displayName = typeNames[option] || option;
                        console.log(`Added another "${displayName}" to the order`);
                    });
                });
            }, 100);
        });
    }
    
    // Initialize printing type buttons to add to table on click
    const printingTypeButtons = document.querySelectorAll('.iprint-option-btn');
    printingTypeButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            printingTypeButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to clicked button
            this.classList.add('active');
        });
    });
    
    // Lanyard Image Upload Preview Functionality
    const lanyardImageUploadInput = document.getElementById('lanyard-image-upload');
    const lanyardImagePreviewContainer = document.getElementById('lanyard-uploaded-files-container');
    const lanyardImagePreviews = document.getElementById('lanyard-uploaded-files-list');
    
    if (lanyardImageUploadInput) {
        lanyardImageUploadInput.addEventListener('change', function(event) {
            const files = event.target.files;
            
            if (files.length > 0) {
                // Show actions container
                const actionsContainer = document.getElementById('lanyard-uploaded-files-actions');
                if (actionsContainer) {
                    actionsContainer.classList.remove('d-none');
                }
                
                lanyardImagePreviews.innerHTML = '';
                
                // Process each file
                Array.from(files).forEach((file, index) => {
                    // Check file size (10MB max)
                    if (file.size > 10 * 1024 * 1024) {
                        alert(`File "${file.name}" exceeds 10MB limit. Please select a smaller file.`);
                        return;
                    }
                    
                    // Check file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf', 'image/vnd.adobe.photoshop', 'application/postscript', 'application/illustrator'];
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    const validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'psd', 'ai', 'eps'];
                    
                    if (!validTypes.includes(file.type) && !validExtensions.includes(fileExtension)) {
                        alert(`File "${file.name}" has an unsupported format. Please upload images, PDF, PSD, AI, or EPS files only.`);
                        return;
                    }
                    
                    // Create preview element
                    const previewItem = document.createElement('div');
                    previewItem.className = 'd-flex align-items-center justify-content-between mb-2 p-2 border rounded';
                    
                    // Create preview based on file type
                    if (file.type.startsWith('image/') && !file.type.includes('photoshop') && !file.type.includes('illustrator')) {
                        // Regular image file
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewItem.innerHTML = `
                                <div class="d-flex align-items-center">
                                    <img src="${e.target.result}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="${file.name}">
                                    <div>
                                        <div class="small text-truncate" style="max-width: 200px;" title="${file.name}">${file.name}</div>
                                        <div class="small text-muted">${(file.size / 1024).toFixed(1)} KB</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLanyardImagePreview(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // Document file (PDF, PSD, AI, EPS)
                        let iconClass = 'fas fa-file';
                        if (file.type === 'application/pdf') iconClass = 'fas fa-file-pdf';
                        else if (file.type.includes('photoshop')) iconClass = 'fas fa-file-image';
                        else if (file.type.includes('illustrator') || fileExtension === 'ai') iconClass = 'fas fa-paint-brush';
                        else if (fileExtension === 'eps') iconClass = 'fas fa-vector-square';
                        
                        previewItem.innerHTML = `
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="font-size: 1.5rem;">
                                    <i class="${iconClass} text-primary"></i>
                                </div>
                                <div>
                                    <div class="small text-truncate" style="max-width: 200px;" title="${file.name}">${file.name}</div>
                                    <div class="small text-muted">${(file.size / 1024).toFixed(1)} KB</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLanyardImagePreview(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                    }
                    
                    lanyardImagePreviews.appendChild(previewItem);
                });
            } else {
                // No files selected
                const actionsContainer = document.getElementById('lanyard-uploaded-files-actions');
                if (actionsContainer) {
                    actionsContainer.classList.add('d-none');
                }
                lanyardImagePreviews.innerHTML = '<div class="text-muted small">No files attached yet</div>';
            }
        });
    }
    
    // Remove Lanyard Image Preview Function
    window.removeLanyardImagePreview = function(button) {
        const previewItem = button.closest('.d-flex');
        if (previewItem) {
            previewItem.remove();
            
            // Update file input
            const files = lanyardImageUploadInput.files;
            const dt = new DataTransfer();
            
            // Get filename from preview
            const fileName = previewItem.querySelector('.small').textContent;
            
            // Remove file from input
            Array.from(files).forEach(file => {
                if (file.name !== fileName) {
                    dt.items.add(file);
                }
            });
            
            lanyardImageUploadInput.files = dt.files;
            
            // Hide actions container if no files left
            if (lanyardImagePreviews.children.length === 0) {
                const actionsContainer = document.getElementById('lanyard-uploaded-files-actions');
                if (actionsContainer) {
                    actionsContainer.classList.add('d-none');
                }
                lanyardImagePreviews.innerHTML = '<div class="text-muted small">No files attached yet</div>';
            }
        }
    };
    
    // Lanyard Clear All Files Button
    const lanyardClearAllFilesButton = document.getElementById('lanyard-clear-all-files');
    if (lanyardClearAllFilesButton) {
        lanyardClearAllFilesButton.addEventListener('click', function() {
            // Clear file input
            lanyardImageUploadInput.value = '';
            
            // Clear previews
            lanyardImagePreviews.innerHTML = '<div class="text-muted small">No files attached yet</div>';
            
            // Hide actions container
            const actionsContainer = document.getElementById('lanyard-uploaded-files-actions');
            if (actionsContainer) {
                actionsContainer.classList.add('d-none');
            }
        });
    }
    
    // Lanyard Paste Screenshot Functionality
    const lanyardPasteArea = document.getElementById('lanyard-paste-area');
    const lanyardPastedImageContainer = document.getElementById('lanyard-pasted-image-container');
    const lanyardPastedImagePreview = document.getElementById('lanyard-pasted-image-preview');
    const lanyardUsePastedImageButton = document.getElementById('lanyard-use-pasted-image');
    const lanyardClearPastedImageButton = document.getElementById('lanyard-clear-pasted-image');
    
    if (lanyardPasteArea) {
        lanyardPasteArea.addEventListener('paste', function(e) {
            e.preventDefault();
            
            // Check if clipboard contains image
            const items = e.clipboardData.items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const blob = items[i].getAsFile();
                    const reader = new FileReader();
                    
                    reader.onload = function(event) {
                        lanyardPastedImagePreview.src = event.target.result;
                        lanyardPastedImageContainer.classList.remove('d-none');
                        
                        // Store the blob for later use
                        lanyardPastedImagePreview.dataset.blob = event.target.result;
                    };
                    
                    reader.readAsDataURL(blob);
                    break;
                }
            }
        });
    }
    
    if (lanyardUsePastedImageButton) {
        lanyardUsePastedImageButton.addEventListener('click', function() {
            // Create a file from the pasted image
            const dataURL = lanyardPastedImagePreview.dataset.blob;
            if (dataURL) {
                // Convert data URL to blob
                const blob = dataURLToBlob(dataURL);
                const file = new File([blob], 'pasted-screenshot.png', { type: 'image/png' });
                
                // Add to file input
                const dt = new DataTransfer();
                const currentFiles = lanyardImageUploadInput.files;
                
                // Add existing files
                Array.from(currentFiles).forEach(f => dt.items.add(f));
                
                // Add new file
                dt.items.add(file);
                
                // Update file input
                lanyardImageUploadInput.files = dt.files;
                
                // Trigger change event to update preview
                lanyardImageUploadInput.dispatchEvent(new Event('change'));
                
                // Clear pasted image
                lanyardPastedImageContainer.classList.add('d-none');
                lanyardPastedImagePreview.src = '';
                delete lanyardPastedImagePreview.dataset.blob;
            }
        });
    }
    
    if (lanyardClearPastedImageButton) {
        lanyardClearPastedImageButton.addEventListener('click', function() {
            lanyardPastedImageContainer.classList.add('d-none');
            lanyardPastedImagePreview.src = '';
            delete lanyardPastedImagePreview.dataset.blob;
        });
    }
    
    // Lanyard Camera Functionality
    const lanyardStartCameraButton = document.getElementById('lanyard-start-camera');
    const lanyardCapturePhotoButton = document.getElementById('lanyard-capture-photo');
    const lanyardStopCameraButton = document.getElementById('lanyard-stop-camera');
    const lanyardUseCapturedPhotoButton = document.getElementById('lanyard-use-captured-photo');
    const lanyardRetakePhotoButton = document.getElementById('lanyard-retake-photo');
    const lanyardCameraPreview = document.getElementById('lanyard-camera-preview');
    const lanyardCameraPreviewContainer = document.getElementById('lanyard-camera-preview-container');
    const lanyardCapturedPhotoContainer = document.getElementById('lanyard-captured-photo-container');
    const lanyardCapturedPhotoPreview = document.getElementById('lanyard-captured-photo-preview');
    
    let lanyardStream = null;
    let lanyardCapturedImageBlob = null;
    
    // Helper function to convert data URL to blob
    function dataURLToBlob(dataURL) {
        const parts = dataURL.split(';base64,');
        const contentType = parts[0].split(':')[1];
        const raw = window.atob(parts[1]);
        const rawLength = raw.length;
        const uInt8Array = new Uint8Array(rawLength);
        
        for (let i = 0; i < rawLength; ++i) {
            uInt8Array[i] = raw.charCodeAt(i);
        }
        
        return new Blob([uInt8Array], { type: contentType });
    }
    
    // Start Camera for Lanyard
    if (lanyardStartCameraButton) {
        lanyardStartCameraButton.addEventListener('click', async function() {
            try {
                // Request camera access
                lanyardStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'environment', // Prefer rear camera
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false 
                });
                
                // Show camera preview
                lanyardCameraPreview.srcObject = lanyardStream;
                lanyardCameraPreviewContainer.classList.remove('d-none');
                
                // Update button states
                lanyardStartCameraButton.classList.add('d-none');
                lanyardCapturePhotoButton.classList.remove('d-none');
                lanyardStopCameraButton.classList.remove('d-none');
                
                console.log('Lanyard camera started successfully');
            } catch (error) {
                console.error('Error accessing camera:', error);
                alert('Unable to access camera. Please check permissions and try again.');
            }
        });
    }
    
    // Capture Photo for Lanyard
    if (lanyardCapturePhotoButton) {
        lanyardCapturePhotoButton.addEventListener('click', function() {
            if (!lanyardStream) return;
            
            // Create canvas for capturing
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            
            // Set canvas dimensions to match video
            canvas.width = lanyardCameraPreview.videoWidth;
            canvas.height = lanyardCameraPreview.videoHeight;
            
            // Draw video frame to canvas
            context.drawImage(lanyardCameraPreview, 0, 0, canvas.width, canvas.height);
            
            // Convert canvas to data URL
            const dataURL = canvas.toDataURL('image/jpeg', 0.8);
            
            // Show captured photo preview
            lanyardCapturedPhotoPreview.src = dataURL;
            lanyardCapturedPhotoContainer.classList.remove('d-none');
            lanyardCameraPreviewContainer.classList.add('d-none');
            
            // Store the data URL for later use
            lanyardCapturedPhotoPreview.dataset.dataURL = dataURL;
            
            console.log('Lanyard photo captured');
        });
    }
    
    // Stop Camera for Lanyard
    if (lanyardStopCameraButton) {
        lanyardStopCameraButton.addEventListener('click', function() {
            if (lanyardStream) {
                // Stop all tracks
                lanyardStream.getTracks().forEach(track => track.stop());
                lanyardStream = null;
                
                // Hide camera preview
                lanyardCameraPreviewContainer.classList.add('d-none');
                
                // Reset button states
                lanyardStartCameraButton.classList.remove('d-none');
                lanyardCapturePhotoButton.classList.add('d-none');
                lanyardStopCameraButton.classList.add('d-none');
                
                console.log('Lanyard camera stopped');
            }
        });
    }
    
    // Use Captured Photo for Lanyard
    if (lanyardUseCapturedPhotoButton) {
        lanyardUseCapturedPhotoButton.addEventListener('click', function() {
            const dataURL = lanyardCapturedPhotoPreview.dataset.dataURL;
            if (dataURL) {
                // Convert data URL to blob
                const blob = dataURLToBlob(dataURL);
                const file = new File([blob], 'captured-photo.jpg', { type: 'image/jpeg' });
                
                // Add to file input
                const dt = new DataTransfer();
                const currentFiles = lanyardImageUploadInput.files;
                
                // Add existing files
                Array.from(currentFiles).forEach(f => dt.items.add(f));
                
                // Add new file
                dt.items.add(file);
                
                // Update file input
                lanyardImageUploadInput.files = dt.files;
                
                // Trigger change event to update preview
                lanyardImageUploadInput.dispatchEvent(new Event('change'));
                
                // Clear captured photo
                lanyardCapturedPhotoContainer.classList.add('d-none');
                lanyardCapturedPhotoPreview.src = '';
                delete lanyardCapturedPhotoPreview.dataset.dataURL;
                
                // Stop camera if still running
                if (lanyardStream) {
                    lanyardStream.getTracks().forEach(track => track.stop());
                    lanyardStream = null;
                }
                
                // Reset button states
                lanyardStartCameraButton.classList.remove('d-none');
                lanyardCapturePhotoButton.classList.add('d-none');
                lanyardStopCameraButton.classList.add('d-none');
                lanyardCameraPreviewContainer.classList.add('d-none');
            }
        });
    }
    
    // Retake Photo for Lanyard
    if (lanyardRetakePhotoButton) {
        lanyardRetakePhotoButton.addEventListener('click', function() {
            // Clear captured photo
            lanyardCapturedPhotoContainer.classList.add('d-none');
            lanyardCapturedPhotoPreview.src = '';
            delete lanyardCapturedPhotoPreview.dataset.dataURL;
            
            // Show camera preview again
            if (lanyardStream) {
                lanyardCameraPreviewContainer.classList.remove('d-none');
            }
        });
    }
    
    // Tarpaulin Image Upload Preview Functionality
    const tarpaulinImageUploadInput = document.getElementById('tarpaulin-image-upload');
    const tarpaulinImagePreviews = document.getElementById('tarpaulin-uploaded-files-list');
    
    if (tarpaulinImageUploadInput) {
        tarpaulinImageUploadInput.addEventListener('change', function(event) {
            const files = event.target.files;
            
            if (files.length > 0) {
                // Show actions container
                const actionsContainer = document.getElementById('tarpaulin-uploaded-files-actions');
                if (actionsContainer) {
                    actionsContainer.classList.remove('d-none');
                }
                
                tarpaulinImagePreviews.innerHTML = '';
                
                // Process each file
                Array.from(files).forEach((file, index) => {
                    // Check file size (10MB max)
                    if (file.size > 10 * 1024 * 1024) {
                        alert(`File "${file.name}" exceeds 10MB limit. Please select a smaller file.`);
                        return;
                    }
                    
                    // Check file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf', 'image/vnd.adobe.photoshop', 'application/postscript', 'application/illustrator'];
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    const validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'psd', 'ai', 'eps'];
                    
                    if (!validTypes.includes(file.type) && !validExtensions.includes(fileExtension)) {
                        alert(`File "${file.name}" has an unsupported format. Please upload images, PDF, PSD, AI, or EPS files only.`);
                        return;
                    }
                    
                    // Create preview element
                    const previewItem = document.createElement('div');
                    previewItem.className = 'd-flex align-items-center justify-content-between mb-2 p-2 border rounded';
                    
                    // Create preview based on file type
                    if (file.type.startsWith('image/') && !file.type.includes('photoshop') && !file.type.includes('illustrator')) {
                        // Regular image file
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewItem.innerHTML = `
                                <div class="d-flex align-items-center">
                                    <img src="${e.target.result}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="${file.name}">
                                    <div>
                                        <div class="small text-truncate" style="max-width: 200px;" title="${file.name}">${file.name}</div>
                                        <div class="small text-muted">${(file.size / 1024).toFixed(1)} KB</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTarpaulinImagePreview(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // Document file (PDF, PSD, AI, EPS)
                        let iconClass = 'fas fa-file';
                        if (file.type === 'application/pdf') iconClass = 'fas fa-file-pdf';
                        else if (file.type.includes('photoshop')) iconClass = 'fas fa-file-image';
                        else if (file.type.includes('illustrator') || fileExtension === 'ai') iconClass = 'fas fa-paint-brush';
                        else if (fileExtension === 'eps') iconClass = 'fas fa-vector-square';
                        
                        previewItem.innerHTML = `
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="font-size: 1.5rem;">
                                    <i class="${iconClass} text-primary"></i>
                                </div>
                                <div>
                                    <div class="small text-truncate" style="max-width: 200px;" title="${file.name}">${file.name}</div>
                                    <div class="small text-muted">${(file.size / 1024).toFixed(1)} KB</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTarpaulinImagePreview(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                    }
                    
                    tarpaulinImagePreviews.appendChild(previewItem);
                });
            } else {
                // No files selected
                const actionsContainer = document.getElementById('tarpaulin-uploaded-files-actions');
                if (actionsContainer) {
                    actionsContainer.classList.add('d-none');
                }
                tarpaulinImagePreviews.innerHTML = '<div class="text-muted small">No files attached yet</div>';
            }
        });
    }
    
    // Remove Tarpaulin Image Preview Function
    window.removeTarpaulinImagePreview = function(button) {
        const previewItem = button.closest('.d-flex');
        if (previewItem) {
            previewItem.remove();
        }
        
        // Check if any previews remain
        const previews = document.querySelectorAll('#tarpaulin-uploaded-files-list .d-flex');
        if (previews.length === 0) {
            const actionsContainer = document.getElementById('tarpaulin-uploaded-files-actions');
            if (actionsContainer) {
                actionsContainer.classList.add('d-none');
            }
            tarpaulinImagePreviews.innerHTML = '<div class="text-muted small">No files attached yet</div>';
        }
    };
    
    // Tarpaulin Clear All Files Function
    const tarpaulinClearAllBtn = document.getElementById('tarpaulin-clear-all-files');
    if (tarpaulinClearAllBtn) {
        tarpaulinClearAllBtn.addEventListener('click', function() {
            const tarpaulinImagePreviews = document.getElementById('tarpaulin-uploaded-files-list');
            if (tarpaulinImagePreviews) {
                tarpaulinImagePreviews.innerHTML = '<div class="text-muted small">No files attached yet</div>';
            }
            
            const actionsContainer = document.getElementById('tarpaulin-uploaded-files-actions');
            if (actionsContainer) {
                actionsContainer.classList.add('d-none');
            }
            
            // Clear file input
            const tarpaulinImageUploadInput = document.getElementById('tarpaulin-image-upload');
            if (tarpaulinImageUploadInput) {
                tarpaulinImageUploadInput.value = '';
            }
        });
    }
    
    // Tarpaulin Camera Functionality
    const tarpaulinStartCameraBtn = document.getElementById('tarpaulin-start-camera-btn');
    const tarpaulinCameraPlaceholder = document.getElementById('tarpaulin-camera-placeholder');
    const tarpaulinCameraPreview = document.getElementById('tarpaulin-camera-preview');
    const tarpaulinCameraVideo = document.getElementById('tarpaulin-camera-video');
    const tarpaulinCaptureBtn = document.getElementById('tarpaulin-capture-btn');
    const tarpaulinCameraRetryBtn = document.getElementById('tarpaulin-camera-retry-btn');
    
    let tarpaulinCameraStream = null;
    
    if (tarpaulinStartCameraBtn) {
        tarpaulinStartCameraBtn.addEventListener('click', async function() {
            try {
                tarpaulinCameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' },
                    audio: false
                });
                
                tarpaulinCameraVideo.srcObject = tarpaulinCameraStream;
                tarpaulinCameraPlaceholder.style.display = 'none';
                tarpaulinCameraPreview.style.display = 'block';
            } catch (error) {
                console.error('Error accessing camera:', error);
                alert('Unable to access camera. Please check permissions and try again.');
            }
        });
    }
    
    if (tarpaulinCaptureBtn) {
        tarpaulinCaptureBtn.addEventListener('click', function() {
            if (!tarpaulinCameraStream) return;
            
            const canvas = document.createElement('canvas');
            canvas.width = tarpaulinCameraVideo.videoWidth;
            canvas.height = tarpaulinCameraVideo.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(tarpaulinCameraVideo, 0, 0, canvas.width, canvas.height);
            
            const imageDataUrl = canvas.toDataURL('image/jpeg');
            
            // Add to previews
            const tarpaulinImagePreviews = document.getElementById('tarpaulin-uploaded-files-list');
            if (tarpaulinImagePreviews) {
                // Show actions container
                const actionsContainer = document.getElementById('tarpaulin-uploaded-files-actions');
                if (actionsContainer) {
                    actionsContainer.classList.remove('d-none');
                }
                
                // Clear "no files" message if present
                if (tarpaulinImagePreviews.innerHTML.includes('No files attached yet')) {
                    tarpaulinImagePreviews.innerHTML = '';
                }
                
                const previewItem = document.createElement('div');
                previewItem.className = 'd-flex align-items-center justify-content-between mb-2 p-2 border rounded';
                previewItem.innerHTML = `
                    <div class="d-flex align-items-center">
                        <img src="${imageDataUrl}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="Camera Capture">
                        <div>
                            <div class="small">Camera Capture</div>
                            <div class="small text-muted">${new Date().toLocaleString()}</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTarpaulinImagePreview(this)">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                
                tarpaulinImagePreviews.appendChild(previewItem);
                
                // Show retry button
                tarpaulinCaptureBtn.style.display = 'none';
                tarpaulinCameraRetryBtn.style.display = 'inline-block';
            }
        });
    }
    
    if (tarpaulinCameraRetryBtn) {
        tarpaulinCameraRetryBtn.addEventListener('click', function() {
            tarpaulinCaptureBtn.style.display = 'inline-block';
            tarpaulinCameraRetryBtn.style.display = 'none';
        });
    }
    
    // Stop camera when leaving tab
    const tarpaulinCameraTab = document.getElementById('tarpaulin-camera-tab');
    if (tarpaulinCameraTab) {
        tarpaulinCameraTab.addEventListener('click', function() {
            // Camera already started, do nothing
        });
        
        // Stop camera when switching away from camera tab
        const otherTabs = document.querySelectorAll('#tarpaulin-uploadTabs .nav-link:not(#tarpaulin-camera-tab)');
        otherTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                if (tarpaulinCameraStream) {
                    tarpaulinCameraStream.getTracks().forEach(track => track.stop());
                    tarpaulinCameraStream = null;
                    tarpaulinCameraPlaceholder.style.display = 'block';
                    tarpaulinCameraPreview.style.display = 'none';
                    tarpaulinCaptureBtn.style.display = 'inline-block';
                    tarpaulinCameraRetryBtn.style.display = 'none';
                }
            });
        });
    }
    
    // Tarpaulin Paste Functionality
    const tarpaulinPasteArea = document.getElementById('tarpaulin-paste-area');
    if (tarpaulinPasteArea) {
        tarpaulinPasteArea.addEventListener('paste', function(event) {
            const items = (event.clipboardData || event.originalEvent.clipboardData).items;
            
            for (let item of items) {
                if (item.type.indexOf('image') !== -1) {
                    const blob = item.getAsFile();
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const tarpaulinImagePreviews = document.getElementById('tarpaulin-uploaded-files-list');
                        if (tarpaulinImagePreviews) {
                            // Show actions container
                            const actionsContainer = document.getElementById('tarpaulin-uploaded-files-actions');
                            if (actionsContainer) {
                                actionsContainer.classList.remove('d-none');
                            }
                            
                            // Clear "no files" message if present
                            if (tarpaulinImagePreviews.innerHTML.includes('No files attached yet')) {
                                tarpaulinImagePreviews.innerHTML = '';
                            }
                            
                            const previewItem = document.createElement('div');
                            previewItem.className = 'd-flex align-items-center justify-content-between mb-2 p-2 border rounded';
                            previewItem.innerHTML = `
                                <div class="d-flex align-items-center">
                                    <img src="${e.target.result}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="Pasted Image">
                                    <div>
                                        <div class="small">Pasted Screenshot</div>
                                        <div class="small text-muted">${new Date().toLocaleString()}</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTarpaulinImagePreview(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
                            
                            tarpaulinImagePreviews.appendChild(previewItem);
                        }
                    };
                    
                    reader.readAsDataURL(blob);
                    break;
                }
            }
        });
    }
    
    // Tarpaulin Quantity Update Functionality
    function updateTarpaulinQuantitySummary() {
        const tarpaulinForm = document.getElementById('tarpaulin-form');
        if (tarpaulinForm && tarpaulinForm.style.display !== 'none') {
            const quantityInput = tarpaulinForm.querySelector('input[name="tarpaulin_quantity"]');
            const totalQuantitySpan = document.getElementById('tarpaulin-total-quantity');
            
            if (quantityInput && totalQuantitySpan) {
                const quantity = parseInt(quantityInput.value) || 0;
                totalQuantitySpan.textContent = quantity;
            }
        }
    }
    
    // Add event listener for tarpaulin quantity input
    const tarpaulinForm = document.getElementById('tarpaulin-form');
    if (tarpaulinForm) {
        const quantityInput = tarpaulinForm.querySelector('input[name="tarpaulin_quantity"]');
        if (quantityInput) {
            quantityInput.addEventListener('input', updateTarpaulinQuantitySummary);
            quantityInput.addEventListener('change', updateTarpaulinQuantitySummary);
            
            // Initial update
            updateTarpaulinQuantitySummary();
        }
    }
    
    // Also update when showing the tarpaulin form
    if (window.showIprintOption) {
        const originalShowIprintOption = window.showIprintOption;
        window.showIprintOption = function(option) {
            originalShowIprintOption(option);
            
            // If showing tarpaulin form, update quantity summary
            if (option === 'tarpaulin') {
                setTimeout(updateTarpaulinQuantitySummary, 100);
            }
            
            // If showing sublimation form, update quantity summary
            if (option === 'sublimation') {
                setTimeout(updateSublimationQuantitySummary, 100);
            }
        };
    }
    
    // Sublimation Image Upload Preview Functionality
    const sublimationImageUploadInput = document.getElementById('sublimation-image-upload');
    const sublimationImagePreviews = document.getElementById('sublimation-uploaded-files-list');
    
    if (sublimationImageUploadInput) {
        sublimationImageUploadInput.addEventListener('change', function(event) {
            const files = event.target.files;
            
            if (files.length > 0) {
                // Show actions container
                const actionsContainer = document.getElementById('sublimation-uploaded-files-actions');
                if (actionsContainer) {
                    actionsContainer.classList.remove('d-none');
                }
                
                sublimationImagePreviews.innerHTML = '';
                
                // Process each file
                Array.from(files).forEach((file, index) => {
                    // Check file size (10MB max)
                    if (file.size > 10 * 1024 * 1024) {
                        alert(`File "${file.name}" exceeds 10MB limit. Please select a smaller file.`);
                        return;
                    }
                    
                    // Check file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf', 'image/vnd.adobe.photoshop', 'application/postscript', 'application/illustrator'];
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    const validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'psd', 'ai', 'eps'];
                    
                    if (!validTypes.includes(file.type) && !validExtensions.includes(fileExtension)) {
                        alert(`File "${file.name}" has an unsupported format. Please upload images, PDF, PSD, AI, or EPS files only.`);
                        return;
                    }
                    
                    // Create preview element
                    const previewItem = document.createElement('div');
                    previewItem.className = 'd-flex align-items-center justify-content-between mb-2 p-2 border rounded';
                    
                    // Create preview based on file type
                    if (file.type.startsWith('image/') && !file.type.includes('photoshop') && !file.type.includes('illustrator')) {
                        // Regular image file
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewItem.innerHTML = `
                                <div class="d-flex align-items-center">
                                    <img src="${e.target.result}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="${file.name}">
                                    <div>
                                        <div class="small text-truncate" style="max-width: 200px;" title="${file.name}">${file.name}</div>
                                        <div class="small text-muted">${(file.size / 1024).toFixed(1)} KB</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSublimationImagePreview(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // Document file (PDF, PSD, AI, EPS)
                        let iconClass = 'fas fa-file';
                        if (file.type === 'application/pdf') iconClass = 'fas fa-file-pdf';
                        else if (file.type.includes('photoshop')) iconClass = 'fas fa-file-image';
                        else if (file.type.includes('illustrator') || fileExtension === 'ai') iconClass = 'fas fa-paint-brush';
                        else if (fileExtension === 'eps') iconClass = 'fas fa-vector-square';
                        
                        previewItem.innerHTML = `
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="font-size: 1.5rem;">
                                    <i class="${iconClass} text-primary"></i>
                                </div>
                                <div>
                                    <div class="small text-truncate" style="max-width: 200px;" title="${file.name}">${file.name}</div>
                                    <div class="small text-muted">${(file.size / 1024).toFixed(1)} KB</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSublimationImagePreview(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                    }
                    
                    sublimationImagePreviews.appendChild(previewItem);
                });
            } else {
                // No files selected
                const actionsContainer = document.getElementById('sublimation-uploaded-files-actions');
                if (actionsContainer) {
                    actionsContainer.classList.add('d-none');
                }
                sublimationImagePreviews.innerHTML = '<div class="text-muted small">No files attached yet</div>';
            }
        });
    }
    
    // Remove Sublimation Image Preview Function
    window.removeSublimationImagePreview = function(button) {
        const previewItem = button.closest('.d-flex');
        if (previewItem) {
            previewItem.remove();
        }
        
        // Check if any previews remain
        const previews = document.querySelectorAll('#sublimation-uploaded-files-list .d-flex');
        if (previews.length === 0) {
            const actionsContainer = document.getElementById('sublimation-uploaded-files-actions');
            if (actionsContainer) {
                actionsContainer.classList.add('d-none');
            }
            sublimationImagePreviews.innerHTML = '<div class="text-muted small">No files attached yet</div>';
        }
    };
    
    // Sublimation Clear All Files Function
    const sublimationClearAllBtn = document.getElementById('sublimation-clear-all-files');
    if (sublimationClearAllBtn) {
        sublimationClearAllBtn.addEventListener('click', function() {
            const sublimationImagePreviews = document.getElementById('sublimation-uploaded-files-list');
            if (sublimationImagePreviews) {
                sublimationImagePreviews.innerHTML = '<div class="text-muted small">No files attached yet</div>';
            }
            
            const actionsContainer = document.getElementById('sublimation-uploaded-files-actions');
            if (actionsContainer) {
                actionsContainer.classList.add('d-none');
            }
            
            // Clear file input
            const sublimationImageUploadInput = document.getElementById('sublimation-image-upload');
            if (sublimationImageUploadInput) {
                sublimationImageUploadInput.value = '';
            }
        });
    }
    
    // Sublimation Camera Functionality
    const sublimationStartCameraBtn = document.getElementById('sublimation-start-camera-btn');
    const sublimationCameraPlaceholder = document.getElementById('sublimation-camera-placeholder');
    const sublimationCameraPreview = document.getElementById('sublimation-camera-preview');
    const sublimationCameraVideo = document.getElementById('sublimation-camera-video');
    const sublimationCaptureBtn = document.getElementById('sublimation-capture-btn');
    const sublimationCameraRetryBtn = document.getElementById('sublimation-camera-retry-btn');
    
    let sublimationCameraStream = null;
    
    if (sublimationStartCameraBtn) {
        sublimationStartCameraBtn.addEventListener('click', async function() {
            try {
                sublimationCameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' },
                    audio: false
                });
                
                sublimationCameraVideo.srcObject = sublimationCameraStream;
                sublimationCameraPlaceholder.style.display = 'none';
                sublimationCameraPreview.style.display = 'block';
            } catch (error) {
                console.error('Error accessing camera:', error);
                alert('Unable to access camera. Please check permissions and try again.');
            }
        });
    }
    
    if (sublimationCaptureBtn) {
        sublimationCaptureBtn.addEventListener('click', function() {
            if (!sublimationCameraStream) return;
            
            const canvas = document.createElement('canvas');
            canvas.width = sublimationCameraVideo.videoWidth;
            canvas.height = sublimationCameraVideo.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(sublimationCameraVideo, 0, 0, canvas.width, canvas.height);
            
            const imageDataUrl = canvas.toDataURL('image/jpeg');
            
            // Add to previews
            const sublimationImagePreviews = document.getElementById('sublimation-uploaded-files-list');
            if (sublimationImagePreviews) {
                // Show actions container
                const actionsContainer = document.getElementById('sublimation-uploaded-files-actions');
                if (actionsContainer) {
                    actionsContainer.classList.remove('d-none');
                }
                
                // Clear "no files" message if present
                if (sublimationImagePreviews.innerHTML.includes('No files attached yet')) {
                    sublimationImagePreviews.innerHTML = '';
                }
                
                const previewItem = document.createElement('div');
                previewItem.className = 'd-flex align-items-center justify-content-between mb-2 p-2 border rounded';
                previewItem.innerHTML = `
                    <div class="d-flex align-items-center">
                        <img src="${imageDataUrl}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="Camera Capture">
                        <div>
                            <div class="small">Camera Capture</div>
                            <div class="small text-muted">${new Date().toLocaleString()}</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSublimationImagePreview(this)">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                
                sublimationImagePreviews.appendChild(previewItem);
                
                // Show retry button
                sublimationCaptureBtn.style.display = 'none';
                sublimationCameraRetryBtn.style.display = 'inline-block';
            }
        });
    }
    
    if (sublimationCameraRetryBtn) {
        sublimationCameraRetryBtn.addEventListener('click', function() {
            sublimationCaptureBtn.style.display = 'inline-block';
            sublimationCameraRetryBtn.style.display = 'none';
        });
    }
    
    // Stop camera when leaving tab
    const sublimationCameraTab = document.getElementById('sublimation-camera-tab');
    if (sublimationCameraTab) {
        sublimationCameraTab.addEventListener('click', function() {
            // Camera already started, do nothing
        });
        
        // Stop camera when switching away from camera tab
        const otherTabs = document.querySelectorAll('#sublimation-uploadTabs .nav-link:not(#sublimation-camera-tab)');
        otherTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                if (sublimationCameraStream) {
                    sublimationCameraStream.getTracks().forEach(track => track.stop());
                    sublimationCameraStream = null;
                    sublimationCameraPlaceholder.style.display = 'block';
                    sublimationCameraPreview.style.display = 'none';
                    sublimationCaptureBtn.style.display = 'inline-block';
                    sublimationCameraRetryBtn.style.display = 'none';
                }
            });
        });
    }
    
    // Sublimation Paste Functionality
    const sublimationPasteArea = document.getElementById('sublimation-paste-area');
    if (sublimationPasteArea) {
        sublimationPasteArea.addEventListener('paste', function(event) {
            const items = (event.clipboardData || event.originalEvent.clipboardData).items;
            
            for (let item of items) {
                if (item.type.indexOf('image') !== -1) {
                    const blob = item.getAsFile();
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const sublimationImagePreviews = document.getElementById('sublimation-uploaded-files-list');
                        if (sublimationImagePreviews) {
                            // Show actions container
                            const actionsContainer = document.getElementById('sublimation-uploaded-files-actions');
                            if (actionsContainer) {
                                actionsContainer.classList.remove('d-none');
                            }
                            
                            // Clear "no files" message if present
                            if (sublimationImagePreviews.innerHTML.includes('No files attached yet')) {
                                sublimationImagePreviews.innerHTML = '';
                            }
                            
                            const previewItem = document.createElement('div');
                            previewItem.className = 'd-flex align-items-center justify-content-between mb-2 p-2 border rounded';
                            previewItem.innerHTML = `
                                <div class="d-flex align-items-center">
                                    <img src="${e.target.result}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="Pasted Image">
                                    <div>
                                        <div class="small">Pasted Screenshot</div>
                                        <div class="small text-muted">${new Date().toLocaleString()}</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSublimationImagePreview(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
                            
                            sublimationImagePreviews.appendChild(previewItem);
                        }
                    };
                    
                    reader.readAsDataURL(blob);
                    break;
                }
            }
        });
    }
    
    // Sublimation Quantity Update Functionality
    function updateSublimationQuantitySummary() {
        const sublimationForm = document.getElementById('sublimation-form');
        if (sublimationForm && sublimationForm.style.display !== 'none') {
            const quantityInput = sublimationForm.querySelector('input[name="sublimation_quantity"]');
            const totalQuantitySpan = document.getElementById('sublimation-total-quantity');
            
            if (quantityInput && totalQuantitySpan) {
                const quantity = parseInt(quantityInput.value) || 0;
                totalQuantitySpan.textContent = quantity;
            }
        }
    }
    
    // Add event listener for sublimation quantity input
    const sublimationForm = document.getElementById('sublimation-form');
    if (sublimationForm) {
        const quantityInput = sublimationForm.querySelector('input[name="sublimation_quantity"]');
        if (quantityInput) {
            quantityInput.addEventListener('input', updateSublimationQuantitySummary);
            quantityInput.addEventListener('change', updateSublimationQuantitySummary);
            
            // Initial update
            updateSublimationQuantitySummary();
        }
    }
    
    // Embroidery Image Upload Preview Functionality
    const embroideryImageUploadInput = document.getElementById('embroidery-image-upload');
    const embroideryImagePreviews = document.getElementById('embroidery-uploaded-files-list');
    
    if (embroideryImageUploadInput) {
        embroideryImageUploadInput.addEventListener('change', function(event) {
            const files = event.target.files;
            
            if (files.length > 0) {
                // Show actions container
                const actionsContainer = document.getElementById('embroidery-uploaded-files-actions');
                if (actionsContainer) {
                    actionsContainer.classList.remove('d-none');
                }
                
                embroideryImagePreviews.innerHTML = '';
                
                // Process each file
                Array.from(files).forEach((file, index) => {
                    // Check file size (10MB max)
                    if (file.size > 10 * 1024 * 1024) {
                        alert(`File "${file.name}" exceeds 10MB limit. Please select a smaller file.`);
                        return;
                    }
                    
                    // Check file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf', 'image/vnd.adobe.photoshop', 'application/postscript', 'application/illustrator'];
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    const validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'psd', 'ai', 'eps'];
                    
                    if (!validTypes.includes(file.type) && !validExtensions.includes(fileExtension)) {
                        alert(`File "${file.name}" has an unsupported format. Please upload images, PDF, PSD, AI, or EPS files only.`);
                        return;
                    }
                    
                    // Create preview element
                    const previewItem = document.createElement('div');
                    previewItem.className = 'd-flex align-items-center justify-content-between mb-2 p-2 border rounded';
                    
                    // Create preview based on file type
                    if (file.type.startsWith('image/') && !file.type.includes('photoshop') && !file.type.includes('illustrator')) {
                        // Regular image file
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewItem.innerHTML = `
                                <div class="d-flex align-items-center">
                                    <img src="${e.target.result}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="${file.name}">
                                    <div>
                                        <div class="small text-truncate" style="max-width: 200px;" title="${file.name}">${file.name}</div>
                                        <div class="small text-muted">${(file.size / 1024).toFixed(1)} KB</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEmbroideryImagePreview(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // Document file (PDF, PSD, AI, EPS)
                        let iconClass = 'fas fa-file';
                        if (file.type === 'application/pdf') iconClass = 'fas fa-file-pdf';
                        else if (file.type.includes('photoshop')) iconClass = 'fas fa-file-image';
                        else if (file.type.includes('illustrator') || fileExtension === 'ai') iconClass = 'fas fa-paint-brush';
                        else if (fileExtension === 'eps') iconClass = 'fas fa-vector-square';
                        
                        previewItem.innerHTML = `
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="font-size: 1.5rem;">
                                    <i class="${iconClass} text-primary"></i>
                                </div>
                                <div>
                                    <div class="small text-truncate" style="max-width: 200px;" title="${file.name}">${file.name}</div>
                                    <div class="small text-muted">${(file.size / 1024).toFixed(1)} KB</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEmbroideryImagePreview(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                    }
                    
                    embroideryImagePreviews.appendChild(previewItem);
                });
            } else {
                // No files selected
                const actionsContainer = document.getElementById('embroidery-uploaded-files-actions');
                if (actionsContainer) {
                    actionsContainer.classList.add('d-none');
                }
                embroideryImagePreviews.innerHTML = '<div class="text-muted small">No files attached yet</div>';
            }
        });
    }
    
    // Remove Embroidery Image Preview Function
    window.removeEmbroideryImagePreview = function(button) {
        const previewItem = button.closest('.d-flex');
        if (previewItem) {
            previewItem.remove();
        }
        
        // Check if any previews remain
        const previews = document.querySelectorAll('#embroidery-uploaded-files-list .d-flex');
        if (previews.length === 0) {
            const actionsContainer = document.getElementById('embroidery-uploaded-files-actions');
            if (actionsContainer) {
                actionsContainer.classList.add('d-none');
            }
            embroideryImagePreviews.innerHTML = '<div class="text-muted small">No files attached yet</div>';
        }
    };
    
    // Embroidery Clear All Files Function
    const embroideryClearAllBtn = document.getElementById('embroidery-clear-all-files');
    if (embroideryClearAllBtn) {
        embroideryClearAllBtn.addEventListener('click', function() {
            const embroideryImagePreviews = document.getElementById('embroidery-uploaded-files-list');
            if (embroideryImagePreviews) {
                embroideryImagePreviews.innerHTML = '<div class="text-muted small">No files attached yet</div>';
            }
            
            const actionsContainer = document.getElementById('embroidery-uploaded-files-actions');
            if (actionsContainer) {
                actionsContainer.classList.add('d-none');
            }
            
            // Clear file input
            const embroideryImageUploadInput = document.getElementById('embroidery-image-upload');
            if (embroideryImageUploadInput) {
                embroideryImageUploadInput.value = '';
            }
        });
    }
    
    // Embroidery Camera Functionality
    const embroideryStartCameraBtn = document.getElementById('embroidery-start-camera-btn');
    const embroideryCameraPlaceholder = document.getElementById('embroidery-camera-placeholder');
    const embroideryCameraPreview = document.getElementById('embroidery-camera-preview');
    const embroideryCameraVideo = document.getElementById('embroidery-camera-video');
    const embroideryCaptureBtn = document.getElementById('embroidery-capture-btn');
    const embroideryCameraRetryBtn = document.getElementById('embroidery-camera-retry-btn');
    
    let embroideryCameraStream = null;
    
    if (embroideryStartCameraBtn) {
        embroideryStartCameraBtn.addEventListener('click', async function() {
            try {
                embroideryCameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' },
                    audio: false
                });
                
                embroideryCameraVideo.srcObject = embroideryCameraStream;
                embroideryCameraPlaceholder.style.display = 'none';
                embroideryCameraPreview.style.display = 'block';
            } catch (error) {
                console.error('Error accessing camera:', error);
                alert('Unable to access camera. Please check permissions and try again.');
            }
        });
    }
    
    if (embroideryCaptureBtn) {
        embroideryCaptureBtn.addEventListener('click', function() {
            if (!embroideryCameraStream) return;
            
            const canvas = document.createElement('canvas');
            canvas.width = embroideryCameraVideo.videoWidth;
            canvas.height = embroideryCameraVideo.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(embroideryCameraVideo, 0, 0, canvas.width, canvas.height);
            
            const imageDataUrl = canvas.toDataURL('image/jpeg');
            
            // Add to previews
            const embroideryImagePreviews = document.getElementById('embroidery-uploaded-files-list');
            if (embroideryImagePreviews) {
                // Show actions container
                const actionsContainer = document.getElementById('embroidery-uploaded-files-actions');
                if (actionsContainer) {
                    actionsContainer.classList.remove('d-none');
                }
                
                // Clear "no files" message if present
                if (embroideryImagePreviews.innerHTML.includes('No files attached yet')) {
                    embroideryImagePreviews.innerHTML = '';
                }
                
                const previewItem = document.createElement('div');
                previewItem.className = 'd-flex align-items-center justify-content-between mb-2 p-2 border rounded';
                previewItem.innerHTML = `
                    <div class="d-flex align-items-center">
                        <img src="${imageDataUrl}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="Camera Capture">
                        <div>
                            <div class="small">Camera Capture</div>
                            <div class="small text-muted">${new Date().toLocaleString()}</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEmbroideryImagePreview(this)">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                
                embroideryImagePreviews.appendChild(previewItem);
                
                // Show retry button
                embroideryCaptureBtn.style.display = 'none';
                embroideryCameraRetryBtn.style.display = 'inline-block';
            }
        });
    }
    
    if (embroideryCameraRetryBtn) {
        embroideryCameraRetryBtn.addEventListener('click', function() {
            embroideryCaptureBtn.style.display = 'inline-block';
            embroideryCameraRetryBtn.style.display = 'none';
        });
    }
    
    // Stop camera when leaving tab
    const embroideryCameraTab = document.getElementById('embroidery-camera-tab');
    if (embroideryCameraTab) {
        embroideryCameraTab.addEventListener('click', function() {
            // Camera already started, do nothing
        });
        
        // Stop camera when switching away from camera tab
        const otherTabs = document.querySelectorAll('#embroidery-uploadTabs .nav-link:not(#embroidery-camera-tab)');
        otherTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                if (embroideryCameraStream) {
                    embroideryCameraStream.getTracks().forEach(track => track.stop());
                    embroideryCameraStream = null;
                    embroideryCameraPlaceholder.style.display = 'block';
                    embroideryCameraPreview.style.display = 'none';
                    embroideryCaptureBtn.style.display = 'inline-block';
                    embroideryCameraRetryBtn.style.display = 'none';
                }
            });
        });
    }
    
    // Embroidery Paste Functionality
    const embroideryPasteArea = document.getElementById('embroidery-paste-area');
    if (embroideryPasteArea) {
        embroideryPasteArea.addEventListener('paste', function(event) {
            const items = (event.clipboardData || event.originalEvent.clipboardData).items;
            
            for (let item of items) {
                if (item.type.indexOf('image') !== -1) {
                    const blob = item.getAsFile();
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const embroideryImagePreviews = document.getElementById('embroidery-uploaded-files-list');
                        if (embroideryImagePreviews) {
                            // Show actions container
                            const actionsContainer = document.getElementById('embroidery-uploaded-files-actions');
                            if (actionsContainer) {
                                actionsContainer.classList.remove('d-none');
                            }
                            
                            // Clear "no files" message if present
                            if (embroideryImagePreviews.innerHTML.includes('No files attached yet')) {
                                embroideryImagePreviews.innerHTML = '';
                            }
                            
                            const previewItem = document.createElement('div');
                            previewItem.className = 'd-flex align-items-center justify-content-between mb-2 p-2 border rounded';
                            previewItem.innerHTML = `
                                <div class="d-flex align-items-center">
                                    <img src="${e.target.result}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="Pasted Image">
                                    <div>
                                        <div class="small">Pasted Screenshot</div>
                                        <div class="small text-muted">${new Date().toLocaleString()}</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEmbroideryImagePreview(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
                            
                            embroideryImagePreviews.appendChild(previewItem);
                        }
                    };
                    
                    reader.readAsDataURL(blob);
                    break;
                }
            }
        });
    }
    
    // Embroidery Quantity Update Functionality (Updated to include print and shirt sizes)
    function updateEmbroideryQuantitySummary() {
        const embroideryForm = document.getElementById('embroidery-form');
        if (embroideryForm && embroideryForm.style.display !== 'none') {
            let totalQuantity = 0;
            
            // Add print size quantities
            const printSizeQuantities = embroideryForm.querySelectorAll('.embroidery-print-size-quantity');
            printSizeQuantities.forEach(input => {
                const quantity = parseInt(input.value) || 0;
                totalQuantity += quantity;
            });
            
            // Add shirt size quantities
            const shirtSizeQuantities = embroideryForm.querySelectorAll('.embroidery-shirt-size-quantity');
            shirtSizeQuantities.forEach(input => {
                const quantity = parseInt(input.value) || 0;
                totalQuantity += quantity;
            });
            
            const totalQuantitySpan = document.getElementById('embroidery-total-quantity');
            if (totalQuantitySpan) {
                totalQuantitySpan.textContent = totalQuantity || 1;
            }
        }
    }
    
    // Add event listener for embroidery quantity input
    const embroideryForm = document.getElementById('embroidery-form');
    if (embroideryForm) {
        const quantityInput = embroideryForm.querySelector('input[name="embroidery_quantity"]');
        if (quantityInput) {
            quantityInput.addEventListener('input', updateEmbroideryQuantitySummary);
            quantityInput.addEventListener('change', updateEmbroideryQuantitySummary);
            
            // Initial update
            updateEmbroideryQuantitySummary();
        }
    }
    
    // Also update when showing the embroidery form
    if (window.showIprintOption) {
        const originalShowIprintOption = window.showIprintOption;
        window.showIprintOption = function(option) {
            originalShowIprintOption(option);
            
            // If showing embroidery form, update quantity summary
            if (option === 'embroidery') {
                setTimeout(updateEmbroideryQuantitySummary, 100);
            }
        };
    }
    
    // Embroidery Dynamic Table Functions
    // Add Embroidery Print Size Row
    window.addEmbroideryPrintSizeRow = function() {
        console.log('Add embroidery print size row clicked');
        
        try {
            const container = document.getElementById('embroidery-print-sizes-container');
            if (!container) {
                console.error('Embroidery print sizes container not found');
                return;
            }
            
            // Count existing rows
            const existingRows = container.querySelectorAll('.embroidery-print-size-row');
            const rowCount = existingRows.length;
            const rowNumber = rowCount + 1;
            
            // Create new row HTML with delete button
            const newRow = document.createElement('tr');
            newRow.className = 'embroidery-print-size-row';
            newRow.innerHTML = `
                <td class="align-middle text-center">${rowNumber}</td>
                <td>
                    <select class="form-select form-select-sm embroidery-print-size-select" name="embroidery_print_size[]" required>
                        <option value="" selected disabled>Select print size</option>
                        <option value="8x10">8x10 inches</option>
                        <option value="10x12">10x12 inches</option>
                        <option value="12x14">12x14 inches</option>
                        <option value="14x16">14x16 inches</option>
                        <option value="16x20">16x20 inches</option>
                        <option value="custom">Custom Size</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm embroidery-custom-size-input" name="embroidery_custom_size[]" placeholder="e.g., 18x24 inches" style="display: none;">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm embroidery-print-size-quantity" name="embroidery_print_size_quantity[]" min="1" value="1" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger delete-embroidery-print-size-btn" onclick="deleteEmbroideryPrintSizeRow(this)" title="Delete this print size">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            container.appendChild(newRow);
            
            // Add event listener for custom size toggle
            const select = newRow.querySelector('.embroidery-print-size-select');
            const customInput = newRow.querySelector('.embroidery-custom-size-input');
            
            select.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customInput.style.display = 'block';
                    customInput.required = true;
                } else {
                    customInput.style.display = 'none';
                    customInput.required = false;
                    customInput.value = '';
                }
            });
            
            // Add quantity change listener
            const quantityInput = newRow.querySelector('.embroidery-print-size-quantity');
            quantityInput.addEventListener('input', updateEmbroideryQuantitySummary);
            quantityInput.addEventListener('change', updateEmbroideryQuantitySummary);
            
            // Update row numbers and enable delete buttons
            updateEmbroideryPrintSizeRowNumbers();
            
            console.log('Added embroidery print size row, total rows:', rowCount + 1);
            
        } catch (error) {
            console.error('Error adding embroidery print size row:', error);
        }
    };
    
    // Delete Embroidery Print Size Row
    window.deleteEmbroideryPrintSizeRow = function(button) {
        console.log('Delete embroidery print size row clicked');
        
        try {
            const row = button.closest('.embroidery-print-size-row');
            if (row) {
                row.remove();
                updateEmbroideryPrintSizeRowNumbers();
                updateEmbroideryQuantitySummary();
                console.log('Deleted embroidery print size row');
            }
        } catch (error) {
            console.error('Error deleting embroidery print size row:', error);
        }
    };
    
    // Update Embroidery Print Size Row Numbers
    function updateEmbroideryPrintSizeRowNumbers() {
        const rows = document.querySelectorAll('.embroidery-print-size-row');
        rows.forEach((row, index) => {
            const rowNumberCell = row.querySelector('td:first-child');
            const deleteButton = row.querySelector('.delete-embroidery-print-size-btn');
            
            if (rowNumberCell) {
                rowNumberCell.textContent = index + 1;
            }
            
            if (deleteButton) {
                // Disable delete button if this is the only row
                deleteButton.disabled = rows.length === 1;
                deleteButton.title = rows.length === 1 ? 'Cannot delete the only print size' : 'Delete this print size';
            }
        });
    }
    
    // Add Embroidery Shirt Size Row
    window.addEmbroideryShirtSizeRow = function() {
        console.log('Add embroidery shirt size row clicked');
        
        try {
            const container = document.getElementById('embroidery-shirt-sizes-container');
            if (!container) {
                console.error('Embroidery shirt sizes container not found');
                return;
            }
            
            // Count existing rows
            const existingRows = container.querySelectorAll('.embroidery-shirt-size-row');
            const rowCount = existingRows.length;
            const rowNumber = rowCount + 1;
            
            // Create new row HTML with delete button
            const newRow = document.createElement('tr');
            newRow.className = 'embroidery-shirt-size-row';
            newRow.innerHTML = `
                <td class="align-middle text-center">${rowNumber}</td>
                <td>
                    <select class="form-select form-select-sm embroidery-shirt-size-select" name="embroidery_shirt_size[]" required>
                        <option value="" selected disabled>Select size</option>
                        <option value="xs">XS (Extra Small)</option>
                        <option value="s">S (Small)</option>
                        <option value="m">M (Medium)</option>
                        <option value="l">L (Large)</option>
                        <option value="xl">XL (Extra Large)</option>
                        <option value="2xl">2XL (Double Extra Large)</option>
                        <option value="3xl">3XL (Triple Extra Large)</option>
                        <option value="4xl">4XL (4X Extra Large)</option>
                        <option value="5xl">5XL (5X Extra Large)</option>
                        <option value="6xl">6XL (6X Extra Large)</option>
                        <option value="7xl">7XL (7X Extra Large)</option>
                        <option value="8xl">8XL (8X Extra Large)</option>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm embroidery-shirt-size-quantity" name="embroidery_shirt_size_quantity[]" min="1" value="1" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger delete-embroidery-shirt-size-btn" onclick="deleteEmbroideryShirtSizeRow(this)" title="Delete this size">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            container.appendChild(newRow);
            
            // Add quantity change listener
            const quantityInput = newRow.querySelector('.embroidery-shirt-size-quantity');
            quantityInput.addEventListener('input', updateEmbroideryQuantitySummary);
            quantityInput.addEventListener('change', updateEmbroideryQuantitySummary);
            
            // Update row numbers and enable delete buttons
            updateEmbroideryShirtSizeRowNumbers();
            
            console.log('Added embroidery shirt size row, total rows:', rowCount + 1);
            
        } catch (error) {
            console.error('Error adding embroidery shirt size row:', error);
        }
    };
    
    // Delete Embroidery Shirt Size Row
    window.deleteEmbroideryShirtSizeRow = function(button) {
        console.log('Delete embroidery shirt size row clicked');
        
        try {
            const row = button.closest('.embroidery-shirt-size-row');
            if (row) {
                row.remove();
                updateEmbroideryShirtSizeRowNumbers();
                updateEmbroideryQuantitySummary();
                console.log('Deleted embroidery shirt size row');
            }
        } catch (error) {
            console.error('Error deleting embroidery shirt size row:', error);
        }
    };
    
    // Update Embroidery Shirt Size Row Numbers
    function updateEmbroideryShirtSizeRowNumbers() {
        const rows = document.querySelectorAll('.embroidery-shirt-size-row');
        rows.forEach((row, index) => {
            const rowNumberCell = row.querySelector('td:first-child');
            const deleteButton = row.querySelector('.delete-embroidery-shirt-size-btn');
            
            if (rowNumberCell) {
                rowNumberCell.textContent = index + 1;
            }
            
            if (deleteButton) {
                // Disable delete button if this is the only row
                deleteButton.disabled = rows.length === 1;
                deleteButton.title = rows.length === 1 ? 'Cannot delete the only size' : 'Delete this size';
            }
        });
    }
    
    // Initialize Embroidery form event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners for embroidery print size custom input toggle
        const embroideryPrintSizeSelects = document.querySelectorAll('.embroidery-print-size-select');
        embroideryPrintSizeSelects.forEach(select => {
            const customInput = select.closest('.embroidery-print-size-row').querySelector('.embroidery-custom-size-input');
            
            select.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customInput.style.display = 'block';
                    customInput.required = true;
                } else {
                    customInput.style.display = 'none';
                    customInput.required = false;
                    customInput.value = '';
                }
            });
        });
        
        // Add event listeners for embroidery quantity inputs
        const embroideryPrintSizeQuantities = document.querySelectorAll('.embroidery-print-size-quantity');
        embroideryPrintSizeQuantities.forEach(input => {
            input.addEventListener('input', updateEmbroideryQuantitySummary);
            input.addEventListener('change', updateEmbroideryQuantitySummary);
        });
        
        const embroideryShirtSizeQuantities = document.querySelectorAll('.embroidery-shirt-size-quantity');
        embroideryShirtSizeQuantities.forEach(input => {
            input.addEventListener('input', updateEmbroideryQuantitySummary);
            input.addEventListener('change', updateEmbroideryQuantitySummary);
        });
        
        // Add event listeners for embroidery add buttons
        const addEmbroideryPrintSizeBtn = document.getElementById('add-embroidery-print-size');
        if (addEmbroideryPrintSizeBtn) {
            addEmbroideryPrintSizeBtn.addEventListener('click', addEmbroideryPrintSizeRow);
        }
        
        const addEmbroideryShirtSizeBtn = document.getElementById('add-embroidery-shirt-size');
        if (addEmbroideryShirtSizeBtn) {
            addEmbroideryShirtSizeBtn.addEventListener('click', addEmbroideryShirtSizeRow);
        }
        
        // Initial quantity update
        updateEmbroideryQuantitySummary();
    });
    
    // Lanyard Quantity Update Functionality
    function updateLanyardQuantitySummary() {
        const lanyardForm = document.getElementById('lanyard-form');
        if (lanyardForm && lanyardForm.style.display !== 'none') {
            const quantityInput = lanyardForm.querySelector('input[name="lanyard_quantity"]');
            const totalQuantitySpan = document.getElementById('lanyard-total-quantity');
            
            if (quantityInput && totalQuantitySpan) {
                const quantity = parseInt(quantityInput.value) || 0;
                totalQuantitySpan.textContent = quantity;
            }
        }
    }
    
    // Add event listener for lanyard quantity input
    const lanyardForm = document.getElementById('lanyard-form');
    if (lanyardForm) {
        const quantityInput = lanyardForm.querySelector('input[name="lanyard_quantity"]');
        if (quantityInput) {
            quantityInput.addEventListener('input', updateLanyardQuantitySummary);
            quantityInput.addEventListener('change', updateLanyardQuantitySummary);
            
            // Initial update
            updateLanyardQuantitySummary();
        }
    }
    
    // Also update when showing the lanyard form
    const originalShowIprintOption = window.showIprintOption;
    window.showIprintOption = function(option) {
        originalShowIprintOption(option);
        
        // If showing lanyard form, update quantity summary
        if (option === 'lanyard') {
            setTimeout(updateLanyardQuantitySummary, 100);
        }
    };
    
    // Check all forms for completion status on page load
    setTimeout(() => {
        checkAllFormsCompletion();
        updateLanyardQuantitySummary();
    }, 500);
    
    console.log('Event listeners setup complete');
});
</script>
@endpush