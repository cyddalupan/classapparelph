@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="fas fa-sliders-h me-2"></i>Pricing Rules Dashboard
            </h1>
            <p class="text-muted">Configure pricing rules for all services and product types</p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="{{ route('product-pricing.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Pricing
                </a>
            </div>
        </div>
    </div>

    <!-- Service Cards -->
    <div class="row">
        @foreach($services as $key => $service)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-start border-{{ $service['color'] }} border-4">
                <div class="card-header bg-{{ $service['color'] }} bg-opacity-10">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="{{ $service['icon'] }} me-2 text-{{ $service['color'] }}"></i>
                                {{ $service['name'] }}
                            </h5>
                            <small class="text-muted">{{ $service['description'] }}</small>
                        </div>
                        <span class="badge bg-{{ $service['configured'] ? 'success' : 'secondary' }}">
                            {{ $service['configured'] ? 'Configured' : 'Not Set Up' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if($service['configured'])
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Base Prices</small>
                            <small class="text-{{ $service['color'] }}">{{ $service['price_count'] }} configured</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-{{ $service['color'] }}" 
                                 style="width: {{ min(100, ($service['price_count'] / 10) * 100) }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Combo Discounts</small>
                            <small class="text-{{ $service['color'] }}">{{ $service['combo_count'] }} rules</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-{{ $service['color'] }}" 
                                 style="width: {{ min(100, ($service['combo_count'] / 5) * 100) }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Bulk Discounts</small>
                            <small class="text-{{ $service['color'] }}">{{ $service['bulk_count'] }} tiers</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-{{ $service['color'] }}" 
                                 style="width: {{ min(100, ($service['bulk_count'] / 5) * 100) }}%"></div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="{{ $service['icon'] }} fa-3x text-{{ $service['color'] }} mb-3 opacity-50"></i>
                        <p class="text-muted">No pricing rules configured yet.</p>
                        <small class="text-muted">Click "Set Up Rules" to configure pricing for this service.</small>
                    </div>
                    @endif
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-grid">
                        @if($service['configured'])
                        <a href="{{ route($service['edit_route']) }}" class="btn btn-{{ $service['color'] }}">
                            <i class="fas fa-edit me-1"></i> Edit Rules
                        </a>
                        @else
                        <button class="btn btn-outline-{{ $service['color'] }}" disabled>
                            <i class="fas fa-cog me-1"></i> Set Up Rules
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <i class="fas fa-sync-alt fa-2x text-primary mb-3"></i>
                                    <h6>Sync with Product Pricing</h6>
                                    <p class="text-muted small">Update all rules from current product prices</p>
                                    <button class="btn btn-sm btn-outline-primary" disabled>
                                        <i class="fas fa-sync me-1"></i> Sync Now
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-export fa-2x text-success mb-3"></i>
                                    <h6>Export Rules</h6>
                                    <p class="text-muted small">Download all pricing rules as CSV/Excel</p>
                                    <button class="btn btn-sm btn-outline-success" disabled>
                                        <i class="fas fa-download me-1"></i> Export
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <i class="fas fa-history fa-2x text-info mb-3"></i>
                                    <h6>Rule History</h6>
                                    <p class="text-muted small">View changes to pricing rules over time</p>
                                    <button class="btn btn-sm btn-outline-info" disabled>
                                        <i class="fas fa-history me-1"></i> View History
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> System Status</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Garment Printing:</strong> Active with {{ $services['printing']['price_count'] }} prices
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-times-circle text-secondary me-2"></i>
                                    <strong>Bulk Order Rules:</strong> Not configured
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-times-circle text-secondary me-2"></i>
                                    <strong>Full Sublimation:</strong> Not configured
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-times-circle text-secondary me-2"></i>
                                    <strong>Tarpaulin & Banner:</strong> Not configured
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-times-circle text-secondary me-2"></i>
                                    <strong>Embroidery:</strong> Not configured
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-times-circle text-secondary me-2"></i>
                                    <strong>Sticker & Decal:</strong> Not configured
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
.progress {
    background-color: #f0f0f0;
}
</style>
@endpush