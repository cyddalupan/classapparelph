@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Inventory Action</h1>
                    <p class="text-muted mb-0">Manage your inventory items</p>
                </div>
            </div>

            <!-- Step 1: Select Category -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Step 1: Select Category</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Click on one of the boxes below to select a category</p>
                    
                    <!-- Category Boxes -->
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
                                    <p class="card-text text-muted small mb-0">Sneakers, boots, sandals</p>
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
                                    <p class="card-text text-muted small mb-0">Belts, hats, sunglasses</p>
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
                    
                    <!-- Hidden field for selected category -->
                    <input type="hidden" name="selected-category" id="selected-category" value="">
                </div>
            </div>

            <!-- Shirt Products Buttons (Hidden by default, shows when Shirt Products selected) -->
            <div id="shirt-products-buttons" style="display: none;">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Shirt Products Actions</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <button type="button" class="btn btn-primary" id="shirt-add-item-btn">
                                <i class="fas fa-plus-circle me-2"></i>Add Shirt Product
                            </button>
                            <button type="button" class="btn btn-warning" id="shirt-deduct-item-btn">
                                <i class="fas fa-minus-circle me-2"></i>Deduct Shirt Product
                            </button>
                            <button type="button" class="btn btn-success" id="add-new-shirt-product-btn">
                                <i class="fas fa-plus-square me-2"></i>Add New Shirt Product
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Category Box Styling */
    .category-box {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .category-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    .category-box.selected {
        border-color: #4e73df !important;
        border-width: 2px;
        background-color: #f8f9fc;
    }
    
    .category-icon {
        color: #4e73df;
    }
    
    /* Button Styling */
    #shirt-products-buttons .btn {
        min-width: 180px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
    }
</style>
@endpush

