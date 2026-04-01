<x-app-layout>
    @section('page-title', 'Add New Product')
    
    <x-slot name="header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-plus-circle"></i>
                Add New Product
            </h1>
            <p class="page-subtitle">Create a new product</p>
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
                        <h5 class="card-title mb-0">Product Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-12 mb-4">
                                    <h6 class="section-title mb-3">Basic Information</h6>
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label required">Product Name</label>
                                            <input type="text" name="name" class="form-control" 
                                                   value="{{ old('name') }}" required 
                                                   placeholder="e.g., Premium Cotton T-Shirt">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">SKU</label>
                                            <input type="text" name="sku" class="form-control" 
                                                   value="{{ old('sku') }}" 
                                                   placeholder="e.g., TSH-001">
                                            <small class="text-muted">Leave blank to auto-generate</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3" 
                                                  placeholder="Product description...">{{ old('description') }}</textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required">Product Type</label>
                                            <select name="type" class="form-select" required>
                                                <option value="">Select Type</option>
                                                @foreach($productTypes as $value => $label)
                                                    <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
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
                                                    <option value="{{ $value }}" {{ old('brand') == $value ? 'selected' : '' }}>
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
                                                <option value="{{ $value }}" {{ old('material') == $value ? 'selected' : '' }}>
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
                                                       step="0.01" min="0" value="{{ old('base_price', 0) }}" required
                                                       placeholder="0.00">
                                            </div>
                                            <small class="text-muted">Cost of the blank product</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required">Printing Cost per Color (₱)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" name="printing_cost_per_color" class="form-control" 
                                                       step="0.01" min="0" value="{{ old('printing_cost_per_color', 0) }}" required
                                                       placeholder="0.00">
                                            </div>
                                            <small class="text-muted">Additional cost for each print color</small>
                                        </div>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Total Price:</strong> Base Price + Printing Cost per Color = 
                                        <span id="total-price-display" class="fw-bold">₱0.00</span>
                                    </div>
                                </div>
                                
                                <!-- Inventory -->
                                <div class="col-md-12 mb-4">
                                    <h6 class="section-title mb-3">Inventory</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required">Stock Quantity</label>
                                            <input type="number" name="stock_quantity" class="form-control" 
                                                   min="0" value="{{ old('stock_quantity', 0) }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required">Reorder Level</label>
                                            <input type="number" name="reorder_level" class="form-control" 
                                                   min="0" value="{{ old('reorder_level', 10) }}" required>
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
                                                               id="color-{{ $hex }}" {{ in_array($hex, old('available_colors', [])) ? 'checked' : '' }}>
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
                                                               id="size-{{ $code }}" {{ in_array($code, old('available_sizes', [])) ? 'checked' : '' }}>
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
                                    <div class="mb-3">
                                        <label class="form-label">Product Image</label>
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
                                               id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <strong>Active Product</strong>
                                            <small class="text-muted d-block">Inactive products won't appear in sales</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar Help -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Tips & Guidelines
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <h6><i class="fas fa-lightbulb me-2"></i>Pricing Tips</h6>
                            <ul class="mb-0">
                                <li>Base Price: Cost of blank product</li>
                                <li>Printing Cost: Added for each color in design</li>
                                <li>Total = Base + Printing Cost</li>
                            </ul>
                        </div>
                        
                        <div class="alert alert-warning mb-3">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Inventory Management</h6>
                            <ul class="mb-0">
                                <li>Set realistic reorder levels</li>
                                <li>Update stock after sales</li>
                                <li>Monitor low stock alerts</li>
                            </ul>
                        </div>
                        
                        <div class="alert alert-success">
                            <h6><i class="fas fa-check-circle me-2"></i>Best Practices</h6>
                            <ul class="mb-0">
                                <li>Use clear, descriptive names</li>
                                <li>Upload high-quality images</li>
                                <li>Keep SKUs unique and meaningful</li>
                                <li>Set accurate pricing for profitability</li>
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
    </script>
    @endpush
    @endsection
</x-app-layout>