@extends('layouts.app')

@section('page-title', $customer->name . ' | Customers')

@section('content')
<div class="page-content">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <a href="{{ route('customers.index') }}" class="text-muted text-decoration-none mb-2 d-inline-block">
                <i class="fas fa-arrow-left me-1"></i> Back to Customers
            </a>
            <h1 class="page-title mb-1">
                <i class="fas fa-user me-2"></i>{{ $customer->name }}
                <code class="ms-2" style="font-size: 14px;">{{ $customer->customer_id_number }}</code>
                @php
                    $tierColors = ['bronze' => '#cd7f32', 'silver' => '#a8a8a8', 'gold' => '#ffd700', 'platinum' => '#e5e4e2'];
                    $tierColor = $tierColors[$customer->customer_tier] ?? '#cd7f32';
                @endphp
                <span class="badge ms-1" style="background: {{ $tierColor }}; color: #000; font-size: 12px; vertical-align: middle;">
                    {{ ucfirst($customer->customer_tier) }}
                </span>
            </h1>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="editCustomerBtn" onclick="toggleEdit()">
                <i class="fas fa-edit me-1"></i> Edit
            </button>
            <a href="{{ route('sales.prototype.create', ['customer_id' => $customer->id]) }}" class="btn btn-success">
                <i class="fas fa-cart-plus me-1"></i> New Sale
            </a>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Total Orders</div>
                    <div class="display-6 fw-bold text-primary">{{ $orderCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Total Spent</div>
                    <div class="display-6 fw-bold text-success">₱{{ number_format($totalSpent, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Outstanding Balance</div>
                    <div class="display-6 fw-bold {{ $outstandingBalance > 0 ? 'text-danger' : 'text-secondary' }}">
                        ₱{{ number_format($outstandingBalance, 2) }}
                    </div>
                    @if($outstandingBalance > 0)
                        <span class="badge bg-danger mt-1"><i class="fas fa-exclamation-circle me-1"></i>May pending balance pa</span>
                    @else
                        <span class="badge bg-success mt-1">Fully paid ✓</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Avg Order Value</div>
                    <div class="display-6 fw-bold text-info">
                        ₱{{ number_format($orderCount > 0 ? $totalSpent / $orderCount : 0, 2) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Days Since Last Order</div>
                    @php
                        $daysSince = $customer->last_order_date 
                            ? round(\Carbon\Carbon::parse($customer->last_order_date)->diffInDays(now()), 2) 
                            : null;
                    @endphp
                    <div class="display-6 fw-bold {{ $daysSince !== null && $daysSince > 30 ? 'text-warning' : 'text-secondary' }}">
                        {{ $daysSince !== null ? $daysSince . ' days' : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Customer Info (Editable) -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Customer Information</h5>
                </div>
                <div class="card-body">
                    <form id="customerEditForm">
                        <input type="hidden" id="customer_id" value="{{ $customer->id }}">
                        
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="edit_name" value="{{ $customer->name }}" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">+63</span>
                                <input type="tel" class="form-control" id="edit_phone" 
                                    value="{{ preg_replace('/^\+63/', '', $customer->phone) }}" disabled>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" value="{{ $customer->email }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Marketplace / Source</label>
                            <select class="form-control" id="edit_marketplace" disabled>
                                @foreach(\App\Models\Customer::getMarketplaceOptions() as $val => $label)
                                    <option value="{{ $val }}" {{ $customer->marketplace == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Location / Address</label>
                            <textarea class="form-control" id="edit_location" rows="2" disabled>{{ $customer->location }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Company</label>
                            <input type="text" class="form-control" id="edit_company" value="{{ $customer->company }}" disabled>
                        </div>

                        <div class="mb-3 d-none" id="editActions">
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" onclick="saveCustomerEdit()">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="toggleEdit()">
                                    Cancel
                                </button>
                            </div>
                            <div id="editStatus" class="mt-2"></div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- NOTES SECTION -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="editNotesBtn" onclick="toggleNotesEdit()">
                        <i class="fas fa-edit me-1"></i> Edit
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-0" id="notesDisplay">
                        {{ $customer->notes ?: 'No notes yet.' }}
                    </p>
                    <div class="d-none" id="notesEditWrap">
                        <textarea class="form-control" id="edit_notes" rows="3" placeholder="Maglagay ng notes tungkol sa customer...">{{ $customer->notes }}</textarea>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-primary" onclick="saveCustomerNotes()">
                                <i class="fas fa-save me-1"></i> Save Notes
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="toggleNotesEdit()">Cancel</button>
                        </div>
                        <div id="notesStatus" class="mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Order History -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order History</h5>
                    <span class="badge bg-primary rounded-pill">{{ $orderCount }} total</span>
                </div>
                <div class="card-body p-0">
                    @if($orders->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($orders as $order)
                                <div class="list-group-item border-0 border-bottom px-3 py-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>Order #{{ $order->id }}</strong>
                                            <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'secondary') }} ms-2">
                                                {{ ucfirst($order->status ?? 'draft') }}
                                            </span>
                                            <div class="small text-muted mt-1">
                                                @if($order->department_name)
                                                    {{ $order->department_name }} &middot;
                                                @endif
                                                {{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y h:i A') }}
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold">₱{{ number_format($order->subtotal ?? 0, 2) }}</div>
                                            @if(isset($order->balance_due) && $order->balance_due > 0)
                                                <div class="small text-danger fw-bold">Bal: ₱{{ number_format($order->balance_due, 2) }}</div>
                                            @endif
                                            <a href="{{ route('sales.prototype.show', $order->id) }}" class="small text-primary text-decoration-none">View</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-shopping-bag fa-3x mb-3 d-block"></i>
                            <p class="mb-2">No orders yet for this customer.</p>
                            <a href="{{ route('sales.prototype.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-success">
                                <i class="fas fa-plus me-1"></i> Create First Order
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- RECENTLY PURCHASED ITEMS -->
            @if($recentItems->count() > 0)
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-box me-2"></i>Recently Purchased Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($recentItems as $item)
                            <div class="list-group-item border-0 border-bottom px-3 py-2">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>{{ $item->item_name ?? $item->product_name ?? 'Item' }}</strong>
                                        <span class="text-muted ms-2">x{{ $item->quantity }}</span>
                                    </div>
                                    <span class="text-muted">₱{{ number_format($item->total_price ?? $item->price ?? 0, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleEdit() {
    const fields = ['edit_name', 'edit_phone', 'edit_email', 'edit_marketplace', 'edit_location', 'edit_company'];
    const actions = document.getElementById('editActions');
    const btn = document.getElementById('editCustomerBtn');
    const isEditing = actions.classList.contains('d-none');
    
    fields.forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.disabled = !isEditing;
    });
    
    if (isEditing) {
        actions.classList.remove('d-none');
        btn.innerHTML = '<i class="fas fa-times me-1"></i> Cancel';
    } else {
        actions.classList.add('d-none');
        btn.innerHTML = '<i class="fas fa-edit me-1"></i> Edit';
        document.getElementById('editStatus').innerHTML = '';
    }
}

function toggleNotesEdit() {
    const wrap = document.getElementById('notesEditWrap');
    const display = document.getElementById('notesDisplay');
    const btn = document.getElementById('editNotesBtn');
    const isEditing = wrap.classList.contains('d-none');
    
    if (isEditing) {
        wrap.classList.remove('d-none');
        display.classList.add('d-none');
        btn.innerHTML = '<i class="fas fa-times me-1"></i> Cancel';
    } else {
        wrap.classList.add('d-none');
        display.classList.remove('d-none');
        btn.innerHTML = '<i class="fas fa-edit me-1"></i> Edit';
        document.getElementById('notesStatus').innerHTML = '';
    }
}

function saveCustomerNotes() {
    const id = document.getElementById('customer_id').value;
    const notes = document.getElementById('edit_notes').value.trim();
    const status = document.getElementById('notesStatus');
    
    status.innerHTML = '<div class="text-primary"><i class="fas fa-spinner fa-spin me-1"></i>Saving...</div>';
    
    fetch('/api/customers/' + id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ notes: notes, _token: '{{ csrf_token() }}' })
    })
    .then(function(resp) { return resp.json(); })
    .then(function(result) {
        if (result.success) {
            status.innerHTML = '<div class="text-success"><i class="fas fa-check me-1"></i>Notes saved!</div>';
            document.getElementById('notesDisplay').textContent = notes || 'No notes yet.';
            setTimeout(function() { toggleNotesEdit(); }, 1200);
        } else {
            status.innerHTML = '<div class="alert alert-danger py-2 mb-0">' + (result.message || 'Save failed') + '</div>';
        }
    })
    .catch(function(err) {
        status.innerHTML = '<div class="alert alert-danger py-2 mb-0">Error saving notes.</div>';
        console.error(err);
    });
}

function saveCustomerEdit() {
    const id = document.getElementById('customer_id').value;
    const data = {
        name: document.getElementById('edit_name').value.trim(),
        phone: '+63' + document.getElementById('edit_phone').value.trim().replace(/^0/, ''),
        email: document.getElementById('edit_email').value.trim(),
        marketplace: document.getElementById('edit_marketplace').value,
        location: document.getElementById('edit_location').value.trim(),
        company: document.getElementById('edit_company').value.trim(),
        _token: '{{ csrf_token() }}'
    };
    
    if (!data.name) {
        document.getElementById('editStatus').innerHTML = '<div class="alert alert-danger py-2 mb-0">Name is required.</div>';
        return;
    }
    
    document.getElementById('editStatus').innerHTML = '<div class="text-primary"><i class="fas fa-spinner fa-spin me-1"></i>Saving...</div>';
    
    fetch('/api/customers/' + id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(data)
    })
    .then(function(resp) { return resp.json(); })
    .then(function(result) {
        if (result.success) {
            document.getElementById('editStatus').innerHTML = '<div class="text-success"><i class="fas fa-check me-1"></i>Customer updated!</div>';
            setTimeout(function() { toggleEdit(); }, 1500);
        } else {
            document.getElementById('editStatus').innerHTML = '<div class="alert alert-danger py-2 mb-0">' + (result.message || 'Save failed') + '</div>';
        }
    })
    .catch(function(err) {
        document.getElementById('editStatus').innerHTML = '<div class="alert alert-danger py-2 mb-0">Error saving changes.</div>';
        console.error(err);
    });
}
</script>
@endpush
