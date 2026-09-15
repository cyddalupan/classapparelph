@extends('layouts.app')

@section('title', 'Rejected & Cancelled — Review')

@push('styles')
<style>
    .main-content, .content-area { min-width: 0; }

    .rc-header {
        background: linear-gradient(135deg, #1f2937 0%, #334155 55%, #475569 100%);
        border-radius: 14px;
        padding: 20px 24px;
        color: #fff;
        box-shadow: 0 6px 18px rgba(51, 65, 85, 0.25);
        position: relative;
        overflow: hidden;
    }
    .rc-header::after {
        content: '♻️';
        position: absolute;
        right: 18px;
        bottom: -14px;
        font-size: 72px;
        opacity: 0.12;
        transform: rotate(-10deg);
    }
    .rc-header h2 { font-weight: 800; letter-spacing: 0.3px; margin: 0; }
    .rc-header .rc-sub { opacity: 0.85; font-size: 13px; }
    .rc-stat {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 10px;
        padding: 8px 14px;
        text-align: center;
        backdrop-filter: blur(4px);
        min-width: 90px;
    }
    .rc-stat .num { font-size: 1.35rem; font-weight: 800; line-height: 1; }
    .rc-stat .lbl { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; }

    .rc-section-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: #1f2937;
        margin: 26px 0 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .rc-section-title .rc-count-pill {
        font-size: 12px;
        font-weight: 700;
        border-radius: 20px;
        padding: 2px 10px;
        background: #e2e8f0;
        color: #334155;
    }

    .rc-card {
        border: 1px solid #e9ecef;
        border-left: 5px solid #6b7280;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform .15s ease, box-shadow .15s ease;
        padding: 14px 18px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .rc-card:hover { transform: translateY(-3px); box-shadow: 0 8px 22px rgba(51,65,85,0.10); }
    .rc-card.rc-cancelled { border-left-color: #be185d; }
    .rc-card.rc-change { border-left-color: #d97706; }
    .rc-card.rc-addon { border-left-color: #2563eb; }
    .rc-card .rc-sale-link { font-weight: 700; color: #0d6efd; font-size: 15px; text-decoration: none; }
    .rc-card .rc-sale-link:hover { text-decoration: underline; }
    .rc-meta { font-size: 12px; color: #6c757d; margin-top: 2px; }
    .rc-chip {
        display: inline-block;
        border-radius: 6px;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: 600;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
        margin-right: 4px;
        margin-top: 4px;
    }
    .rc-chip.rc-chip-pink { background: #fdf2f8; color: #be185d; border-color: #f9a8d4; }
    .rc-chip.rc-chip-amber { background: #fffbeb; color: #b45309; border-color: #fcd34d; }
    .rc-chip.rc-chip-blue { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .rc-chip.rc-chip-red { background: #fef2f2; color: #b91c1c; border-color: #fecaca; font-weight: 700; }
    .rc-chip.rc-chip-green { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .rc-reason { font-size: 12px; color: #b91c1c; margin-top: 4px; }
    .rc-reason i { opacity: 0.7; }
    .rc-action-btn { border-radius: 20px; padding: 6px 16px; font-size: 13px; font-weight: 600; border: none; transition: all .15s ease; }
    .rc-action-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .rc-empty {
        text-align: center;
        color: #6c757d;
        padding: 34px 20px;
        background: #fff;
        border-radius: 14px;
        border: 1px dashed #dee2e6;
    }
    .rc-empty i { font-size: 40px; color: #cbd5e1; display: block; margin-bottom: 10px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="rc-header mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <a href="{{ route('sales.prototype.list') }}" class="btn btn-sm text-white mb-2" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);"><i class="fas fa-arrow-left me-1"></i> Manager List</a>
                <h2 class="mb-1"><i class="fas fa-recycle me-2"></i>Rejected &amp; Cancelled</h2>
                <div class="rc-sub">Review at i-restore ang mga cancelled sales at rejected request — hindi na tuluyang mawawala</div>
            </div>
            <div class="d-flex gap-2">
                <div class="rc-stat">
                    <div class="num">{{ count($cancelledSales) }}</div>
                    <div class="lbl">Cancelled</div>
                </div>
                <div class="rc-stat">
                    <div class="num" style="color:#fcd34d;">{{ count($rejectedChanges) }}</div>
                    <div class="lbl">Rejected Change</div>
                </div>
                <div class="rc-stat">
                    <div class="num" style="color:#93c5fd;">{{ count($rejectedAddons) }}</div>
                    <div class="lbl">Rejected Add-on</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Cancelled Sales (overload rejects) ── --}}
    <div class="rc-section-title">
        <i class="fas fa-ban" style="color:#be185d;"></i> Cancelled Sales
        <span class="rc-count-pill">{{ count($cancelledSales) }}</span>
    </div>
    @forelse($cancelledSales as $s)
        @php
            $eff = $saleEff[$s->id] ?? 0;
            $need = $s->rescheduled_date ?: $s->estimated_completion_date;
            $needKey = $need ? \Carbon\Carbon::parse($need)->format('Y-m-d') : null;
            $load = $needKey ? ($dayLoads[$needKey] ?? 0) : 0;
            $projected = $load + $eff;
            $fits = $projected <= ($capacity ?? 180);
        @endphp
        <div class="rc-card rc-cancelled">
            <div>
                <a href="{{ route('sales.prototype.show', $s->id) }}" target="_blank" class="rc-sale-link">
                    {{ $s->sales_number ?: ('Sale #' . $s->id) }}
                </a>
                <div class="rc-meta">
                    {{ $s->customer_name ?: '—' }} · {{ $s->department_name }}
                </div>
                <div>
                    @if($needKey)
                        <span class="rc-chip rc-chip-blue"><i class="fas fa-calendar-alt me-1"></i>Need {{ \Carbon\Carbon::parse($needKey)->format('M d, Y') }}</span>
                        <span class="rc-chip {{ $fits ? 'rc-chip-green' : 'rc-chip-red' }}">
                            Load {{ $load }}/{{ $capacity ?? 180 }} → {{ $projected }}@if(!$fits) (+{{ $projected - ($capacity ?? 180) }} over)@endif
                        </span>
                    @else
                        <span class="rc-chip">Walang due date</span>
                    @endif
                    <span class="rc-chip rc-chip-pink">~{{ $eff }} eff pcs</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($fits)
                    <span class="rc-chip rc-chip-green" style="font-size:11px;">Kasya → babalik sa <b>Active</b></span>
                @else
                    <span class="rc-chip rc-chip-amber" style="font-size:11px;">Over pa → babalik sa <b>Pending Approval</b></span>
                @endif
                <button type="button" class="btn btn-success rc-action-btn" onclick="restoreCancelled({{ $s->id }}, this)">
                    <i class="fas fa-undo me-1"></i>Restore
                </button>
            </div>
        </div>
    @empty
        <div class="rc-empty">
            <i class="fas fa-check-circle"></i>
            <div style="font-weight:600;">Walang cancelled sale</div>
        </div>
    @endforelse

    {{-- ── Rejected Change / Reprocess requests ── --}}
    <div class="rc-section-title">
        <i class="fas fa-pen-to-square" style="color:#d97706;"></i> Rejected Change / Reprocess
        <span class="rc-count-pill">{{ count($rejectedChanges) }}</span>
    </div>
    @forelse($rejectedChanges as $c)
        @php
            $isReprocess = ($c->type ?? 'addition') === 'reprocess';
            $saleCancelled = ($c->sale_status ?? '') === 'cancelled';
        @endphp
        <div class="rc-card rc-change">
            <div>
                <a href="{{ route('sales.prototype.show', $c->sale_id) }}" target="_blank" class="rc-sale-link">
                    {{ $c->sales_number ?: ('Sale #' . $c->sale_id) }}
                </a>
                <div class="rc-meta">
                    {{ $c->customer_name ?: '—' }}
                    @if($c->submitted_by_name) · Requested by {{ $c->submitted_by_name }} @endif
                </div>
                <div>
                    <span class="rc-chip {{ $isReprocess ? 'rc-chip-amber' : 'rc-chip-blue' }}">
                        {{ $isReprocess ? 'Reprocess' : 'Add Product' }}
                    </span>
                    @if($c->change_summary)
                        <span class="rc-chip">{{ \Illuminate\Support\Str::limit($c->change_summary, 90) }}</span>
                    @endif
                    @if($c->total_before !== null && $c->total_after !== null)
                        <span class="rc-chip">₱{{ number_format((float)$c->total_before, 2) }} → ₱{{ number_format((float)$c->total_after, 2) }}</span>
                    @endif
                    @if($c->rejected_at)
                        <span class="rc-chip">Rejected {{ \Carbon\Carbon::parse($c->rejected_at)->format('M d, Y') }}</span>
                    @endif
                    @if($saleCancelled)
                        <span class="rc-chip rc-chip-red">Sale is cancelled</span>
                    @endif
                </div>
                @if($c->rejection_reason)
                    <div class="rc-reason"><i class="fas fa-circle-exclamation me-1"></i>Reason: {{ $c->rejection_reason }}</div>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-warning rc-action-btn" onclick="restoreChange({{ $c->change_id }}, this)"
                    @if($saleCancelled) disabled title="I-restore muna ang cancelled sale" @endif
                    style="color:#7c2d12;">
                    <i class="fas fa-undo me-1"></i>Restore
                </button>
            </div>
        </div>
    @empty
        <div class="rc-empty">
            <i class="fas fa-check-circle"></i>
            <div style="font-weight:600;">Walang rejected na change request</div>
        </div>
    @endforelse

    {{-- ── Rejected Add-ons ── --}}
    <div class="rc-section-title">
        <i class="fas fa-plus-circle" style="color:#2563eb;"></i> Rejected Add-ons
        <span class="rc-count-pill">{{ count($rejectedAddons) }}</span>
    </div>
    @forelse($rejectedAddons as $a)
        @php
            $items = is_string($a->requested_items) ? json_decode($a->requested_items, true) : ($a->requested_items ?? []);
            $itemCount = is_array($items) ? count($items) : 0;
            $saleCancelled = ($a->sale_status ?? '') === 'cancelled';
        @endphp
        <div class="rc-card rc-addon">
            <div>
                <a href="{{ route('sales.prototype.show', $a->sale_id) }}" target="_blank" class="rc-sale-link">
                    {{ $a->sales_number ?: ('Sale #' . $a->sale_id) }}
                </a>
                <div class="rc-meta">
                    {{ $a->customer_name ?: '—' }}
                    @if($a->requested_by) · Requested by {{ $a->requested_by }} @endif
                </div>
                <div>
                    <span class="rc-chip rc-chip-blue">{{ $itemCount }} item(s)</span>
                    @if($a->reason)
                        <span class="rc-chip">{{ \Illuminate\Support\Str::limit($a->reason, 90) }}</span>
                    @endif
                    @if($saleCancelled)
                        <span class="rc-chip rc-chip-red">Sale is cancelled</span>
                    @endif
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-primary rc-action-btn" onclick="restoreAddon({{ $a->addon_id }}, this)"
                    @if($saleCancelled) disabled title="I-restore muna ang cancelled sale" @endif>
                    <i class="fas fa-undo me-1"></i>Restore
                </button>
            </div>
        </div>
    @empty
        <div class="rc-empty">
            <i class="fas fa-check-circle"></i>
            <div style="font-weight:600;">Walang rejected na add-on</div>
        </div>
    @endforelse

</div>
@endsection

@push('scripts')
<script>
function rcToast(msg, type) {
    var existing = document.getElementById('rcToast');
    if (existing) existing.remove();
    var toast = document.createElement('div');
    toast.id = 'rcToast';
    toast.textContent = msg;
    toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:' + (type === 'error' ? '#dc3545' : '#198754') + ';color:#fff;padding:12px 20px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.2);font-size:14px;font-weight:600;max-width:380px;transition:opacity 0.3s;';
    document.body.appendChild(toast);
    setTimeout(function() { toast.style.opacity = '0'; setTimeout(function() { toast.remove(); }, 300); }, 3000);
}

function rcPost(url, btn, confirmMsg, successMsg) {
    if (!confirm(confirmMsg)) return;
    if (btn) btn.disabled = true;
    var csrf = document.querySelector('meta[name="csrf-token"]');
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({})
    })
    .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, d: d }; }); })
    .then(function(res) {
        var d = res.d || {};
        if (d.success) {
            rcToast('✅ ' + (d.message || successMsg), 'success');
            setTimeout(function() { window.location.reload(); }, 900);
        } else {
            rcToast(d.message || d.error || 'Action failed.', 'error');
            if (btn) btn.disabled = false;
        }
    })
    .catch(function() {
        rcToast('Network error — please try again.', 'error');
        if (btn) btn.disabled = false;
    });
}

function restoreCancelled(id, btn) {
    rcPost('/sales/prototype/' + id + '/restore-cancelled', btn,
        'I-restore ang cancelled sale na ito? (Kung over capacity pa, babalik ito sa Pending Approval.)',
        'Na-restore ang sale.');
}
function restoreChange(id, btn) {
    rcPost('/sales/prototype/change/' + id + '/restore', btn,
        'I-restore ang rejected request na ito? Babalik ito sa pending approval queue.',
        'Na-restore ang request.');
}
function restoreAddon(id, btn) {
    rcPost('/sales/prototype/addon/' + id + '/restore', btn,
        'I-restore ang rejected add-on na ito? Babalik ito sa pending approval queue.',
        'Na-restore ang add-on.');
}
</script>
@endpush
