@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="page-title">Procurement Dashboard</h1>
                <p class="page-subtitle">Compilation of all department orders</p>
            </div>
            <a href="{{ route('procurement.orders.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Order
            </a>
        </div>
    </div>
</div>

<div class="container">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0 fw-bold text-primary">{{ $stats['total'] ?? 0 }}</h3>
                    <small class="text-muted">Total Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0 fw-bold text-warning">{{ $stats['pending'] ?? 0 }}</h3>
                    <small class="text-muted">Pending (Draft + For Approval)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #0dcaf0 !important;">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0 fw-bold text-info">{{ $stats['in_progress'] ?? 0 }}</h3>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #198754 !important;">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0 fw-bold text-success">{{ $stats['completed'] ?? 0 }}</h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('procurement.orders.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-medium">Department</label>
                    <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-medium">Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="for_approval" {{ request('status') == 'for_approval' ? 'selected' : '' }}>For Approval</option>
                        <option value="for_procurement" {{ request('status') == 'for_procurement' ? 'selected' : '' }}>For Procurement</option>
                        <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Ordered</option>
                        <option value="for_delivery" {{ request('status') == 'for_delivery' ? 'selected' : '' }}>For Delivery</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partially Received</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-medium">Search Order #</label>
                    <input type="text" name="search" class="form-control form-control-sm" 
                           placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Compiled Items by Category --}}
    @if(count($compiledItems) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                <i class="fas fa-list-alt me-1"></i> Compiled Items Across Orders
            </h6>
            <span class="badge bg-secondary">{{ collect($compiledItems)->sum('total_qty') }} total items</span>
        </div>
        <div class="card-body p-0">
            @foreach($compiledItems as $category => $catItems)
            <div class="border-bottom">
                <div class="px-3 py-2 bg-light d-flex justify-content-between align-items-center"
                     onclick="$(this).next().toggle()" style="cursor:pointer;">
                    <strong>
                        <i class="fas fa-folder me-1"></i> {{ $category }}
                    </strong>
                    <span class="badge bg-primary rounded-pill">{{ $catItems['total_qty'] }} pcs</span>
                </div>
                <div class="px-3 py-2" style="display:{{ $loop->first ? 'block' : 'none' }};">
                    <table class="table table-sm table-borderless mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Brand</th>
                                <th>Size</th>
                                <th>Color</th>
                                <th class="text-center">Total Ordered</th>
                                <th>Departments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($catItems['items'] as $compiled)
                            <tr>
                                <td><strong>{{ $compiled['name'] }}</strong></td>
                                <td><span class="badge bg-secondary">{{ $compiled['brand'] }}</span></td>
                                <td>{{ $compiled['size'] }}</td>
                                <td><span class="badge" style="background:{{ $compiled['color'] == 'RED' ? '#dc3545' : ($compiled['color'] == 'BLUE' ? '#0d6efd' : ($compiled['color'] == 'BLACK' ? '#212529' : ($compiled['color'] == 'WHITE' ? '#6c757d' : '#6c757d'))) }}; color:#fff;">{{ $compiled['color'] }}</span></td>
                                <td class="text-center fw-bold">{{ $compiled['total_qty'] }}</td>
                                <td>
                                    @foreach($compiled['departments'] as $d)
                                        <span class="badge bg-info me-1">{{ $d['name'] }}: {{ $d['qty'] }}</span>
                                    @endforeach
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Order List --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                <i class="fas fa-file-invoice me-1"></i> Orders
                <span class="badge bg-secondary ms-1">{{ $orders->total() }}</span>
            </h6>
        </div>
        <div class="card-body p-0">
            @if($orders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Department</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>{{ $order->department?->name ?? 'General' }}</td>
                            <td>{{ $order->items->count() }} items</td>
                            <td><span class="badge bg-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span></td>
                            <td>{{ $order->creator?->name ?? 'Unknown' }}</td>
                            <td><small class="text-muted">{{ $order->created_at->format('M d, Y h:i A') }}</small></td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="viewOrder({{ $order->id }}); return false;">
                                            <i class="fas fa-eye me-1"></i> View Details
                                        </a></li>
                                        @if($order->status === 'draft')
                                        <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $order->id }}, 'for_approval'); return false;">
                                            <i class="fas fa-paper-plane me-1"></i> Submit for Approval
                                        </a></li>
                                        @endif
                                        @if($order->status === 'for_approval')
                                        <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $order->id }}, 'for_procurement'); return false;">
                                            <i class="fas fa-check-circle me-1"></i> Approve
                                        </a></li>
                                        @endif
                                        @if($order->status === 'for_procurement')
                                        <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $order->id }}, 'ordered'); return false;">
                                            <i class="fas fa-shopping-cart me-1"></i> Mark as Ordered
                                        </a></li>
                                        @endif
                                        @if(in_array($order->status, ['ordered', 'for_delivery']))
                                        <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $order->id }}, 'completed'); return false;">
                                            <i class="fas fa-check-double me-1"></i> Mark as Received
                                        </a></li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus({{ $order->id }}, 'cancelled'); return false;">
                                            <i class="fas fa-times me-1"></i> Cancel Order
                                        </a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-3">
                {{ $orders->appends(request()->query())->links() }}
            </div>
            @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <p>No orders found. <a href="{{ route('procurement.orders.create') }}">Create your first order</a></p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Order Detail Modal --}}
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewOrder(id) {
    const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
    const body = document.getElementById('orderDetailBody');
    body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading...</p></div>';
    modal.show();

    fetch('/procurement/orders/' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const o = data.order;
                body.innerHTML = `
                    <div class="mb-3 pb-2 border-bottom d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1">${escHtml(o.order_number)}</h5>
                            <p class="mb-0 text-muted small">${o.department ? escHtml(o.department.name) : 'General'} &middot; ${o.creator ? escHtml(o.creator.name) : 'Unknown'}</p>
                        </div>
                        <span class="badge bg-${statusColor(o.status)}">${statusLabel(o.status)}</span>
                    </div>
                    ${o.notes ? `<p class="small"><strong>Notes:</strong> ${escHtml(o.notes)}</p>` : ''}
                    <h6 class="fw-bold mt-3 mb-2">Items</h6>
                    <table class="table table-sm">
                        <thead><tr><th>Item</th><th>SKU</th><th class="text-center">Qty</th><th class="text-end">Price</th></tr></thead>
                        <tbody>
                            ${o.items.map(item => `
                                <tr>
                                    <td>${escHtml(item.item_name)}</td>
                                    <td><small class="text-muted">${item.sku || '-'}</small></td>
                                    <td class="text-center">${item.quantity_ordered || item.quantity}</td>
                                    <td class="text-end">${item.unit_price ? '₱' + parseFloat(item.unit_price).toFixed(2) : '-'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            } else {
                body.innerHTML = '<div class="alert alert-danger">Failed to load order.</div>';
            }
        })
        .catch(() => {
            body.innerHTML = '<div class="alert alert-danger">Failed to load order details.</div>';
        });
}

