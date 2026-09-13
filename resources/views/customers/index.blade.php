@extends('layouts.app')

@section('page-title', 'Customers')

@section('content')
<div class="page-content customers-page">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-users"></i>
                Customers
            </h1>
            <p class="page-subtitle">Manage your customer database — track orders, lifetime value, and transactions</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="customerSearch" class="form-control" placeholder="Search by name, phone, email, or ID number..." autocomplete="off">
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-outline-secondary me-2" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                        <i class="fas fa-filter me-1"></i> Filters
                    </button>
                    <button class="btn btn-primary" onclick="window.location.href='{{ route('sales.prototype.create') }}'">
                        <i class="fas fa-plus-circle me-1"></i> New Sale
                    </button>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="collapse mb-3" id="filterPanel">
                <div class="card card-body bg-light">
                    <div class="row g-2">
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Tier</label>
                            <select id="filterTier" class="form-select form-select-sm">
                                <option value="">All Tiers</option>
                                <option value="bronze">Bronze</option>
                                <option value="silver">Silver</option>
                                <option value="gold">Gold</option>
                                <option value="platinum">Platinum</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Min Spent (₱)</label>
                            <input type="number" id="filterMinSpent" class="form-control form-control-sm" placeholder="0" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Max Spent (₱)</label>
                            <input type="number" id="filterMaxSpent" class="form-control form-control-sm" placeholder="999999" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Outstanding Balance</label>
                            <select id="filterOutstanding" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="has">May Balance</option>
                                <option value="none">Fully Paid</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Marketplace</label>
                            <select id="filterMarketplace" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="shopee">Shopee</option>
                                <option value="lazada">Lazada</option>
                                <option value="facebook">Facebook</option>
                                <option value="tiktok">TikTok</option>
                                <option value="direct">Direct</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Date From</label>
                            <input type="date" id="filterDateFrom" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Date To</label>
                            <input type="date" id="filterDateTo" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col text-end">
                            <button class="btn btn-sm btn-outline-danger" id="resetFiltersBtn">
                                <i class="fas fa-undo me-1"></i> Reset Filters
                            </button>
                            <button class="btn btn-sm btn-primary" id="applyFiltersBtn">
                                <i class="fas fa-search me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="customerTable">
                    <thead class="table-light">
                        <tr>
                            <th>Customer ID</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Orders</th>
                            <th>Total Spent</th>
                            <th>Outstanding</th>
                            <th>Tier</th>
                            <th>Last Order</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr class="customer-row" data-id="{{ $customer->id }}">
                            <td data-label="Customer ID"><code>{{ $customer->customer_id_number }}</code></td>
                            <td data-label="Name">
                                <strong>{{ $customer->name }}</strong>
                                @if($customer->company)
                                    <br><small class="text-muted">{{ $customer->company }}</small>
                                @endif
                            </td>
                            <td data-label="Contact">
                                @if($customer->phone)<small><i class="fas fa-phone me-1"></i>{{ $customer->phone }}</small><br>@endif
                                @if($customer->email)<small><i class="fas fa-envelope me-1"></i>{{ $customer->email }}</small>@endif
                            </td>
                            <td class="text-center" data-label="Orders">{{ $customer->total_orders }}</td>
                            <td class="text-end" data-label="Total Spent">₱{{ number_format($customer->total_spent, 2) }}</td>
                            <td class="text-end" data-label="Outstanding">
                                @php $custOut = (float) ($outstanding[$customer->id] ?? 0); @endphp
                                @if($custOut > 0)
                                    <span class="badge bg-danger">₱{{ number_format($custOut, 2) }}</span>
                                @else
                                    <span class="badge bg-success">₱0.00</span>
                                @endif
                            </td>
                            <td data-label="Tier">
                                @php
                                    $tierColors = ['bronze' => '#cd7f32', 'silver' => '#a8a8a8', 'gold' => '#ffd700', 'platinum' => '#e5e4e2'];
                                    $tierColor = $tierColors[$customer->customer_tier] ?? '#cd7f32';
                                @endphp
                                <span class="badge" style="background: {{ $tierColor }}; color: #000;">
                                    {{ ucfirst($customer->customer_tier) }}
                                </span>
                            </td>
                            <td data-label="Last Order"><small>{{ $customer->last_order_date ? $customer->last_order_date->format('M d, Y') : 'N/A' }}</small></td>
                            <td data-label="Created By"><small>{{ $customer->creator ? $customer->creator->display_label : '—' }}</small></td>
                            <td data-label="Actions">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('sales.prototype.create', ['customer_id' => $customer->id]) }}" class="btn btn-outline-success" title="Add Sale">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                No customers yet.<br>
                                <a href="{{ route('sales.prototype.create') }}" class="btn btn-sm btn-primary mt-2">Create your first sale</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>

