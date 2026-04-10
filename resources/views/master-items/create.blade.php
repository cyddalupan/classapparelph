@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Add New Master Product
                    </h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('master-items.store') }}" method="POST">
                        @csrf
                        
                        @php
                            // Check if category parameter is present in URL
                            $urlCategory = request()->get('category');
                            $isShirtProducts = $urlCategory === 'Shirt Products';
                        @endphp
                        
                        @if($isShirtProducts)
                            <!-- Hidden category field for Shirt Products (from URL parameter) -->
                            <input type="hidden" id="category" name="category" value="Shirt Products">
                            
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <div class="alert alert-info py-2">
                                    <i class="fas fa-tshirt me-2"></i>
                                    <strong>Shirt Products</strong> (T-shirts, polo, hoodies)
                                    <div class="form-text mt-1">Category automatically selected from previous page</div>
                                </div>
                            </div>
                        @else
                            <!-- Normal category dropdown for other cases -->
                            <div class="mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select category</option>
                                    <option value="Shirt Products" {{ old('category') == 'Shirt Products' ? 'selected' : '' }}>Shirt Products (T-shirts, polo, hoodies)</option>
                                    <option value="Other Products" {{ old('category') == 'Other Products' ? 'selected' : '' }}>Other Products (Mugs, totebags, lanyards, etc.)</option>
                                    <option value="Machine and Equipments" {{ old('category') == 'Machine and Equipments' ? 'selected' : '' }}>Machines & Equipment (Tools, machines, equipment)</option>
                                    <option value="Garment Materials" {{ old('category') == 'Garment Materials' ? 'selected' : '' }}>Garment Materials (Fabrics, threads, accessories)</option>
                                    <option value="Printing and Office Supplies" {{ old('category') == 'Printing and Office Supplies' ? 'selected' : '' }}>Printing & Office Supplies (Ink, paper, office supplies)</option>
                                </select>
                                <div class="form-text">Select the appropriate category for this product</div>
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="{{ old('name') }}" placeholder="Enter product name" required>
                        </div>
                        
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-bolt me-2"></i>
                            <strong>Auto-SKU Generation</strong>
                            <div class="mt-1">SKUs will be automatically generated from Brand + Type + Color + Size</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3" placeholder="Enter product description">{{ old('description') }}</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="unit_price" class="form-label">Unit Price (₱)</label>
                            <input type="number" class="form-control" id="unit_price" name="unit_price" 
                                   value="{{ old('unit_price') }}" placeholder="0.00" step="0.01" min="0">
                            <div class="form-text">Price per unit in Philippine Pesos</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="barcode" class="form-label">Barcode</label>
                            <input type="text" class="form-control" id="barcode" name="barcode" 
                                   value="{{ old('barcode') }}" placeholder="Enter barcode (optional)">
                        </div>
                        
                        <!-- Category-specific fields (will be shown/hidden based on category selection) -->
                        <div id="shirt-fields" class="category-fields" style="display: none;">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-tshirt me-1"></i> Shirt Product Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">

                                        <div class="col-md-6 mb-3">
                                            <label for="color" class="form-label">Color</label>
                                            <input type="text" class="form-control" id="color" name="color" placeholder="Enter color">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="material" class="form-label">Material</label>
                                            <input type="text" class="form-control" id="material" name="material" placeholder="e.g., Cotton, Polyester">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="brand" class="form-label">Brand</label>
                                            <input type="text" class="form-control" id="brand" name="brand" placeholder="Enter brand name">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="type" class="form-label">Type</label>
                                            <input type="text" class="form-control" id="type" name="type" placeholder="e.g., T-Shirt, Polo, Hoodie">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="machine-fields" class="category-fields" style="display: none;">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-tools me-1"></i> Machine & Equipment Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="model" class="form-label">Model</label>
                                            <input type="text" class="form-control" id="model" name="model" placeholder="Enter model number">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="serial_number" class="form-label">Serial Number</label>
                                            <input type="text" class="form-control" id="serial_number" name="serial_number" placeholder="Enter serial number">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="warranty_period" class="form-label">Warranty Period</label>
                                            <input type="text" class="form-control" id="warranty_period" name="warranty_period" placeholder="e.g., 1 year">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="power_requirement" class="form-label">Power Requirement</label>
                                            <input type="text" class="form-control" id="power_requirement" name="power_requirement" placeholder="e.g., 220V, 50Hz">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="material-fields" class="category-fields" style="display: none;">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-cut me-1"></i> Garment Material Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="fabric_type" class="form-label">Fabric Type</label>
                                            <input type="text" class="form-control" id="fabric_type" name="fabric_type" placeholder="e.g., Cotton, Silk, Polyester">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="weight" class="form-label">Weight (per meter)</label>
                                            <input type="text" class="form-control" id="weight" name="weight" placeholder="e.g., 200gsm">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="width" class="form-label">Width</label>
                                            <input type="text" class="form-control" id="width" name="width" placeholder="e.g., 150cm">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="color_fastness" class="form-label">Color Fastness</label>
                                            <select class="form-select" id="color_fastness" name="color_fastness">
                                                <option value="">Select level</option>
                                                <option value="Excellent">Excellent</option>
                                                <option value="Good">Good</option>
                                                <option value="Fair">Fair</option>
                                                <option value="Poor">Poor</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="printing-fields" class="category-fields" style="display: none;">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-print me-1"></i> Printing & Office Supply Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="paper_type" class="form-label">Paper Type</label>
                                            <input type="text" class="form-control" id="paper_type" name="paper_type" placeholder="e.g., Bond, Glossy, Matte">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="paper_size" class="form-label">Paper Size</label>
                                            <input type="text" class="form-control" id="paper_size" name="paper_size" placeholder="e.g., A4, Letter, Legal">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="ink_type" class="form-label">Ink Type</label>
                                            <input type="text" class="form-control" id="ink_type" name="ink_type" placeholder="e.g., CMYK, Pigment, Dye">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="yield" class="form-label">Yield (pages/cartridge)</label>
                                            <input type="number" class="form-control" id="yield" name="yield" placeholder="e.g., 1000">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('master-items.index') }}" class="btn btn-secondary me-md-2">
                                <i class="fas fa-arrow-left me-1"></i> Back to Catalog
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Master Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const categoryFields = document.querySelectorAll('.category-fields');
    
    function showCategoryFields() {
        // Hide all category-specific fields
        categoryFields.forEach(field => {
            field.style.display = 'none';
        });
        
        // Show fields for selected category
        const selectedCategory = categorySelect.value;
        if (selectedCategory === 'Shirt Products') {
            document.getElementById('shirt-fields').style.display = 'block';
        } else if (selectedCategory === 'Machine and Equipments') {
            document.getElementById('machine-fields').style.display = 'block';
        } else if (selectedCategory === 'Garment Materials') {
            document.getElementById('material-fields').style.display = 'block';
        } else if (selectedCategory === 'Printing and Office Supplies') {
            document.getElementById('printing-fields').style.display = 'block';
        }
        // Other Products doesn't have special fields
    }
    
    // Check if category is a hidden input (Shirt Products from URL)
    if (categorySelect && categorySelect.type === 'hidden') {
        // It's a hidden input with value="Shirt Products"
        console.log('Category is hidden input with value:', categorySelect.value);
        
        // Immediately show Shirt Products fields
        document.getElementById('shirt-fields').style.display = 'block';
        
        // Also trigger size selection section creation
        setTimeout(() => {
            if (typeof createSizeSelectionSection === 'function') {
                createSizeSelectionSection();
            }
        }, 100);
    } 
    // If it's a select element, add event listener
    else if (categorySelect && categorySelect.tagName === 'SELECT') {
        // Initial show based on current selection
        showCategoryFields();
        
        // Update when category changes
        categorySelect.addEventListener('change', showCategoryFields);
    }
});

