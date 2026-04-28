@extends('layouts.app')

@section('title', 'Garment Printing Test - CLASS Apparel PH')

@push('styles')
<style>
    .test-badge { font-size: 0.7rem; vertical-align: middle; }
    .product-box {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .product-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }
    .selected-item-card {
        transition: all 0.2s ease;
        border: 1px solid #e0e0e0;
    }
    .selected-item-card:hover {
        border-color: #667eea;
    }
    .print-size-checkbox {
        display: flex;
        align-items: center;
        padding: 6px 10px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        margin-bottom: 4px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .print-size-checkbox:hover {
        background: #f0f7ff;
        border-color: #667eea;
    }
    .print-size-checkbox.selected {
        background: #e8f4fd;
        border-color: #0d6efd;
    }
    .print-size-checkbox input[type="checkbox"] {
        margin-right: 8px;
    }
    .print-size-price {
        margin-left: auto;
        font-weight: 600;
        color: #0d6efd;
    }
    /* Reference Image Drop Zone */
    .reference-drop-zone {
        border: 2px dashed #adb5bd;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fafafa;
        min-height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }
    .reference-drop-zone:hover,
    .reference-drop-zone.drag-over {
        border-color: #667eea;
        background: #f0f4ff;
    }
    .reference-drop-zone.drag-over {
        border-style: solid;
    }
    .reference-drop-zone.has-image {
        border-style: solid;
        border-color: #28a745;
    }
    .reference-previews-gallery {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    .reference-preview-container {
        position: relative;
        display: inline-block;
        max-width: 140px;
    }
    .reference-preview-container img {
        width: 100%;
        height: 100px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }
    .reference-remove-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #dc3545;
        color: #fff;
        border: none;
        font-size: 14px;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: all 0.15s ease;
    }
    .reference-remove-btn:hover {
        background: #c82333;
        transform: scale(1.1);
    }
</style>
@endpush

@section('content')

<script>
// ============================================
// GARMENT PRINTING TEST MODAL - CRITICAL FUNCTIONS
// Defined BEFORE HTML to be available for onclick
// ============================================

let selectedItems = [];
let currentProductType = '';
let currentDepartment = '';
let uploadedReferenceImages = [];
let referenceImageCounter = 0;

// Open product modal
window.openProductModal = function(productType) {
    console.log('Opening modal for:', productType);
    currentProductType = productType;
    currentDepartment = '';

    var modalTitle = document.getElementById('productModalLabel');
    if (modalTitle) {
        var titles = {
            'garment': 'Garment Printing',
            'tarpaulin': 'Tarpaulin Printing',
            'embroidery': 'Embroidery',
            'cutting': 'Cutting Services',
            'sewing': 'Sewing Services',
            'design': 'Design Services'
        };
        var icons = {
            'garment': 'fa-tshirt',
            'tarpaulin': 'fa-image',
            'embroidery': 'fa-thread',
            'cutting': 'fa-cut',
            'sewing': 'fa-sewing-machine',
            'design': 'fa-pencil-ruler'
        };
        modalTitle.innerHTML = '<i class="fas ' + icons[productType] + '"></i> ' + titles[productType];
    }

    loadProductsFromDatabase(productType);
    loadFilterOptions(productType);
    
    // Show/hide printing section based on product type
    var printingSection = document.getElementById('printingOptionsSection');
    if (printingSection) {
        if (productType === 'garment') {
            printingSection.style.display = 'block';
            populatePrintTypes();
        } else {
            printingSection.style.display = 'none';
        }
    }

    var modalEl = document.getElementById('productModal');
    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    } else {
        // Bootstrap not yet loaded, retry after a short delay
        setTimeout(function() {
            var el = document.getElementById('productModal');
            if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var m = new bootstrap.Modal(el);
                m.show();
            }
        }, 500);
    }
};

// Load products from API
window.loadProductsFromDatabase = function(productType) {
    const select = document.querySelector('.product-row .product-select');
    if (!select) return;

    select.innerHTML = '<option value="">Loading products...</option>';

    // Also show a loading indicator in the datalists
    var allSelects = document.querySelectorAll('.product-select');
    allSelects.forEach(function(s) {
        if (s !== select) s.innerHTML = '<option value="">Loading products...</option>';
    });

    fetch('/api/products-for-box/' + productType)
        .then(function(response) { return response.json(); })
        .then(function(products) {
            // Save all products for filter reference
            allLoadedProducts = products;
            // Populate product selects (all rows)
            allSelects.forEach(function(s) {
                populateProductSelect(s, products);
            });

            var qtyInputs = document.querySelectorAll('.product-quantity');
            qtyInputs.forEach(function(el) { el.value = '1'; });
            var priceDisplay = document.getElementById('productPriceDisplay');
            if (priceDisplay) priceDisplay.value = '₱0.00';
            var notes = document.getElementById('productNotes');
            if (notes) notes.value = '';
            var deptSelect = document.getElementById('departmentSelect');
            if (deptSelect) deptSelect.value = '';
            setupProductSelectListeners();
            updateModalOrderSummary();
        })
        .catch(function(error) {
            console.error('Error loading products:', error);
            allSelects.forEach(function(s) {
                s.innerHTML = '<option value="">Error loading products. Please try again.</option>';
            });
        });
};

