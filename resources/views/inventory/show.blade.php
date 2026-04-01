<x-app-layout>
    @section('page-title', $inventory->name . ' - Inventory Details')
    
    <x-slot name="header">
        <div class="page-header-content">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="inventory-icon-large">
                        @switch($inventory->type)
                            @case('raw_material')
                                <i class="fas fa-industry"></i>
                                @break
                            @case('finished_good')
                                <i class="fas fa-tshirt"></i>
                                @break
                            @case('consumable')
                                <i class="fas fa-tools"></i>
                                @break
                            @case('equipment')
                                <i class="fas fa-cogs"></i>
                                @break
                        @endswitch
                    </div>
                </div>
                <div>
                    <h1 class="page-title">
                        {{ $inventory->name }}
                    </h1>
                    <p class="page-subtitle">
                        <span class="badge bg-{{ $inventory->stock_status_color }} me-2">
                            {{ $inventory->stock_status_text }}
                        </span>
                        @if(!$inventory->is_active)
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    @section('content')
    <div class="page-content">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Left Column: Basic Information -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Basic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">SKU:</th>
                                        <td>
                                            @if($inventory->sku)
                                                <code>{{ $inventory->sku }}</code>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Type:</th>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ ucfirst(str_replace('_', ' ', $inventory->type)) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Category:</th>
                                        <td>{{ $inventory->category }}</td>
                                    </tr>
                                    <tr>
                                        <th>Unit of Measure:</th>
                                        <td>{{ $inventory->unit_of_measure }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Unit Price:</th>
                                        <td class="text-end">₱{{ number_format($inventory->unit_price, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Current Stock:</th>
                                        <td class="text-end">
                                            <strong>{{ number_format($inventory->current_stock, 3) }}</strong>
                                            <div class="text-muted small">{{ $inventory->unit_of_measure }}</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Minimum Stock:</th>
                                        <td class="text-end">
                                            {{ number_format($inventory->minimum_stock, 3) }}
                                            <div class="text-muted small">{{ $inventory->unit_of_measure }}</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Reorder Quantity:</th>
                                        <td class="text-end">
                                            @if($inventory->reorder_quantity)
                                                {{ number_format($inventory->reorder_quantity, 3) }}
                                                <div class="text-muted small">{{ $inventory->unit_of_measure }}</div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        @if($inventory->description)
                            <div class="mt-3">
                                <h6>Description</h6>
                                <p class="mb-0">{{ $inventory->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Stock Adjustment Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exchange-alt text-warning me-2"></i>
                            Adjust Stock
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('inventory.updateStock', $inventory) }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="adjustment_type" class="form-label">Adjustment Type</label>
                                        <select class="form-select" id="adjustment_type" name="adjustment_type" required>
                                            <option value="add">Add Stock</option>
                                            <option value="subtract">Subtract Stock</option>
                                            <option value="set">Set Stock</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity</label>
                                        <input type="number" class="form-control" id="quantity" name="quantity" step="0.001" min="0" required>
                                        <div class="form-text">{{ $inventory->unit_of_measure }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes (Optional)</label>
                                        <input type="text" class="form-control" id="notes" name="notes" placeholder="Reason for adjustment">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-2"></i>Update Stock
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Additional Information & Actions -->
            <div class="col-md-4">
                <!-- Supplier Information -->
                @if($inventory->supplier)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-truck text-success me-2"></i>
                            Supplier Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>{{ $inventory->supplier->name }}</h6>
                        @if($inventory->supplier->contact_person)
                            <p class="mb-1 small">
                                <i class="fas fa-user me-1"></i>
                                {{ $inventory->supplier->contact_person }}
                            </p>
                        @endif
                        @if($inventory->supplier->email)
                            <p class="mb-1 small">
                                <i class="fas fa-envelope me-1"></i>
                                {{ $inventory->supplier->email }}
                            </p>
                        @endif
                        @if($inventory->supplier->phone)
                            <p class="mb-1 small">
                                <i class="fas fa-phone me-1"></i>
                                {{ $inventory->supplier->phone }}
                            </p>
                        @endif
                        @if($inventory->supplier_sku)
                            <p class="mb-0 small">
                                <i class="fas fa-barcode me-1"></i>
                                Supplier SKU: <code>{{ $inventory->supplier_sku }}</code>
                            </p>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Additional Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt text-info me-2"></i>
                            Additional Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            @if($inventory->storage_location)
                                <tr>
                                    <th>Storage Location:</th>
                                    <td>{{ $inventory->storage_location }}</td>
                                </tr>
                            @endif
                            @if($inventory->last_restocked_at)
                                <tr>
                                    <th>Last Restocked:</th>
                                    <td>{{ $inventory->last_restocked_at->format('M d, Y') }}</td>
                                </tr>
                            @endif
                            @if($inventory->expiry_date)
                                <tr>
                                    <th>Expiry Date:</th>
                                    <td>
                                        {{ $inventory->expiry_date->format('M d, Y') }}
                                        @if($inventory->expiry_date->isPast())
                                            <span class="badge bg-danger ms-1">Expired</span>
                                        @elseif($inventory->expiry_date->diffInDays(now()) <= 30)
                                            <span class="badge bg-warning ms-1">Expiring Soon</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th>Created:</th>
                                <td>{{ $inventory->created_at->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td>{{ $inventory->updated_at->format('M d, Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Specifications -->
                @if($inventory->specifications && count($inventory->specifications) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list-alt text-secondary me-2"></i>
                            Specifications
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            @foreach($inventory->specifications as $key => $value)
                                <tr>
                                    <th width="40%">{{ $key }}:</th>
                                    <td>{{ $value }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cogs text-dark me-2"></i>
                            Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('inventory.edit', $inventory) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Edit Item
                            </a>
                            <form action="{{ route('inventory.destroy', $inventory) }}" method="POST" class="d-grid">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this item?')">
                                    <i class="fas fa-trash me-2"></i>Delete Item
                                </button>
                            </form>
                            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Inventory
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @push('styles')
    <style>
        .inventory-icon-large {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }
        
        .table th {
            font-weight: 600;
            color: #495057;
        }
        
        .table td {
            vertical-align: middle;
        }
    </style>
    @endpush
</x-app-layout>