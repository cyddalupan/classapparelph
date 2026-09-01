@extends('layouts.app')

@section('title', 'Pending Approval — Class Overload')

@push('styles')
<style>
    .main-content, .content-area { min-width: 0; }
    .pa-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }
    .pa-header h2 { margin: 0; font-size: 20px; }
    .pa-card {
        background: #fff;
        border: 1px solid #f1d6e4;
        border-left: 5px solid #be185d;
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .pa-empty {
        text-align: center;
        color: #6c757d;
        padding: 60px 20px;
        background: #fff;
        border-radius: 12px;
        border: 1px dashed #dee2e6;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="pa-header">
        <div>
            <h2>⏳ Pending Approval — Class Overload</h2>
            <div class="text-muted" style="font-size:13px;">
                {{ count($pendingApprovals) }} sale(s) na lampas sa 180 effective pcs/day at naghihintay ng approval.
                Hindi sila counted sa day load hangga't hindi naa-approve.
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.prototype.list') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Manager List
            </a>
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
                <a href="{{ route('sales.prototype.show', $pa->id) }}" target="_blank" style="font-weight:700;color:#0d6efd;font-size:15px;">
                    {{ $pa->sales_number ?: ('Sale #' . $pa->id) }}
                </a>
                <div class="text-muted" style="font-size:12px;margin-top:2px;">
                    {{ $pa->customer_name ?: '—' }} · {{ $pa->department_name }}
                    @if($pa->approval_requested_at)
                        · Requested {{ \Carbon\Carbon::parse($pa->approval_requested_at)->diffForHumans() }}
                    @endif
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge" style="background:#be185d;color:#fff;font-size:13px;padding:6px 12px;">~{{ $paEff }} eff pcs</span>
                <button type="button" class="btn btn-sm btn-success" onclick="overloadAction({{ $pa->id }}, 'approve', this)">✓ Approve</button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="overloadAction({{ $pa->id }}, 'reject', this)">✗ Reject</button>
            </div>
        </div>
    @empty
        <div class="pa-empty">
            <div style="font-size:40px;">✅</div>
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