// Populate a product select element with options
window.populateProductSelect = function(select, products) {
    select.innerHTML = '<option value="">Select product</option>';

    if (products.length === 0) {
        select.innerHTML = '<option value="">No products found</option>';
        return;
    }

    products.forEach(function(p) {
        var opt = document.createElement('option');
        opt.value = p.id;

        // Extract size from description if size column is null
        var productSize = p.size;
        if (!productSize && p.description) {
            var sizeMatch = p.description.match(/Size:\s*([^,]+)/i);
            if (sizeMatch) {
                productSize = sizeMatch[1].trim();
            }
        }

        // Build display text: Size: M RED BLACKHORSE ROUNDNECK - ₱110.00
        var displayText = '';

        if (productSize) {
            displayText += 'Size: ' + productSize;
        }

        if (p.color) {
            displayText += ' ' + p.color;
        }

        if (p.brand) {
            displayText += ' ' + p.brand;
        }

        // Try to extract shirt type from description
        var shirtType = p.shirt_type;
        if (!shirtType && p.description) {
            var typeMatch = p.description.match(/Type:\s*([^,]+)/i);
            if (typeMatch) {
                shirtType = typeMatch[1].trim();
            }
        }

        if (shirtType) {
            displayText += ' ' + shirtType;
        }

        // Add price at the end
        displayText += ' - ₱' + parseFloat(p.base_price).toFixed(2);

        opt.textContent = displayText;
        opt.dataset.price = p.base_price;
        opt.dataset.productName = p.name;
        opt.dataset.brand = p.brand || '';
        opt.dataset.size = productSize || '';
        opt.dataset.color = p.color || '';
        opt.dataset.volumeDiscounts = JSON.stringify(p.volume_discounts || []);
        opt.dataset.productData = JSON.stringify(p);
        select.appendChild(opt);
    });
};

// Setup product select change listeners
function setupProductSelectListeners() {
    document.querySelectorAll('.product-select').forEach(function(select) {
        select.addEventListener('change', function() {
            updateModalOrderSummary();
        });
    });
    document.querySelectorAll('.product-quantity').forEach(function(input) {
        input.addEventListener('input', function() {
            updateModalOrderSummary();
        });
    });
}

// Update modal order summary (inside the modal)
function updateModalOrderSummary() {
    var totalQty = 0;
    var totalAmount = 0;
    var unitPriceText = '₱0.00';
    var discountText = 'No bulk discount applied';

    document.querySelectorAll('.product-row').forEach(function(row) {
        var select = row.querySelector('.product-select');
        var qtyInput = row.querySelector('.product-quantity');

        if (select && select.value && qtyInput) {
            var opt = select.options[select.selectedIndex];
            var qty = parseInt(qtyInput.value) || 0;
            var price = parseFloat(opt.dataset.price) || 0;

            if (qty > 0 && price > 0) {
                totalQty += qty;

                // Check volume discounts
                var volumeDiscounts = opt.dataset.volumeDiscounts ? JSON.parse(opt.dataset.volumeDiscounts) : [];
                var basePrice = price;
                for (var di = 0; di < volumeDiscounts.length; di++) {
                    if (totalQty >= volumeDiscounts[di].min_quantity) {
                        price = volumeDiscounts[di].price_per_unit;
                        break;
                    }
                }
                if (price !== basePrice) {
                    var discPercent = ((basePrice - price) / basePrice) * 100;
                    discountText = 'Bulk discount: ' + discPercent.toFixed(1) + '% (Save: ₱' + ((basePrice - price) * qty).toFixed(2) + ')';
                }

                unitPriceText = '₱' + price.toFixed(2);
                totalAmount += qty * price;
            }
        }
    });

    var tqd = document.getElementById('totalQuantityDisplay');
    if (tqd) tqd.textContent = totalQty;
    var pd = document.getElementById('priceDisplay');
    if (pd) pd.textContent = unitPriceText;
    var dd = document.getElementById('discountDisplay');
    if (dd) {
        dd.textContent = discountText;
        dd.className = totalQty >= 10 ? 'text-success small' : 'text-muted small';
    }
    var itd = document.getElementById('itemTotalDisplay');
    if (itd) itd.textContent = '₱' + totalAmount.toFixed(2);
    var ppd = document.getElementById('productPriceDisplay');
    if (ppd) ppd.value = unitPriceText;

    updateProductsBreakdown();
    
    // Recalculate print pricing summary when quantity changes
    if (selectedPrintSizes && selectedPrintSizes.length > 0) {
        updatePrintSummary();
    }
}

// Update products breakdown in modal summary
function updateProductsBreakdown() {
    var container = document.getElementById('productsBreakdown');
    if (!container) return;

    var totalQty = 0;
    document.querySelectorAll('.product-row').forEach(function(row) {
        var qtyInput = row.querySelector('.product-quantity');
        if (qtyInput) totalQty += parseInt(qtyInput.value) || 0;
    });

    if (totalQty === 0) {
        container.innerHTML = '<div class="text-muted small mb-2">No products selected yet</div>';
        return;
    }

    var html = '<div class="small">';
    document.querySelectorAll('.product-row').forEach(function(row) {
        var select = row.querySelector('.product-select');
        var qtyInput = row.querySelector('.product-quantity');

        if (select && select.value && qtyInput) {
            var opt = select.options[select.selectedIndex];
            var qty = parseInt(qtyInput.value) || 0;
            var price = parseFloat(opt.dataset.price) || 0;
            if (qty > 0) {
                var name = opt.textContent.split(' - ')[0];
                var lineTotal = qty * price;
                html += '<div class="mb-1">' + qty + 'x ' + name + ' <span class="float-end">₱' + lineTotal.toFixed(2) + '</span></div>';
                html += '<div class="text-muted" style="font-size:10px;margin-top:-2px;margin-bottom:4px;">' + qty + ' × ₱' + price.toFixed(2) + ' = ₱' + lineTotal.toFixed(2) + '</div>';
            }
        }
    });
    html += '</div>';
    container.innerHTML = html;
}

