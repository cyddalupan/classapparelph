@extends('layouts.app')

@section('title', 'Close-out Review — Payment Close-outs')

@push('styles')
<style>
    .px-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #4c1d95 50%, #7c3aed 100%);
        border-radius: 16px;
        padding: 20px 26px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(76, 29, 149, .25);
    }
    .px-hero::after {
        content: "🕵️";
        position: absolute;
        right: 18px;
        bottom: -18px;
        font-size: 84px;
        opacity: .15;
        transform: rotate(-8deg);
    }
    .px-hero h4 { font-weight: 800; letter-spacing: .3px; }
    .px-hero .sub { opacity: .92; font-size: 12.5px; }
    .px-stat {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        padding: 14px 18px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .05);
        height: 100%;
        text-align: center;
    }
    .px-stat .val { font-size: 24px; font-weight: 800; line-height: 1.1; color: #111827; }
    .px-stat .lbl { font-size: 11.5px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
    .px-card {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .04);
        overflow: hidden;
    }
    .px-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f3f5;
        background: #fafbfc;
        font-weight: 700;
    }
    .px-reason {
        background: #f5f3ff;
        border-left: 3px solid #a78bfa;
        border-radius: 0 8px 8px 0;
        padding: 8px 12px;
        font-size: .88rem;
    }
    .px-proof {
        width: 100%;
        max-height: 220px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
    }
    .px-flow {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: .82rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <div class="px-hero mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Close-out Review — Payment Close-outs</h4>
            <div class="sub mt-1">Mga close-out na in-ACCEPT ng Accountant. I-review ang dahilan at proof, tapos i-mark as REVIEWED para ma-unlock ang archive ng sale.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.prototype.payment-review.accountant') }}" class="btn btn-light btn-sm"><i class="fas fa-file-invoice-dollar me-1"></i> Accountant Queue</a>
            <a href="{{ route('sales.prototype.list') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Manager List</a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('sales.prototype.payment-review.executive') }}" class="mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Search sales # / customer...">
            </div>
            <div class="col-md-3">
                <select name="filter" class="form-select">
                    <option value="pending" {{ $filter === 'pending' ? 'selected' : '' }}>⏳ Pending review (accepted)</option>
                    <option value="reviewed" {{ $filter === 'reviewed' ? 'selected' : '' }}>✓ Reviewed</option>
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dark w-100"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
        </div>
    </form>

    @if($reviews->isEmpty())
        <div class="px-card">
            <div class="p-5 text-center text-muted">
                <i class="fas fa-check-double fa-3x mb-3 d-block opacity-50"></i>
                Walang payment close-out dito. ✅
            </div>
        </div>
    @else
        @foreach($reviews as $rv)
        <div class="px-card mb-3" id="review-{{ $rv->id }}">
            <div class="px-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <a href="{{ route('sales.prototype.show', $rv->prototype_sale_id) }}" class="text-decoration-none fw-bold">
                        {{ $rv->sales_number ?: '#' . $rv->prototype_sale_id }}
                    </a>
                    <span class="ms-1 text-muted small">{{ $rv->customer_name }}</span>
                    @if($rv->status === 'accepted')
                        <span class="badge bg-success ms-2">✓ Accepted — pending review</span>
                    @elseif($rv->status === 'reviewed')
                        <span class="badge bg-secondary ms-2">Reviewed</span>
                    @endif
                </div>
                <div class="small text-muted">
                    <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($rv->created_at)->format('M d, Y g:i A') }}
                </div>
            </div>
            <div class="p-3">
                <div class="px-flow mb-3 d-flex flex-wrap gap-3">
                    <span><i class="fas fa-user me-1"></i>Requested: <strong>{{ $rv->requested_by_name }}</strong> · {{ \Carbon\Carbon::parse($rv->created_at)->format('M d, g:i A') }}</span>
                    <span><i class="fas fa-user-check text-success me-1"></i>Accountant: <strong>{{ $rv->accountant_name ?: '—' }}</strong> @if($rv->accountant_action_at)· {{ \Carbon\Carbon::parse($rv->accountant_action_at)->format('M d, g:i A') }} @endif</span>
                    @if($rv->status === 'reviewed')
                        <span><i class="fas fa-clipboard-check text-primary me-1"></i>Reviewed: <strong>{{ $rv->reviewed_by_name }}</strong> @if($rv->reviewed_at)· {{ \Carbon\Carbon::parse($rv->reviewed_at)->format('M d, Y g:i A') }} @endif</span>
                    @endif
                </div>

                <div class="row g-3">
                    <div class="col-md-7">
                        <div class="fw-bold mb-2 d-flex align-items-center gap-2">
                            <span>Close-out amount (in-accept ng Accountant):</span>
                            <span class="text-danger fs-5">₱{{ number_format($rv->amount, 2) }}</span>
                        </div>
                        @if($rv->reference_number)
                            <div class="small text-muted mb-2"><i class="fas fa-hashtag me-1"></i>Ref #{{ $rv->reference_number }}</div>
                        @endif
                        <div class="px-reason mb-2">
                            <i class="fas fa-comment-dots me-1"></i><strong>Reason (mula sa Sales Agent):</strong> {{ $rv->reason }}
                        </div>
                        @if($rv->accountant_note)
                            <div class="px-reason mb-2" style="background:#f0fdf4;border-left-color:#4ade80;">
                                <i class="fas fa-stamp text-success me-1"></i><strong>Accountant note:</strong> {{ $rv->accountant_note }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-5">
                        <div class="small text-muted mb-1"><i class="fas fa-image me-1"></i>Proof image (na-verify ng Accountant)</div>
                        <img src="{{ $rv->proof_image }}" alt="Proof" class="px-proof" onclick="openLightbox('{{ $rv->proof_image }}')">
                    </div>
                </div>

                @if($rv->status === 'accepted')
                <hr>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="small text-muted me-auto">
                        <i class="fas fa-info-circle me-1"></i>Pag ma-mark na reviewed → puwede nang i-archive ang sale na ito. (Recorded sa audit history.)
                    </div>
                    <button class="btn btn-primary" onclick="markReviewed({{ $rv->id }})">
                        <i class="fas fa-clipboard-check me-1"></i>Mark as Reviewed
                    </button>
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
window.markReviewed = function(id) {
    var btn = event.target.closest('button');
    var original = btn.innerHTML;
    if (!confirm('I-mark as REVIEWED ang close-out na ito? Pagkatapos nito, puwede nang i-archive ang sale.')) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    fetch('{{ route("sales.prototype.payment-review.mark-reviewed", "REVIEW_ID") }}'.replace('REVIEW_ID', id), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
        },
        body: JSON.stringify({})
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
