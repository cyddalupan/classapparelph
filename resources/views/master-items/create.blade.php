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
                            $validCategories = ['Shirt Products', 'Other Products', 'Machine and Equipments', 'Garment Materials', 'Printing and Office Supplies'];
                            $hasUrlCategory = in_array($urlCategory, $validCategories);
                        @endphp
                        
                        @if($hasUrlCategory)
                            <!-- Hidden category field when coming from URL parameter -->
                            <input type="hidden" id="category" name="category" value="{{ $urlCategory }}">
                            
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <div class="alert alert-info py-2">
                                    @if($urlCategory === 'Shirt Products')
                                        <i class="fas fa-tshirt me-2"></i>
                                        <strong>Shirt Products</strong> (T-shirts, polo, hoodies)
                                    @elseif($urlCategory === 'Other Products')
                                        <i class="fas fa-gift me-2"></i>
                                        <strong>Other Products</strong> (Mugs, totebags, lanyards, etc.)
                                    @elseif($urlCategory === 'Machine and Equipments')
                                        <i class="fas fa-tools me-2"></i>
                                        <strong>Machines & Equipment</strong> (Tools, machines, equipment)
                                    @elseif($urlCategory === 'Garment Materials')
                                        <i class="fas fa-cut me-2"></i>
                                        <strong>Garment Materials</strong> (Fabrics, threads, accessories)
                                    @elseif($urlCategory === 'Printing and Office Supplies')
                                        <i class="fas fa-print me-2"></i>
                                        <strong>Printing & Office Supplies</strong> (Ink, paper, office supplies)
                                    @endif
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
                        
                        <div class="alert alert-info mb-3" id="sku-info-alert">
                            <i class="fas fa-bolt me-2"></i>
                            <strong>Auto-SKU Generation</strong>
                            <div class="mt-1" id="sku-info-text">SKUs will be automatically generated from Brand + Type + Color + Size</div>
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
                        <div id="shirt-fields" class="category-fields" style="display: none; position: absolute; left: -9999px; top: -9999px;">
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
                        
                        <div id="machine-fields" class="category-fields" style="display: none; position: absolute; left: -9999px; top: -9999px;">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-tools me-1"></i> Machine & Equipment Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="machine_brand" class="form-label">Brand</label>
                                            <input type="text" class="form-control" id="machine_brand" name="machine_brand" placeholder="e.g., Brother, Juki, Ricoh">
                                        </div>
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
                        
                        <div id="material-fields" class="category-fields" style="display: none; position: absolute; left: -9999px; top: -9999px;">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-cut me-1"></i> Garment Material Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="material_type" class="form-label">Material Type</label>
                                            <select class="form-select" id="material_type" name="material_type">
                                                <option value="">Select Material Type</option>
                                                <option value="Fabric">Fabric</option>
                                                <option value="Thread">Thread</option>
                                                <option value="Needle">Needle</option>
                                                <option value="Scissors">Scissors</option>
                                                <option value="Pins">Pins</option>
                                                <option value="Buttons">Buttons</option>
                                                <option value="Zippers">Zippers</option>
                                                <option value="Elastic">Elastic</option>
                                                <option value="Interfacing">Interfacing</option>
                                                <option value="Other">Other Sewing Material</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="material_brand" class="form-label">Brand</label>
                                            <input type="text" class="form-control" id="material_brand" name="material_brand" placeholder="e.g., Coats, Gütermann, Singer">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="material_color" class="form-label">Color</label>
                                            <input type="text" class="form-control" id="material_color" name="material_color" placeholder="e.g., Red, White, Black, Assorted">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="material_specification" class="form-label">Specification</label>
                                            <input type="text" class="form-control" id="material_specification" name="material_specification" placeholder="e.g., 200gsm, Size 5, 150cm width, 100m spool">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="material_sku" class="form-label">SKU (Manual Override - Optional)</label>
                                            <input type="text" class="form-control" id="material_sku" name="material_sku" placeholder="Optional: Enter manual SKU to override auto-generation">
                                            <small class="text-muted">Leave empty for auto-generated SKU with random suffix</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="material_name" class="form-label">Item Name</label>
                                            <input type="text" class="form-control" id="material_name" name="material_name" placeholder="e.g., Red Thread Spool, Fabric Scissors, Sewing Needles">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="printing-fields" class="category-fields" style="display: none; position: absolute; left: -9999px; top: -9999px;">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-print me-1"></i> Printing & Office Supply Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- NEW FIELDS FOR PRINTING SERVICES -->
                                        <div class="col-md-6 mb-3">
                                            <label for="printing_product_type" class="form-label">Type of Print *</label>
                                            <input type="text" class="form-control" id="printing_product_type" name="printing_product_type" placeholder="e.g., DTF Print, Sublimation Print, Screen Print">
                                            <div class="form-text">Enter the printing method (DTF, Sublimation, Screen, etc.)</div>
                                        </div>
                                        
                                        <!-- EXISTING FIELDS -->
                                        <div class="col-md-6 mb-3">
                                            <label for="paper_type" class="form-label">Paper Type</label>
                                            <input type="text" class="form-control" id="paper_type" name="paper_type" placeholder="e.g., Bond, Glossy, Matte">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="paper_size" class="form-label">Size of Print *</label>
                                            <input type="text" class="form-control" id="paper_size" name="paper_size" placeholder="e.g., A4 Print, Logo Print, A3 Print, Full Color">
                                            <div class="form-text">Enter the print size/type (A4, Logo, A3, Full Color, etc.)</div>
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
                        
                        <!-- Other Products Fields -->
                        <div id="other-fields" class="category-fields" style="display: none; position: absolute; left: -9999px; top: -9999px;">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-gift me-1"></i> Other Product Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="other_brand" class="form-label">Brand</label>
                                            <input type="text" class="form-control" id="other_brand" name="other_brand" placeholder="e.g., Yalex, Fruit of the Loom, Custom">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="product_type" class="form-label">Product Type</label>
                                            <select class="form-control" id="product_type" name="product_type">
                                                <option value="">Select product type</option>
                                                <option value="Mug">Mug</option>
                                                <option value="Tote Bag">Tote Bag</option>
                                                <option value="Lanyard">Lanyard</option>
                                                <option value="Pen">Pen</option>
                                                <option value="Notebook">Notebook</option>
                                                <option value="Keychain">Keychain</option>
                                                <option value="Tumbler">Tumbler</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="other_material" class="form-label">Material</label>
                                            <input type="text" class="form-control" id="other_material" name="other_material" placeholder="e.g., Ceramic, Cotton, Nylon, Plastic">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="size_dimension" class="form-label">Size / Dimensions</label>
                                            <input type="text" class="form-control" id="size_dimension" name="size_dimension" placeholder="e.g., 11oz, 16oz, 12x16 inches">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="other_color" class="form-label">Color</label>
                                            <input type="text" class="form-control" id="other_color" name="other_color" placeholder="Enter color">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="design_area" class="form-label">Design / Print Area</label>
                                            <input type="text" class="form-control" id="design_area" name="design_area" placeholder="e.g., Front only, Full wrap, 3x3 inches">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="other_features" class="form-label">Special Features</label>
                                            <textarea class="form-control" id="other_features" name="other_features" rows="2" placeholder="e.g., Handle color, Strap type, Clip type, Insulated"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- SKU Preview Section (for all categories) -->
                        <div id="sizePreview" class="mt-4 mb-4">
                            <div class="alert alert-info">Enter product details to see SKU preview</div>
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
    
    // If category element doesn't exist, exit early
    if (!categorySelect) {
        console.warn('Category element (#category) not found on page');
        return;
    }
    
    function showCategoryFields() {
        // Safety check
        if (!categorySelect) {
            console.error('categorySelect is not defined!');
            return;
        }
        
        // First, remove required attribute from all category-specific fields
        categoryFields.forEach(field => {
            const requiredInputs = field.querySelectorAll('input[data-was-required], select[data-was-required], textarea[data-was-required]');
            requiredInputs.forEach(input => {
                input.removeAttribute('required');
            });
        });
        
        // Hide all category-specific fields (and move offscreen)
        categoryFields.forEach(field => {
            field.style.display = 'none';
            field.style.position = 'absolute';
            field.style.left = '-9999px';
            field.style.top = '-9999px';
        });
        
        // Show fields for selected category (and position normally)
        const selectedCategory = categorySelect.value;
        let activeField = null;
        
        if (selectedCategory === 'Shirt Products') {
            activeField = document.getElementById('shirt-fields');
            // Update SKU info message for shirts
            document.getElementById('sku-info-text').textContent = 'SKUs will be automatically generated from Brand + Type + Color + Size';
            
            // Add event listeners to Shirt fields for SKU preview
            const brand = document.getElementById('brand');
            const type = document.getElementById('type');
            const color = document.getElementById('color');
            
            if (brand) brand.addEventListener('input', updatePreview);
            if (type) type.addEventListener('input', updatePreview);
            if (color) color.addEventListener('input', updatePreview);
        } else if (selectedCategory === 'Machine and Equipments') {
            activeField = document.getElementById('machine-fields');
            document.getElementById('sku-info-text').textContent = 'SKUs will be automatically generated from Brand + Type + Specifications';
            
            // Add event listeners to Machine fields for SKU preview
            const machineBrand = document.getElementById('machine_brand');
            const machineType = document.getElementById('machine_type');
            const specifications = document.getElementById('specifications');
            
            if (machineBrand) machineBrand.addEventListener('input', updatePreview);
            if (machineType) machineType.addEventListener('input', updatePreview);
            if (specifications) specifications.addEventListener('input', updatePreview);
        } else if (selectedCategory === 'Garment Materials') {
            activeField = document.getElementById('material-fields');
            document.getElementById('sku-info-text').textContent = 'SKU will be auto-generated from Brand + Material + Color + Item Name + Random suffix (ABC123). Or enter manual SKU below.';
            
            // Add event listeners to Garment Materials fields for SKU preview
            const materialSku = document.getElementById('material_sku');
            const materialName = document.getElementById('material_name');
            
            if (materialSku) materialSku.addEventListener('input', updatePreview);
            if (materialName) materialName.addEventListener('input', updatePreview);
        } else if (selectedCategory === 'Printing and Office Supplies') {
            activeField = document.getElementById('printing-fields');
            document.getElementById('sku-info-text').textContent = 'SKUs will be automatically generated from Product Name + Type of Print + Paper Type + Size of Print + Random ABC123 suffix';
            
            // Add event listeners to Printing fields for SKU preview
            const productName = document.getElementById('name');
            const printingProductType = document.getElementById('printing_product_type');
            const paperType = document.getElementById('paper_type');
            const paperSizeField = document.getElementById('paper_size');
            
            if (productName) productName.addEventListener('input', updatePreview);
            if (printingProductType) printingProductType.addEventListener('input', updatePreview);
            if (paperType) paperType.addEventListener('input', updatePreview);
            if (paperSizeField) paperSizeField.addEventListener('input', updatePreview);
        } else if (selectedCategory === 'Other Products') {
            activeField = document.getElementById('other-fields');
            // Update SKU info message for Other Products
            document.getElementById('sku-info-text').textContent = 'SKUs will be automatically generated from Brand + Product Type + Material + Color';
            
            // Add event listeners to Other Products fields for SKU preview
            const otherBrand = document.getElementById('other_brand');
            const productType = document.getElementById('product_type');
            const otherMaterial = document.getElementById('other_material');
            const otherColor = document.getElementById('other_color');
            
            if (otherBrand) otherBrand.addEventListener('input', updatePreview);
            if (productType) productType.addEventListener('change', updatePreview);
            if (otherMaterial) otherMaterial.addEventListener('input', updatePreview);
            if (otherColor) otherColor.addEventListener('input', updatePreview);
        }
        
        if (activeField) {
            activeField.style.display = 'block';
            activeField.style.position = 'static';
            activeField.style.left = 'auto';
            activeField.style.top = 'auto';
            
            // Restore required attribute for inputs in active category
            const requiredInputs = activeField.querySelectorAll('input[data-was-required], select[data-was-required], textarea[data-was-required]');
            requiredInputs.forEach(input => {
                input.setAttribute('required', 'required');
                input.removeAttribute('data-was-required');
            });
        }
    }
    
    // Check if category is a hidden input (from URL parameter)
    if (categorySelect && categorySelect.type === 'hidden') {
        // It's a hidden input with value from URL
        const categoryValue = categorySelect.value;
        console.log('Category is hidden input with value:', categoryValue);
        
        // Define required fields for each category
        const requiredFieldsMap = {
            'Shirt Products': ['brand', 'type', 'color'],
            'Other Products': ['other_brand', 'product_type', 'other_material', 'other_color'],
            'Machine and Equipments': ['machine_brand', 'machine_type', 'specifications'],
            'Garment Materials': ['material_name'],
            'Printing and Office Supplies': ['printing_product_type', 'paper_size']
        };
        
        // Mark fields that should be required
        categoryFields.forEach(field => {
            const categoryId = field.id.replace('-fields', '');
            let categoryName = '';
            
            switch(categoryId) {
                case 'shirt': categoryName = 'Shirt Products'; break;
                case 'other': categoryName = 'Other Products'; break;
                case 'machine': categoryName = 'Machine and Equipments'; break;
                case 'material': categoryName = 'Garment Materials'; break;
                case 'printing': categoryName = 'Printing and Office Supplies'; break;
            }
            
            if (categoryName && requiredFieldsMap[categoryName]) {
                requiredFieldsMap[categoryName].forEach(fieldName => {
                    const input = field.querySelector(`[name="${fieldName}"]`);
                    if (input) {
                        input.setAttribute('data-was-required', 'true');
                    }
                });
            }
        });
        
        // First hide ALL category fields (and move offscreen)
        categoryFields.forEach(field => {
            field.style.display = 'none';
            field.style.position = 'absolute';
            field.style.left = '-9999px';
            field.style.top = '-9999px';
        });
        
        // Then show appropriate fields based on category (and position normally)
        let activeField = null;
        
        if (categoryValue === 'Shirt Products') {
            activeField = document.getElementById('shirt-fields');
            // Update SKU info message for shirts
            document.getElementById('sku-info-text').textContent = 'SKUs will be automatically generated from Brand + Type + Color + Size';
            
            // Add event listeners to Shirt fields for SKU preview
            setTimeout(() => {
                const brand = document.getElementById('brand');
                const type = document.getElementById('type');
                const color = document.getElementById('color');
                
                if (brand) brand.addEventListener('input', updatePreview);
                if (type) type.addEventListener('input', updatePreview);
                if (color) color.addEventListener('input', updatePreview);
            }, 100);
            
            // Also trigger size selection section creation
            setTimeout(() => {
                if (typeof createSizeSelectionSection === 'function') {
                    createSizeSelectionSection();
                }
            }, 100);
        } else if (categoryValue === 'Other Products') {
            activeField = document.getElementById('other-fields');
            // Update SKU info message for Other Products
            document.getElementById('sku-info-text').textContent = 'SKUs will be automatically generated from Brand + Product Type + Material + Color';
            
            // Add event listeners to Other Products fields for SKU preview
            setTimeout(() => {
                const otherBrand = document.getElementById('other_brand');
                const productType = document.getElementById('product_type');
                const otherMaterial = document.getElementById('other_material');
                const otherColor = document.getElementById('other_color');
                
                if (otherBrand) otherBrand.addEventListener('input', updatePreview);
                if (productType) productType.addEventListener('change', updatePreview);
                if (otherMaterial) otherMaterial.addEventListener('input', updatePreview);
                if (otherColor) otherColor.addEventListener('input', updatePreview);
            }, 100);
        } else if (categoryValue === 'Machine and Equipments') {
            activeField = document.getElementById('machine-fields');
            document.getElementById('sku-info-text').textContent = 'SKUs will be automatically generated from Brand + Type + Specifications';
            
            // Add event listeners to Machine fields for SKU preview
            setTimeout(() => {
                const machineBrand = document.getElementById('machine_brand');
                const machineType = document.getElementById('machine_type');
                const specifications = document.getElementById('specifications');
                
                if (machineBrand) machineBrand.addEventListener('input', updatePreview);
                if (machineType) machineType.addEventListener('input', updatePreview);
                if (specifications) specifications.addEventListener('input', updatePreview);
            }, 100);
        } else if (categoryValue === 'Garment Materials') {
            activeField = document.getElementById('material-fields');
            document.getElementById('sku-info-text').textContent = 'SKU will be auto-generated from Brand + Material + Color + Item Name + Random suffix (ABC123). Or enter manual SKU below.';
            
            // Add event listeners to Garment Materials fields for SKU preview
            setTimeout(() => {
                const materialSku = document.getElementById('material_sku');
                const materialName = document.getElementById('material_name');
                
                if (materialSku) materialSku.addEventListener('input', updatePreview);
                if (materialName) materialName.addEventListener('input', updatePreview);
            }, 100);
        } else if (categoryValue === 'Printing and Office Supplies') {
            activeField = document.getElementById('printing-fields');
            document.getElementById('sku-info-text').textContent = 'SKUs will be automatically generated from Product Name + Type of Print + Paper Type + Size of Print + Random ABC123 suffix';
            
            // Add event listeners to Printing fields for SKU preview
            setTimeout(() => {
                const productName = document.getElementById('name');
                const printingProductType = document.getElementById('printing_product_type');
                const paperType = document.getElementById('paper_type');
                const paperSizeField = document.getElementById('paper_size');
                
                if (productName) productName.addEventListener('input', updatePreview);
                if (printingProductType) printingProductType.addEventListener('input', updatePreview);
                if (paperType) paperType.addEventListener('input', updatePreview);
                if (paperSizeField) paperSizeField.addEventListener('input', updatePreview);
            }, 100);
        }
        
        if (activeField) {
            activeField.style.display = 'block';
            activeField.style.position = 'static';
            activeField.style.left = 'auto';
            activeField.style.top = 'auto';
            
            // Restore required attribute for inputs in active category
            const requiredInputs = activeField.querySelectorAll('input[data-was-required], select[data-was-required], textarea[data-was-required]');
            requiredInputs.forEach(input => {
                input.setAttribute('required', 'required');
                input.removeAttribute('data-was-required');
            });
        }
    } 
    // If it's a select element, add event listener
    if (categorySelect && categorySelect.tagName === 'SELECT') {
        // Define required fields for each category
        const requiredFieldsMap = {
            'Shirt Products': ['brand', 'type', 'color'],
            'Other Products': ['other_brand', 'product_type', 'other_material', 'other_color'],
            'Machine and Equipments': ['machine_brand', 'machine_type', 'specifications'],
            'Garment Materials': ['material_name'],
            'Printing and Office Supplies': ['printing_product_type', 'paper_size']
        };
        
        // Mark fields that should be required
        categoryFields.forEach(field => {
            const categoryId = field.id.replace('-fields', '');
            let categoryName = '';
            
            switch(categoryId) {
                case 'shirt': categoryName = 'Shirt Products'; break;
                case 'other': categoryName = 'Other Products'; break;
                case 'machine': categoryName = 'Machine and Equipments'; break;
                case 'material': categoryName = 'Garment Materials'; break;
                case 'printing': categoryName = 'Printing and Office Supplies'; break;
            }
            
            if (categoryName && requiredFieldsMap[categoryName]) {
                requiredFieldsMap[categoryName].forEach(fieldName => {
                    const input = field.querySelector(`[name="${fieldName}"]`);
                    if (input) {
                        input.setAttribute('data-was-required', 'true');
                    }
                });
            }
        });
        
        // Initial show based on current selection
        showCategoryFields();
        
        // Update when category changes
        categorySelect.addEventListener('change', showCategoryFields);
    }
    // If it's a hidden input (from URL parameter), just show the appropriate fields
    else if (categorySelect && categorySelect.tagName === 'INPUT') {
        // The hidden input already has the value, so just show fields
        // This is handled by the code above that checks categorySelect.type === 'hidden'
    }
});