// Handle image upload (shared for click, drop, paste) — appends to array
window.handleReferenceImage = function(file) {
    if (!file || !file.type || !file.type.startsWith('image/')) return;

    var reader = new FileReader();
    reader.onload = function(e) {
        var id = ++referenceImageCounter;
        uploadedReferenceImages.push({id: id, dataUrl: e.target.result});
        renderReferencePreviews();
    };
    reader.readAsDataURL(file);
};

// Render all reference image previews
window.renderReferencePreviews = function() {
    var gallery = document.getElementById('referencePreviewsGallery');
    if (!gallery) return;

    if (uploadedReferenceImages.length === 0) {
        gallery.innerHTML = '';
    } else {
        var html = '';
        for (var i = 0; i < uploadedReferenceImages.length; i++) {
            var img = uploadedReferenceImages[i];
            html += '<div class="reference-preview-container">'
                + '<button type="button" class="reference-remove-btn" onclick="removeReferenceImage(' + img.id + ')" title="Remove image">×</button>'
                + '<img src="' + img.dataUrl.replace(/"/g, '&quot;') + '" alt="Reference image">'
                + '</div>';
        }
        gallery.innerHTML = html;
    }
};

// Clear all reference images
window.clearAllReferenceImages = function() {
    uploadedReferenceImages = [];
    referenceImageCounter = 0;
    var gallery = document.getElementById('referencePreviewsGallery');
    if (gallery) gallery.innerHTML = '';
    var zone = document.getElementById('referenceDropZone');
    if (zone) zone.classList.remove('has-image');
};

// Remove a single reference image by id
window.removeReferenceImage = function(id) {
    for (var i = 0; i < uploadedReferenceImages.length; i++) {
        if (uploadedReferenceImages[i].id === id) {
            uploadedReferenceImages.splice(i, 1);
            break;
        }
    }
    renderReferencePreviews();
};

// Reset drop zone to default state
window.resetReferenceDropZone = function() {
    var zone = document.getElementById('referenceDropZone');
    if (!zone) return;
    zone.classList.remove('has-image');
};

// Handle paste from clipboard
window.onReferencePaste = function(e) {
    var items = (e.clipboardData || e.originalEvent.clipboardData || window.clipboardData).items;
    if (!items) return;
    for (var i = 0; i < items.length; i++) {
        if (items[i].type && items[i].type.startsWith('image/')) {
            var file = items[i].getAsFile();
            if (file) {
                window.handleReferenceImage(file);
                e.preventDefault();
                return;
            }
        }
    }
};

// Handle drag events
window.onReferenceDragOver = function(e) {
    e.preventDefault();
    e.currentTarget.classList.add('drag-over');
};

window.onReferenceDragLeave = function(e) {
    e.currentTarget.classList.remove('drag-over');
};

window.onReferenceDrop = function(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    var files = e.dataTransfer.files;
    if (files && files.length > 0) {
        for (var fi = 0; fi < files.length; fi++) {
            window.handleReferenceImage(files[fi]);
        }
    }
};

// Handle file picker click
window.onReferenceFilePickerChange = function(e) {
    var files = e.target.files;
    if (files && files.length > 0) {
        for (var fi = 0; fi < files.length; fi++) {
            window.handleReferenceImage(files[fi]);
        }
    }
    // Reset input so the same file can be re-selected
    e.target.value = '';
};

// Trigger hidden file input
window.triggerReferenceFilePicker = function() {
    var input = document.getElementById('referenceFileInput');
    if (input) input.click();
};

// Add item to cart
window.addItemToCart = function() {
    var deptSelect = document.getElementById('departmentSelect');
    var notes = document.getElementById('productNotes');

    if (!deptSelect) return;

    var rows = document.querySelectorAll('.product-row');
    var cartItems = [];
    var department = deptSelect.value;

    if (!department) {
        alert('Please select a department');
        deptSelect.focus();
        return;
    }

    var hasValidProducts = false;
    var validationError = false;

    rows.forEach(function(row, index) {
        if (validationError) return;

        var select = row.querySelector('.product-select');
        var qtyInput = row.querySelector('.product-quantity');

        if (select && qtyInput) {
            var selectedOption = select.options[select.selectedIndex];
            var quantity = parseInt(qtyInput.value) || 0;
            var price = selectedOption && selectedOption.value ? parseFloat(selectedOption.dataset.price) : 0;

            if (!select.value) {
                alert('Please select a product for row ' + (index + 1));
                select.focus();
                validationError = true;
                return;
            }

            if (quantity <= 0) {
                alert('Please enter a valid quantity for row ' + (index + 1));
                qtyInput.focus();
                validationError = true;
                return;
            }

            if (price <= 0) {
                alert('Invalid price for product in row ' + (index + 1));
                validationError = true;
                return;
            }

            var productName = selectedOption.dataset.productName || 'Unknown Product';
            var brand = selectedOption.dataset.brand || '';
            var size = selectedOption.dataset.size || '';
            var color = selectedOption.dataset.color || '';

            if (brand) productName += ' (' + brand + ')';
            if (size) productName += ' ' + size;
            if (color) productName += ' ' + color;

            // Get printing data if applicable
            var printData = null;
            var printType = document.getElementById('printTypeSelect');
            if (currentProductType === 'garment' && printType && printType.value && selectedPrintSizes.length > 0) {
                printData = {
                    print_type: printType.value,
                    print_sizes: selectedPrintSizes.slice(),
                    prices: printingData.prices || []
                };
            }

            var cartItem = {
                id: Date.now() + index,
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
                timestamp: new Date().toISOString(),
                printing: printData,
                referenceImages: uploadedReferenceImages
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

    cartItems.forEach(function(item) { selectedItems.push(item); });
    updateSelectedItemsDisplay();

    // Reset form
    initializeProductRows();
    if (deptSelect) deptSelect.value = '';
    if (notes) notes.value = '';
    
    // Reset printing options
    selectedPrintSizes = [];
    hidePrintingSections();
    var ptSelect = document.getElementById('printTypeSelect');
    if (ptSelect) ptSelect.value = '';
    
    // Clear reference images
    clearAllReferenceImages();

    // Close modal
    var modalEl = document.getElementById('productModal');
    if (modalEl) {
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }

    showToast('Successfully added ' + cartItems.length + ' product(s) to cart!', 'success');
};

// Initialize/Reset product rows
function initializeProductRows() {
    var container = document.getElementById('productRowsContainer');
    if (!container) return;

    var rows = container.querySelectorAll('.product-row');
    rows.forEach(function(row, idx) {
        if (idx > 0) row.remove();
    });

    var firstRow = container.querySelector('.product-row');
    if (firstRow) {
        var select = firstRow.querySelector('.product-select');
        var qty = firstRow.querySelector('.product-quantity');
        if (select) {
            select.innerHTML = '<option value="">Select a product</option>';
            if (currentProductType) {
                loadProductsForRow(select, currentProductType);
            }
        }
        if (qty) qty.value = '1';
    }

    var tqd = document.getElementById('totalQuantityDisplay');
    if (tqd) tqd.textContent = '0';
    var pd = document.getElementById('priceDisplay');
    if (pd) pd.textContent = '₱0.00';
    var itd = document.getElementById('itemTotalDisplay');
    if (itd) itd.textContent = '₱0.00';
    var ppd = document.getElementById('productPriceDisplay');
    if (ppd) ppd.value = '₱0.00';
    var pb = document.getElementById('productsBreakdown');
    if (pb) pb.innerHTML = '<div class="text-muted small mb-2">No products selected yet</div>';
    var dd = document.getElementById('discountDisplay');
    if (dd) {
        dd.textContent = 'No bulk discount applied';
        dd.className = 'text-muted small';
    }
}

// Load products for a specific row select
function loadProductsForRow(select, productType) {
    select.innerHTML = '<option value="">Loading...</option>';

    fetch('/api/products-for-box/' + productType)
        .then(function(response) { return response.json(); })
        .then(function(products) {
            allLoadedProducts = products;
            populateProductSelect(select, products);
            setupProductSelectListeners();
        })
        .catch(function(error) {
            console.error('Error loading products:', error);
            select.innerHTML = '<option value="">Error loading products</option>';
        });
}

// Update selected items display (main page cart view)
window.updateSelectedItemsDisplay = function() {
    var container = document.getElementById('selectedItemsContainer');
    if (!container) return;

    if (selectedItems.length === 0) {
        container.innerHTML = '<div class="alert alert-light text-center py-4"><i class="fas fa-shopping-cart fa-2x text-muted mb-3"></i><p class="mb-0">No items added yet. Click on a product box above to add items.</p></div>';
        return;
    }

    var html = '<div class="row g-3">';
    var deptNames = { 'iprint': 'iPrint', 'consol': 'Consol', 'cinco': 'Cinco', 'class': 'Class', 'mto': 'Made to Order', 'other': 'Other' };
    var deptColors = { 'iprint': 'primary', 'consol': 'info', 'cinco': 'warning', 'class': 'success', 'mto': 'danger', 'other': 'secondary' };

    selectedItems.forEach(function(item, index) {
        html += '<div class="col-md-6 col-lg-4">';
        html += '<div class="selected-item-card card">';
        html += '<div class="card-body">';
        html += '<div class="d-flex justify-content-between align-items-start mb-2">';
        html += '<h6 class="card-title mb-0">' + escapeHtml(item.name) + '</h6>';
        html += '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(' + index + ')"><i class="fas fa-times"></i></button>';
        html += '</div>';
        html += '<div class="mb-2">';
        html += '<span class="badge bg-' + deptColors[item.department] + ' item-department-badge">' + (deptNames[item.department] || item.department) + '</span>';
        html += '<span class="badge bg-light text-dark item-department-badge ms-1">' + item.productType + '</span>';
        html += '</div>';
        html += '<div class="d-flex justify-content-between mb-1"><span class="text-muted">Quantity:</span><span>' + item.quantity + '</span></div>';
        html += '<div class="d-flex justify-content-between mb-1"><span class="text-muted">Unit Price:</span><span>₱' + item.unitPrice.toFixed(2) + '</span></div>';
        html += '<div class="d-flex justify-content-between mb-2"><span class="text-muted">Total:</span><span class="fw-bold">₱' + item.totalPrice.toFixed(2) + '</span></div>';
        if (item.notes) {
            html += '<p class="mb-0 small text-muted"><i class="fas fa-sticky-note me-1"></i> ' + escapeHtml(item.notes) + '</p>';
        }
        html += '</div></div></div>';
    });

    html += '</div>';
    container.innerHTML = html;
    updateOrderSummaryDisplay();
};

// Escape HTML to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Remove item from cart
window.removeItem = function(index) {
    if (confirm('Remove this item from cart?')) {
        selectedItems.splice(index, 1);
        updateSelectedItemsDisplay();
        showToast('Item removed from cart', 'warning');
    }
};

// Update order summary display (main page)
function updateOrderSummaryDisplay() {
    var subtotal = 0;
    var deptTotals = {};
    var deptCount = {};

    selectedItems.forEach(function(item) {
        subtotal += item.totalPrice;
        if (!deptTotals[item.department]) {
            deptTotals[item.department] = 0;
            deptCount[item.department] = 0;
        }
        deptTotals[item.department] += item.totalPrice;
        deptCount[item.department] += 1;
    });

    var tax = subtotal * 0.12;

    var sd = document.getElementById('subtotalDisplay');
    if (sd) sd.textContent = '₱' + subtotal.toFixed(2);
    var td = document.getElementById('taxDisplay');
    if (td) td.textContent = '₱' + tax.toFixed(2);
    var totd = document.getElementById('totalDisplay');
    if (totd) totd.textContent = '₱' + (subtotal + tax).toFixed(2);

    updateDepartmentBreakdownDisplay(deptTotals, deptCount);
}

// Update department breakdown display
function updateDepartmentBreakdownDisplay(departmentTotals, departmentCount) {
    var container = document.getElementById('departmentBreakdown');
    if (!container) return;

    var keys = Object.keys(departmentTotals);
    if (keys.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0">No items assigned to departments yet.</p>';
        return;
    }

    var html = '<div class="row g-3">';
    var deptNames = { 'iprint': 'iPrint', 'consol': 'Consol', 'cinco': 'Cinco', 'class': 'Class', 'mto': 'Made to Order', 'other': 'Other' };
    var deptColors = { 'iprint': 'primary', 'consol': 'info', 'cinco': 'warning', 'class': 'success', 'mto': 'danger', 'other': 'secondary' };

    keys.forEach(function(deptCode) {
        var total = departmentTotals[deptCode];
        var count = departmentCount[deptCode] || 0;
        html += '<div class="col-md-6">';
        html += '<div class="card"><div class="card-body">';
        html += '<div class="d-flex justify-content-between align-items-center">';
        html += '<h6 class="card-title mb-0">';
        html += '<span class="badge bg-' + deptColors[deptCode] + ' me-2">' + (deptNames[deptCode] || deptCode) + '</span>';
        html += count + ' item' + (count !== 1 ? 's' : '');
        html += '</h6>';
        html += '<span class="fw-bold">₱' + total.toFixed(2) + '</span>';
        html += '</div></div></div></div>';
    });

    html += '</div>';
    container.innerHTML = html;
}

// Show toast notification
window.showToast = function(message, type) {
    if (!type) type = 'info';
    var container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    var toastId = 'toast-' + Date.now();
    var typeClasses = {
        'success': 'bg-success text-white',
        'warning': 'bg-warning text-dark',
        'error': 'bg-danger text-white',
        'info': 'bg-info text-white'
    };

    var toast = document.createElement('div');
    toast.id = toastId;
    toast.className = 'toast ' + (typeClasses[type] || typeClasses.info) + ' border-0';
    toast.style.minWidth = '300px';
    toast.style.marginBottom = '10px';

    toast.innerHTML = '<div class="toast-body"><div class="d-flex justify-content-between align-items-center"><span>' + message + '</span><button type="button" class="btn-close btn-close-white" onclick="document.getElementById(\'' + toastId + '\').remove()"></button></div></div>';

    container.appendChild(toast);
    setTimeout(function() {
        var el = document.getElementById(toastId);
        if (el) el.remove();
    }, 3000);
};

// Export test data to console
window.exportTestData = function() {
    console.log('=== GARMENT TEST PAGE DATA ===');
    console.log('Selected Items:', JSON.stringify(selectedItems, null, 2));
    console.log('Total Items:', selectedItems.length);
    console.log('==============================');
    alert('Data logged to browser console. Press F12 > Console to view.');
};

// ======================
// FILTER SYSTEM
// ======================

// Load filter options when modal opens
window.loadFilterOptions = function(productType) {
    fetch('/api/filter-options/' + productType)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            // Populate Brand datalist
            var brandDatalist = document.getElementById('brandOptions');
            if (brandDatalist && data.brands) {
                brandDatalist.innerHTML = '';
                data.brands.forEach(function(brand) {
                    var opt = document.createElement('option');
                    opt.value = brand;
                    brandDatalist.appendChild(opt);
                });
            }

            // Populate Type datalist
            var typeDatalist = document.getElementById('typeOptions');
            if (typeDatalist && data.types) {
                typeDatalist.innerHTML = '';
                data.types.forEach(function(type) {
                    var opt = document.createElement('option');
                    opt.value = type;
                    typeDatalist.appendChild(opt);
                });
            }

            // Populate Color datalist
            var colorDatalist = document.getElementById('colorOptions');
            if (colorDatalist && data.colors) {
                colorDatalist.innerHTML = '';
                data.colors.forEach(function(color) {
                    var opt = document.createElement('option');
                    opt.value = color;
                    colorDatalist.appendChild(opt);
                });
            }
        })
        .catch(function(error) {
            console.error('Error loading filter options:', error);
        });
};

// Apply filters — reloads products from server with filter params
window.applyFilters = function() {
    var brand = document.getElementById('filterBrand') ? document.getElementById('filterBrand').value.trim() : '';
    var type = document.getElementById('filterType') ? document.getElementById('filterType').value.trim() : '';
    var color = document.getElementById('filterColor') ? document.getElementById('filterColor').value.trim() : '';

    // Show loading in all dropdowns
    document.querySelectorAll('.product-select').forEach(function(dropdown) {
        dropdown.innerHTML = '<option value="">Applying filters...</option>';
    });

    // Load products with filters from SERVER
    loadProductsWithFilters(currentProductType, brand, type, color);
};

// Reset filters
window.resetFilters = function() {
    document.getElementById('filterBrand').value = '';
    document.getElementById('filterType').value = '';
    document.getElementById('filterColor').value = '';

    // Show loading in all dropdowns
    document.querySelectorAll('.product-select').forEach(function(dropdown) {
        dropdown.innerHTML = '<option value="">Resetting filters...</option>';
    });

    // Reload all products
    loadProductsFromDatabase(currentProductType);
};

// Load products with filter parameters from server
window.loadProductsWithFilters = function(productType, brand, type, color) {
    var allDropdowns = document.querySelectorAll('.product-select');
    if (allDropdowns.length === 0) return;

    // Build query parameters
    var params = new URLSearchParams();
    if (brand) params.append('brand', brand);
    if (type) params.append('type', type);
    if (color) params.append('color', color);

    fetch('/api/products-for-box/' + productType + '?' + params.toString())
        .then(function(response) { return response.json(); })
        .then(function(products) {
            // Update ALL dropdowns
            allDropdowns.forEach(function(dropdown) {
                populateProductSelect(dropdown, products);
            });

            setupProductSelectListeners();
            updateModalOrderSummary();

            if (products.length === 0) {
                showToast('No products match your filters. Try different filters.', 'warning');
            }
        })
        .catch(function(error) {
            console.error('Error loading filtered products:', error);
            allDropdowns.forEach(function(dropdown) {
                dropdown.innerHTML = '<option value="">Error loading products. Please try again.</option>';
            });
        });
};

// ============================================
// PRINTING OPTIONS SYSTEM
// ============================================

let printingData = {};        // Cached printing options
let selectedPrintSizes = [];  // Currently checked print size IDs

// Populate print type dropdown (called when modal opens for garment)
function populatePrintTypes() {
    var select = document.getElementById('printTypeSelect');
    if (!select) return;
    
    // Reset
    select.innerHTML = '<option value="">-- Select Print Type --</option>';
    select.value = '';
    hidePrintingSections();
    
    // Setup change handler (avoids duplicate listeners)
    select.onchange = function() {
        selectedPrintSizes = [];
        var val = this.value;
        if (val) {
            loadPrintingOptions(val);
        } else {
            hidePrintingSections();
        }
    };
    
    // Load DTF first to get print types list
    fetch('/api/printing/options/dtf')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            printingData = data;
            var types = data.print_types || [];
            types.forEach(function(t) {
                var opt = document.createElement('option');
                opt.value = t;
                var label = t.charAt(0).toUpperCase() + t.slice(1) + ' Print';
                opt.textContent = label;
                select.appendChild(opt);
            });
        })
        .catch(function(err) {
            console.error('Error loading print types:', err);
        });
}

// Hide all printing sections
function hidePrintingSections() {
    var sizes = document.getElementById('printSizesContainer');
    var sidebar = document.getElementById('printSummarySidebar');
    var qtySection = document.getElementById('printQuantitySection');
    if (sizes) sizes.style.display = 'none';
    if (sidebar) sidebar.innerHTML = '';
    if (qtySection) qtySection.style.display = 'none';
}

// Load printing options (called when print type changes)
function loadPrintingOptions(printType) {
    if (!printType) {
        hidePrintingSections();
        return;
    }
    
    fetch('/api/printing/options/' + printType)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            printingData = data;
            renderPrintSizes(data.prices);
            document.getElementById('printSizesContainer').style.display = 'block';
            selectedPrintSizes = [];
            updatePrintSummary();
        })
        .catch(function(err) {
            console.error('Error loading print options:', err);
            document.getElementById('printSizesList').innerHTML = '<div class="text-danger small">Failed to load print options.</div>';
        });
}

