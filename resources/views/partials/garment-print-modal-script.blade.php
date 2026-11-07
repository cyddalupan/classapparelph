{{-- SHARED: exact garment printing modal JS from Create Sale (single source of truth) --}}
/* ===================================================================
   SHARED GARMENT PRINTING MODAL — dependencies
   (guarded: kung defined na sa page, huwag nang i-overwrite)
   =================================================================== */

window.garment_buildProductOption = window.garment_buildProductOption || function(p) {
    var productSize = p.size;
    if (!productSize && p.description) {
        var sizeMatch = p.description.match(/Size:\s*([^,]+)/i);
        if (sizeMatch) productSize = sizeMatch[1].trim();
    }
    var displayText = '';
    if (productSize) displayText += 'Size: ' + productSize;
    if (p.color) displayText += ' ' + p.color;
    if (p.brand) displayText += ' ' + p.brand;
    var shirtType = p.shirt_type;
    if (!shirtType && p.description) {
        var typeMatch = p.description.match(/Type:\s*([^,]+)/i);
        if (typeMatch) shirtType = typeMatch[1].trim();
    }
    if (shirtType) displayText += ' ' + shirtType;
    displayText += ' - \u20B1' + parseFloat(p.base_price).toFixed(2);
    return displayText;
};

window.loadProductsFromDatabase = window.loadProductsFromDatabase || function(productType) {
    // For garment, populate the multiple product selects (in garment modal body)
    // For other types, populate the single product select (in generic modal body)
    
    // Build display text for a product option
    function buildProductOption(p) {
        let productSize = p.size;
        if (!productSize && p.description) {
            const sizeMatch = p.description.match(/Size:\s*([^,]+)/i);
            if (sizeMatch) productSize = sizeMatch[1].trim();
        }
        let displayText = '';
        if (productSize) displayText += 'Size: ' + productSize;
        if (p.color) displayText += ' ' + p.color;
        if (p.brand) displayText += ' ' + p.brand;
        let shirtType = p.shirt_type;
        if (!shirtType && p.description) {
            const typeMatch = p.description.match(/Type:\s*([^,]+)/i);
            if (typeMatch) shirtType = typeMatch[1].trim();
        }
        if (shirtType) displayText += ' ' + shirtType;
        displayText += ' - \u20B1' + parseFloat(p.base_price).toFixed(2);
        return { displayText: displayText, productSize: productSize, shirtType: shirtType };
    }
    
    // Populate a single select element with products
    function populateSelect(selectEl, products) {
        if (!selectEl) return;
        selectEl.innerHTML = '<option value="">Select product</option>';
        if (products.length === 0) {
            selectEl.innerHTML = '<option value="">No products found.</option>';
            return;
        }
        products.forEach(function(p) {
            var info = buildProductOption(p);
            var opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = info.displayText;
            opt.dataset.price = p.base_price;
            opt.dataset.productName = p.name;
            opt.dataset.brand = p.brand || '';
            opt.dataset.size = info.productSize || '';
            opt.dataset.color = p.color || '';
            opt.dataset.volumeDiscounts = JSON.stringify(p.volume_discounts);
            opt.dataset.productData = JSON.stringify(p);
            selectEl.appendChild(opt);
        });
    }
    
    // Determine which selects to populate
    var selects = [];
    if (productType === 'garment') {
        // Populate all garment product rows
        selects = document.querySelectorAll('#garment_productRowsContainer .product-select');
    } else {
        // Populate the single product select in generic body
        var singleSelect = document.getElementById('productSelect');
        if (singleSelect) selects = [singleSelect];
    }
    
    if (selects.length === 0) return;
    
    // Locked rows (may napili nang product) ay hindi ginagalaw ng reload/filter.
    var targets = [];
    selects.forEach(function(s) { if (!s.value) targets.push(s); });
    window.__capPrevSel = null;
    if (targets.length === 0) {
        if (typeof showToast === 'function') showToast('Naka-lock lahat ng napiling product — i-clear muna para ma-apply ang filter.', 'info');
        return;
    }

    // Show loading
    targets.forEach(function(s) { s.innerHTML = '<option value="">Loading products...</option>'; });
    
    // Fetch products from API
    fetch('/api/products-for-box/' + productType)
        .then(function(response) { return response.json(); })
        .then(function(products) {
            targets.forEach(function(s) { populateSelect(s, products); });
            window.cap_lastOptionsHtml = targets.length ? targets[0].innerHTML : null;
            
            if (productType === 'garment') {
                // Reset qty and department for garment
                // I-reset ang qty ng WALANG napiling product lang (locked rows: huwag galawin)
                document.querySelectorAll('#garment_productRowsContainer .product-row').forEach(function(rw) {
                    var sl = rw.querySelector('.product-select');
                    var qi = rw.querySelector('.product-quantity');
                    if (sl && !sl.value && qi) qi.value = '1';
                });
                /* Department auto-assigned; no reset needed */
                var notes = document.getElementById('garment_productNotes');
                if (notes) notes.value = '';
                // Recalculate garment summary
                garment_updatePrintSummary();
            } else {
                // Reset form for non-garment
                const qty = document.getElementById('productQuantity');
                const price = document.getElementById('productPrice');
                const notes = document.getElementById('productNotes');
                if (qty) qty.value = '1';
                if (price) price.value = '';
                if (notes) notes.value = '';
                /* Department auto-assigned; no manual select needed */
                
                // Auto-fill price on select
                if (selects[0]) {
                    selects[0].addEventListener('change', function() {
                        const opt = this.options[this.selectedIndex];
                        if (opt.value && opt.dataset.price && price) {
                            price.value = opt.dataset.price;
                            calculateItemTotal();
                        }
                    });
                }
                calculateItemTotal();
            }
        })
        .catch(function(error) {
            console.error('Error loading products:', error);
            targets.forEach(function(s) {
                s.innerHTML = '<option value="">Error loading products. Please try again.</option>';
            });
        });
};

    window.garment_loadFilterOptions = window.garment_loadFilterOptions || function(productType) {
        fetch('/api/filter-options/' + productType)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                ['brand', 'type', 'color', 'size', 'material'].forEach(function(type) {
                    var datalist = document.getElementById('garment_' + type + 'Options');
                    var field = document.getElementById('garment_filter' + type.charAt(0).toUpperCase() + type.slice(1));
                    if (datalist) {
                        datalist.innerHTML = '';
                        var items = data[type + 's'] || data[type] || [];
                        if (items.length) {
                            items.forEach(function(item) {
                                var opt = document.createElement('option');
                                opt.value = item;
                                datalist.appendChild(opt);
                            });
                            if (field) field.placeholder = 'Type or select ' + type + '...';
                        }
                    }
                });
            })
            .catch(function(err) {
                console.error('Error loading garment filter options:', err);
            });
    };
    
    window.garment_applyFilters = window.garment_applyFilters || function() {
        var brand = document.getElementById('garment_filterBrand')?.value.trim() || '';
        var type = document.getElementById('garment_filterType')?.value.trim() || '';
        var color = document.getElementById('garment_filterColor')?.value.trim() || '';
        var size = document.getElementById('garment_filterSize')?.value.trim() || '';
        var material = document.getElementById('garment_filterMaterial')?.value.trim() || '';
        
        // Locked rows (may napili) = hindi gagalawin. Empty rows lang ang fi-fill.
        var selects = document.querySelectorAll('#garment_productRowsContainer .product-select');
        var targets = [];
        selects.forEach(function(d) { if (!d.value) targets.push(d); });
        if (targets.length === 0) {
            if (typeof showToast === 'function') showToast('Naka-lock lahat ng napiling product — i-clear muna para ma-apply ang filter.', 'info');
            return;
        }
        targets.forEach(function(dropdown) {
            dropdown.innerHTML = '<option value="">Applying filters...</option>';
        });
        
        var params = new URLSearchParams();
        if (brand) params.set('brand', brand);
        if (type) params.set('type', type);
        if (color) params.set('color', color);
        if (size) params.set('size', size);
        if (material) params.set('material', material);
        
        fetch('/api/products-for-box/' + currentProductType + '?' + params.toString())
            .then(function(r) { return r.json(); })
            .then(function(products) {
                targets.forEach(function(dropdown) {
                    dropdown.innerHTML = '<option value="">Select product</option>';
                    products.forEach(function(p) {
                        var opt = document.createElement('option');
                        opt.value = p.id;
                        opt.textContent = window.garment_buildProductOption(p);
                        opt.dataset.price = p.base_price;
                        opt.dataset.brand = p.brand || '';
                        var pSize = p.size;
                        if (!pSize && p.description) {
                            var sizeM = p.description.match(/Size:\s*([^,]+)/i);
                            if (sizeM) pSize = sizeM[1].trim();
                        }
                        opt.dataset.size = pSize || '';
                        opt.dataset.color = p.color || '';
                        opt.dataset.productName = p.name || '';
                        opt.dataset.productData = JSON.stringify(p);
                        dropdown.appendChild(opt);
                    });
                    if (products.length === 0) {
                        dropdown.innerHTML = '<option value="">No products match your filters</option>';
                    }
                });
                window.cap_lastOptionsHtml = targets.length ? targets[0].innerHTML : null;
                garment_updatePrintSummary();
            })
            .catch(function() {
                targets.forEach(function(d) { d.innerHTML = '<option value="">Error loading</option>'; });
            });
    };
    
    window.garment_resetFilters = window.garment_resetFilters || function() {
        document.getElementById('garment_filterBrand').value = '';
        document.getElementById('garment_filterType').value = '';
        document.getElementById('garment_filterColor').value = '';
        var sizeEl = document.getElementById('garment_filterSize');
        if (sizeEl) sizeEl.value = '';
        var matEl = document.getElementById('garment_filterMaterial');
        if (matEl) matEl.value = '';
        
        // Same rule: locked rows hindi ginagalaw.
        var selects = document.querySelectorAll('#garment_productRowsContainer .product-select');
        var targets = [];
        selects.forEach(function(d) { if (!d.value) targets.push(d); });
        if (targets.length === 0) {
            if (typeof showToast === 'function') showToast('Naka-lock lahat ng napiling product — i-clear muna para ma-apply ang filter.', 'info');
            return;
        }
        targets.forEach(function(dropdown) {
            dropdown.innerHTML = '<option value="">Resetting filters...</option>';
        });
        
        fetch('/api/products-for-box/' + currentProductType)
            .then(function(r) { return r.json(); })
            .then(function(products) {
                targets.forEach(function(dropdown) {
                    dropdown.innerHTML = '<option value="">Select product</option>';
                    products.forEach(function(p) {
                        var opt = document.createElement('option');
                        opt.value = p.id;
                        opt.textContent = window.garment_buildProductOption(p);
                        opt.dataset.price = p.base_price;
                        opt.dataset.brand = p.brand || '';
                        var pSize = p.size;
                        if (!pSize && p.description) {
                            var sizeM = p.description.match(/Size:\s*([^,]+)/i);
                            if (sizeM) pSize = sizeM[1].trim();
                        }
                        opt.dataset.size = pSize || '';
                        opt.dataset.color = p.color || '';
                        opt.dataset.productName = p.name || '';
                        opt.dataset.productData = JSON.stringify(p);
                        dropdown.appendChild(opt);
                    });
                    if (products.length === 0) {
                        dropdown.innerHTML = '<option value="">No products available</option>';
                    }
                });
                window.cap_lastOptionsHtml = targets.length ? targets[0].innerHTML : null;
                garment_updatePrintSummary();
            })
            .catch(function() {
                targets.forEach(function(d) { d.innerHTML = '<option value="">Error loading</option>'; });
            });
    };

    window.updateProductPriceDisplay = window.updateProductPriceDisplay || function(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const price = selectedOption && selectedOption.value ? selectedOption.dataset.price : 0;
        
        // Update price display (show first selected product's price)
        const priceDisplay = document.getElementById('productPriceDisplay');
        if (priceDisplay) {
            priceDisplay.value = price ? `₱${parseFloat(price).toFixed(2)}` : '₱0.00';
        }
    };

