@extends('layouts.app')

@section('title', 'Pending Add-ons — Review')

@push('styles')
<style>
    .main-content, .content-area { min-width: 0; }

    .pa-header {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 55%, #115e59 100%);
        border-radius: 14px;
        padding: 20px 24px;
        color: #fff;
        box-shadow: 0 6px 18px rgba(13, 148, 136, 0.25);
        position: relative;
        overflow: hidden;
    }
    .pa-header::after {
        content: '➕';
        position: absolute;
        right: 18px;
        bottom: -14px;
        font-size: 72px;
        opacity: 0.12;
        transform: rotate(-8deg);
    }
    .pa-header h2 { font-weight: 800; letter-spacing: 0.3px; margin: 0; }
    .pa-header .pa-sub { opacity: 0.88; font-size: 13px; }
    .pa-stat {
        background: rgba(255,255,255,0.14);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 10px;
        padding: 8px 14px;
        text-align: center;
        min-width: 96px;
    }
    .pa-stat .num { font-size: 1.35rem; font-weight: 800; line-height: 1; }
    .pa-stat .lbl { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; }

    .pa-card {
        border: 1px solid #e9ecef;
        border-left-width: 5px;
        border-radius: 12px;
        background: #fff;
        transition: box-shadow .15s ease, transform .15s ease;
    }
    .pa-card:hover { box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08); transform: translateY(-1px); }
    .pa-card.stale { border-left-color: #dc3545; }
    .pa-card.fresh { border-left-color: #f0ad4e; }
    .pa-card .pa-title { font-weight: 700; color: #0f172a; }
    .pa-card .pa-meta { font-size: 12.5px; color: #64748b; }
    .pa-empty { padding: 48px 16px; text-align: center; color: #64748b; }
    .pa-badge-source {
        font-size: 11px; font-weight: 700; border-radius: 20px; padding: 2px 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="pa-header mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 position-relative" style="z-index:1;">
            <div>
                <h2><i class="fas fa-plus-circle me-2"></i>Pending Add-ons</h2>
                <div class="pa-sub mt-1">Lahat ng add-on at "Add Product" (change) requests na naghihintay ng approval.</div>
            </div>
            <div class="d-flex gap-3">
                <div class="pa-stat"><div class="num">{{ $requests->count() }}</div><div class="lbl">Total Pending</div></div>
                <div class="pa-stat"><div class="num">{{ $requests->where('source', 'addon')->count() }}</div><div class="lbl">Add-ons</div></div>
                <div class="pa-stat"><div class="num">{{ $requests->where('source', 'change')->count() }}</div><div class="lbl">Add Product</div></div>
            </div>
        </div>
    </div>

    @if($requests->isEmpty())
        <div class="card pa-card">
            <div class="pa-empty">
                <i class="fas fa-check-circle fa-3x mb-3" style="color:#28a745;"></i>
                <p class="mb-0">Walang pending add-on requests. 🎉</p>
            </div>
        </div>
    @else
        <div class="row g-3" id="paList">
            @foreach($requests as $req)
                @php
                    $ageH = (float) ($req->age_hours ?? 0);
                    $ageLabel = $ageH < 1 ? '< 1h ago' : ($ageH < 24 ? round($ageH) . 'h ago' : round($ageH / 24) . 'd ago');
                    $stale = $ageH >= 24;
                    $isChange = ($req->source ?? '') === 'change';
                @endphp
                <div class="col-12 col-lg-6">
                    <div class="pa-card {{ $stale ? 'stale' : 'fresh' }} p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div class="flex-grow-1" style="min-width:0;">
                                <div class="pa-title mb-1">
                                    {{ $req->sales_number ?? '—' }} — {{ $req->customer_name ?? 'Unknown' }}
                                    <span class="pa-badge-source {{ $isChange ? 'bg-info text-dark' : 'bg-secondary text-white' }} ms-1">
                                        {{ $isChange ? '📝 Add Product' : '➕ Add-on' }}
                                    </span>
                                    <span class="badge {{ $stale ? 'bg-danger' : 'bg-light text-dark border' }} ms-1" title="Waiting {{ $ageLabel }}">{{ $stale ? '⏰ ' : '' }}{{ $ageLabel }}</span>
                                </div>

                                @if($isChange)
                                    @php $diff = (float) $req->total_after - (float) $req->total_before; @endphp
                                    <div class="pa-meta">{{ $req->change_summary ?? 'Change request' }}</div>
                                    <div class="pa-meta">
                                        ₱{{ number_format((float) $req->total_before, 2) }} →
                                        <strong>₱{{ number_format((float) $req->total_after, 2) }}</strong>
                                        ({{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 2) }})
                                    </div>
                                @else
                                    @php
                                        $items = is_array($req->items ?? null) ? $req->items : [];
                                        $labels = [];
                                        foreach ($items as $it) {
                                            $sub = $it['subItems'][0] ?? null;
                                            $labels[] = $sub
                                                ? trim(($sub['brand'] ?? '') . ' ' . ($sub['size'] ?? '') . ' ' . ($sub['color'] ?? '')) . ' ×' . ($it['totalQty'] ?? '')
                                                : (($it['name'] ?? 'Item') . ' ×' . ($it['totalQty'] ?? ''));
                                        }
                                    @endphp
                                    <div class="pa-meta">{{ implode(', ', $labels) }}</div>
                                    @if(!empty($req->reason))
                                        <div class="pa-meta">Reason: {{ $req->reason }}</div>
                                    @endif
                                @endif

                                <div class="pa-meta mt-1">
                                    By: {{ $req->requested_by ?? 'Agent' }} •
                                    {{ \Illuminate\Support\Carbon::parse($req->created_at)->timezone('Asia/Manila')->format('M j, Y g:i A') }}
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-1 text-end" style="min-width:110px;">
                                @if($isManager)
                                    @if($isChange)
                                        <button class="btn btn-success btn-sm" onclick="paApproveChange({{ $req->id }}, {{ $req->sale_id }})">✅ Approve</button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="paRejectChange({{ $req->id }}, {{ $req->sale_id }})">❌ Reject</button>
                                    @else
                                        <button class="btn btn-success btn-sm" onclick="paApproveAddon({{ $req->id }}, {{ $req->sale_id }})">✅ Approve</button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="paRejectAddon({{ $req->id }}, {{ $req->sale_id }})">❌ Reject</button>
                                    @endif
                                @endif
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('sales.prototype.show', $req->sale_id) }}">🔎 View Sale</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
(function () {
    var CSRF = '{{ csrf_token() }}';

    function toast(msg, type) {
        type = type || 'info';
        var el = document.createElement('div');
        var colors = { success: '#d4edda,#155724', danger: '#f8d7da,#721c24', warning: '#fff3cd,#856404', info: '#d1ecf1,#0c5460' };
        var c = (colors[type] || colors.info).split(',');
        el.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;padding:12px 20px;border-radius:8px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.15);max-width:400px;background:' + c[0] + ';color:' + c[1] + ';';
        el.innerHTML = msg;
        document.body.appendChild(el);
        setTimeout(function () { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; setTimeout(function () { el.remove(); }, 300); }, 4500);
    }

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(body || {})
        }).then(function (r) { return r.json().catch(function () { return {}; }); });
    }

    function reloadSoon() { setTimeout(function () { window.location.reload(); }, 600); }

    window.paApproveAddon = function (id, saleId) {
        if (!confirm('Approve this add-on request? Pricing will be recalculated.')) return;
        post('/sales/prototype/addon/' + id + '/approve', { approved_by: 'Manager' })
            .then(function (d) {
                if (d.success) { toast('✅ Add-on approved.' + (d.new_subtotal != null ? ' New subtotal: ₱' + Number(d.new_subtotal).toFixed(2) : ''), 'success'); reloadSoon(); }
                else toast('Error: ' + (d.error || 'Unknown'), 'danger');
            }).catch(function (e) { toast('Failed: ' + e.message, 'danger'); });
    };

    window.paRejectAddon = function (id, saleId) {
        if (!confirm('Reject this add-on request?')) return;
        post('/sales/prototype/addon/' + id + '/reject', { approved_by: 'Manager' })
            .then(function (d) {
                if (d.success) { toast('❌ Add-on rejected.', 'success'); reloadSoon(); }
                else toast(d.error || 'Failed to reject.', 'danger');
            }).catch(function (e) { toast('Failed: ' + e.message, 'danger'); });
    };

    window.paApproveChange = function (id, saleId) {
        if (!confirm('Approve this Add Product change request? Services and total will be updated.')) return;
        post('/sales/prototype/change/' + id + '/approve', {})
            .then(function (d) {
                if (d.success) { toast('✅ ' + (d.message || 'Change approved.'), 'success'); reloadSoon(); }
                else toast(d.message || 'Failed to approve.', 'danger');
            }).catch(function (e) { toast('Failed: ' + e.message, 'danger'); });
    };

    window.paRejectChange = function (id, saleId) {
        var reason = prompt('Rejection reason (min 5 characters):');
        if (reason === null) return;
        reason = (reason || '').trim();
        if (reason.length < 5) { toast('Rejection reason must be at least 5 characters.', 'danger'); return; }
        post('/sales/prototype/change/' + id + '/reject', { reason: reason })
            .then(function (d) {
                if (d.success) { toast('❌ Change request rejected.', 'success'); reloadSoon(); }
                else toast(d.message || d.error || 'Failed to reject.', 'danger');
            }).catch(function (e) { toast('Failed: ' + e.message, 'danger'); });
    };
})();
</script>
@endpush
@endsection
