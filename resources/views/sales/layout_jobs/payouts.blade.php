@extends('layouts.app')

@section('title', 'Layout Payout Requests')

@push('styles')
<style>
    .lj-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #2563eb 100%);
        border-radius: 16px; padding: 20px 26px; color: #fff; position: relative;
        overflow: hidden; box-shadow: 0 8px 24px rgba(30, 58, 138, .25);
    }
    .lj-hero::after {
        content: "💸"; position: absolute; right: 18px; bottom: -18px;
        font-size: 84px; opacity: .16; transform: rotate(-8deg);
    }
    .lj-hero h4 { font-weight: 800; letter-spacing: .3px; }
    .lj-hero .sub { opacity: .92; font-size: 12.5px; }
    .lj-stat {
        background: #fff; border: 1px solid #eef0f4; border-radius: 14px; padding: 12px 16px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .05); height: 100%;
    }
    .lj-stat .val { font-size: 22px; font-weight: 800; line-height: 1.1; color: #111827; }
    .lj-stat .lbl { font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
    .lj-card {
        background: #fff; border: 1px solid #eef0f4; border-radius: 14px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .04); overflow: hidden;
    }
    .lj-badge { font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 20px; letter-spacing: .3px; white-space: nowrap; }
    .lj-badge.pending { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
    .lj-badge.verified { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .lj-badge.rejected { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .lj-badge.requested { background: #fefce8; color: #a16207; border: 1px solid #fef08a; }
    .lj-muted { color: #6b7280; font-size: .8rem; }
    .lj-amount { font-size: 15px; font-weight: 800; color: #111827; }
    .lj-btn-mini { font-size: 11.5px; padding: 3px 10px; border-radius: 8px; font-weight: 600; }
    .lj-thumb { width: 46px; height: 46px; border-radius: 10px; object-fit: cover; border: 1px solid #e5e7eb; background: #f9fafb; cursor: pointer; }
    .modal-img-preview { max-width: 100%; max-height: 240px; border-radius: 10px; border: 1px solid #e5e7eb; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="lj-hero mb-3">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
            <div>
                <h4 class="mb-0">💸 Layout Payout Requests</h4>
                <div class="sub">Mga payout request ng layout-doers — i-check ang account details at bayaran. Bawas agad sa available credit nila pag na-request.</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('sales.layout-jobs.all') }}" class="btn btn-outline-light btn-sm"><i class="fas fa-list"></i> Layout Job List (All)</a>
                <a href="{{ route('sales.layout-jobs.dashboard') }}" class="btn btn-outline-light btn-sm"><i class="fas fa-chart-line"></i> Layout Dashboard</a>
            </div>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <div class="lj-stat">
                <div class="lbl">Pending Requests</div>
                <div class="val">{{ $pending->count() }}</div>
                <div class="lj-muted">requested / paid (hinihintay pa ang payout)</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="lj-stat">
                <div class="lbl">Pending Amount</div>
                <div class="val">₱{{ number_format($pending->sum('amount'), 2) }}</div>
                <div class="lj-muted">kabuuang halagang hinihintay bayaran</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="lj-stat">
                <div class="lbl">Bayad na (history)</div>
                <div class="val">{{ $history->where('status', 'verified')->count() }}</div>
                <div class="lj-muted">verified payouts sa huling 100 records</div>
            </div>
        </div>
    </div>

    <div class="lj-card mb-3">
        <div class="p-3">
            <h6 class="mb-1">⏳ Pending Payout Requests</h6>
            <div class="lj-muted mb-2">Piliin ang account na pinambayad, ilagay ang reference at proof — o i-verify &amp; pay agad.</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th><th>Layout Doer</th><th>Amount</th><th>Account Details</th><th>Payment</th>
                            <th>Notes</th><th>Status</th><th>Requested</th><th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($pending as $p)
                        <tr>
                            <td>#{{ $p->id }}</td>
                            <td>
                                @if($p->gaUser)
                                    <x-user-chip :user="$p->gaUser" :size="18" />
                                @else
                                    —
                                @endif
                            </td>
                            <td class="lj-amount">₱{{ number_format($p->amount, 2) }}</td>
                            <td>
                                <div>{{ $p->account_name ?: '—' }} {{ $p->account_number ? '· ' . $p->account_number : '' }}</div>
                                @if($p->account_proof_path)
                                <a href="javascript:void(0)" class="lj-muted" onclick="showImage('{{ asset('storage/' . $p->account_proof_path) }}')"><i class="fas fa-image"></i> account proof</a>
                                @endif
                            </td>
                            <td>
                                @if($p->payment_proof_path)
                                <img src="{{ asset('storage/' . $p->payment_proof_path) }}" class="lj-thumb" title="Payment proof — i-click para palakihin" onclick="showImage('{{ asset('storage/' . $p->payment_proof_path) }}')">
                                <div class="lj-muted" style="font-size:10.5px;">{{ $p->payment_method ?: '—' }}{{ $p->payment_reference ? ' · ' . $p->payment_reference : '' }}</div>
                                @else
                                <span class="lj-muted">— wala pa</span>
                                @endif
                            </td>
                            <td class="lj-muted" style="max-width:220px;">{{ $p->request_notes ?: '—' }}</td>
                            <td><span class="lj-badge {{ $p->status === 'paid' ? 'pending' : 'requested' }}">{{ $p->status }}</span></td>
                            <td class="lj-muted">{{ $p->requested_at?->format('M d, Y h:i A') }}</td>
                            <td class="text-end">
                                @if($p->status === 'requested')
                                <button class="btn btn-sm btn-warning lj-btn-mini" onclick="openPayPayout({{ $p->id }})">💸 Pay</button>
                                @elseif($p->status === 'paid')
                                {{-- Bayad na — walang Pay button. Verify na lang para ma-close. --}}
                                <button class="btn btn-sm btn-success lj-btn-mini" onclick="verifyPaidPayout({{ $p->id }}, '{{ addslashes($p->payment_method ?? '') }}', '{{ addslashes($p->payment_reference ?? '') }}')">✅ Verify</button>
                                @else
                                <span class="lj-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center lj-muted py-4">Walang pending payout request ngayon. 👍</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="lj-card mb-3">
        <div class="p-3">
            <h6 class="mb-1">📜 Payout History</h6>
            <div class="lj-muted mb-2">Huling 100 na verified / rejected na payout.</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>#</th><th>Layout Doer</th><th>Amount</th><th>Account Details</th><th>Status</th><th>Paid / Reason</th><th>Received</th><th>Requested</th></tr>
                    </thead>
                    <tbody>
                    @forelse($history as $p)
                        <tr>
                            <td>#{{ $p->id }}</td>
                            <td>
                                @if($p->gaUser)
                                    <x-user-chip :user="$p->gaUser" :size="18" />
                                @else
                                    —
                                @endif
                            </td>
                            <td class="lj-amount">₱{{ number_format($p->amount, 2) }}</td>
                            <td>
                                <div>{{ $p->account_name ?: '—' }} {{ $p->account_number ? '· ' . $p->account_number : '' }}</div>
                                @if($p->payment_method)
                                <span class="lj-muted"><i class="fas fa-wallet"></i> {{ $p->payment_method }}{{ $p->payment_reference ? ' · ' . $p->payment_reference : '' }}</span>
                                @endif
                                @if($p->payment_proof_path)
                                <a href="javascript:void(0)" onclick="showImage('{{ asset('storage/' . $p->payment_proof_path) }}')">
                                    <img src="{{ asset('storage/' . $p->payment_proof_path) }}" class="lj-thumb" title="Payment proof">
                                </a>
                                @endif
                            </td>
                            <td><span class="lj-badge {{ $p->status === 'verified' ? 'verified' : 'rejected' }}">{{ $p->status }}</span></td>
                            <td class="lj-muted" style="max-width:220px;">
                                @if($p->status === 'rejected')
                                    <span class="text-danger">✗ {{ $p->reject_reason ?: 'No reason' }}</span>
                                @else
                                    {{ $p->paid_at?->format('M d, Y h:i A') ?: '—' }}
                                @endif
                            </td>
                            <td class="lj-muted">
                                @if($p->received_at)
                                    @if($p->receivedBy)
                                        <x-user-chip :user="$p->receivedBy" :size="16" />
                                    @endif
                                    <div><i class="fas fa-check-circle text-success"></i> {{ $p->received_at->timezone('Asia/Manila')->format('M d, Y g:i A') }}</div>
                                @else
                                    <span>hindi pa nag-confirm</span>
                                @endif
                            </td>
                            <td class="lj-muted">{{ $p->requested_at?->format('M d, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center lj-muted py-4">Wala pang history.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Image modal --}}
<div class="modal fade" id="imgModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center p-2"><img id="imgModalSrc" src="" class="modal-img-preview" style="max-height:80vh;"></div></div></div></div>

{{-- Pay payout modal --}}
<div class="modal fade" id="payPayoutModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h6 class="modal-title">💸 Bayaran ang Payout Request</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <input type="hidden" id="payPayoutId">
        <div id="payPayoutSummary" class="mb-2 p-2 rounded" style="background:#fffbeb;border:1px solid #fef08a;font-size:13px;"></div>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="lj-muted">Payment Account (pambayad sa GA)</label>
                <select id="payPayoutMethod" class="form-select">
                    <option value="">— piliin ang account —</option>
                    @foreach($paymentAccounts ?? [] as $pa)
                    <option value="{{ $pa->name }}">{{ $pa->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6">
                <label class="lj-muted">Reference</label>
                <input type="text" id="payPayoutRef" class="form-control" placeholder="Ref #">
            </div>
        </div>
        <label class="lj-muted">Payment Proof (screenshot)</label>
        <input type="file" id="payPayoutProof" class="form-control" accept="image/*">
        <div class="mt-2"><small class="lj-muted">Pwede ring i-verify kaagad (bawas agad sa credit ng layout-doer) o pay muna.</small></div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-outline-danger" onclick="rejectPayout()">Reject</button>
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-warning" onclick="submitPayPayout('pay')">Pay (proof only)</button>
        <button class="btn btn-success" onclick="submitPayPayout('verify')">Verify &amp; Pay</button>
    </div>
</div></div></div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
const PAYOUT_MAP = @json($payoutMap ?? []);

function showImage(src) {
    document.getElementById('imgModalSrc').src = src;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('imgModal')).show();
}

function openPayPayout(payoutId) {
    document.getElementById('payPayoutId').value = payoutId;
    const p = PAYOUT_MAP[payoutId] || {};
    const sum = document.getElementById('payPayoutSummary');
    let html = '<b>Payout #' + payoutId + '</b> — ₱' + (p.amount ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2}) + '<br>';
    html += '<span class="lj-muted">Para kay:</span> ' + (p.ga || '—');
    if (p.account) html += '<br><span class="lj-muted">Account:</span> ' + p.account;
    if (p.notes) html += '<br><span class="lj-muted">Notes:</span> ' + p.notes;
    if (p.proof) html += '<br><a href="javascript:void(0)" onclick="showImage(\'' + p.proof + '\')" class="lj-muted"><i class="fas fa-image"></i> view account proof</a>';
    sum.innerHTML = html;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('payPayoutModal')).show();
}

function submitPayPayout(action) {
    const id = document.getElementById('payPayoutId').value;
    if (window.refLenOK && !window.refLenOK(document.getElementById('payPayoutRef'))) {
        alert('Minimum 6 characters ang Reference #. Pakihabaan muna bago mag-submit.');
        return;
    }
    const fd = new FormData();
    fd.append('action', action);
    fd.append('payment_method', document.getElementById('payPayoutMethod').value);
    fd.append('payment_reference', document.getElementById('payPayoutRef').value);
    const proof = document.getElementById('payPayoutProof').files[0];
    if (proof) fd.append('payment_proof', proof);
    fetch('/sales/layout-jobs/payout/' + id + '/pay', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
        body: fd
    }).then(r => r.json()).then(d => {
        alert(d.error || 'Saved ✓');
        if (!d.error) location.reload();
    }).catch(() => alert('May error — subukan ulit.'));
}

function verifyPaidPayout(id, method, ref) {
    if (!confirm('I-verify ang payout #' + id + '? Markahan itong bayad na at isasara ang payout.')) return;
    const fd = new FormData();
    fd.append('action', 'verify');
    fd.append('payment_method', method || 'Cash');
    fd.append('payment_reference', ref || '');
    fetch('/sales/layout-jobs/payout/' + id + '/pay', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
        body: fd
    }).then(r => r.json()).then(d => {
        alert(d.error || 'Verified ✓');
        if (!d.error) location.reload();
    }).catch(() => alert('May error — subukan ulit.'));
}

function rejectPayout() {
    const id = document.getElementById('payPayoutId').value;
    const reason = prompt('Reason for rejection:') || '';
    if (!reason) return alert('Kailangan ng reason.');
    fetch('/sales/layout-jobs/payout/' + id + '/pay', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify({action: 'reject', reason: reason})
    }).then(r => r.json()).then(d => { alert(d.error || 'Rejected ✓'); location.reload(); });
}
</script>
@endpush
