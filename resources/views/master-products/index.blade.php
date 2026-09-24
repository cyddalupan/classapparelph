@extends('layouts.app')

@section('content')
@php
    $categoryBoxes = [
        ['key' => 'shirt',         'label' => 'Shirt Products',           'cat' => 'Shirt Products',                'icon' => 'fa-tshirt',   'color' => 'primary', 'desc' => 'T-shirts, polo, hoodies'],
        ['key' => 'uncategorized', 'label' => 'Other Products',           'cat' => 'Other Products',                'icon' => 'fa-gift',     'color' => 'info',    'desc' => 'Mugs, totebags, lanyards'],
        ['key' => 'machines',      'label' => 'Machines & Equipment',     'cat' => 'Machine and Equipments',        'icon' => 'fa-tools',    'color' => 'danger',  'desc' => 'Tools, machines, equipment'],
        ['key' => 'materials',     'label' => 'Garment Materials',        'cat' => 'Garment Materials',             'icon' => 'fa-cut',      'color' => 'success', 'desc' => 'Fabrics, threads, accessories'],
        ['key' => 'printing',      'label' => 'Printing & Office',        'cat' => 'Printing and Office Supplies',  'icon' => 'fa-print',    'color' => 'warning', 'desc' => 'Ink, paper, office supplies'],
    ];