// Render print size checkboxes
function renderPrintSizes(prices) {
    var container = document.getElementById('printSizesList');
    if (!container) return;
    
    if (!prices || prices.length === 0) {
        container.innerHTML = '<div class="text-muted small">No print sizes available for this type.</div>';
        return;
    }
    
    var html = '';
    prices.forEach(function(p) {
        html += '<label class="print-size-checkbox">';
        html += '<input type="checkbox" value="' + p.id + '" onchange="onPrintSizeChange(' + p.id + ', this)">';
        html += '<span>' + p.name + '</span>';
        html += '<span class="print-size-price">₱' + p.price.toFixed(2) + '</span>';
        html += '</label>';
    });
    container.innerHTML = html;
}

// Handle print size checkbox change
function onPrintSizeChange(sizeId, checkbox) {
    var labelEl = checkbox.closest('label');
    if (checkbox.checked) {
        if (labelEl) labelEl.classList.add('selected');
        if (selectedPrintSizes.indexOf(sizeId) === -1) {
            selectedPrintSizes.push(sizeId);
        }
    } else {
        if (labelEl) labelEl.classList.remove('selected');
        var idx = selectedPrintSizes.indexOf(sizeId);
        if (idx !== -1) {
            selectedPrintSizes.splice(idx, 1);
        }
    }
    
    updatePrintSummary();
}

