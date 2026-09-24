@extends('layouts.app')

@section('title', 'Payment Review Queue')

@push('styles')
<style>
    .pr-hero {
        background: linear-gradient(135deg, #0f766e 0%, #0d9488 45%, #14b8a6 100%);
        border-radius: 16px;
        padding: 20px 26px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(13, 148, 136, .25);
    }
    .pr-hero::after {
        content: "🧾";
        position: absolute;
        right: 18px;
        bottom: -18px;
        font-size: 84px;
        opacity: .15;
        transform: rotate(-8deg);
    }
    .pr-hero h4 { font-weight: 800; letter-spacing: .3px; }
    .pr-hero .sub { opacity: .92; font-size: 12.5px; }
    .pr-stat {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        padding: 14px 18px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .05);
        height: 100%;
        text-align: center;
    }
    .pr-stat .val { font-size: 24px; font-weight: 800; line-height: 1.1; color: #111827; }
    .pr-stat .lbl { font-size: 11.5px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
    .pr-card {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .04);
        overflow: hidden;
    }
    .pr-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f3f5;
        background: #fafbfc;
        font-weight: 700;
    }
    .pr-reason {
        background: #f0fdfa;
        border-left: 3px solid #2dd4bf;
        border-radius: 0 8px 8px 0;
        padding: 8px 12px;
        font-size: .88rem;
    }
    .pr-proof {
        width: 100%;
        max-height: 220px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
    }
    .pr-note {
        background: #fff7ed;
        border-left: 3px solid #fbbf24;
        border-radius: 0 8px 8px 0;
        padding: 8px 12px;
        font-size: .85rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <div class="pr-hero mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Payment Review Queue</h4>
            <div class="sub mt-1">Balance close-out requests (EWT/taxes/bawas) — i-verify ang proof, tapos ACCEPT (zero balance → DONE unlocked) o REJECT (may dahilan).</div>
        </div>
        <a href="{{ route('sales.prototype.list') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Manager List</a>
    </div>

    <!-- Status counts -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <a href="{{ route('sales.prototype.payment-review.accountant', ['status' => 'requested']) }}" class="text-decoration-none">
                <div class="pr-stat {{ $status === 'requested' ? 'border border-success' : '' }}">
                    <div class="val text-warning">{{ number_format($counts['requested']) }}</div>
                    <div class="lbl">⏳ For Review</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('sales.prototype.payment-review.accountant', ['status' => 'accepted']) }}" class="text-decoration-none">
                <div class="pr-stat {{ $status === 'accepted' ? 'border border-success' : '' }}">
                    <div class="val text-success">{{ number_format($counts['accepted']) }}</div>
                    <div class="lbl">✓ Accepted</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('sales.prototype.payment-review.accountant', ['status' => 'rejected']) }}" class="text-decoration-none">
                <div class="pr-stat {{ $status === 'rejected' ? 'border border-danger' : '' }}">
                    <div class="val text-danger">{{ number_format($counts['rejected']) }}</div>
                    <div class="lbl">✗ Rejected</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('sales.prototype.payment-review.accountant', ['status' => 'reviewed']) }}" class="text-decoration-none">
                <div class="pr-stat {{ $status === 'reviewed' ? 'border border-success' : '' }}">
                    <div class="val" style="color:#7c3aed;">{{ number_format($counts['reviewed']) }}</div>
                    <div class="lbl">Reviewed</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('sales.prototype.payment-review.accountant') }}" class="mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Search sales # / customer...">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="requested" {{ $status === 'requested' ? 'selected' : '' }}>⏳ For Review</option>
                    <option value="accepted" {{ $status === 'accepted' ? 'selected' : '' }}>✓ Accepted</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>✗ Rejected</option>
                    <option value="reviewed" {{ $status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dark w-100"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
            <div class="col-md-3 text-md-end">
                @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isCoo()))
                <a href="{{ route('sales.prototype.payment-review.executive') }}" class="btn btn-outline-primary w-100"><i class="fas fa-clipboard-check me-1"></i> Close-out Review</a>
                @endif
            </div>
        </div>
    </form>

    <!-- List -->
    @if($reviews->isEmpty())
        <div class="pr-card">
            <div class="p-5 text-center text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
                Walang payment review request dito. ✅
            </div>
        </div>
    @else
        @foreach($reviews as $rv)
        <div class="pr-card mb-3" id="review-{{ $rv->id }}">
            <div class="pr-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <a href="{{ route('sales.prototype.show', $rv->prototype_sale_id) }}" class="text-decoration-none fw-bold">
                        {{ $rv->sales_number ?: '#' . $rv->prototype_sale_id }}
                    </a>
                    <span class="ms-1 text-muted small">{{ $rv->customer_name }}</span>
                    @php
                        $rvBadge = match($rv->status) {
                            'requested' => ['bg-warning text-dark', '⏳ For Review'],
                            'accepted'  => ['bg-success', '✓ Accepted — for close-out review'],
                            'rejected'  => ['bg-danger', '✗ Rejected'],
                            'reviewed'  => ['bg-secondary', 'Reviewed'],
                            default     => ['bg-secondary', ucfirst($rv->status)],
                        };
                    @endphp
                    <span class="badge {{ $rvBadge[0] }} ms-2">{{ $rvBadge[1] }}</span>
                </div>
                <div class="small text-muted">
                    <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($rv->created_at)->format('M d, Y g:i A') }}
                </div>
            </div>
            <div class="p-3">
                <div class="row g-3">
                    <div class="col-md-7">
                        <div class="mb-2">
                            <span class="badge bg-secondary">Requested by</span>
                            <span class="ms-1">{{ $rv->requested_by_name }}</span>
                        </div>
                        <div class="mb-3">
                            <div class="fw-bold d-flex align-items-center gap-2">
                                <span>Close-out amount:</span>
                                <span class="text-danger fs-5">₱{{ number_format($rv->amount, 2) }}</span>
                            </div>
                            @if($rv->reference_number)
                                <div class="small text-muted"><i class="fas fa-hashtag me-1"></i>Ref #{{ $rv->reference_number }}</div>
                            @endif
                        </div>
                        <div class="pr-reason mb-2">
                            <i class="fas fa-comment-dots me-1"></i><strong>Reason:</strong> {{ $rv->reason }}
                        </div>
                        @if($rv->sale_payment_note)
                            <div class="pr-note mb-2" style="background:#fffbeb;border-left-color:#f59e0b;">
                                <i class="fas fa-sticky-note me-1"></i><strong>Note mula sa Sales (Step 3 payment):</strong> {{ $rv->sale_payment_note }}
                            </div>
                        @endif

                        @if($rv->status === 'rejected' && $rv->accountant_note)
                            <div class="pr-note mt-2">
                                <i class="fas fa-times-circle text-danger me-1"></i><strong>Rejection reason ({{ $rv->accountant_name ?: 'Accountant' }}):</strong> {{ $rv->accountant_note }}
                            </div>
                        @endif
                        @if(in_array($rv->status, ['accepted', 'reviewed']) && $rv->accountant_note)
                            <div class="pr-note mt-2" style="background:#f0fdf4;border-left-color:#4ade80;">
                                <i class="fas fa-check-circle text-success me-1"></i><strong>Note ({{ $rv->accountant_name ?: 'Accountant' }}):</strong> {{ $rv->accountant_note }}
                            </div>
                        @endif
                        @if($rv->status === 'reviewed')
                            <div class="small text-muted mt-2">
                                <i class="fas fa-clipboard-check me-1"></i>Reviewed by <strong>{{ $rv->reviewed_by_name }}</strong>
                                @if($rv->reviewed_at) · {{ \Carbon\Carbon::parse($rv->reviewed_at)->format('M d, Y g:i A') }} @endif
                            </div>
                        @endif
                    </div>
                    <div class="col-md-5">
                        <div class="small text-muted mb-1"><i class="fas fa-image me-1"></i>Proof image ({{ $rv->accountant_name ? 'verified' : 'i-verify' }})</div>
                        <img src="{{ $rv->proof_image }}" alt="Proof" class="pr-proof" onclick="openLightbox('{{ $rv->proof_image }}')">
                    </div>
                </div>

                @if($rv->status === 'requested')
                <hr>
                <div class="row g-2 align-items-start">
                    <div class="col-md-8">
                        <textarea id="note-{{ $rv->id }}" class="form-control" rows="2" placeholder="Accountant note — REJECT ay REQUIRED ang dahilan (min 10 characters); ACCEPT ay optional."></textarea>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button class="btn btn-success flex-fill" onclick="decision({{ $rv->id }}, 'accept')">
                            <i class="fas fa-check me-1"></i>Accept — Close-out
                        </button>
                        <button class="btn btn-danger flex-fill" onclick="decision({{ $rv->id }}, 'reject')">
                            <i class="fas fa-times me-1"></i>Reject
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach

        <div class="mt-3">
            {{ $reviews->links() }}
        </div>
    @endif
