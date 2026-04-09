@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-edit text-warning me-2"></i>
                        Edit Inventory Item
                    </h1>
                    <p class="text-muted mb-0">Update details for: <strong>{{ $inventory->name }}</strong> (SKU: {{ $inventory->sku }})</p>
                </div>
                <div>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Edit Form -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Item Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventory.update', $inventory) }}" method="POST" id="edit-inventory-form">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-info-circle text-primary me-2"></i>
                                    Basic Information
                                </h6>
                            </div>
                            
                            <!-- Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Item Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="{{ old('name', $inventory->name) }}" required>
                                <div class="form-text">Enter a descriptive name for this item</div>
                            </div>
                            
                            <!-- SKU -->
                            <div class="col-md-6 mb-3">
                                <label for="sku" class="form-label">SKU (Stock Keeping Unit)</label>
                                <input type="text" class="form-control" id="sku" name="sku" 
                                       value="{{ old('sku', $inventory->sku) }}">
                                <div class="form-text">Unique identifier for this item</div>
                            </div>
                            
                            <!-- Category -->
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    @php
                                        // Extract categories from the associative array
                                        // $categories is an array like: ['raw_material' => [...], 'finished_good' => [...]]
                                        // We need to get the first set of categories
                                        $categoryList = !empty($categories) ? reset($categories) : [];
                                    @endphp
                                    @foreach($categoryList as $category)
                                        <option value="{{ $category }}" {{ old('category', $inventory->category) == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Type -->
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Item Type *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type }}" {{ old('type', $inventory->type) == $type ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Description -->
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $inventory->description) }}</textarea>
                                <div class="form-text">Optional description of the item</div>
                            </div>
                        </div>
                        
                        <!-- Pricing & Stock -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-chart-line text-primary me-2"></i>
                                    Pricing & Stock
                                </h6>
                            </div>
                            
                            <!-- Unit Price -->
                            <div class="col-md-4 mb-3">
                                <label for="unit_price" class="form-label">Unit Price (₱) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="unit_price" name="unit_price" 
                                           step="0.01" min="0" value="{{ old('unit_price', $inventory->unit_price) }}" required>
                                </div>
                                <div class="form-text">Price per unit</div>
                            </div>
                            
                            <!-- Unit of Measure -->
                            <div class="col-md-4 mb-3">
                                <label for="unit_of_measure" class="form-label">Unit of Measure</label>
                                <input type="text" class="form-control" id="unit_of_measure" name="unit_of_measure" 
                                       value="{{ old('unit_of_measure', $inventory->unit_of_measure) }}">
                                <div class="form-text">e.g., piece, kg, meter, box</div>
                            </div>
                            
                            <!-- Current Stock -->
                            <div class="col-md-4 mb-3">
                                <label for="current_stock" class="form-label">Current Stock *</label>
                                <input type="number" class="form-control" id="current_stock" name="current_stock" 
                                       min="0" value="{{ old('current_stock', $inventory->current_stock) }}" required>
                                <div class="form-text">Current quantity in inventory</div>
                            </div>
                            
                            <!-- Minimum Stock -->
                            <div class="col-md-4 mb-3">
                                <label for="minimum_stock" class="form-label">Minimum Stock Level</label>
                                <input type="number" class="form-control" id="minimum_stock" name="minimum_stock" 
                                       min="0" value="{{ old('minimum_stock', $inventory->minimum_stock) }}">
                                <div class="form-text">Reorder when stock falls below this level</div>
                            </div>
                            
                            <!-- Reorder Quantity -->
                            <div class="col-md-4 mb-3">
                                <label for="reorder_quantity" class="form-label">Reorder Quantity</label>
                                <input type="number" class="form-control" id="reorder_quantity" name="reorder_quantity" 
                                       min="0" value="{{ old('reorder_quantity', $inventory->reorder_quantity) }}">
                                <div class="form-text">Quantity to order when restocking</div>
                            </div>
                            
                            <!-- Status -->
                            <div class="col-md-4 mb-3">
                                <label for="is_active" class="form-label">Status</label>
                                <select class="form-select" id="is_active" name="is_active">
                                    <option value="1" {{ old('is_active', $inventory->is_active) == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', $inventory->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <div class="form-text">Active items appear in inventory lists</div>
                            </div>
                        </div>
                        
                        <!-- Supplier Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-truck text-primary me-2"></i>
                                    Supplier Information
                                </h6>
                            </div>
                            
                            <!-- Supplier -->
                            <div class="col-md-6 mb-3">
                                <label for="supplier_id" class="form-label">Supplier</label>
                                <select class="form-select" id="supplier_id" name="supplier_id">
                                    <option value="">No Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $inventory->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Supplier SKU -->
                            <div class="col-md-6 mb-3">
                                <label for="supplier_sku" class="form-label">Supplier SKU</label>
                                <input type="text" class="form-control" id="supplier_sku" name="supplier_sku" 
                                       value="{{ old('supplier_sku', $inventory->supplier_sku) }}">
                                <div class="form-text">Supplier's product code</div>
                            </div>
                        </div>
                        
                        <!-- Additional Details -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-ellipsis-h text-primary me-2"></i>
                                    Additional Details
                                </h6>
                            </div>
                            
                            <!-- Specifications -->
                            <div class="col-md-6 mb-3">
                                <label for="specifications" class="form-label">Specifications</label>
                                <textarea class="form-control" id="specifications" name="specifications" rows="2">{{ old('specifications', $inventory->specifications) }}</textarea>
                                <div class="form-text">Technical specifications or details</div>
                            </div>
                            
                            <!-- Storage Location -->
                            <div class="col-md-6 mb-3">
                                <label for="storage_location" class="form-label">Storage Location</label>
                                <input type="text" class="form-control" id="storage_location" name="storage_location" 
                                       value="{{ old('storage_location', $inventory->storage_location) }}">
                                <div class="form-text">Where this item is stored</div>
                            </div>
                            
                            <!-- Last Restocked -->
                            <div class="col-md-6 mb-3">
                                <label for="last_restocked_at" class="form-label">Last Restocked Date</label>
                                <input type="date" class="form-control" id="last_restocked_at" name="last_restocked_at" 
                                       value="{{ old('last_restocked_at', $inventory->last_restocked_at ? $inventory->last_restocked_at->format('Y-m-d') : '') }}">
                            </div>
                            
                            <!-- Expiry Date -->
                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date" 
                                       value="{{ old('expiry_date', $inventory->expiry_date ? $inventory->expiry_date->format('Y-m-d') : '') }}">
                                <div class="form-text">For perishable items only</div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    
                                    <div class="btn-group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Save Changes
                                        </button>
                                        
                                        <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" 
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="visually-hidden">More Options</span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button type="submit" name="action" value="save_and_new" class="dropdown-item">
                                                    <i class="fas fa-save me-2"></i> Save & Create New
                                                </button>
                                            </li>
                                            <li>
                                                <button type="submit" name="action" value="save_and_duplicate" class="dropdown-item">
                                                    <i class="fas fa-copy me-2"></i> Save & Duplicate
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a href="{{ route('inventory.index') }}" class="dropdown-item">
                                                    <i class="fas fa-list me-2"></i> Back to Inventory List
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Danger Zone -->
            <div class="card border-danger mt-4">
                <div class="card-header bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Danger Zone
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <strong>Warning:</strong> These actions are irreversible. Please proceed with caution.
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Delete This Item</h6>
                            <p class="text-muted small mb-0">
                                Permanently remove this item from inventory. This action cannot be undone.
                            </p>
                        </div>
                        
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-1"></i> Delete Item
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="lead">Are you sure you want to delete this item?</p>
                <p>
                    <strong>Item:</strong> {{ $inventory->name }}<br>
                    <strong>SKU:</strong> {{ $inventory->sku }}<br>
                    <strong>Category:</strong> {{ $inventory->category }}
                </p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. All inventory data for this item will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <form action="{{ route('inventory.destroy', $inventory) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Form Validation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('edit-inventory-form');
    
    // Form validation
    form.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
                
                // Add error message if not already present
                if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'This field is required';
                    field.parentNode.appendChild(errorDiv);
                }
            } else {
                field.classList.remove('is-invalid');
                // Remove error message if present
                if (field.nextElementSibling && field.nextElementSibling.classList.contains('invalid-feedback')) {
                    field.nextElementSibling.remove();
                }
            }
        });
        
        // Prevent form submission if validation fails
        if (!isValid) {
            event.preventDefault();
            event.stopPropagation();
            
            // Scroll to first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
    
    // Real-time validation on input
    form.querySelectorAll('[required]').forEach(field => {
        field.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                // Remove error message if present
                if (this.nextElementSibling && this.nextElementSibling.classList.contains('invalid-feedback')) {
                    this.nextElementSibling.remove();
                }
            }
        });
    });
});
</script>

@endsection