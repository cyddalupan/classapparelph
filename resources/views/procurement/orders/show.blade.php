@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <a href="{{ route('procurement.orders.index') }}" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left me-1"></i> Back to Orders
                </a>
                <h1 class="page-title mt-1">{{ $order->order_number }}</h1>
                <p class="page-subtitle mb-0">
                    {{ $order->department?->name ?? 'General' }}
                    @if($order->supplier)
                        &middot; <i class="fas fa-store me-1"></i>{{ $order->supplier->name }}
                    @endif
                    &middot; {{ $order->creator?->name ?? 'Unknown' }}
                </p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-{{ $order->statusColor() }} fs-6 px-3 py-2">{{ $order->statusLabel() }}</span>
            </div>
        </div>
    </div>
</div>

<div class="container">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ⚠️ Pending Verification Banner --}}
    @if($order->status === 'for_verification')
        @if($order->verified_at)
        <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-2">
            <i class="fas fa-check-circle fa-lg"></i>
            <div>
                <strong>✅ Verified</strong> by <strong>{{ $order->verifier?->name ?? 'Manager' }}</strong>
                ({{ $order->verified_at->format('M d, Y h:i A') }})
                <br><small>Verification done — procurement can now mark as Completed to update inventory.</small>
            </div>
        </div>
        @else
        <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-2">
            <i class="fas fa-clock fa-lg"></i>
            <div>
                <strong>Pending Verification</strong> — This delivery needs to be checked by the shop manager.
                @if(auth()->id() === $order->department?->manager_id || auth()->user()->isAdmin())
                <br><small>You are the manager! Use the verification form below.</small>
                @endif
            </div>
        </div>
        @endif
    @endif

    <div class="row g-4">
        {{-- LEFT: Order Details --}}
        <div class="col-md-8">
            {{-- Feature 1: Supplier Summary (Grouped by Brand/Color/Size) --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-list-alt me-1"></i> Items Summary
                        <small class="text-muted ms-2">(grouped by brand / color / size)</small>
                    </h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="copyOrderSummary()">
                        <i class="fas fa-copy me-1"></i> Copy for Supplier
                    </button>
                </div>
                <div class="card-body p-0" id="order-summary">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Brand</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th class="text-center">Ordered</th>
                                    <th class="text-center">From Supplier</th>
                                    <th class="text-center">Verified</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $grandTotal = 0; $grandOrdered = 0; $grandFromSupplier = 0; @endphp
                                @foreach($groupedItems as $specKey => $specItems)
                                @php
                                    $first = $specItems->first();
                                    $masterItem = $first->masterItem;
                                    $brand = $masterItem?->brand ?? '';
                                    $color = $masterItem?->color ?? $masterItem?->other_color ?? '';
                                    $size = $masterItem?->size ?? '';
                                    $totalQty = $specItems->sum('quantity_ordered');
                                    $totalFromSupplier = $specItems->sum('qty_from_supplier') ?: $totalQty;
                                    $totalVerified = $specItems->sum('qty_verified');
                                    // Sum actual subtotals per item to handle different prices
                                    $subtotal = $specItems->sum(fn($i) => $i->quantity_ordered * $i->unit_price);
                                    $grandTotal += $subtotal;
                                    $grandOrdered += $totalQty;
                                    $grandFromSupplier += $totalFromSupplier;
                                @endphp
                                <tr>
                                    <td><strong>{{ $first->item_name }}</strong></td>
                                    <td><span class="badge bg-secondary">{{ $brand ?: '—' }}</span></td>
                                    <td>
                                        @if($color)
                                        <span class="badge" style="background:{{ $color === 'RED' ? '#dc3545' : ($color === 'BLUE' ? '#0d6efd' : ($color === 'BLACK' ? '#212529' : ($color === 'WHITE' ? '#6c757d' : '#6c757d'))) }}; color:#fff;">
                                            {{ $color }}
                                        </span>
                                        @else<span class="text-muted">—</span>@endif
                                    </td>
                                    <td>{{ $size ?: '—' }}</td>
                                    <td class="text-center fw-bold">{{ $totalQty }}</td>
                                    <td class="text-center {{ $totalFromSupplier < $totalQty ? 'text-warning fw-bold' : 'text-success' }}">
                                        {{ $totalFromSupplier }}
                                        @if($totalFromSupplier < $totalQty)
                                            @if(auth()->user()->isProcurement() || auth()->user()->isAdmin())
                                            <i class="fas fa-exclamation-triangle text-warning ms-1" title="Supplier only has {{ $totalFromSupplier }} out of {{ $totalQty }} ordered"></i>
                                            @else
                                            <i class="fas fa-exclamation-triangle text-warning ms-1" title="Available: {{ $totalFromSupplier }} out of {{ $totalQty }} ordered"></i>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold {{ $totalVerified > 0 ? 'text-success' : 'text-muted' }}">{{ $totalVerified > 0 ? $totalVerified : '—' }}</td>
                                    <td class="text-end">
                                        @php
                                            $prices = $specItems->pluck('unit_price')->unique();
                                        @endphp
                                        @if($prices->count() === 1)
                                            ₱{{ number_format($prices->first(), 2) }}
                                        @else
                                            <span class="text-muted small" title="Varying prices: ₱{{ $prices->map(fn($p) => number_format($p,2))->join(', ') }}">
                                                ₱{{ number_format($prices->min(), 2) }} – ₱{{ number_format($prices->max(), 2) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">₱{{ number_format($subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-center fw-bold">{{ $grandOrdered }}</td>
                                    <td class="text-center fw-bold {{ $grandFromSupplier < $grandOrdered ? 'text-warning' : 'text-success' }}">{{ $grandFromSupplier }}</td>
                                    <td colspan="2" class="text-end fw-bold">Total: ₱{{ number_format($grandTotal, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 📋 SUPPLIER AVAILABILITY (Procurement) --}}
            {{-- Procurement checks with supplier and notes how many are available + any substitutions --}}
            @if((auth()->user()->isProcurement() || auth()->user()->isAdmin()) && in_array($order->status, ['draft', 'for_approval', 'for_procurement', 'ordered', 'ongoing', 'preparing', 'for_delivery']) && $order->supplier_id)
            <div class="card border-0 shadow-sm mb-4 border-start border-4 border-primary">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-store me-1"></i> Supplier Availability</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">
                        <i class="fas fa-info-circle me-1"></i>
                        I-check sa supplier kung ilan talaga available. Pag may kulang, ilagay mo yung actual na available at notes kung may alternate brand.
                    </p>
                    <form method="POST" action="{{ route('procurement.orders.supplier-availability', $order->id) }}" id="supplierAvailForm">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Ordered</th>
                                        <th class="text-center">Supplier Has*</th>
                                        <th>Notes (substitutions, brand change)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->item_name }}</strong>
                                            <span class="text-muted small">({{ $item->sku }})</span>
                                            <span class="text-muted small">— ₱{{ number_format($item->unit_price, 2) }}</span>
                                        </td>
                                        <td class="text-center fw-bold">{{ $item->quantity_ordered }}</td>
                                        <td class="text-center">
                                            <input type="number" name="items[{{ $loop->index }}][id]" hidden value="{{ $item->id }}">
                                            <input type="number" name="items[{{ $loop->index }}][qty_from_supplier]" 
                                                class="form-control form-control-sm text-center" 
                                                style="width:80px" min="0" value="{{ $item->qty_from_supplier ?? $item->quantity_ordered }}">
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <input type="text" name="items[{{ $loop->index }}][supplier_notes]" 
                                                    class="form-control form-control-sm flex-grow-1" 
                                                    placeholder="e.g., Only 15 avail, offering Hanes as alt"
                                                    value="{{ $item->supplier_notes }}">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="openSubstituteModal({{ $item->id }}, '{{ addslashes($item->item_name) }}')"
                                                    title="Substitute with different brand">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Supplier Info
                            </button>
                            <small class="text-muted mt-1">Then use the Status dropdown below to move to <strong>For Delivery</strong> when supplier has shipped.</small>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- Substitute Item Modal --}}
            @if((auth()->user()->isProcurement() || auth()->user()->isAdmin()) && $order->supplier_id)
            <div class="modal fade" id="substituteModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <form id="substituteForm" method="POST">
                            @csrf
                            <div class="modal-header bg-warning text-dark">
                                <h6 class="modal-title"><i class="fas fa-exchange-alt me-1"></i> Substitute Item</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="small text-muted mb-2">
                                    Replacing: <strong id="subModalCurrentItem"></strong>
                                </p>
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="subSearchInput" 
                                        placeholder="Search by name, brand, SKU..." onkeyup="filterSubstituteItems()">
                                </div>
                                <div class="row g-2" id="subCatalogItems">
                                    @foreach($catalogItems as $cat)
                                    <div class="col-md-6 col-lg-4 sub-item-card" 
                                         data-name="{{ strtolower($cat->name) }}" 
                                         data-brand="{{ strtolower($cat->brand ?? '') }}"
                                         data-sku="{{ strtolower($cat->sku) }}">
                                        <div class="card border cursor-pointer sub-item-card-inner" 
                                             onclick="selectSubstitute({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->brand ?? 'N/A') }}', {{ $cat->unit_price }})">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="small">{{ $cat->name }}</strong>
                                                        <span class="text-muted small d-block">{{ $cat->brand }} — {{ $cat->color ?? '' }} {{ $cat->size ?? '' }}</span>
                                                        <span class="text-muted small">SKU: {{ $cat->sku }}</span>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-primary">₱{{ number_format($cat->unit_price, 2) }}</span>
                                                        @if($cat->current_stock > 0)
                                                        <span class="badge bg-success d-block mt-1">Stock: {{ $cat->current_stock }}</span>
                                                        @else
                                                        <span class="badge bg-danger d-block mt-1">Out of Stock</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-warning" id="subConfirmBtn" disabled>
                                    <i class="fas fa-check me-1"></i> Confirm Substitute
                                </button>
                                <input type="hidden" name="master_item_id" id="subMasterItemId">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            {{-- ✅ VERIFICATION FORM (Manager) — only shows if NOT yet verified --}}
            @if($order->status === 'for_verification' && !$order->verified_at && (auth()->id() === $order->department?->manager_id || auth()->user()->isAdmin()))
            <div class="card border-0 shadow-sm mb-4 border-start border-4 border-success">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold text-success"><i class="fas fa-clipboard-check me-1"></i> Verify Delivery</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        I-check mo yung dumating. Enter kung ilan talaga yung good condition — 
                        <strong>verification lang ito, hindi completion</strong>.
                        Kung may shortage, ilagay mo lang yung actual count.
                    </p>
                    <form method="POST" action="{{ route('procurement.orders.verify', $order->id) }}" id="verifyForm">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Ordered</th>
                                        <th class="text-center">Supplier Has</th>
                                        <th class="text-center">Supplier Notes</th>
                                        <th class="text-center">Counted*</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    @php $supplierQty = $item->qty_from_supplier ?? $item->quantity_ordered; @endphp
                                    <tr>
                                        <td><strong>{{ $item->item_name }}</strong></td>
                                        <td class="text-center">{{ $item->quantity_ordered }}</td>
                                        <td class="text-center fw-bold {{ $supplierQty < $item->quantity_ordered ? 'text-warning' : 'text-success' }}">
                                            {{ $supplierQty }}
                                            @if($supplierQty < $item->quantity_ordered)
                                            @if(auth()->user()->isProcurement() || auth()->user()->isAdmin())
                                            <i class="fas fa-exclamation-triangle text-warning ms-1" title="Supplier only has {{ $supplierQty }} instead of {{ $item->quantity_ordered }}"></i>
                                            @else
                                            <i class="fas fa-exclamation-triangle text-warning ms-1" title="Available: {{ $supplierQty }} out of {{ $item->quantity_ordered }} ordered"></i>
                                            @endif
                                            @endif
                                        </td>
                                        <td class="text-center small text-muted">{{ $item->supplier_notes ?: '—' }}</td>
                                        <td class="text-center">
                                            <input type="number" name="items[{{ $loop->index }}][id]" hidden value="{{ $item->id }}">
                                            <input type="number" name="items[{{ $loop->index }}][qty_verified]" class="form-control form-control-sm text-center fw-bold" style="width:80px" min="0" value="{{ $supplierQty }}" required>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-double me-1"></i> Confirm Verification
                            </button>
                            <small class="text-muted mt-1">✅ Verification lang ito. Status stays <strong>For Verification</strong> — procurement ang mag complete para mag-update ang inventory.</small>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- Status / Supplier Update — only procurement and admin can change status --}}
            @if(auth()->user()->isProcurement() || auth()->user()->isAdmin())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-tasks me-1"></i> Update Order</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('procurement.orders.status', $order->id) }}" class="row g-2 align-items-end">
                        @csrf @method('PUT')
                        <div class="col-md-5">
                            <label class="form-label small">Change Status</label>
                            <select name="status" class="form-select form-select-sm" required>
                                @foreach(['draft','for_approval','for_procurement','ordered','ongoing','preparing','for_delivery','partial','delivered','for_verification','completed','cancelled'] as $st)
                                    @if($st === 'completed' && !auth()->user()->isAdmin() && auth()->id() !== $order->department?->manager_id)
                                        @continue
                                    @endif
                                    @if($st === 'for_verification' && !auth()->user()->isProcurement() && !auth()->user()->isAdmin())
                                        @continue
                                    @endif
                                    <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $st)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Supplier</label>
                            <select name="supplier_id" class="form-select form-select-sm">
                                <option value="">— No Supplier —</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}" {{ $order->supplier_id == $s->id ? 'selected' : '' }}>
                                        {{ $s->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-grid">
                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                        </div>
                        <div class="col-12">
                            <textarea name="procurement_notes" class="form-control form-control-sm" rows="2" placeholder="Procurement notes / remarks for this update...">{{ $order->procurement_notes }}</textarea>
                        </div>
                    </form>
                </div>
            </div>
            @endif {{-- end: Status Update (procurement/admin only) --}}

            {{-- Feature 7: Notify Manager --}}
            @if(auth()->user()->isProcurement() || auth()->user()->isAdmin())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-bell me-1 text-warning"></i> Notify Manager</h6>
                </div>
                <div class="card-body">
                    @if($order->department?->manager_id)
                    <form method="POST" action="{{ route('procurement.orders.notify', $order->id) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label small">Priority</label>
                            <select name="type" class="form-select form-select-sm" required>
                                <option value="info">Info</option>
                                <option value="status_update">Status Update</option>
                                <option value="reminder">Reminder</option>
                                <option value="urgent">🚨 Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Title</label>
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="e.g., Order ready for review" required>
                        </div>
                        <div class="col-md-4 d-grid">
                            <button type="submit" class="btn btn-sm btn-warning">
                                <i class="fas fa-bell me-1"></i> Notify Manager
                            </button>
                        </div>
                        <div class="col-12">
                            <textarea name="message" class="form-control form-control-sm" rows="1" placeholder="Optional message for the manager..."></textarea>
                        </div>
                    </form>
                    @else
                    <div class="alert alert-info mb-0 py-2 small">
                        <i class="fas fa-info-circle me-1"></i> No manager assigned to this department ({{ $order->department?->name ?? 'General' }}).
                        Ask an admin to assign a manager.
                    </div>
                    @endif
                </div>
            </div>

            {{-- Notifications sent to manager --}}
            @if($order->notifications->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-history me-1"></i> Notification History</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($order->notifications as $n)
                        <div class="list-group-item py-2 d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-{{ $n->type === 'urgent' ? 'danger' : ($n->type === 'reminder' ? 'warning' : 'info') }} me-1">
                                    {{ ucfirst($n->type) }}
                                </span>
                                <strong>{{ $n->title }}</strong>
                                @if($n->message)<p class="mb-0 small text-muted">{{ $n->message }}</p>@endif
                                <small class="text-muted">
                                    From {{ $n->fromUser?->name }} → {{ $n->toUser?->name }}
                                    &middot; {{ $n->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $n->is_read ? 'success' : 'warning' }}">
                                {{ $n->is_read ? 'Read' : 'Unread' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            @endif
        </div>

        {{-- RIGHT SIDEBAR: Feature 5 (Remarks) --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-comments me-1"></i> Remarks
                        <span class="badge bg-secondary ms-1">{{ $order->remarks->count() }}</span>
                    </h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="showAddRemark()">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                    @if($order->remarks->count() > 0)
                    <div class="list-group list-group-flush" id="remarks-list">
                        @foreach($order->remarks as $remark)
                        <div class="list-group-item py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div class="d-flex align-items-center gap-1">
                                    <strong class="small">{{ $remark->user?->name ?? 'Unknown' }}</strong>
                                    <span class="badge bg-{{ $remark->type === 'issue' ? 'danger' : ($remark->type === 'shortage' ? 'warning' : ($remark->type === 'damage' ? 'dark' : 'secondary')) }} rounded-pill" style="font-size:9px;">
                                        {{ ucfirst($remark->type) }}
                                    </span>
                                    @if($remark->is_internal)<span class="badge bg-info rounded-pill" style="font-size:9px;">Internal</span>@endif
                                </div>
                                <small class="text-muted" style="font-size:10px;">{{ $remark->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0 small">{{ $remark->remark }}</p>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4 text-muted small">
                        <i class="fas fa-comment-slash fa-2x mb-2"></i>
                        <p class="mb-0">No remarks yet.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Delivery Timeline --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-timeline me-1"></i> Timeline</h6>
                </div>
                <div class="card-body py-2 small">
                    <div class="mb-1"><strong>Created:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</div>
                    <div class="mb-1"><strong>Department:</strong> {{ $order->department?->name ?? 'General' }}</div>
                    @if(auth()->user()->isProcurement() || auth()->user()->isAdmin())
                    <div class="mb-1"><strong>Supplier:</strong> {{ $order->supplier?->name ?? 'Not set' }}</div>
                    @endif
                    <div class="mb-1"><strong>Total Items:</strong> {{ $order->items->sum('quantity_ordered') }}</div>
                    @if($order->submitted_at)<div class="mb-1"><strong>Submitted:</strong> {{ $order->submitted_at->format('M d, Y h:i A') }}</div>@endif
                    @if($order->ordered_at)<div class="mb-1"><strong>Ordered:</strong> {{ $order->ordered_at->format('M d, Y h:i A') }}</div>@endif
                    @if($order->received_at)<div class="mb-1"><strong>Delivery Received:</strong> {{ $order->received_at->format('M d, Y h:i A') }} <span class="text-muted">({{ $order->receiver?->name ?? '' }})</span></div>@endif
                    @if($order->verified_at)<div class="mb-1"><strong>Verified by Manager:</strong> {{ $order->verified_at->format('M d, Y h:i A') }} <span class="text-muted">({{ $order->verifier?->name ?? '' }})</span></div>@endif
                    @if($order->notes)
                    <hr class="my-2">
                    <div><strong>Notes:</strong><br>{{ $order->notes }}</div>
                    @endif
                    @if($order->procurement_notes)
                    <hr class="my-2">
                    <div><strong>Procurement Notes:</strong><br>{{ $order->procurement_notes }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Remarks Modal --}}
<div class="modal fade" id="addRemarkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('procurement.orders.remark', $order->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Remark</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small">Type</label>
                        <select name="type" class="form-select form-select-sm" required>
                            <option value="general">General</option>
                            <option value="issue">Issue / Problem</option>
                            <option value="shortage">Shortage</option>
                            <option value="damage">Damage</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Remark *</label>
                        <textarea name="remark" class="form-control" rows="3" required placeholder="What's the issue? Missing items? Damaged goods?"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_internal" value="1" class="form-check-input" id="isInternal">
                        <label class="form-check-label small" for="isInternal">Internal only (not visible to manager)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Add Remark</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
.cursor-pointer { cursor: pointer; }
.sub-item-card-inner { transition: all 0.15s ease; }
.sub-item-card-inner:hover { border-color: #ffc107 !important; background: #fffbf0; }
</style>
<script>
function showAddRemark() {
    new bootstrap.Modal(document.getElementById('addRemarkModal')).show();
}

function copyOrderSummary() {
    const table = document.querySelector('#order-summary table');
    if (!table) return;

    let text = '=== ORDER: {{ $order->order_number }} ===\n\n';
    @if(auth()->user()->isProcurement() || auth()->user()->isAdmin())
    text += 'Supplier: {{ $order->supplier?->name ?? "N/A" }}\n';
    @endif
    text += 'Department: {{ $order->department?->name ?? "General" }}\n\n';

    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 5) {
            const item = cells[0]?.textContent?.trim() || '';
            const brand = cells[1]?.textContent?.trim() || '';
            const color = cells[2]?.textContent?.trim() || '';
            const size = cells[3]?.textContent?.trim() || '';
            const qty = cells[4]?.textContent?.trim() || '';
            text += `${item} | ${brand} | ${color} | ${size} | ${qty}\n`;
        }
    });

    navigator.clipboard.writeText(text).then(() => {
        showToast('Order summary copied!', 'success');
    }).catch(() => {
        showToast('Failed to copy.', 'warning');
    });
}

function showToast(msg, type) {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-bg-' + (type || 'success') + ' border-0 show';
    toast.innerHTML = '<div class="d-flex"><div class="toast-body">' + (msg || '') + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    container.appendChild(toast);
    setTimeout(() => { toast.remove(); }, 4000);
}

// Substitute item modal
let selectedItemId = null;

function openSubstituteModal(itemId, itemName) {
    selectedItemId = itemId;
    document.getElementById('subModalCurrentItem').textContent = itemName;
    document.getElementById('subMasterItemId').value = '';
    document.getElementById('subConfirmBtn').disabled = true;
    document.getElementById('subSearchInput').value = '';
    filterSubstituteItems();
    // Highlight current substitute if any
    document.querySelectorAll('.sub-item-card-inner').forEach(c => c.classList.remove('border-warning', 'border-2'));
    new bootstrap.Modal(document.getElementById('substituteModal')).show();
}

function selectSubstitute(id, name, brand, price) {
    document.getElementById('subMasterItemId').value = id;
    document.getElementById('subConfirmBtn').disabled = false;
    document.querySelectorAll('.sub-item-card-inner').forEach(c => c.classList.remove('border-warning', 'border-2'));
    // Find the clicked card — toggle visual
    const cards = document.querySelectorAll('.sub-item-card-inner');
    const btn = event.currentTarget;
    btn.classList.add('border-warning', 'border-2');
    document.getElementById('subConfirmBtn').innerHTML = '<i class="fas fa-check me-1"></i> Substitute to ' + name + ' (₱' + price.toFixed(2) + ')';
}

function filterSubstituteItems() {
    const query = document.getElementById('subSearchInput').value.toLowerCase();
    document.querySelectorAll('.sub-item-card').forEach(card => {
        const name = card.dataset.name || '';
        const brand = card.dataset.brand || '';
        const sku = card.dataset.sku || '';
        const match = name.includes(query) || brand.includes(query) || sku.includes(query);
        card.style.display = match ? '' : 'none';
    });
}

// Handle substitute form submit — set correct action URL
document.addEventListener('DOMContentLoaded', function() {
    const subForm = document.getElementById('substituteForm');
    if (subForm) {
        subForm.addEventListener('submit', function(e) {
            if (!selectedItemId || !document.getElementById('subMasterItemId').value) {
                e.preventDefault();
                showToast('Please select a substitute item first.', 'warning');
                return;
            }
            // Set the action to the correct route
            const orderId = {{ $order->id }};
            this.action = '/procurement/orders/' + orderId + '/items/' + selectedItemId + '/substitute';
        });
    }
});
</script>
@endpush
