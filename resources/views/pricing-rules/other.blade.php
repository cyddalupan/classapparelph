@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="fas fa-box-open me-2"></i>Other Items / Products — Pricing Rules
            </h1>
            <p class="text-muted mb-0">
                Per-item prices para sa non-apparel products, naka-tab kada product (hal. Mug).
                Ito ang nagpapalabas ng item sa <strong>Other Items</strong> box ng sales form
                (dapat may <em>Sales Team Price</em>).
            </p>
        </div>
        <div class="col-auto">
            <a href="{{ route('pricing.rules') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Pricing Rules
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $groups = $items->groupBy('name');
    @endphp

    @if($items->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-50"></i>
                <p class="text-muted mb-0">
                    Wala pang Other Items. Gumawa muna sa Master Products (Sales Box = <code>other</code>).
                </p>
            </div>
        </div>
    @else
    <form method="POST" action="{{ route('pricing.rules.other.prices') }}">
        @csrf

        <div class="card">
            <div class="card-header bg-transparent pb-0">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0"><i class="fas fa-tags me-1"></i> Item Prices by Product</h6>
                    <span class="badge bg-secondary">{{ $items->count() }} item(s) · {{ $groups->count() }} product(s)</span>
                </div>
                <ul class="nav nav-tabs card-header-tabs" id="otherProductTabs" role="tablist">
                    @foreach($groups as $productName => $group)
                        @php $tabId = 'other-tab-' . $loop->index; @endphp
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                    id="{{ $tabId }}-btn"
                                    data-bs-toggle="tab"
                                    data-bs-target="#{{ $tabId }}"
                                    type="button" role="tab"
                                    aria-controls="{{ $tabId }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                <i class="fas fa-mug-hot me-1"></i> {{ $productName }}
                                <span class="badge bg-warning text-dark ms-1">{{ $group->count() }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="tab-content" id="otherProductTabsContent">
                    @foreach($groups as $productName => $group)
                        @php $tabId = 'other-tab-' . $loop->index; @endphp
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                             id="{{ $tabId }}" role="tabpanel" aria-labelledby="{{ $tabId }}-btn">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Type</th>
                                            <th>SKU</th>
                                            <th class="text-end" style="width:150px;">Supplier Cost (₱)</th>
                                            <th class="text-end" style="width:150px;">Sales Team Price (₱)</th>
                                            <th class="text-end" style="width:150px;">Agent Cost (₱)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($group as $item)
                                        @php
                                            $sup = $item->productPricings->firstWhere('price_tier', 'supplier_cost');
                                            $sal = $item->productPricings->firstWhere('price_tier', 'sales_team');
                                            $agt = $item->productPricings->firstWhere('price_tier', 'agent_cost');
                                            $supVal = $sup ? ($sup->base_price ?? $sup->final_price) : null;
                                            $salVal = $sal ? ($sal->final_price ?? $sal->base_price) : null;
                                            $agtVal = $agt ? ($agt->base_price ?? $agt->final_price) : null;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $item->shirt_type ?? $item->name }}</strong>
                                                @if($item->brand)
                                                    <div class="small text-muted">Brand: {{ $item->brand }}</div>
                                                @endif
                                            </td>
                                            <td><code>{{ $item->sku ?? '—' }}</code></td>
                                            <td class="text-end">
                                                <input type="number" step="0.01" min="0"
                                                       class="form-control form-control-sm text-end ms-auto" style="max-width:140px;"
                                                       name="prices[{{ $item->id }}][supplier_cost]"
                                                       value="{{ $supVal }}" placeholder="—">
                                            </td>
                                            <td class="text-end">
                                                <input type="number" step="0.01" min="0"
                                                       class="form-control form-control-sm text-end ms-auto" style="max-width:140px;"
                                                       name="prices[{{ $item->id }}][sales_team]"
                                                       value="{{ $salVal }}" placeholder="—">
                                            </td>
                                            <td class="text-end">
                                                <input type="number" step="0.01" min="0"
                                                       class="form-control form-control-sm text-end ms-auto" style="max-width:140px;"
                                                       name="prices[{{ $item->id }}][agent_cost]"
                                                       value="{{ $agtVal }}" placeholder="—">
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Blank = keep current price. Ang <strong>Sales Team Price</strong> ang lumalabas sa sales form.
                </small>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save me-1"></i> Save Prices
                </button>
            </div>
        </div>
    </form>
    @endif
</div>
@endsection