@endphp
<style>
    .mp-cat-box { cursor: pointer; transition: all .2s ease; border: 2px solid transparent; height: 100%; }
    .mp-cat-box:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,.08); border-color: #3a86ff; }
    .mp-cat-box.selected { border-color: #3a86ff; background: rgba(58,134,255,.05); }
    .mp-stat .avatar-title { width: 40px; height: 40px; display:flex; align-items:center; justify-content:center; color:#fff; }
    .mp-table td { vertical-align: middle; }
</style>

<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0"><i class="fas fa-boxes-stacked me-2"></i>Master Products &amp; Pricing</h1>
            <p class="text-muted mb-0">Catalog + per-tier pricing + Sales Box assignment — in one place</p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="{{ route('master-items.create') }}{{ $category ? '?category='.urlencode($category) : '' }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add New Product
                </a>
                <a href="{{ route('product-pricing.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-tags me-1"></i> Old Pricing Page
                </a>
            </div>
        </div>
    </div>

    <div class="alert alert-light border d-flex align-items-center">
        <i class="fas fa-circle-info me-2"></i>
        <div><strong>Bukas na lahat dito:</strong> pwede nang i-edit ang details, pricing, Sales Box, at volume discounts mula sa isang pahina lang.</div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-start border-primary border-4 mp-stat">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted mb-1">Total Items</h6><h4 class="mb-0">{{ $stats['total_items'] }}</h4></div>
                    <span class="avatar-title bg-primary rounded-circle"><i class="fas fa-boxes"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-start border-danger border-4 mp-stat">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted mb-1">No Pricing</h6><h4 class="mb-0">{{ $stats['items_without_pricing'] }}</h4></div>
                    <span class="avatar-title bg-danger rounded-circle"><i class="fas fa-triangle-exclamation"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-start border-secondary border-4 mp-stat">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted mb-1">Updated (7d)</h6><h4 class="mb-0">{{ $stats['recently_updated'] }}</h4></div>
                    <span class="avatar-title bg-secondary rounded-circle"><i class="fas fa-history"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-start border-success border-4 mp-stat">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted mb-1">Assigned to a Box</h6><h4 class="mb-0">{{ $salesBoxes->sum(fn($b) => 0) + $items->total() ? '' : '' }}<span id="mpAssignedCount">{{ \App\Models\MasterItem::whereNotNull('sales_box')->where('sales_box','!=','')->count() }}</span></h4></div>
                    <span class="avatar-title bg-success rounded-circle"><i class="fas fa-box-open"></i></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Category nav -->
    <div class="row mb-3">
        <div class="col-12">
            <h6 class="text-muted mb-2">Categories</h6>
            <div class="row g-3">
                @foreach($categoryBoxes as $b)
                <div class="col-md-4 col-lg-2-4">
                    <a href="{{ route('master-products.index', array_filter(['category' => $b['cat']])) }}"
                       class="mp-cat-box card {{ ($category === $b['cat']) ? 'selected' : '' }} text-decoration-none">
                        <div class="card-body text-center d-flex flex-column justify-content-center align-items-center py-3">
                            <i class="fas {{ $b['icon'] }} fa-2x text-{{ $b['color'] }} mb-2"></i>
                            <h6 class="card-title mb-1 small">{{ $b['label'] }}</h6>
                            <p class="card-text text-muted small mb-1 d-none d-lg-block">{{ $b['desc'] }}</p>
                            <span class="badge bg-{{ $b['color'] }}">{{ $categoryCounts[$b['key']] }} products</span>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('master-products.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" for="mp-search">Search</label>
                    <input type="text" class="form-control" id="mp-search" name="search" value="{{ $search }}" placeholder="Name, SKU, description...">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="mp-category">Category</label>
                    <select class="form-select" id="mp-category" name="category">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="mp-brand">Brand</label>
                    <select class="form-select" id="mp-brand" name="brand">
                        <option value="">All Brands</option>
                        @foreach($brands as $b)
                            <option value="{{ $b }}" {{ $brand === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="mp-type">Type</label>
                    <select class="form-select" id="mp-type" name="shirt_type">
                        <option value="">All Types</option>
                        @foreach($shirtTypes as $t)
                            <option value="{{ $t }}" {{ $shirtType === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="mp-color">Color</label>
                    <select class="form-select" id="mp-color" name="color">
                        <option value="">All Colors</option>
                        @foreach($colors as $c)
                            <option value="{{ $c }}" {{ $color === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label" for="mp-salesbox">Box</label>
                    <select class="form-select" id="mp-salesbox" name="sales_box">
                        <option value="">All</option>
                        @foreach($salesBoxes as $sb)
                            <option value="{{ $sb }}" {{ $salesBox === $sb ? 'selected' : '' }}>{{ $sb }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="{{ route('master-products.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body">
            @if($items->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h4>No products found</h4>
                    <p class="text-muted">Try adjusting your filters.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mp-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Brand / Type</th>
                                <th>Size</th>
                                <th>Color</th>
                                <th>Sales Box</th>
                                <th>Supplier</th>
                                <th>Sales Team</th>
                                <th>Agent</th>
                                <th>Volume</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                @php
                                    $supplier = $item->getPricingForTier('supplier_cost');
                                    $sales    = $item->getPricingForTier('sales_team');
                                    $agent    = $item->getPricingForTier('agent_cost');
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $item->name }}</strong>
                                        <div class="small text-muted"><code>{{ $item->sku ?? 'N/A' }}</code></div>
                                    </td>
                                    <td><span class="small">{{ $item->category }}</span></td>
                                    <td class="small">
                                        {{ $item->brand ?: '—' }}<br>
                                        <span class="text-muted">{{ $item->shirt_type ?: '—' }}</span>
                                    </td>
                                    <td class="small">{{ $item->size ?: '—' }}</td>
                                    <td class="small">{{ $item->color ?: '—' }}</td>
                                    <td>
                                        @if($item->sales_box)
                                            <span class="badge bg-info text-dark">{{ $item->sales_box }}</span>
                                        @else
                                            <span class="badge bg-light text-muted">unassigned</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-light text-dark">₱{{ number_format($supplier->final_price ?? $item->unit_price ?? 0, 2) }}</span></td>
                                    <td>
                                        @if($sales)
                                            <span class="badge bg-info text-white">₱{{ number_format($sales->final_price, 2) }}</span>
                                        @else
                                            <span class="badge bg-light text-muted">Not set</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($agent)
                                            <span class="badge bg-warning text-dark">₱{{ number_format($agent->base_price, 2) }}</span>
                                        @else
                                            <span class="badge bg-light text-muted">Not set</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->volume_discounts_display)
                                            <span class="small text-muted"><i class="fas fa-layer-group text-info me-1"></i>{{ $item->volume_discounts_display }}</span>
                                        @else
                                            <span class="badge bg-light text-muted">None</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('master-products.edit', $item->id) }}" class="btn btn-outline-primary" title="Edit (tabbed: details, pricing, sales box)"><i class="fas fa-pen-to-square"></i></a>
                                            <a href="{{ route('product-pricing.edit', $item->id) }}" class="btn btn-outline-secondary" title="Old pricing page"><i class="fas fa-money-bill-wave"></i></a>
                                            <a href="{{ route('master-items.edit', $item->id) }}" class="btn btn-outline-secondary" title="Old product page"><i class="fas fa-cog"></i></a>
                                        </div>

                                    </td>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="small text-muted">Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }}</div>
                    <div>{{ $items->links() }}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
