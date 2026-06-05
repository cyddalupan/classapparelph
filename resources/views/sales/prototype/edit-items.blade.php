@extends('layouts.app')

@section('title', 'Edit Items — Order #' . $sale->sales_number)

@push('styles')
<style>
    .edit-container { max-width: 1200px; margin: 0 auto; }
    .current-items { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .change-summary { background: #fff3cd; border: 1px solid #ffc107; border-radius: 12px; padding: 16px; margin-bottom: 20px; }
    .item-card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 16px; margin-bottom: 12px; position: relative; }
    .item-card.removed { opacity: 0.5; background: #f8d7da; text-decoration: line-through; }
    .item-card.added { background: #d4edda; border-color: #28a745; }
    .item-card.modified { background: #fff3cd; border-color: #ffc107; }
    .item-card .remove-btn, .item-card .restore-btn { position: absolute; top: 8px; right: 8px; }
    .product-search { margin-bottom: 20px; }
    .product-search input { border-radius: 8px; }
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-bottom: 20px; }
    .product-option { border: 1px solid #dee2e6; border-radius: 8px; padding: 10px; cursor: pointer; text-align: center; transition: all 0.2s; }
    .product-option:hover { border-color: #667eea; background: #f0f0ff; }
    .product-option.selected { border-color: #28a745; background: #d4edda; }
    .new-item-form { background: #f8f9fa; border-radius: 12px; padding: 20px; margin-bottom: 20px; display: none; }
    .size-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    @media (max-width: 768px) { .size-row { flex-direction: column; align-items: stretch; } }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="edit-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Edit Items — #{{ $sale->sales_number }}</h4>
                <p class="text-muted mb-0">{{ $sale->customer_name }} | {{ $sale->department_name ?? 'Class' }}</p>
            </div>
            <div>
                <a href="{{ route('sales.prototype.show', $sale->id) }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <form id="editItemsForm" method="POST">
            @csrf
            <input type="hidden" name="department" value="{{ $sale->department_name ?? 'class' }}">

            <!-- Change Summary Box -->
            <div class="change-summary" id="changeSummaryBox" style="display: none;">
                <h6><i class="fas fa-info-circle me-2"></i>Summary of Changes</h6>
                <div id="changeSummaryText" class="mb-2"></div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-danger" id="removedCount">0 removed</span>
                    <span class="badge bg-success" id="addedCount">0 added</span>
                    <span class="badge bg-warning text-dark" id="modifiedCount">0 modified</span>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Original Total</small>
                        <div class="fw-bold" id="originalTotal">₱{{ number_format($sale->total_amount, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">New Total</small>
                        <div class="fw-bold" id="newTotal">₱{{ number_format($sale->total_amount, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Difference</small>
                        <div class="fw-bold" id="totalDiff">₱0.00</div>
                    </div>
                </div>
            </div>

            <!-- Current Items -->
            <div class="current-items mb-4">
                <h5 class="mb-3"><i class="fas fa-box me-2"></i>Current Products</h5>
                <p class="text-muted small mb-3">Check items to remove, or modify quantities/prices. Changes are submitted for Manager approval.</p>

                <div id="itemsContainer">
                    @forelse($services as $index => $item)
                        @php
                            $itemId = $item['id'] ?? ('new_' . $index);
                            $qty = $item['quantity'] ?? ($item['qty'] ?? 1);
                            $price = $item['unitPrice'] ?? ($item['price'] ?? 0);
                            $itemName = $item['name'] ?? '';
                            $total = $item['totalPrice'] ?? ($qty * $price);
                            $sizes = $item['sublimationForm']['sizes'] ?? [];
                            $parts = $item['sublimationForm']['parts'] ?? [];
                            $notes = $item['notes'] ?? '';
                        @endphp
                        <div class="item-card" data-item-id="{{ $itemId }}" data-original-qty="{{ $qty }}" data-original-price="{{ $price }}" data-original-total="{{ $total }}">
                            <div class="row align-items-start">
                                <div class="col-auto">
                                    <div class="form-check">
                                        <input class="form-check-input item-toggle" type="checkbox" value="{{ $itemId }}" id="remove_{{ $index }}" checked>
                                        <label class="form-check-label" for="remove_{{ $index }}">
                                            <small class="text-muted">Include</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="fw-bold">{{ $itemName }}</div>
                                    @if(!empty($sizes))
                                        <div class="text-muted small">
                                            Sizes:
                                            @foreach($sizes as $s)
                                                {{ $s['size'] }} ({{ $s['quantity'] }}x @ ₱{{ number_format($s['price'], 2) }})
                                                @if(!$loop->last) | @endif
                                            @endforeach
                                        </div>
                                    @endif
                                    @if(!empty($parts))
                                        <div class="text-muted small">
                                            Parts: {{ implode(', ', array_map(fn($p) => $p['name'] . ' (+₱' . number_format($p['price'], 2) . ')', $parts)) }}
                                        </div>
                                    @endif
                                    @if($notes)
                                        <div class="text-muted small"><em>{{ $notes }}</em></div>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <div class="size-row mb-1">
                                        <div>
                                            <small class="text-muted">Qty</small>
                                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm item-qty" value="{{ $qty }}" min="0" style="width: 70px;" data-index="{{ $index }}">
                                        </div>
                                        <div>
                                            <small class="text-muted">Price</small>
                                            <input type="number" name="items[{{ $index }}][unitPrice]" class="form-control form-control-sm item-price" value="{{ $price }}" step="0.01" min="0" style="width: 100px;" data-index="{{ $index }}">
                                        </div>
                                        <div>
                                            <small class="text-muted">Total</small>
                                            <div class="item-total fw-bold">₱{{ number_format($total, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="items[{{ $index }}][name]" value="{{ $itemName }}">
                            <input type="hidden" name="items[{{ $index }}][department]" value="{{ $item['department'] ?? $sale->department_name ?? 'class' }}">
                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $itemId }}">
                            <input type="hidden" name="items[{{ $index }}][productType]" value="{{ $item['productType'] ?? 'cutting' }}">
                            <input type="hidden" name="items[{{ $index }}][notes]" value="{{ $notes }}">
                            @if(!empty($sizes))
                                <input type="hidden" name="items[{{ $index }}][sizes]" value="{{ json_encode($sizes) }}">
                            @endif
                            @if(!empty($parts))
                                <input type="hidden" name="items[{{ $index }}][parts]" value="{{ json_encode($parts) }}">
                            @endif
                            @if(isset($item['sublimationForm']))
                                <input type="hidden" name="items[{{ $index }}][sublimationForm]" value="{{ json_encode($item['sublimationForm']) }}">
                            @endif
                        </div>
                    @empty
                        <div class="alert alert-info">No items found.</div>
                    @endforelse
                </div>
            </div>

            <!-- Add New Product -->
            <div class="current-items mb-4">
                <h5 class="mb-3"><i class="fas fa-plus-circle text-success me-2"></i>Add New Product</h5>
                
                @if(isset($products) && $products->count() > 0)
                <div class="product-search">
                    <input type="text" class="form-control" id="productSearch" placeholder="Search products..." onkeyup="filterProducts()">
                </div>
                <div class="product-grid" id="productGrid">
                    @foreach($products as $product)
                    <div class="product-option" onclick="selectProduct('{{ addslashes($product->name) }}')" data-name="{{ $product->name }}">
                        <div class="fw-bold small">{{ $product->name }}</div>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Manual Add Form -->
                <div class="new-item-form" id="newItemForm">
                    <h6 class="mb-3">New Item Details</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="newItemName" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="newItemQty" value="1" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unit Price (₱)</label>
                            <input type="number" class="form-control" id="newItemPrice" value="0" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-success" onclick="addNewItem()">
                            <i class="fas fa-plus"></i> Add to Changes
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="cancelNewItem()">Cancel</button>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-success" id="showAddForm" onclick="showAddForm()">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                        <i class="fas fa-paper-plane"></i> Submit for Approval
                    </button>
                    <small class="text-muted ms-2" id="submitHint">Make some changes first</small>
                </div>
                <a href="{{ route('sales.prototype.show', $sale->id) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let newItems = [];
let removedItems = [];
let modifiedItems = [];

function calculateTotals() {
    let originalTotal = {{ $sale->total_amount }};
    let newTotal = 0;
    let removed = 0;
    let added = 0;
    let modified = 0;
    let diffTotal = 0;

    // Calculate from current items
    document.querySelectorAll('.item-card').forEach(card => {
        const isChecked = card.querySelector('.item-toggle').checked;
        const qtyInput = card.querySelector('.item-qty');
        const priceInput = card.querySelector('.item-price');
        const originalQty = parseFloat(card.dataset.originalQty);
        const originalPrice = parseFloat(card.dataset.originalPrice);
        const originalTotal = parseFloat(card.dataset.originalTotal);
        const qty = parseInt(qtyInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const total = qty * price;

        if (!isChecked) {
            removed++;
            card.querySelector('.item-total').textContent = '₱0.00 (removed)';
        } else {
            if (total !== originalTotal) {
                modified++;
                card.querySelector('.item-total').textContent = '₱' + total.toFixed(2) + ' ✏️';
            } else {
                card.querySelector('.item-total').textContent = '₱' + total.toFixed(2);
            }
            newTotal += total;
        }
    });

    // Add new items
    newItems.forEach(item => {
        newTotal += item.totalPrice;
        added++;
    });

    diffTotal = newTotal - originalTotal;

    document.getElementById('removedCount').textContent = removed + ' removed';
    document.getElementById('addedCount').textContent = added + ' added';
    document.getElementById('modifiedCount').textContent = modified + ' modified';
    document.getElementById('originalTotal').textContent = '₱' + originalTotal.toFixed(2);
    document.getElementById('newTotal').textContent = '₱' + newTotal.toFixed(2);

    const diffEl = document.getElementById('totalDiff');
    if (diffTotal > 0) {
        diffEl.textContent = '+₱' + diffTotal.toFixed(2);
        diffEl.className = 'fw-bold text-success';
    } else if (diffTotal < 0) {
        diffEl.textContent = '-₱' + Math.abs(diffTotal).toFixed(2);
        diffEl.className = 'fw-bold text-danger';
    } else {
        diffEl.textContent = '₱0.00';
        diffEl.className = 'fw-bold';
    }

    // Show/hide summary box
    const hasChanges = removed > 0 || added > 0 || modified > 0;
    document.getElementById('changeSummaryBox').style.display = hasChanges ? 'block' : 'none';
    document.getElementById('submitBtn').disabled = !hasChanges;
    document.getElementById('submitHint').textContent = hasChanges ? '' : 'Make some changes first';

    // Update change summary text
    const parts = [];
    if (removed > 0) parts.push(removed + ' item(s) removed');
    if (added > 0) parts.push(added + ' item(s) added');
    if (modified > 0) parts.push(modified + ' item(s) modified');
    if (diffTotal !== 0) parts.push('Total ' + (diffTotal > 0 ? 'increased' : 'decreased') + ' by ₱' + Math.abs(diffTotal).toFixed(2));
    document.getElementById('changeSummaryText').textContent = parts.join(', ');
}

function filterProducts() {
    const q = document.getElementById('productSearch').value.toLowerCase();
    document.querySelectorAll('.product-option').forEach(el => {
        el.style.display = el.dataset.name.toLowerCase().includes(q) ? '' : 'none';
    });
}

let selectedProductName = '';

function selectProduct(name) {
    selectedProductName = name;
    document.getElementById('newItemName').value = name;
    document.getElementById('newItemForm').style.display = 'block';
    document.getElementById('showAddForm').style.display = 'none';
    document.getElementById('productSearch').value = '';
    filterProducts();

    // Highlight selected
    document.querySelectorAll('.product-option').forEach(el => {
        el.classList.toggle('selected', el.dataset.name === name);
    });
}

function showAddForm() {
    document.getElementById('newItemForm').style.display = 'block';
    document.getElementById('showAddForm').style.display = 'none';
    document.getElementById('newItemName').value = '';
    document.getElementById('newItemName').removeAttribute('readonly');
    document.getElementById('newItemQty').value = '1';
    document.getElementById('newItemPrice').value = '0';
}

function cancelNewItem() {
    document.getElementById('newItemForm').style.display = 'none';
    document.getElementById('showAddForm').style.display = '';
    selectedProductName = '';
    document.querySelectorAll('.product-option').forEach(el => el.classList.remove('selected'));
}

function addNewItem() {
    const name = document.getElementById('newItemName').value.trim();
    const qty = parseInt(document.getElementById('newItemQty').value) || 1;
    const price = parseFloat(document.getElementById('newItemPrice').value) || 0;

    if (!name) {
        alert('Please enter or select a product name.');
        return;
    }
    if (qty < 1) {
        alert('Quantity must be at least 1.');
        return;
    }

    const totalPrice = qty * price;
    const newIndex = newItems.length;

    newItems.push({ name, quantity: qty, unitPrice: price, totalPrice, department: 'class' });

    // Add visual card
    const card = document.createElement('div');
    card.className = 'item-card added mb-2';
    card.innerHTML = `
        <div class="row align-items-center">
            <div class="col">
                <span class="badge bg-success mb-1">NEW</span>
                <div class="fw-bold">${name}</div>
                <small class="text-muted">Qty: ${qty} × ₱${price.toFixed(2)}</small>
            </div>
            <div class="col-auto">
                <strong>₱${totalPrice.toFixed(2)}</strong>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.item-card').remove(); newItems.splice(${newIndex}, 1); calculateTotals();">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <input type="hidden" name="items[${document.querySelectorAll('.item-card').length + newItems.length - 1}][name]" value="${name}">
        <input type="hidden" name="items[${document.querySelectorAll('.item-card').length + newItems.length - 1}][quantity]" value="${qty}">
        <input type="hidden" name="items[${document.querySelectorAll('.item-card').length + newItems.length - 1}][unitPrice]" value="${price}">
        <input type="hidden" name="items[${document.querySelectorAll('.item-card').length + newItems.length - 1}][department]" value="class">
        <input type="hidden" name="items[${document.querySelectorAll('.item-card').length + newItems.length - 1}][id]" value="new_${Date.now()}">
        <input type="hidden" name="items[${document.querySelectorAll('.item-card').length + newItems.length - 1}][productType]" value="cutting">
    `;

    document.getElementById('itemsContainer').appendChild(card);
    cancelNewItem();
    calculateTotals();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Item toggle checkbox
    document.querySelectorAll('.item-toggle').forEach(cb => {
        cb.addEventListener('change', function() {
            const card = this.closest('.item-card');
            if (!this.checked) {
                card.classList.add('removed');
            } else {
                card.classList.remove('removed');
            }
            calculateTotals();
        });
    });

    // Qty and price changes
    document.querySelectorAll('.item-qty, .item-price').forEach(input => {
        input.addEventListener('input', calculateTotals);
    });

    // Form submit
    document.getElementById('editItemsForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Collect only checked items
        const formData = new FormData(this);
        const checkedIds = new Set();
        document.querySelectorAll('.item-toggle:checked').forEach(cb => {
            checkedIds.add(cb.value);
        });

        // Filter out unchecked items and newly added ones
        // The hidden inputs handle submission via standard form POST
        // But newly added items don't have toggles — they're always included

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

        fetch('{{ route("sales.prototype.submit-change", $sale->id) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("sales.prototype.show", $sale->id) }}?change_submitted=1';
            } else {
                alert(data.message || 'Failed to submit.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit for Approval';
            }
        })
        .catch(err => {
            alert('Error submitting change request.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit for Approval';
        });
    });

    calculateTotals();
});
</script>
@endpush
