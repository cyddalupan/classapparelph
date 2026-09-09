@extends('layouts.app')

@section('title', 'Layout Jobs')

@push('styles')
<style>
    .lj-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #2563eb 100%);
        border-radius: 16px;
        padding: 20px 26px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(30, 58, 138, .25);
    }
    .lj-hero::after {
        content: "🎨";
        position: absolute;
        right: 18px;
        bottom: -18px;
        font-size: 84px;
        opacity: .16;
        transform: rotate(-8deg);
    }
    .lj-hero h4 { font-weight: 800; letter-spacing: .3px; }
    .lj-hero .sub { opacity: .92; font-size: 12.5px; }
    .lj-stat {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        padding: 12px 16px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .05);
        height: 100%;
    }
    .lj-stat .val { font-size: 22px; font-weight: 800; line-height: 1.1; color: #111827; }
    .lj-stat .lbl { font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
    .lj-card {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .04);
        overflow: hidden;
    }
    .lj-badge { font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 20px; letter-spacing: .3px; white-space: nowrap; }
    .lj-badge.paid { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .lj-badge.free { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }
    .lj-badge.open { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .lj-badge.done { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .lj-badge.pending { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
    .lj-badge.verified { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .lj-badge.rejected { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .lj-badge.requested { background: #fefce8; color: #a16207; border: 1px solid #fef08a; }
    .lj-muted { color: #6b7280; font-size: .8rem; }
    .lj-thumb { width: 46px; height: 46px; border-radius: 10px; object-fit: cover; border: 1px solid #e5e7eb; background: #f9fafb; }
    .lj-amount { font-size: 15px; font-weight: 800; color: #111827; }
    .lj-btn-mini { font-size: 11.5px; padding: 3px 10px; border-radius: 8px; font-weight: 600; }
    .modal-img-preview { max-width: 100%; max-height: 240px; border-radius: 10px; border: 1px solid #e5e7eb; }
    .lj-empty { text-align: center; padding: 60px 20px; color: #9ca3af; }
    .lj-empty i { font-size: 42px; display: block; margin-bottom: 10px; opacity: .4; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="lj-hero mb-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h4 class="mb-0">🎨 {{ ($mode ?? 'personal') === 'global' ? 'Layout Job List (All)' : 'My Layout Jobs' }}</h4>
                <div class="sub">{{ ($mode ?? 'personal') === 'global' ? 'Lahat ng bayad / libre na layout jobs — para sa review at payout ng approver.' : 'Bayad / libre na layout jobs mo — assigned sa iyo o ikaw ang gumawa. May GA tag, payment verification, at payout requests.' }}</div>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isCoo() || in_array(auth()->user()->role, ['staff','sales_agent','sales_representative','prod_manager','qa']))
            <a href="{{ route('sales.layout-jobs.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Layout Job</a>
            @endif
        </div>
    </div>

    @if(isset($credit))
    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <div class="lj-stat">
                <div class="lbl">My Available Credit (Total Layout)</div>
                <div class="val">₱{{ number_format($credit, 2) }}</div>
                <div class="lj-muted">done + verified na layout jobs, minus lahat ng na-request nang payout (requested/paid/verified). Bawas agad pag nag-request ka para hindi mag-double.</div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="lj-stat d-flex align-items-center justify-content-between flex-wrap gap-2" style="min-height:100%;">
                <div>
                    <div class="lbl">Payout Request</div>
                    <div class="lj-muted">Mag-request ng payout — buo o partial (max ₱{{ number_format($credit, 2) }}). Pwede kang maglagay ng amount, account number/name, picture proof, at notes.</div>
                </div>
                <button class="btn btn-dark btn-sm" onclick="openPayoutModal()" {{ $credit <= 0 ? 'disabled' : '' }}>
                    <i class="fas fa-hand-holding-usd"></i> Request Payout
                </button>
            </div>
        </div>
    </div>
    @endif

    @if(($mode ?? 'personal') === 'global' && isset($payoutRequests) && $payoutRequests->isNotEmpty())
    {{-- Approver: lahat ng pending payout requests (requested/paid) --}}
    <div class="lj-card mb-3">
        <div class="p-3">
            <h6 class="mb-1">💸 Payout Requests <span class="lj-badge requested">{{ $payoutRequests->count() }} pending</span></h6>
            <div class="lj-muted mb-2">Mga payout request ng layout-doers — i-check ang account details at magbayad. Bawas agad sa available credit nila pag na-request.</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>#</th><th>Layout Doer</th><th>Amount</th><th>Account Details</th><th>Notes</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                    @foreach($payoutRequests as $p)
                    <tr>
                        <td>#{{ $p->id }}</td>
                        <td>{{ $p->gaUser?->name ?: '—' }}</td>
                        <td class="lj-amount">₱{{ number_format($p->amount, 2) }}</td>
                        <td>
                            <div>{{ $p->account_name ?: '—' }} {{ $p->account_number ? '· ' . $p->account_number : '' }}</div>
                            @if($p->account_proof_path)
                            <a href="javascript:void(0)" class="lj-muted" onclick="showImage('{{ asset('storage/' . $p->account_proof_path) }}')"><i class="fas fa-image"></i> view proof</a>
                            @endif
                        </td>
                        <td class="lj-muted" style="max-width:200px;">{{ $p->request_notes ?: '—' }}</td>
                        <td><span class="lj-badge {{ $p->status === 'paid' ? 'pending' : 'requested' }}">{{ $p->status }}</span></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-warning lj-btn-mini" onclick="openPayPayout({{ $p->id }})">💸 Pay</button>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if(($mode ?? 'personal') !== 'global' && isset($payoutRequests) && $payoutRequests->isNotEmpty())
    {{-- GA: sariling payout requests history --}}
    <div class="lj-card mb-3">
        <div class="p-3">
            <h6 class="mb-1">📤 My Payout Requests</h6>
            <div class="lj-muted mb-2">Nasa review ng approver ang requested. Bawas agad sa available credit mo ang requested/paid/verified.</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>#</th><th>Amount</th><th>Account Details</th><th>Notes</th><th>Status</th><th>Requested</th></tr></thead>
                    <tbody>
                    @foreach($payoutRequests as $p)
                    <tr>
                        <td>#{{ $p->id }}</td>
                        <td class="lj-amount">₱{{ number_format($p->amount, 2) }}</td>
                        <td>
                            <div>{{ $p->account_name ?: '—' }} {{ $p->account_number ? '· ' . $p->account_number : '' }}</div>
                            @if($p->account_proof_path)
                            <a href="javascript:void(0)" class="lj-muted" onclick="showImage('{{ asset('storage/' . $p->account_proof_path) }}')"><i class="fas fa-image"></i> view proof</a>
                            @endif
                            @if($p->reject_reason)
                            <div class="text-danger" style="font-size:11px;">✗ {{ $p->reject_reason }}</div>
                            @endif
                        </td>
                        <td class="lj-muted" style="max-width:200px;">{{ $p->request_notes ?: '—' }}</td>
                        <td><span class="lj-badge {{ in_array($p->status, ['requested', 'pending']) ? 'requested' : ($p->status === 'rejected' ? 'rejected' : 'verified') }}">{{ $p->status }}</span></td>
                        <td class="lj-muted">{{ $p->requested_at?->format('M d, Y h:i A') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="lj-card mb-3">
        <div class="lj-card-header p-3 bg-white">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="lj-muted">Search</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Job #, customer, description...">
                </div>
                <div class="col-md-2">
                    <label class="lj-muted">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="paid" {{ request('type')==='paid'?'selected':'' }}>Bayad</option>
                        <option value="free" {{ request('type')==='free'?'selected':'' }}>Libre</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="lj-muted">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="open" {{ request('status')==='open'?'selected':'' }}>Open</option>
                        <option value="done" {{ request('status')==='done'?'selected':'' }}>Done</option>
                        <option value="payment_pending" {{ request('status')==='payment_pending'?'selected':'' }}>Payment Pending</option>
                        <option value="no_amount" {{ request('status')==='no_amount'?'selected':'' }}>Libre — wala pang amount</option>
                    </select>
                </div>
                @if(($mode ?? 'personal') === 'global')
                <div class="col-md-2">
                    <label class="lj-muted">Layout Doer</label>
                    <select name="ga" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($gaUsers as $g)
                        <option value="{{ $g->id }}" {{ request('ga')==$g->id?'selected':'' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-filter"></i> Filter</button>
                    <a href="{{ ($mode ?? 'personal') === 'global' ? route('sales.layout-jobs.all') : route('sales.layout-jobs') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Job #</th>
                        <th>Customer</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Layout Doer</th>
                        <th>Galing kay</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $job)
                    <tr>
                        <td><strong>{{ $job->job_no }}</strong><br><span class="lj-muted">{{ $job->created_at->format('M d, Y') }}</span></td>
                        <td>{{ $job->displayCustomer() }}</td>
                        <td style="max-width:220px;">
                            <div class="d-flex align-items-start gap-2">
                                @if($job->reference_image_path)
                                <img src="{{ asset('storage/' . $job->reference_image_path) }}" class="lj-thumb flex-shrink-0" style="cursor:pointer" onclick="showImage('{{ asset('storage/' . $job->reference_image_path) }}')">
                                @endif
                                <span class="text-truncate d-block" style="font-size:.85rem;">{{ $job->description ?: '—' }}</span>
                            </div>
                            @if($job->sale_id)
                            <span class="lj-badge verified mt-1 d-inline-block"><i class="fas fa-link"></i> Sale #{{ $job->sale_id }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="lj-badge {{ $job->type }}">{{ $job->isPaid() ? 'BAYAD' : 'LIBRE' }}</span>
                        </td>
                        <td>
                            @if($job->amount !== null)
                            <span class="lj-amount">₱{{ number_format($job->amount, 2) }}</span>
                            @if($job->amount_set_by && $job->isFree())
                            <br><span class="lj-muted">set ng approver</span>
                            @endif
                            @else
                            <span class="lj-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($job->isPaid())
                                @php $ps = $job->payment_status; @endphp
                                <span class="lj-badge {{ in_array($ps,['verified']) ? 'verified' : ($ps==='rejected' ? 'rejected' : 'pending') }}">
                                    {{ $ps === 'verified' ? '✓ Verified' : ($ps === 'rejected' ? '✗ Rejected' : '⏳ Pending') }}
                                </span>
                                @if($job->paymentAccount)
                                <br><span class="lj-muted"><i class="fas fa-wallet"></i> {{ $job->paymentAccount->name }}</span>
                                @endif
                                @if($job->payment_screenshot_path)
                                <br><a href="javascript:void(0)" class="lj-muted" onclick="showImage('{{ asset('storage/' . $job->payment_screenshot_path) }}')"><i class="fas fa-receipt"></i> view proof</a>
                                @endif
                            @else
                            <span class="lj-muted">N/A (libre)</span>
                            @endif
                        </td>
                        <td>{{ $job->gaUser?->name ?: '—' }}</td>
                        <td>{{ $job->creator?->name ?: '—' }}</td>
                        <td>
                            @if($job->payout_id)
                            <span class="lj-badge requested">💸 Payout #{{ $job->payout_id }} ({{ $job->payout?->status ?? '' }})</span>
                            @else
                            <span class="lj-badge {{ $job->status === 'done' ? 'done' : 'open' }}">{{ $job->status === 'done' ? '✓ Done' : 'Open' }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @php
                                $isApprover = auth()->user()->isAdmin() || auth()->user()->isCoo() || auth()->user()->isCpo() || auth()->user()->isCmo();
                                // Verify payment: admin O ang may-ari ng payment account
                                $accOwnerId = $job->paymentAccount?->user_id;
                                $canVerifyPay = auth()->user()->isAdmin() || ($accOwnerId && auth()->id() === $accOwnerId);
                            @endphp
                            @if($mode === 'global' && $job->isPaid() && $job->payment_status === 'pending' && $canVerifyPay)
                                {{-- Verify/Reject sa personal list ay inalis — nasa Payment Verification hub na ang pag-verify ng sariling account (2026-09-08). Sa All list lang ito para sa company accounts at admin review. --}}
                                <button class="btn btn-sm btn-success lj-btn-mini mb-1" onclick="verifyPayment({{ $job->id }}, 'verify')">Verify Pay</button>
                                <button class="btn btn-sm btn-danger lj-btn-mini mb-1" onclick="verifyPayment({{ $job->id }}, 'reject')">Reject</button>
                            @endif
                            @if($isApprover)
                                @if($job->isFree() && $job->amount === null && $job->status !== 'done')
                                <button class="btn btn-sm btn-dark lj-btn-mini mb-1" onclick="openSetAmount({{ $job->id }}, '{{ $job->job_no }}')">Set Amount</button>
                                @endif
                            @endif
                            @if($job->isPaid() && $job->payment_status === 'verified' && !$job->sale_id && auth()->id() === $job->created_by)
                                {{-- Spec (2026-09-09): verified + ang nag-create ng job lang ang pwedeng mag-link ng sale --}}
                                <button class="btn btn-sm btn-outline-primary lj-btn-mini mb-1" onclick="openLinkSale({{ $job->id }}, '{{ $job->job_no }}')">Link Sale</button>
                            @endif
                            @if($job->gaUser && auth()->id() === $job->ga_user_id && $job->status === 'open' && !$job->payout_id)
                            <button class="btn btn-sm btn-primary lj-btn-mini mb-1" onclick="markDone({{ $job->id }})">✓ Done</button>
                            @endif
                            @if($isApprover && $job->payout_id && in_array($job->payout?->status, ['requested','paid']))
                            <button class="btn btn-sm btn-warning lj-btn-mini mb-1" onclick="openPayPayout({{ $job->payout_id }})">💸 Pay Payout</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9">
                        <div class="lj-empty">
                            <i class="fas fa-palette"></i>
                            Wala pang layout jobs dito.
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jobs->hasPages())
        <div class="p-3">{{ $jobs->links() }}</div>
        @endif
    </div>
</div>

{{-- Image lightbox modal --}}
<div class="modal fade" id="imgModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center p-2"><img id="imgModalSrc" src="" class="modal-img-preview" style="max-height:80vh;"></div></div></div></div>

{{-- Set amount (libre) modal --}}
<div class="modal fade" id="setAmountModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h6 class="modal-title">Maglagay ng Amount — Libreng Layout</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p class="lj-muted">Magre-reflect ito sa history at sa GA (may amount na yung nilalayout nila).</p>
        <input type="hidden" id="setAmountJobId">
        <label class="lj-muted">Amount (₱)</label>
        <input type="number" id="setAmountValue" class="form-control" min="0" step="0.01" placeholder="0.00">
    </div>
    <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-dark" onclick="submitSetAmount()">Save Amount</button></div>
</div></div></div>

{{-- Link to sale modal --}}
<div class="modal fade" id="linkSaleModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h6 class="modal-title">I-link sa Sale</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p class="lj-muted">Bayad na layout lang ang idinadagdag sa linked sales (libre = hindi).</p>
        <input type="hidden" id="linkSaleJobId">
        <label class="lj-muted">Sale ID / Sales #</label>
        <input type="text" id="linkSaleValue" class="form-control" placeholder="Enter prototype_sales.id o hanapin sa sale page">
    </div>
    <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" onclick="submitLinkSale()">Link</button></div>
</div></div></div>

{{-- Payout request modal (GA) --}}
<div class="modal fade" id="payoutModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h6 class="modal-title">💸 Request Payout</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p class="lj-muted">Buo o partial — hanggang sa available credit mo. <b>Bawas agad</b> sa credit mo ang amount na ire-request para hindi mag-double request.</p>
        <label class="lj-muted">Amount (₱) — max {{ number_format($credit ?? 0, 2) }}</label>
        <input type="number" id="payoutAmount" class="form-control mb-2" min="0.01" max="{{ $credit ?? 0 }}" step="0.01" placeholder="0.00">
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="lj-muted">Account Name</label>
                <input type="text" id="payoutAccountName" class="form-control" placeholder="e.g. Juan Dela Cruz">
            </div>
            <div class="col-6">
                <label class="lj-muted">Account Number</label>
                <input type="text" id="payoutAccountNumber" class="form-control" placeholder="e.g. GCash/Maya/Bank #">
            </div>
        </div>
        <label class="lj-muted">Account Proof (screenshot, optional)</label>
        <input type="file" id="payoutProof" class="form-control mb-2" accept="image/*">
        <label class="lj-muted">Notes (optional)</label>
        <textarea id="payoutNotes" class="form-control" rows="2"></textarea>
    </div>
    <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-dark" onclick="submitPayout()">Request</button></div>
</div></div></div>

{{-- Pay payout modal (approver) --}}
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
        <button class="btn btn-success" onclick="submitPayPayout('verify')">Verify & Pay</button>
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

function verifyPayment(id, action) {
    let reason = '';
    if (action === 'reject') reason = prompt('Reason for rejection:') || '';
    fetch('/sales/layout-jobs/' + id + '/verify-payment', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify({action: action, reason: reason})
    }).then(r => r.json()).then(d => { alert(d.error || 'Saved ✓'); location.reload(); });
}

function openSetAmount(id, jobNo) {
    document.getElementById('setAmountJobId').value = id;
    document.getElementById('setAmountValue').value = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('setAmountModal')).show();
}

function submitSetAmount() {
    const id = document.getElementById('setAmountJobId').value;
    const amount = document.getElementById('setAmountValue').value;
    if (amount === '') return alert('Ilagay ang amount.');
    fetch('/sales/layout-jobs/' + id + '/set-amount', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify({amount: amount})
    }).then(r => r.json()).then(d => { alert(d.error || 'Amount saved — reflect na sa GA ✓'); location.reload(); });
}

function markDone(id) {
    if (!confirm('Mark this layout job as DONE?')) return;
    fetch('/sales/layout-jobs/' + id + '/done', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}
    }).then(r => r.json()).then(d => { alert(d.error || 'Done ✓'); location.reload(); });
}

function openLinkSale(id, jobNo) {
    document.getElementById('linkSaleJobId').value = id;
    document.getElementById('linkSaleValue').value = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('linkSaleModal')).show();
}

function submitLinkSale() {
    const id = document.getElementById('linkSaleJobId').value;
    const saleId = document.getElementById('linkSaleValue').value.trim();
    if (!saleId) return alert('Ilagay ang Sale ID.');
    fetch('/sales/layout-jobs/' + id + '/link-sale', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify({sale_id: saleId})
    }).then(async r => {
        const d = await r.json().catch(() => ({}));
        if (r.ok) { alert('Naka-link na sa sale ✓'); location.reload(); }
        else { alert(d.error || d.message || 'May error — hindi na-save. Pakisubukan muli.'); }
    }).catch(() => alert('Network error — hindi na-save. Pakisubukan muli.'));
}

function openPayoutModal() {
    const amountEl = document.getElementById('payoutAmount');
    const maxVal = parseFloat(amountEl.max || '0');
    amountEl.value = maxVal > 0 ? maxVal.toFixed(2) : '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('payoutModal')).show();
}

function submitPayout() {
    const amount = document.getElementById('payoutAmount').value;
    const accountName = document.getElementById('payoutAccountName').value.trim();
    const accountNumber = document.getElementById('payoutAccountNumber').value.trim();
    const notes = document.getElementById('payoutNotes').value;
    const proof = document.getElementById('payoutProof').files[0];

    if (!amount || parseFloat(amount) <= 0) return alert('Ilagay ang amount.');
    const maxVal = parseFloat(document.getElementById('payoutAmount').max || '0');
    if (parseFloat(amount) > maxVal + 0.001) return alert('Lampas sa available credit mo (₱' + maxVal.toFixed(2) + ').');
    if (!accountName) return alert('Ilagay ang account name.');
    if (!accountNumber) return alert('Ilagay ang account number.');

    const fd = new FormData();
    fd.append('amount', amount);
    fd.append('account_name', accountName);
    fd.append('account_number', accountNumber);
    fd.append('notes', notes);
    if (proof) fd.append('account_proof', proof);

    fetch('/sales/layout-jobs/payout-request', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
        body: fd
    }).then(r => r.json()).then(d => {
        if (d.error) return alert(d.error);
        alert('Payout request sent — bawas agad sa credit mo ✓');
        location.reload();
    }).catch(() => alert('May error sa pag-request.'));
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
    }).then(r => r.json()).then(d => { alert(d.error || 'Saved ✓'); location.reload(); });
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
