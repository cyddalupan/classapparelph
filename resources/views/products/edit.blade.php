<x-app-layout>
    @section('page-title', 'Edit Product: ' . $product->name)
    
    <x-slot name="header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Edit Product
            </h1>
            <p class="page-subtitle">{{ $product->name }}</p>
        </div>
    </x-slot>

    @section('content')
    <div class="page-content">
        <!-- Success/Error Messages -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Edit Product Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-12 mb-4">
                                    <h6 class="section-title mb-3">Basic Information</h6>
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label required">Product Name</label>
                                            <input type="text" name="name" class="form-control" 
                                                   value="{{ old('name', $product->name) }}" required 
                                                   placeholder="e.g., Premium Cotton T-Shirt">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SKU</label>
                                            <input type="text" name="sku" class="form-control" 
                                                   value="{{ old('sku', $product->sku) }}" 
                                                   placeholder="e.g., TSH-001">
                                            <small class="text-muted">Leave blank to auto-generate</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3" 
                                                  placeholder="Product description...">{{ old('description', $product->description) }}</textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required">Product Type</label>
                                            <select name="type" class="form-select" required>
                                                <option value="">Select Type</option>
                                                @foreach($productTypes as $value => $label)
                                                    <option value="{{ $value }}" {{ old('type', $product->type) == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Brand</label>
                                            <select name="brand" class="form-select">
                                                <option value="">Select Brand</option>
                                                @foreach($commonBrands as $value => $label)
                                                    <option value="{{ $value }}" {{ old('brand', $product->brand) == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Material</label>
                                        <select name="material" class="form-select">
                                            <option value="">Select Material</option>
                                            @foreach($commonMaterials as $value => $label)
                                                <option value="{{ $value }}" {{ old('material', $product->material) == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Pricing -->
                                <div class="col-md-12 mb-4">
                                    <h6 class="section-title mb-3">Pricing</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required">Base Price (₱)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" name="base_price" class="form-control" 
                                                       step="0.01" min="0" value="{{ old('base_price', $product->base_price) }}" required
                                                       placeholder="0.00">
                                            </div>
                                            <small class="text-muted">Cost of the blank product</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required">Printing Cost per Color (₱)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" name="printing_cost_per_color" class="form-control" 
                                                       step="0.01" min="0" value="{{ old('printing_cost_per_color', $product->printing_cost_per_color) }}" required
                                                       placeholder="0.00">
                                            </div>
                                            <small class="text-muted">Additional cost for each print color</small>
                                        </div>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Total Price:</strong> Base Price + Printing Cost per Color = 
                                        <span id="total-price-display" class="fw-bold">₱{{ number_format($product->base_price + $product->printing_cost_per_color, 2) }}</span>
                                    </div>
                                </div>
                                
                                <!-- Inventory -->
                                <div class="col-md-12 mb-4">
                                    <h6 class="section-title mb-3">Inventory</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required">Stock Quantity</label>
                                            <input type="number" name="stock_quantity" class="form-control" 
                                                   min="0" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required">Reorder Level</label>
                                            <input type="number" name="reorder_level" class="form-control" 
                                                   min="0" value="{{ old('reorder_level', $product->reorder_level) }}" required>
                                            <small class="text-muted">Alert when stock reaches this level</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Options -->
                                <div class="col-md-12 mb-4">
                                    <h6 class="section-title mb-3">Options</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Available Colors</label>
                                            <div class="color-options">
                                                @foreach($commonColors as $hex => $name)
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="available_colors[]" value="{{ $hex }}" 
                                                               id="color-{{ $hex }}" 
                                                               {{ in_array($hex, old('available_colors', $product->colors_array)) ? 'checked' : '' }}>
                                                        <label class="form-check-label d-flex align-items-center" for="color-{{ $hex }}">
                                                            <span class="color-swatch me-2" style="background-color: {{ $hex }};"></span>
                                                            {{ $name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Available Sizes</label>
                                            <div class="size-options">
                                                @foreach($commonSizes as $code => $name)
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="available_sizes[]" value="{{ $code }}" 
                                                               id="size-{{ $code }}" 
                                                               {{ in_array($code, old('available_sizes', $product->sizes_array)) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="size-{{ $code }}">
                                                            {{ $code }} ({{ $name }})
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Image Upload -->
                                <div class="col-md-12 mb-4">
                                    <h6 class="section-title mb-3">Product Image</h6>
                                    @if($product->image_url)
                                        <div class="mb-3">
                                            <label class="form-label">Current Image</label>
                                            <div>
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" 
                                                     class="img-thumbnail" style="max-width: 200px;">
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                                                    <label class="form-check-label" for="remove_image">
                                                        Remove current image
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="mb-3">
                                        <label class="form-label">{{ $product->image_url ? 'Upload New Image' : 'Product Image' }}</label>
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                        <small class="text-muted">Max 2MB. JPG, PNG, GIF formats only.</small>
                                    </div>
                                    <div id="image-preview" class="mt-3" style="display: none;">
                                        <img id="preview-image" src="#" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                </div>
                                
                                <!-- Status -->
                                <div class="col-md-12 mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" 
                                               id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <strong>Active Product</strong>
                                            <small class="text-muted d-block">Inactive products won't appear in sales</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Cancel
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="confirmDelete('{{ route('products.destroy', $product) }}', '{{ $product->name }}')">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </button>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Product
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar Stats -->
            <div class="col-lg-4">
                <!-- Product Stats -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Product Stats
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="stat-value {{ $product->is_low_stock ? 'text-danger' : 'text-success' }}">
                                    {{ $product->stock_quantity }}
                                </div>
                                <div class="stat-label">In Stock</div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stat-value text-info">
                                    {{ $product->reorder_level }}
                                </div>
                                <div class="stat-label">Reorder Level</div>
                            </div>
                        </div>
                        
                        @if($product->is_low_stock)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Low Stock Alert!</strong>
                                <p class="mb-0">Stock is below reorder level. Consider restocking.</p>
                            </div>
                        @endif
                        
                        <div class="mt-3">
                            <h6>Quick Actions</h6>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" onclick="updateStock({{ $product->id }})">
                                    <i class="fas fa-boxes me-2"></i>Update Stock
                                </button>
                                <a href="#" class="btn btn-outline-success">
                                    <i class="fas fa-shopping-cart me-2"></i>View Sales
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pricing Summary -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-money-bill-wave me-2"></i>Pricing Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td>Base Price:</td>
                                <td class="text-end">₱{{ number_format($product->base_price, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Printing Cost:</td>
                                <td class="text-end">₱{{ number_format($product->printing_cost_per_color, 2) }}</td>
                            </tr>
                            <tr class="table-primary">
                                <td><strong>Total Price:</strong></td>
                                <td class="text-end fw-bold">₱{{ number_format($product->base_price + $product->printing_cost_per_color, 2) }}</td>
                            </tr>
                        </table>
                        
                        <div class="alert alert-info mt-3">
                            <h6><i class="fas fa-lightbulb me-2"></i>Pricing Tips</h6>
                            <ul class="mb-0 small">
                                <li>Consider material costs</li>
                                <li>Factor in labor costs</li>
                                <li>Include profit margin</li>
                                <li>Check competitor pricing</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('styles')
    <style>
        .color-swatch {
            width: 20px;
            height: 20px;
            border-radius: 3px;
            border: 1px solid #dee2e6;
            display: inline-block;
        }
        .color-options, .size-options {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 10px;
        }
        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        .required:after {
            content: " *";
            color: #dc3545;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }
        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
    </style>
    @endpush
    
    @push('scripts')
    <script>
        // Calculate and display total price
        function calculateTotalPrice() {
            const basePrice = parseFloat(document.querySelector('input[name="base_price"]').value) || 0;
            const printingCost = parseFloat(document.querySelector('input[name="printing_cost_per_color"]').value) || 0;
            const total = basePrice + printingCost;
            document.getElementById('total-price-display').textContent = '₱' + total.toFixed(2);
        }
        
        // Initialize calculation
        calculateTotalPrice();
        
        // Update on input change
        document.querySelector('input[name="base_price"]').addEventListener('input', calculateTotalPrice);
        document.querySelector('input[name="printing_cost_per_color"]').addEventListener('input', calculateTotalPrice);
        
        // Image preview
        document.querySelector('input[name="image"]').addEventListener('change', function(e) {
            const preview = document.getElementById('preview-image');
            const previewContainer = document.getElementById('image-preview');
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
            } else {
                previewContainer.style.display = 'none';
            }
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const basePrice = parseFloat(document.querySelector('input[name="base_price"]').value);
            const printingCost = parseFloat(document.querySelector('input[name="printing_cost_per_color"]').value);
            
            if (basePrice < 0 || printingCost < 0) {
                e.preventDefault();
                alert('Prices cannot be negative.');
                return false;
            }
            
            if (isNaN(basePrice) || isNaN(printingCost)) {
                e.preventDefault();
                alert('Please enter valid prices.');
                return false;
            }
            
            return true;
        });
        
        // Delete confirmation
        function confirmDelete(url, productName) {
            if (confirm(`Are you sure you want to delete "${productName}"? This action cannot be undone.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                
                form.appendChild(methodInput);
                form.appendChild(csrfInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Quick stock update
        function updateStock(productId) {
            const adjustment = prompt('Enter stock adjustment (positive to add, negative to remove):');
            if (adjustment !== null) {
                const reason = prompt('Enter reason for stock adjustment:');
                if (reason !== null) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/products/${productId}/update-stock`;
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    const adjustmentInput = document.createElement('input');
                    adjustmentInput.type = 'hidden';
                    adjustmentInput.name = 'adjustment';
                    adjustmentInput.value = adjustment;
                    
                    const reasonInput = document.createElement('input');
                    reasonInput.type = 'hidden';
                    reasonInput.name = 'reason';
                    reasonInput.value = reason;
                    
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    
                    form.appendChild(adjustmentInput);
                    form.appendChild(reasonInput);
                    form.appendChild(csrfInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
    </script>
    @endpush
    @endsection
</x-app-layout>