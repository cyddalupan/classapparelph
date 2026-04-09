@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-md-11 col-lg-10">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Inventory Management</h1>
                    <p class="text-muted mb-0">Browse, create, and manage inventory items</p>
                </div>
                <div class="d-flex">
                    <button class="btn btn-primary me-2" id="refresh-inventory-btn">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                    <button class="btn btn-success" id="export-inventory-btn">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <ul class="nav nav-tabs card-header-tabs" id="inventoryTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="browse-tab" data-bs-toggle="tab" data-bs-target="#browse" type="button" role="tab">
                                <i class="fas fa-list me-1"></i> Browse
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="create-tab" data-bs-toggle="tab" data-bs-target="#create" type="button" role="tab">
                                <i class="fas fa-plus-circle me-1"></i> Create
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button" role="tab">
                                <i class="fas fa-edit me-1"></i> Edit
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button" role="tab">
                                <i class="fas fa-chart-bar me-1"></i> Reports
                            </button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <!-- Tab Content -->
                    <div class="tab-content" id="inventoryTabContent">
                        
                        <!-- BROWSE TAB -->
                        <div class="tab-pane fade show active" id="browse" role="tabpanel">
                            <!-- Category Selection -->
                            <div class="mb-5">
                                <h5 class="mb-3">
                                    <i class="fas fa-layer-group text-primary me-2"></i>
                                    Select Category
                                </h5>
                                <p class="text-muted mb-4">Click on a category to view its inventory items</p>
                                
                                <!-- 5 Category Boxes -->
                                <div class="row g-4">
                                    <!-- Shirt Products -->
                                    <div class="col-12 col-sm-6 col-lg">
                                        <div class="category-box card h-100 border shadow-sm" id="box-shirt" data-category="Shirt Products">
                                            <div class="card-body text-center">
                                                <div class="category-icon mb-2">
                                                    <i class="fas fa-tshirt fa-2x text-primary"></i>
                                                </div>
                                                <h6 class="card-title mb-1">Shirt Products</h6>
                                                <p class="card-text text-muted small mb-0">T-shirts, polo shirts, hoodies</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pants Products -->
                                    <div class="col-12 col-sm-6 col-lg">
                                        <div class="category-box card h-100 border shadow-sm" id="box-pants" data-category="Pants Products">
                                            <div class="card-body text-center">
                                                <div class="category-icon mb-2">
                                                    <i class="fas fa-tshirt fa-2x text-primary"></i>
                                                </div>
                                                <h6 class="card-title mb-1">Pants Products</h6>
                                                <p class="card-text text-muted small mb-0">Jeans, trousers, shorts</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Shoes Products -->
                                    <div class="col-12 col-sm-6 col-lg">
                                        <div class="category-box card h-100 border shadow-sm" id="box-shoes" data-category="Shoes Products">
                                            <div class="card-body text-center">
                                                <div class="category-icon mb-2">
                                                    <i class="fas fa-shoe-prints fa-2x text-primary"></i>
                                                </div>
                                                <h6 class="card-title mb-1">Shoes Products</h6>
                                                <p class="card-text text-muted small mb-0">Sneakers, formal shoes</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Accessories Products -->
                                    <div class="col-12 col-sm-6 col-lg">
                                        <div class="category-box card h-100 border shadow-sm" id="box-accessories" data-category="Accessories Products">
                                            <div class="card-body text-center">
                                                <div class="category-icon mb-2">
                                                    <i class="fas fa-glasses fa-2x text-primary"></i>
                                                </div>
                                                <h6 class="card-title mb-1">Accessories Products</h6>
                                                <p class="card-text text-muted small mb-0">Belts, watches, bags</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Others Products -->
                                    <div class="col-12 col-sm-6 col-lg">
                                        <div class="category-box card h-100 border shadow-sm" id="box-others" data-category="Others Products">
                                            <div class="card-body text-center">
                                                <div class="category-icon mb-2">
                                                    <i class="fas fa-box fa-2x text-primary"></i>
                                                </div>
                                                <h6 class="card-title mb-1">Others Products</h6>
                                                <p class="card-text text-muted small mb-0">Miscellaneous items</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Inventory Table (Initially Hidden) -->
                            <div id="inventory-table-area" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5>
                                        <i class="fas fa-table me-2"></i>
                                        <span id="table-category-title">Inventory Items</span>
                                    </h5>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-2" id="add-item-from-browse">
                                            <i class="fas fa-plus me-1"></i> Add Item
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" id="print-inventory">
                                            <i class="fas fa-print me-1"></i> Print
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped" id="inventory-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>SKU</th>
                                                <th>Name</th>
                                                <th>Category</th>
                                                <th>Type</th>
                                                <th>Price</th>
                                                <th>Stock</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="inventory-table-body">
                                            <!-- Will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="text-center mt-4" id="no-inventory-items" style="display: none;">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5>No Inventory Items</h5>
                                    <p class="text-muted">No items found in this category</p>
                                    <button class="btn btn-primary" id="add-first-item">
                                        <i class="fas fa-plus me-1"></i> Add First Item
                                    </button>
                                </div>
                                
                                <div class="text-center mt-4" id="loading-inventory" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading inventory items...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- CREATE TAB -->
                        <div class="tab-pane fade" id="create" role="tabpanel">
                            <h5 class="mb-4">
                                <i class="fas fa-plus-circle text-success me-2"></i>
                                Create New Inventory Item
                            </h5>
                            
                            <!-- Category Selection for Create -->
                            <div class="mb-4">
                                <label class="form-label">Select Category</label>
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6 col-lg">
                                        <div class="category-select-box card border" data-category="Shirt Products">
                                            <div class="card-body text-center py-3">
                                                <i class="fas fa-tshirt fa-2x text-primary mb-2"></i>
                                                <h6 class="card-title mb-0">Shirt Products</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-lg">
                                        <div class="category-select-box card border" data-category="Pants Products">
                                            <div class="card-body text-center py-3">
                                                <i class="fas fa-tshirt fa-2x text-primary mb-2"></i>
                                                <h6 class="card-title mb-0">Pants Products</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-lg">
                                        <div class="category-select-box card border" data-category="Shoes Products">
                                            <div class="card-body text-center py-3">
                                                <i class="fas fa-shoe-prints fa-2x text-primary mb-2"></i>
                                                <h6 class="card-title mb-0">Shoes Products</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-lg">
                                        <div class="category-select-box card border" data-category="Accessories Products">
                                            <div class="card-body text-center py-3">
                                                <i class="fas fa-glasses fa-2x text-primary mb-2"></i>
                                                <h6 class="card-title mb-0">Accessories Products</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-lg">
                                        <div class="category-select-box card border" data-category="Others Products">
                                            <div class="card-body text-center py-3">
                                                <i class="fas fa-box fa-2x text-primary mb-2"></i>
                                                <h6 class="card-title mb-0">Others Products</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Create Form (Initially Hidden) -->
                            <div id="create-form-area" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Creating item for: <strong id="selected-category-name"></strong>
                                </div>
                                
                                <!-- Shirt Product Form -->
                                <form id="shirt-product-form" style="display: none;">
                                    @csrf
                                    <input type="hidden" name="category" value="Shirt Products">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">SKU *</label>
                                            <input type="text" class="form-control" name="sku" required maxlength="50">
                                            <div class="form-text">Unique stock keeping unit</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Brand *</label>
                                            <input type="text" class="form-control" name="brand" required maxlength="100">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Type *</label>
                                            <input type="text" class="form-control" name="type" required maxlength="50" placeholder="e.g., T-shirt, Polo, Hoodie">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Color *</label>
                                            <input type="text" class="form-control" name="color" required maxlength="50">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Size *</label>
                                            <input type="text" class="form-control" name="size" required maxlength="10" placeholder="e.g., M, L, XL">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Price (PHP) *</label>
                                            <input type="number" class="form-control" name="price" required min="0" step="0.01">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Stock Quantity *</label>
                                            <input type="number" class="form-control" name="stock" required min="0">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Supplier</label>
                                            <input type="text" class="form-control" name="supplier" maxlength="100">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Shop/Location</label>
                                            <input type="text" class="form-control" name="shop" maxlength="100" placeholder="e.g., Main Store">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea class="form-control" name="notes" rows="3" maxlength="500"></textarea>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-secondary me-2" id="cancel-create">Cancel</button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save me-1"></i> Save Shirt Product
                                        </button>
                                    </div>
                                </form>
                                
                                <!-- Generic Product Form (for other categories) -->
                                <form id="generic-product-form" style="display: none;">
                                    @csrf
                                    <input type="hidden" name="category" id="generic-category">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">SKU *</label>
                                            <input type="text" class="form-control" name="sku" required maxlength="50">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Name *</label>
                                            <input type="text" class="form-control" name="name" required maxlength="255">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Type *</label>
                                            <select class="form-select" name="type" required>
                                                <option value="raw_material">Raw Material</option>
                                                <option value="finished_good" selected>Finished Good</option>
                                                <option value="consumable">Consumable</option>
                                                <option value="equipment">Equipment</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Unit of Measure *</label>
                                            <input type="text" class="form-control" name="unit_of_measure" required value="pieces">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Unit Price (PHP) *</label>
                                            <input type="number" class="form-control" name="unit_price" required min="0" step="0.01">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Current Stock *</label>
                                            <input type="number" class="form-control" name="current_stock" required min="0" step="0.001">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="3"></textarea>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-secondary me-2" id="cancel-generic-create">Cancel</button>
                                        <button type="