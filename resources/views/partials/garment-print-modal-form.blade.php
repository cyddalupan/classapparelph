{{-- SHARED: exact garment printing modal form from Create Sale (single source of truth) --}}
@php $printOnlyEnabled = $printOnlyEnabled ?? false; @endphp

                            <ul class="nav nav-tabs mb-3" id="garmentTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="garment-products-tab" data-bs-toggle="tab" data-bs-target="#garment-products-pane" type="button" role="tab"><i class="fas fa-tshirt me-1"></i>Product Details</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="garment-sizes-tab" data-bs-toggle="tab" data-bs-target="#garment-sizes-pane" type="button" role="tab"><i class="fas fa-users me-1"></i>Sizes with name</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="garment-summary-tab" data-bs-toggle="tab" data-bs-target="#garment-summary-pane" type="button" role="tab"><i class="fas fa-shopping-cart me-1"></i>Order Summary</button>
                                </li>
                            </ul>
                            <div class="tab-content" id="garmentTabContent">
                            <!-- TAB 1: PRODUCTS -->
                            <div class="tab-pane fade show active" id="garment-products-pane" role="tabpanel">
                            <div class="row">
                                <div class="col-md-7">
                                    @if($printOnlyEnabled)
                                    <!-- ORDER TYPE — garment+print (shop sells garment) vs print only (customer's own garment) -->
                                    <div class="mb-3">
                                        <label class="form-label">Order Type *</label>
                                        <div class="btn-group w-100" role="group" aria-label="Order type">
                                            <input type="radio" class="btn-check" name="garment_orderType" id="garment_orderTypeGarment" value="garment" checked onchange="garment_setOrderType('garment')">
                                            <label class="btn btn-outline-primary" for="garment_orderTypeGarment"><i class="fas fa-tshirt me-1"></i> Garment + Print</label>
                                            <input type="radio" class="btn-check" name="garment_orderType" id="garment_orderTypePrint" value="print_only" onchange="garment_setOrderType('print_only')">
                                            <label class="btn btn-outline-primary" for="garment_orderTypePrint"><i class="fas fa-print me-1"></i> Print only</label>
                                        </div>
                                        <div class="form-text small">Print only = customer supplies the garment; print lang ang icha-charge.</div>
                                    </div>
                                    @endif
                                    <!-- PROJECT NAME + SHOP -->
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-7">
                                            <label class="form-label">Project Name *</label>
                                            <input type="text" class="form-control" id="garment_projectName" placeholder="e.g. DANNA" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Shop / Department *</label>
                                            <select class="form-select" id="garment_department">
                                                <option value="">— Select Shop —</option>
                                                <option value="iprint">iPrint</option>
                                                <option value="consol">Consol</option>
                                            </select>
                                            <div class="form-text small">Dito mapupunta ang order at ang manager nito.</div>
                                        </div>
                                    </div>
                                    <!-- MULTIPLE PRODUCT ROWS SECTION -->
                                    @if($printOnlyEnabled)<div id="garment_garmentOnlySections">@endif
                                    <!-- GARMENT FILTER SECTION -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-light py-2">
                                            <h6 class="mb-0"><i class="fas fa-filter me-1"></i> Filter Products</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row g-2">
                                                <div class="col-6 col-md-3">
                                                    <label class="form-label small mb-1">Brand</label>
                                                    <input type="text" class="form-control form-control-sm" id="garment_filterBrand" list="garment_brandOptions" placeholder="Type or select brand...">
                                                    <datalist id="garment_brandOptions"></datalist>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="form-label small mb-1">Type</label>
                                                    <input type="text" class="form-control form-control-sm" id="garment_filterType" list="garment_typeOptions" placeholder="Type or select type...">
                                                    <datalist id="garment_typeOptions"></datalist>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="form-label small mb-1">Color</label>
                                                    <input type="text" class="form-control form-control-sm" id="garment_filterColor" list="garment_colorOptions" placeholder="Type or select color...">
                                                    <datalist id="garment_colorOptions"></datalist>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="form-label small mb-1">Size</label>
                                                    <input type="text" class="form-control form-control-sm" id="garment_filterSize" list="garment_sizeOptions" placeholder="Type or select size...">
                                                    <datalist id="garment_sizeOptions"></datalist>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="form-label small mb-1">Material</label>
                                                    <input type="text" class="form-control form-control-sm" id="garment_filterMaterial" list="garment_materialOptions" placeholder="Type or select material...">
                                                    <datalist id="garment_materialOptions"></datalist>
                                                </div>
                                            </div>
                                            <div class="mt-2 text-end">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="garment_applyFilters()">
                                                    <i class="fas fa-filter me-1"></i> Apply Filters
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="garment_resetFilters()">
                                                    <i class="fas fa-redo me-1"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Products *</label>
                                        <div id="garment_productRowsContainer">
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
                                            <button type="button" class="btn btn-sm btn-outline-success" id="garment_addProductRowBtn">
                                                <i class="fas fa-plus-circle me-1"></i> Add Another Product
                                            </button>
                                        </div>
                                    </div>
                                    @if($printOnlyEnabled)</div>{{-- /garment_garmentOnlySections --}}@endif
                                    
                                    <!-- Department auto-assigned from product box selection (no manual pick) -->
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Date Needed <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="garment_dateNeeded" required>
                                        <small class="text-muted">Target completion / delivery date</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Notes (Optional)</label>
                                        <textarea class="form-control" id="garment_productNotes" rows="3" placeholder="Add special instructions..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white small py-2">
                                            <i class="fas fa-image me-1"></i> Mock-up Design
                                        </div>
                                        <div class="card-body py-2 text-center">
                                            <div id="garment_mockupPreviewArea" class="mb-2" style="min-height:140px;display:flex;align-items:center;justify-content:center;border:2px dashed #ddd;border-radius:8px;cursor:pointer;background:#fafafa;overflow:hidden;" onclick="document.getElementById('garment_mockupUpload').click()">
                                                <div id="garment_mockupPlaceholder">
                                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted"></i>
                                                    <p class="text-muted small mb-0 mt-1">Click to upload mock-up image</p>
                                                </div>
                                                <img id="garment_mockupPreview" src="" style="max-height:180px;display:none;max-width:100%;object-fit:contain;">
                                            </div>
                                            <input type="file" id="garment_mockupUpload" accept="image/*" style="display:none" onchange="garment_previewMockup(event)">
                                            <div id="garment_mockupFileInfo" class="small" style="display:none;">
                                                <span id="garment_mockupFileName" class="text-muted"></span>
                                                <button type="button" class="btn btn-sm btn-outline-danger ms-2 py-0 px-1" onclick="garment_removeMockup()" title="Remove"><i class="fas fa-times"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card mt-3">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="fas fa-print me-2"></i>Printing Options</h6>
                                            <div class="mb-3">
                                                <label class="form-label">Print Type *</label>
                                                <select class="form-control" id="garment_printTypeSelect" onchange="garment_loadPrintSizes(this.value)">
                                                    <option value="">-- Select Print Type --</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Print Sizes</label>
                                                <div id="garment_printSizesContainer" class="small" style="display:none;">
                                                    <div id="garment_printSizesList"></div>
                                                </div>
                                            </div>
                                            @if($printOnlyEnabled)
                                            <div class="mb-2" id="garment_printOnlyQtyWrap" style="display:none;">
                                                <label class="form-label">Print Quantity *</label>
                                                <input type="number" class="form-control" id="garment_printOnlyQty" min="1" step="1" placeholder="e.g. 50" oninput="garment_updatePrintSummary()">
                                                <div class="form-text small">Ilang piraso ang ipi-print (customer's own garment).</div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                            <!-- TAB: SIZES WITH NAME (roster + Excel upload, mirrors Full Sublimation) -->
                            <div class="tab-pane fade" id="garment-sizes-pane" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-success py-2 small">
                                        <i class="fas fa-users me-1"></i> Piliin ang <strong>Brand / Type-Collar / Color</strong>, tapos mag-upload ng Excel (pangalan + size). Automatic nang bibilangin ang qty at price kada size.
                                    </div>
                                    <!-- Garment Specification (required for pricing) -->
                                    <div class="card mb-3 border-primary">
                                        <div class="card-header bg-primary text-white small py-2">
                                            <i class="fas fa-tags me-1"></i> Garment Specification
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row g-2">
                                                <div class="col-6 col-md-4">
                                                    <label class="form-label small mb-1">Brand *</label>
                                                    <input type="text" class="form-control form-control-sm" id="garment_specBrand" list="garment_specBrandOptions" placeholder="e.g. YALEX">
                                                    <datalist id="garment_specBrandOptions"></datalist>
                                                </div>
                                                <div class="col-6 col-md-4">
                                                    <label class="form-label small mb-1">Type / Collar *</label>
                                                    <input type="text" class="form-control form-control-sm" id="garment_specType" list="garment_specTypeOptions" placeholder="e.g. ROUNDNECK">
                                                    <datalist id="garment_specTypeOptions"></datalist>
                                                </div>
                                                <div class="col-6 col-md-4">
                                                    <label class="form-label small mb-1">Color</label>
                                                    <input type="text" class="form-control form-control-sm" id="garment_specColor" list="garment_specColorOptions" placeholder="e.g. BLACK">
                                                    <datalist id="garment_specColorOptions"></datalist>
                                                </div>
                                                <div class="col-6 col-md-4">
                                                    <label class="form-label small mb-1">Material</label>
                                                    <input type="text" class="form-control form-control-sm" id="garment_specMaterial" list="garment_specMaterialOptions" placeholder="e.g. Cotton">
                                                    <datalist id="garment_specMaterialOptions"></datalist>
                                                </div>
                                            </div>
                                            <small class="text-muted d-block mt-2"><i class="fas fa-info-circle me-1"></i> Kailangang may Brand at Type/Collar bago makapag-upload ng Excel.</small>
                                        </div>
                                    </div>
                                    <!-- Excel Upload -->
                                    <div class="mb-2">
                                        <div class="d-flex gap-2 flex-wrap align-items-center">
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="garment_excelBtn" disabled onclick="document.getElementById('garment_excelInput').click()">
                                                <i class="fas fa-file-excel me-1"></i>Upload Excel
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" id="garment_resetSizesBtn" onclick="garment_resetSizesWithName()" title="Alisin ang na-upload na listahan at i-reset ang Brand/Type/Color/Material">
                                                <i class="fas fa-trash-alt me-1"></i>Clear / Reset
                                            </button>
                                            <span id="garment_excelGateHint" class="text-muted small">Piliin muna ang Brand at Type/Collar.</span>
                                            <span id="garment_uploadedFileBadge" class="badge bg-info text-dark" style="display:none;"></span>
                                            <input type="file" id="garment_excelInput" accept=".xlsx,.xls,.csv" style="display:none" onchange="garment_uploadExcel(event)">
                                        </div>
                                    </div>
                                    <!-- Roster Table -->
                                    <div class="table-responsive" style="max-height:300px;overflow-y:auto;">
                                        <table class="table table-sm table-bordered mb-0" id="garment_rosterTable">
                                            <thead class="table-light">
                                                <tr id="garment_rosterHeader">
                                                    <th style="width:50px">#</th>
                                                    <th>Name</th>
                                                    <th style="width:80px">Size</th>
                                                    <th style="width:40px">Qty</th>
                                                    <th style="width:40px"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="garment_rosterBody">
                                                <tr id="garment_rosterEmpty">
                                                    <td colspan="5" class="text-center text-muted small py-3">
                                                        <i class="fas fa-user-plus me-1"></i> Add names above to build your roster.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-1 text-end">
                                        <small class="text-muted">
                                            <span id="garment_rosterCount">0</span> people
                                        </small>
                                    </div>
                                    <!-- Brand per size (mixed-brand orders: e.g. XS-3XL sa isang brand, 5XL sa iba) -->
                                    <div class="card mt-2 border-info" id="garment_sizeBrandCard" style="display:none;">
                                        <div class="card-header bg-info text-white small py-2">
                                            <i class="fas fa-tags me-1"></i> Brand per size
                                            <span class="fw-normal">(kapag wala ang size sa unang brand)</span>
                                        </div>
                                        <div class="card-body p-2">
                                            <div id="garment_sizeBrandBody" class="small"></div>
                                            <div class="text-muted mt-1" style="font-size:.72rem;">
                                                <i class="fas fa-info-circle me-1"></i>Blangko = gamitin ang Brand sa itaas. Pumili lang ng ibang brand kapag hindi available ang size sa brand na 'yon.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Size pricing breakdown (from roster + spec) -->
                                    <div class="card mt-3 border-success" id="garment_sizePricingCard" style="display:none;">
                                        <div class="card-header bg-success text-white small py-2">
                                            <i class="fas fa-calculator me-1"></i> Size Breakdown (auto)
                                        </div>
                                        <div class="card-body p-2">
                                            <div id="garment_sizePricingBody" class="small"></div>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between fw-bold">
                                                <span>Total: <span id="garment_sizePricingQty">0</span> pcs</span>
                                                <span id="garment_sizePricingTotal">₱0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                            <!-- TAB 3: ORDER SUMMARY -->
                            <div class="tab-pane fade" id="garment-summary-pane" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="fas fa-shopping-cart me-2"></i>Order Summary</h6>
                                            
                                            <!-- Products Breakdown -->
                                            <div id="garment_productsBreakdown" class="mb-3">
                                                <div class="text-muted small mb-2">No products selected yet</div>
                                            </div>
                                            
                                            <hr>
                                            
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Total Quantity:</span>
                                                <span id="garment_totalQtyDisplay">0</span>
                                            </div>
                                            <div class="d-flex justify-content-between fw-bold mb-2">
                                                <span>Total Amount:</span>
                                                <span id="garment_totalAmountDisplay">₱0.00</span>
                                            </div>
                                            
                                            <!-- Print Summary Sidebar -->
                                            <div id="garment_printSummarySidebar" style="display:none;">
                                                <hr>
                                                <div id="garment_printSizesBreakdown" class="small mb-2"></div>
                                                <div class="d-flex justify-content-between small">
                                                    <span>Print Cost/ea:</span>
                                                    <span id="garment_printCostPerItemDisplay">₱0.00</span>
                                                </div>
                                                <div class="d-flex justify-content-between small">
                                                    <span>Qty:</span>
                                                    <span id="garment_printQtyDisplay">0</span>
                                                </div>
                                                <div class="d-flex justify-content-between small">
                                                    <span>Print Subtotal:</span>
                                                    <span id="garment_printSubtotalDisplay">₱0.00</span>
                                                </div>
                                                <div class="d-flex justify-content-between small text-danger" id="garment_comboDiscountRow" style="display:none;">
                                                    <span>Combo Discount:</span>
                                                    <span id="garment_comboDiscountDisplay">-₱0.00</span>
                                                </div>
                                                <div class="d-flex justify-content-between small text-danger" id="garment_bulkDiscountRow" style="display:none;">
                                                    <span>Bulk Discount:</span>
                                                    <span id="garment_bulkDiscountDisplay">-₱0.00</span>
                                                </div>
                                                <hr class="my-1">
                                                <div class="d-flex justify-content-between fw-bold small">
                                                    <span>Print Total:</span>
                                                    <span id="garment_printTotalDisplay">₱0.00</span>
                                                </div>
                                                <div class="text-muted text-end" style="font-size:11px;" id="garment_printTotalFormula"></div>
                                                <hr>
                                                <div class="d-flex justify-content-between fw-bold">
                                                    <span>Grand Total:</span>
                                                    <span id="garment_grandTotalDisplay">₱0.00</span>
                                                </div>
                                                <div class="text-muted text-end" style="font-size:11px;" id="garment_grandTotalFormula"></div>
                                            </div>
                                            
                                            <!-- Special Price Section -->
                                            <div class="mt-3 p-2 bg-light rounded" id="garment_specialPriceSection" style="display:none;">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="fw-bold small">💵 SPECIAL PRICE ACTIVE</span>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="garment_clearSpecialPrice()">✕ Clear</button>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="small text-muted">Set Print Total:</label>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control" id="garment_specialPrintTotal" min="0" step="0.01" placeholder="0.00" oninput="garment_onSpecialPriceChange()">
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="small text-muted">Reason *</label>
                                                    <input type="text" class="form-control form-control-sm" id="garment_specialPriceReason" placeholder="Why special price?" required>
                                                </div>
                                            </div>
                                            
                                            <button type="button" class="btn btn-outline-warning btn-sm w-100 mt-2" id="garment_specialPriceBtn" onclick="garment_toggleSpecialPrice()">
                                                💰 Special Price
                                            </button>
                                            
                                            <button type="button" class="btn btn-primary w-100 mt-2" id="garment_addItemBtn">
                                                <i class="fas fa-cart-plus me-2"></i> Add to Cart
                                            </button>
                                            <div id="garment_cartGateHint" class="small text-danger mt-1" style="display:none;"></div>
                                            <button type="button" class="btn btn-outline-info w-100 mt-2" id="garment_printOrderSlip" onclick="window.garment_printOrderSlip()">
                                                <i class="fas fa-print me-2"></i> Print Order Slip
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                            </div>
