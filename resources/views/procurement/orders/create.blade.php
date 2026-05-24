@extends('layouts.app')

@section('content')
<style>
.category-group { border: 1px solid #dee2e6; border-radius: 6px; overflow: hidden; margin-bottom: 8px; }
.cat-header { background: #212529; color: #fff; padding: 5px 12px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; cursor: pointer; user-select: none; }
.cat-header:hover { background: #343a40; }
.cat-header .toggle-icon { transition: transform 0.2s; display: inline-block; }
.cat-header.collapsed .toggle-icon { transform: rotate(-90deg); }
.product-group { border-bottom: 1px solid #e9ecef; }
.product-group:last-child { border-bottom: none; }
.pg-header { background: #f8f9fa; padding: 4px 10px; font-size: 0.75rem; font-weight: 600; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; gap: 6px; }
.pg-header .brand-badge { background: #0d6efd; color: #fff; padding: 0 5px; border-radius: 3px; font-size: 0.6rem; font-weight: 700; }
.size-table { width: 100%; border-collapse: collapse; font-size: 0.7rem; table-layout: fixed; border: 1px solid #dee2e6; border-radius: 4px; }
.size-table th { background: #f8f9fa; padding: 4px 10px; text-align: center; font-weight: 600; color: #495057; border-bottom: 1px solid #dee2e6; font-size: 0.65rem; white-space: nowrap; }
.size-table td { padding: 4px 10px; text-align: center; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
.size-table tr:hover td { background: #f0f7ff; }
.size-table tr:last-child td { border-bottom: none; }
.size-label { font-weight: 600; color: #212529; }
.qty-input { width: 50px; text-align: center; border: 1px solid #ced4da; border-radius: 3px; padding: 1px 3px; font-size: 0.7rem; font-weight: 600; }
.qty-input:focus { border-color: #0d6efd; outline: none; box-shadow: 0 0 0 2px rgba(13,110,253,0.15); }
.stock-num { font-weight: 700; }
.stock-num.zero { color: #dc3545; }
.stock-num.low { color: #ffc107; }
.stock-num.ok { color: #198754; }
.rec-num { color: #0d6efd; font-weight: 700; }
.order-item-row { font-size: 0.7rem; padding: 3px 8px; border: 1px solid #e9ecef; border-radius: 4px; margin-bottom: 3px; background: #fafafa; display: flex; align-items: center; gap: 4px; }
.order-item-row:hover { background: #f0f7ff; }
</style>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-0 fw-bold">📦 Create Order</h1>
                <small class="text-muted">Browse by category — click sizes to add to order</small>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>
</div>

<div class="container">
    <!-- Stats -->
    <div class="row g-1 mb-3">
        <div class="col-3"><div class="p-2 bg-danger text-white rounded text-center small fw-bold"><div class="fs-5" id="sCrit">0</div>Critical</div></div>
        <div class="col-3"><div class="p-2 bg-warning text-dark rounded text-center small fw-bold"><div class="fs-5" id="sLow">0</div>Low</div></div>
        <div class="col-3"><div class="p-2 bg-info text-white rounded text-center small fw-bold"><div class="fs-5" id="sRec">0</div>Reorder</div></div>
        <div class="col-3"><div class="p-2 bg-success text-white rounded text-center small fw-bold"><div class="fs-5" id="sOrd">0</div>Order</div></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
                    <span class="fw-bold small"><i class="fas fa-layer-group text-primary me-1"></i>Item Browser</span>
                    <select id="deptFilter" class="form-select form-select-sm" style="width:auto;font-size:0.7rem;" onchange="window.location='{{ request()->url() }}?department_id='+this.value">
                        @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ (string)$departmentId === (string)$d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="card-body p-2">
                    <form id="orderForm" method="POST" action="{{ route('procurement.orders.store') }}">
                        @csrf
                        <input type="hidden" name="department_id" id="deptInput" value="{{ $departmentId ?? '' }}">

                        @foreach($groupedItems as $category => $catItems)
                        @php
                            // Group by (brand + baseName + color)
                            $productGroups = [];
                            foreach ($catItems as $item) {
                                $key = ($item->brand ?: 'General') . '|' . ($item->color ?: '') . '|' . ($item->shirt_type ?: '');
                                if (!isset($productGroups[$key])) {
                                    $productGroups[$key] = [
                                        'brand' => $item->brand ?: '',
                                        'color' => $item->color ?: '',
                                        'type' => $item->shirt_type ?: '',
                                        'material' => $item->material ?: '',
                                        'variants' => []
                                    ];
                                }
                                $productGroups[$key]['variants'][] = $item;
                            }
                        @endphp
                        <div class="category-group">
                            <div class="cat-header d-flex align-items-center justify-content-between" onclick="toggleCat(this)">
                                <span><span class="toggle-icon">▼</span> {{ $category ?: 'Uncategorized' }}</span>
                                <span class="badge bg-light text-dark" style="font-size:0.6rem;">{{ count($catItems) }} items</span>
                            </div>
                            <div>
                                @foreach($productGroups as $pgKey => $pg)
                                @php
                                    // Sort variants by size
                                    $sizeOrder = ['XS','S','M','L','XL','2XL','3XL','4XL','5XL',''];
                                    $variants = collect($pg['variants'])->sortBy(function($v) use ($sizeOrder) {
                                        $idx = array_search(strtoupper($v->size ?? ''), $sizeOrder);
                                        return $idx !== false ? $idx : 999;
                                    });
                                @endphp
                                <div class="product-group">
                                    <div class="pg-header">
                                        <span class="brand-badge">{{ $pg['brand'] ?: 'GEN' }}</span>
                                        <span class="fw-bold">{{ $pg['brand'] ?: '' }} {{ $pg['color'] ? "• {$pg['color']}" : '' }} {{ $pg['type'] ? "• {$pg['type']}" : '' }}</span>
                                        <span class="text-muted" style="font-size:0.6rem;">{{ count($variants) }} sizes</span>
                                    </div>
                                    <div style="overflow-x:auto;">
                                        <table class="size-table">
                                            <thead>
                                                <tr>
                                                    <th style="text-align:left;width:10%;">Size</th>
                                                    <th style="width:12%;">Sold</th>
                                                    <th style="width:14%;">Stock</th>
                                                    <th style="width:14%;">Rec.</th>
                                                    <th style="width:20%;">Order</th>
                                                    <th style="width:12%;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($variants as $item)
                                                @php
                                                    $sold = 0;
                                                    $recQty = 0;
                                                    $priority = '';
                                                    if (isset($recommendationMap[$item->id])) {
                                                        $r = $recommendationMap[$item->id];
                                                        $sold = $r['total_sold'];
                                                        $recQty = $r['recommended_qty'];
                                                        $priority = $r['priority'];
                                                    }
                                                    $stock = (int)$item->current_stock;
                                                @endphp
                                                <tr class="variant-row" data-variant-id="{{ $item->id }}">
                                                    <td class="size-label">{{ $item->size ?: '—' }}</td>
                                                    <td><span class="fw-bold">{{ $sold }}</span></td>
                                                    <td>
                                                        <span class="stock-num {{ $stock <= 0 ? 'zero' : ($stock <= 3 ? 'low' : 'ok') }}">
                                                            {{ $stock }}
                                                        </span>
                                                    </td>
                                                    <td><span class="rec-num">{{ $recQty > 0 ? $recQty : '—' }}</span></td>
                                                    <td>
                                                        <input type="number" class="qty-input order-qty" 
                                                               data-item-id="{{ $item->id }}"
                                                               data-name="{{ $item->name }}"
                                                               data-brand="{{ $item->brand ?: '' }}"
                                                               data-color="{{ $item->color ?: '' }}"
                                                               data-size="{{ $item->size ?: '' }}"
                                                               value="0" min="0" max="999">
                                                    </td>
                                                    <td style="white-space:nowrap;">
                                                        @if($recQty > 0)
                                                        <button type="button" class="btn btn-sm py-0 px-1 btn-outline-primary" style="font-size:0.6rem;" onclick="useRec(this)" title="Use recommended">↻</button>
                                                        @endif
                                                        <button type="button" class="btn btn-sm py-0 px-1 btn-outline-secondary dismiss-btn" style="font-size:0.6rem;" onclick="dismissRow(this)" title="Hide this row">✕</button>
                                                    </td>
                                                </tr>
                                        @endforeach
                                        </tbody>
                                        </table>
                                    </div>
                                <!-- Dismissed items for this product group (inside .product-group so JS can find it) -->
                                <div class="dismissed-section" style="display:none;padding:2px 10px;border-top:1px dashed #dee2e6;">
                                    <div class="d-flex align-items-center justify-content-between py-1">
                                        <small class="text-muted"><i class="fas fa-eye-slash me-1"></i><span class="dismissed-count">0</span> dismissed</small>
                                        <button type="button" class="btn btn-sm py-0 px-2 btn-outline-secondary" style="font-size:0.6rem;" onclick="restoreAll(this)">Restore All</button>
                                    </div>
                                    <div class="dismissed-list"></div>
                                </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <!-- Order items list -->
                        <div class="mt-3 p-2 border rounded" style="background:#f8f9fa;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold small"><i class="fas fa-shopping-cart text-success me-1"></i>Items to Order</span>
                                <span class="badge bg-secondary" id="orderBadge">0</span>
                            </div>
                            <div id="orderItemsList"></div>
                            <div id="emptyOrderMsg" class="text-center py-3 text-muted small">
                                Set quantities above and click "Add All to Order"
                            </div>
                            <div class="d-flex gap-1 mt-2">
                                <button type="button" class="btn btn-sm btn-success" id="addAllBtn" onclick="addAllToOrder()" style="font-size:0.7rem;">
                                    <i class="fas fa-cart-plus me-1"></i> Add All to Order
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="openManualModal()" style="font-size:0.7rem;">
                                    <i class="fas fa-pen me-1"></i> Manual
                                </button>
                                <input type="text" name="notes" class="form-control form-control-sm" placeholder="Notes..." style="font-size:0.7rem;">
                                <button type="submit" class="btn btn-sm btn-primary" id="submitBtn" style="font-size:0.7rem;">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 small fw-bold"><i class="fas fa-receipt text-success me-1"></i>Order Summary</h6>
                </div>
                <div class="card-body p-2" id="summaryPanel">
                    <div class="text-center py-4 text-muted small">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p class="mb-0">Set quantities and add to order</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Manual Item Modal -->
<div class="modal fade" id="manualItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title small fw-bold"><i class="fas fa-pen me-1"></i>Quick Add Item</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size:0.6rem;"></button>
            </div>
            <div class="modal-body p-2">
                <!-- Filters Row -->
                <div class="row g-1 mb-2">
                    <div class="col">
                        <label class="small text-muted mb-1">Category</label>
                        <select id="manualCat" class="form-select form-select-sm" onchange="onCatChange()">
                            <option value="">All Categories</option>
                            @foreach($manualCategories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="manualBrandWrap" class="col" style="display:none;">
                        <label class="small text-muted mb-1">Brand</label>
                        <select id="manualBrand" class="form-select form-select-sm" onchange="filterManualItems()">
                            <option value="">All Brands</option>
                        </select>
                    </div>
                    <div class="col">
                        <label class="small text-muted mb-1">Search</label>
                        <input type="text" id="manualSearch" class="form-control form-control-sm" placeholder="Type item name..." oninput="filterManualItems()">
                    </div>
                </div>

                <!-- Item List with Qty inputs -->
                <div id="manualItemList" class="border rounded p-1 mb-2" style="max-height:280px;overflow-y:auto;">
                    <div class="text-center py-4 text-muted small">
                        <i class="fas fa-search fa-2x mb-1"></i>
                        <p class="mb-0">Select filters above, then type quantity to batch-add items</p>
                    </div>
                </div>

                <!-- Batch add button -->
                <div id="manualQtyBar" class="d-none d-flex justify-content-between align-items-center p-1 bg-light border rounded mb-2">
                    <small class="text-muted" id="manualSelectedCount">0 items selected</small>
                    <button type="button" class="btn btn-sm btn-success" onclick="addBatchManual()">
                        <i class="fas fa-plus me-1"></i>Add Selected
                    </button>
                </div>

            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" style="font-size:0.7rem;">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Full catalog for manual modal quick-pick
const catalogItems = {!! $catalogJson !!};

function openManualModal() {
    // Reset filters
    document.getElementById('manualCat').value = '';
    document.getElementById('manualBrand').value = '';
    document.getElementById('manualBrandWrap').style.display = 'none';
    document.getElementById('manualSearch').value = '';
    document.getElementById('manualItemList').innerHTML = `<div class="text-center py-4 text-muted small">
        <i class="fas fa-search fa-2x mb-1"></i>
        <p class="mb-0">Select filters above, then type quantity to batch-add items</p>
    </div>`;
    document.getElementById('manualQtyBar').classList.add('d-none');
    const modal = new bootstrap.Modal(document.getElementById('manualItemModal'));
    modal.show();
}

function onCatChange() {
    const cat = document.getElementById('manualCat').value;
    const brandWrap = document.getElementById('manualBrandWrap');
    const brandSelect = document.getElementById('manualBrand');
    
    if (!cat) {
        brandWrap.style.display = 'none';
        brandSelect.value = '';
        filterManualItems();
        return;
    }
    
    // Filter brands by selected category
    const brands = catalogItems
        .filter(i => i.category === cat && i.brand)
        .map(i => i.brand)
        .filter((v, idx, arr) => arr.indexOf(v) === idx)
        .sort();
    
    brandSelect.innerHTML = '<option value="">All Brands</option>' +
        brands.map(b => '<option value="' + escHtml(b) + '">' + escHtml(b) + '</option>').join('');
    brandSelect.value = '';
    brandWrap.style.display = '';
    filterManualItems();
}

function filterManualItems() {
    const cat = document.getElementById('manualCat').value;
    const brand = document.getElementById('manualBrand').value;
    const search = document.getElementById('manualSearch').value.toLowerCase();
    
    let filtered = catalogItems;
    if (cat) filtered = filtered.filter(i => i.category === cat);
    if (brand) filtered = filtered.filter(i => i.brand === brand);
    if (search) filtered = filtered.filter(i => i.name.toLowerCase().includes(search) || (i.sku || '').toLowerCase().includes(search));
    
    const list = document.getElementById('manualItemList');
    if (filtered.length === 0) {
        list.innerHTML = `<div class="text-center py-3 text-muted small"><i class="fas fa-box-open fa-2x mb-1"></i><p class="mb-0">No items found</p></div>`;
        document.getElementById('manualQtyBar').classList.add('d-none');
        return;
    }
    
    list.innerHTML = filtered.map(i => `<div class="d-flex justify-content-between align-items-center p-1 border-bottom" style="cursor:default;">
        <div class="small flex-grow-1">
            <strong>${escHtml(i.name)}</strong>
            ${i.brand ? `<span class="text-muted ms-1">${escHtml(i.brand)}</span>` : ''}
            <br><span class="text-muted" style="font-size:0.75rem;">${escHtml(i.sku || '')} ${i.unit_price ? '— ₱' + parseFloat(i.unit_price).toFixed(2) : ''}</span>
        </div>
        <div class="text-end small me-2 text-nowrap">
            <span class="badge ${i.current_stock > 0 ? 'bg-success' : 'bg-secondary'}" style="font-size:0.65rem;">Stock: ${i.current_stock}</span>
        </div>
        <div style="min-width:80px;">
            <input type="number" class="form-control form-control-sm manual-batch-qty" data-mid="${i.id}" min="0" value="0" style="font-size:0.7rem;" onchange="updateBatchCount()" onfocus="this.value==='0' && (this.value='')" onblur="this.value==='' && (this.value='0')">
        </div>
    </div>`).join('');
    document.getElementById('manualQtyBar').classList.remove('d-none');
    updateBatchCount();
}

function updateBatchCount() {
    const inputs = document.querySelectorAll('.manual-batch-qty');
    let count = 0;
    inputs.forEach(i => { if (parseInt(i.value) > 0) count++; });
    document.getElementById('manualSelectedCount').textContent = count + ' item' + (count !== 1 ? 's' : '') + ' selected';
}

function addBatchManual() {
    const inputs = document.querySelectorAll('.manual-batch-qty');
    const selected = [];
    inputs.forEach(i => {
        const qty = parseInt(i.value);
        if (qty > 0) {
            const item = catalogItems.find(c => c.id === parseInt(i.dataset.mid));
            if (item) {
                selected.push({
                    id: null,
                    master_item_id: null,
                    name: item.name,
                    qty: qty,
                    sku: item.sku || '',
                    unitPrice: item.unit_price || 0,
                    notes: ''
                });
            }
        }
    });
    
    if (selected.length === 0) {
        showToast('Set quantities first', 'warning');
        return;
    }
    
    selected.forEach(item => {
        orderItems.push(item);
    });
    renderOrderItems();
    updateHiddenInputs();
    
    showToast(selected.length + ' item' + (selected.length !== 1 ? 's' : '') + ' added to order!', 'success');
    
    // Reset qty inputs
    inputs.forEach(i => { i.value = '0'; });
    updateBatchCount();
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}
</script>

<script>
const items = {!! json_encode($items) !!};
const recMap = {!! json_encode($recommendationMap ?? []) !!};
let orderItems = [];

function toggleCat(el) {
    el.classList.toggle('collapsed');
    const body = el.nextElementSibling;
    body.style.display = body.style.display === 'none' ? 'block' : 'none';
}

// Add manual item to order
function useRec(btn) {
    const tr = btn.closest('tr');
    const input = tr.querySelector('.order-qty');
    const recTd = tr.querySelector('.rec-num');
    const rec = parseInt(recTd.textContent) || 0;
    if (rec > 0) input.value = rec;
    input.dispatchEvent(new Event('input'));
}

// Dismiss row (hide from table)
function dismissRow(btn) {
    const tr = btn.closest('tr');
    const productGroup = btn.closest('.product-group');
    const section = productGroup.querySelector('.dismissed-section');
    const list = section.querySelector('.dismissed-list');
    const count = section.querySelector('.dismissed-count');
    
    // Clone the row info for dismissed section
    const size = tr.querySelector('.size-label')?.textContent || '-';
    const sold = tr.querySelector('.fw-bold')?.textContent || '0';
    const stock = tr.querySelector('.stock-num')?.textContent || '0';
    const itemName = productGroup.querySelector('.pg-header .fw-bold')?.textContent || 'Item';
    
    // Hide the row
    tr.style.display = 'none';
    
    // Add to dismissed list
    const item = document.createElement('div');
    item.className = 'd-flex align-items-center gap-2 py-1 border-bottom';
    item.style.fontSize = '0.7rem';
    item.innerHTML = `
        <span class="text-muted">${size}</span>
        <span class="ms-auto text-muted small">Dismissed</span>
        <button type="button" class="btn btn-sm py-0 px-1 btn-outline-primary" style="font-size:0.6rem;" onclick="restoreRow(this)">Restore</button>
    `;
    list.appendChild(item);
    
    // Update count and show section
    const dismissedCount = list.children.length;
    count.textContent = dismissedCount;
    section.style.display = 'block';
}

// Restore single row
function restoreRow(btn) {
    const itemDiv = btn.closest('.d-flex');
    const productGroup = btn.closest('.product-group');
    const section = productGroup.querySelector('.dismissed-section');
    const list = section.querySelector('.dismissed-list');
    const count = section.querySelector('.dismissed-count');
    
    // Find the hidden row by click order
    const allHidden = productGroup.querySelectorAll('.variant-row[style*="display: none"]');
    const idx = Array.from(list.children).indexOf(itemDiv.closest('.d-flex'));
    if (allHidden[idx]) {
        allHidden[idx].style.display = '';
    }
    
    itemDiv.remove();
    
    // Update count
    const dismissedCount = list.children.length;
    count.textContent = dismissedCount;
    if (dismissedCount === 0) {
        section.style.display = 'none';
    }
}

// Restore all dismissed rows
function restoreAll(btn) {
    const section = btn.closest('.dismissed-section');
    const productGroup = btn.closest('.product-group');
    const list = section.querySelector('.dismissed-list');
    
    // Restore all hidden rows
    const allHidden = productGroup.querySelectorAll('.variant-row[style*="display: none"]');
    allHidden.forEach(tr => { tr.style.display = ''; });
    
    // Clear dismissed list
    list.innerHTML = '';
    section.style.display = 'none';
}

function addAllToOrder() {
    const inputs = document.querySelectorAll('.order-qty');
    let added = 0;
    orderItems = [];
    
    inputs.forEach(inp => {
        const qty = parseInt(inp.value) || 0;
        if (qty <= 0) return;
        
        orderItems.push({
            id: inp.dataset.itemId,
            name: inp.dataset.name,
            brand: inp.dataset.brand,
            color: inp.dataset.color,
            size: inp.dataset.size,
            qty: qty
        });
        added++;
        inp.value = 0;
        
        // Auto-dismiss the row from the browser table
        const row = inp.closest('tr.variant-row');
        const dismissBtn = row?.querySelector('.dismiss-btn');
        if (dismissBtn) dismissRow(dismissBtn);
    });
    
    renderOrderItems();
    if (added > 0) showToast('Added ' + added + ' item(s) to order');
}

function renderOrderItems() {
    const list = document.getElementById('orderItemsList');
    const empty = document.getElementById('emptyOrderMsg');
    const badge = document.getElementById('orderBadge');
    
    if (orderItems.length === 0) {
        list.innerHTML = '';
        empty.style.display = 'block';
        badge.textContent = '0';
        document.getElementById('sOrd').textContent = '0';
        document.getElementById('summaryPanel').innerHTML = '<div class="text-center py-4 text-muted small"><i class="fas fa-inbox fa-2x mb-2"></i><p class="mb-0">No items yet</p></div>';
        return;
    }
    
    empty.style.display = 'none';
    badge.textContent = orderItems.length;
    document.getElementById('sOrd').textContent = orderItems.length;
    
    let html = '';
    let totalQty = 0;
    
    orderItems.forEach((item, idx) => {
        totalQty += item.qty;
        
        // Determine if this is an existing item or manual item
        const hasId = item.id && item.id !== 'null' && item.id !== null;
        
        html += `
            <div class="order-item-row" data-idx="${idx}">
                ${hasId 
                    ? `<input type="hidden" name="items[${idx}][master_item_id]" value="${item.id}">`
                    : `<input type="hidden" name="items[${idx}][item_name]" value="${item.name}">`
                }
                <input type="hidden" name="items[${idx}][quantity]" value="${item.qty}">
                ${item.sku ? `<input type="hidden" name="items[${idx}][sku]" value="${item.sku}">` : ''}
                ${item.unitPrice ? `<input type="hidden" name="items[${idx}][unit_price]" value="${item.unitPrice}">` : ''}
                <span class="fw-bold">${item.name}</span>
                ${item.brand ? `<small class="text-muted">${item.brand}</small>` : ''}
                ${item.color ? `<small class="text-muted">${item.color}</small>` : ''}
                ${item.size ? `<small class="text-muted">(${item.size})</small>` : ''}
                ${item.sku ? `<small class="text-muted">SKU: ${item.sku}</small>` : ''}
                ${item.unitPrice ? `<small class="text-muted">₱${item.unitPrice}</small>` : ''}
                <span class="ms-auto badge bg-primary">x${item.qty}</span>
                <button type="button" class="btn btn-sm btn-outline-danger ms-1" style="font-size:0.7rem;padding:1px 6px;" onclick="removeOrderItem(${idx})" title="Remove this item">✕ Remove</button>
            </div>
        `;
    });
    
    list.innerHTML = html;
    
    // Summary
    document.getElementById('summaryPanel').innerHTML = `
        <div style="max-height:250px;overflow-y:auto;">${html}</div>
        <hr class="my-1">
        <div class="d-flex justify-content-between small fw-bold px-1">
            <span>Items: ${orderItems.length}</span>
            <span>Total Qty: ${totalQty}</span>
        </div>
    `;
}

let undoTimeout = null;
let undoSnapshot = null;

function removeOrderItem(idx) {
    // Save entire state for undo
    undoSnapshot = JSON.parse(JSON.stringify(orderItems));
    orderItems.splice(idx, 1);
    renderOrderItems();
    showUndoToast();
}

function showUndoToast() {
    if (undoTimeout) clearTimeout(undoTimeout);
    const existing = document.getElementById('undo-toast');
    if (existing) existing.remove();
    
    const toast = document.createElement('div');
    toast.id = 'undo-toast';
    toast.className = 'position-fixed bottom-0 start-50 translate-middle-x mb-3 alert alert-warning py-2 px-3 small shadow d-flex align-items-center gap-2';
    toast.style.zIndex = '99999';
    toast.style.maxWidth = '400px';
    toast.innerHTML = 'Item removed. <button type="button" class="btn btn-sm btn-outline-dark py-0 px-2 fw-bold ms-2" onclick="undoRemove()">Undo</button>';
    document.body.appendChild(toast);
    
    undoTimeout = setTimeout(() => {
        const t = document.getElementById('undo-toast');
        if (t) t.remove();
        undoSnapshot = null;
    }, 5000);
}

function undoRemove() {
    const toast = document.getElementById('undo-toast');
    if (toast) toast.remove();
    if (undoTimeout) clearTimeout(undoTimeout);
    if (undoSnapshot) {
        orderItems = undoSnapshot;
        undoSnapshot = null;
        renderOrderItems();
    }
}

// Submit
document.getElementById('orderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (orderItems.length === 0) { alert('Add items first!'); return; }
    
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) { alert('✅ ' + res.message); location.reload(); }
        else { alert('Error: ' + res.message); }
    })
    .catch(err => alert('Network: ' + err.message))
    .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i>'; });
});

// Toast
function showToast(msg) {
    const t = document.createElement('div');
    t.className = 'position-fixed bottom-0 end-0 m-3 alert alert-success py-2 px-3 small shadow';
    t.style.zIndex = 9999;
    t.innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2000);
}

// Update stats from recommendation counts
document.addEventListener('DOMContentLoaded', function() {
    let c=0, l=0, r=0;
    @if(isset($recommendations))
    @foreach($recommendations as $rec)
    @if($rec['priority'] === 'critical') c++; @endif
    @if($rec['priority'] === 'high') l++; @endif
    @if($rec['priority'] === 'medium') r++; @endif
    @endforeach
    @endif
    document.getElementById('sCrit').textContent = c;
    document.getElementById('sLow').textContent = l;
    document.getElementById('sRec').textContent = r;
});
</script>
@endsection
