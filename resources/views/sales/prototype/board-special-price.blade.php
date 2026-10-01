@extends('layouts.app')

@section('title', 'Special Price — Board Member')

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
    .sp-hero::after { content: "⭐"; position: absolute; right: 18px; bottom: -18px; font-size: 84px; opacity: .14; transform: rotate(-8deg); }
    .sp-hero h4 { font-weight: 800; letter-spacing: .3px; }
    .sp-hero .sub { opacity: .92; font-size: 12.5px; }
    .sp-stat { background: #fff; border: 1px solid #eef0f4; border-radius: 14px; padding: 14px 18px; box-shadow: 0 2px 10px rgba(17, 24, 39, .05); }
    .sp-stat .val { font-size: 24px; font-weight: 800; line-height: 1.1; color: #111827; }
    .sp-stat .lbl { font-size: 11.5px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
    .sp-card { background: #fff; border: 1px solid #eef0f4; border-radius: 14px; box-shadow: 0 2px 10px rgba(17, 24, 39, .04); overflow: hidden; }
    .sp-card-header { padding: 12px 16px; border-bottom: 1px solid #f1f3f5; background: #fafbfc; }
    .sp-reason { background: #fff8e1; border: 1px solid #ffe082; border-radius: 8px; padding: 6px 10px; font-size: .85rem; }
    .sp-reason.empty { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .kind-badge { font-size: .68rem; font-weight: 700; letter-spacing: .4px; padding: 3px 8px; border-radius: 20px; text-transform: uppercase; }
    .kind-sublimation { background: #ede9fe; color: #6d28d9; }
    .kind-garment { background: #dbeafe; color: #1d4ed8; }
    .sp-muted { color: #6b7280; font-size: .8rem; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    @include('sales.prototype._board-tabs')

    <div class="sp-hero mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-0"><i class="fas fa-tags me-2"></i>Special Price</h4>
            <div class="sub mt-1">Mga order na may manual special price override — kasama ang reason at ang history ng checks.</div>
        </div>
        @unless($canCheck)
            <span class="badge bg-light text-dark border"><i class="fas fa-eye me-1"></i>View-only — si CEO lang ang makaka-mark as checked</span>
        @endunless
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
                <div class="val">{{ $lines->filter(fn ($l) => !empty($l['reviewed']))->count() }}</div>
                <div class="lbl">Checked na</div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('sales.prototype.board.special-price') }}" class="d-flex gap-2 mb-3">
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Search sales number or customer…" style="max-width:320px;">
        <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-search"></i></button>
        @if($q)
            <a href="{{ route('sales.prototype.board.special-price') }}" class="btn btn-sm btn-outline-danger">Clear</a>
        @endif
    </form>

    @php
        $pendingLines = $lines->filter(fn ($l) => empty($l['reviewed']))->values();
        $checkedLines = $lines->filter(fn ($l) => !empty($l['reviewed']))->values();
    @endphp

    @if($checkedLines->isNotEmpty())
        <div class="text-end mb-2">
            <a href="#spChecked" class="btn btn-sm btn-outline-success" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="spChecked">
                <i class="fas fa-check-circle me-1"></i>Checked na ({{ $checkedLines->count() }}) <i class="fas fa-chevron-down ms-1"></i>
            </a>
        </div>
    @endif

    @forelse($pendingLines as $line)
        @include('sales.prototype.partials.special_price_line', ['line' => $line, 'canCheck' => $canCheck])
    @empty
        <div class="alert alert-info">Walang naiiwang pending — lahat ng special price line ay checked na. 🎉</div>
    @endforelse

    @if($checkedLines->isNotEmpty())
        <div class="collapse" id="spChecked">
            <div class="d-flex align-items-center gap-2 mb-2 mt-2">
                <i class="fas fa-check-circle text-success"></i>
                <span class="fw-semibold">Checked na ({{ $checkedLines->count() }})</span>
            </div>
            @foreach($checkedLines as $line)
                @include('sales.prototype.partials.special_price_line', ['line' => $line, 'canCheck' => $canCheck])
            @endforeach
        </div>
    @endif

    @if($unmapped->isNotEmpty())
        <div class="card border-warning mb-3">
            <div class="card-header bg-warning text-dark fw-bold"><i class="fas fa-exclamation-triangle me-1"></i> May flag pero hindi ma-extract ang line details</div>
            <div class="card-body py-2">
                <div class="small text-muted mb-2">Safety net: mga order na ang raw JSON ay may special-price flag pero hindi na-parse bilang linya. I-click ang sales number para i-check manually.</div>
                @foreach($unmapped as $sale)
                    <a href="{{ route('sales.prototype.show', $sale->id) }}" class="badge bg-light text-dark border me-1 mb-1 text-decoration-none">{{ $sale->sales_number }}</a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-2 mb-2">{{ $sales->links() }}</div>

    <!-- HISTORY — sino/kailan nag-check (view-only) -->
    <div class="sp-card mt-4">
        <div class="sp-card-header d-flex justify-content-between align-items-center">
            <div class="fw-bold"><i class="fas fa-history me-2 text-success"></i>History ng Checks</div>
            <span class="sp-muted">{{ $history->count() }} record(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sale #</th>
                        <th>Customer</th>
                        <th>Item</th>
                        <th>Kind</th>
                        <th>Checked by</th>
                        <th>Checked at</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $h)
                        <tr>
                            <td>
                                @if($h['sale'])
                                    <a href="{{ route('sales.prototype.show', $h['sale']->id) }}" class="text-decoration-none fw-semibold">{{ $h['sale']->sales_number }}</a>
                                @else
                                    <span class="text-muted">#{{ '—' }}</span>
                                @endif
                            </td>
                            <td>{{ $h['sale']->customer_name ?? '—' }}</td>
                            <td class="small">{{ $h['item'] }}</td>
                            <td>
                                @if($h['kind'] !== '—')
                                    <span class="kind-badge {{ $h['kind'] === 'Sublimation' ? 'kind-sublimation' : 'kind-garment' }}">{{ $h['kind'] }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="small">{{ $h['by'] }}</td>
                            <td class="small text-muted">{{ $h['at'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Wala pang checked na special price line.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@if($canCheck)
@push('scripts')
<script>
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.sp-review-btn');
        if (!btn) return;
        e.preventDefault();
        btn.disabled = true;
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving…';
        fetch('{{ route('sales.prototype.special-price.review') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ sale_id: btn.dataset.sale, line_key: btn.dataset.key })
        })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
        .then(function (res) {
            if (!res.ok || !res.d.success) { throw new Error((res.d && res.d.message) || 'Error'); }
            location.reload();
        })
        .catch(function (err) {
            btn.disabled = false;
            btn.innerHTML = original;
            alert('Hindi ma-save: ' + err.message);
        });
    });
</script>
@endpush
@endif