<!-- View Customer Modal -->
<div class="modal fade" id="viewCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user me-2"></i>Customer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="customerDetailBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading customer details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ---- Customers LIST: MOBILE view (additive — desktop/tablet >=768px untouched) ---- */
@media (max-width: 767.98px) {
    /* Reclaim the stacked page paddings so the card uses the full phone width */
    .content-area:has(.customers-page),
    .page-content:has(> .customers-page) { padding-left: 8px; padding-right: 8px; }
    .customers-page { padding-left: 0; padding-right: 0; }
    .customers-page .card-body { padding: 12px 10px; }

    /* Header actions: stack + full-width buttons */
    .customers-page .row.mb-3 > .col-md-6 { margin-bottom: .5rem; }
    .customers-page .row.mb-3 > .col-md-6.text-end { text-align: left !important; display: flex; gap: .5rem; }
    .customers-page .row.mb-3 > .col-md-6.text-end .btn { flex: 1 1 0; }
    .customers-page .input-group { width: 100%; }

    /* Table -> compact 2-column cards (labels from data-label) */
    .customers-page #customerTable thead { display: none; }
    .customers-page #customerTable,
    .customers-page #customerTable tbody { display: block; width: 100%; border: 0; }
    .customers-page #customerTable tr.customer-row {
        display: grid; grid-template-columns: repeat(12, 1fr); gap: .5rem .6rem;
        border: 1px solid #e3e6ea; border-radius: 12px; background: #fff;
        padding: .65rem .75rem; margin: 0 0 .7rem;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .05);
    }
    .customers-page #customerTable tr.customer-row > td {
        display: block; border: 0; padding: 0; margin: 0; text-align: left;
        overflow-wrap: anywhere;
    }
    .customers-page #customerTable tr.customer-row > td::before {
        content: attr(data-label); display: block;
        font-size: 10px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
        color: #8a94a6; margin-bottom: 1px;
    }
    .customers-page #customerTable tr.customer-row > td[data-label="Name"] { grid-row: 1; grid-column: 1 / span 8; }
    .customers-page #customerTable tr.customer-row > td[data-label="Tier"] { grid-row: 1; grid-column: 9 / span 4; text-align: right; }
    .customers-page #customerTable tr.customer-row > td[data-label="Tier"]::before { text-align: right; }
    .customers-page #customerTable tr.customer-row > td[data-label="Customer ID"] { grid-row: 2; grid-column: 1 / span 6; }
    .customers-page #customerTable tr.customer-row > td[data-label="Orders"] { grid-row: 2; grid-column: 7 / span 6; }
    .customers-page #customerTable tr.customer-row > td[data-label="Total Spent"] { grid-row: 3; grid-column: 1 / span 6; }
    .customers-page #customerTable tr.customer-row > td[data-label="Outstanding"] { grid-row: 3; grid-column: 7 / span 6; }
    .customers-page #customerTable tr.customer-row > td[data-label="Last Order"] { grid-row: 4; grid-column: 1 / span 6; }
    .customers-page #customerTable tr.customer-row > td[data-label="Created By"] { grid-row: 4; grid-column: 7 / span 6; }
    .customers-page #customerTable tr.customer-row > td[data-label="Contact"] { grid-row: 5; grid-column: 1 / span 12; }
    .customers-page #customerTable tr.customer-row > td[data-label="Actions"] { grid-row: 6; grid-column: 1 / span 12; }
    .customers-page #customerTable tr.customer-row > td[data-label="Name"] strong { font-size: 15px; }
    .customers-page #customerTable tr.customer-row > td[data-label="Actions"] .btn-group { width: 100%; }
    .customers-page #customerTable tr.customer-row > td[data-label="Actions"] .btn-group .btn { flex: 1 1 0; }

    /* Empty / status rows */
    .customers-page #customerTable tbody tr:not(.customer-row) { display: block; }
    .customers-page #customerTable tbody tr:not(.customer-row) td { display: block; width: 100%; border: 0; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const $ = function(selector) {
        if (selector.startsWith('#')) {
            return document.getElementById(selector.slice(1));
        }
        return document.querySelector(selector);
    };
    $.val = function(el) { return el ? el.value : ''; };
    $.get = function(url, params, cb) {
        const qs = Object.keys(params).filter(k => params[k]).map(k => encodeURIComponent(k) + '=' + encodeURIComponent(params[k])).join('&');
        fetch(url + (qs ? '?' + qs : ''))
            .then(r => r.json())
            .then(cb)
            .catch(function() { renderTable([]); });
    };

    function getFilterParams() {
        return {
            q: $('#customerSearch').value,
            tier: $('#filterTier').value,
            min_spent: $('#filterMinSpent').value,
            max_spent: $('#filterMaxSpent').value,
            marketplace: $('#filterMarketplace').value,
            balance: $('#filterOutstanding').value,
            date_from: $('#filterDateFrom').value,
            date_to: $('#filterDateTo').value
        };
    }

    function fetchCustomers() {
        const params = getFilterParams();
        const hasFilters = params.tier || params.min_spent || params.max_spent || params.marketplace || params.balance || params.date_from || params.date_to;
        const hasSearch = params.q && params.q.length >= 2;

        if (!hasSearch && !hasFilters) {
            window.location.reload();
            return;
        }

        $.get('/api/customers/search', params, function(res) {
            renderTable(res.customers);
        });
    }

    let searchTimeout;
    $('#customerSearch').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(fetchCustomers, 400);
    });

    $('#applyFiltersBtn').addEventListener('click', fetchCustomers);

    $('#resetFiltersBtn').addEventListener('click', function() {
        $('#filterTier').value = '';
        $('#filterMinSpent').value = '';
        $('#filterMaxSpent').value = '';
        $('#filterMarketplace').value = '';
        $('#filterOutstanding').value = '';
        $('#filterDateFrom').value = '';
        $('#filterDateTo').value = '';
        $('#customerSearch').value = '';
        window.location.reload();
    });

    $('#customerSearch').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') fetchCustomers();
    });

    function renderTable(customers) {
        const tbody = document.querySelector('#customerTable tbody');
        tbody.innerHTML = '';

        if (!customers || customers.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No customers found.</td></tr>';
            return;
        }

        const tierColors = { bronze: '#cd7f32', silver: '#a8a8a8', gold: '#ffd700', platinum: '#e5e4e2' };

        customers.forEach(function(c) {
            const tierColor = tierColors[c.customer_tier] || '#cd7f32';
            const lastOrder = c.last_order_date ? new Date(c.last_order_date).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';

            tbody.innerHTML += `
                <tr class="customer-row" data-id="${c.id}">
                    <td data-label="Customer ID"><code>${c.customer_id_number || '—'}</code></td>
                    <td data-label="Name">
                        <strong>${c.name}</strong>
                        ${c.company ? '<br><small class="text-muted">' + c.company + '</small>' : ''}
                    </td>
                    <td data-label="Contact">
                        ${c.phone ? '<small><i class="fas fa-phone me-1"></i>' + c.phone + '</small><br>' : ''}
                        ${c.email ? '<small><i class="fas fa-envelope me-1"></i>' + c.email + '</small>' : ''}
                    </td>
                    <td class="text-center" data-label="Orders">${c.total_orders}</td>
                    <td class="text-end" data-label="Total Spent">₱${parseFloat(c.total_spent).toFixed(2)}</td>
                    <td class="text-end" data-label="Outstanding">${parseFloat(c.outstanding_balance || 0) > 0
                        ? '<span class="badge bg-danger">₱' + parseFloat(c.outstanding_balance).toFixed(2) + '</span>'
                        : '<span class="badge bg-success">₱0.00</span>'}</td>
                    <td data-label="Tier"><span class="badge" style="background: ${tierColor}; color: #000;">${c.customer_tier.charAt(0).toUpperCase() + c.customer_tier.slice(1)}</span></td>
                    <td data-label="Last Order"><small>${lastOrder}</small></td>
                    <td data-label="Created By"><small>${c.creator ? c.creator.name : '—'}</small></td>
                    <td data-label="Actions">
                        <div class="btn-group btn-group-sm">
                            <a href="/customers/${c.id}" class="btn btn-outline-primary" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/sales/prototype/create?customer_id=${c.id}" class="btn btn-outline-success" title="Add Sale">
                                <i class="fas fa-cart-plus"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
});
</script>
@endpush
