@extends('layouts.app')

@section('title', 'Pending Approval — Class Overload')

@push('styles')
<style>
    .main-content, .content-area { min-width: 0; }

    /* Header — same gradient style as Delay List, but rose/pink theme */
    .pa-header {
        background: linear-gradient(135deg, #3b0d26 0%, #7c1d4d 60%, #be185d 100%);
        border-radius: 14px;
        padding: 20px 24px;
        color: #fff;
        box-shadow: 0 6px 18px rgba(190, 24, 93, 0.22);
        position: relative;
        overflow: hidden;
    }
    .pa-header::after {
        content: '⏳';
        position: absolute;
        right: 18px;
        bottom: -14px;
        font-size: 72px;
        opacity: 0.12;
        transform: rotate(-10deg);
    }
    .pa-header h2 { font-weight: 800; letter-spacing: 0.3px; margin: 0; }
    .pa-header .pa-sub { opacity: 0.85; font-size: 13px; }
    .pa-stat {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 10px;
        padding: 8px 14px;
        text-align: center;
        backdrop-filter: blur(4px);
        min-width: 90px;
    }
    .pa-stat .num { font-size: 1.35rem; font-weight: 800; line-height: 1; }
    .pa-stat .lbl { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; }

    /* Cards — same as delay list cards */
    .pa-card {
        border: 1px solid #e9ecef;
        border-left: 5px solid #be185d;
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
    .pa-card:hover { transform: translateY(-3px); box-shadow: 0 8px 22px rgba(190,24,93,0.10); }
    .pa-card .pa-sale-link {
        font-weight: 700;
        color: #0d6efd;
        font-size: 15px;
        text-decoration: none;
    }
    .pa-card .pa-sale-link:hover { text-decoration: underline; }
    .pa-meta { font-size: 12px; color: #6c757d; margin-top: 2px; }
    .pa-eff-badge {
        background: #be185d;
        color: #fff;
        font-size: 13px;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 700;
        box-shadow: 0 3px 8px rgba(190,24,93,0.25);
        white-space: nowrap;
    }
    .pa-req-chip {
        display: inline-block;
        border-radius: 6px;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: 600;
        background: #fdf2f8;
        color: #be185d;
        border: 1px solid #f9a8d4;
    }
    .pa-action-btn {
        border-radius: 20px;
        padding: 6px 16px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        transition: all .15s ease;
    }
    .pa-action-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

    /* Empty state */
    .pa-empty {
        text-align: center;
        color: #6c757d;
        padding: 60px 20px;
        background: #fff;
        border-radius: 14px;
        border: 1px dashed #dee2e6;
    }
    .pa-empty i { font-size: 48px; color: #f9a8d4; display: block; margin-bottom: 12px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="pa-header mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <a href="{{ route('sales.prototype.list') }}" class="btn btn-sm text-white mb-2" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);"><i class="fas fa-arrow-left me-1"></i> Manager List</a>
                <h2 class="mb-1"><i class="fas fa-hourglass-half me-2"></i>Pending Approval</h2>
                <div class="pa-sub">Class overload sales — lampas sa 180 effective pcs/day at naghihintay ng approval</div>
            </div>
            <div class="d-flex gap-2">
                <div class="pa-stat">
                    <div class="num">{{ count($pendingApprovals) }}</div>
                    <div class="lbl">Pending</div>
                </div>
                <div class="pa-stat">
                    <div class="num" style="color:#7ef0a3;">
                        @php
                            $paTotalEff = 0;
                            foreach ($pendingApprovals as $pa) {
                                $paSvc = is_string($pa->services) ? json_decode($pa->services, true) : ($pa->services ?? []);
                                foreach ($paSvc as $pi) {
                                    $pg = strtoupper(trim($pi['sublimationForm']['garment']['name'] ?? ''));
                                    $pq = (int)($pi['quantity'] ?? $pi['qty'] ?? 1) ?: 1;
                                    if (in_array($pg, ['TSHIRT ROUNDNECK', 'TSHIRT VNECK', 'JERSEY UP'])) $paTotalEff += $pq;
                                    elseif ($pg === 'JERSEY UP AND DOWN') $paTotalEff += $pq * 2;
                                    else $paTotalEff += $pq;
                                }
                            }
                        @endphp
                        {{ $paTotalEff }}
                    </div>
                    <div class="lbl">Eff Pcs</div>
                </div>
            </div>
        </div>
    </div>

    @forelse($pendingApprovals as $pa)
        @php
            $paSvc = is_string($pa->services) ? json_decode($pa->services, true) : ($pa->services ?? []);
            $paEff = 0;
            foreach ($paSvc as $pi) {
                $pg = strtoupper(trim($pi['sublimationForm']['garment']['name'] ?? ''));
                $pq = (int)($pi['quantity'] ?? $pi['qty'] ?? 1) ?: 1;
                if (in_array($pg, ['TSHIRT ROUNDNECK', 'TSHIRT VNECK', 'JERSEY UP'])) $paEff += $pq;
                elseif ($pg === 'JERSEY UP AND DOWN') $paEff += $pq * 2;
                else $paEff += $pq;
            }
        @endphp
        <div class="pa-card">
            <div>
                <a href="{{ route('sales.prototype.show', $pa->id) }}" target="_blank" class="pa-sale-link">
                    {{ $pa->sales_number ?: ('Sale #' . $pa->id) }}
                </a>
                <div class="pa-meta">
                    {{ $pa->customer_name ?: '—' }} · {{ $pa->department_name }}
                    @if($pa->approval_requested_at)
                        <span class="pa-req-chip ms-1"><i class="fas fa-clock me-1"></i>Requested {{ \Carbon\Carbon::parse($pa->approval_requested_at)->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="pa-eff-badge">~{{ $paEff }} eff pcs</span>
                <button type="button" class="btn btn-success pa-action-btn" onclick="overloadAction({{ $pa->id }}, 'approve', this)">✓ Approve</button>
                <button type="button" class="btn btn-outline-danger pa-action-btn" onclick="overloadAction({{ $pa->id }}, 'reject', this)">✗ Reject</button>
            </div>
        </div>
    @empty
        <div class="pa-empty">
            <i class="fas fa-check-circle"></i>
            <div style="font-weight:600;margin-top:8px;">Wala pang pending approval</div>
            <div style="font-size:13px;">Lalabas dito ang Class sales na lampas sa 180 effective pcs/day.</div>
        </div>
    @endforelse

</div>
@endsection

@push('scripts')
<script>
function showToast(msg, type) {
    var existing = document.getElementById('notifyToast');
    if (existing) existing.remove();
    var toast = document.createElement('div');
    toast.id = 'notifyToast';
    toast.textContent = msg;
    toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:' + (type === 'error' ? '#dc3545' : '#198754') + ';color:#fff;padding:12px 20px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.2);font-size:14px;font-weight:600;max-width:360px;transition:opacity 0.3s;';
    document.body.appendChild(toast);
    setTimeout(function() { toast.style.opacity = '0'; setTimeout(function() { toast.remove(); }, 300); }, 2500);
}

// Phase 3: approve/reject an overloaded Class sale (pending_approval)
function overloadAction(saleId, action, btn) {
    if (!confirm(action === 'approve' ? 'Approve this overloaded sale? It will count toward the day load.' : 'Reject this overloaded sale? It will be cancelled.')) return;
    if (btn) { btn.disabled = true; }
    var csrf = document.querySelector('meta[name="csrf-token"]');
    fetch('/sales/prototype/' + saleId + '/' + (action === 'approve' ? 'approve-overload' : 'reject-overload'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({})
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            showToast('✅ ' + d.message, 'success');
            setTimeout(function() { window.location.reload(); }, 800);
        } else {
            showToast(d.message || 'Action failed.', 'error');
            if (btn) { btn.disabled = false; }
        }
    })
    .catch(function() {
        showToast('Network error — please try again.', 'error');
        if (btn) { btn.disabled = false; }
    });
}
</script>
@endpush
