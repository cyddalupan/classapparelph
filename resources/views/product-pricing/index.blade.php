@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="fas fa-tags me-2"></i>Product Pricing Management
            </h1>
            <p class="text-muted">Manage pricing for all products across different user tiers</p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
                    <i class="fas fa-edit me-1"></i> Bulk Update
                </button>
                <a href="{{ route('master-items.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-boxes me-1"></i> Master Items
                </a>
                <a href="{{ route('printing.public') }}" class="btn btn-outline-success">
                    <i class="fas fa-calculator me-1"></i> Pricing Calculator
                </a>
                <a href="{{ route('pricing.rules') }}" class="btn btn-outline-info">
                    <i class="fas fa-sliders-h me-1"></i> Pricing Rules
                </a>
            </div>
        </div>
    </div>

    <!-- Dashboard Stats -->
    <div class="row mb-4">
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Items</h6>
                            <h4 class="mb-0">{{ $stats['total_items'] }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary rounded-circle">
                                <i class="fas fa-boxes"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Avg. Margin</h6>
                            <h4 class="mb-0">{{ $stats['avg_margin'] }}%</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-success rounded-circle">
                                <i class="fas fa-percentage"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Profit Potential</h6>
                            <h4 class="mb-0">₱{{ number_format($stats['profit_potential'], 2) }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-info rounded-circle">
                                <i class="fas fa-chart-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Low Margin</h6>
                            <h4 class="mb-0">{{ $stats['low_margin_alerts'] }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-warning rounded-circle">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-start border-secondary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Recently Updated</h6>
                            <h4 class="mb-0">{{ $stats['recently_updated'] }}</h4>
                            <small class="text-muted">Last 24 hours</small>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-secondary rounded-circle">
                                <i class="fas fa-history"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('product-pricing.index') }}" class="row g-3">
                <!-- First Row of Filters -->
                <div class="col-md-2">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="price_tier" class="form-label">Price Tier</label>
                    <select class="form-select" id="price_tier" name="price_tier">
                        <option value="supplier_cost" {{ $priceTier == 'supplier_cost' ? 'selected' : '' }}>Supplier Cost</option>
                        <option value="sales_team" {{ $priceTier == 'sales_team' ? 'selected' : '' }}>Sales Team</option>
                        <option value="agent_cost" {{ $priceTier == 'agent_cost' ? 'selected' : '' }}>Agent Cost</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="brand" class="form-label">Brand</label>
                    <select class="form-select" id="brand" name="brand">
                        <option value="">All Brands</option>
                        @foreach($brands as $brandOption)
                            <option value="{{ $brandOption }}" {{ $brand == $brandOption ? 'selected' : '' }}>{{ $brandOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="shirt_type" class="form-label">Type</label>
                    <select class="form-select" id="shirt_type" name="shirt_type">
                        <option value="">All Types</option>
                        @foreach($shirtTypes as $typeOption)
                            <option value="{{ $typeOption }}" {{ $shirtType == $typeOption ? 'selected' : '' }}>{{ $typeOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="color" class="form-label">Color</label>
                    <select class="form-select" id="color" name="color">
                        <option value="">All Colors</option>
                        @foreach($colors as $colorOption)
                            <option value="{{ $colorOption }}" {{ $color == $colorOption ? 'selected' : '' }}>{{ $colorOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ $search }}" placeholder="Name, SKU...">
                </div>
                
                <!-- Second Row: Action Buttons -->
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Apply Filters
                            </button>
                            <a href="{{ route('product-pricing.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i> Filter by Brand, Type, Color to find products faster
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Pricing Table -->
    <div class="card">
        <div class="card-body">
            @if($items->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h4>No products found</h4>
                    <p class="text-muted">Try adjusting your filters or add products to the master list.</p>
                    <a href="{{ route('master-items.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Products
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                    </div>
                                </th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>SKU</th>
                                <th>Supplier Cost</th>
                                <th>Sales Team Price</th>
                                <th>Agent Cost</th>
                                <th>Agent Markup</th>
                                <th>Volume Discounts</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                @php
                                    $supplierPricing = $item->getPricingForTier('supplier_cost');
                                    $salesPricing = $item->getPricingForTier('sales_team');
                                    $agentPricing = $item->getPricingForTier('agent_cost');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input item-checkbox" type="checkbox" 
                                                   value="{{ $item->id }}" data-item-id="{{ $item->id }}">
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $item->name }}</strong>
                                        @if($item->description)
                                            <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->category }}</td>
                                    <td><code>{{ $item->sku ?? 'N/A' }}</code></td>
                                    <td>
                                        <span class="badge bg-light text-dark fs-6">
                                            ₱{{ number_format($supplierPricing->final_price ?? $item->unit_price ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($salesPricing)
                                            <span class="badge bg-info text-white fs-6">
                                                ₱{{ number_format($salesPricing->final_price, 2) }}
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                +{{ number_format($salesPricing->markup_percentage ?? 0, 1) }}%
                                            </small>
                                        @else
                                            <span class="badge bg-light text-muted">Not set</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($agentPricing)
                                            <span class="badge bg-warning text-dark fs-6">
                                                ₱{{ number_format($agentPricing->base_price, 2) }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted">Not set</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($agentPricing && $agentPricing->markup_percentage)
                                            <span class="badge bg-success text-white">
                                                {{ number_format($agentPricing->markup_percentage, 1) }}%
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                ₱{{ number_format($agentPricing->markup_amount, 2) }}
                                            </small>
                                        @else
                                            <span class="badge bg-light text-muted">0%</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->volume_discounts_display)
                                            <div class="small text-muted">
                                                <i class="fas fa-layer-group text-info me-1"></i>
                                                {{ $item->volume_discounts_display }}
                                            </div>
                                        @else
                                            <span class="badge bg-light text-muted">None</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('product-pricing.edit', $item->id) }}" 
                                               class="btn btn-outline-primary" title="Edit Pricing">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('master-items.edit', $item->id) }}" 
                                               class="btn btn-outline-secondary" title="Edit Product">
                                                <i class="fas fa-cog"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} items
                    </div>
                    <div>
                        {{ $items->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1" aria-labelledby="bulkUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkUpdateModalLabel">
                    <i class="fas fa-edit me-2"></i>Bulk Update Pricing
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Selected Items:</strong> <span id="selectedCount">0</span> items
                </div>
                
                <form id="bulkUpdateForm">
                    @csrf
                    <input type="hidden" name="item_ids" id="bulkItemIds">
                    
                    <div class="mb-3">
                        <label for="bulkPriceTier" class="form-label">Price Tier</label>
                        <select class="form-select" id="bulkPriceTier" name="price_tier" required>
                            <option value="supplier_cost">Supplier Cost (Your Cost)</option>
                            <option value="sales_team">Sales Team Price</option>
                            <option value="agent_cost">Agent Cost</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bulkField" class="form-label">Field to Update</label>
                        <select class="form-select" id="bulkField" name="field" required>
                            <option value="supplier_cost">Supplier Cost Amount</option>
                            <option value="sales_team_price">Sales Team Price</option>
                            <option value="agent_markup_percentage">Agent Markup Percentage</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bulkValue" class="form-label">New Value</label>
                        <div class="input-group">
                            <span class="input-group-text" id="valuePrefix">₱</span>
                            <input type="number" class="form-control" id="bulkValue" name="value" 
                                   step="0.01" min="0" required>
                            <span class="input-group-text" id="valueSuffix"></span>
                        </div>
                        <div class="form-text" id="valueHelp">Enter the new value for selected items</div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This will update all selected items. This action cannot be undone.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmBulkUpdate">
                    <i class="fas fa-save me-1"></i> Update Selected Items
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let selectedItems = [];
    
    // Select all checkbox
    $('#selectAll').change(function() {
        $('.item-checkbox').prop('checked', this.checked);
        updateSelectedItems();
    });
    
    // Individual checkbox
    $('.item-checkbox').change(function() {
        updateSelectedItems();
        $('#selectAll').prop('checked', 
            $('.item-checkbox:checked').length === $('.item-checkbox').length
        );
    });
    
    // Update selected items array
    function updateSelectedItems() {
        selectedItems = [];
        $('.item-checkbox:checked').each(function() {
            selectedItems.push($(this).val());
        });
        $('#selectedCount').text(selectedItems.length);
        $('#bulkItemIds').val(JSON.stringify(selectedItems));
    }
    
    // Update field prefix/suffix based on selection
    $('#bulkField').change(function() {
        const field = $(this).val();
        const prefix = $('#valuePrefix');
        const suffix = $('#valueSuffix');
        const help = $('#valueHelp');
        
        if (field === 'agent_markup_percentage') {
            prefix.text('');
            suffix.text('%');
            help.text('Enter markup percentage (e.g., 30 for 30%)');
        } else {
            prefix.text('₱');
            suffix.text('');
            help.text('Enter the new price amount');
        }
    });
    
    // Bulk update form submission
    $('#confirmBulkUpdate').click(function() {
        if (selectedItems.length === 0) {
            alert('Please select at least one item to update.');
            return;
        }
        
        const formData = $('#bulkUpdateForm').serialize();
        
        $.ajax({
            url: '{{ route("product-pricing.bulk-update") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#bulkUpdateModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
            }
        });
    });
    
    // Initialize
    updateSelectedItems();
});
</script>
@endpush
@endsection