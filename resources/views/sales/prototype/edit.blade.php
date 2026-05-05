@extends('layouts.app')

@section('title', 'Edit Order #' . $sale->sales_number)

@push('styles')
<style>
    .edit-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .section-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .section-title {
        font-weight: 600;
        font-size: 18px;
        color: #333;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #667eea;
    }
    .item-row {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        border-left: 4px solid #667eea;
        position: relative;
    }
    .item-row .remove-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        background: #fee;
        color: #dc3545;
        border: none;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 12px;
        cursor: pointer;
    }
    .item-row .remove-btn:hover {
        background: #fcc;
    }
    .total-display {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
    }
    .total-display h3 {
        margin: 0;
        font-size: 14px;
        opacity: 0.9;
    }
    .total-display .amount {
        font-size: 36px;
        font-weight: 700;
    }
    .add-item-btn {
        border: 2px dashed #667eea;
        border-radius: 8px;
        padding: 16px;
        text-align: center;
        color: #667eea;
        cursor: pointer;
        background: transparent;
        width: 100%;
        font-weight: 500;
        transition: all 0.2s;
    }
    .add-item-btn:hover {
        background: #f0f2ff;
    }
    .form-input {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px 14px;
        width: 100%;
        font-size: 14px;
        transition: border-color 0.2s;
    }
    .form-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
    }
    .form-label {
        font-size: 13px;
        font-weight: 500;
        color: #555;
        margin-bottom: 4px;
        display: block;
    }
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .info-field {
        padding: 8px 0;
    }
    .info-field .label {
        font-size: 12px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .info-field .value {
        font-size: 15px;
        font-weight: 500;
        color: #333;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 14px 32px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102,126,234,0.35);
    }
    .btn-back {
        background: #f0f0f0;
        color: #555;
        border: none;
        border-radius: 10px;
        padding: 14px 24px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-back:hover {
        background: #e0e0e0;
    }
    .flash-message {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 14px;
    }
    .flash-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .flash-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="edit-container">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="flash-message flash-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash-message flash-error">{{ session('error') }}</div>
        @endif

        <!-- Back Button -->
        <div style="margin-bottom: 16px;">
            <a href="{{ route('sales.prototype.kanban') }}" class="btn-back">
                ← Back to Kanban
            </a>
        </div>

        <!-- Order Info Card -->
        <div class="section-card">
            <div class="section-title">Order Information</div>
            <div class="info-grid">
                <div class="info-field">
                    <div class="label">Order #</div>
                    <div class="value">{{ $sale->sales_number }}</div>
                </div>
                <div class="info-field">
                    <div class="label">Department</div>
                    <div class="value">
                        <span class="status-badge" style="background:{{ $deptColors[$sale->department_id] ?? '#6c757d' }};color:white;">
                            {{ $deptLabels[$sale->department_id] ?? $sale->department_name ?? 'Unknown' }}
                        </span>
                    </div>
                </div>
                <div class="info-field">
                    <div class="label">Customer</div>
                    <div class="value">{{ $sale->customer_name }}</div>
                </div>
                <div class="info-field">
                    <div class="label">Phone</div>
                    <div class="value">{{ $sale->customer_phone }}</div>
                </div>
                <div class="info-field">
                    <div class="label">Sales Agent</div>
                    <div class="value">{{ $sale->sales_agent_name ?? 'N/A' }}</div>
                </div>
                <div class="info-field">
                    <div class="label">Status</div>
                    <div class="value">
                        <span class="status-badge" style="background:#fff3cd;color:#856404;">
                            {{ ucfirst(str_replace('_', ' ', $sale->kanban_status ?? 'new')) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Items Form -->
        <form method="POST" action="{{ route('sales.prototype.update', $sale->id) }}" id="editForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="department_id" value="{{ $sale->department_id }}">

            <!-- Items Section -->
            <div class="section-card">
                <div class="section-title">Order Items</div>
                <div id="itemsContainer">
                    @foreach($services as $index => $item)
                        @php $itemName = is_string($item) ? $item : ($item['name'] ?? $item['service'] ?? json_encode($item)); @endphp
                        <div class="item-row" data-index="{{ $index }}">
                            <button type="button" class="remove-btn" onclick="removeItem(this)">✕ Remove</button>
                            <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;">
                                <div>
                                    <label class="form-label">Item Name</label>
                                    <input type="text" name="items[{{ $index }}][name]" value="{{ $itemName }}" class="form-input" required>
                                </div>
                                <div>
                                    <label class="form-label">Qty</label>
                                    <input type="number" name="items[{{ $index }}][qty]" value="{{ $item['qty'] ?? 1 }}" min="1" class="form-input" onchange="calcTotal()">
                                </div>
                                <div>
                                    <label class="form-label">Price (₱)</label>
                                    <input type="number" name="items[{{ $index }}][price]" value="{{ $item['price'] ?? 0 }}" min="0" step="0.01" class="form-input" onchange="calcTotal()">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="add-item-btn" onclick="addItem()" style="margin-top:12px;">
                    + Add New Item
                </button>
            </div>

            <!-- Internal Notes -->
            <div class="section-card">
                <div class="section-title">Internal Notes</div>
                <textarea name="internal_notes" class="form-input" rows="3" style="resize:vertical;">{{ $sale->internal_notes ?? '' }}</textarea>
            </div>

            <!-- Total & Save -->
            <div class="section-card" style="display:flex;align-items:center;justify-content:space-between;gap:20px;">
                <div class="total-display" style="flex:1;">
                    <h3>Total Amount</h3>
                    <div class="amount">₱<span id="totalAmount">{{ number_format($sale->total_amount, 2) }}</span></div>
                </div>
                <button type="submit" class="btn-save">💾 Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
let itemIndex = {{ count($services) }};

function addItem() {
    const container = document.getElementById('itemsContainer');
    const idx = itemIndex++;
    const div = document.createElement('div');
    div.className = 'item-row';
    div.dataset.index = idx;
    div.innerHTML = `
        <button type="button" class="remove-btn" onclick="removeItem(this)">✕ Remove</button>
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;">
            <div>
                <label class="form-label">Item Name</label>
                <input type="text" name="items[${idx}][name]" class="form-input" required placeholder="Enter item name">
            </div>
            <div>
                <label class="form-label">Qty</label>
                <input type="number" name="items[${idx}][qty]" value="1" min="1" class="form-input" onchange="calcTotal()">
            </div>
            <div>
                <label class="form-label">Price (₱)</label>
                <input type="number" name="items[${idx}][price]" value="0" min="0" step="0.01" class="form-input" onchange="calcTotal()">
            </div>
        </div>
    `;
    container.appendChild(div);
    calcTotal();
}

function removeItem(btn) {
    if (document.querySelectorAll('.item-row').length <= 1) {
        alert('Cannot remove the last item.');
        return;
    }
    btn.closest('.item-row').remove();
    calcTotal();
}

function calcTotal() {
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('input[name$="[qty]"]')?.value) || 1;
        const price = parseFloat(row.querySelector('input[name$="[price]"]')?.value) || 0;
        total += qty * price;
    });
    document.getElementById('totalAmount').textContent = total.toFixed(2);
}
</script>
@endsection