// ==================== BULK CREATION - SIZE SELECTION ====================

// Common sizes for selection
const commonSizes = ['10', '12', 'M', 'L', 'XL'];

// Generate SKU based on inputs (removing vowels like PHP controller)
function generateSKU() {
    const brand = document.getElementById('brand').value.trim();
    const type = document.getElementById('type').value.trim();
    const color = document.getElementById('color').value.trim();
    
    if (!brand && !type && !color) {
        return '';
    }
    
    // Function to remove vowels (matches PHP controller logic)
    const removeVowels = (text) => {
        // Remove vowels (both uppercase and lowercase)
        let result = text.replace(/[aeiouAEIOU]/g, '');
        // Remove spaces and special characters, keep only letters/numbers
        result = result.replace(/[^A-Z0-9]/gi, '');
        return result.toUpperCase();
    };
    
    const skuParts = [];
    if (brand) skuParts.push(removeVowels(brand));
    if (type) skuParts.push(removeVowels(type));
    if (color) skuParts.push(removeVowels(color));
    
    // Simple SKU format: BRAND-TYPE-COLOR (vowels removed)
    return skuParts.join('-');
}

// Update preview when inputs change
function updatePreview() {
    const baseSKU = generateSKU();
    const previewDiv = document.getElementById('sizePreview');
    
    if (!baseSKU) {
        previewDiv.innerHTML = '<div class="alert alert-warning">Enter brand, type, and color to see SKU preview</div>';
        return;
    }
    
    let html = '<h6 class="mb-3">Selected Sizes & SKUs:</h6>';
    html += '<div class="table-responsive"><table class="table table-sm table-hover">';
    html += '<thead><tr><th>Size</th><th>SKU</th><th>Status</th></tr></thead><tbody>';
    
    commonSizes.forEach(size => {
        const checkbox = document.getElementById(`size_${size}`);
        const isChecked = checkbox ? checkbox.checked : false;
        const sku = `${baseSKU}-${size}`;
        
        html += `<tr>
            <td>${size}</td>
            <td><code>${sku}</code></td>
            <td>${isChecked ? '<span class="badge bg-success">Selected</span>' : '<span class="badge bg-secondary">Not Selected</span>'}</td>
        </tr>`;
    });
    
    html += '</tbody></table></div>';
    previewDiv.innerHTML = html;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add size selection section after category fields
    const categoryFields = document.querySelector('.category-fields');
    if (categoryFields) {
        const sizeSection = document.createElement('div');
        sizeSection.className = 'card mb-4';
        sizeSection.innerHTML = `
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Size Selection</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <i class="fas fa-lightbulb me-1"></i>
                    <strong>Check one or more sizes</strong> to create products. Works for single items or bulk creation.
                </p>
                
                <div class="row mb-3">
                    ${commonSizes.map(size => `
                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="size_${size}" name="selected_sizes[]" value="${size}" onchange="updatePreview()">
                                <label class="form-check-label" for="size_${size}">
                                    Size ${size}
                                </label>
                            </div>
                        </div>
                    `).join('')}
                </div>
                
                <div id="sizePreview" class="mt-3">
                    <div class="alert alert-info">Select sizes to see SKU preview</div>
                </div>
            </div>
        `;
        
        categoryFields.parentNode.insertBefore(sizeSection, categoryFields.nextSibling);
    }
    
    // Add event listeners to brand/type/color inputs
    ['brand', 'type', 'color'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', updatePreview);
        }
    });
    
    // Initial preview update
    setTimeout(updatePreview, 100);
    
    // ==================== AUTO-SELECT CATEGORY FROM URL ====================
    
    // Check for category parameter in URL (only needed for select elements)
    const urlParams = new URLSearchParams(window.location.search);
    const categoryParam = urlParams.get('category');
    
    // Only run auto-selection if categorySelect is a SELECT element (not hidden input)
    if (categoryParam && categorySelect && categorySelect.tagName === 'SELECT') {
        // Find the option that matches the category parameter
        const options = categorySelect.options;
        for (let i = 0; i < options.length; i++) {
            if (options[i].value === categoryParam) {
                categorySelect.value = categoryParam;
                console.log(`Auto-selected category from URL: ${categoryParam}`);
                
                // Trigger the change event to show the appropriate fields
                const event = new Event('change');
                categorySelect.dispatchEvent(event);
                break;
            }
        }
    }
});
</script>
@endsection