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
    @if(!empty($boardTabs))
        @include('sales.prototype._board-tabs')
    @endif
    <div class="lj-hero mb-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h4 class="mb-0">🎨 {{ ($mode ?? 'personal') === 'global' ? 'Layout Job List (All)' : 'My Layout Jobs' }}</h4>
                <div class="sub">{{ ($mode ?? 'personal') === 'global' ? 'Lahat ng bayad / libre na layout jobs — para sa review at payout ng approver.' : 'Bayad / libre na layout jobs mo — assigned sa iyo o ikaw ang gumawa. May GA tag, payment verification, at payout requests.' }}</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if(($mode ?? 'personal') === 'global')
                <a href="{{ route('sales.layout-jobs.payouts') }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-hand-holding-usd"></i> Payout Requests
                    @php
                        // Badge = requested lang (hindi pa bayad). Yung paid na ay "for verification" na —
                        // hindi na pending request (Andrew 2026-10-21).
                        $badgeRequested = isset($payoutRequests) ? $payoutRequests->where('status', 'requested')->count() : 0;
                        $badgePaid = isset($payoutRequests) ? $payoutRequests->where('status', 'paid')->count() : 0;
                    @endphp
                    <span class="badge bg-dark ms-1">{{ $badgeRequested }}</span>
                    @if($badgePaid > 0)
                    <span class="badge bg-success ms-1" title="Bayad na — hinihintay ang confirm ng layout doer o verify ng approver">✓ {{ $badgePaid }}</span>
                    @endif
                </a>
                @endif
                @if(in_array(auth()->user()->role, ['admin','coo','cpo','cmo']))
                <a href="{{ route('sales.layout-jobs.dashboard') }}" class="btn btn-outline-light btn-sm"><i class="fas fa-chart-line"></i> Layout Dashboard</a>
                @endif
                @if(in_array(auth()->user()->role, ['admin','staff','coo','cpo','cmo','sales_agent','sales_representative','prod_manager','qa','hr_accountant_agent']))
                <a href="{{ route('sales.layout-jobs.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Layout Job</a>
                @endif
            </div>
        </div>
    </div>

    @if(isset($credit))
    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <div class="lj-stat">
                <div class="lbl">{{ $credit < 0 ? 'Utang sa Company (Layout)' : 'My Available Credit (Total Layout)' }}</div>
                <div class="val" style="{{ $credit < 0 ? 'color:#b91c1c;' : '' }}">₱{{ number_format($credit, 2) }}</div>
                @if($credit < 0)
                <div class="lj-muted">⚠️ Sobra na ang nakuha mong payout kaysa sa earned mo. Bawas ito sa mga susunod na layout job — kumuha ng bagong job para mawala ang negative.</div>
                @else
                <div class="lj-muted">done + verified na layout jobs, minus lahat ng na-request nang payout (requested/paid/verified). Bawas agad pag nag-request ka para hindi mag-double.</div>
                @endif
            </div>
        </div>
        <div class="col-md-8">
            <div class="lj-stat d-flex align-items-center justify-content-between flex-wrap gap-2" style="min-height:100%;">
                <div>
                    <div class="lbl">Payout Request</div>
                    @if($credit <= 0)
                    <div class="lj-muted">Wala pang pwedeng i-request — kailangan mo munang magkaroon ng bagong layout job para mabayaran ang negative/utang.</div>
                    @else
                    <div class="lj-muted">Mag-request ng payout — buo o partial (max ₱{{ number_format($credit, 2) }}). Pwede kang maglagay ng amount, account number/name, picture proof, at notes.</div>
                    @endif
                </div>
                <button class="btn btn-dark btn-sm" onclick="openPayoutModal()" {{ $credit <= 0 ? 'disabled' : '' }}>
                    <i class="fas fa-hand-holding-usd"></i> Request Payout
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Payout Requests: may sariling page na (Andrew 2026-10-21) — /sales/layout-jobs/payouts --}}

    @if(($mode ?? 'personal') !== 'global' && isset($payoutRequests) && $payoutRequests->isNotEmpty())
    {{-- GA: sariling payout requests history --}}
    <div class="lj-card mb-3">
        <div class="p-3">
            <h6 class="mb-1">📤 My Payout Requests</h6>
            <div class="lj-muted mb-2">Nasa review ng approver ang requested. Bawas agad sa available credit mo ang requested/paid/verified.</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>#</th><th>Amount</th><th>Account Details</th><th>Notes</th><th>Status</th><th>Requested</th><th class="text-end">Action</th></tr></thead>
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
                        <td class="text-end">
                            @if($p->status === 'paid')
                            {{-- Bayad na ng approver (may proof) → GA confirm na natanggap niya (Andrew 2026-10-21) --}}
                            <button class="btn btn-sm btn-success lj-btn-mini" onclick="confirmReceipt({{ $p->id }}, {{ (float) $p->amount }})">✅ Natanggap ko na</button>
                            @else
                            <span class="lj-muted">—</span>
                            @endif
                        </td>
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
                <div class="col-md-2">
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
                        <option value="cancelled" {{ request('status')==='cancelled'?'selected':'' }}>🚫 Hindi Tumuloy</option>
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
                <div class="col-md-2">
                    <label class="lj-muted">Galing kay (Agent)</label>
                    <select name="agent" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($agentUsers ?? [] as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent')==$ag->id?'selected':'' }}>{{ $ag->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-filter"></i> Filter</button>
                    <a href="{{ !empty($boardTabs) ? route('sales.prototype.board.layout-jobs') : (($mode ?? 'personal') === 'global' ? route('sales.layout-jobs.all') : route('sales.layout-jobs')) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <td style="max-width:280px;">
                            <div class="d-flex align-items-start gap-2">
                                @if($job->reference_image_path)
                                <img src="{{ asset('storage/' . $job->reference_image_path) }}" class="lj-thumb flex-shrink-0" style="cursor:pointer" onclick="showImage('{{ asset('storage/' . $job->reference_image_path) }}')">
                                @endif
                                {{-- Layout notes / design info mula sa creation (textarea) — buo na, may line breaks --}}
                                @if($job->description)
                                <span class="d-block" title="{{ $job->description }}" style="font-size:.85rem;white-space:pre-line;max-height:4.9em;overflow:hidden;word-break:break-word;">{{ $job->description }}</span>
                                @else
                                <span class="lj-muted">—</span>
                                @endif
                            </div>
                            @if($job->sale_id)
                            <a href="{{ route('sales.prototype.show', $job->sale_id) }}" target="_blank" rel="noopener" class="lj-badge verified mt-1 d-inline-block" style="text-decoration:none;"><i class="fas fa-link"></i> Sale #{{ $job->sale_id }}</a>
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
                                @if($ps === 'rejected' && $job->payment_reject_reason)
                                <br><span class="text-danger" style="font-size:11px;" title="{{ $job->payment_reject_reason }}">✗ {{ \Illuminate\Support\Str::limit($job->payment_reject_reason, 45) }}</span>
                                @endif
                                @if($ps === 'pending' && $job->payment_resubmit_count > 0)
                                <br><span class="lj-muted" style="font-size:11px;"><i class="fas fa-redo"></i> re-upload #{{ $job->payment_resubmit_count }}</span>
                                @endif
                            @else
                            <span class="lj-muted">N/A (libre)</span>
                            @endif
                        </td>
                        <td>
                            @if($job->gaUser)
                                <x-user-chip :user="$job->gaUser" :size="18" />
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($job->creator)
                                <x-user-chip :user="$job->creator" :size="18" />
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($job->isCancelled())
                            <span class="lj-badge rejected">✗ Hindi Tumuloy</span>
                            @if($job->cancel_reason)
                            <br><span class="lj-muted" title="{{ $job->cancel_reason }}"><i class="fas fa-comment"></i> {{ \Illuminate\Support\Str::limit($job->cancel_reason, 40) }}</span>
                            @endif
                            @elseif($job->payout_id)
                            <span class="lj-badge requested">💸 Payout #{{ $job->payout_id }} ({{ $job->payout?->status ?? '' }})</span>
                            @else
                            @php
                                $canRetagRow = ($mode ?? 'personal') !== 'global'
                                    && !$job->isCancelled() && !$job->payout_id
                                    && (auth()->id() === $job->created_by || $isApprover || auth()->id() === $job->ga_user_id);
                                $retagLocked = $job->retag_locked_until && now()->lt($job->retag_locked_until);
                            @endphp
                            @if($job->status === 'done' && $canRetagRow)
                            {{-- Clickable status → undo + change tag (may confirmation) --}}
                            <a href="javascript:void(0)" class="text-decoration-none" title="I-click para i-undo / i-retag sa ibang doer"
                               onclick="openRetag({{ $job->id }}, '{{ $job->job_no }}', {{ (int) $job->ga_user_id }}, '{{ addslashes($job->gaUser?->name ?? '') }}', 'done')">
                                <span class="lj-badge done">✓ Done <i class="fas fa-sync-alt" style="font-size:9px;"></i></span>
                            </a>
                            @else
                            <span class="lj-badge {{ $job->status === 'done' ? 'done' : 'open' }}">{{ $job->status === 'done' ? '✓ Done' : 'Open' }}</span>
                            @endif
                            @if($retagLocked && $job->status === 'open')
                            <br><span class="lj-muted" style="font-size:11px;" title="Naka-lock pagkatapos ng retag/undo"><i class="fas fa-lock"></i> done lock hanggang {{ $job->retag_locked_until->timezone('Asia/Manila')->format('M d, Y') }}</span>
                            @endif
                            @if($job->retag_count > 0)
                            <br><span class="lj-muted" style="font-size:11px;"><i class="fas fa-retweet"></i> na-retag {{ $job->retag_count }}x</span>
                            @endif
                            @endif
                        </td>
                        <td class="text-end">
                            @php
                                $isApprover = auth()->user()->isAdmin() || auth()->user()->isCoo() || auth()->user()->isCpo() || auth()->user()->isCmo();
                                // Verify payment: ANG MAY-ARI lang ng payment account (Andrew 2026-10-21).
                                // Admin lang ang fallback kapag company account (walang owner).
                                $accOwnerId = $job->paymentAccount?->user_id;
                                $canVerifyPay = $accOwnerId
                                    ? (auth()->id() === $accOwnerId)
                                    : auth()->user()->isAdmin();
                            @endphp
                            @if(($mode ?? 'personal') !== 'global' && $job->isPaid() && $job->payment_status === 'rejected' && !$job->isCancelled() && !$job->payout_id && (auth()->id() === $job->created_by || $isApprover))
                            {{-- Rejected payment → pwedeng mag-upload ulit ng proof/ref (Andrew 2026-10-21). Personal list lang — hindi sa Layout Job List (All). --}}
                            <button class="btn btn-sm btn-outline-danger lj-btn-mini mb-1" onclick="openResubmitPayment({{ $job->id }}, '{{ $job->job_no }}', {{ (int) $job->payment_account_id }}, '{{ addslashes($job->payment_reference ?? '') }}')">📤 Upload Payment Ulit</button>
                            @endif
                            @if($mode === 'global' && $job->isPaid() && $job->payment_status === 'pending' && $canVerifyPay)
                                {{-- Verify/Reject sa personal list ay inalis — nasa Payment Verification hub na ang pag-verify ng sariling account (2026-09-08). Sa All list lang ito para sa company accounts at admin review. --}}
                                <button class="btn btn-sm btn-success lj-btn-mini mb-1" onclick="verifyPayment({{ $job->id }}, 'verify')">Verify Pay</button>
                                <button class="btn btn-sm btn-danger lj-btn-mini mb-1" onclick="verifyPayment({{ $job->id }}, 'reject')">Reject</button>
                            @endif
                            @if(($mode ?? 'personal') !== 'global' && $job->isFree() && !$job->isCancelled() && !$job->payout_id && auth()->id() === $job->created_by)
                            {{-- Libre → biglang nagbayad ang client: CREATOR lang ang pwedeng mag-upload (Andrew 2026-10-21) --}}
                            <button class="btn btn-sm btn-outline-success lj-btn-mini mb-1" onclick="openFreePayment({{ $job->id }}, '{{ $job->job_no }}')">💳 Upload Payment</button>
                            @endif
                            @if($isApprover)
                                @php
                                    // Amount: libre (may sale O hindi tumuloy) ; bayad (rejected, CEO/COO lang)
                                    $canSetAmount = ($job->sale_id || $job->isCancelled())
                                        && ($job->isFree() || ($job->isPaid() && $job->payment_status === 'rejected'))
                                        && ($job->isFree() || auth()->user()->isAdmin() || auth()->user()->isCoo());
                                @endphp
                                @if($canSetAmount)
                                <button class="btn btn-sm btn-dark lj-btn-mini mb-1" onclick="openSetAmount({{ $job->id }}, '{{ $job->job_no }}', {{ $job->amount !== null ? (float) $job->amount : 'null' }})">{{ $job->amount === null ? 'Set Amount' : '✏️ Edit Amount' }}</button>
                                @endif
                            @endif
                            @if(!$job->sale_id && auth()->id() === $job->created_by)
                                {{-- Spec: ang nag-create lang ang pwedeng mag-link; bayad = verified muna, libre = pwede agad (Andrew 2026-09-17) --}}
                                <button class="btn btn-sm btn-outline-primary lj-btn-mini mb-1" onclick="openLinkSale({{ $job->id }}, '{{ $job->job_no }}')">Link Sale</button>
                            @endif
                            @if(($mode ?? 'personal') !== 'global' && in_array($job->status, ['open', 'done']) && !$job->isCancelled() && !$job->payout_id && (auth()->id() === $job->created_by || $isApprover || auth()->id() === $job->ga_user_id))
                            {{-- Retag / undo ng layout doer (Andrew 2026-10-21) — may nagkakamali ng tag. Done → undo + 7-day lock. --}}
                            <button class="btn btn-sm btn-outline-dark lj-btn-mini mb-1" onclick="openRetag({{ $job->id }}, '{{ $job->job_no }}', {{ (int) $job->ga_user_id }}, '{{ addslashes($job->gaUser?->name ?? '') }}', '{{ $job->status }}')">🔁 {{ $job->status === 'done' ? 'Undo / Retag' : 'Retag' }}</button>
                            @endif
                            @if(($mode ?? 'personal') !== 'global' && !$job->sale_id && !$job->isCancelled() && !$job->payout_id && (auth()->id() === $job->created_by || $isApprover) && (!$job->isPaid() || in_array($job->payment_status, ['verified', 'rejected'], true)))
                            {{-- "Hindi Tumuloy" — personal list lang (Andrew 2026-10-21). Bayad: kailangan verified o rejected ang payment muna. --}}
                            <button class="btn btn-sm btn-outline-secondary lj-btn-mini mb-1" onclick="openCancelJob({{ $job->id }}, '{{ $job->job_no }}', '{{ $job->isFree() ? 'libre' : 'bayad' }}')">🚫 Hindi Tumuloy</button>
                            @endif
                            @if($job->gaUser && auth()->id() === $job->ga_user_id && $job->status === 'open' && !$job->payout_id)
                                @if($job->retag_locked_until && now()->lt($job->retag_locked_until))
                                <button class="btn btn-sm btn-secondary lj-btn-mini mb-1" disabled title="Naka-lock hanggang {{ $job->retag_locked_until->timezone('Asia/Manila')->format('M d, Y g:i A') }}">✓ Done 🔒</button>
                                @else
                                <button class="btn btn-sm btn-primary lj-btn-mini mb-1" onclick="markDone({{ $job->id }})">✓ Done</button>
                                @endif
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

    @if(($mode ?? 'personal') === 'global')
    {{-- Set Amount history — sino ang nag-set ng amount sa libreng layout job (Andrew 2026-09-17) --}}
    <div class="lj-card mb-3" id="amountSetHistory">
        <div class="p-3">
            <h6 class="mb-1">📝 Set Amount History <span class="lj-badge">{{ isset($amountSetHistory) ? $amountSetHistory->count() : 0 }}</span></h6>
            <div class="lj-muted mb-2">Sino ang nag-set ng amount sa libreng layout job at kailan (pinakahuling 45). Dito makikita ang approval trail ng amount.</div>
            @if(isset($amountSetHistory) && $amountSetHistory->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>#</th><th>Job No</th><th>Customer</th><th>Layout Doer</th><th class="text-end">Amount</th><th>Pinagmulan</th><th>Set By</th><th>Set At</th></tr></thead>
                    <tbody>
                    @foreach($amountSetHistory as $h)
                    @php
                        $isSetAction = $h->amount_set_by !== null;
                        $actor = $isSetAction ? $h->amountSetter?->name : $h->creator?->name;
                        $when  = $isSetAction ? $h->amount_set_at : $h->created_at;
                    @endphp
                    <tr>
                        <td>#{{ $h->id }}</td>
                        <td><span class="fw-bold">{{ $h->job_no }}</span>@if($h->sale_id) <span class="lj-badge done ms-1">Sale #{{ $h->sale_id }}</span>@endif</td>
                        <td>{{ $h->displayCustomer() }}</td>
                        <td>{{ $h->gaUser?->name ?: '—' }}</td>
                        <td class="text-end lj-amount">₱{{ number_format((float) $h->amount, 2) }}</td>
                        <td><span class="lj-badge {{ $h->isFree() ? 'free' : 'paid' }}">{{ $h->isFree() ? 'Libre · Set Amount' : 'Bayad · created' }}</span></td>
                        <td>{{ $actor ?: '—' }}</td>
                        <td class="lj-muted">{{ $when?->format('M d, Y h:i A') ?: '—' }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="lj-muted text-center py-3">Wala pa pang naka-set na amount. 👍</div>
            @endif
        </div>
    </div>
    @endif
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

{{-- Hindi Tumuloy modal (client did not push through — walang sale na na-create) --}}
<div class="modal fade" id="cancelJobModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h6 class="modal-title">🚫 Hindi Tumuloy ang Client</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p class="lj-muted mb-2">Walang sale na na-create dahil hindi tumuloy ang client sa layout na ito. <b id="cancelJobNoLabel"></b></p>
        <label class="lj-muted">Reason (opsyonal — pero mas magandang may nakalagay)</label>
        <textarea id="cancelReason" class="form-control mb-3" rows="3" maxlength="1000" placeholder="e.g. Hindi na tumuloy ang client / hindi na nagpatuloy sa order"></textarea>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="cancelConfirmChk">
            <label class="form-check-label" for="cancelConfirmChk">Sigurado ka ba na <b>hindi tumuloy</b> ang client sa layout na ito?</label>
        </div>
        <input type="hidden" id="cancelJobId">
    </div>
    <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger" id="cancelJobBtn" disabled onclick="submitCancelJob()">Oo, Hindi Tumuloy</button>
    </div>
</div></div></div>

{{-- Libre → Bayad: upload payment modal (Andrew 2026-10-21) --}}
<div class="modal fade" id="freePaymentModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h6 class="modal-title">💳 Upload Payment <span class="lj-muted" id="freePayJobNoLabel"></span></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p class="lj-muted mb-2">Libre ang layout na ito — kung nagbayad pala ang client, i-upload dito. Magiging <b>BAYAD</b> ang job at lalabas sa Payment column, tapos i-verify ng verifier.</p>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="lj-muted">Amount (₱)</label>
                <input type="number" id="freePayAmount" class="form-control" min="0.01" step="0.01" placeholder="0.00">
            </div>
            <div class="col-6">
                <label class="lj-muted">Payment Account</label>
                <select id="freePayAccount" class="form-select">
                    <option value="">— piliin —</option>
                    @foreach($paymentAccounts ?? [] as $pa)
                    <option value="{{ $pa->id }}">{{ $pa->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="lj-muted">Reference #</label>
                <input type="text" id="freePayRef" class="form-control" placeholder="Transaction / ref #">
            </div>
            <div class="col-6">
                <label class="lj-muted">Date ng Payment</label>
                <input type="date" id="freePayDate" class="form-control" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
            </div>
        </div>
        <label class="lj-muted">Payment Screenshot</label>
        <input type="file" id="freePayProof" class="form-control" accept="image/*">
        <input type="hidden" id="freePayJobId">
    </div>
    <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success" onclick="submitFreePayment()">I-upload at gawing BAYAD</button></div>
</div></div></div>

{{-- Retag layout doer modal (habang open pa) --}}
<div class="modal fade" id="retagModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h6 class="modal-title">🔁 Retag ng Layout Doer <span class="lj-muted" id="retagJobNoLabel"></span></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p class="lj-muted mb-2">Kasalukuyang naka-tag: <b id="retagCurrentGa">—</b>. Pwede pang palitan hangga't <b>open</b> pa ang job.</p>
        <div id="retagUndoNote" style="display:none;" class="mb-2 p-2 rounded" style="background:#fef2f2;border:1px solid #fecaca;font-size:12px;">
            <b>⚠️ Done na ang job na ito.</b> Pag i-retag mo, <b>ma-undo ang Done</b> → balik sa <b>Open</b>, at <b>hindi pwedeng i-mark done ng 7 araw</b> (anti-abuse sa credit).
        </div>
        <label class="lj-muted">Bagong Layout Doer</label>
        <select id="retagGa" class="form-select">
            <option value="">— piliin ang layout doer —</option>
            @foreach($gaUsers ?? [] as $g)
            <option value="{{ $g->id }}">{{ $g->name }}</option>
            @endforeach
        </select>
        <input type="hidden" id="retagJobId">
        <input type="hidden" id="retagCurrentId">
    </div>
    <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-dark" id="retagSubmitBtn" onclick="submitRetag()">I-retag</button></div>
</div></div></div>

{{-- Re-upload payment after rejection (Andrew 2026-10-21) --}}
<div class="modal fade" id="resubmitPaymentModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h6 class="modal-title">📤 Upload Payment Ulit <span class="lj-muted" id="resubmitJobNoLabel"></span></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p class="lj-muted mb-2">Na-reject ang payment na ito. Mag-upload ng <b>bagong</b> reference at/o bagong screenshot — babalik ito sa verifier bilang <b>Pending</b>.</p>
        <label class="lj-muted">Payment Account</label>
        <select id="resubmitAccount" class="form-select mb-2">
            <option value="">— piliin ang account —</option>
            @foreach($paymentAccounts ?? [] as $pa)
            <option value="{{ $pa->id }}">{{ $pa->name }}</option>
            @endforeach
        </select>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="lj-muted">Reference #</label>
                <input type="text" id="resubmitRef" class="form-control" placeholder="Transaction / ref #">
            </div>
            <div class="col-6">
                <label class="lj-muted">Date ng Payment</label>
                <input type="date" id="resubmitDate" class="form-control" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
            </div>
        </div>
        <label class="lj-muted">Payment Screenshot (bagong proof)</label>
        <input type="file" id="resubmitProof" class="form-control" accept="image/*">
        <input type="hidden" id="resubmitJobId">
    </div>
    <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger" onclick="submitResubmitPayment()">I-submit Ulit</button></div>
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

function openSetAmount(id, jobNo, currentAmount) {
    document.getElementById('setAmountJobId').value = id;
    document.getElementById('setAmountValue').value = (currentAmount === null || currentAmount === undefined) ? '' : currentAmount;
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

function openCancelJob(id, jobNo, typeLabel) {
    document.getElementById('cancelJobId').value = id;
    document.getElementById('cancelJobNoLabel').textContent = jobNo ? '(' + jobNo + ' · ' + typeLabel + ')' : '';
    document.getElementById('cancelReason').value = '';
    document.getElementById('cancelConfirmChk').checked = false;
    document.getElementById('cancelJobBtn').disabled = true;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('cancelJobModal')).show();
}

document.getElementById('cancelConfirmChk')?.addEventListener('change', function () {
    document.getElementById('cancelJobBtn').disabled = !this.checked;
});

function submitCancelJob() {
    const id = document.getElementById('cancelJobId').value;
    const reason = document.getElementById('cancelReason').value.trim();
    if (!document.getElementById('cancelConfirmChk').checked) return alert('I-check muna ang confirmation.');
    fetch('/sales/layout-jobs/' + id + '/cancel', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify({reason: reason})
    }).then(r => r.json()).then(d => { alert(d.error || 'Na-marka na: Hindi Tumuloy ✓'); if (!d.error) location.reload(); });
}

function openFreePayment(id, jobNo) {
    document.getElementById('freePayJobId').value = id;
    document.getElementById('freePayJobNoLabel').textContent = jobNo ? '(' + jobNo + ')' : '';
    document.getElementById('freePayAmount').value = '';
    document.getElementById('freePayAccount').value = '';
    document.getElementById('freePayRef').value = '';
    document.getElementById('freePayDate').value = new Date().toISOString().slice(0, 10);
    document.getElementById('freePayProof').value = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('freePaymentModal')).show();
}

function submitFreePayment() {
    const id = document.getElementById('freePayJobId').value;
    const amount = document.getElementById('freePayAmount').value;
    const account = document.getElementById('freePayAccount').value;
    const ref = document.getElementById('freePayRef').value.trim();
    const proof = document.getElementById('freePayProof').files[0];
    if (!amount || Number(amount) <= 0) return alert('Ilagay ang amount na binayad.');
    if (!account) return alert('Piliin kung saang account binayad.');
    if (!ref && !proof) return alert('Maglagay ng reference # O screenshot ng payment.');
    if (!confirm('Gawing BAYAD ang libreng layout na ito at i-submit sa verifier?')) return;
    const fd = new FormData();
    fd.append('amount', amount);
    fd.append('payment_account_id', account);
    fd.append('payment_reference', ref);
    fd.append('payment_date', document.getElementById('freePayDate').value || '');
    if (proof) fd.append('payment_screenshot', proof);
    fetch('/sales/layout-jobs/' + id + '/upload-payment', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
        body: fd
    }).then(r => r.json()).then(d => {
        if (d.error || d.message) return alert(d.error || ('Error: ' + d.message));
        alert('Naka-upload ✓ BAYAD na ang job — hintayin ang verification.');
        location.reload();
    }).catch(() => alert('May error sa upload — subukan ulit.'));
}

function confirmReceipt(payoutId, amount) {
    if (!confirm('Kinumpirma mo bang NATANGGAP mo na ang ₱' + Number(amount).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' na payout?\n\nPag na-confirm, maitatak na itong VERIFIED.')) return;
    fetch('/sales/layout-jobs/payout/' + payoutId + '/confirm-receipt', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}
    }).then(r => r.json()).then(d => {
        alert(d.error || 'Confirmed ✓ Salamat!');
        if (!d.error) location.reload();
    }).catch(() => alert('May error — subukan ulit.'));
}