window.showToast = window.showToast || function(message, type) {
    if (type === undefined) type = 'info';
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    const toastId = 'toast-' + Date.now();
    const typeClasses = {
        'success': 'bg-success text-white',
        'warning': 'bg-warning text-dark',
        'error': 'bg-danger text-white',
        'info': 'bg-info text-white'
    };
    
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast ${typeClasses[type] || typeClasses['info']} border-0`;
    toast.style.minWidth = '300px';
    toast.style.marginBottom = '10px';
    
    toast.innerHTML = `
        <div class="toast-body">
            <div class="d-flex justify-content-between align-items-center">
                <span>${message}</span>
                <button type="button" class="btn-close btn-close-white" onclick="document.getElementById('${toastId}').remove()"></button>
            </div>
        </div>
    `;
    
    toast.classList.add('show');
    
    container.appendChild(toast);
    setTimeout(() => {
        if (document.getElementById(toastId)) {
            document.getElementById(toastId).remove();
        }
    }, 3000);
};

window.updateSelectedItemsDisplay = window.updateSelectedItemsDisplay || function () {};

window.updateOrderSummary = window.updateOrderSummary || function () {};

function normalizeSize(raw) {
    raw = raw.toUpperCase().trim();
    // Handle "SMALL", "MEDIUM", "LARGE", "XLARGE", "2XLARGE", "5XLARGE" etc.
    var map = {
        'SMALL': 'S',
        'MEDIUM': 'M',
        'LARGE': 'L',
        'XLARGE': 'XL',
        'EXTRA LARGE': 'XL',
        'EXTRA-LARGE': 'XL',
        'EXTRA_SMALL': 'XS',
        'EXTRA SMALL': 'XS',
        'EXTRA-SMALL': 'XS',
        'XSMALL': 'XS',
        'X-SMALL': 'XS',
        'X LARGE': 'XL',
        'X-LARGE': 'XL',
        '2XLARGE': '2XL',
        '2X LARGE': '2XL',
        '2X-LARGE': '2XL',
        '3XLARGE': '3XL',
        '3X LARGE': '3XL',
        '3X-LARGE': '3XL',
        '4XLARGE': '4XL',
        '4X LARGE': '4XL',
        '4X-LARGE': '4XL',
        '5XLARGE': '5XL',
        '5X LARGE': '5XL',
        '5X-LARGE': '5XL',
        '6XLARGE': '6XL',
        '7XLARGE': '7XL',
        '8XLARGE': '8XL'
    };
    if (map[raw]) return map[raw];
    
    // Catch-alls: if ends with 'ARGE' or 'LARGE', strip the suffix
    if (raw.endsWith('ARGE')) return raw.slice(0, -4);  // XLARGE -> XL, 5XLARGE -> 5XL
    if (raw.endsWith('LARGE')) return raw.slice(0, -5); // X-LARGE -> X- (fallback)
    if (raw.endsWith('SMALL')) return raw.slice(0, -5) + 'S';  // XSMALL -> XS
    
    return raw;
}

window.sublimation_sizeRank = window.sublimation_sizeRank || function(sizeStr) {
    var s = normalizeSize(String(sizeStr == null ? '' : sizeStr).toUpperCase().trim());
    var i = SUB_SIZE_ORDER.indexOf(s);
    if (i >= 0) return i;                 // XS..8XL -> 0..11
    var n = parseFloat(s);
    if (!isNaN(n)) return 1000 + n;       // numeric sizes (waist) -> pagkatapos ng letter sizes
    return 99999;                         // hindi kilala -> hulihan (stable)
};

window.sublimation_sortRowsBySize = window.sublimation_sortRowsBySize || function(rows, sizeCol) {
    if (!rows || sizeCol < 0) return rows;   // walang size column -> huwag galawin
    return rows.map(function(r, i) { return { r: r, i: i }; })
        .sort(function(a, b) {
            var ra = sublimation_sizeRank(a.r ? a.r[sizeCol] : '');
            var rb = sublimation_sizeRank(b.r ? b.r[sizeCol] : '');
            if (ra !== rb) return ra - rb;
            return a.i - b.i;             // stable: pantay na size -> ayon sa orihinal na order
        })
        .map(function(x) { return x.r; });
};

function formatDateDDMMYYYY(dateStr) {
    if (!dateStr) return '';
    var parts = dateStr.split('-');
    if (parts.length === 3) {
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }
    return dateStr;
}

function _escHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

// ============================================
// GARMENT PRINTING FUNCTIONS
// ============================================

// Garment-specific variables
var garment_printingData = {};
var garment_selectedPrintSizes = [];
var garment_uploadedReferenceImages = [];
var garment_referenceImageCounter = 0;

// Open garment modal
// Reset the whole garment form so reopening the modal starts clean.
// (Matches Full Sublimation, which also resets every time the modal opens.)
window.garment_resetForm = function() {
    // Spec (Brand / Type-Collar / Color)
    ['garment_specBrand', 'garment_specType', 'garment_specColor', 'garment_specMaterial'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    // Project info + notes
    ['garment_projectName', 'garment_dateNeeded', 'garment_productNotes'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    // Shop / department resets to blank every new order (dapat piliin ni user)
    var gDeptEl = document.getElementById('garment_department');
    if (gDeptEl) gDeptEl.value = '';
    // Sizes with name: roster + Excel upload + add-row inputs
    if (typeof garment_clearRoster === 'function') garment_clearRoster();
    var excelInput = document.getElementById('garment_excelInput');
    if (excelInput) excelInput.value = '';
    ['garment_roster_newName', 'garment_roster_newNumber'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    var newSize = document.getElementById('garment_roster_newSize');
    if (newSize) newSize.selectedIndex = 0;
    var spCard = document.getElementById('garment_sizePricingCard');
    if (spCard) spCard.style.display = 'none';
    // Mockup + print selection
    if (typeof garment_clearMockup === 'function') garment_clearMockup();
    if (typeof garment_clearPrintSelection === 'function') garment_clearPrintSelection();
    // Product rows
    if (typeof garment_initializeProductRows === 'function') garment_initializeProductRows();
    // Re-evaluate the Excel/spec gate
    if (typeof garment_updateSpecGate === 'function') garment_updateSpecGate();
    if (typeof garment_updatePrintSummary === 'function') garment_updatePrintSummary();
};

window.garment_openModal = function(productType) {
    console.log('Opening garment modal for:', productType);
    // Fresh form every time the modal is opened (no leftovers from a previous close)
    garment_resetForm();
    // Reset order-slip tracking (must re-print each new order)
    window.garment_orderSlipViewed = false;
    var gSlipBtn = document.getElementById('garment_printOrderSlip');
    if (gSlipBtn) {
        gSlipBtn.classList.remove('btn-success');
        gSlipBtn.classList.add('btn-outline-info');
        gSlipBtn.innerHTML = '<i class="fas fa-print me-2"></i> Print Order Slip';
    }
    garment_initializeProductRows();
    garment_loadFilterOptions(productType);
    loadProductsFromDatabase(productType);
    garment_initSpec();
    setTimeout(function() {
        garment_populatePrintTypes();
    }, 100);
    setTimeout(function() { window.garment_refreshCartGate(); }, 300);
};

// Garment-specific product rows system
window.garment_initializeProductRows = function() {
    const container = document.getElementById('garment_productRowsContainer');
    if (!container) return;
    
    // Reset to single row
    const firstRow = container.querySelector('.product-row:first-child');
    if (firstRow) {
        container.innerHTML = '';
        container.appendChild(firstRow);
    }
    const firstSelect = container.querySelector('.product-select');
    const firstQty = container.querySelector('.product-quantity');
    if (firstSelect) firstSelect.innerHTML = '<option value="">Select a product</option>';
    if (firstQty) firstQty.value = '1';
    
    garment_setupProductRowEvents();
};

window.garment_setupProductRowEvents = function() {
    const container = document.getElementById('garment_productRowsContainer');
    if (!container) return;
    
    // Add row button
    const addBtn = document.getElementById('garment_addProductRowBtn');
    if (addBtn) {
        addBtn.onclick = function() {
            garment_addProductRow();
        };
    }
    
    // Remove buttons
    container.querySelectorAll('.remove-row').forEach(function(btn) {
        btn.onclick = function() {
            var row = this.closest('.product-row');
            if (row && container.querySelectorAll('.product-row').length > 1) {
                row.remove();
                garment_updatePrintSummary();
            }
        };
    });
    
    // Select change listeners
    container.querySelectorAll('.product-select').forEach(function(sel) {
        sel.onchange = function() {
            garment_updatePrintSummary();
        };
    });
    
    // Quantity change listeners
    container.querySelectorAll('.product-quantity').forEach(function(qty) {
        qty.oninput = function() {
            garment_updatePrintSummary();
        };
    });
};

window.garment_addProductRow = function() {
    const container = document.getElementById('garment_productRowsContainer');
    const template = container.querySelector('.product-row-template');
    if (!container || !template) return;
    
    var newRow = template.cloneNode(true);
    newRow.classList.remove('product-row-template', 'd-none');
    newRow.classList.add('product-row');
    
    // Clear selections in new row
    var select = newRow.querySelector('.product-select');
    if (select) {
        select.selectedIndex = 0;
    }
    var qtyInput = newRow.querySelector('.product-quantity');
    if (qtyInput) qtyInput.value = '1';
    
    container.appendChild(newRow);
    
    // Reload products into the new select if available
    var currentType = window.currentProductType;
    if (currentType && select) {
        select.innerHTML = '<option value="">Loading products...</option>';
        fetch('/api/products-for-box/' + currentType)
            .then(function(r) { return r.json(); })
            .then(function(products) {
                select.innerHTML = '<option value="">Select product</option>';
                products.forEach(function(p) {
                    var opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = window.garment_buildProductOption(p);
                    opt.dataset.price = p.base_price;
                    var pSize = p.size;
                    if (!pSize && p.description) {
                        var sizeM = p.description.match(/Size:\s*([^,]+)/i);
                        if (sizeM) pSize = sizeM[1].trim();
                    }
                    opt.dataset.brand = p.brand || '';
                    opt.dataset.size = pSize || '';
                    opt.dataset.color = p.color || '';
                    opt.dataset.productName = p.name || '';
                    opt.dataset.productData = JSON.stringify(p);
                    select.appendChild(opt);
                });
                garment_updatePrintSummary();
            })
            .catch(function() {
                select.innerHTML = '<option value="">Error loading</option>';
            });
    }
    
    // Wire remove button
    var removeBtn = newRow.querySelector('.remove-row');
    if (removeBtn) {
        removeBtn.onclick = function() {
            if (container.querySelectorAll('.product-row').length > 1) {
                newRow.remove();
                garment_updatePrintSummary();
            }
        };
    }
    
    // Wire select/qty changes
    if (select) {
        select.onchange = function() { garment_updatePrintSummary(); };
    }
    if (qtyInput) {
        qtyInput.oninput = function() { garment_updatePrintSummary(); };
    }
};

// Populate print type dropdown
window.garment_populatePrintTypes = function() {
    var select = document.getElementById('garment_printTypeSelect');
    if (!select) return;
    select.innerHTML = '<option value="">-- Select Print Type --</option>';
    garment_hidePrintingSections();
    select.onchange = function() {
        garment_selectedPrintSizes = [];
        var val = this.value;
        if (val) {
            garment_loadPrintSizes(val);
        } else {
            garment_hidePrintingSections();
        }
    };
    fetch('/api/printing/options/dtf')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            garment_printingData = data;
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
};

// Load print sizes for selected print type
window.garment_loadPrintSizes = function(printType) {
    if (!printType) {
        garment_hidePrintingSections();
        return;
    }
    fetch('/api/printing/options/' + printType)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            garment_printingData = data;
            garment_renderPrintSizes(data.prices);
            var container = document.getElementById('garment_printSizesContainer');
            if (container) container.style.display = 'block';
            garment_selectedPrintSizes = [];
            garment_updatePrintSummary();
        })
        .catch(function(err) {
            console.error('Error loading print options:', err);
            var list = document.getElementById('garment_printSizesList');
            if (list) list.innerHTML = '<div class="text-danger small">Failed to load print options.</div>';
        });
};

// Render print size rows (checkbox + quantity). Quantity lets you order e.g. 2x Logo
// which auto-upgrades (Logo x2 -> Half A4) per the DTF upgrade ladder.
window.garment_renderPrintSizes = function(prices) {
    var container = document.getElementById('garment_printSizesList');
    if (!container) return;
    if (!prices || prices.length === 0) {
        container.innerHTML = '<div class="text-muted small">No print sizes available.</div>';
        return;
    }
    var html = '';
    prices.forEach(function(p) {
        html += '<div class="print-size-row d-flex align-items-center mb-1 p-1 border rounded">';
        html += '<input type="checkbox" class="print-size-checkbox me-2" value="' + p.id + '" onchange="garment_togglePrintSize(' + p.id + ', this)">';
        html += '<span class="flex-grow-1">' + p.name + '</span>';
        html += '<input type="number" class="form-control form-control-sm print-size-qty me-2" style="width:64px;" min="1" value="1" data-size-id="' + p.id + '" oninput="garment_onPrintQtyChange(' + p.id + ', this)">';
        html += '<span class="text-muted" style="width:72px;text-align:right;">\u20B1' + p.price.toFixed(2) + '</span>';
        html += '</div>';
    });
    container.innerHTML = html;
};

// Toggle print size selection
window.garment_togglePrintSize = function(sizeId, checkbox) {
    var row = checkbox.closest('.print-size-row');
    if (row) row.style.borderColor = checkbox.checked ? '#0d6efd' : '#dee2e6';
    garment_syncSelectedPrintSizes();
};

// Sync the flat selected list from checkboxes x quantity
window.garment_syncSelectedPrintSizes = function() {
    garment_selectedPrintSizes = garment_collectRawPrintSizes();
    garment_updatePrintSummary();
};

// Change handler for a size quantity input
window.garment_onPrintQtyChange = function(sizeId, input) {
    var row = input.closest('.print-size-row');
    var cb = row ? row.querySelector('.print-size-checkbox') : null;
    // Typing a quantity implies selecting that size
    if (cb && !cb.checked && parseInt(input.value) > 0) {
        cb.checked = true;
        if (row) row.style.borderColor = '#0d6efd';
    }
    garment_syncSelectedPrintSizes();
};

// Flat list of selected prints (one entry per copy)
window.garment_collectRawPrintSizes = function() {
    var out = [];
    document.querySelectorAll('#garment_printSizesList .print-size-row').forEach(function(row) {
        var cb = row.querySelector('.print-size-checkbox');
        var qtyEl = row.querySelector('.print-size-qty');
        if (!cb || !cb.checked) return;
        var id = parseInt(cb.value);
        var q = qtyEl ? (parseInt(qtyEl.value) || 1) : 1;
        for (var i = 0; i < q; i++) out.push(id);
    });
    return out;
};

// Resolve a raw list of size ids into the upgraded set using the upgrade ladder.
// Returns { sizes: [ids], log: [...], surcharge: number }
window.garment_resolvePrint = function(rawIds) {
    var rules = (garment_printingData && garment_printingData.upgrades) || [];
    // highest quantity rules first (e.g. 4x Logo -> A4 before 2x Logo -> Half A4)
    rules = rules.slice().sort(function(a, b) {
        var d = parseInt(b.from_quantity) - parseInt(a.from_quantity);
        if (d !== 0) return d;
        return parseInt(a.from_size_id) - parseInt(b.from_size_id);
    });
    var counts = {};
    (rawIds || []).forEach(function(id) { id = parseInt(id); if (!isNaN(id)) counts[id] = (counts[id] || 0) + 1; });
    var log = [];
    var surcharge = 0;
    if (rules.length) {
        var changed = true, guard = 0;
        while (changed && guard++ < 200) {
            changed = false;
            rules.forEach(function(r) {
                var from = parseInt(r.from_size_id), qty = parseInt(r.from_quantity), to = parseInt(r.to_size_id);
                if (qty < 1) return;
                if ((counts[from] || 0) >= qty) {
                    var times = Math.floor(counts[from] / qty);
                    counts[from] -= times * qty;
                    counts[to] = (counts[to] || 0) + times;
                    var ruleSur = (parseFloat(r.surcharge) || 0) * times;
                    surcharge += ruleSur;
                    changed = true;
                    log.push({ from_size_id: from, from_quantity: qty, to_size_id: to, times: times, surcharge: ruleSur });
                }
            });
        }
    }
    var sizes = [];
    Object.keys(counts).forEach(function(k) { for (var i = 0; i < counts[k]; i++) sizes.push(parseInt(k)); });
    return { sizes: sizes, log: log, surcharge: Math.round(surcharge * 100) / 100 };
};

window.garment_resolveUpgradedSizes = function(rawIds) {
    return garment_resolvePrint(rawIds).sizes;
};

// Human text for an upgrade log, e.g. "2x Logo -> 1x Half A4"
window.garment_upgradeLogText = function(log) {
    var prices = garment_printingData.prices || [];
    function nameOf(id) { for (var i = 0; i < prices.length; i++) { if (parseInt(prices[i].id) === parseInt(id)) return prices[i].name; } return id; }
    return (log || []).map(function(l) {
        var t = (l.from_quantity * l.times) + 'x ' + nameOf(l.from_size_id) + ' \u2192 ' + l.times + 'x ' + nameOf(l.to_size_id);
        if (l.surcharge) t += ' (+\u20B1' + l.surcharge.toFixed(2) + ')';
        return t;
    }).join(', ');
};

// Special Price functions
window.garment_hasSpecialPrice = false;
window.garment_toggleSpecialPrice = function() {
    var section = document.getElementById('garment_specialPriceSection');
    var btn = document.getElementById('garment_specialPriceBtn');
    if (!section) return;
    var showing = section.style.display !== 'none';
    if (showing) {
        section.style.display = 'none';
        btn.textContent = '💰 Special Price';
        btn.classList.remove('btn-warning');
        btn.classList.add('btn-outline-warning');
        garment_hasSpecialPrice = false;
        garment_updatePrintSummary();
    } else {
        section.style.display = 'block';
        btn.textContent = '💰 Cancel Special Price';
        btn.classList.remove('btn-outline-warning');
        btn.classList.add('btn-warning');
        // Pre-fill with current print total
        var printTotalEl = document.getElementById('garment_printTotalDisplay');
        var spInput = document.getElementById('garment_specialPrintTotal');
        if (printTotalEl && spInput) {
            var current = parseFloat(printTotalEl.textContent.replace(/[₱,]/g, '')) || 0;
            if (current > 0) spInput.value = current.toFixed(2);
        }
        garment_hasSpecialPrice = true;
        garment_updatePrintSummary();
    }
};

window.garment_clearSpecialPrice = function() {
    var section = document.getElementById('garment_specialPriceSection');
    var btn = document.getElementById('garment_specialPriceBtn');
    var spInput = document.getElementById('garment_specialPrintTotal');
    var reasonInput = document.getElementById('garment_specialPriceReason');
    if (section) section.style.display = 'none';
    if (btn) {
        btn.textContent = '💰 Special Price';
        btn.classList.remove('btn-warning');
        btn.classList.add('btn-outline-warning');
    }
    if (spInput) spInput.value = '';
    if (reasonInput) reasonInput.value = '';
    garment_hasSpecialPrice = false;
    garment_updatePrintSummary();
};

window.garment_onSpecialPriceChange = function() {
    garment_updatePrintSummary();
};

// Clear print selection
window.garment_clearPrintSelection = function() {
    garment_selectedPrintSizes = [];
    garment_printingData = {};
    var select = document.getElementById('garment_printTypeSelect');
    if (select) select.value = '';
    garment_hidePrintingSections();
};

// Hide printing sections
window.garment_hidePrintingSections = function() {
    var container = document.getElementById('garment_printSizesContainer');
    var sidebar = document.getElementById('garment_printSummarySidebar');
    if (container) container.style.display = 'none';
    if (sidebar) sidebar.style.display = 'none';
};

// Update print summary (combo, bulk, totals)
window.garment_updatePrintSummary = function() {
    var sidebar = document.getElementById('garment_printSummarySidebar');
    // Print sizes are OPTIONAL: product qty/amount (Order Summary) must always compute.
    // Hide only the print-specific sidebar when no print size is chosen.
    var hasPrintSizes = garment_selectedPrintSizes.length > 0;
    if (!hasPrintSizes && sidebar) sidebar.style.display = 'none';
    var prices = garment_printingData.prices || [];
    var combos = garment_printingData.combos || [];
    var bulkTiers = garment_printingData.bulk_tiers || [];
    // ===== Print qty is based on the garment qty (roster qty, else product rows) =====
    var roster = (typeof garment_computeSizePricing === 'function')
        ? garment_computeSizePricing()
        : { qty: 0, amount: 0, hasRows: false, missing: [], perSize: {}, keys: [] };
    var productTotal = 0;
    var qtyTotal = 0;
    if (!roster.hasRows) {
        document.querySelectorAll('#garment_productRowsContainer .product-row').forEach(function(row) {
            var qtyInput = row.querySelector('.product-quantity');
            var opt = row.querySelector('.product-select option:checked');
            // Huwag bilangin ang row na walang napiling product (qty default = 1 pero walang item).
            if (!opt || !opt.value) return;
            var qty = parseInt(qtyInput ? qtyInput.value : 0) || 0;
            var price = opt.dataset.price ? parseFloat(opt.dataset.price) : 0;
            qtyTotal += qty;
            productTotal += qty * price;
        });
    } else {
        qtyTotal = roster.qty;
        productTotal = roster.amount;
    }
    // Print qty follows the same source as Order Summary (roster/Excel wins, else product rows).
    var totalQty = qtyTotal > 0 ? qtyTotal : 0;
    // Resolve upgrades first (DTF ladder: Logo x2 -> Half A4, Half A4 x2 -> A4, ...)
    var resolvedPrint = garment_resolvePrint(garment_selectedPrintSizes);
    var effectiveSizes = resolvedPrint.sizes;
    var upgradeNote = garment_upgradeLogText(resolvedPrint.log);
    var upgradeSurcharge = parseFloat(resolvedPrint.surcharge) || 0;
    var printCostPerItem = upgradeSurcharge;
    var selectedSizeDetails = [];
    effectiveSizes.forEach(function(sizeId) {
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
    // Base (raw) print cost per item, before combo/bulk.
    var rawPrintCostPerItem = printCostPerItem;

    // Combo: count-aware match. A combo like "A4 + A4" needs TWO A4 prints —
    // the old in_array() check applied it with only one.
    var sizeCounts = {};
    effectiveSizes.forEach(function(id) { sizeCounts[id] = (sizeCounts[id] || 0) + 1; });
    var comboDiscount = 0;
    var comboLabel = '';
    combos.forEach(function(c) {
        var s1 = parseInt(c.size1_id);
        var s2 = parseInt(c.size2_id);
        var matched = (s1 === s2)
            ? (sizeCounts[s1] || 0) >= 2
            : ((sizeCounts[s1] || 0) >= 1 && (sizeCounts[s2] || 0) >= 1);
        if (matched && c.discount > comboDiscount) {
            comboDiscount = c.discount;
            if (c.size1_name && c.size2_name) comboLabel = c.size1_name + ' + ' + c.size2_name;
        }
    });

    // Subtotal = raw cost x qty. Discounts are shown as their own lines.
    var subtotal = rawPrintCostPerItem * totalQty;
    var comboTotal = comboDiscount * totalQty;
    var subtotalAfterCombo = subtotal - comboTotal;
    var bulkDiscount = 0;
    var bulkLabel = '';
    var bulkRateText = '';
    // ===== Bulk size gate: when ON, bulk applies only for A3/A4 prints =====
    var bulkGate = garment_printingData.bulk_gate || { active: false, sizes: [] };
    var bulkGateSatisfied = true;
    if (bulkGate.active) {
        var gateNames = (bulkGate.sizes && bulkGate.sizes.length ? bulkGate.sizes : ['A3', 'A4'])
            .map(function(n) { return String(n).trim().toUpperCase(); });
        var pickedNames = selectedSizeDetails.map(function(sz) {
            return String(sz.name || '').trim().toUpperCase();
        });
        bulkGateSatisfied = pickedNames.some(function(n) { return gateNames.indexOf(n) !== -1; });
    }
    for (var bi = bulkGateSatisfied ? bulkTiers.length - 1 : -1; bi >= 0; bi--) {
        var tier = bulkTiers[bi];
        if (totalQty >= tier.min && totalQty <= tier.max) {
            if (tier.type === 'percentage') {
                bulkDiscount = subtotalAfterCombo * (tier.percent / 100);
                bulkLabel = tier.label;
                bulkRateText = tier.percent + '% of ₱' + subtotalAfterCombo.toFixed(2);
            } else if (tier.type === 'fixed') {
                bulkDiscount = tier.amount * totalQty;
                bulkLabel = tier.label;
                bulkRateText = '₱' + tier.amount.toFixed(2) + ' × ' + totalQty;
            }
            break;
        }
    }
    var total = subtotalAfterCombo - bulkDiscount;
    
    // SPECIAL PRICE OVERRIDE
    var isSpecialPrice = window.garment_hasSpecialPrice || false;
    var specialTotal = 0;
    if (isSpecialPrice) {
        var spInput = document.getElementById('garment_specialPrintTotal');
        if (spInput) {
            specialTotal = parseFloat(spInput.value) || 0;
        }
    }
    
    // Render sizes breakdown
    var sizesHtml = '';
    if (upgradeNote) {
        sizesHtml += '<div class="text-muted small mb-1">Auto-combined: ' + upgradeNote + '</div>';
    }
    selectedSizeDetails.forEach(function(s) {
        sizesHtml += '<div class="d-flex justify-content-between">';
        sizesHtml += '<span>' + s.name + '</span>';
        sizesHtml += '<span>₱' + s.price.toFixed(2) + '</span>';
        sizesHtml += '</div>';
    });
    if (selectedSizeDetails.length > 1) {
        sizesHtml += '<div class="d-flex justify-content-between fw-semibold border-top mt-1 pt-1">';
        sizesHtml += '<span>Cost/ea</span>';
        sizesHtml += '<span>₱' + rawPrintCostPerItem.toFixed(2) + '</span>';
        sizesHtml += '</div>';
    }
    var sizesBreakdown = document.getElementById('garment_printSizesBreakdown');
    if (sizesBreakdown) sizesBreakdown.innerHTML = sizesHtml;
    var costPerItemEl = document.getElementById('garment_printCostPerItemDisplay');
    var qtyDisplayEl = document.getElementById('garment_printQtyDisplay');
    var subtotalEl = document.getElementById('garment_printSubtotalDisplay');
    var comboRow = document.getElementById('garment_comboDiscountRow');
    var comboDisplay = document.getElementById('garment_comboDiscountDisplay');
    var bulkRow = document.getElementById('garment_bulkDiscountRow');
    var bulkDisplay = document.getElementById('garment_bulkDiscountDisplay');
    var printTotalEl = document.getElementById('garment_printTotalDisplay');
    var grandTotalEl = document.getElementById('garment_grandTotalDisplay');
    // Show original print cost (before any discounts)
    if (costPerItemEl) costPerItemEl.textContent = '₱' + rawPrintCostPerItem.toFixed(2);
    if (qtyDisplayEl) qtyDisplayEl.textContent = totalQty;
    if (subtotalEl) subtotalEl.textContent = '₱' + subtotal.toFixed(2) + ' (₱' + rawPrintCostPerItem.toFixed(2) + ' × ' + totalQty + ')';
    if (isSpecialPrice && specialTotal > 0) {
        // Special price: hide combo/bulk, show original qty/cost but use special total
        if (comboRow) comboRow.style.setProperty('display', 'none', 'important');
        if (bulkRow) bulkRow.style.setProperty('display', 'none', 'important');
        if (printTotalEl) printTotalEl.textContent = '₱' + specialTotal.toFixed(2) + ' (Special Price)';
    } else {
        // Normal: raw cost/ea + separate discount lines (matches pricing rules)
        if (comboTotal > 0 && comboRow && comboDisplay) {
            comboRow.style.setProperty('display', 'flex', 'important');
            comboDisplay.textContent = '-₱' + comboTotal.toFixed(2) + ' (₱' + comboDiscount.toFixed(2) + ' × ' + totalQty + (comboLabel ? ' · ' + comboLabel : '') + ')';
        } else if (comboRow) {
            comboRow.style.setProperty('display', 'none', 'important');
        }
        if (bulkDiscount > 0 && bulkRow && bulkDisplay) {
            bulkRow.style.setProperty('display', 'flex', 'important');
            bulkDisplay.textContent = '-₱' + bulkDiscount.toFixed(2) + ' (' + bulkRateText + (bulkLabel ? ' · ' + bulkLabel : '') + ')';
        } else if (bulkRow) {
            bulkRow.style.setProperty('display', 'none', 'important');
        }
        if (printTotalEl) printTotalEl.textContent = '₱' + total.toFixed(2);
        var ptf = document.getElementById('garment_printTotalFormula');
        if (ptf) {
            var f = '₱' + subtotal.toFixed(2);
            if (comboTotal > 0) f += ' − ₱' + comboTotal.toFixed(2);
            if (bulkDiscount > 0) f += ' − ₱' + bulkDiscount.toFixed(2);
            ptf.textContent = f + ' = ₱' + total.toFixed(2);
        }
    }
    
    var grandTotal = productTotal + (isSpecialPrice && specialTotal > 0 ? specialTotal : total);
    if (grandTotalEl) grandTotalEl.textContent = '\u20B1' + grandTotal.toFixed(2);
    var gtf = document.getElementById('garment_grandTotalFormula');
    if (gtf) gtf.textContent = 'products ₱' + productTotal.toFixed(2) + ' + print ₱' + (isSpecialPrice && specialTotal > 0 ? specialTotal : total).toFixed(2) + ' = ₱' + grandTotal.toFixed(2);
    if (sidebar) sidebar.style.display = (hasPrintSizes || roster.hasRows) ? 'block' : 'none';
    var qtyDisplay = document.getElementById('garment_totalQtyDisplay');
    var amtDisplay = document.getElementById('garment_totalAmountDisplay');
    if (qtyDisplay) qtyDisplay.textContent = qtyTotal;
    if (amtDisplay) amtDisplay.textContent = '\u20B1' + productTotal.toFixed(2);
    
    // Render breakdown: yung galing sa 'Sizes with name' ang ipapakita kung may laman,
    // kung wala, dun lang sa Product Details product rows.
    var breakdownEl = document.getElementById('garment_productsBreakdown');
    if (breakdownEl) {
        if (roster.hasRows) {
            var rh = '<div class="text-muted small mb-1"><i class="fas fa-users me-1"></i>From Sizes with name</div>';
            var rkeys = roster.keys || Object.keys(roster.perSize || {});
            rkeys.forEach(function(s) {
                var q = (roster.perSize || {})[s] || 0;
                var res = garment_findItemPrice(s);
                if (res.price == null) {
                    rh += '<div class="d-flex justify-content-between align-items-center mb-0 small"><span>' + _escHtml(s) + ' <span class="text-muted">x' + q + '</span></span><span class="text-danger">no price</span></div>';
                    if (res.note) rh += '<div class="text-danger mb-1" style="font-size:.75rem;line-height:1.15;">' + _escHtml(res.note) + '</div>';
                } else {
                    var _specB = ((document.getElementById('garment_specBrand') || {}).value || '').trim().toUpperCase();
                    var _usedB = (typeof window.garment_effectiveBrand === 'function') ? window.garment_effectiveBrand(s) : _specB;
                    var _brandTag = (_usedB && _usedB !== _specB) ? ' <span class="badge bg-info text-dark" style="font-size:.62rem;">' + _escHtml(_usedB) + '</span>' : '';
                    rh += '<div class="d-flex justify-content-between align-items-center mb-1 small"><span>' + _escHtml(s) + _brandTag + ' <span class="text-muted">x' + q + ' × \u20B1' + res.price.toFixed(2) + '</span></span><span>\u20B1' + (res.price * q).toFixed(2) + '</span></div>';
                    if (res.note) rh += '<div class="text-warning mb-1" style="font-size:.72rem;line-height:1.15;"><i class="fas fa-info-circle me-1"></i>' + _escHtml(res.note) + '</div>';
                }
            });
            var specLabel = [(document.getElementById('garment_specBrand') || {}).value || '', (document.getElementById('garment_specType') || {}).value || '', (document.getElementById('garment_specColor') || {}).value || '', (document.getElementById('garment_specMaterial') || {}).value || ''].filter(Boolean).join(' / ');
            if (specLabel) rh += '<div class="text-muted small">' + _escHtml(specLabel) + '</div>';
            if (roster.missing && roster.missing.length) rh += '<div class="text-danger small mt-1"><i class="fas fa-exclamation-triangle me-1"></i>Walang price: ' + _escHtml(roster.missing.join(', ')) + ' — i-check ang Master Items (brand/type/color/size rows).</div>';
            breakdownEl.innerHTML = rh;
        } else {
        var rows = document.querySelectorAll('#garment_productRowsContainer .product-row');
        var hasProducts = false;
        var html = '';
        rows.forEach(function(row) {
            var sel = row.querySelector('.product-select');
            var qtyInput = row.querySelector('.product-quantity');
            if (!sel || !qtyInput) return;
            var opt = sel.options[sel.selectedIndex];
            var qty = parseInt(qtyInput.value) || 0;
            if (!opt || !opt.value || qty === 0) return;
            hasProducts = true;
            var price = opt.dataset.price ? parseFloat(opt.dataset.price) : 0;
            var subtotal = qty * price;
            var brand = opt.dataset.brand || '';
            var size = opt.dataset.size || '';
            var color = opt.dataset.color || '';
            var shortName = [brand, color, size].filter(Boolean).join(' ');
            if (!shortName) {
                var name = opt.textContent.split(' - ')[0] || 'Product';
                shortName = name;
            }
            html += '<div class="d-flex justify-content-between align-items-center mb-1 small">';
            html += '<span>' + shortName + ' <span class="text-muted">x' + qty + ' × \u20B1' + price.toFixed(2) + '</span></span>';
            html += '<span>\u20B1' + subtotal.toFixed(2) + '</span>';
            html += '</div>';
        });
        if (hasProducts) {
            breakdownEl.innerHTML = html;
        } else {
            breakdownEl.innerHTML = '<div class="text-muted small mb-2">No products selected yet</div>';
        }
        }
    }
};

// Reference image handling
window.garment_triggerReferenceFilePicker = function() {
    var picker = document.getElementById('referenceFilePicker');
    if (picker) picker.click();
};
window.garment_onReferencePaste = function(e) {
    var items = (e.clipboardData || e.originalEvent.clipboardData).items;
    for (var i = 0; i < items.length; i++) {
        if (items[i].type.indexOf('image') === 0) {
            var file = items[i].getAsFile();
            if (file) garment_handleReferenceImage(file);
        }
    }
};
window.garment_onReferenceDragOver = function(e) {
    e.preventDefault();
    e.stopPropagation();
    var dz = document.getElementById('referenceDropZone');
    if (dz) dz.style.borderColor = '#0d6efd';
};
window.garment_onReferenceDragLeave = function(e) {
    e.preventDefault();
    e.stopPropagation();
    var dz = document.getElementById('referenceDropZone');
    if (dz) dz.style.borderColor = '#ccc';
};
window.garment_onReferenceDrop = function(e) {
    e.preventDefault();
    e.stopPropagation();
    var dz = document.getElementById('referenceDropZone');
    if (dz) dz.style.borderColor = '#ccc';
    var files = e.dataTransfer.files;
    for (var i = 0; i < files.length; i++) {
        if (files[i].type.indexOf('image') === 0) {
            garment_handleReferenceImage(files[i]);
        }
    }
};
window.garment_onReferenceFilePickerChange = function(e) {
    var files = e.target.files;
    for (var i = 0; i < files.length; i++) {
        garment_handleReferenceImage(files[i]);
    }
    e.target.value = '';
};
window.garment_handleReferenceImage = function(file) {
    var reader = new FileReader();
    reader.onload = function(e) {
        garment_referenceImageCounter++;
        garment_uploadedReferenceImages.push({
            id: garment_referenceImageCounter,
            dataUrl: e.target.result,
            name: file.name
        });
        garment_renderReferencePreviews();
    };
    reader.readAsDataURL(file);
};
window.garment_renderReferencePreviews = function() {
    var container = document.getElementById('referencePreviewsGallery');
    if (!container) return;
    if (garment_uploadedReferenceImages.length === 0) {
        container.innerHTML = '';
        return;
    }
    var html = '';
    garment_uploadedReferenceImages.forEach(function(img) {
        html += '<div style="position:relative;display:inline-block;width:80px;height:80px;border:1px solid #dee2e6;border-radius:6px;overflow:hidden;">';
        html += '<img src="' + img.dataUrl + '" style="width:100%;height:100%;object-fit:cover;">';
        html += '<button type="button" onclick="garment_removeReferenceImage(' + img.id + ')" style="position:absolute;top:2px;right:2px;background:rgba(0,0,0,0.6);color:white;border:none;border-radius:50%;width:20px;height:20px;font-size:12px;line-height:20px;text-align:center;padding:0;cursor:pointer;">&times;</button>';
        html += '</div>';
    });
    container.innerHTML = html;
};
window.garment_clearAllReferenceImages = function() {
    garment_uploadedReferenceImages = [];
    garment_referenceImageCounter = 0;
    garment_renderReferencePreviews();
};
window.garment_removeReferenceImage = function(id) {
    garment_uploadedReferenceImages = garment_uploadedReferenceImages.filter(function(img) { return img.id !== id; });
    garment_renderReferencePreviews();
};

// Mock-up image handling (single image, mirrors Full Sublimation modal)
window.garment_uploadedMockup = null;
window.garment_previewMockup = function(event) {
    var file = event.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var preview = document.getElementById('garment_mockupPreview');
        var placeholder = document.getElementById('garment_mockupPlaceholder');
        var fileInfo = document.getElementById('garment_mockupFileInfo');
        var fileName = document.getElementById('garment_mockupFileName');
        if (preview) { preview.src = e.target.result; preview.style.display = ''; }
        if (placeholder) placeholder.style.display = 'none';
        if (fileName) fileName.textContent = file.name;
        if (fileInfo) fileInfo.style.display = '';
        window.garment_uploadedMockup = { name: file.name, dataUrl: e.target.result };
    };
    reader.readAsDataURL(file);
};
window.garment_removeMockup = function() {
    var preview = document.getElementById('garment_mockupPreview');
    var placeholder = document.getElementById('garment_mockupPlaceholder');
    var fileInfo = document.getElementById('garment_mockupFileInfo');
    var upload = document.getElementById('garment_mockupUpload');
    if (preview) { preview.src = ''; preview.style.display = 'none'; }
    if (placeholder) placeholder.style.display = '';
    if (fileInfo) fileInfo.style.display = 'none';
    if (upload) upload.value = '';
    window.garment_uploadedMockup = null;
};
window.garment_clearMockup = function() {
    window.garment_uploadedMockup = null;
    garment_removeMockup();
};

// ===== SIZES WITH NAME (roster) — mirrors Full Sublimation =====
window.garment_addRosterRow = function() {
    var nameEl = document.getElementById('garment_roster_newName');
    var numEl = document.getElementById('garment_roster_newNumber');
    var sizeEl = document.getElementById('garment_roster_newSize');
    var name = nameEl ? nameEl.value.trim() : '';
    var number = numEl ? numEl.value.trim() : '';
    var size = sizeEl ? sizeEl.value : '';
    if (!name) { alert('Please enter a name.'); if (nameEl) nameEl.focus(); return; }
    if (!size) { alert('Please select a size.'); if (sizeEl) sizeEl.focus(); return; }
    var tbody = document.getElementById('garment_rosterBody');
    if (!tbody) return;
    var empty = document.getElementById('garment_rosterEmpty');
    if (empty) empty.style.display = 'none';
    var row = document.createElement('tr');
    row.className = 'roster-row';
    var rowNum = tbody.querySelectorAll('tr.roster-row').length + 1;
    var cellsHtml = '<td class="text-center align-middle">' + rowNum + '</td>'
        + '<td class="align-middle">' + _escHtml(name)
        + '<input type="hidden" class="roster-name" value="' + _escHtml(name) + '">'
        + '<input type="hidden" class="roster-number" value="' + _escHtml(number) + '">'
        + '</td>'
        + '<td class="align-middle">' + size + '<input type="hidden" class="roster-size" value="' + size + '"></td>'
        + '<td class="text-center align-middle">1<input type="hidden" class="roster-qty" value="1"></td>'
        + '<td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="garment_removeRosterRow(this)" title="Remove"><i class="fas fa-times"></i></button></td>';
    row.innerHTML = cellsHtml;
    tbody.appendChild(row);
    if (numEl) numEl.value = '';
    if (nameEl) nameEl.value = '';
    if (sizeEl) sizeEl.selectedIndex = 0;
    if (nameEl) nameEl.focus();
    garment_updateRosterCount();
    if (typeof garment_updatePrintSummary === 'function') garment_updatePrintSummary();
};
window.garment_removeRosterRow = function(btn) {
    var row = btn.closest('tr');
    if (!row) return;
    row.remove();
    var tbody = document.getElementById('garment_rosterBody');
    if (tbody) {
        var remaining = tbody.querySelectorAll('tr.roster-row');
        remaining.forEach(function(r, idx) {
            var numTd = r.querySelector('td:first-child');
            if (numTd) numTd.textContent = (idx + 1).toString();
        });
        if (remaining.length === 0) {
            var empty = document.getElementById('garment_rosterEmpty');
            if (empty) empty.style.display = '';
        }
    }
    garment_updateRosterCount();
    if (typeof garment_updatePrintSummary === 'function') garment_updatePrintSummary();
};
window.garment_updateRosterCount = function() {
    var countEl = document.getElementById('garment_rosterCount');
    var tbody = document.getElementById('garment_rosterBody');
    if (countEl && tbody) countEl.textContent = tbody.querySelectorAll('tr.roster-row').length;
};
window.garment_toggleBulkPaste = function() {
    var area = document.getElementById('garmentBulkPasteArea');
    if (area) area.style.display = area.style.display === 'none' ? '' : 'none';
};
window.garment_bulkPaste = function() {
    var textarea = document.getElementById('garment_bulkPasteInput');
    if (!textarea || !textarea.value.trim()) { alert('Paste your list first.'); return; }
    var lines = textarea.value.trim().split('\n');
    var added = 0;
    lines.forEach(function(line) {
        line = line.trim();
        if (!line) return;
        var parts = line.split(',').map(function(s) { return s.trim(); });
        var name, number, size;
        if (parts.length >= 3) { number = parts[0]; name = parts[1]; size = parts[2].toUpperCase(); }
        else if (parts.length === 2) { number = ''; name = parts[0]; size = parts[1].toUpperCase(); }
        else return;
        if (!name || !size) return;
        var nameEl = document.getElementById('garment_roster_newName');
        var numEl = document.getElementById('garment_roster_newNumber');
        var sizeEl = document.getElementById('garment_roster_newSize');
        if (nameEl) nameEl.value = name;
        if (numEl) numEl.value = number;
        if (sizeEl) {
            for (var i = 0; i < sizeEl.options.length; i++) {
                if (sizeEl.options[i].value.toUpperCase() === size) { sizeEl.selectedIndex = i; break; }
            }
        }
        garment_addRosterRow();
        added++;
    });
    textarea.value = '';
    var area = document.getElementById('garmentBulkPasteArea');
    if (area) area.style.display = 'none';
    if (typeof showToast === 'function') showToast(added + ' person' + (added > 1 ? 's' : '') + ' added to roster!', 'success');
};
window.garment_uploadExcel = function(event) {
    var file = event.target.files[0];
    if (!file) return;
    window.garment_lastFileName = file.name || 'file';
    // Require spec (Brand + Type/Collar) before importing
    var brandEl = document.getElementById('garment_specBrand');
    var typeEl = document.getElementById('garment_specType');
    if (!brandEl || !typeEl || !brandEl.value.trim() || !typeEl.value.trim()) {
        if (typeof showToast === 'function') showToast('Piliin muna ang Brand at Type/Collar bago mag-upload.', 'warning');
        event.target.value = '';
        return;
    }
    function loadAndParse() {
        var reader = new FileReader();
        reader.onload = function(e) {
            try {
                var data = new Uint8Array(e.target.result);
                var workbook = XLSX.read(data, {type: 'array'});
                var firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                var json = XLSX.utils.sheet_to_json(firstSheet, {header: 1, defval: ''});
                if (json.length < 1) { showToast('Excel file is empty.', 'warning'); return; }
                var headers = json[0].map(function(h) { return String(h).trim(); });
                var rows = json.slice(1).filter(function(r) { return r.some(function(cell) { return String(cell).trim() !== ''; }); });
                if (rows.length < 1) { showToast('No data rows found (header only).', 'warning'); return; }
                garment_autoBuildFromExcel(headers, rows);
            } catch(err) {
                showToast('Error reading file: ' + err.message, 'danger');
            }
        };
        reader.readAsArrayBuffer(file);
    }
    if (typeof XLSX === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js';
        script.onload = loadAndParse;
        script.onerror = function() {
            var script2 = document.createElement('script');
            script2.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
            script2.onload = loadAndParse;
            script2.onerror = function() { showToast('Failed to load Excel parser. Check internet connection.', 'danger'); };
            document.head.appendChild(script2);
        };
        document.head.appendChild(script);
    } else {
        loadAndParse();
    }
};
window.garment_autoBuildFromExcel = function(headers, rows) {
    var tbody = document.getElementById('garment_rosterBody');
    var theadRow = document.getElementById('garment_rosterHeader');
    if (!tbody || !theadRow) return;
    var nameCols = [], sizeCol = -1, numCol = -1, qtyCol = -1;
    headers.forEach(function(h, idx) {
        var hl = (h || '').toLowerCase().trim();
        if (hl.indexOf('name') >= 0 || hl === 'person' || hl === 'player' || hl === 'employee') nameCols.push(idx);
        else if (hl === 'size' || hl === 'sizes') sizeCol = idx;
    });
    headers.forEach(function(h, idx) {
        var hl = (h || '').toLowerCase().trim();
        if (nameCols.indexOf(idx) >= 0 || idx === sizeCol) return;
        if ((hl.indexOf('number') >= 0 || hl === 'no' || hl === 'no.' || hl === '#' || hl === 'jersey') && numCol < 0) numCol = idx;
    });
    headers.forEach(function(h, idx) {
        var hl = (h || '').toLowerCase().trim();
        if (nameCols.indexOf(idx) >= 0 || idx === sizeCol || idx === numCol) return;
        if ((hl === 'qty' || hl === 'quantity' || hl === 'count') && qtyCol < 0) qtyCol = idx;
    });
    if (sizeCol >= 0 && typeof sublimation_sortRowsBySize === 'function') {
        rows = sublimation_sortRowsBySize(rows, sizeCol);
    }
    var displayCols = [];
    headers.forEach(function(h, idx) {
        var cssClass = '';
        if (nameCols.indexOf(idx) >= 0) cssClass = 'roster-name';
        else if (idx === sizeCol) cssClass = 'roster-size';
        else if (idx === numCol) cssClass = 'roster-number';
        else if (idx === qtyCol) cssClass = 'roster-qty';
        displayCols.push({header: h || '(Col ' + (idx + 1) + ')', colIdx: idx, cssClass: cssClass});
    });
    tbody.innerHTML = '';
    var empty = document.getElementById('garment_rosterEmpty');
    if (empty) empty.style.display = 'none';
    var headerHtml = '<th style="width:50px">#</th>';
    displayCols.forEach(function(col) { headerHtml += '<th>' + _escHtml(col.header) + '</th>'; });
    headerHtml += '<th style="width:40px"></th>';
    theadRow.innerHTML = headerHtml;
    var added = 0;
    rows.forEach(function(row) {
        var hasData = false;
        for (var ci = 0; ci < row.length; ci++) { if (row[ci] !== undefined && row[ci] !== null && String(row[ci]).trim() !== '') { hasData = true; break; } }
        if (!hasData) return;
        var tr = document.createElement('tr');
        tr.className = 'roster-row';
        var cellsHtml = '<td class="text-center align-middle">' + (added + 1) + '</td>';
        displayCols.forEach(function(col) {
            var rawVal = (col.colIdx >= 0 && row[col.colIdx] !== undefined && row[col.colIdx] !== null) ? String(row[col.colIdx]).trim() : '';
            var displayVal = rawVal;
            if (col.cssClass === 'roster-size') displayVal = normalizeSize(displayVal.toUpperCase());
            else if (col.cssClass === 'roster-number' || col.cssClass === 'roster-qty') {
                var numParsed = parseFloat(displayVal);
                if (!isNaN(numParsed) && String(numParsed) === displayVal) displayVal = String(numParsed);
                else displayVal = displayVal.replace(/\.0+$/, '');
            }
            cellsHtml += '<td class="align-middle">' + _escHtml(displayVal)
                + '<input type="hidden" class="' + col.cssClass + '" data-header="' + _escHtml(col.header) + '" value="' + _escHtml(displayVal) + '">'
                + '</td>';
        });
        cellsHtml += '<td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="garment_removeRosterRow(this)" title="Remove"><i class="fas fa-times"></i></button></td>';
        tr.innerHTML = cellsHtml;
        tbody.appendChild(tr);
        added++;
    });
    if (added === 0 && empty) { tbody.appendChild(empty); empty.style.display = ''; empty.querySelector('td').colSpan = displayCols.length + 2; }
    garment_updateRosterCount();
    var input = document.getElementById('garment_excelInput');
    var badgeEl = document.getElementById('garment_uploadedFileBadge');
    if (badgeEl) {
        if (added > 0) {
            badgeEl.style.display = '';
            badgeEl.innerHTML = '<i class="fas fa-paperclip me-1"></i>Uploaded: ' + _escHtml(window.garment_lastFileName || 'file') + ' (' + added + ' rows)';
        } else {
            badgeEl.style.display = 'none';
            badgeEl.textContent = '';
        }
    }
    if (input) input.value = '';
    if (typeof garment_updatePrintSummary === 'function') garment_updatePrintSummary();
    if (typeof showToast === 'function') showToast(added > 0 ? (added + ' person' + (added > 1 ? 's' : '') + ' imported from Excel!') : 'No valid rows to import from Excel.', added > 0 ? 'success' : 'warning');
};
window.garment_getRosterData = function() {
    var rows = document.querySelectorAll('#garment_rosterBody tr.roster-row');
    var out = [];
    rows.forEach(function(r) {
        var nameEl = r.querySelector('.roster-name');
        var sizeEl = r.querySelector('.roster-size');
        var numEl = r.querySelector('.roster-number');
        var qtyEl = r.querySelector('.roster-qty');
        out.push({
            name: nameEl ? nameEl.value : (r.querySelector('td:nth-child(2)') ? r.querySelector('td:nth-child(2)').textContent.trim() : ''),
            number: numEl ? numEl.value : '',
            size: sizeEl ? sizeEl.value : '',
            qty: qtyEl ? (parseInt(qtyEl.value) || 1) : 1
        });
    });
    return out;
};
window.garment_clearRoster = function() {
    var tbody = document.getElementById('garment_rosterBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    // Ibalik ang default header (nire-replace ito ng Excel column headers).
    var theadRow = document.getElementById('garment_rosterHeader');
    if (theadRow) {
        theadRow.innerHTML = '<th style="width:50px">#</th>'
            + '<th>Name</th>'
            + '<th style="width:80px">Size</th>'
            + '<th style="width:40px">Qty</th>'
            + '<th style="width:40px"></th>';
    }
    // Ang placeholder ay nasa loob ng tbody, kaya nawawala kapag nag-innerHTML = ''.
    // I-recreate para may nakikitang "Add names above..." hint pagkatapos ng reset.
    var empty = document.getElementById('garment_rosterEmpty');
    if (!empty) {
        empty = document.createElement('tr');
        empty.id = 'garment_rosterEmpty';
        empty.innerHTML = '<td colspan="5" class="text-center text-muted small py-3"><i class="fas fa-user-plus me-1"></i> Add names above to build your roster.</td>';
    }
    tbody.appendChild(empty);
    empty.style.display = '';
    var emptyTd = empty.querySelector('td');
    if (emptyTd) emptyTd.colSpan = 5;
    // Wala nang rows -> burahin din ang brand-per-size overrides
    window.garment_sizeBrands = {};
    window.__capSizeBrandSig = null;
    var sbCard = document.getElementById('garment_sizeBrandCard');
    if (sbCard) sbCard.style.display = 'none';
    var sbBody = document.getElementById('garment_sizeBrandBody');
    if (sbBody) sbBody.innerHTML = '';
    garment_updateRosterCount();
    if (typeof garment_updatePrintSummary === 'function') garment_updatePrintSummary();
};

// ===== Clear / Reset ng "Sizes with name" =====
// Kapag mali ang na-upload na file (o mali ang Brand/Type/Color/Material),
// isang click lang: tanggal lahat ng rows + reset ang spec fields + refresh ang tab.
window.garment_resetSizesWithName = function(skipConfirm) {
    var tbody = document.getElementById('garment_rosterBody');
    var rowCount = tbody ? tbody.querySelectorAll('tr.roster-row').length : 0;
    var specIds = ['garment_specBrand', 'garment_specType', 'garment_specColor', 'garment_specMaterial'];
    var hasSpec = specIds.some(function(id) {
        var el = document.getElementById(id);
        return el && (el.value || '').trim() !== '';
    });
    if (!rowCount && !hasSpec) {
        if (typeof showToast === 'function') showToast('Wala pang naka-upload o napiling Brand/Type — wala nang i-reset.', 'info');
        return;
    }
    if (!skipConfirm) {
        var msg = 'I-reset ang "Sizes with name"?\n\n';
        if (rowCount) msg += '• Tatanggalin ang ' + rowCount + ' naka-upload na row\n';
        if (hasSpec) msg += '• Burahin ang Brand / Type / Color / Material\n';
        msg += '• Mawawala rin ang size breakdown at print summary\n\nSige pa?';
        if (!window.confirm(msg)) return;
    }
    // 1) Rows + header
    if (typeof garment_clearRoster === 'function') garment_clearRoster();
    // 2) File input (para makapag-upload ulit ng parehong file)
    var fileInput = document.getElementById('garment_excelInput');
    if (fileInput) fileInput.value = '';
    var badge = document.getElementById('garment_uploadedFileBadge');
    if (badge) { badge.style.display = 'none'; badge.textContent = ''; }
    // 3) Spec fields
    specIds.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    // 4) Size pricing card
    var spCard = document.getElementById('garment_sizePricingCard');
    if (spCard) spCard.style.display = 'none';
    var spBody = document.getElementById('garment_sizePricingBody');
    if (spBody) spBody.innerHTML = '';
    var spQty = document.getElementById('garment_sizePricingQty');
    if (spQty) spQty.textContent = '0';
    var spTot = document.getElementById('garment_sizePricingTotal');
    if (spTot) spTot.textContent = '\u20B10.00';
    // 5) Refresh gate + summary
    if (typeof garment_updateSpecGate === 'function') garment_updateSpecGate();
    if (typeof garment_updatePrintSummary === 'function') garment_updatePrintSummary();
    if (typeof showToast === 'function') showToast('Na-reset na ang Sizes with name — pwedeng mag-upload ulit.', 'success');
};

// ===== GARMENT SPEC (Brand / Type-Collar / Color) + auto size pricing =====
var garment_specItems = null;      // cached /api/products-for-box/garment items
var garment_specLoading = false;

window.garment_initSpec = function() {
    // Attach gate listeners (idempotent)
    ['garment_specBrand', 'garment_specType', 'garment_specColor', 'garment_specMaterial'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el && !el.dataset.specBound) {
            el.dataset.specBound = '1';
            el.addEventListener('input', function() { garment_updateSpecGate(); });
            el.addEventListener('change', function() { garment_updateSpecGate(); });
        }
    });
    garment_updateSpecGate();
    if (garment_specItems || garment_specLoading) return;
    garment_specLoading = true;
    fetch('/api/products-for-box/garment', { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(items) {
            garment_specItems = Array.isArray(items) ? items : [];
            garment_specLoading = false;
            function uniq(key) {
                var seen = {};
                garment_specItems.forEach(function(it) {
                    var v = (it[key] || '').toString().trim();
                    if (v) seen[v] = true;
                });
                return Object.keys(seen).sort();
            }
            function fill(listId, values) {
                var dl = document.getElementById(listId);
                if (!dl) return;
                dl.innerHTML = '';
                values.forEach(function(v) {
                    var o = document.createElement('option');
                    o.value = v;
                    dl.appendChild(o);
                });
            }
            fill('garment_specBrandOptions', uniq('brand'));
            fill('garment_specTypeOptions', uniq('shirt_type'));
            fill('garment_specColorOptions', uniq('color'));
            fill('garment_specMaterialOptions', uniq('material'));
        })
        .catch(function() { garment_specLoading = false; });
};

window.garment_updateSpecGate = function() {
    var brand = (document.getElementById('garment_specBrand') || {}).value || '';
    var type = (document.getElementById('garment_specType') || {}).value || '';
    var ready = brand.trim() !== '' && type.trim() !== '';
    var btn = document.getElementById('garment_excelBtn');
    var hint = document.getElementById('garment_excelGateHint');
    if (btn) btn.disabled = !ready;
    if (hint) hint.style.display = ready ? 'none' : '';
    if (ready && garment_getRosterData && garment_getRosterData().length && typeof garment_updatePrintSummary === 'function') garment_updatePrintSummary();
};

// Find the unit price for a given size using the selected Brand / Type-Collar / Color.
// Size order helper para sa "available sizes" hint.
window.garment_sizeOrder = ['XS','S','M','L','XL','2XL','3XL','4XL','5XL','6XL','7XL','8XL'];

// Price lookup na may progressive fallback:
//   1) brand + type + size + color + material   (exact)
//   2) brand + type + size + color              (ignore material)
//   3) brand + type + size + material           (ignore color)
//   4) brand + type + size                      (ignore both)
// Kung wala pa rin -> reason 'no-size' + listahan ng available sizes para malaman agad
// kung kulang ang Master Items (hindi lang basta "no price").
// ===== Brand per size (mixed brand) =====
// Halimbawa: APPLE TEE hanggang 3XL lang, pero ang 5XL ay GILDAN.
// window.garment_sizeBrands = { '5XL': 'GILDAN' }  -> size-level override.
// Blangko = inherit sa Brand field sa itaas (backwards compatible).
window.garment_sizeBrands = {};

window.garment_effectiveBrand = function(size) {
    var s = (typeof normalizeSize === 'function' ? normalizeSize((size || '').toUpperCase()) : (size || '').toUpperCase());
    var ov = window.garment_sizeBrands[s];
    if (ov && String(ov).trim()) return String(ov).trim().toUpperCase();
    return ((document.getElementById('garment_specBrand') || {}).value || '').trim().toUpperCase();
};

// Listahan ng brands na may row para sa size na ito (same Type/Collar).
window.garment_brandsForSize = function(size) {
    var out = [], seen = {};
    if (!garment_specItems) return out;
    var type = ((document.getElementById('garment_specType') || {}).value || '').trim().toUpperCase();
    var sz = (typeof normalizeSize === 'function' ? normalizeSize((size || '').toUpperCase()) : (size || '').toUpperCase());
    garment_specItems.forEach(function(it) {
        if (type && (it.shirt_type || '').toUpperCase() !== type) return;
        var its = (typeof normalizeSize === 'function' ? normalizeSize((it.size || '').toUpperCase()) : (it.size || '').toUpperCase());
        if (its !== sz) return;
        var b = (it.brand || '').trim().toUpperCase();
        if (!b || seen[b]) return;
        seen[b] = true;
        out.push(b);
    });
    out.sort();
    return out;
};

window.garment_renderSizeBrandMap = function(perSize, keys) {
    var card = document.getElementById('garment_sizeBrandCard');
    var body = document.getElementById('garment_sizeBrandBody');
    if (!card || !body) return;
    var specBrand = ((document.getElementById('garment_specBrand') || {}).value || '').trim().toUpperCase();
    keys = keys || Object.keys(perSize || {});
    if (!keys.length) { card.style.display = 'none'; body.innerHTML = ''; return; }
    var html = '';
    keys.forEach(function(s) {
        var ov = window.garment_sizeBrands[s] || '';
        var brands = window.garment_brandsForSize(s);
        var inheritedOk = specBrand && brands.indexOf(specBrand) >= 0;
        var q = (perSize || {})[s] || 0;
        html += '<div class="d-flex align-items-center justify-content-between mb-1 gap-2">'
             +      '<span style="min-width:78px;">' + _escHtml(s) + ' <span class="text-muted">x' + q + '</span></span>'
             +      '<select class="form-select form-select-sm garment-size-brand" data-size="' + _escHtml(s) + '" style="max-width:240px;">'
             +      '<option value="">' + (specBrand ? ('Gamitin: ' + _escHtml(specBrand)) : 'Gamitin ang Brand sa itaas')
             +      (specBrand && !inheritedOk ? ' \u2014 walang ' + _escHtml(s) : '') + '</option>';
        brands.forEach(function(b) {
            html += '<option value="' + _escHtml(b) + '"' + (ov && String(ov).toUpperCase() === b ? ' selected' : '') + '>' + _escHtml(b) + '</option>';
        });
        html += '</select></div>';
        if (!inheritedOk && brands.length) {
            html += '<div class="text-warning mb-1" style="font-size:.72rem;line-height:1.15;">'
                 +  '<i class="fas fa-info-circle me-1"></i>Walang ' + _escHtml(s) + ' sa ' + _escHtml(specBrand || 'napiling brand') + ' \u2014 meron sa: ' + _escHtml(brands.join(', '))
                 +  '</div>';
        }
    });
    body.innerHTML = html;
    card.style.display = '';
    body.querySelectorAll('.garment-size-brand').forEach(function(sel) {
        sel.addEventListener('change', function() {
            var sz = sel.getAttribute('data-size');
            if (sel.value) window.garment_sizeBrands[sz] = sel.value;
            else delete window.garment_sizeBrands[sz];
            if (typeof garment_updatePrintSummary === 'function') garment_updatePrintSummary();
        });
    });
};

window.garment_findItemPrice = function(size) {
    if (!garment_specItems) return { price: null, reason: 'no-data', note: 'Hindi pa nag-load ang price list' };
    var brand = (typeof window.garment_effectiveBrand === 'function')
        ? window.garment_effectiveBrand(size)
        : ((document.getElementById('garment_specBrand') || {}).value || '').trim().toUpperCase();
    var type = ((document.getElementById('garment_specType') || {}).value || '').trim().toUpperCase();
    var color = ((document.getElementById('garment_specColor') || {}).value || '').trim().toUpperCase();
    var material = ((document.getElementById('garment_specMaterial') || {}).value || '').trim().toUpperCase();
    var _nm = function(v) { return (typeof normalizeSize === 'function' ? normalizeSize((v || '').toUpperCase()) : (v || '').toUpperCase()); };
    var sz = _nm(size);
    if (!brand || !type) return { price: null, reason: 'no-spec', note: 'Pumili muna ng Brand at Type/Collar' };

    function pick(useColor, useMaterial) {
        for (var i = 0; i < garment_specItems.length; i++) {
            var it = garment_specItems[i];
            if ((it.brand || '').toUpperCase() !== brand) continue;
            if ((it.shirt_type || '').toUpperCase() !== type) continue;
            if (_nm(it.size || '') !== sz) continue;
            if (useColor && color) {
                var ic = (it.color || '').toUpperCase();
                if (ic !== color && ic !== 'ALL COLOR') continue;
            }
            if (useMaterial && material) {
                if ((it.material || '').toUpperCase() !== material) continue;
            }
            return it;
        }
        return null;
    }

    var hit = pick(true, true);
    if (hit) return { price: parseFloat(hit.base_price) || 0, reason: 'ok', color: hit.color, material: hit.material };

    var relaxed = [];
    if (material) {
        hit = pick(true, false);
        if (hit) return {
            price: parseFloat(hit.base_price) || 0, reason: 'material-fallback',
            color: hit.color, material: hit.material,
            note: 'Walang "' + material + '" para sa ' + brand + ' — "' + (hit.material || '—') + '" ang price na ginamit'
        };
        relaxed.push('material');
    }
    if (color) {
        hit = pick(false, !!material);
        if (hit) return {
            price: parseFloat(hit.base_price) || 0, reason: 'color-fallback',
            color: hit.color, material: hit.material,
            note: 'Walang kulay "' + color + '" — "' + (hit.color || '—') + '" ang price na ginamit'
        };
        relaxed.push('color');
    }
    if (!relaxed.length || relaxed.length > 1) {
        hit = pick(false, false);
        if (hit) return {
            price: parseFloat(hit.base_price) || 0, reason: 'spec-fallback',
            color: hit.color, material: hit.material,
            note: 'Walang exact ' + [color, material].filter(Boolean).join(' ') + ' — "' + (hit.color || '') + ' ' + (hit.material || '') + '" ang price na ginamit'
        };
    }

    // Wala talagang size row — ipakita kung anong sizes ang meron.
    var avail = {};
    garment_specItems.forEach(function(it) {
        if ((it.brand || '').toUpperCase() !== brand) return;
        if ((it.shirt_type || '').toUpperCase() !== type) return;
        if (color) {
            var ic = (it.color || '').toUpperCase();
            if (ic !== color && ic !== 'ALL COLOR') return;
        }
        avail[_nm(it.size || '')] = true;
    });
    var availList = Object.keys(avail).sort(function(a, b) {
        var ia = window.garment_sizeOrder.indexOf(a), ib = window.garment_sizeOrder.indexOf(b);
        if (ia < 0) ia = 99; if (ib < 0) ib = 99;
        return ia - ib;
    });
    if (!availList.length) {
        // walang row kahit anong size -> baka wala ang color/material combo
        var anyColor = {};
        garment_specItems.forEach(function(it) {
            if ((it.brand || '').toUpperCase() !== brand) return;
            if ((it.shirt_type || '').toUpperCase() !== type) return;
            anyColor[(it.color || '—')] = true;
        });
        var colorList = Object.keys(anyColor);
        return {
            price: null, reason: 'no-item', availableSizes: [],
            note: 'Walang price row para sa ' + brand + ' / ' + type + ' / ' + (color || '—') +
                  (colorList.length ? ' (meron na kulay: ' + colorList.slice(0, 8).join(', ') + (colorList.length > 8 ? '…' : '') + ')' : '')
        };
    }
    return {
        price: null, reason: 'no-size', availableSizes: availList,
        note: 'Walang size ' + (size || '') + ' sa ' + brand + ' ' + (color ? '/ ' + color + ' ' : '') + '(meron: ' + availList.join(', ') + ')'
    };
};

window.garment_computeSizePricing = function() {
    var card = document.getElementById('garment_sizePricingCard');
    var body = document.getElementById('garment_sizePricingBody');
    var qtyEl = document.getElementById('garment_sizePricingQty');
    var totEl = document.getElementById('garment_sizePricingTotal');
    var rows = (typeof garment_getRosterData === 'function') ? garment_getRosterData() : [];
    if (!rows.length) {
        if (card) card.style.display = 'none';
        if (body) body.innerHTML = '';
        return { qty: 0, amount: 0, hasRows: false, missing: [], perSize: {} };
    }
    // Aggregate qty per size
    var perSize = {};
    rows.forEach(function(r) {
        var s = (typeof normalizeSize === 'function' ? normalizeSize((r.size || '').toUpperCase()) : (r.size || '').toUpperCase()) || '—';
        perSize[s] = (perSize[s] || 0) + (parseInt(r.qty) || 1);
    });
    var order = ['XS','S','M','L','XL','2XL','3XL','4XL','5XL','6XL','7XL','8XL'];
    var keys = Object.keys(perSize).sort(function(a, b) {
        var ia = order.indexOf(a), ib = order.indexOf(b);
        if (ia < 0) ia = 99; if (ib < 0) ib = 99;
        return ia - ib;
    });
    // Brand-per-size card (re-render lang kapag nagbago ang sizes/spec/override)
    try {
        var _sig = keys.join(',') + '|'
            + (((document.getElementById('garment_specBrand') || {}).value) || '').trim().toUpperCase() + '|'
            + (((document.getElementById('garment_specType') || {}).value) || '').trim().toUpperCase() + '|'
            + (garment_specItems ? garment_specItems.length : 0) + '|'
            + JSON.stringify(window.garment_sizeBrands || {});
        if (window.__capSizeBrandSig !== _sig) {
            window.__capSizeBrandSig = _sig;
            if (typeof window.garment_renderSizeBrandMap === 'function') window.garment_renderSizeBrandMap(perSize, keys);
        }
    } catch (e) { /* huwag ipa-crash ang summary sa cosmetic na bagay */ }
    var html = '', totalQty = 0, totalAmt = 0, missing = [];
    keys.forEach(function(s) {
        var q = perSize[s];
        var res = garment_findItemPrice(s);
        totalQty += q;
        if (res.price == null) {
            missing.push(s);
            html += '<div class="d-flex justify-content-between"><span>' + _escHtml(s) + ' \u00D7 ' + q + '</span><span class="text-danger">no price</span></div>';
            if (res.note) html += '<div class="text-danger" style="font-size:.75rem;line-height:1.15;">' + _escHtml(res.note) + '</div>';
        } else {
            var amt = res.price * q;
            totalAmt += amt;
            var _cardSpecB = ((document.getElementById('garment_specBrand') || {}).value || '').trim().toUpperCase();
            var _cardUsedB = (typeof window.garment_effectiveBrand === 'function') ? window.garment_effectiveBrand(s) : _cardSpecB;
            var _cardTag = (_cardUsedB && _cardUsedB !== _cardSpecB) ? ' <span class="badge bg-info text-dark" style="font-size:.62rem;">' + _escHtml(_cardUsedB) + '</span>' : '';
            html += '<div class="d-flex justify-content-between"><span>' + _escHtml(s) + _cardTag + ' × ' + q + ' <span class="text-muted">@ \u20B1' + res.price.toFixed(2) + '</span></span><span>\u20B1' + amt.toFixed(2) + '</span></div>';
            if (res.note) html += '<div class="text-warning" style="font-size:.72rem;line-height:1.15;"><i class="fas fa-info-circle me-1"></i>' + _escHtml(res.note) + '</div>';
        }
    });
    if (missing.length) {
        html += '<div class="alert alert-warning py-1 px-2 mt-2 mb-0 small"><i class="fas fa-exclamation-triangle me-1"></i>Walang price row para sa: ' + _escHtml(missing.join(', ')) + ' (brand/type/color/size na pinili).</div>';
    }
    if (body) body.innerHTML = html;
    if (qtyEl) qtyEl.textContent = totalQty;
    if (totEl) totEl.textContent = '₱' + totalAmt.toFixed(2);
    if (card) card.style.display = '';
    return { qty: totalQty, amount: totalAmt, hasRows: true, missing: missing, perSize: perSize, keys: keys };
};

// ===== Garment Add-to-Cart gate =====
// Blocks Add to Cart until lahat ng kailangan ay kumpleto.
window.garment_cartMissing = function() {
    var missing = [];
    var v = function(id) { var el = document.getElementById(id); return el ? (el.value || '').trim() : ''; };

    if (!v('garment_projectName')) missing.push('Project Name');
    if (!v('garment_department')) missing.push('Shop / Department');

    var rosterRows = (typeof garment_getRosterData === 'function') ? (garment_getRosterData() || []) : [];
    if (rosterRows.length > 0) {
        if (!v('garment_specBrand') || !v('garment_specType')) {
            missing.push('Garment Specification (Brand / Type-Collar)');
        }
    } else {
        var hasProductRow = false;
        document.querySelectorAll('#garment_productRowsContainer .product-row').forEach(function(row) {
            var sel = row.querySelector('.product-select');
            var qty = parseInt((row.querySelector('.product-quantity') || {}).value) || 0;
            if (sel && sel.value && qty > 0) hasProductRow = true;
        });
        if (!hasProductRow) missing.push('Products (o mag-upload ng Sizes with name)');
    }

    if (!v('garment_dateNeeded')) missing.push('Date Needed');
    if (!window.garment_uploadedMockup || !window.garment_uploadedMockup.dataUrl) missing.push('Mock-up design');

    var printType = v('garment_printTypeSelect');
    if (!printType) {
        missing.push('Print Type');
    } else if (!window.garment_selectedPrintSizes || !window.garment_selectedPrintSizes.length) {
        missing.push('Print Sizes');
    }

    if (!window.garment_orderSlipViewed) missing.push('Print Order Slip');
    return missing;
};

window.garment_refreshCartGate = function() {
    var btn = document.getElementById('garment_addItemBtn');
    var hint = document.getElementById('garment_cartGateHint');
    if (!btn) return;
    var missing = window.garment_cartMissing();
    var blocked = missing.length > 0;
    btn.disabled = blocked;
    btn.classList.toggle('disabled', blocked);
    btn.title = blocked ? ('Kulang pa: ' + missing.join(', ')) : '';
    if (hint) {
        hint.style.display = blocked ? '' : 'none';
        hint.innerHTML = blocked
            ? '<i class="fas fa-lock me-1"></i>Kulang pa: ' + missing.map(function(m) { return _escHtml(m); }).join(', ')
            : '';
    }
};

// Live refresh ng gate habang nag-e-edit sa garment modal
(function() {
    var pending = null;
    function schedule() {
        var body = document.getElementById('garmentModalBody');
        if (!body || body.style.display === 'none') return;
        if (pending) return;
        pending = setTimeout(function() { pending = null; window.garment_refreshCartGate(); }, 150);
    }
    ['input', 'change', 'click', 'keyup'].forEach(function(evt) {
        document.addEventListener(evt, schedule, true);
    });
})();

// Garment add to cart
window.garment_addItemToCart = function() {
    // Shop / department chosen in the modal (iPrint or Consol — different managers)
    var department = ((document.getElementById('garment_department') || {}).value || 'iprint').toLowerCase();
    if (['iprint', 'consol'].indexOf(department) === -1) department = 'iprint';

    // Hard gate: huwag pahintulutan kung may kulang pa
    var gateMissing = window.garment_cartMissing();
    if (gateMissing.length) {
        window.garment_refreshCartGate();
        alert('Kulang pa bago ma-add sa cart:\n\n• ' + gateMissing.join('\n• '));
        return;
    }
    var rows = document.querySelectorAll('#garment_productRowsContainer .product-row');
    var subItems = [];
    var validationError = false;
    var dateNeededEl = document.getElementById('garment_dateNeeded');
    if (dateNeededEl && !dateNeededEl.value) {
        alert('Please select a Date Needed for this garment order.');
        dateNeededEl.focus();
        return;
    }

    // Validate Order Slip was printed (same gate as Full Sublimation)
    if (!window.garment_orderSlipViewed) {
        alert('Please click "Print Order Slip" first before adding to cart.');
        return;
    }

    // Security gate: a mock-up design image is REQUIRED
    if (!window.garment_uploadedMockup || !window.garment_uploadedMockup.dataUrl) {
        alert('Kailangan munang mag-upload ng mock-up design image bago i-add sa cart.');
        var mockupUploadEl = document.getElementById('garment_mockupUpload');
        if (mockupUploadEl) mockupUploadEl.focus();
        return;
    }
    var notes = document.getElementById('garment_productNotes');
    var printTypeEl = document.getElementById('garment_printTypeSelect');

    // Priority (mirrors Order Summary + print slip): if "Sizes with name" (roster)
    // has rows, build items from size pricing and SKIP the Product Details rows.
    var rosterArr = (typeof garment_getRosterData === 'function') ? garment_getRosterData() : [];
    if (rosterArr.length > 0) {
        var specBrand = ((document.getElementById('garment_specBrand') || {}).value || '').trim();
        var specType = ((document.getElementById('garment_specType') || {}).value || '').trim();
        var specColor = ((document.getElementById('garment_specColor') || {}).value || '').trim();
        var specMaterial = ((document.getElementById('garment_specMaterial') || {}).value || '').trim();
        var specShort = [specBrand, specType, specColor].filter(Boolean).join(' ').trim();
        var sizeRes = (typeof garment_computeSizePricing === 'function') ? garment_computeSizePricing() : { keys: [], perSize: {}, missing: [] };
        if (sizeRes.missing && sizeRes.missing.length) {
            alert('Walang price para sa size: ' + sizeRes.missing.join(', ') + '.\n\nPumili ng ibang Brand sa "Brand per size" (kung wala ang size sa unang brand), o i-check ang Master Items.');
            return;
        }
        (sizeRes.keys || Object.keys(sizeRes.perSize || {})).forEach(function(s) {
            var q = (sizeRes.perSize || {})[s] || 0;
            if (q <= 0) return;
            var pRes = (typeof garment_findItemPrice === 'function') ? garment_findItemPrice(s) : { price: null };
            if (pRes.price == null || pRes.price <= 0) return;
            var label = ((specShort ? specShort + ' ' : '') + s).trim();
            subItems.push({
                productId: null,
                name: label,
                shortName: label,
                brand: (typeof window.garment_effectiveBrand === 'function') ? window.garment_effectiveBrand(s) : specBrand,
                brandSpec: specBrand,
                size: s,
                color: specColor,
                quantity: q,
                unitPrice: pRes.price,
                totalPrice: q * pRes.price,
                fromRoster: true
            });
        });
    } else {
    rows.forEach(function(row, index) {
        if (validationError) return;
        var select = row.querySelector('.product-select');
        var qtyInput = row.querySelector('.product-quantity');
        if (!select || !qtyInput) return;
        var selectedOption = select.options[select.selectedIndex];
        var quantity = parseInt(qtyInput.value) || 0;
        var price = selectedOption && selectedOption.value ? parseFloat(selectedOption.dataset.price) : 0;
        if (!select.value) { alert('Please select a product for row ' + (index + 1)); select.focus(); validationError = true; return; }
        if (quantity <= 0) { alert('Please enter valid quantity for row ' + (index + 1)); qtyInput.focus(); validationError = true; return; }
        if (price <= 0) { alert('Invalid price for product in row ' + (index + 1)); validationError = true; return; }
        var brand = selectedOption.dataset.brand || '';
        var size = selectedOption.dataset.size || '';
        var color = selectedOption.dataset.color || '';
        var productName = selectedOption.dataset.productName || 'Unknown Product';
        var shortName = [brand, color, size].filter(Boolean).join(' ');
        if (!shortName) shortName = productName;
        subItems.push({
            productId: select.value,
            name: productName,
            shortName: shortName,
            brand: brand,
            size: size,
            color: color,
            quantity: quantity,
            unitPrice: price,
            totalPrice: quantity * price
        });
    });
    }
    if (validationError) return;
    if (subItems.length === 0) { alert('Please add at least one product'); return; }
    // Compute combined totals
    var totalQty = 0;
    var totalProductPrice = 0;
    subItems.forEach(function(si) {
        totalQty += si.quantity;
        totalProductPrice += si.totalPrice;
    });
    // Build printing summary (mirroring garment_updatePrintSummary logic)
    var printDetails = null;
    if (garment_selectedPrintSizes && garment_selectedPrintSizes.length > 0) {
        var printTypeName = printTypeEl ? printTypeEl.options[printTypeEl.selectedIndex]?.text || printTypeEl.value : '';
        var printPrices = garment_printingData.prices || [];
        var combos = garment_printingData.combos || [];
        var selectedPrintNames = [];
        // Resolve upgrades first so the receipt matches the sidebar
        var resolvedSub = garment_resolvePrint(garment_selectedPrintSizes);
        var effectiveSubSizes = resolvedSub.sizes;
        var printCostRaw = parseFloat(resolvedSub.surcharge) || 0;
        effectiveSubSizes.forEach(function(sizeId) {
            for (var pi = 0; pi < printPrices.length; pi++) {
                if (printPrices[pi].id === sizeId) {
                    selectedPrintNames.push(printPrices[pi].name);
                    printCostRaw += printPrices[pi].price;
                    break;
                }
            }
        });
        var printQty = totalQty;
        // Combo (count-aware, same logic as garment_updatePrintSummary)
        var subSizeCounts = {};
        effectiveSubSizes.forEach(function(id) { subSizeCounts[id] = (subSizeCounts[id] || 0) + 1; });
        var comboDiscount = 0;
        var comboLabel = '';
        combos.forEach(function(c) {
            var s1 = parseInt(c.size1_id);
            var s2 = parseInt(c.size2_id);
            var matched = (s1 === s2)
                ? (subSizeCounts[s1] || 0) >= 2
                : ((subSizeCounts[s1] || 0) >= 1 && (subSizeCounts[s2] || 0) >= 1);
            if (matched && c.discount > comboDiscount) {
                comboDiscount = c.discount;
                if (c.size1_name && c.size2_name) comboLabel = c.size1_name + ' + ' + c.size2_name;
            }
        });
        var subtotalBeforeBulk = printCostRaw * printQty;
        var comboTotal = comboDiscount * printQty;
        var subtotalAfterCombo = subtotalBeforeBulk - comboTotal;
        // Bulk discount (respect the A3/A4 gate like the summary)
        var bulkTiers = garment_printingData.bulk_tiers || [];
        var bulkGateSub = garment_printingData.bulk_gate || { active: false, sizes: [] };
        var bulkDiscount = 0;
        var bulkLabelSub = '';
        var bulkGateOk = true;
        if (bulkGateSub.active) {
            var gateSubNames = (bulkGateSub.sizes && bulkGateSub.sizes.length ? bulkGateSub.sizes : ['A3', 'A4'])
                .map(function(n) { return String(n).trim().toUpperCase(); });
            var pickedSubNames = selectedPrintNames.map(function(n) { return String(n).trim().toUpperCase(); });
            bulkGateOk = pickedSubNames.some(function(n) { return gateSubNames.indexOf(n) !== -1; });
        }
        for (var bi = bulkGateOk ? bulkTiers.length - 1 : -1; bi >= 0; bi--) {
            var tier = bulkTiers[bi];
            if (printQty >= tier.min && printQty <= tier.max) {
                if (tier.type === 'percentage') {
                    bulkDiscount = subtotalAfterCombo * (tier.percent / 100);
                } else if (tier.type === 'fixed') {
                    bulkDiscount = tier.amount * printQty;
                }
                bulkLabelSub = tier.label || '';
                break;
            }
        }
        // Special price override
        var isSpecialPrice = window.garment_hasSpecialPrice || false;
        var specialTotal = 0;
        var specialReason = '';
        if (isSpecialPrice) {
            var spInput = document.getElementById('garment_specialPrintTotal');
            var reasonInput = document.getElementById('garment_specialPriceReason');
            if (spInput) specialTotal = parseFloat(spInput.value) || 0;
            if (reasonInput) specialReason = reasonInput.value.trim();
        }
        printDetails = {
            printType: printTypeName,
            printSizes: selectedPrintNames,
            printCostPerItem: isSpecialPrice && specialTotal > 0 ? (specialTotal / printQty) : printCostRaw,
            printQty: printQty,
            printSubtotal: isSpecialPrice && specialTotal > 0 ? specialTotal : (subtotalAfterCombo - bulkDiscount),
            comboDiscount: comboDiscount,
            comboTotal: comboTotal,
            comboLabel: comboLabel,
            bulkLabel: bulkLabelSub,
            bulkDiscount: bulkDiscount,
            isSpecialPrice: isSpecialPrice,
            specialTotal: isSpecialPrice ? specialTotal : 0,
            specialReason: specialReason
        };
    }
    // Create single garment order item
    var garmentItem = {
        id: Date.now(),
        productType: 'garment',
        department: department,
        name: 'Garment Order',
        totalQty: totalQty,
        totalProductPrice: totalProductPrice,
        quantity: totalQty,
        unitPrice: totalProductPrice,
        totalPrice: totalProductPrice,
        date_needed: document.getElementById('garment_dateNeeded')?.value || '',
        project_name: (document.getElementById('garment_projectName')?.value || '').trim(),
        notes: notes ? notes.value.trim() : '',
        timestamp: new Date().toISOString(),
        subItems: subItems,
        printing: printDetails,
        sizesWithNames: (typeof garment_getRosterData === 'function') ? garment_getRosterData() : [],
        mockup: (window.garment_uploadedMockup && window.garment_uploadedMockup.dataUrl) ? { name: window.garment_uploadedMockup.name, dataUrl: window.garment_uploadedMockup.dataUrl } : null
    };
    // === Submit hook (sale page) ===
    // Kapag naka-define ang window.garment_submitOverride, doon ipapasa ang built item
    // (Add Product / Reprocess sa sale details) — pareho pa rin ang buong form/validation.
    if (typeof window.garment_submitOverride === 'function') {
        window.garment_submitOverride(garmentItem);
        return;
    }
    if (typeof selectedItems !== 'undefined') selectedItems.push(garmentItem);
    if (typeof updateSelectedItemsDisplay === 'function') updateSelectedItemsDisplay();
    if (typeof updateOrderSummary === 'function') updateOrderSummary();
    garment_clearMockup();
    if (typeof garment_clearRoster === 'function') garment_clearRoster();
    garment_clearPrintSelection();
    if (typeof garment_initializeProductRows === 'function') garment_initializeProductRows();
    /* Department auto-assigned; no reset needed */
    var notesEl = document.getElementById('garment_productNotes');
    if (notesEl) notesEl.value = '';
    var modalEl = document.getElementById('productModal');
    if (modalEl) {
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
    showToast('Garment order added to cart!', 'success');
};

// ===== GARMENT ORDER SLIP (print preview before Add to Cart) =====
window.garment_printOrderSlip = function() {
    function gEsc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function(c) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'})[c]; }); }
    function gVal(id) { var el = document.getElementById(id); return el ? (el.value || '') : ''; }

    // Security gate: require a mock-up image before printing the slip too
    if (!window.garment_uploadedMockup || !window.garment_uploadedMockup.dataUrl) {
        alert('Kailangan munang mag-upload ng mock-up design image bago i-print ang Order Slip.');
        var mockupUploadEl2 = document.getElementById('garment_mockupUpload');
        if (mockupUploadEl2) mockupUploadEl2.focus();
        return;
    }

    document.getElementById('gps_projectName').textContent = gVal('garment_projectName');
    var gShop = gVal('garment_department');
    var gShopLabel = document.getElementById('gps_shop');
    if (gShopLabel) gShopLabel.textContent = (gShop === 'consol') ? 'CONSOL' : 'IPRINT';
    var dn = gVal('garment_dateNeeded');
    document.getElementById('gps_dateNeeded').textContent = (typeof formatDateDDMMYYYY === 'function') ? formatDateDDMMYYYY(dn) : dn;

    // Ensure summary is fresh
    if (typeof garment_updatePrintSummary === 'function') garment_updatePrintSummary();
    var qtyTxt = (document.getElementById('garment_totalQtyDisplay') || {}).textContent || '0';
    var amtTxt = (document.getElementById('garment_totalAmountDisplay') || {}).textContent || '\u20B10.00';
    document.getElementById('gps_qty').textContent = qtyTxt + ' PCS';
    document.getElementById('gps_amount').textContent = amtTxt;

    // Products table - follow same priority as Order Summary:
    // if "Sizes with name" (roster) has rows, show per-size; else show Product Details rows.
    var body = document.getElementById('gps_productsBody');
    body.innerHTML = '';
    var rosterArr = (typeof garment_getRosterData === 'function') ? garment_getRosterData() : [];
    if (rosterArr.length > 0 && typeof garment_computeSizePricing === 'function') {
        var specLbl = [gVal('garment_specBrand'), gVal('garment_specType'), gVal('garment_specColor'), gVal('garment_specMaterial')].filter(Boolean).join(' / ');
        if (specLbl) body.innerHTML += '<tr><td colspan="4" style="text-align:left;font-weight:bold;">' + gEsc(specLbl) + ' <span style="font-weight:normal;color:#555;">(Sizes with name)</span></td></tr>';
        var res = garment_computeSizePricing();
        var keys = res.keys || Object.keys(res.perSize || {});
        keys.forEach(function(s) {
            var q = (res.perSize || {})[s] || 0;
            var pRes = (typeof garment_findItemPrice === 'function') ? garment_findItemPrice(s) : { price: null };
            var pr = pRes.price;
            body.innerHTML += '<tr><td>' + gEsc(s) + '</td><td style="text-align:center;">' + q + '</td>'
                + '<td style="text-align:right;">' + (pr != null ? ('\u20B1' + pr.toFixed(2)) : '\u2014') + '</td>'
                + '<td style="text-align:right;">' + (pr != null ? ('\u20B1' + (pr * q).toFixed(2)) : '\u2014') + '</td></tr>';
        });
    } else {
        document.querySelectorAll('#garment_productRowsContainer .product-row').forEach(function(row) {
            var sel = row.querySelector('.product-select');
            var qtyI = row.querySelector('.product-quantity');
            if (!sel || !qtyI) return;
            var opt = sel.options[sel.selectedIndex];
            var q = parseInt(qtyI.value) || 0;
            if (!opt || !opt.value || q === 0) return;
            var price = opt.dataset.price ? parseFloat(opt.dataset.price) : 0;
            var name = opt.dataset.productName || '';
            var desc = [opt.dataset.brand, opt.dataset.color, opt.dataset.size].filter(Boolean).join(' / ');
            body.innerHTML += '<tr><td>' + gEsc(name) + (desc ? ' <span style="color:#555;">(' + gEsc(desc) + ')</span>' : '') + '</td>'
                + '<td style="text-align:center;">' + q + '</td>'
                + '<td style="text-align:right;">\u20B1' + price.toFixed(2) + '</td>'
                + '<td style="text-align:right;">\u20B1' + (q * price).toFixed(2) + '</td></tr>';
        });
    }
    if (!body.innerHTML) body.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#777;">No items</td></tr>';

    // Printing options
    var ptSel = document.getElementById('garment_printTypeSelect');
    var ptText = ptSel && ptSel.selectedIndex > 0 ? ptSel.options[ptSel.selectedIndex].textContent : '';
    var sizeNames = [];
    document.querySelectorAll('#garment_printSizesList input[type=checkbox]:checked').forEach(function(cb) {
        var lbl = cb.parentElement;
        var sp = lbl ? lbl.querySelector('span') : null;
        if (sp) sizeNames.push(sp.textContent.trim());
    });
    var poDiv = document.getElementById('gps_printOptions');
    if (ptText || sizeNames.length) {
        poDiv.innerHTML = (ptText ? '<div><b>Print Type:</b> ' + gEsc(ptText) + '</div>' : '')
            + (sizeNames.length ? '<div><b>Print Sizes:</b> ' + gEsc(sizeNames.join(', ')) + '</div>' : '');
    } else {
        poDiv.innerHTML = '<div style="color:#777;">\u2014</div>';
    }

    // Mockup
    var mp = document.getElementById('garment_mockupPreview');
    var mImg = document.getElementById('gps_mockupImg');
    var mPh = document.getElementById('gps_mockupPlaceholder');
    if (mp && mp.src && mp.style.display !== 'none') { mImg.src = mp.src; mImg.style.display = ''; mPh.style.display = 'none'; }
    else { mImg.style.display = 'none'; mPh.style.display = ''; }

    // Name list (roster) - dynamic columns
    var rBody = document.getElementById('gps_rosterBody');
    var rTable = document.getElementById('gps_rosterTable');
    rBody.innerHTML = '';
    var rRows = document.querySelectorAll('#garment_rosterBody tr.roster-row');
    if (rRows.length) {
        var hdr = document.getElementById('garment_rosterHeader');
        var cols = ['#'];
        if (hdr) { var ths = hdr.querySelectorAll('th'); for (var ci = 1; ci < ths.length - 1; ci++) cols.push((ths[ci].textContent || '').trim()); }
        else { cols = ['#', 'NAME', 'SIZE', 'QTY']; }
        var thead = rTable.querySelector('thead'); if (!thead) { thead = document.createElement('thead'); rTable.insertBefore(thead, rBody); }
        var hh = '<tr>'; cols.forEach(function(h) { hh += '<th>' + gEsc(h) + '</th>'; }); hh += '</tr>';
        thead.innerHTML = hh;
        var idx = 1;
        rRows.forEach(function(row) {
            var tds = row.querySelectorAll('td'); if (tds.length < 2) return;
            var cells = []; for (var i = 1; i < tds.length - 1; i++) cells.push(tds[i]);
            if (!cells.some(function(td) { return (td.textContent || '').trim() !== ''; })) return;
            var rh = '<tr><td>' + (idx++) + '</td>';
            cells.forEach(function(td) { rh += '<td style="text-align:left;">' + gEsc((td.textContent || '').trim()) + '</td>'; });
            rh += '</tr>'; rBody.innerHTML += rh;
        });
    } else {
        var thead2 = rTable.querySelector('thead'); if (thead2) thead2.innerHTML = '';
        rBody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#777;">No names</td></tr>';
    }

    // Note
    var notes = gVal('garment_productNotes');
    document.getElementById('gps_note').textContent = notes ? 'Note: ' + notes : '';
    if (typeof window.garment_refreshCartGate === 'function') window.garment_refreshCartGate();

    // Print
    var slip = document.getElementById('garment_printSlip');
    document.body.classList.add('gps-printing');
    slip.style.display = 'block';
    window.print();
    slip.style.display = 'none';
    document.body.classList.remove('gps-printing');

    // Mark as viewed (gate for Add to Cart)
    window.garment_orderSlipViewed = true;
    var btn = document.getElementById('garment_printOrderSlip');
    if (btn) {
        btn.classList.remove('btn-outline-info');
        btn.classList.add('btn-success');
        btn.innerHTML = '<i class="fas fa-check me-2"></i> Order Slip Printed';
    }
};
