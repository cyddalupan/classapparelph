@extends('layouts.app')

@section('title', 'Special Price Dashboard')

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
        content: "📊";
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
        height: 100%;
    }
    .sp-stat .val { font-size: 26px; font-weight: 800; line-height: 1.1; color: #111827; }
    .sp-stat .lbl { font-size: 11.5px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
    .sp-card {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .04);
        overflow: hidden;
        height: 100%;
    }
    .sp-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f3f5;
        background: #fafbfc;
        font-weight: 700;
    }
    .sp-bar {
        height: 8px;
        background: #f1f3f5;
        border-radius: 20px;
        overflow: hidden;
        display: flex;
    }
    .sp-bar > .sp-bar-checked { background: #198754; }
    .sp-bar > .sp-bar-pending { background: #ffc107; }
    .sp-muted { color: #6b7280; font-size: .8rem; }
    .kind-badge {
        font-size: .68rem; font-weight: 700; letter-spacing: .4px;
        padding: 3px 8px; border-radius: 20px; text-transform: uppercase;
    }
    .kind-sublimation { background: #ede9fe; color: #6d28d9; }
    .kind-garment { background: #dbeafe; color: #1d4ed8; }
    .sp-ring {
        width: 132px; height: 132px; border-radius: 50%;
        display: grid; place-items: center; margin: 0 auto;
    }
    .sp-ring-inner {
        width: 104px; height: 104px; border-radius: 50%; background: #fff;
        display: grid; place-items: center; box-shadow: inset 0 0 0 1px #eef0f4;
    }
    .sp-ring-inner .pct { font-size: 26px; font-weight: 800; color: #111827; line-height: 1; }
    .sp-ring-inner .cap { font-size: 10.5px; color: #6b7280; font-weight: 700; letter-spacing: .5px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <div class="sp-hero mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Special Price Dashboard</h4>
            <div class="sub mt-1">Buod ng lahat ng order na may manual special price override — kasama ang checked / pending, per kind, per department, at per agent.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.prototype.special-price-list') }}" class="btn btn-light btn-sm"><i class="fas fa-list me-1"></i> Special Price List</a>
            <a href="{{ route('sales.prototype.ga-order-list') }}" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left me-1"></i> GA Order List</a>
        </div>
    </div>

    {{-- Top stats --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="sp-stat">
                <div class="val">{{ number_format($totalLines) }}</div>
                <div class="lbl">Special Price Lines</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="sp-stat">
                <div class="val text-success">{{ number_format($checkedCount) }}</div>
                <div class="lbl">Checked</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="sp-stat">
                <div class="val text-warning">{{ number_format($pendingCount) }}</div>
                <div class="lbl">Pending</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="sp-stat">
                <div class="val text-danger">{{ number_format($noReason) }}</div>
                <div class="lbl">Walang Reason ⚠️</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        {{-- Progress ring --}}
        <div class="col-md-4">
            <div class="sp-card">
                <div class="sp-card-header"><i class="fas fa-check-double me-1"></i> Review Progress</div>
                <div class="p-3 text-center">
                    @php
                        $deg = (int) round($pctChecked * 3.6);
                    @endphp
                    <div class="sp-ring" style="background: conic-gradient(#198754 0deg {{ $deg }}deg, #f1f3f5 {{ $deg }}deg 360deg);">
                        <div class="sp-ring-inner">
                            <div>
                                <div class="pct">{{ $pctChecked }}%</div>
                                <div class="cap">CHECKED</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 sp-muted">{{ number_format($checkedCount) }} of {{ number_format($totalLines) }} lines na-check na</div>
                    <div class="mt-2">
                        <div class="sp-bar">
                            @if($totalLines > 0)
                                <div class="sp-bar-checked" style="width: {{ $pctChecked }}%;"></div>
                                <div class="sp-bar-pending" style="width: {{ 100 - $pctChecked }}%;"></div>
                            @else
                                <div class="sp-bar-pending" style="width: 100%;"></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- By kind --}}
        <div class="col-md-4">
            <div class="sp-card">
                <div class="sp-card-header"><i class="fas fa-layer-group me-1"></i> Per Kind</div>
                <div class="p-3">
                    @forelse($byKind as $row)
                        @php $p = $row['total'] > 0 ? (int) round($row['checked'] / $row['total'] * 100) : 0; @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="kind-badge {{ $row['label'] === 'Sublimation' ? 'kind-sublimation' : 'kind-garment' }}">{{ $row['label'] }}</span>
                                <span class="small"><strong>{{ $row['total'] }}</strong> <span class="sp-muted">lines · {{ $row['checked'] }} checked · Qty {{ number_format($row['qty']) }}</span></span>
                            </div>
                            <div class="sp-bar mt-2">
                                <div class="sp-bar-checked" style="width: {{ $p }}%;"></div>
                                <div class="sp-bar-pending" style="width: {{ 100 - $p }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="sp-muted">Walang data.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- By department --}}
        <div class="col-md-4">
            <div class="sp-card">
                <div class="sp-card-header"><i class="fas fa-building me-1"></i> Per Department</div>
                <div class="p-3">
                    @forelse($byDept as $row)
                        @php $p = $row['total'] > 0 ? (int) round($row['checked'] / $row['total'] * 100) : 0; @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">{{ $row['label'] }}</span>
                                <span class="small"><strong>{{ $row['total'] }}</strong> <span class="sp-muted">lines · {{ $row['checked'] }} checked</span></span>
                            </div>
                            <div class="sp-bar mt-2">
                                <div class="sp-bar-checked" style="width: {{ $p }}%;"></div>
                                <div class="sp-bar-pending" style="width: {{ 100 - $p }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="sp-muted">Walang data.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        {{-- By agent --}}
        <div class="col-md-6">
            <div class="sp-card">
                <div class="sp-card-header"><i class="fas fa-user-tie me-1"></i> Top Agents (may special price)</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Agent</th><th class="text-end">Lines</th><th class="text-end">Checked</th><th class="text-end">Pending</th></tr>
                        </thead>
                        <tbody>
                            @forelse($byAgent as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td class="text-end fw-semibold">{{ $row['total'] }}</td>
                                    <td class="text-end text-success">{{ $row['checked'] }}</td>
                                    <td class="text-end text-warning">{{ $row['total'] - $row['checked'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="sp-muted">Walang data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent checks --}}
        <div class="col-md-6">
            <div class="sp-card">
                <div class="sp-card-header"><i class="fas fa-history me-1"></i> Recent Checks</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Sales #</th><th>Item</th><th>By</th><th class="text-end">When</th></tr>
                        </thead>
                        <tbody>
                            @forelse($recent as $row)
                                <tr>
                                    <td><a href="{{ route('sales.prototype.show', $row['sale']->id) }}" class="text-decoration-none">{{ $row['sale']->sales_number }}</a></td>
                                    <td class="small">{{ \Illuminate\Support\Str::limit($row['item'], 26) }}</td>
                                    <td class="small">{{ $row['by'] }}</td>
                                    <td class="text-end sp-muted">{{ $row['at'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="sp-muted">Wala pang checked.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Needs attention --}}
    <div class="sp-card mb-3">
        <div class="sp-card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-exclamation-triangle text-danger me-1"></i> Walang Reason — kailangan ng pansin</span>
            <span class="badge bg-danger">{{ $noReason }}</span>
        </div>
        @if($noReasonLines->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Sales #</th><th>Customer</th><th>Kind</th><th>Item</th><th class="text-end">Qty</th><th class="text-end">Price</th></tr>
                    </thead>
                    <tbody>
                        @foreach($noReasonLines as $line)
                            <tr class="{{ !empty($line['reviewed']) ? 'opacity-50' : '' }}">
                                <td><a href="{{ route('sales.prototype.show', $line['sale']->id) }}" class="text-decoration-none">{{ $line['sale']->sales_number }}</a></td>
                                <td class="small">{{ $line['sale']->customer_name }}</td>
                                <td><span class="kind-badge {{ $line['kind'] === 'Sublimation' ? 'kind-sublimation' : 'kind-garment' }}">{{ $line['kind'] }}</span></td>
                                <td class="small">{{ \Illuminate\Support\Str::limit($line['itemName'], 30) }}</td>
                                <td class="text-end">{{ $line['qty'] }}</td>
                                <td class="text-end">@if($line['price'] !== null && $line['price'] !== '')₱{{ number_format((float)$line['price'], 2) }}@else<span class="text-muted">—</span>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-3 text-success"><i class="fas fa-check-circle me-1"></i> Lahat ng special price line ay may reason. 👌</div>
        @endif
    </div>

    {{-- Footer summary --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="sp-muted">
            {{ number_format($sales->count()) }} order(s) na may special-price flag ·
            {{ number_format($unmappedCount) }} unmapped (may flag pero hindi ma-extract)
        </div>
        <a href="{{ route('sales.prototype.special-price-list') }}" class="btn btn-sm btn-outline-warning">
            <i class="fas fa-list me-1"></i> Buksan ang Check/Review List
        </a>
    </div>

</div>
@endsection
