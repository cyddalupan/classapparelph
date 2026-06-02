@extends('layouts.app')

@section('content')

{{-- Pending Verifications Summary — shown on index page for quick access --}}
@if($pendingVerificationsCount > 0)
<div class="bg-warning bg-opacity-10 border-bottom border-warning py-1">
    <div class="container">
        <div class="d-flex align-items-center gap-2 small">
            <i class="fas fa-clipboard-check text-warning"></i>
            <a href="{{ route('procurement.orders.index', ['status' => 'for_verification']) }}" class="text-decoration-none fw-bold">
                {{ $pendingVerificationsCount }} order(s) pending verification
            </a>
            <span class="text-muted">— click to review</span>
        </div>
    </div>
</div>
@endif

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="page-title">Procurement Dashboard</h1>
                <p class="page-subtitle">Manage orders per supplier — screenshot, send, track</p>
            </div>
            <div class="d-flex gap-2">
                @if($isProcurement || $isAdmin)
                <a href="{{ route('procurement.suppliers.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-truck me-1"></i> Suppliers
                </a>
                <a href="{{ route('procurement.analytics') }}" class="btn btn-outline-info">
                    <i class="fas fa-chart-line me-1"></i> Analytics
                </a>
                @endif
                <a href="{{ route('procurement.orders.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> New Order
                </a>
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

    {{-- Feature 3: Grand Total Summary --}}
    <div class="row g-2 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body text-center py-2">
                    <h3 class="mb-0 fw-bold text-primary">{{ $totalOrders }}</h3>
                    <small class="text-muted">Total Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #198754 !important;">
                <div class="card-body text-center py-2">
                    <h3 class="mb-0 fw-bold text-success">{{ $grandTotalQty }}</h3>
                    <small class="text-muted">Total Items Ordered</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body text-center py-2">
                    <h3 class="mb-0 fw-bold text-danger">₱{{ number_format($grandTotalCost, 2) }}</h3>
                    <small class="text-muted">Grand Total Cost</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #6f42c1 !important;">
                <div class="card-body text-center py-2">
                    <h3 class="mb-0 fw-bold text-purple" style="color:#6f42c3">{{ $completedCount }}</h3>
                    <small class="text-muted">Completed <span class="text-success fw-bold">₱{{ number_format($completedTotalCost, 2) }}</span></small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters (Feature 2: Supplier Filter) --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('procurement.orders.index') }}" class="row g-2 align-items-end">
                @if(auth()->user()->isProcurement() || auth()->user()->isAdmin())
                <div class="col-md-3">
                    <label class="form-label small fw-medium">
                        <i class="fas fa-store me-1"></i> Supplier
                    </label>
                    <select name="supplier_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label small fw-medium">Department</label>
                    <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-medium">Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $st)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-medium"><i class="fas fa-calendar me-1"></i> From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from', today()->toDateString()) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-medium"><i class="fas fa-calendar me-1"></i> To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to', today()->toDateString()) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-medium">Search Order #</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Per-Supplier Summary (Features 1, 2, 3) --}}
    @if(count($ordersGroupedBySupplier) > 0)
        @foreach($ordersGroupedBySupplier as $supplierName => $supplierOrders)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-store me-1 text-primary"></i>
                        @if(auth()->user()->isProcurement() || auth()->user()->isAdmin())
                        {{ $supplierName }}
                        @else
                        Supplier
                        @endif
                    </h6>
                    <small class="text-muted">
                        {{ $supplierTotals[$supplierName]['count'] }} order(s) &middot;
                        {{ $supplierTotals[$supplierName]['total_qty'] }} items &middot;
                        ₱{{ number_format($supplierTotals[$supplierName]['total_cost'], 2) }}
                    </small>
                </div>
                @if(auth()->user()->isProcurement() || auth()->user()->isAdmin())
                <button class="btn btn-sm btn-outline-secondary" onclick="copySupplierSummary('{{ Str::slug($supplierName) }}')">
                    <i class="fas fa-copy me-1"></i> Copy
                </button>
                @endif
            </div>

            {{-- Feature 1: Summarize by Brand/Color/Size --}}
            <div class="card-body p-0" id="supplier-summary-{{ Str::slug($supplierName) }}">
                @php
                    // Collect all items across this supplier's orders, grouped by brand/color/size
                    $allItems = collect();
                    foreach ($supplierOrders as $o) {
                        foreach ($o->items as $item) {
                            $masterItem = $item->masterItem;
                            $brand = $masterItem?->brand ?? '';
                            $color = $masterItem?->color ?? $masterItem?->other_color ?? '';
                            $size = $masterItem?->size ?? '';
                            $key = trim("{$brand}|{$color}|{$size}");
                            $allItems->push([
                                'item' => $item,
                                'masterItem' => $masterItem,
                                'key' => $key,
                                'brand' => $brand,
                                'color' => $color,
                                'size' => $size,
                                'name' => $item->item_name,
                                'qty' => $item->quantity_ordered,
                                'price' => $item->unit_price ?? 0,
                            ]);
                        }
                    }
                    $groupedBySpec = $allItems->groupBy('key');
                @endphp

                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Brand</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedBySpec as $key => $specItems)
                            @php
                                $first = $specItems->first();
                                $totalQty = $specItems->sum('qty');
                                $totalPrice = $specItems->sum(fn($si) => $si['price'] * $si['qty']);
                            @endphp
                            <tr>
                                <td><strong>{{ $first['name'] }}</strong></td>
                                <td><span class="badge bg-secondary">{{ $first['brand'] ?: '—' }}</span></td>
                                <td>
                                    @if($first['color'])
                                    <span class="badge" style="background:{{ $first['color'] == 'RED' ? '#dc3545' : ($first['color'] == 'BLUE' ? '#0d6efd' : ($first['color'] == 'BLACK' ? '#212529' : ($first['color'] == 'WHITE' ? '#6c757d' : '#6c757d'))) }}; color:#fff;">
                                        {{ $first['color'] }}
                                    </span>
                                    @else<span class="text-muted">—</span>@endif
                                </td>
                                <td>{{ $first['size'] ?: '—' }}</td>
                                <td class="text-center fw-bold">{{ $totalQty }}</td>
                                <td class="text-end">₱{{ number_format($first['price'], 2) }}</td>
                                <td class="text-end fw-bold">₱{{ number_format($totalPrice, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4"></td>
                                <td class="text-center fw-bold">{{ $specItems->sum('qty') }}</td>
                                <td></td>
                                <td class="text-end fw-bold">₱{{ number_format($groupedBySpec->sum(fn($g) => $g->sum(fn($si) => $si['price'] * $si['qty'])), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Orders for this supplier --}}
            <div class="card-footer bg-white px-0 py-2">
                <div class="px-3">
                    <small class="text-muted fw-medium">
                        <i class="fas fa-file-invoice me-1"></i> Orders:
                    </small>
                    @foreach($supplierOrders as $o)
                    <a href="{{ route('procurement.orders.show', $o->id) }}" class="badge bg-light text-dark text-decoration-none me-1 border">
                        {{ $o->order_number }}
                        <span class="badge bg-{{ $o->statusColor() }} ms-1">{{ $o->statusLabel() }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        {{-- Pagination --}}
        <div class="mb-4">
            {{ $orders->appends(request()->query())->links() }}
        </div>

        {{-- 📊 Completed Orders Review — overpayment/underpayment --}}
        @if(count($discrepantOrders) > 0)
        <div class="card border-0 shadow-sm mb-4 border-start border-4 border-purple" style="border-left-color: #6f42c1 !important;">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold" style="color:#6f42c3">
                    <i class="fas fa-clipboard-check me-1"></i> Completed Orders Review
                    <span class="badge bg-purple ms-2" style="background:#6f42c1">{{ count($discrepantOrders) }}</span>
                </h6>
                <small class="text-muted">Orders na may pagkakaiba ng inorder vs natanggap</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                @if(auth()->user()->isProcurement() || auth()->user()->isAdmin())
                                <th>Supplier</th>
                                @endif
                                <th>Department</th>
                                <th class="text-end">Ordered Cost</th>
                                <th class="text-end">Actual Cost</th>
                                <th class="text-end">Difference</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($discrepantOrders as $d)
                            @php
                                $diffClass = $d['difference'] > 0 ? 'text-danger' : ($d['difference'] < 0 ? 'text-success' : '');
                                $diffIcon = $d['difference'] > 0 ? '⬆️ Overpayment' : ($d['difference'] < 0 ? '⬇️ Underpayment' : '');
                            @endphp
                            <tr>
                                <td><strong>{{ $d['order']->order_number }}</strong></td>
                                @if(auth()->user()->isProcurement() || auth()->user()->isAdmin())
                                <td>{{ $d['order']->supplier?->name ?? '—' }}</td>
                                @endif
                                <td>{{ $d['order']->department?->name ?? '—' }}</td>
                                <td class="text-end">₱{{ number_format($d['ordered_cost'], 2) }}</td>
                                <td class="text-end">₱{{ number_format($d['actual_cost'], 2) }}</td>
                                <td class="text-end fw-bold {{ $diffClass }}">
                                    @if($d['difference'] != 0)
                                    ₱{{ number_format(abs($d['difference']), 2) }}
                                    <br><small>{{ $diffIcon }}</small>
                                    @else
                                    <span class="text-success">✓ Exact</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('procurement.orders.show', $d['order']->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            @php
                                $totalOrderedCost = collect($discrepantOrders)->sum('ordered_cost');
                                $totalActualCost = collect($discrepantOrders)->sum('actual_cost');
                                $totalDiff = $totalOrderedCost - $totalActualCost;
                            @endphp
                            <tr>
                                <td colspan="{{ auth()->user()->isProcurement() || auth()->user()->isAdmin() ? 3 : 2 }}" class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold">₱{{ number_format($totalOrderedCost, 2) }}</td>
                                <td class="text-end fw-bold">₱{{ number_format($totalActualCost, 2) }}</td>
                                <td class="text-end fw-bold {{ $totalDiff > 0 ? 'text-danger' : ($totalDiff < 0 ? 'text-success' : '') }}">
                                    @if($totalDiff != 0)
                                    ₱{{ number_format(abs($totalDiff), 2) }}
                                    <br><small>{{ $totalDiff > 0 ? '⬆️ Total Overpayment' : '⬇️ Total Underpayment' }}</small>
                                    @else
                                    <span class="text-success">✓ Exact</span>
                                    @endif
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-2 text-muted small">
                <i class="fas fa-info-circle me-1"></i>
                Difference = Ordered Cost − Actual Cost. Positive = sobra bayad (overpayment), Negative = kulang bayad (underpayment).
            </div>
        </div>
        @endif

    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-inbox fa-3x mb-3"></i>
            <p>No orders found. <a href="{{ route('procurement.orders.create') }}">Create your first order</a></p>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function copySupplierSummary(slug) {
    const el = document.getElementById('supplier-summary-' + slug);
    if (!el) return;
    
    // Extract text from the table
    const rows = el.querySelectorAll('table tbody tr');
    let text = '=== SUPPLIER ORDER SUMMARY ===\n\n';
    
    el.querySelectorAll('table thead th').forEach((th, i) => {
        if (i > 0) text += th.textContent.trim() + '\t';
    });
    text += '\n' + '='.repeat(60) + '\n';
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        cells.forEach((td, i) => {
            text += td.textContent.trim() + '\t';
        });
        text += '\n';
    });
    
    // Add totals
    const tfoot = el.querySelector('table tfoot');
    if (tfoot) {
        text += '-'.repeat(60) + '\n';
        tfoot.querySelectorAll('td').forEach((td, i) => {
            text += td.textContent.trim() + '\t';
        });
    }
    
    navigator.clipboard.writeText(text).then(() => {
        showToast('Supplier summary copied!', 'success');
    }).catch(() => {
        showToast('Failed to copy. Select and copy manually.', 'warning');
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
</script>
@endpush