// ==================== BULK CREATION - SIZE SELECTION ====================

// Common sizes for selection - Complete shirt size range
const commonSizes = [
    // Numeric sizes
    '10', '12', '14', '16', '18', '20',
    // Letter sizes  
    'XS', 'S', 'M', 'L', 'XL', 
    '2XL', '3XL', '4XL', '5XL', '6XL', '7XL', '8XL'
];

// Generate SKU based on inputs (removing vowels like PHP controller)
function generateSKU() {
    // Get current category
    const categorySelect = document.getElementById('category');
    if (!categorySelect) return '';
    
    const currentCategory = categorySelect.value;
    
    // Function to remove vowels (matches PHP controller logic)
    const removeVowels = (text) => {
        // Remove vowels (both uppercase and lowercase)
        let result = text.replace(/[aeiouAEIOU]/g, '');
        // Remove spaces and special characters, keep only letters/numbers
        result = result.replace(/[^A-Z0-9]/gi, '');
        return result.toUpperCase();
    };
    
    const skuParts = [];
    
    // Different field mapping for each category
    if (currentCategory === 'Shirt Products') {
        const brand = document.getElementById('brand').value.trim();
        const type = document.getElementById('type').value.trim();
        const color = document.getElementById('color').value.trim();
        
        if (!brand && !type && !color) {
            return '';
        }
        
        if (brand) skuParts.push(removeVowels(brand));
        if (type) skuParts.push(removeVowels(type));
        if (color) skuParts.push(removeVowels(color));
        
    } else if (currentCategory === 'Other Products') {
        const brand = document.getElementById('other_brand').value.trim();
        const productType = document.getElementById('product_type').value.trim();
        const material = document.getElementById('other_material').value.trim();
        const color = document.getElementById('other_color').value.trim();
        
        if (!brand && !productType && !material && !color) {
            return '';
        }
        
        if (brand) skuParts.push(removeVowels(brand));
        if (productType) skuParts.push(removeVowels(productType));
        if (material) skuParts.push(removeVowels(material));
        if (color) skuParts.push(removeVowels(color));
        
    } else if (currentCategory === 'Machine and Equipments') {
        const brand = document.getElementById('machine_brand').value.trim();
        const machineType = document.getElementById('machine_type').value.trim();
        const specifications = document.getElementById('specifications').value.trim();
        
        if (!brand && !machineType && !specifications) {
            return '';
        }
        
        if (brand) skuParts.push(removeVowels(brand));
        if (machineType) skuParts.push(removeVowels(machineType));
        if (specifications) skuParts.push(removeVowels(specifications));
        
    } else if (currentCategory === 'Garment Materials') {
        // HYBRID SYSTEM: Auto-generated + Random suffix
        const materialBrand = document.getElementById('material_brand').value.trim();
        const materialType = document.getElementById('material_type').value.trim();
        const materialColor = document.getElementById('material_color').value.trim();
        const materialName = document.getElementById('material_name').value.trim();
        const manualSku = document.getElementById('material_sku').value.trim();
        
        // If manual SKU provided, use it directly
        if (manualSku) {
            return manualSku;
        }
        
        // Auto-generate from Brand + Material + Color + Item Name
        if (materialBrand) skuParts.push(removeVowels(materialBrand));
        if (materialType) skuParts.push(removeVowels(materialType));
        if (materialColor) skuParts.push(removeVowels(materialColor));
        if (materialName) skuParts.push(removeVowels(materialName));
        
        // Generate random suffix (ABC123 format)
        const randomLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const randomNumbers = '0123456789';
        
        let randomSuffix = '';
        // Add 3 random letters
        for (let i = 0; i < 3; i++) {
            randomSuffix += randomLetters.charAt(Math.floor(Math.random() * randomLetters.length));
        }
        // Add 3 random numbers
        for (let i = 0; i < 3; i++) {
            randomSuffix += randomNumbers.charAt(Math.floor(Math.random() * randomNumbers.length));
        }
        
        // Combine: BRAND-MATERIAL-COLOR-ITEMNAME-ABC123
        const baseSKU = skuParts.join('-');
        return baseSKU ? `${baseSKU}-${randomSuffix}` : randomSuffix;
        
    } else if (currentCategory === 'Printing and Office Supplies') {
        const productName = document.getElementById('name').value.trim();
        const printType = document.getElementById('printing_product_type').value.trim();
        const paperType = document.getElementById('paper_type').value.trim();
        const paperSize = document.getElementById('paper_size').value.trim();
        
        if (!productName && !printType && !paperType && !paperSize) {
            return '';
        }
        
        // Format: PRODUCTNAME-PRINTTYPE-PAPERTYPE-PAPERSIZE
        if (productName) skuParts.push(removeVowels(productName));
        if (printType) skuParts.push(removeVowels(printType));
        if (paperType) skuParts.push(removeVowels(paperType));
        if (paperSize) skuParts.push(removeVowels(paperSize));
        
        // Combine: ALL PARTS (controller will add random suffix)
        const baseSKU = skuParts.join('-');
        return baseSKU || '';
    }
    
    // Simple SKU format: PARTS (vowels removed)
    return skuParts.join('-');
}