// Update print summary (combo, bulk, totals)
function updatePrintSummary() {
    var sidebarContainer = document.getElementById('printSummarySidebar');
    var qtySection = document.getElementById('printQuantitySection');
    
    if (selectedPrintSizes.length === 0) {
        if (sidebarContainer) sidebarContainer.innerHTML = '';
        if (qtySection) qtySection.style.display = 'none';
        return;
    }
    
    if (qtySection) qtySection.style.display = 'block';
    
    var prices = printingData.prices || [];
    var combos = printingData.combos || [];
    var bulkTiers = printingData.bulk_tiers || [];
    
    // Get manual print quantity from input
    var qtyInputEl = document.getElementById('printQuantityInput');
    var totalQty = qtyInputEl ? parseInt(qtyInputEl.value) || 1 : 1;
    
    // Calculate print cost per item
    var printCostPerItem = 0;
    var selectedSizeDetails = [];
    
    selectedPrintSizes.forEach(function(sizeId) {
        var found = null;
        for (var i = 0; i < prices.length; i++) {
            if (prices[i].id === sizeId) {
                found = prices[i];
                break;
            }
        }
        if (found) {
            printCostPerItem += found.price;
            selectedSizeDetails.push(found);
        }
    });
    
    // Calculate combo discount - only the highest discount applies
    var comboDiscount = 0;
    var comboDetails = [];
    combos.forEach(function(c) {
        if (selectedPrintSizes.indexOf(c.size1_id) !== -1 && 
            selectedPrintSizes.indexOf(c.size2_id) !== -1) {
            comboDetails.push(c);
        }
    });
    // Sort by discount descending and take only the highest
    if (comboDetails.length > 0) {
        comboDetails.sort(function(a, b) { return b.discount - a.discount; });
        var best = comboDetails[0];
        comboDiscount = best.discount;
        comboDetails = [best];
    }
    
    var printCostAfterCombo = printCostPerItem - comboDiscount;
    var subtotal = printCostAfterCombo * totalQty;
    
    // Calculate bulk discount
    var transactionCount = totalQty;
    var bulkDiscount = 0;
    var bulkLabel = '';
    
    // Sort bulk tiers by min (they should already be sorted from server)
    for (var bi = bulkTiers.length - 1; bi >= 0; bi--) {
        var tier = bulkTiers[bi];
        if (transactionCount >= tier.min && transactionCount <= tier.max) {
            if (tier.type === 'percentage') {
                bulkDiscount = subtotal * (tier.percent / 100);
                bulkLabel = tier.label;
            } else if (tier.type === 'fixed') {
                // Fixed amount per item
                bulkDiscount = tier.amount * transactionCount;
                bulkLabel = tier.label;
            }
            break;
        }
    }
    
    var total = subtotal - bulkDiscount;
    
    // Render sizes breakdown
    var breakdownHtml = '';
    selectedSizeDetails.forEach(function(s) {
        breakdownHtml += '<div class="d-flex justify-content-between">';
        breakdownHtml += '<span>' + s.name + '</span>';
        breakdownHtml += '<span>₱' + s.price.toFixed(2) + '</span>';
        breakdownHtml += '</div>';
    });
    // Render compact sidebar version in right column
    if (sidebarContainer) {
        var sidebarHtml = '';
        selectedSizeDetails.forEach(function(s) {
            sidebarHtml += '<div class="d-flex justify-content-between">';
            sidebarHtml += '<span>' + s.name + '</span>';
            sidebarHtml += '<span>₱' + s.price.toFixed(2) + '</span>';
            sidebarHtml += '</div>';
        });
        if (comboDetails.length > 0) {
            sidebarHtml += '<div class="d-flex justify-content-between text-success mt-1">';
            sidebarHtml += '<span>Combo: ' + comboDetails[0].size1_name + ' + ' + comboDetails[0].size2_name + '</span>';
            sidebarHtml += '<span>-₱' + comboDetails[0].discount.toFixed(2) + '</span>';
            sidebarHtml += '</div>';
        }
        sidebarHtml += '<div class="d-flex justify-content-between mt-1">';
        sidebarHtml += '<span class="text-muted">Print Cost/ea:</span>';
        sidebarHtml += '<span>₱' + printCostAfterCombo.toFixed(2) + '</span>';
        sidebarHtml += '</div>';
        sidebarHtml += '<div class="d-flex justify-content-between">';
        sidebarHtml += '<span class="text-muted">Qty:</span>';
        sidebarHtml += '<span>' + totalQty + '</span>';
        sidebarHtml += '</div>';
        sidebarHtml += '<div class="d-flex justify-content-between">';
        sidebarHtml += '<span class="text-muted">Print Subtotal:</span>';
        sidebarHtml += '<span>₱' + subtotal.toFixed(2) + '</span>';
        sidebarHtml += '</div>';
        if (bulkDiscount > 0) {
            sidebarHtml += '<div class="d-flex justify-content-between text-danger mt-1">';
            sidebarHtml += '<span>Bulk (' + bulkLabel + ')</span>';
            sidebarHtml += '<span>-₱' + bulkDiscount.toFixed(2) + '</span>';
            sidebarHtml += '</div>';
        }
        sidebarHtml += '<hr class="my-1">';
        sidebarHtml += '<div class="d-flex justify-content-between fw-bold">';
        sidebarHtml += '<span>Print Total</span>';
        sidebarHtml += '<span>₱' + total.toFixed(2) + '</span>';
        sidebarHtml += '</div>';
        
        // Get product total from the order summary
        var productTotal = 0;
        var totalDisplay = document.getElementById('itemTotalDisplay');
        if (totalDisplay) {
            productTotal = parseFloat(totalDisplay.textContent.replace(/₱/g, '').replace(/,/g, '')) || 0;
        }
        var grandTotal = productTotal + total;
        
        sidebarHtml += '<div class="d-flex justify-content-between fw-bold mt-2 pt-1 border-top border-2">';
        sidebarHtml += '<span class="text-primary">Grand Total</span>';
        sidebarHtml += '<span class="text-primary">₱' + grandTotal.toFixed(2) + '</span>';
        sidebarHtml += '</div>';
        sidebarContainer.innerHTML = sidebarHtml;
    }
}


