@extends('layouts.app')

@section('title', 'Special Price Review')

@push('styles')
<style>
    .sp-hero {
        background: linear-gradient(135deg, #7a4d00 0%, #b8860b 45%, #e6a817 100%);
        border-radius: 16px;
        padding: 20px 26px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(184, 134, 11, .25);
    }
    .sp-hero::after {
        content: "⭐";
        position: absolute;
        right: 18px;
        bottom: -18px;
        font-size: 84px;
        opacity: .14;
        transform: rotate(-8deg);
    }
    .sp-hero h4 { font-weight: 800; letter-spacing: .3px; }
    .sp-hero .sub { opacity: .92; font-size: 12.5px; }
    .sp-stat {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        padding: 14px 18px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .05);
    }
    .sp-stat .val { font-size: 24px; font-weight: 800; line-height: 1.1; color: #111827; }
    .sp-stat .lbl { font-size: 11.5px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
    .sp-card {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .04);
        overflow: hidden;
    }
    .sp-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f3f5;
        background: #fafbfc;
    }
    .sp-reason {
        background: #fff8e1;
        border: 1px solid #ffe082;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: .85rem;
    }
    .sp-reason.empty { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .kind-badge {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .4px;
        padding: 3px 8px;
        border-radius: 20px;
        text-transform: uppercase;
    }
    .kind-sublimation { background: #ede9fe; color: #6d28d9; }
    .kind-garment { background: #dbeafe; color: #1d4ed8; }
    .sp-muted { color: #6b7280; font-size: .8rem; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <div class="sp-hero mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-0"><i class="fas fa-tags me-2"></i>Special Price Review</h4>
            <div class="sub mt-1">Mga order na may manual special price override — kasama ang reason para ma-review.</div>
        </div>
        <a href="{{ route('sales.prototype.ga-order-list') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> GA Order List</a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="sp-stat">
                <div class="val">{{ $lines->count() }}</div>
                <div class="lbl">Special Price Lines</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sp-stat">
                <div class="val">{{ $sales->total() }}</div>
                <div class="lbl">Orders (may flag)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sp-stat">
                <div class="val">{{ $lines->where('reason', '')->count() }}</div>
                <div class="lbl">Walang Reason ⚠️</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="sp-stat">
                <div class="val">{{ $unmapped->count() }}</div>
                <div class="lbl">Unmapped (check)</div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('sales.prototype.special-price-list') }}" class="d-flex gap-2 mb-3">
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Search sales number or customer…" style="max-width:320px;">
        <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-search"></i></button>
        @if($q)
            <a href="{{ route('sales.prototype.special-price-list') }}" class="btn btn-sm btn-outline-danger">Clear</a>
        @endif
    </form>

    @forelse($lines as $line)
        @php $sale = $line['sale']; @endphp
        <div class="sp-card mb-3">
            <div class="sp-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('sales.prototype.show', $sale->id) }}" class="fw-bold text-decoration-none">{{ $sale->sales_number }}</a>
                    <span class="text-muted small">·</span>
                    <span>{{ $sale->customer_name }}</span>
                    @if(isset($departmentLabels[$sale->department_id]))
                        <span class="badge bg-secondary">{{ $departmentLabels[$sale->department_id] }}</span>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge {{ $sale->status === 'completed' ? 'bg-success' : ($sale->status === 'confirmed' ? 'bg-primary' : 'bg-warning text-dark') }}">{{ strtoupper($sale->status) }}</span>
                    <span class="text-muted small"><i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y') }}</span>
                </div>
            </div>
            <div class="p-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div>
                        <span class="kind-badge {{ $line['kind'] === 'Sublimation' ? 'kind-sublimation' : 'kind-garment' }}">{{ $line['kind'] }}</span>
                        <span class="fw-semibold ms-2">{{ $line['itemName'] }}</span>
                        @if($line['project'])
                            <div class="sp-muted mt-1"><i class="fas fa-briefcase me-1"></i>{{ $line['project'] }}</div>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-warning" style="font-size:1.05rem;">
                            @if($line['price'] !== null && $line['price'] !== '')
                                ₱{{ number_format((float)$line['price'], 2) }}@if($line['kind'] === 'Sublimation')/pc @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                        <div class="sp-muted">Qty: {{ $line['qty'] }}</div>
                    </div>
                </div>
                <div class="sp-reason {{ $line['reason'] === '' ? 'empty' : '' }}">
                    <i class="fas fa-comment-dots me-1"></i>
                    <strong>Reason:</strong>
                    {{ $line['reason'] !== '' ? $line['reason'] : '⚠️ Walang reason na nilagay!' }}
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">Walang nahanap na order na may special price.</div>
    @endforelse

    @if($unmapped->isNotEmpty())
        <div class="card border-warning mb-3">
            <div class="card-header bg-warning text-dark fw-bold"><i class="fas fa-exclamation-triangle me-1"></i> May flag pero hindi ma-extract ang line details</div>
            <div class="card-body py-2">
                @foreach($unmapped as $sale)
                    <a href="{{ route('sales.prototype.show', $sale->id) }}" class="badge bg-light text-dark border me-1 mb-1 text-decoration-none">{{ $sale->sales_number }}</a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-3">
        {{ $sales->links() }}
    </div>

</div>
@endsection