// Update preview when inputs change
function updatePreview() {
    const baseSKU = generateSKU();
    const previewDiv = document.getElementById('sizePreview');
    
    // If no preview div exists, exit early
    if (!previewDiv) {
        console.warn('SKU preview div not found');
        return;
    }
    
    // Get current category
    const categorySelect = document.getElementById('category');
    if (!categorySelect) return;
    
    const currentCategory = categorySelect.value;
    
    if (!baseSKU) {
        let message = 'Enter required fields to see SKU preview';
        
        if (currentCategory === 'Shirt Products') {
            message = 'Enter brand, type, and color to see SKU preview';
        } else if (currentCategory === 'Other Products') {
            message = 'Enter brand, product type, material, and color to see SKU preview';
        } else if (currentCategory === 'Machine and Equipments') {
            message = 'Enter brand, type, and specifications to see SKU preview';
        } else if (currentCategory === 'Garment Materials') {
            message = 'Enter Brand, Material, Color, and Item Name to see auto-generated SKU with random suffix. Or enter manual SKU below.';
        } else if (currentCategory === 'Printing and Office Supplies') {
            message = 'Enter product name, type of print, paper type, and size of print to see SKU preview (random suffix added on save)';
        }
        
        previewDiv.innerHTML = `<div class="alert alert-warning">${message}</div>`;
        return;
    }
    
    // Check category - only show size table for Shirt Products
    if (currentCategory === 'Shirt Products') {
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
    } else {
        // For non-Shirt categories, show simple SKU preview
        previewDiv.innerHTML = `
            <div class="alert alert-success">
                <h6 class="mb-2">SKU Preview:</h6>
                <div class="d-flex align-items-center">
                    <code class="fs-5 me-3">${baseSKU}</code>
                    <span class="badge bg-info">Ready to Save</span>
                </div>
                <small class="text-muted mt-2 d-block">This SKU will be saved to inventory</small>
            </div>
        `;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check current category - only add size selection for Shirt Products
    const categorySelect = document.getElementById('category');
    if (!categorySelect) return;
    
    const currentCategory = categorySelect.value;
    
    // Only add size selection for Shirt Products
    if (currentCategory === 'Shirt Products') {
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
});
</script>
@endsection