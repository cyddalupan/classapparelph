@extends('layouts.app')

@section('page-title', 'Customers')

@section('content')
<div class="page-content">
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
                        <input type="text" id="customerSearch" class="form-control" placeholder="Search by name, phone, email, or ID number...">
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-primary" onclick="window.location.href='{{ route('sales.prototype.create') }}'">
                        <i class="fas fa-plus-circle me-1"></i> New Sale
                    </button>
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
                            <th>Tier</th>
                            <th>Last Order</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr class="customer-row" data-id="{{ $customer->id }}">
                            <td><code>{{ $customer->customer_id_number }}</code></td>
                            <td>
                                <strong>{{ $customer->name }}</strong>
                                @if($customer->company)
                                    <br><small class="text-muted">{{ $customer->company }}</small>
                                @endif
                            </td>
                            <td>
                                @if($customer->phone)<small><i class="fas fa-phone me-1"></i>{{ $customer->phone }}</small><br>@endif
                                @if($customer->email)<small><i class="fas fa-envelope me-1"></i>{{ $customer->email }}</small>@endif
                            </td>
                            <td class="text-center">{{ $customer->total_orders }}</td>
                            <td class="text-end">₱{{ number_format($customer->total_spent, 2) }}</td>
                            <td>
                                @php
                                    $tierColors = ['bronze' => '#cd7f32', 'silver' => '#a8a8a8', 'gold' => '#ffd700', 'platinum' => '#e5e4e2'];
                                    $tierColor = $tierColors[$customer->customer_tier] ?? '#cd7f32';
                                @endphp
                                <span class="badge" style="background: {{ $tierColor }}; color: #000;">
                                    {{ ucfirst($customer->customer_tier) }}
                                </span>
                            </td>
                            <td><small>{{ $customer->last_order_date ? $customer->last_order_date->format('M d, Y') : 'N/A' }}</small></td>
                            <td><small>{{ $customer->creator ? $customer->creator->name : '—' }}</small></td>
                            <td>
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
                            <td colspan="9" class="text-center py-5 text-muted">
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

@push('scripts')
<script>
$(document).ready(function() {
    let searchTimeout;
    $('#customerSearch').on('input', function() {
        clearTimeout(searchTimeout);
        const q = $(this).val();
        searchTimeout = setTimeout(function() {
            if (q.length >= 2) {
                $.get('/api/customers/search', { q: q }, function(res) {
                    renderTable(res.customers);
                });
            } else if (q.length === 0) {
                window.location.reload();
            }
        }, 400);
    });

    function renderTable(customers) {
        const tbody = $('#customerTable tbody');
        tbody.empty();

        if (!customers || customers.length === 0) {
            tbody.html('<tr><td colspan="9" class="text-center py-4 text-muted">No customers found.</td></tr>');
            return;
        }

        const tierColors = { bronze: '#cd7f32', silver: '#a8a8a8', gold: '#ffd700', platinum: '#e5e4e2' };

        customers.forEach(function(c) {
            const tierColor = tierColors[c.customer_tier] || '#cd7f32';
            const lastOrder = c.last_order_date ? new Date(c.last_order_date).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';

            tbody.append(`
                <tr class="customer-row" data-id="${c.id}">
                    <td><code>${c.customer_id_number || '—'}</code></td>
                    <td>
                        <strong>${c.name}</strong>
                        ${c.company ? '<br><small class="text-muted">' + c.company + '</small>' : ''}
                    </td>
                    <td>
                        ${c.phone ? '<small><i class="fas fa-phone me-1"></i>' + c.phone + '</small><br>' : ''}
                        ${c.email ? '<small><i class="fas fa-envelope me-1"></i>' + c.email + '</small>' : ''}
                    </td>
                    <td class="text-center">${c.total_orders}</td>
                    <td class="text-end">₱${parseFloat(c.total_spent).toFixed(2)}</td>
                    <td><span class="badge" style="background: ${tierColor}; color: #000;">${c.customer_tier.charAt(0).toUpperCase() + c.customer_tier.slice(1)}</span></td>
                    <td><small>${lastOrder}</small></td>
                    <td><small>${c.creator ? c.creator.name : '—'}</small></td>
                    <td>
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
            `);
        });
    }
});
</script>
@endpush