</div>

<!-- Lightbox -->
<div id="imageLightbox" style="display:none;"></div>
@endsection

@push('scripts')
<script>
window.decision = function(id, action) {
    var note = (document.getElementById('note-' + id) || {}).value || '';
    if (action === 'reject' && note.trim().length < 10) {
        alert('Kailangan ng dahilan (min 10 characters) para i-reject ang request.');
        return;
    }
    var btn = event.target.closest('button');
    var original = btn.innerHTML;
    if (!confirm(action === 'accept'
        ? 'I-ACCEPT ang close-out na ito? Zero ang balance → ma-u-unlock ang DONE, at mapupunta sa close-out review (final sign-off).'
        : 'I-REJECT ang request na ito? Lalabas ito sa payment history at audit trail.')) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    fetch('{{ route("sales.prototype.payment-review.decision", "REVIEW_ID") }}'.replace('REVIEW_ID', id), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
        },
        body: JSON.stringify({ action: action, accountant_note: note })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed.');
            btn.disabled = false;
            btn.innerHTML = original;
        }
    })
    .catch(function() { alert('Request failed.'); btn.disabled = false; btn.innerHTML = original; });
};

window.openLightbox = function(src) {
    var lb = document.getElementById('imageLightbox');
    if (!lb) return;
    lb.innerHTML = '';
    lb.style.cssText = 'display:flex!important;align-items:center;justify-content:center;position:fixed;top:0;left:0;width:100%;height:100%;z-index:100000;background:rgba(0,0,0,0.85);cursor:zoom-out;';
    var closeBtn = document.createElement('button');
    closeBtn.innerHTML = '&times;';
    closeBtn.style.cssText = 'position:absolute;top:15px;right:25px;font-size:32px;color:white;background:none;border:none;cursor:pointer;z-index:100001;';
    closeBtn.addEventListener('click', function(e) { e.stopPropagation(); closeLightbox(); });
    lb.appendChild(closeBtn);
    var img = document.createElement('img');
    img.src = src;
    img.style.cssText = 'max-width:90%;max-height:90%;border-radius:8px;box-shadow:0 10px 40px rgba(0,0,0,.6);';
    lb.appendChild(img);
    lb.addEventListener('click', function() { closeLightbox(); });
};
function closeLightbox() {
    var lb = document.getElementById('imageLightbox');
    if (lb) lb.style.display = 'none';
}
</script>
@endpush