function openRetag(id, jobNo, currentId, currentName, status) {
    document.getElementById('retagJobId').value = id;
    document.getElementById('retagCurrentId').value = currentId || '';
    document.getElementById('retagJobNoLabel').textContent = jobNo ? '(' + jobNo + ')' : '';
    document.getElementById('retagCurrentGa').textContent = currentName || '—';
    document.getElementById('retagGa').value = '';
    const isDone = status === 'done';
    document.getElementById('retagUndoNote').style.display = isDone ? 'block' : 'none';
    document.getElementById('retagSubmitBtn').textContent = isDone ? 'Undo at I-retag' : 'I-retag';
    document.getElementById('retagSubmitBtn').className = isDone ? 'btn btn-danger' : 'btn btn-dark';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('retagModal')).show();
}

function submitRetag() {
    const id = document.getElementById('retagJobId').value;
    const gaId = document.getElementById('retagGa').value;
    if (!gaId) return alert('Piliin ang bagong layout doer.');
    if (String(gaId) === String(document.getElementById('retagCurrentId').value)) {
        return alert('Siya na ang naka-tag — pumili ng iba.');
    }
    const isUndo = document.getElementById('retagUndoNote').style.display === 'block';
    if (isUndo) {
        const ok = confirm('UNDO + RETAG\n\nBabalik sa OPEN ang job na ito at mawawala ang "Done" ni ' +
            (document.getElementById('retagCurrentGa').textContent || 'kasalukuyang doer') +
            '.\n\nPagka-retag, HINDI pwedeng i-mark done ng 7 araw.\n\nSigurado ka ba?');
        if (!ok) return;
    } else {
        if (!confirm('I-retag ang layout doer? Lilipat ang job sa account ng bagong doer.')) return;
    }
    fetch('/sales/layout-jobs/' + id + '/retag', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify({ga_user_id: gaId})
    }).then(r => r.json()).then(d => {
        if (d.error || d.message) return alert(d.error || ('Error: ' + d.message));
        let msg = 'Na-retag kay ' + (d.ga || 'bagong GA') + ' ✓';
        if (d.undone) msg += '\n\nNa-undo ang Done → balik sa Open. Naka-lock ng 7 araw bago pwedeng i-done ulit.';
        alert(msg);
        location.reload();
    }).catch(() => alert('May error — subukan ulit.'));
}

