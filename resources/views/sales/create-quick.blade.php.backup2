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
                                    <button type="button" class="btn btn-outline-primary w-100 py-2" data-order-type="iprint" onclick="showOrderForm('iprint');">
                                        <i class="fas fa-print fa-lg me-2"></i>
                                        <span class="small fw-bold">iPrint</span>
                                    </button>
                                </div>
                                
                                <!-- Consol Option -->
                                <div class="col-md-3 col-sm-6">
                                    <button type="button" class="btn btn-outline-success w-100 py-2" data-order-type="consol" onclick="alert('Consol button clicked!'); showOrderForm('consol');">
                                        <i class="fas fa-boxes fa-lg me-2"></i>
                                        <span class="small fw-bold">Consol</span>
                                    </button>
                                </div>
                                
                                <!-- Class Option -->
                                <div class="col-md-3 col-sm-6">
                                    <button type="button" class="btn btn-outline-info w-100 py-2" data-order-type="class" onclick="alert('Class button clicked!'); showOrderForm('class');">
                                        <i class="fas fa-graduation-cap fa-lg me-2"></i>
                                        <span class="small fw-bold">Class</span>
                                    </button>
                                </div>
                                
                                <!-- Cinco Option -->
                                <div class="col-md-3 col-sm-6">
                                    <button type="button" class="btn btn-outline-warning w-100 py-2" data-order-type="cinco" onclick="alert('Cinco button clicked!'); showOrderForm('cinco');">
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
                                                    <button type="button" class="btn btn-outline-primary w-100 py-2 iprint-option-btn" data-option="dtf" onclick="showIprintOption('dtf');">
                                                        <i class="fas fa-print fa-lg me-2"></i>
                                                        <span class="small fw-bold">DTF & Subli</span>
                                                    </button>
                                                </div>
                                                <div class="col-md-4 col-sm-6">
                                                    <button type="button" class="btn btn-outline-success w-100 py-2 iprint-option-btn" data-option="lanyard" onclick="showIprintOption('lanyard');">
                                                        <i class="fas fa-id-card fa-lg me-2"></i>
                                                        <span class="small fw-bold">Lanyard</span>
                                                    </button>
                                                </div>
                                                <div class="col-md-4 col-sm-6">
                                                    <button type="button" class="btn btn-outline-info w-100 py-2 iprint-option-btn" data-option="tarpaulin" onclick="showIprintOption('tarpaulin');">
                                                        <i class="fas fa-image fa-lg me-2"></i>
                                                        <span class="small fw-bold">Tarpaulin</span>
                                                    </button>
                                                </div>
                                                <div class="col-md-4 col-sm-6">
                                                    <button type="button" class="btn btn-outline-warning w-100 py-2 iprint-option-btn" data-option="sublimation" onclick="showIprintOption('sublimation');">
                                                        <i class="fas fa-fire fa-lg me-2"></i>
                                                        <span class="small fw-bold">Sublimation</span>
                                                    </button>
                                                </div>
                                                <div class="col-md-4 col-sm-6">
                                                    <button type="button" class="btn btn-outline-danger w-100 py-2 iprint-option-btn" data-option="embroidery" onclick="showIprintOption('embroidery');">
                                                        <i class="fas fa-threads fa-lg me-2"></i>
                                                        <span class="small fw-bold">Embroidery</span>
                                                    </button>
                                                </div>
                                                <div class="col-md-4 col-sm-6">
                                                    <button type="button" class="btn btn-outline-secondary w-100 py-2 iprint-option-btn" data-option="other" onclick="showIprintOption('other');">
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
                                            </div>
                                        </div>
                                        
                                        <!-- Step 2: Detailed Form (Hidden Initially) -->
                                        <div id="iprint-details-container" style="display: none;">
                                            <div class="mb-3 p-3 border rounded">
                                                <h6 class="mb-3 text-primary" id="iprint-selected-type">Selected: DTF and Subli Print</h6>
                                                
                                                <!-- DTF and Subli Print Form -->
                                                <div id="dtf-form" class="iprint-type-form" style="display: none;">
                                                    <div class="row g-3">
                                                        <!-- Type of Print Dropdown - Centered below header -->
                                                        <div class="col-md-12 text-center mb-3">
                                                            <div class="d-inline-block" style="max-width: 300px;">
                                                                <label class="form-label fw-semibold small mb-1">Type of Print</label>
                                                                <select class="form-select form-select-sm" name="dtf_print_type" id="dtf-print-type" required>
                                                                    <option value="" selected disabled>Select print type</option>
                                                                    <option value="dtf">DTF Print</option>
                                                                    <option value="subli">Subli Print</option>
                                                                </select>
                                                            </div>
                                                        </div>

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
                                                        <div class="col-md-12 mt-3">
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
                                                            <input type="number" class="form-control form-control-sm" name="lanyard_quantity" id="lanyard-quantity-input" min="50" value="100">
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
                                                        <!-- Print Details -->
                                                        <div class="col-md-12 mt-3">
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
                                                            <input type="number" class="form-control form-control-sm" name="tarpaulin_quantity" id="tarpaulin-quantity-input" min="1" value="1">
                                                        </div>
                                                        <!-- Print Specifications -->
                                                        <div class="col-md-12 mt-3">
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
                                                            <input type="number" class="form-control form-control-sm" name="sublimation_quantity" id="sublimation-simple-quantity" min="1" value="1">
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
                                                        <!-- Design Details -->
                                                        <div class="col-md-12 mt-3">
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
                                                                    <tbody id="embroidery-print-sizes-container">
                                                                        <!-- First Print Size Row -->
                                                                        <tr class="embroidery-print-size-row">
                                                                            <td class="align-middle text-center">1</td>
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
                                                                                <button type="button" class="btn btn-sm btn-outline-danger delete-embroidery-print-size-btn" onclick="deleteEmbroideryPrintSizeRow(this)" disabled title="Cannot delete the only print size">
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
                                                            <select class="form-select form-select-sm" name="embroidery_material_type" required>
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
                                                            <select class="form-select form-select-sm" name="embroidery_brand" required>
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
                                                            <select class="form-select form-select-sm" name="embroidery_color" required>
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
                                                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-embroidery-shirt-size">
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
                                                        <div class="col-md-12 mt-3">
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
                                                                                <div id="embroidery-pasted-image-container" class="d-none">
                                                                                    <h6 class="small">Pasted Screenshot:</h6>
                                                                                    <img id="embroidery-pasted-image-preview" class="img-fluid rounded border mb-2" style="max-height: 200px;">
                                                                                    <div>
                                                                                        <button type="button" class="btn btn-sm btn-primary" id="embroidery-use-pasted-image">
                                                                                            <i class="fas fa-check me-1"></i> Use This Screenshot
                                                                                        </button>
                                                                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="embroidery-clear-pasted-image">
                                                                                            <i class="fas fa-times me-1"></i> Clear
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <!-- Camera Tab -->
                                                                        <div class="tab-pane fade" id="embroidery-camera-tab-pane" role="tabpanel">
                                                                            <div class="text-center">
                                                                                <!-- Camera Preview -->
                                                                                <div id="embroidery-camera-preview-container" class="mb-3 d-none">
                                                                                    <video id="embroidery-camera-preview" autoplay playsinline class="img-fluid rounded border" style="max-height: 200px;"></video>
                                                                                </div>
                                                                                
                                                                                <!-- Camera Controls -->
                                                                                <div class="mb-3">
                                                                                    <button type="button" class="btn btn-sm btn-primary" id="embroidery-start-camera">
                                                                                        <i class="fas fa-camera me-1"></i> Start Camera
                                                                                    </button>
                                                                                    <button type="button" class="btn btn-sm btn-success d-none" id="embroidery-capture-photo">
                                                                                        <i class="fas fa-camera me-1"></i> Capture Photo
                                                                                    </button>
                                                                                    <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="embroidery-stop-camera">
                                                                                        <i class="fas fa-stop me-1"></i> Stop Camera
                                                                                    </button>
                                                                                </div>
                                                                                
                                                                                <!-- Captured Photo Preview -->
                                                                                <div id="embroidery-captured-photo-container" class="d-none">
                                                                                    <h6 class="small">Captured Photo:</h6>
                                                                                    <canvas id="embroidery-captured-photo-canvas" class="d-none"></canvas>
                                                                                    <img id="embroidery-captured-photo-preview" class="img-fluid rounded border mb-2" style="max-height: 200px;">
                                                                                    <div>
                                                                                        <button type="button" class="btn btn-sm btn-primary" id="embroidery-use-captured-photo">
                                                                                            <i class="fas fa-check me-1"></i> Use This Photo
                                                                                        </button>
                                                                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="embroidery-retake-photo">
                                                                                            <i class="fas fa-redo me-1"></i> Retake
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Quantity Display Badge -->
                                                        <div class="col-md-12">
                                                            <div class="alert alert-info p-2">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="small fw-semibold mb-2">Print Sizes Summary:</div>
                                                                        <div id="embroidery-print-size-breakdown" class="small mb-2">
                                                                            <div class="d-flex align-items-center mb-1">
                                                                                <span class="me-2">8x10:</span>
                                                                                <span class="badge bg-secondary">0</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="mt-2">
                                                                            <span class="small fw-semibold">Print Total:</span>
                                                                            <span class="badge bg-primary ms-2" id="embroidery-print-total-quantity">1</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="small fw-semibold mb-2">Shirt Sizes Summary:</div>
                                                                        <div id="embroidery-shirt-size-breakdown" class="small mb-2">
                                                                            <div class="d-flex align-items-center mb-1">
                                                                                <span class="me-2">XS:</span>
                                                                                <span class="badge bg-secondary">0</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="mt-2">
                                                                            <span class="small fw-semibold">Shirt Total:</span>
                                                                            <span class="badge bg-primary ms-2" id="embroidery-shirt-total-quantity">1</span>
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
                                        <!-- Consol form will be added later -->
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Consol form will be implemented separately.
                                        </div>
                                    </div>
                                    
                                    <!-- Class Form -->
                                    <div id="class-form" style="display: none;">
                                        <!-- Class form will be added later -->
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Class form will be implemented separately.
                                        </div>
                                    </div>
                                    
                                    <!-- Cinco Form -->
                                    <div id="cinco-form" style="display: none;">
                                        <!-- Cinco form will be added later -->
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Cinco form will be implemented separately.
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
        console.log('DEBUG: Simple JavaScript loaded');
        
        // Function to show/hide order forms (iPrint, Consol, Class, Cinco)
        function showOrderForm(type) {
            console.log('DEBUG: showOrderForm called with type:', type);
            
            // Hide all main forms
            const forms = ['iprint-form', 'consol-form', 'class-form', 'cinco-form'];
            forms.forEach(formId => {
                const form = document.getElementById(formId);
                if (form) form.style.display = 'none';
            });
            
            // Hide all iprint-type-form elements (DTF, Lanyard, etc.)
            document.querySelectorAll('.iprint-type-form').forEach(form => {
                form.style.display = 'none';
            });
            
            // Show the selected form
            const targetForm = document.getElementById(type + '-form');
            if (targetForm) targetForm.style.display = 'block';
            
            // Show the order-forms-container
            const container = document.getElementById('order-forms-container');
            if (container) container.style.display = 'block';
            
            // Hide details containers when switching main forms
            const details = document.getElementById('iprint-details-container');
            if (details) details.style.display = 'none';
            
            const table = document.getElementById('selected-printing-types-container');
            if (table) table.style.display = 'none';
        }
        
        // Function to show/hide iPrint option forms (DTF, Lanyard, etc.)
        // Create a new form instance for a specific item
        // Reinitialize event listeners for cloned form instances
        function reinitializeFormEventListeners(form, option, instanceId) {
            console.log('DEBUG: Reinitializing event listeners for:', form.id, 'option:', option, 'instance:', instanceId);
            
            // Initialize quantity auto-compute for this form
            initializeQuantityAutoComputeForForm(form);
            
            // REINITIALIZE IMAGE UPLOAD INPUTS for this form instance
            reinitializeImageUploadInputs(form, option, instanceId);
            
            console.log('DEBUG: Event listeners reinitialized for:', form.id);
        }
        

        
        // Reinitialize image upload inputs for cloned form instances
        function reinitializeImageUploadInputs(form, option, instanceId) {
            console.log('DEBUG: Reinitializing image upload inputs for:', form.id);
            
            // Find all image upload inputs in this form
            const imageUploadInputs = form.querySelectorAll('input[type="file"]');
            imageUploadInputs.forEach(input => {
                // Clone the input to clear any existing event listeners
                const newInput = input.cloneNode(true);
                input.parentNode.replaceChild(newInput, input);
                
                console.log('DEBUG: Replaced image upload input:', newInput.id);
            });
            
            // The global event delegation in initializeImageUploadPreviews()
            // will handle these new inputs automatically
        }
        
        // Initialize quantity auto-compute for a specific form
        function initializeQuantityAutoComputeForForm(form) {
            // Find all quantity inputs in this form
            const quantityInputs = form.querySelectorAll('input[type="number"]');
            quantityInputs.forEach(input => {
                // Remove existing listeners first
                const newInput = input.cloneNode(true);
                input.parentNode.replaceChild(newInput, input);
                
                // Add new listeners
                newInput.addEventListener('input', function() {
                    // Determine which container this belongs to
                    const container = this.closest('tbody');
                    if (container) {
                        const containerId = container.id;
                        // Check for any container with "sizes-container" in ID
                        if (containerId && containerId.includes('sizes-container')) {
                            calculateTotalQuantity(containerId);
                        }
                    }
                });
                
                newInput.addEventListener('change', function() {
                    const container = this.closest('tbody');
                    if (container) {
                        const containerId = container.id;
                        // Check for any container with "sizes-container" in ID
                        if (containerId && containerId.includes('sizes-container')) {
                            calculateTotalQuantity(containerId);
                        }
                    }
                });
            });
            
            // Also handle simple quantity inputs
            const simpleQuantityInputs = form.querySelectorAll('[id*="lanyard-quantity-input"], [id*="tarpaulin-quantity-input"], [id*="sublimation-simple-quantity"]');
            simpleQuantityInputs.forEach(input => {
                const newInput = input.cloneNode(true);
                input.parentNode.replaceChild(newInput, input);
                
                newInput.addEventListener('input', function() {
                    updateSimpleTotalQuantity(this.id, this.value);
                });
                
                newInput.addEventListener('change', function() {
                    updateSimpleTotalQuantity(this.id, this.value);
                });
            });
        }
        
        // Restore form data from table details when editing again
        function restoreFormDataFromTable(instanceId, option, form) {
            console.log('DEBUG: Restoring form data for:', instanceId, option);
            
            // Get the current details from table
            const detailsElement = document.getElementById(`details-${instanceId}`);
            if (!detailsElement) return;
            
            const detailsText = detailsElement.textContent;
            console.log('DEBUG: Current details:', detailsText);
            
            // If details show "Missing" or "Click Edit", no data to restore
            if (detailsText.includes('Missing') || detailsText.includes('Click Edit')) {
                console.log('DEBUG: No saved data to restore');
                return;
            }
            
            // Parse the details text based on option type
            if (option === 'dtf') {
                // Format: "Type: {print_type}, Size: {size}, Qty: {quantity}"
                const typeMatch = detailsText.match(/Type:\s*([^,]+)/);
                const sizeMatch = detailsText.match(/Size:\s*([^,]+)/);
                const qtyMatch = detailsText.match(/Qty:\s*(\d+)/);
                
                if (typeMatch) {
                    const printTypeSelect = form.querySelector('select[name="dtf_print_type"]');
                    if (printTypeSelect) {
                        // Find and select the matching option
                        for (let i = 0; i < printTypeSelect.options.length; i++) {
                            if (printTypeSelect.options[i].text === typeMatch[1].trim()) {
                                printTypeSelect.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }
                
                if (sizeMatch) {
                    const sizeSelect = form.querySelector('select[name="dtf_print_size[]"]');
                    if (sizeSelect) {
                        for (let i = 0; i < sizeSelect.options.length; i++) {
                            if (sizeSelect.options[i].text === sizeMatch[1].trim()) {
                                sizeSelect.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }
                
                if (qtyMatch) {
                    // For DTF, quantity is in the total badge, not a single input
                    // We'll update the quantity inputs if they exist
                    const quantityInputs = form.querySelectorAll('input[name="dtf_print_quantity[]"]');
                    if (quantityInputs.length > 0) {
                        // Set first quantity input to the total
                        quantityInputs[0].value = qtyMatch[1];
                    }
                }
                
            } else if (option === 'lanyard') {
                // Format: "Type: {type}, Qty: {quantity}"
                const typeMatch = detailsText.match(/Type:\s*([^,]+)/);
                const qtyMatch = detailsText.match(/Qty:\s*(\d+)/);
                
                if (typeMatch) {
                    const typeSelect = form.querySelector('select[name="lanyard_type"]');
                    if (typeSelect) {
                        for (let i = 0; i < typeSelect.options.length; i++) {
                            if (typeSelect.options[i].text === typeMatch[1].trim()) {
                                typeSelect.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }
                
                if (qtyMatch) {
                    const qtyInput = form.querySelector('input[name="lanyard_quantity"]');
                    if (qtyInput) qtyInput.value = qtyMatch[1];
                }
                
            } else if (option === 'tarpaulin') {
                // Format: "Size: {size}, Qty: {quantity}"
                const sizeMatch = detailsText.match(/Size:\s*([^,]+)/);
                const qtyMatch = detailsText.match(/Qty:\s*(\d+)/);
                
                if (sizeMatch) {
                    const sizeSelect = form.querySelector('select[name="tarpaulin_size"]');
                    if (sizeSelect) {
                        for (let i = 0; i < sizeSelect.options.length; i++) {
                            if (sizeSelect.options[i].text === sizeMatch[1].trim()) {
                                sizeSelect.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }
                
                if (qtyMatch) {
                    const qtyInput = form.querySelector('input[name="tarpaulin_quantity"]');
                    if (qtyInput) qtyInput.value = qtyMatch[1];
                }
                
            } else if (option === 'sublimation') {
                // Format: "Type: {type}, Size: {size}, Qty: {quantity}"
                const typeMatch = detailsText.match(/Type:\s*([^,]+)/);
                const sizeMatch = detailsText.match(/Size:\s*([^,]+)/);
                const qtyMatch = detailsText.match(/Qty:\s*(\d+)/);
                
                if (typeMatch) {
                    const typeSelect = form.querySelector('select[name="sublimation_type"]');
                    if (typeSelect) {
                        for (let i = 0; i < typeSelect.options.length; i++) {
                            if (typeSelect.options[i].text === typeMatch[1].trim()) {
                                typeSelect.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }
                
                if (sizeMatch) {
                    const sizeSelect = form.querySelector('select[name="sublimation_size"]');
                    if (sizeSelect) {
                        for (let i = 0; i < sizeSelect.options.length; i++) {
                            if (sizeSelect.options[i].text === sizeMatch[1].trim()) {
                                sizeSelect.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }
                
                if (qtyMatch) {
                    const qtyInput = form.querySelector('input[name="sublimation_quantity"]');
                    if (qtyInput) qtyInput.value = qtyMatch[1];
                }
                
            } else if (option === 'embroidery') {
                // Format: "Type: {type}, Size: {size}, Qty: {quantity}"
                const typeMatch = detailsText.match(/Type:\s*([^,]+)/);
                const sizeMatch = detailsText.match(/Size:\s*([^,]+)/);
                const qtyMatch = detailsText.match(/Qty:\s*(\d+)/);
                
                if (typeMatch) {
                    const typeSelect = form.querySelector('select[name="embroidery_type"]');
                    if (typeSelect) {
                        for (let i = 0; i < typeSelect.options.length; i++) {
                            if (typeSelect.options[i].text === typeMatch[1].trim()) {
                                typeSelect.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }
                
                if (sizeMatch) {
                    const sizeSelect = form.querySelector('select[name="embroidery_size"]');
                    if (sizeSelect) {
                        for (let i = 0; i < sizeSelect.options.length; i++) {
                            if (sizeSelect.options[i].text === sizeMatch[1].trim()) {
                                sizeSelect.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }
                
                if (qtyMatch) {
                    const qtyInput = form.querySelector('input[name="embroidery_quantity"]');
                    if (qtyInput) qtyInput.value = qtyMatch[1];
                }
            }
            
            console.log('DEBUG: Form data restored for:', instanceId);
        }
        
        function createFormInstance(instanceId, option) {
            console.log('DEBUG: Creating form instance for:', instanceId, option);
            
            // Get the original form template
            const originalForm = document.getElementById(option + '-form');
            if (!originalForm) {
                console.error('DEBUG: Original form not found:', option + '-form');
                return;
            }
            
            // Clone the form
            const formClone = originalForm.cloneNode(true);
            formClone.id = `${option}-form-${instanceId}`;
            formClone.style.display = 'block';
            
            // Add instance ID as data attribute
            formClone.dataset.instanceId = instanceId;
            
            // FIX DUPLICATE IDs in cloned form
            // Make all IDs unique by appending instanceId
            const allElements = formClone.querySelectorAll('[id]');
            console.log('DEBUG: Found', allElements.length, 'elements with IDs in cloned form');
            allElements.forEach(element => {
                const originalId = element.id;
                if (originalId && !originalId.includes(`-${instanceId}`)) {
                    console.log('DEBUG: Updating ID from', originalId, 'to', `${originalId}-${instanceId}`);
                    element.id = `${originalId}-${instanceId}`;
                    
                    // Also update 'for' attributes in labels
                    const labels = formClone.querySelectorAll(`label[for="${originalId}"]`);
                    labels.forEach(label => {
                        label.setAttribute('for', `${originalId}-${instanceId}`);
                    });
                }
            });
            
            // IMPORTANT: Reset all form inputs to default state
            // This ensures each new instance starts fresh
            const inputs = formClone.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.type === 'text' || input.type === 'number') {
                    input.value = '';
                } else if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0; // Reset to first option
                } else if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                }
            });
            
            // Reset quantity badges
            const quantityBadges = formClone.querySelectorAll('[id$="-total-quantity"]');
            quantityBadges.forEach(badge => {
                badge.textContent = '0';
            });
            
            // Reset image preview containers
            const previewContainers = formClone.querySelectorAll('[id$="-previews"], [id$="-files-list"]');
            previewContainers.forEach(container => {
                // Remove all image previews
                const previews = container.querySelectorAll('.col-md-4, .mb-2.p-2.border.rounded');
                previews.forEach(preview => preview.remove());
                
                // Add "No files" message if empty
                if (container.children.length === 0) {
                    const noFilesMessage = document.createElement('div');
                    noFilesMessage.className = 'text-muted small';
                    noFilesMessage.textContent = 'No files attached yet';
                    container.appendChild(noFilesMessage);
                }
            });
            
            // Hide image preview containers
            const parentContainers = formClone.querySelectorAll('[id$="-preview-container"], [id$="-files-container"]');
            parentContainers.forEach(container => {
                if (container.id !== 'lanyard-uploaded-files-container') {
                    container.classList.add('d-none');
                }
            });
            
            // Insert after the original form
            originalForm.parentNode.insertBefore(formClone, originalForm.nextSibling);
            
            // REINITIALIZE EVENT LISTENERS for the cloned form
            reinitializeFormEventListeners(formClone, option, instanceId);
            
            // Show the cloned form
            showIprintOptionForInstance(instanceId, option);
            
            console.log('DEBUG: Created form instance:', formClone.id);
        }
        
        // Show form for a specific instance
        function showIprintOptionForInstance(instanceId, option) {
            console.log('DEBUG: showIprintOptionForInstance called for:', instanceId, option);
            
            // IMPORTANT: Hide ALL form instances (not just same option)
            // This ensures only one form is visible at a time
            const allInstanceForms = document.querySelectorAll('[id*="-form-"]');
            allInstanceForms.forEach(form => {
                // Only hide if it's an instance form (contains "-form-" followed by ID)
                if (form.id.includes('-form-')) {
                    form.style.display = 'none';
                }
            });
            
            // Also hide all original forms
            document.querySelectorAll('.iprint-type-form').forEach(form => {
                form.style.display = 'none';
            });
            
            // Show the specific instance form
            const targetForm = document.getElementById(`${option}-form-${instanceId}`);
            if (targetForm) {
                targetForm.style.display = 'block';
                
                // Show parent container
                const parent = document.getElementById('iprint-details-container');
                if (parent) parent.style.display = 'block';
            } else {
                // Fallback to original form
                showIprintOption(option, true);
            }
        }
        
        function showIprintOption(option, skipAddToTable = false) {
            console.log('DEBUG: showIprintOption called with option:', option, 'skipAddToTable:', skipAddToTable);
            
            // Hide all iprint-type-form elements
            document.querySelectorAll('.iprint-type-form').forEach(form => {
                form.style.display = 'none';
            });
            
            // Also hide all instance forms
            document.querySelectorAll('[id*="-form-"]').forEach(form => {
                if (form.id.includes('-form-')) {
                    form.style.display = 'none';
                }
            });
            
            // Show the selected option form
            const targetForm = document.getElementById(option + '-form');
            if (targetForm) {
                targetForm.style.display = 'block';
                
                // Show parent container
                const parent = document.getElementById('iprint-details-container');
                if (parent) parent.style.display = 'block';
            }
            
            // Add to table unless skipping
            if (!skipAddToTable) {
                addPrintingTypeToTable(option);
            }
        }
        
        // Function to add printing type to the Order Items table
        function addPrintingTypeToTable(option) {
            console.log('DEBUG: addPrintingTypeToTable called with option:', option);
            
            const tableContainer = document.getElementById('selected-printing-types-container');
            if (!tableContainer) return;
            
            // Show the table container
            tableContainer.style.display = 'block';
            
            const tableBody = document.getElementById('printing-types-tbody');
            if (!tableBody) {
                console.error('DEBUG: Table body not found! Looking for: printing-types-tbody');
                return;
            }
            
            // Generate unique ID
            const instanceId = `${option}_${Date.now()}_${Math.floor(Math.random() * 1000)}`;
            
            // Get row number
            const rowNumber = tableBody.children.length + 1;
            
            // Add new row
            const newRow = document.createElement('tr');
            newRow.id = `printing-type-row-${instanceId}`;
            newRow.dataset.instanceId = instanceId;
            newRow.dataset.option = option;
            newRow.innerHTML = `
                <td>${rowNumber}</td>
                <td>${option.toUpperCase()}</td>
                <td>
                    <span class="badge bg-warning status-badge" id="status-${instanceId}">Pending</span>
                </td>
                <td id="details-${instanceId}">Click Edit to add details</td>
                <td>
                    <button type="button" class="btn btn-sm btn-warning" onclick="editPrintingType('${instanceId}', '${option}')">Edit</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removePrintingType('${instanceId}')">Remove</button>
                </td>
            `;
            
            tableBody.appendChild(newRow);
            console.log('DEBUG: Added item to table:', instanceId);
            
            // Show notification for successful add
            showNotification('Item added to order list', 'success');
        }
        
        // Simple edit function
        function editPrintingType(instanceId, option) {
            console.log('DEBUG: editPrintingType called for:', instanceId, option);
            
            // Check if this instance already has a form
            const existingForm = document.getElementById(`${option}-form-${instanceId}`);
            
            if (existingForm) {
                // Show existing form for this instance
                showIprintOptionForInstance(instanceId, option);
                
                // RESTORE FORM DATA from table details
                restoreFormDataFromTable(instanceId, option, existingForm);
            } else {
                // Create new form instance for this item
                createFormInstance(instanceId, option);
            }
            
            // Setup auto-update for this form
            setupAutoUpdate(instanceId, option);
        }
        
        // Remove function with confirmation
        function removePrintingType(instanceId) {
            console.log('DEBUG: removePrintingType called for:', instanceId);
            
            // Show professional confirmation modal
            showConfirmationModal(
                'Remove Item',
                'Are you sure you want to remove this item?',
                () => {
                    // User clicked Yes
                    const row = document.getElementById(`printing-type-row-${instanceId}`);
                    if (row) {
                        row.remove();
                    }
                    
                    // Also remove the form instance if it exists
                    const formInstance = document.querySelector(`[data-instance-id="${instanceId}"]`);
                    if (formInstance) {
                        formInstance.remove();
                    }
                    
                    // Remove any instance form
                    const instanceForms = document.querySelectorAll(`[id$="-form-${instanceId}"]`);
                    instanceForms.forEach(form => form.remove());
                    
                    // Update row numbers
                    updateRowNumbers();
                    
                    // Show success notification
                    showNotification('Item has been removed successfully', 'success');
                },
                () => {
                    // User clicked No
                    console.log('DEBUG: Remove cancelled by user');
                }
            );
        }
        
        // Update row numbers in the table
        function updateRowNumbers() {
            const tableBody = document.getElementById('printing-types-tbody');
            if (!tableBody) return;
            
            const rows = tableBody.querySelectorAll('tr');
            rows.forEach((row, index) => {
                // Update the first cell (row number)
                if (row.cells[0]) {
                    row.cells[0].textContent = index + 1;
                }
            });
        }
        
        // Confirmation Modal System
        function showConfirmationModal(title, message, onConfirm, onCancel) {
            // Remove existing modal if any
            const existingModal = document.getElementById('confirmation-modal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Create modal overlay
            const modalOverlay = document.createElement('div');
            modalOverlay.id = 'confirmation-modal';
            modalOverlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                animation: fadeIn 0.2s ease-out;
            `;
            
            // Create modal content
            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: white;
                border-radius: 0.75rem;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                width: 90%;
                max-width: 400px;
                animation: scaleIn 0.3s ease-out;
            `;
            
            // Modal header
            const modalHeader = document.createElement('div');
            modalHeader.style.cssText = `
                padding: 1.5rem 1.5rem 1rem;
                border-bottom: 1px solid #e5e7eb;
            `;
            
            const modalTitle = document.createElement('h3');
            modalTitle.style.cssText = `
                margin: 0;
                font-size: 1.25rem;
                font-weight: 600;
                color: #111827;
            `;
            modalTitle.textContent = title;
            
            // Modal body
            const modalBody = document.createElement('div');
            modalBody.style.cssText = `
                padding: 1.5rem;
            `;
            
            const modalMessage = document.createElement('p');
            modalMessage.style.cssText = `
                margin: 0;
                color: #6b7280;
                line-height: 1.5;
            `;
            modalMessage.textContent = message;
            
            // Modal footer
            const modalFooter = document.createElement('div');
            modalFooter.style.cssText = `
                padding: 1rem 1.5rem 1.5rem;
                border-top: 1px solid #e5e7eb;
                display: flex;
                justify-content: flex-end;
                gap: 0.75rem;
            `;
            
            // Cancel button
            const cancelButton = document.createElement('button');
            cancelButton.type = 'button';
            cancelButton.textContent = 'Cancel';
            cancelButton.style.cssText = `
                padding: 0.5rem 1rem;
                background: white;
                border: 1px solid #d1d5db;
                border-radius: 0.375rem;
                color: #374151;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
            `;
            cancelButton.onmouseover = () => cancelButton.style.backgroundColor = '#f9fafb';
            cancelButton.onmouseout = () => cancelButton.style.backgroundColor = 'white';
            cancelButton.onclick = () => {
                modalOverlay.remove();
                if (onCancel) onCancel();
            };
            
            // Confirm button
            const confirmButton = document.createElement('button');
            confirmButton.type = 'button';
            confirmButton.textContent = 'Yes, Remove';
            confirmButton.style.cssText = `
                padding: 0.5rem 1rem;
                background: #ef4444;
                border: 1px solid #ef4444;
                border-radius: 0.375rem;
                color: white;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
            `;
            confirmButton.onmouseover = () => confirmButton.style.backgroundColor = '#dc2626';
            confirmButton.onmouseout = () => confirmButton.style.backgroundColor = '#ef4444';
            confirmButton.onclick = () => {
                modalOverlay.remove();
                if (onConfirm) onConfirm();
            };
            
            // Assemble modal
            modalHeader.appendChild(modalTitle);
            modalBody.appendChild(modalMessage);
            modalFooter.appendChild(cancelButton);
            modalFooter.appendChild(confirmButton);
            
            modalContent.appendChild(modalHeader);
            modalContent.appendChild(modalBody);
            modalContent.appendChild(modalFooter);
            modalOverlay.appendChild(modalContent);
            
            // Add animations to style
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                
                @keyframes scaleIn {
                    from { transform: scale(0.9); opacity: 0; }
                    to { transform: scale(1); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
            
            // Add to document
            document.body.appendChild(modalOverlay);
            
            // Close on ESC key
            const handleKeyDown = (e) => {
                if (e.key === 'Escape') {
                    modalOverlay.remove();
                    document.removeEventListener('keydown', handleKeyDown);
                    if (onCancel) onCancel();
                }
            };
            document.addEventListener('keydown', handleKeyDown);
            
            // Close on overlay click
            modalOverlay.onclick = (e) => {
                if (e.target === modalOverlay) {
                    modalOverlay.remove();
                    document.removeEventListener('keydown', handleKeyDown);
                    if (onCancel) onCancel();
                }
            };
        }
        
        // Notification system
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification-toast');
            existingNotifications.forEach(notification => {
                notification.remove();
            });
            
            // Create notification
            const notification = document.createElement('div');
            notification.className = `notification-toast ${type}`;
            notification.innerHTML = `
                <div class="notification-icon">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-message">${message}</div>
                </div>
                <button class="notification-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            // Add to page
            document.body.appendChild(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }
        
        // Add CSS for notifications if not already present
        if (!document.querySelector('#notification-styles')) {
            const notificationStyles = document.createElement('style');
            notificationStyles.id = 'notification-styles';
            notificationStyles.textContent = `
                .notification-toast {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    padding: 1.5rem;
                    background: white;
                    border-radius: 0.75rem;
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                    border-left: 6px solid #3b82f6;
                    min-width: 350px;
                    max-width: 450px;
                    animation: fadeInScale 0.3s ease-out;
                }
                
                @keyframes fadeInScale {
                    from {
                        transform: translate(-50%, -50%) scale(0.9);
                        opacity: 0;
                    }
                    to {
                        transform: translate(-50%, -50%) scale(1);
                        opacity: 1;
                    }
                }
                
                .notification-toast.success {
                    border-left-color: #10b981;
                }
                
                .notification-toast.error {
                    border-left-color: #ef4444;
                }
                
                .notification-icon {
                    width: 32px;
                    height: 32px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 50%;
                }
                
                .notification-toast .notification-icon {
                    background: rgba(59, 130, 246, 0.1);
                    color: #3b82f6;
                }
                
                .notification-toast.success .notification-icon {
                    background: rgba(16, 185, 129, 0.1);
                    color: #10b981;
                }
                
                .notification-toast.error .notification-icon {
                    background: rgba(239, 68, 68, 0.1);
                    color: #ef4444;
                }
                
                .notification-content {
                    flex: 1;
                }
                
                .notification-message {
                    font-size: 0.9375rem;
                    color: #1e293b;
                    line-height: 1.4;
                }
                
                .notification-close {
                    background: none;
                    border: none;
                    color: #94a3b8;
                    cursor: pointer;
                    padding: 0.25rem;
                    border-radius: 0.25rem;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .notification-close:hover {
                    background: #f1f5f9;
                    color: #64748b;
                }
            `;
            document.head.appendChild(notificationStyles);
        }
        
        // Setup auto-update for form inputs
        function setupAutoUpdate(instanceId, option) {
            // Try to find instance form first, then fallback to original form
            let form = document.getElementById(`${option}-form-${instanceId}`);
            if (!form) {
                form = document.getElementById(option + '-form');
            }
            if (!form) return;
            
            console.log('DEBUG: Setting up auto-update for:', instanceId, option, 'form:', form.id);
            
            // Remove any existing event listeners from this form
            // We'll use event delegation instead of attaching to individual inputs
            
            // Create handler function first
            const handleFormUpdate = (event) => {
                // Only handle input, select, and textarea events
                if (event.target.matches('input, select, textarea')) {
                    console.log('DEBUG: Form input changed, updating details for:', instanceId, 'target:', event.target.name, 'value:', event.target.value);
                    // Debounce the update to avoid too many calls
                    clearTimeout(form._updateTimeout);
                    form._updateTimeout = setTimeout(() => {
                        updateTableDetails(instanceId, option);
                    }, 300);
                }
            };
            
            // Remove existing listeners if any
            if (form._updateHandler) {
                form.removeEventListener('input', form._updateHandler);
                form.removeEventListener('change', form._updateHandler);
            }
            
            // Store handler on form for later removal
            form._updateHandler = handleFormUpdate;
            
            // Add event listeners using event delegation
            form.addEventListener('input', handleFormUpdate);
            form.addEventListener('change', handleFormUpdate);
            
            // Also update immediately to show current state
            updateTableDetails(instanceId, option);
        }
        
        // Update table details based on form data
        function updateTableDetails(instanceId, option) {
            console.log('DEBUG: updateTableDetails called for:', instanceId, option, 'at:', new Date().toISOString(), 'NEW LOGIC');
            
            // Try to find instance form first, then fallback to original form
            let form = document.getElementById(`${option}-form-${instanceId}`);
            if (!form) {
                form = document.getElementById(option + '-form');
            }
            if (!form) return;
            
            const detailsElement = document.getElementById(`details-${instanceId}`);
            if (!detailsElement) return;
            
            // Collect form data
            const formData = {};
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                if (input.name && input.value) {
                    formData[input.name] = input.value;
                }
            });
            
            // Generate summary based on form data - show only what's missing
            let summary = '';
            let missingFields = [];
            
            if (option === 'dtf') {
                // Get print type from form
                const printTypeSelect = form.querySelector('select[name="dtf_print_type"]');
                const printType = printTypeSelect ? printTypeSelect.value : '';
                
                // Get all size selections (multiple rows possible)
                const sizeSelects = form.querySelectorAll('select[name="dtf_print_size[]"]');
                const sizes = Array.from(sizeSelects).map(select => select.value).filter(v => v);
                
                // Check what's missing
                if (!printType) missingFields.push('Print Type');
                if (sizes.length === 0) missingFields.push('Size');
                
                if (missingFields.length > 0) {
                    summary = `Missing: ${missingFields.join(', ')}`;
                } else {
                    summary = 'Complete';
                }
            } else if (option === 'lanyard') {
                // Get lanyard type from form
                const typeSelect = form.querySelector('select[name="lanyard_type"]');
                const type = typeSelect ? typeSelect.value : '';
                
                // Get quantity from form
                const quantityInput = form.querySelector('input[name="lanyard_quantity"]');
                const quantity = quantityInput ? quantityInput.value : '';
                
                // Check what's missing
                if (!type) missingFields.push('Type');
                if (!quantity) missingFields.push('Quantity');
                
                if (missingFields.length > 0) {
                    summary = `Missing: ${missingFields.join(', ')}`;
                } else {
                    summary = 'Complete';
                }
            } else if (option === 'tarpaulin') {
                // Get tarpaulin size from form
                const sizeSelect = form.querySelector('select[name="tarpaulin_size"]');
                const size = sizeSelect ? sizeSelect.value : '';
                
                // Get quantity from form
                const quantityInput = form.querySelector('input[name="tarpaulin_quantity"]');
                const quantity = quantityInput ? quantityInput.value : '';
                
                // Check what's missing
                if (!size) missingFields.push('Size');
                if (!quantity) missingFields.push('Quantity');
                
                if (missingFields.length > 0) {
                    summary = `Missing: ${missingFields.join(', ')}`;
                } else {
                    summary = 'Complete';
                }
            } else if (option === 'sublimation') {
                // Get sublimation type from form
                const typeSelect = form.querySelector('select[name="sublimation_type"]');
                const type = typeSelect ? typeSelect.value : '';
                
                // Get all size selections (multiple rows possible)
                const sizeSelects = form.querySelectorAll('select[name="sublimation_size[]"]');
                const sizes = Array.from(sizeSelects).map(select => select.value).filter(v => v);
                
                // Check what's missing
                if (!type) missingFields.push('Type');
                if (sizes.length === 0) missingFields.push('Size');
                
                if (missingFields.length > 0) {
                    summary = `Missing: ${missingFields.join(', ')}`;
                } else {
                    summary = 'Complete';
                }
            } else if (option === 'embroidery') {
                // Get embroidery type from form
                const typeSelect = form.querySelector('select[name="embroidery_type"]');
                const type = typeSelect ? typeSelect.value : '';
                
                // Get all size selections (multiple rows possible)
                const sizeSelects = form.querySelectorAll('select[name="embroidery_size[]"]');
                const sizes = Array.from(sizeSelects).map(select => select.value).filter(v => v);
                
                // Check what's missing
                if (!type) missingFields.push('Type');
                if (sizes.length === 0) missingFields.push('Size');
                
                if (missingFields.length > 0) {
                    summary = `Missing: ${missingFields.join(', ')}`;
                } else {
                    summary = 'Complete';
                }
            }
            
            // Update details element
            detailsElement.textContent = summary;
            
            // Update status badge
            const statusBadge = document.getElementById(`status-${instanceId}`);
            if (statusBadge) {
                if (summary.includes('Missing:')) {
                    statusBadge.textContent = 'Pending';
                    statusBadge.className = 'badge bg-warning status-badge';
                } else {
                    statusBadge.textContent = 'Complete';
                    statusBadge.className = 'badge bg-success status-badge';
                }
            }
            
            console.log('DEBUG: Updated details:', summary);
        }
    </script>
    
    <script>
        // Add event listeners for Add Another Print Size buttons
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DEBUG: DOM fully loaded');
            console.log('DEBUG: Form data store initialized');
            
            // Initialize image upload previews for all forms
            initializeImageUploadPreviews();
            
            // USE EVENT DELEGATION for all Add Size buttons
            // This works for both original forms and form instances
            document.addEventListener('click', function(event) {
                // Check if clicked element is an "Add Print Size" button
                if (event.target.matches('#add-print-size, [id^="add-print-size-"]')) {
                    // Find the container for this button
                    const button = event.target;
                    console.log('DEBUG: Add Print Size button clicked, ID:', button.id);
                    
                    // Extract instance ID from button ID - SIMPLIFIED LOGIC
                    let containerId = 'print-sizes-container';
                    if (button.id.includes('-')) {
                        // Get everything after "add-print-size-"
                        const instanceId = button.id.replace('add-print-size-', '');
                        if (instanceId) {
                            containerId = `print-sizes-container-${instanceId}`;
                        }
                    }
                    console.log('DEBUG: Looking for container:', containerId);
                    
                    // DEBUG: List all containers with similar IDs
                    const allContainers = document.querySelectorAll('[id^="print-sizes-container"]');
                    console.log('DEBUG: All print size containers found:', Array.from(allContainers).map(c => c.id));
                    
                    addPrintSizeRow(containerId, 'dtf_print_size[]', 'dtf_custom_size[]', 'dtf_print_size_quantity[]');
                }
                
                // Check if clicked element is an "Add Shirt Size" button
                if (event.target.matches('#add-shirt-size, [id^="add-shirt-size-"]')) {
                    // Find the container for this button
                    const button = event.target;
                    console.log('DEBUG: Add Shirt Size button clicked, ID:', button.id);
                    
                    // Extract instance ID from button ID - SIMPLIFIED LOGIC
                    let containerId = 'shirt-sizes-container';
                    if (button.id.includes('-')) {
                        // Get everything after "add-shirt-size-"
                        const instanceId = button.id.replace('add-shirt-size-', '');
                        if (instanceId) {
                            containerId = `shirt-sizes-container-${instanceId}`;
                        }
                    }
                    console.log('DEBUG: Looking for container:', containerId);
                    
                    // DEBUG: List all containers with similar IDs
                    const allContainers = document.querySelectorAll('[id^="shirt-sizes-container"]');
                    console.log('DEBUG: All shirt size containers found:', Array.from(allContainers).map(c => c.id));
                    
                    addShirtSizeRow(containerId, 'dtf_shirt_size[]', 'dtf_shirt_size_quantity[]');
                }
                
                // Check if clicked element is an "Add Embroidery Print Size" button
                if (event.target.matches('#add-embroidery-print-size, [id^="add-embroidery-print-size-"]')) {
                    // Find the container for this button
                    const button = event.target;
                    console.log('DEBUG: Add Embroidery Print Size button clicked, ID:', button.id);
                    
                    // Extract instance ID from button ID - SIMPLIFIED LOGIC
                    let containerId = 'embroidery-print-sizes-container';
                    if (button.id.includes('-')) {
                        // Get everything after "add-embroidery-print-size-"
                        const instanceId = button.id.replace('add-embroidery-print-size-', '');
                        if (instanceId) {
                            containerId = `embroidery-print-sizes-container-${instanceId}`;
                        }
                    }
                    console.log('DEBUG: Looking for container:', containerId);
                    
                    addPrintSizeRow(containerId, 'embroidery_print_size[]', 'embroidery_custom_size[]', 'embroidery_print_size_quantity[]');
                }
                
                // Check if clicked element is an "Add Embroidery Shirt Size" button
                if (event.target.matches('#add-embroidery-shirt-size, [id^="add-embroidery-shirt-size-"]')) {
                    // Find the container for this button
                    const button = event.target;
                    console.log('DEBUG: Add Embroidery Shirt Size button clicked, ID:', button.id);
                    
                    // Extract instance ID from button ID - SIMPLIFIED LOGIC
                    let containerId = 'embroidery-shirt-sizes-container';
                    if (button.id.includes('-')) {
                        // Get everything after "add-embroidery-shirt-size-"
                        const instanceId = button.id.replace('add-embroidery-shirt-size-', '');
                        if (instanceId) {
                            containerId = `embroidery-shirt-sizes-container-${instanceId}`;
                        }
                    }
                    console.log('DEBUG: Looking for container:', containerId);
                    
                    addShirtSizeRow(containerId, 'embroidery_shirt_size[]', 'embroidery_shirt_size_quantity[]');
                }
                
                // Check if clicked element is an "Add Sublimation Print Size" button
                if (event.target.matches('#add-sublimation-print-size, [id^="add-sublimation-print-size-"]')) {
                    // Find the container for this button
                    const button = event.target;
                    console.log('DEBUG: Add Sublimation Print Size button clicked, ID:', button.id);
                    
                    // Extract instance ID from button ID - SIMPLIFIED LOGIC
                    let containerId = 'sublimation-print-sizes-container';
                    if (button.id.includes('-')) {
                        // Get everything after "add-sublimation-print-size-"
                        const instanceId = button.id.replace('add-sublimation-print-size-', '');
                        if (instanceId) {
                            containerId = `sublimation-print-sizes-container-${instanceId}`;
                        }
                    }
                    console.log('DEBUG: Looking for container:', containerId);
                    
                    addPrintSizeRow(containerId, 'sublimation_print_size[]', 'sublimation_custom_size[]', 'sublimation_print_size_quantity[]');
                }
            });
            
            // Initialize quantity auto-compute
            initializeQuantityAutoCompute();
        });
        
        // Function to add a new print size row
        function addPrintSizeRow(containerId, sizeSelectName, customInputName, quantityInputName) {
            console.log('DEBUG: addPrintSizeRow called with containerId:', containerId);
            const container = document.getElementById(containerId);
            if (!container) {
                console.log('DEBUG: Container not found:', containerId);
                return;
            }
            
            // Get current row count
            const currentRows = container.querySelectorAll('.print-size-row');
            const rowNumber = currentRows.length + 1;
            
            // Create new row
            const newRow = document.createElement('tr');
            newRow.className = 'print-size-row';
            newRow.innerHTML = `
                <td class="align-middle text-center">${rowNumber}</td>
                <td>
                    <select class="form-select form-select-sm print-size-select" name="${sizeSelectName}" required>
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
                    <input type="text" class="form-control form-control-sm custom-size-input" name="${customInputName}" placeholder="e.g., 18x24 inches" style="display: none;">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm print-size-quantity" name="${quantityInputName}" min="1" value="1" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger delete-print-size-btn" onclick="deletePrintSizeRow(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            // Add to container
            container.appendChild(newRow);
            console.log('DEBUG: Added new print size row #', rowNumber);
            
            // Enable delete buttons on all rows (including first one)
            const deleteButtons = container.querySelectorAll('.delete-print-size-btn');
            deleteButtons.forEach(btn => {
                btn.disabled = false;
                btn.title = 'Delete this size';
            });
            
            // Add event listener for custom size toggle
            const sizeSelect = newRow.querySelector('.print-size-select');
            const customInput = newRow.querySelector('.custom-size-input');
            
            if (sizeSelect && customInput) {
                sizeSelect.addEventListener('change', function() {
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
            
            // Add event listener for quantity change to update total
            const quantityInput = newRow.querySelector('.print-size-quantity');
            if (quantityInput) {
                quantityInput.addEventListener('input', function() {
                    calculateTotalQuantity(containerId);
                });
                quantityInput.addEventListener('change', function() {
                    calculateTotalQuantity(containerId);
                });
            }
        }
        
        // Function to add a new shirt size row (3 columns: #, Size, Quantity, Actions)
        function addShirtSizeRow(containerId, sizeSelectName, quantityInputName) {
            console.log('DEBUG: addShirtSizeRow called for container:', containerId);
            
            const container = document.getElementById(containerId);
            if (!container) {
                console.error('DEBUG: Container not found:', containerId);
                return;
            }
            
            // Get current row count
            const currentRows = container.querySelectorAll('.shirt-size-row');
            const rowNumber = currentRows.length + 1;
            
            // Create new row (3 columns: #, Size, Quantity, Actions)
            const newRow = document.createElement('tr');
            newRow.className = 'shirt-size-row';
            newRow.innerHTML = `
                <td class="align-middle text-center">${rowNumber}</td>
                <td>
                    <select class="form-select form-select-sm shirt-size-select" name="${sizeSelectName}" required>
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
                    <input type="number" class="form-control form-control-sm shirt-size-quantity" name="${quantityInputName}" min="1" value="1" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger delete-shirt-size-btn" onclick="deleteShirtSizeRow(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            // Add to container
            container.appendChild(newRow);
            console.log('DEBUG: Added new shirt size row #', rowNumber);
            
            // Enable delete buttons on all rows (including first one)
            const deleteButtons = container.querySelectorAll('.delete-shirt-size-btn');
            deleteButtons.forEach(btn => {
                btn.disabled = false;
                btn.title = 'Delete this size';
            });
            
            // Add event listener for quantity change to update total
            const quantityInput = newRow.querySelector('.shirt-size-quantity');
            if (quantityInput) {
                quantityInput.addEventListener('input', function() {
                    calculateTotalQuantity(containerId);
                });
                quantityInput.addEventListener('change', function() {
                    calculateTotalQuantity(containerId);
                });
            }
        }
        
        // Function to delete a shirt size row
        function deleteShirtSizeRow(button) {
            console.log('DEBUG: deleteShirtSizeRow called');
            
            const row = button.closest('.shirt-size-row');
            if (!row) return;
            
            const container = row.closest('tbody');
            if (!container) return;
            
            // Don't delete if it's the only row
            const allRows = container.querySelectorAll('.shirt-size-row');
            if (allRows.length <= 1) {
                console.log('DEBUG: Cannot delete the only row');
                return;
            }
            
            // Remove the row
            row.remove();
            console.log('DEBUG: Deleted shirt size row');
            
            // Update row numbers
            updateShirtSizeRowNumbers(container);
            
            // If only one row left, disable its delete button
            const remainingRows = container.querySelectorAll('.shirt-size-row');
            if (remainingRows.length === 1) {
                const deleteBtn = remainingRows[0].querySelector('.delete-shirt-size-btn');
                if (deleteBtn) {
                    deleteBtn.disabled = true;
                    deleteBtn.title = 'Cannot delete the only size';
                }
            }
        }
        
        // Update row numbers in shirt size table
        function updateShirtSizeRowNumbers(container) {
            const rows = container.querySelectorAll('.shirt-size-row');
            rows.forEach((row, index) => {
                const numberCell = row.querySelector('td:first-child');
                if (numberCell) {
                    numberCell.textContent = index + 1;
                }
            });
        }
        
        // Function to delete a print size row
        function deletePrintSizeRow(button) {
            console.log('DEBUG: deletePrintSizeRow called');
            
            const row = button.closest('.print-size-row');
            if (!row) return;
            
            const container = row.closest('tbody');
            if (!container) return;
            
            // Don't delete if it's the only row
            const allRows = container.querySelectorAll('.print-size-row');
            if (allRows.length <= 1) {
                console.log('DEBUG: Cannot delete the only row');
                return;
            }
            
            // Remove the row
            row.remove();
            console.log('DEBUG: Deleted print size row');
            
            // Update row numbers
            updatePrintSizeRowNumbers(container);
            
            // If only one row left, disable its delete button
            const remainingRows = container.querySelectorAll('.print-size-row');
            if (remainingRows.length === 1) {
                const deleteBtn = remainingRows[0].querySelector('.delete-print-size-btn');
                if (deleteBtn) {
                    deleteBtn.disabled = true;
                    deleteBtn.title = 'Cannot delete the only print size';
                }
            }
        }
        
        // Update row numbers in print size table
        function updatePrintSizeRowNumbers(container) {
            const rows = container.querySelectorAll('.print-size-row');
            rows.forEach((row, index) => {
                const numberCell = row.querySelector('td:first-child');
                if (numberCell) {
                    numberCell.textContent = index + 1;
                }
            });
        }
        
        // Function to calculate total quantity for DTF, Sublimation, Embroidery forms
        function calculateTotalQuantity(containerId) {
            console.log('DEBUG: calculateTotalQuantity called for:', containerId);
            
            let total = 0;
            
            // Get all quantity inputs in the container
            const container = document.getElementById(containerId);
            if (!container) return 0;
            
            // Check if it's a print size container or shirt size container
            const isPrintSize = containerId.includes('print-sizes');
            const isShirtSize = containerId.includes('shirt-sizes');
            
            if (isPrintSize) {
                // For print size containers
                const quantityInputs = container.querySelectorAll('.print-size-quantity');
                quantityInputs.forEach(input => {
                    const value = parseInt(input.value) || 0;
                    total += value;
                });
                
                // Update the corresponding total display
                // Find the total quantity badge within the same form/container
                // Based on container type, look for specific badge
                const form = container.closest('[id*="-form"]');
                if (form) {
                    let badgeSelector = '';
                    
                    if (containerId.includes('print-sizes-container')) {
                        // For print sizes, look for badge with id containing "print-total-quantity"
                        badgeSelector = '[id*="print-total-quantity"]';
                    } else if (containerId.includes('sublimation-print-sizes-container')) {
                        // For sublimation print sizes
                        badgeSelector = '[id*="sublimation-total-quantity"]';
                    } else if (containerId.includes('embroidery-print-sizes-container')) {
                        // For embroidery print sizes
                        badgeSelector = '[id*="embroidery-print-total-quantity"]';
                    }
                    
                    if (badgeSelector) {
                        const totalBadge = form.querySelector(badgeSelector);
                        if (totalBadge) {
                            totalBadge.textContent = total;
                        }
                    }
                }
            } else if (isShirtSize) {
                // For shirt size containers
                const quantityInputs = container.querySelectorAll('.shirt-size-quantity');
                quantityInputs.forEach(input => {
                    const value = parseInt(input.value) || 0;
                    total += value;
                });
                
                // Update the corresponding total display
                // Find the total quantity badge within the same form/container
                // Based on container type, look for specific badge
                const form = container.closest('[id*="-form"]');
                if (form) {
                    let badgeSelector = '';
                    
                    if (containerId.includes('shirt-sizes-container')) {
                        // For shirt sizes, look for badge with id containing "shirt-total-quantity"
                        badgeSelector = '[id*="shirt-total-quantity"]';
                    } else if (containerId.includes('embroidery-shirt-sizes-container')) {
                        // For embroidery shirt sizes
                        badgeSelector = '[id*="embroidery-shirt-total-quantity"]';
                    }
                    
                    if (badgeSelector) {
                        const totalBadge = form.querySelector(badgeSelector);
                        if (totalBadge) {
                            totalBadge.textContent = total;
                    }
                }
            }
            
            console.log('DEBUG: Total quantity calculated:', total);
            return total;
        }
        
        // Function to update simple total quantity for Lanyard, Tarpaulin forms
        function updateSimpleTotalQuantity(inputId, displayId) {
            console.log('DEBUG: updateSimpleTotalQuantity called for input:', inputId);
            
            const input = document.getElementById(inputId);
            const display = document.getElementById(displayId);
            
            if (!input || !display) return;
            
            const value = parseInt(input.value) || 0;
            display.textContent = value;
            
            console.log('DEBUG: Updated simple total:', value);
        }
        
        // Function to initialize quantity auto-compute for all forms
        function initializeQuantityAutoCompute() {
            console.log('DEBUG: Initializing quantity auto-compute');
            
            // DTF form quantity listeners
            const dtfPrintQuantityInputs = document.querySelectorAll('#print-sizes-container .print-size-quantity');
            dtfPrintQuantityInputs.forEach(input => {
                input.addEventListener('input', () => calculateTotalQuantity('print-sizes-container'));
                input.addEventListener('change', () => calculateTotalQuantity('print-sizes-container'));
            });
            
            const dtfShirtQuantityInputs = document.querySelectorAll('#shirt-sizes-container .shirt-size-quantity');
            dtfShirtQuantityInputs.forEach(input => {
                input.addEventListener('input', () => calculateTotalQuantity('shirt-sizes-container'));
                input.addEventListener('change', () => calculateTotalQuantity('shirt-sizes-container'));
            });
            
            // Lanyard form quantity listener
            const lanyardQuantityInput = document.querySelector('input[name="lanyard_quantity"]');
            if (lanyardQuantityInput) {
                lanyardQuantityInput.addEventListener('input', () => updateSimpleTotalQuantity('lanyard-quantity-input', 'lanyard-total-quantity'));
                lanyardQuantityInput.addEventListener('change', () => updateSimpleTotalQuantity('lanyard-quantity-input', 'lanyard-total-quantity'));
            }
            
            // Tarpaulin form quantity listener
            const tarpaulinQuantityInput = document.querySelector('input[name="tarpaulin_quantity"]');
            if (tarpaulinQuantityInput) {
                tarpaulinQuantityInput.addEventListener('input', () => updateSimpleTotalQuantity('tarpaulin-quantity-input', 'tarpaulin-total-quantity'));
                tarpaulinQuantityInput.addEventListener('change', () => updateSimpleTotalQuantity('tarpaulin-quantity-input', 'tarpaulin-total-quantity'));
            }
            
            // Sublimation form quantity listeners
            const sublimationQuantityInputs = document.querySelectorAll('#sublimation-print-sizes-container .print-size-quantity');
            sublimationQuantityInputs.forEach(input => {
                input.addEventListener('input', () => calculateTotalQuantity('sublimation-print-sizes-container'));
                input.addEventListener('change', () => calculateTotalQuantity('sublimation-print-sizes-container'));
            });
            
            // Sublimation simple quantity listener
            const sublimationSimpleQuantityInput = document.querySelector('input[name="sublimation_quantity"]');
            if (sublimationSimpleQuantityInput) {
                sublimationSimpleQuantityInput.addEventListener('input', () => {
                    const value = parseInt(sublimationSimpleQuantityInput.value) || 0;
                    document.getElementById('sublimation-total-quantity').textContent = value;
                });
                sublimationSimpleQuantityInput.addEventListener('change', () => {
                    const value = parseInt(sublimationSimpleQuantityInput.value) || 0;
                    document.getElementById('sublimation-total-quantity').textContent = value;
                });
            }
            
            // Embroidery form quantity listeners
            const embroideryPrintQuantityInputs = document.querySelectorAll('#embroidery-print-sizes-container .print-size-quantity');
            embroideryPrintQuantityInputs.forEach(input => {
                input.addEventListener('input', () => calculateTotalQuantity('embroidery-print-sizes-container'));
                input.addEventListener('change', () => calculateTotalQuantity('embroidery-print-sizes-container'));
            });
            
            const embroideryShirtQuantityInputs = document.querySelectorAll('#embroidery-shirt-sizes-container .shirt-size-quantity');
            embroideryShirtQuantityInputs.forEach(input => {
                input.addEventListener('input', () => calculateTotalQuantity('embroidery-shirt-sizes-container'));
                input.addEventListener('change', () => calculateTotalQuantity('embroidery-shirt-sizes-container'));
            });
            
            console.log('DEBUG: Quantity auto-compute initialized');
        }
        
        // Function to initialize image upload previews for all forms
        function initializeImageUploadPreviews() {
            console.log('DEBUG: Initializing image upload previews');
            
            // Check if event listener is already attached
            if (window.imageUploadPreviewInitialized) {
                console.log('DEBUG: Image upload preview already initialized');
                return;
            }
            
            // Mark as initialized
            window.imageUploadPreviewInitialized = true;
            
            // Use event delegation on the document level
            document.addEventListener('change', function(event) {
                const target = event.target;
                const targetId = target.id;
                
                // Check if this is one of our image upload inputs
                if (!targetId || !targetId.includes('image-upload')) {
                    return;
                }
                
                console.log('DEBUG: Image upload changed via delegation:', targetId, 'Files:', target.files?.length || 0);
                
                // Debounce to prevent multiple calls
                if (window.imageUploadDebounce) {
                    clearTimeout(window.imageUploadDebounce);
                }
                
                window.imageUploadDebounce = setTimeout(() => {
                    console.log('DEBUG: Processing file upload via delegation for:', targetId);
                    
                    let previewContainerId = '';
                    let containerToShowId = '';
                    let isGridLayout = true;
                    
                    // Handle DTF image upload (original and instances)
                    if (targetId === 'dtf-image-upload' || targetId.startsWith('dtf-image-upload-')) {
                        // For instances, extract the instanceId
                        let instanceId = '';
                        if (targetId.includes('-upload-')) {
                            instanceId = targetId.split('-upload-')[1];
                        }
                        
                        if (instanceId) {
                            // Instance form: use unique container IDs
                            previewContainerId = `image-previews-${instanceId}`;
                            containerToShowId = `image-preview-container-${instanceId}`;
                        } else {
                            // Original form
                            previewContainerId = 'image-previews';
                            containerToShowId = 'image-preview-container';
                        }
                        isGridLayout = true;
                        
                    } else if (targetId === 'lanyard-image-upload' || targetId.startsWith('lanyard-image-upload-')) {
                        let instanceId = '';
                        if (targetId.includes('-upload-')) {
                            instanceId = targetId.split('-upload-')[1];
                        }
                        
                        if (instanceId) {
                            previewContainerId = `lanyard-uploaded-files-list-${instanceId}`;
                        } else {
                            previewContainerId = 'lanyard-uploaded-files-list';
                        }
                        containerToShowId = null;
                        isGridLayout = false;
                        
                    } else if (targetId === 'tarpaulin-image-upload' || targetId.startsWith('tarpaulin-image-upload-')) {
                        let instanceId = '';
                        if (targetId.includes('-upload-')) {
                            instanceId = targetId.split('-upload-')[1];
                        }
                        
                        if (instanceId) {
                            previewContainerId = `tarpaulin-image-previews-${instanceId}`;
                            containerToShowId = `tarpaulin-image-preview-container-${instanceId}`;
                        } else {
                            previewContainerId = 'tarpaulin-image-previews';
                            containerToShowId = 'tarpaulin-image-preview-container';
                        }
                        isGridLayout = true;
                        
                    } else if (targetId === 'sublimation-image-upload' || targetId.startsWith('sublimation-image-upload-')) {
                        let instanceId = '';
                        if (targetId.includes('-upload-')) {
                            instanceId = targetId.split('-upload-')[1];
                        }
                        
                        if (instanceId) {
                            previewContainerId = `sublimation-image-previews-${instanceId}`;
                            containerToShowId = `sublimation-image-preview-container-${instanceId}`;
                        } else {
                            previewContainerId = 'sublimation-image-previews';
                            containerToShowId = 'sublimation-image-preview-container';
                        }
                        isGridLayout = true;
                        
                    } else if (targetId === 'embroidery-image-upload' || targetId.startsWith('embroidery-image-upload-')) {
                        let instanceId = '';
                        if (targetId.includes('-upload-')) {
                            instanceId = targetId.split('-upload-')[1];
                        }
                        
                        if (instanceId) {
                            previewContainerId = `embroidery-image-previews-${instanceId}`;
                            containerToShowId = `embroidery-image-preview-container-${instanceId}`;
                        } else {
                            previewContainerId = 'embroidery-image-previews';
                            containerToShowId = 'embroidery-image-preview-container';
                        }
                        isGridLayout = true;
                    }
                    
                    if (previewContainerId) {
                        handleImageUpload(event, previewContainerId, containerToShowId, isGridLayout);
                    }
                }, 100);
            });
            
            console.log('DEBUG: Image upload previews initialized via event delegation');
        }
        
        // Create preview container for embroidery if it doesn't exist
        function createEmbroideryPreviewContainer() {
            console.log('DEBUG: Creating embroidery preview container');
            
            // Find the embroidery form section
            const embroideryForm = document.querySelector('#embroidery-form');
            if (!embroideryForm) return;
            
            // Check if container already exists
            if (document.getElementById('embroidery-image-preview-container')) {
                console.log('DEBUG: Embroidery preview container already exists');
                return;
            }
            
            // Create preview container
            const previewContainer = document.createElement('div');
            previewContainer.id = 'embroidery-image-preview-container';
            previewContainer.className = 'd-none mt-3';
            previewContainer.innerHTML = `
                <h6 class="mb-2">Uploaded Images:</h6>
                <div id="embroidery-image-previews" class="row g-2"></div>
            `;
            
            // Insert after the upload tabs
            const uploadTabs = embroideryForm.querySelector('.tab-content');
            if (uploadTabs) {
                uploadTabs.parentNode.insertBefore(previewContainer, uploadTabs.nextSibling);
                console.log('DEBUG: Embroidery preview container created');
            }
        }
        
        // Handle image upload and display preview
        function handleImageUpload(event, previewContainerId, containerToShowId, isGridLayout = true) {
            const files = event.target.files;
            if (!files || files.length === 0) return;
            
            // Track how many times this function is called
            if (!window.handleImageUploadCount) {
                window.handleImageUploadCount = 0;
            }
            window.handleImageUploadCount++;
            
            console.log('DEBUG: Files selected:', files.length, 'Grid layout:', isGridLayout, 'Call #:', window.handleImageUploadCount);
            
            // Show the preview container
            if (containerToShowId) {
                const container = document.getElementById(containerToShowId);
                if (container) {
                    container.classList.remove('d-none');
                }
            }
            
            // Get the preview container
            const previewContainer = document.getElementById(previewContainerId);
            if (!previewContainer) {
                console.error('DEBUG: Preview container not found:', previewContainerId);
                return;
            }
            
            // Clear "No files attached yet" message if present
            const noFilesMessage = previewContainer.querySelector('.text-muted.small');
            if (noFilesMessage && noFilesMessage.textContent.includes('No files attached')) {
                noFilesMessage.remove();
            }
            
            // Clear existing previews to avoid duplicates
            // Only clear if this is the first call or if we're replacing files
            const existingPreviews = previewContainer.querySelectorAll('.col-md-4, .mb-2.p-2.border.rounded');
            if (existingPreviews.length > 0) {
                console.log('DEBUG: Clearing', existingPreviews.length, 'existing previews to avoid duplicates');
                existingPreviews.forEach(preview => preview.remove());
            }
            
            // Process each file
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                if (file.type.startsWith('image/')) {
                    // For image files, create preview with image
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        if (isGridLayout) {
                            // Grid layout (DTF, Tarpaulin, Sublimation, Embroidery)
                            const previewCard = document.createElement('div');
                            previewCard.className = 'col-md-4 col-sm-6 mb-3';
                            previewCard.innerHTML = `
                                <div class="card h-100">
                                    <div class="card-body p-2 text-center">
                                        <div class="position-relative">
                                            <img src="${e.target.result}" class="img-fluid rounded" style="max-height: 150px; object-fit: contain;" alt="${file.name}">
                                            <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-1" onclick="removeImagePreview(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted d-block text-truncate" title="${file.name}">${file.name}</small>
                                            <small class="text-muted">${formatFileSize(file.size)}</small>
                                        </div>
                                    </div>
                                </div>
                            `;
                            previewContainer.appendChild(previewCard);
                        } else {
                            // List layout (Lanyard)
                            const fileItem = document.createElement('div');
                            fileItem.className = 'mb-2 p-2 border rounded';
                            fileItem.innerHTML = `
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <img src="${e.target.result}" class="rounded" style="width: 60px; height: 60px; object-fit: cover;" alt="${file.name}">
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <small class="fw-semibold text-truncate" style="max-width: 200px;" title="${file.name}">${file.name}</small>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeImagePreview(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">${formatFileSize(file.size)}</small>
                                    </div>
                                </div>
                            `;
                            previewContainer.appendChild(fileItem);
                        }
                        console.log('DEBUG: Added image preview for:', file.name);
                    };
                    
                    reader.readAsDataURL(file);
                } else {
                    // For non-image files (PDF, PSD, etc.)
                    if (isGridLayout) {
                        // Grid layout
                        const previewCard = document.createElement('div');
                        previewCard.className = 'col-md-4 col-sm-6 mb-3';
                        previewCard.innerHTML = `
                            <div class="card h-100">
                                <div class="card-body p-2 text-center">
                                    <div class="position-relative">
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                            <i class="fas fa-file fa-3x text-secondary"></i>
                                            <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-1" onclick="removeImagePreview(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted d-block text-truncate" title="${file.name}">${file.name}</small>
                                        <small class="text-muted">${formatFileSize(file.size)}</small>
                                        <small class="badge bg-info mt-1">${getFileExtension(file.name)}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                        previewContainer.appendChild(previewCard);
                    } else {
                        // List layout
                        const fileItem = document.createElement('div');
                        fileItem.className = 'mb-2 p-2 border rounded';
                        fileItem.innerHTML = `
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fas fa-file fa-2x text-secondary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <small class="fw-semibold text-truncate" style="max-width: 200px;" title="${file.name}">${file.name}</small>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeImagePreview(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">${formatFileSize(file.size)}</small>
                                    <small class="badge bg-info">${getFileExtension(file.name)}</small>
                                </div>
                            </div>
                        `;
                        previewContainer.appendChild(fileItem);
                    }
                    console.log('DEBUG: Added file preview for:', file.name);
                }
            }
        }
        
        // Remove image preview with confirmation
        function removeImagePreview(button) {
            // Show professional confirmation modal
            showConfirmationModal(
                'Remove Image',
                'Are you sure you want to remove this image?',
                () => {
                    // User clicked Yes
                    // Try to find the preview item (could be .col-md-4 for grid or .mb-2 for list)
                    const previewItem = button.closest('.col-md-4, .mb-2.p-2.border.rounded');
                    if (previewItem) {
                        previewItem.remove();
                        console.log('DEBUG: Removed image preview');
                        
                        // Check if any previews left
                        const previewContainer = button.closest('#image-previews, #lanyard-uploaded-files-list, #tarpaulin-image-previews, #sublimation-image-previews, #embroidery-image-previews');
                        if (previewContainer && previewContainer.children.length === 0) {
                            // Show "No files" message
                            const noFilesMessage = document.createElement('div');
                            noFilesMessage.className = 'text-muted small';
                            noFilesMessage.textContent = 'No files attached yet';
                            previewContainer.appendChild(noFilesMessage);
                            
                            // Hide the parent container if it exists
                            const parentContainer = previewContainer.closest('[id$="-preview-container"], [id$="-files-container"]');
                            if (parentContainer && parentContainer.id !== 'lanyard-uploaded-files-container') {
                                parentContainer.classList.add('d-none');
                            }
                        }
                    }
                },
                () => {
                    // User clicked No
                    console.log('DEBUG: Image removal cancelled by user');
                }
            );
        }
        
        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Get file extension
        function getFileExtension(filename) {
            return filename.split('.').pop().toUpperCase();
        }
    </script>
@endpush