function updateStatus(id, status) {
    const labels = {
        'draft': 'Draft', 'for_approval': 'For Approval', 'for_procurement': 'For Procurement',
        'ordered': 'Ordered', 'for_delivery': 'For Delivery', 'partial': 'Partially Received',
        'completed': 'Completed', 'cancelled': 'Cancelled'
    };
    const confirmMsg = status === 'cancelled' 
        ? 'Are you sure you want to cancel this order?' 
        : 'Mark this order as "' + (labels[status] || status) + '"?';
    
    if (!confirm(confirmMsg)) return;

    fetch('/procurement/orders/' + id + '/status', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ status: status })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Status updated!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Failed to update status.', 'danger');
        }
    })
    .catch(() => {
        showToast('Network error', 'danger');
    });
}

function statusColor(s) {
    const map = {'draft':'secondary','for_approval':'warning','for_procurement':'info','ordered':'primary','for_delivery':'success','partial':'warning','completed':'success','cancelled':'danger'};
    return map[s] || 'secondary';
}

function statusLabel(s) {
    const map = {'draft':'Draft','for_approval':'For Approval','for_procurement':'For Procurement','ordered':'Ordered','for_delivery':'For Delivery','partial':'Partially Received','completed':'Completed','cancelled':'Cancelled'};
    return map[s] || s;
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}

function showToast(msg, type) {
    // Create toast if it doesn't exist
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-bg-' + (type || 'success') + ' border-0 show';
    toast.innerHTML = '<div class="d-flex"><div class="toast-body">' + escHtml(msg) + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    container.appendChild(toast);
    setTimeout(() => { toast.remove(); }, 4000);
}
</script>
@endpush