function openResubmitPayment(id, jobNo, accountId, ref) {
    document.getElementById('resubmitJobId').value = id;
    document.getElementById('resubmitJobNoLabel').textContent = jobNo ? '(' + jobNo + ')' : '';
    document.getElementById('resubmitAccount').value = accountId ? String(accountId) : '';
    document.getElementById('resubmitRef').value = ref || '';
    document.getElementById('resubmitDate').value = new Date().toISOString().slice(0, 10);
    document.getElementById('resubmitProof').value = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('resubmitPaymentModal')).show();
}

function submitResubmitPayment() {
    const id = document.getElementById('resubmitJobId').value;
    const ref = document.getElementById('resubmitRef').value.trim();
    const proof = document.getElementById('resubmitProof').files[0];
    if (!ref && !proof) return alert('Maglagay ng bagong reference # O bagong screenshot.');
    const fd = new FormData();
    fd.append('payment_account_id', document.getElementById('resubmitAccount').value || '');
    fd.append('payment_reference', ref);
    fd.append('payment_date', document.getElementById('resubmitDate').value || '');
    if (proof) fd.append('payment_screenshot', proof);
    fetch('/sales/layout-jobs/' + id + '/resubmit-payment', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
        body: fd
    }).then(r => r.json()).then(d => {
        alert(d.error || (d.message ? 'Error: ' + d.message : 'Naka-submit ulit — balik sa Pending ✓'));
        if (!d.error && !d.message) location.reload();
    }).catch(() => alert('May error sa upload — subukan ulit.'));
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
    // Reference # minimum 6 characters (Andrew 2026-09-17)
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
