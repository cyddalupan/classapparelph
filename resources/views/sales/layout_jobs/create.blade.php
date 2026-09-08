@extends('layouts.app')

@section('title', 'New Layout Job')

@push('styles')
<style>
    .lj-create-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #2563eb 100%);
        border-radius: 16px;
        padding: 18px 24px;
        color: #fff;
        box-shadow: 0 8px 24px rgba(30, 58, 138, .2);
    }
    .lj-create-hero h4 { font-weight: 800; }
    .lj-card {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .04);
    }
    .lj-type-option {
        border: 2px solid #e5e7eb;
        border-radius: 14px;
        padding: 16px;
        cursor: pointer;
        text-align: center;
        transition: all .15s ease;
        background: #fff;
    }
    .lj-type-option:hover { border-color: #93c5fd; background: #f8fafc; }
    .lj-type-option.active { border-color: #2563eb; background: #eff6ff; box-shadow: 0 4px 14px rgba(37, 99, 235, .15); }
    .lj-type-option .icon { font-size: 26px; }
    .lj-type-option .ttl { font-weight: 700; font-size: 15px; color: #111827; }
    .lj-type-option .dsc { font-size: 11.5px; color: #6b7280; }
    .lj-paybox {
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        padding: 14px;
        background: #f8fafc;
    }
    .lj-required { color: #dc2626; }

    /* Toast notification (katulad ng sales create) */
    .notification-toast {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1.25rem 1.5rem;
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,.1), 0 10px 10px -5px rgba(0,0,0,.04);
        border-left: 6px solid #3b82f6;
        min-width: 340px;
        max-width: 460px;
        animation: fadeInScale .3s ease-out;
    }
    @keyframes fadeInScale {
        from { transform: translate(-50%, -50%) scale(.9); opacity: 0; }
        to { transform: translate(-50%, -50%) scale(1); opacity: 1; }
    }
    .notification-toast.success { border-left-color: #10b981; }
    .notification-toast.error { border-left-color: #ef4444; }
    .notification-icon {
        width: 32px; height: 32px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
    }
    .notification-toast .notification-icon { background: rgba(59,130,246,.1); color: #3b82f6; }
    .notification-toast.success .notification-icon { background: rgba(16,185,129,.1); color: #10b981; }
    .notification-toast.error .notification-icon { background: rgba(239,68,68,.1); color: #ef4444; }
    .notification-content { flex: 1; }
    .notification-message { font-size: .9375rem; color: #1e293b; line-height: 1.4; }
    .notification-close {
        background: none; border: none; color: #94a3b8; cursor: pointer;
        padding: .25rem; border-radius: .25rem;
    }
    .notification-close:hover { color: #64748b; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3" style="max-width: 900px;">
    <a href="{{ route('sales.layout-jobs') }}" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left"></i> Back to Layout Jobs</a>

    <div class="lj-create-hero mb-3">
        <h4 class="mb-1">🎨 New Layout Job</h4>
        <div class="opacity-75" style="font-size:12.5px;">Bayad (client nagbayad ng layout fee) o Libre (waived — walang threshold). I-tag ang Layout Doer na gagawa.</div>
    </div>

    <div class="lj-card p-4">
        <form id="layoutJobForm" enctype="multipart/form-data">
            @csrf

            {{-- Type toggle --}}
            <label class="form-label fw-semibold">Type ng Layout <span class="lj-required">*</span></label>
            <div class="row g-2 mb-3" id="typeOptions">
                <div class="col-6">
                    <div class="lj-type-option active" data-type="paid" onclick="selectType('paid')">
                        <div class="icon">💵</div>
                        <div class="ttl">Bayad</div>
                        <div class="dsc">May layout fee — mag-u-upload ng payment, reference, at image</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="lj-type-option" data-type="free" onclick="selectType('free')">
                        <div class="icon">🎁</div>
                        <div class="ttl">Libre</div>
                        <div class="dsc">Waived — walang payment, walang threshold. I-tag lang ang GA</div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="type" id="typeVal" value="paid">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Customer Name <span class="lj-required">*</span></label>
                    <input type="text" name="customer_name" class="form-control" required placeholder="Pangalan ng client / company">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Layout Doer (tag) <span class="lj-required">*</span></label>
                    <select name="ga_user_id" class="form-select" required>
                        <option value="">— piliin ang GA —</option>
                        @foreach($gaUsers as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Pag na-save, mapupunta sa kanya ang job info.</div>
                </div>

                <div class="col-12">
                    <label class="form-label">Layout / Design Info</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Hal: polo button design, NAOHJ logo, 2 colors, front & back..."></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Reference Image (design na gagayahin)</label>
                    <input type="file" name="reference_image" class="form-control" accept="image/*">
                    <div class="form-text">Optional pero recommended — para may basehan ang GA.</div>
                </div>
            </div>

            {{-- PAID fields (conditional) --}}
            <div id="paidFields" class="mt-3">
                <div class="lj-paybox">
                    <div class="fw-semibold mb-2">💵 Payment Details — Bayad na Layout</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Layout Fee (₱) <span class="lj-required">*</span></label>
                            <input type="number" name="amount" class="form-control" min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Saan Binayad / Payment Account <span class="lj-required">*</span></label>
                            <select name="payment_account_id" class="form-select">
                                <option value="">— piliin ang account —</option>
                                @foreach($paymentAccounts ?? [] as $pa)
                                <option value="{{ $pa->id }}">{{ $pa->name }}@if($pa->account_number) ({{ $pa->account_number }})@endif</option>
                                @endforeach
                            </select>
                            <div class="form-text">Account kung saan binayad ni client — ang may-ari nito ang magve-verify.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Reference #</label>
                            <input type="text" name="payment_reference" class="form-control" placeholder="Transaction / ref #">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Payment Screenshot <span class="lj-required">*</span></label>
                            <input type="file" name="payment_screenshot" class="form-control" accept="image/*">
                            <div class="form-text">Screenshot ng bayad ni client (GCash/bank transfer receipt).</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('sales.layout-jobs') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-paper-plane"></i> Create Layout Job</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function selectType(type) {
    document.querySelectorAll('.lj-type-option').forEach(el => el.classList.toggle('active', el.dataset.type === type));
    document.getElementById('typeVal').value = type;
    document.getElementById('paidFields').style.display = type === 'paid' ? '' : 'none';
}
document.getElementById('paidFields').style.display = '';

// Toast notification (katulad ng sales create quick page)
function showNotification(message, type = 'success') {
    const existing = document.querySelectorAll('.notification-toast');
    existing.forEach(n => n.remove());

    const notification = document.createElement('div');
    notification.className = `notification-toast ${type}`;
    notification.innerHTML = `
        <div class="notification-icon">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        </div>
        <div class="notification-content">
            <div class="notification-message">${message}</div>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.body.appendChild(notification);
    setTimeout(() => { if (notification.parentElement) notification.remove(); }, 5000);
}

document.getElementById('layoutJobForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = this.querySelector('button[type=submit]');
    btn.disabled = true;

    const fd = new FormData(this);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch('/sales/layout-jobs', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
        body: fd
    }).then(r => r.json()).then(d => {
        if (d.error) {
            showNotification(d.error, 'error');
            btn.disabled = false;
            return;
        }
        btn.innerHTML = '<i class="fas fa-check"></i> Created ✓';
        showNotification('Layout Job <strong>' + d.job_no + '</strong> created ✓ — nasa Layout Jobs na ito, at kung bayad, nasa Payment Verification na ng account owner.', 'success');
        setTimeout(() => { window.location.href = '/sales/layout-jobs'; }, 1800);
    }).catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Create Layout Job';
        showNotification('May error — subukan ulit.', 'error');
    });
});
</script>
@endpush
