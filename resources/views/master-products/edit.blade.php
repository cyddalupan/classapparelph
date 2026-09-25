@extends('layouts.app')

@section('content')
@php
    $categories = [
        'Shirt Products', 'Other Products', 'Machine and Equipments',
        'Garment Materials', 'Printing and Office Supplies',
    ];
@endphp

<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0"><i class="fas fa-pen-to-square me-2"></i>Edit Product &amp; Pricing</h1>
            <p class="text-muted mb-0">
                <strong>{{ $item->name }}</strong>
                <span class="badge bg-secondary ms-1">#{{ $item->id }}</span>
                <code class="ms-1">{{ $item->sku ?? 'N/A' }}</code>
            </p>
        </div>
        <div class="col-auto">
            <a href="{{ route('master-products.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to list
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><strong>Please fix:</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <ul class="nav nav-tabs mb-4" id="mpTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#mp-details" type="button"><i class="fas fa-circle-info me-1"></i> Product Details</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#mp-pricing" type="button"><i class="fas fa-tags me-1"></i> Pricing</button></li>
    </ul>

    <div class="tab-content">
        <!-- Main form: Details + Pricing (single submit) -->
        <form action="{{ route('product-pricing.update', $item->id) }}" method="POST" id="mpForm">
            @csrf
            @method('PUT')

            <!-- Details tab -->
            <div class="tab-pane fade show active" id="mp-details">
                <div class="card mb-4">
                    <div class="card-header bg-light"><h6 class="mb-0"><i class="fas fa-circle-info me-1"></i> Product Details</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Product Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required value="{{ old('name', $item->name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="category">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">-- Select --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ old('category', $item->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                    @if($item->category && !in_array($item->category, $categories))
                                        <option value="{{ $item->category }}" selected>{{ $item->category }} (current)</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="sku">SKU</label>
                                <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku', $item->sku) }}">
                            </div>
                            {{-- barcode kept as hidden passthrough: hindi needed sa shirts UI, pero preserved para hindi mawipe sa save --}}
                            <input type="hidden" name="barcode" value="{{ old('barcode', $item->barcode) }}">
                            <div class="col-md-6">
                                <label class="form-label" for="unit_price">Unit Price (₱)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="unit_price" name="unit_price" value="{{ old('unit_price', $item->unit_price) }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label" for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="2">{{ old('description', $item->description) }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="text-muted mb-3"><i class="fas fa-tags me-1"></i> Catalog Attributes
                            <small class="text-muted">(ginagamit ng sales-box filters)</small>
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label" for="brand">Brand</label>
                                <input type="text" class="form-control" id="brand" name="brand" value="{{ old('brand', $item->brand) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="shirt_type">Type</label>
                                <input type="text" class="form-control" id="shirt_type" name="shirt_type" value="{{ old('shirt_type', $item->shirt_type) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="color">Color</label>
                                <input type="text" class="form-control" id="color" name="color" value="{{ old('color', $item->color) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="size">Size</label>
                                <input type="text" class="form-control" id="size" name="size" value="{{ old('size', $item->size) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing tab -->
            <div class="tab-pane fade" id="mp-pricing">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-primary">
                            <div class="card-header bg-primary text-white"><h6 class="mb-0"><i class="fas fa-industry me-1"></i> Supplier Cost <small class="float-end">Your Cost</small></h6></div>
                            <div class="card-body">
                                <label class="form-label" for="supplier_cost">Cost Price *</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="supplier_cost" name="supplier_cost_base_price" step="0.01" min="0" required
                                           value="{{ old('supplier_cost_base_price', $supplierPricing->base_price ?? $item->unit_price ?? 0) }}">
                                </div>
                                <div class="form-text">Your actual cost for this product</div>
                                <div class="alert alert-info py-2 mt-3 mb-0"><small><strong>Final Price:</strong> <span id="supplierFinalPrice">₱{{ number_format($supplierPricing->final_price ?? $item->unit_price ?? 0, 2) }}</span></small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-info">
                            <div class="card-header bg-info text-white"><h6 class="mb-0"><i class="fas fa-users me-1"></i> Sales Team Price <small class="float-end">Fixed to Team</small></h6></div>
                            <div class="card-body">
                                <label class="form-label" for="sales_team_price">Selling Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="sales_team_price" name="sales_team_final_price" step="0.01" min="0"
                                           value="{{ old('sales_team_final_price', $salesPricing->final_price ?? '') }}">
                                </div>
                                <div class="form-text">Fixed price for your sales team</div>
                                <input type="hidden" name="sales_team_base_price" value="{{ old('supplier_cost_base_price', $supplierPricing->base_price ?? $item->unit_price ?? 0) }}">
                                @if($salesPricing)
                                    <div class="alert alert-success py-2 mt-3 mb-0"><small><strong>Markup:</strong> {{ number_format($salesPricing->markup_percentage ?? 0, 1) }}% (₱{{ number_format($salesPricing->markup_amount ?? 0, 2) }})</small></div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-warning">
                            <div class="card-header bg-warning text-dark"><h6 class="mb-0"><i class="fas fa-user-tie me-1"></i> Agent Cost <small class="float-end">Base for Agents</small></h6></div>
                            <div class="card-body">
                                <label class="form-label" for="agent_base_price">Base Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="agent_base_price" name="agent_cost_base_price" step="0.01" min="0"
                                           value="{{ old('agent_cost_base_price', $agentPricing->base_price ?? '') }}">
                                </div>
                                <label class="form-label mt-3" for="agent_markup_percentage">Suggested Markup %</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="agent_markup_percentage" name="agent_cost_markup_percentage" step="0.1" min="0" max="100"
                                           value="{{ old('agent_cost_markup_percentage', $agentPricing->markup_percentage ?? '') }}">
                                    <span class="input-group-text">%</span>
                                </div>
                                @if($agentPricing && $agentPricing->final_price)
                                    <div class="alert alert-success py-2 mt-3 mb-0"><small><strong>Final with Markup:</strong> ₱{{ number_format($agentPricing->final_price, 2) }}</small></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sales Box removed from the edit UI; kept as hidden passthrough para hindi mawipe ang existing assignment sa save --}}
            <input type="hidden" name="sales_box" value="{{ old('sales_box', $item->sales_box) }}">

            <div class="d-flex justify-content-end mb-5">
                <a href="{{ route('master-products.index') }}" class="btn btn-secondary me-2"><i class="fas fa-times me-1"></i> Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Details &amp; Pricing</button>
            </div>
        </form>

    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    function updateSupplierFinalPrice() {
        const cost = parseFloat($('#supplier_cost').val()) || 0;
        $('#supplierFinalPrice').text('₱' + cost.toFixed(2));
    }
    $('#supplier_cost').on('input', updateSupplierFinalPrice);
    updateSupplierFinalPrice();

    $('#mpForm').submit(function () {
        const supplierBase = parseFloat($('#supplier_cost').val()) || 0;
        const salesFinal = parseFloat($('#sales_team_price').val()) || 0;
        if (salesFinal > 0 && supplierBase > 0) {
            const amt = salesFinal - supplierBase;
            const pct = (amt / supplierBase) * 100;
            $(this).append(`<input type="hidden" name="sales_team_markup_amount" value="${amt.toFixed(2)}">`);
            $(this).append(`<input type="hidden" name="sales_team_markup_percentage" value="${pct.toFixed(2)}">`);
        }
        const agentBase = parseFloat($('#agent_base_price').val()) || 0;
        const agentPct = parseFloat($('#agent_markup_percentage').val()) || 0;
        if (agentBase > 0 && agentPct > 0) {
            const amt = (agentBase * agentPct) / 100;
            $(this).append(`<input type="hidden" name="agent_cost_markup_amount" value="${amt.toFixed(2)}">`);
            $(this).append(`<input type="hidden" name="agent_cost_final_price" value="${(agentBase + amt).toFixed(2)}">`);
        }
    });

    // preserve active tab after validation redirect
    const urlTab = new URLSearchParams(location.search).get('tab');
    if (urlTab) { const el = document.querySelector(`[data-bs-target="#mp-${urlTab}"]`); if (el) new bootstrap.Tab(el).show(); }
});
</script>
@endpush
@endsection