</script>

@section('content')
<div class="container-fluid">
    <!-- Top bar with TEST mode indicator -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">
                        <i class="fas fa-flask text-warning me-2"></i>
                        Garment Printing Modal Test
                    </h4>
                </div>
                <div class="col-auto">
                    <span class="badge bg-warning text-dark px-3 py-2">
                        🧪 TEST MODE
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="exportTestData()">
                        <i class="fas fa-download me-1"></i> Export Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Boxes Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-4">
                <!-- Box: Garment Printing -->
                <div class="col-md-4">
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

                <!-- Box: Tarpaulin -->
                <div class="col-md-4">
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

                <!-- Box: Embroidery -->
                <div class="col-md-4">
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

                <!-- Box: Cutting -->
                <div class="col-md-4">
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

                <!-- Box: Sewing -->
                <div class="col-md-4">
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

                <!-- Box: Design -->
                <div class="col-md-4">
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
        </div>
    </div>

    <!-- Selected Items Section -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-shopping-cart me-2"></i>
                Selected Items
            </h5>
        </div>
        <div class="card-body">
            <div id="selectedItemsContainer">
                <div class="alert alert-light text-center py-4">
                    <i class="fas fa-shopping-cart fa-2x text-muted mb-3"></i>
                    <p class="mb-0">No items added yet. Click on a product box above to add items.</p>
                </div>
            </div>

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
                    <div class="card bg-light">
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
                                <span id="totalDisplay" class="text-primary">₱0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                                        <datalist id="brandOptions"></datalist>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">Size</label>
                                        <input type="text" class="form-control form-control-sm" id="filterType" list="typeOptions" placeholder="Type or select size...">
                                        <datalist id="typeOptions"></datalist>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1">Color</label>
                                        <input type="text" class="form-control form-control-sm" id="filterColor" list="colorOptions" placeholder="Type or select color...">
                                        <datalist id="colorOptions"></datalist>
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

                        <!-- PRODUCT ROWS SECTION -->
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

                        <!-- PRINTING OPTIONS SECTION -->
                        <div class="card mb-3 border-primary" id="printingOptionsSection" style="display:none;">
                            <div class="card-header bg-primary bg-opacity-10 py-2">
                                <h6 class="mb-0"><i class="fas fa-print me-1"></i> Printing Options</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-3">
                                    <label class="form-label">Type of Print</label>
                                    <select class="form-control" id="printTypeSelect">
                                        <option value="">-- Select Print Type --</option>
                                    </select>
                                    <div class="form-text">Select printing method for the selected products.</div>
                                </div>

                                <div id="printSizesContainer" style="display:none;">
                                    <label class="form-label">Print Sizes</label>
                                    <div id="printSizesList" class="mb-2"></div>
                                    <div class="form-text">Check all print sizes needed. Multiple selections apply combo discounts.</div>
                                </div>

                                <div class="mb-3" id="printQuantitySection" style="display:none;">
                                    <label class="form-label small mb-1">Print Quantity:</label>
                                    <input type="number" class="form-control" id="printQuantityInput" value="1" min="1" onchange="updatePrintSummary()" oninput="updatePrintSummary()">
                                </div>

                                <!-- REFERENCE IMAGE UPLOAD -->
                                <div class="mt-3">
                                    <label class="form-label">Reference Image <span class="text-muted">(optional)</span></label>
                                    <div class="form-text small mb-2">Attach a reference image for print placement or design.</div>
                                    <div id="referenceDropZone"
                                         class="reference-drop-zone"
                                         onclick="triggerReferenceFilePicker()"
                                         onpaste="onReferencePaste(event)"
                                         ondragover="onReferenceDragOver(event)"
                                         ondragleave="onReferenceDragLeave(event)"
                                         ondrop="onReferenceDrop(event)">
                                        <div class="mb-1"><i class="fas fa-image fa-2x text-muted"></i></div>
                                        <div>Drop reference image here, click to browse, or paste (Ctrl+V)</div>
                                    </div>
                                    <input type="file" id="referenceFileInput" accept="image/*" style="display:none" onchange="onReferenceFilePickerChange(event)">
                                    <div id="referencePreviewsGallery" class="reference-previews-gallery"></div>
                                </div>
                            </div>
                        </div>
                        <!-- END PRINTING OPTIONS SECTION -->

                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="productNotes" rows="3" placeholder="Add special instructions, colors, sizes, etc."></textarea>
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
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Order Summary</h6>

                                <div id="productsBreakdown" class="mb-3">
                                    <div class="text-muted small mb-2">No products selected yet</div>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Quantity:</span>
                                    <span id="totalQuantityDisplay">0</span>
                                </div>
                                <!-- Unit Price and Discount removed per Andrew's request -->
                                <hr>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total Amount:</span>
                                    <span id="itemTotalDisplay">₱0.00</span>
                                </div>

                                <div id="printSummarySidebar" class="mt-3 small">
                                    <!-- Populated by updatePrintSummary() -->
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add product row button
    var addRowBtn = document.getElementById('addProductRow');
    if (addRowBtn) {
        addRowBtn.addEventListener('click', function() {
            var container = document.getElementById('productRowsContainer');
            var template = container.querySelector('.product-row-template');
            if (!template) return;

            var newRow = template.cloneNode(true);
            newRow.classList.remove('product-row-template', 'd-none');
            newRow.classList.add('product-row');

            var select = newRow.querySelector('.product-select');
            var qty = newRow.querySelector('.product-quantity');
            if (select) {
                select.innerHTML = '<option value="">Select a product</option>';
                if (currentProductType) {
                    loadProductsForRow(select, currentProductType);
                }
            }
            if (qty) qty.value = '1';

            var removeBtn = newRow.querySelector('.remove-row');
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    newRow.remove();
                    updateModalOrderSummary();
                });
            }

            container.appendChild(newRow);
            setupProductSelectListeners();
            updateModalOrderSummary();
        });
    }

    // Product row remove buttons (existing)
    document.querySelectorAll('#productRowsContainer .remove-row').forEach(function(btn) {
        btn.addEventListener('click', function(ev) {
            var row = this.closest('.product-row');
            if (row) {
                row.remove();
                updateModalOrderSummary();
            }
        });
    });

    // Add item button
    var addBtn = document.getElementById('addItemBtn');
    if (addBtn) {
        addBtn.addEventListener('click', addItemToCart);
    }

    setupProductSelectListeners();
    updateModalOrderSummary();
});
</script>
@endpush
