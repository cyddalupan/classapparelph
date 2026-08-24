@extends('layouts.app')

@section('title', 'Damage Reports')

@push('styles')
<style>
    .damage-card { transition: all 0.2s; border-left: 4px solid #dee2e6; }
    .damage-card.submitted { border-left-color: #ffc107; }
    .damage-card.under_review { border-left-color: #0dcaf0; }
    .damage-card.issued { border-left-color: #fd7e14; }
    .damage-card.acknowledged { border-left-color: #6f42c1; }
    .damage-card.contested { border-left-color: #dc3545; }
    .damage-card.resolved { border-left-color: #198754; }
    .damage-card.dismissed { border-left-color: #6c757d; opacity: 0.7; }
    .damage-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); transform: translateX(2px); }
    .section-title { font-weight: 600; color: #333; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e9ecef; }
    .filter-active { font-weight: 600; background: #e9ecef; }
    .sev-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; }
    .sev-minor { background: #6c757d; }
    .sev-major { background: #fd7e14; }
    .sev-critical { background: #dc3545; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Damage Reports</h4>
            <p class="text-muted mb-0 small">Report, review, and track damage incidents per shop</p>
        </div>
        <div class="d-flex gap-2">
            @if($managedShop)
                <span class="badge bg-info align-self-center">
                    <i class="fas fa-store me-1"></i>Managing: {{ $managedShop->name }}
                </span>
            @endif
            <a href="{{ route('damage.create') }}" class="btn btn-danger btn-sm">
                <i class="fas fa-plus me-1"></i>Report Damage
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('damage.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="all">All Statuses</option>
                        @foreach(\App\Models\DamageReport::STATUSES as $val => $label)
                            <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Shop</label>
                    <select name="shop_id" class="form-select form-select-sm">
                        <option value="">All Shops</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1">Search</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Report # or description...">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report list -->
    <div class="row">
        @forelse($reports as $report)
            <div class="col-md-6 col-xl-4 mb-3">
                <a href="{{ route('damage.show', $report->id) }}" class="text-decoration-none">
                    <div class="card damage-card {{ $report->status }} shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="fw-bold text-dark">{{ $report->report_no }}</span>
                                    <span class="badge bg-{{ $report->status === 'dismissed' ? 'secondary' : ($report->status === 'contested' ? 'danger' : ($report->status === 'resolved' ? 'success' : 'info')) }} ms-1">
                                        {{ \App\Models\DamageReport::STATUSES[$report->status] ?? $report->status }}
                                    </span>
                                </div>
                                <span class="badge bg-secondary">{{ $report->shop->name ?? 'Unknown Shop' }}</span>
                            </div>
                            <p class="text-muted small mb-2" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                {{ $report->description }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center small text-muted">
                                <span>
                                    <span class="sev-dot sev-{{ $report->severity }}"></span>
                                    {{ ucfirst($report->severity) }}
                                    @if($report->points > 0)
                                        · {{ $report->points }} pt{{ $report->points > 1 ? 's' : '' }}
                                    @endif
                                </span>
                                <span><i class="far fa-clock me-1"></i>{{ $report->created_at->format('M d, Y') }}</span>
                            </div>
                            @if($report->sale)
                                <div class="mt-2 small">
                                    <span class="badge bg-light text-dark border"><i class="fas fa-tag me-1"></i>{{ $report->sale->sales_number }}</span>
                                </div>
                            @endif
                            @if($report->accountableUsers->count() > 0)
                                <div class="mt-2 small text-muted">
                                    <i class="fas fa-users me-1"></i>
                                    @foreach($report->accountableUsers as $au)
                                        <span class="badge bg-{{ $au->acknowledge_status === 'acknowledged' ? 'success' : ($au->acknowledge_status === 'contested' ? 'danger' : 'warning') }} me-1">
                                            {{ $au->user->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                    <p class="mb-0">No damage reports found.</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $reports->links() }}
    </div>
</div>
@endsection