<!-- Add New Shirt Product Modal -->
<div class="modal fade" id="addShirtProductModal" tabindex="-1" aria-labelledby="addShirtProductModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addShirtProductModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add New Shirt Product
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addShirtProductForm">
                    @csrf
                    
                    <div class="row">
                        <!-- SKU -->
                        <div class="col-md-6 mb-3">
                            <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sku" name="sku" required 
                                   placeholder="e.g., TSHIRT-BLK-M" maxlength="50">
                            <div class="form-text">Unique identifier for this shirt product</div>
                        </div>
                        
                        <!-- Brand -->
                        <div class="col-md-6 mb-3">
                            <label for="brand" class="form-label">Brand <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="brand" name="brand" required 
                                   placeholder="e.g., Nike, Adidas, Uniqlo" maxlength="100">
                            <div class="form-text">Brand of the shirt</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Type -->
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Select type</option>
                                <option value="T-shirt">T-shirt</option>
                                <option value="Polo">Polo</option>
                                <option value="Hoodie">Hoodie</option>
                                <option value="Sweatshirt">Sweatshirt</option>
                                <option value="Tank Top">Tank Top</option>
                                <option value="Long Sleeve">Long Sleeve</option>
                            </select>
                            <div class="form-text">Type of shirt</div>
                        </div>
                        
                        <!-- Color -->
                        <div class="col-md-6 mb-3">
                            <label for="color" class="form-label">Color <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="color" name="color" required 
                                   placeholder="e.g., Black, White, Blue" maxlength="50">
                            <div class="form-text">Color of the shirt</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Size -->
                        <div class="col-md-6 mb-3">
                            <label for="size" class="form-label">Size <span class="text-danger">*</span></label>
                            <select class="form-select" id="size" name="size" required>
                                <option value="">Select size</option>
                                <option value="XS">XS</option>
                                <option value="S">S</option>
                                <option value="M">M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                                <option value="XXL">XXL</option>
                                <option value="XXXL">XXXL</option>
                            </select>
                            <div class="form-text">Size of the shirt</div>
                        </div>
                        
                        <!-- Price -->
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price (₱) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="price" name="price" required 
                                   step="0.01" min="0" placeholder="0.00">
                            <div class="form-text">Price per unit in Philippine Peso</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Stock Quantity -->
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Initial Stock <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stock" name="stock" required 
                                   min="0" placeholder="0" value="0">
                            <div class="form-text">Initial quantity in stock</div>
                        </div>
                        
                        <!-- Supplier -->
                        <div class="col-md-6 mb-3">
                            <label for="supplier" class="form-label">Supplier</label>
                            <input type="text" class="form-control" id="supplier" name="supplier" 
                                   placeholder="Supplier name" maxlength="100">
                            <div class="form-text">Supplier of this shirt</div>
                        </div>
                    </div>
                    
                    <!-- Shop/Location -->
                    <div class="mb-3">
                        <label for="shop" class="form-label">Shop/Location</label>
                        <input type="text" class="form-control" id="shop" name="shop" 
                               placeholder="e.g., Main Store, Online, Warehouse" maxlength="100">
                        <div class="form-text">Where this shirt is located</div>
                    </div>
                    
                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" 
                                  placeholder="Additional notes about this shirt product"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" id="submitNewShirtProductBtn">
                    <i class="fas fa-save me-2"></i>Save Shirt Product
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Inventory page loaded - Clean version');
        
        // Category selection
        const categoryBoxes = document.querySelectorAll('.category-box');
        const selectedCategoryField = document.getElementById('selected-category');
        const shirtProductsButtons = document.getElementById('shirt-products-buttons');
        
        // Category selection handler
        categoryBoxes.forEach(box => {
            box.addEventListener('click', function() {
                console.log('Category clicked:', this.getAttribute('data-category'));
                
                // Remove selection from all boxes
                categoryBoxes.forEach(b => {
                    b.classList.remove('selected');
                });
                
                // Add selection to clicked box
                this.classList.add('selected');
                
                // Set category value
                const category = this.getAttribute('data-category');
                selectedCategoryField.value = category;
                
                // Show/hide shirt products buttons
                if (category === 'Shirt Products') {
                    console.log('Showing shirt products buttons');
                    shirtProductsButtons.style.display = 'block';
                } else {
                    console.log('Hiding shirt products buttons');
                    shirtProductsButtons.style.display = 'none';
                }
            });
        });
        
        // Shirt Products: Add Shirt Product button
        const shirtAddItemBtn = document.getElementById('shirt-add-item-btn');
        if (shirtAddItemBtn) {
            shirtAddItemBtn.addEventListener('click', function() {
                alert('Add Shirt Product button clicked!\nFunctionality coming soon.');
                console.log('Add Shirt Product button clicked');
            });
        }
        
        // Shirt Products: Deduct Shirt Product button
        const shirtDeductItemBtn = document.getElementById('shirt-deduct-item-btn');
        if (shirtDeductItemBtn) {
            shirtDeductItemBtn.addEventListener('click', function() {
                alert('Deduct Shirt Product button clicked!\nFunctionality coming soon.');
                console.log('Deduct Shirt Product button clicked');
            });
        }
        
        // Shirt Products: Add New Shirt Product button
        const addNewShirtProductBtn = document.getElementById('add-new-shirt-product-btn');
        if (addNewShirtProductBtn) {
            addNewShirtProductBtn.addEventListener('click', function() {
                console.log('Add New Shirt Product button clicked - Opening modal');
                
                // Open the Add New Shirt Product modal
                const shirtModalElement = document.getElementById('addShirtProductModal');
                
                if (shirtModalElement && typeof bootstrap !== 'undefined') {
                    const addShirtProductModal = new bootstrap.Modal(shirtModalElement);
                    addShirtProductModal.show();
                    console.log('Modal opened successfully');
                } else {
                    console.error('Modal element or Bootstrap not found');
                    console.log('Modal element:', shirtModalElement);
                    console.log('Bootstrap:', typeof bootstrap);
                    alert('Error: Could not open the form. Please refresh the page.');
                }
            });
        }
        
        // Form submission handler
        const submitNewShirtProductBtn = document.getElementById('submitNewShirtProductBtn');
        if (submitNewShirtProductBtn) {
            submitNewShirtProductBtn.addEventListener('click', function() {
                console.log('Save Shirt Product button clicked');
                
                const form = document.getElementById('addShirtProductForm');
                if (!form) {
                    console.error('Form not found');
                    alert('Error: Form not found');
                    return;
                }
                
                // Basic form validation
                if (!form.checkValidity()) {
                    console.log('Form validation failed');
                    form.reportValidity();
                    return;
                }
                
                // Disable button and show loading
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                this.disabled = true;
                
                // Collect form data
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                console.log('Form data:', data);
                
                // Real AJAX submission
                fetch('/inventory/shirt-products', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(result => {
                    console.log('Success:', result);
                    
                    // Re-enable button
                    this.innerHTML = originalText;
                    this.disabled = false;
                    
                    // Show success message
                    alert(result.message + '\n\n' +
                          'SKU: ' + result.data.sku + '\n' +
                          'Product: ' + result.data.name + '\n' +
                          'Price: ₱' + result.data.price + '\n' +
                          'Stock: ' + result.data.stock + '\n' +
                          'ID: ' + result.data.id);
                    
                    // Close modal
                    const modalElement = document.getElementById('addShirtProductModal');
                    if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                        modal.hide();
                    }
                    
                    // Reset form
                    form.reset();
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Re-enable button
                    this.innerHTML = originalText;
                    this.disabled = false;
                    
                    // Show error message
                    alert('Error saving shirt product: ' + error.message + '\n\nPlease check the form and try again.');
                });
            });
        }
        
        // Reset form when modal closes
        const shirtModalElement = document.getElementById('addShirtProductModal');
        if (shirtModalElement) {
            shirtModalElement.addEventListener('hidden.bs.modal', function () {
                console.log('Modal closed - resetting form');
                const form = document.getElementById('addShirtProductForm');
                if (form) {
                    form.reset();
                }
            });
        }
    });
</script>
@endpush

@endsection