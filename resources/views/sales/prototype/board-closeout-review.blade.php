@extends('layouts.app')

@section('title', 'Close Out Review — Board Member')

@push('styles')
<style>
    .cx-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #4c1d95 50%, #7c3aed 100%);
        border-radius: 16px;
        padding: 20px 26px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(76, 29, 149, .25);
    }
    .cx-hero::after { content: "🕵️"; position: absolute; right: 18px; bottom: -18px; font-size: 84px; opacity: .15; transform: rotate(-8deg); }
    .cx-hero h4 { font-weight: 800; letter-spacing: .3px; }
    .cx-hero .sub { opacity: .92; font-size: 12.5px; }
    .cx-stat { background: #fff; border: 1px solid #eef0f4; border-radius: 14px; padding: 14px 18px; box-shadow: 0 2px 10px rgba(17, 24, 39, .05); text-align: center; }
    .cx-stat .val { font-size: 24px; font-weight: 800; line-height: 1.1; color: #111827; }
    .cx-stat .lbl { font-size: 11.5px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
    .cx-card { background: #fff; border: 1px solid #eef0f4; border-radius: 14px; box-shadow: 0 2px 10px rgba(17, 24, 39, .04); overflow: hidden; }
    .cx-reason { background: #f5f3ff; border-left: 3px solid #a78bfa; border-radius: 0 8px 8px 0; padding: 8px 12px; font-size: .88rem; }
    .cx-flow { background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 10px 12px; font-size: .82rem; }
    .cx-proof { width: 100%; max-height: 220px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0; cursor: pointer; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    @include('sales.prototype._board-tabs')

    <div class="cx-hero mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Close Out Review</h4>
            <div class="sub mt-1">Mga payment close-out (EWT/taxes/bawas) — listahan para makita. <strong>View-only</strong>: walang pwedeng gawin dito.</div>
        </div>
        <span class="badge bg-light text-dark border"><i class="fas fa-eye me-1"></i>View-only</span>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="cx-stat"><div class="val">{{ $allCount }}</div><div class="lbl">Total Close-outs</div></div>
        </div>
        <div class="col-md-4">
            <div class="cx-stat"><div class="val">{{ $pendingReviewCount }}</div><div class="lbl">Pending Review (accepted)</div></div>
        </div>
        <div class="col-md-4">
            <div class="cx-stat"><div class="val">{{ $archivedReviewCount }}</div><div class="lbl">Reviewed (archived)</div></div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-2 flex-wrap">
        <a href="{{ route('sales.prototype.board.close-out-review') }}" class="btn btn-sm {{ $filter === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">All ({{ $allCount }})</a>
        <a href="{{ route('sales.prototype.board.close-out-review', ['filter' => 'pending']) }}" class="btn btn-sm {{ $filter === 'pending' ? 'btn-dark' : 'btn-outline-dark' }}"><i class="fas fa-hourglass-half me-1"></i>Pending Review ({{ $pendingReviewCount }})</a>
        <a href="{{ route('sales.prototype.board.close-out-review', ['filter' => 'reviewed']) }}" class="btn btn-sm {{ $filter === 'reviewed' ? 'btn-dark' : 'btn-outline-dark' }}"><i class="fas fa-box-archive me-1"></i>Reviewed ({{ $archivedReviewCount }})</a>
    </div>

    <form method="GET" action="{{ route('sales.prototype.board.close-out-review') }}" class="d-flex gap-2 mb-3">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Search sales # / customer…" style="max-width:320px;">
        <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-search"></i></button>
        @if($q)
            <a href="{{ route('sales.prototype.board.close-out-review', ['filter' => $filter]) }}" class="btn btn-sm btn-outline-danger">Clear</a>
        @endif
    </form>

    <div class="cx-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sale #</th>
                        <th>Customer</th>
                        <th>Dept</th>
                        <th class="text-end">Close-out Amount</th>
                        <th>Requested by</th>
                        <th>Accountant</th>
                        <th>Status</th>
                        <th>Reviewed by</th>
                        <th>Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $rv)
                        <tr>
                            <td>
                                <a href="{{ route('sales.prototype.show', $rv->prototype_sale_id) }}" class="text-decoration-none fw-semibold" title="Tingnan ang sale (view-only)">
                                    {{ $rv->sales_number ?: ('#' . $rv->prototype_sale_id) }}
                                </a>
                            </td>
                            <td>{{ $rv->customer_name }}</td>
                            <td><span class="badge bg-secondary">{{ $departmentLabels[$rv->department_id] ?? ('Dept ' . $rv->department_id) }}</span></td>
                            <td class="text-end fw-bold text-danger">₱{{ number_format($rv->amount, 2) }}</td>
                            <td class="small">{{ $rv->requested_by_name ?: '—' }}</td>
                            <td class="small">{{ $rv->accountant_name ?: '—' }}</td>
                            <td>
                                @if($rv->status === 'accepted')
                                    <span class="badge bg-success">✓ Accepted — pending review</span>
                                @elseif($rv->status === 'reviewed')
                                    <span class="badge bg-secondary">Reviewed</span>
                                @else
                                    <span class="badge bg-warning text-dark">{{ $rv->status }}</span>
                                @endif
                            </td>
                            <td class="small">{{ $rv->reviewed_by_name ?: '—' }}</td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($rv->created_at)->format('M d, Y') }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#cx-{{ $rv->id }}" aria-expanded="false" aria-controls="cx-{{ $rv->id }}">
                                    <i class="fas fa-eye me-1"></i>View
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse" id="cx-{{ $rv->id }}">
                            <td colspan="10" class="bg-light">
                                <div class="p-2">
                                    <div class="cx-flow mb-2 d-flex flex-wrap gap-3">
                                        <span><i class="fas fa-user me-1"></i>Requested: <strong>{{ $rv->requested_by_name ?: '—' }}</strong> · {{ \Carbon\Carbon::parse($rv->created_at)->format('M d, g:i A') }}</span>
                                        <span><i class="fas fa-user-check text-success me-1"></i>Accountant: <strong>{{ $rv->accountant_name ?: '—' }}</strong> @if($rv->accountant_action_at)· {{ \Carbon\Carbon::parse($rv->accountant_action_at)->format('M d, g:i A') }} @endif</span>
                                        @if($rv->status === 'reviewed')
                                            <span><i class="fas fa-clipboard-check text-primary me-1"></i>Reviewed: <strong>{{ $rv->reviewed_by_name ?: '—' }}</strong> @if($rv->reviewed_at)· {{ \Carbon\Carbon::parse($rv->reviewed_at)->format('M d, Y g:i A') }} @endif</span>
                                        @endif
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-7">
                                            <div class="mb-2"><strong>Close-out amount:</strong> <span class="text-danger fw-bold">₱{{ number_format($rv->amount, 2) }}</span></div>
                                            @if($rv->reference_number)
                                                <div class="small text-muted mb-2"><i class="fas fa-hashtag me-1"></i>Ref #{{ $rv->reference_number }}</div>
                                            @endif
                                            <div class="cx-reason"><i class="fas fa-comment-dots me-1"></i><strong>Reason:</strong> {{ $rv->reason }}</div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="small text-muted mb-1"><i class="fas fa-image me-1"></i>Proof image</div>
                                            @if($rv->proof_image)
                                                <img src="{{ $rv->proof_image }}" alt="Proof" class="cx-proof" onclick="cxLightbox('{{ $rv->proof_image }}')">
                                            @else
                                                <span class="text-muted small">— walang proof —</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4">Walang payment close-out dito. ✅</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $reviews->links() }}</div>

</div>

<div id="cxLightbox" style="display:none;"></div>
@endsection

@push('scripts')
<script>
window.cxLightbox = function(src) {
    var lb = document.getElementById('cxLightbox');
    if (!lb) return;
    lb.innerHTML = '';
    lb.style.cssText = 'display:flex!important;align-items:center;justify-content:center;position:fixed;top:0;left:0;width:100%;height:100%;z-index:100000;background:rgba(0,0,0,0.85);cursor:zoom-out;';
    var closeBtn = document.createElement('button');
    closeBtn.innerHTML = '&times;';
    closeBtn.style.cssText = 'position:absolute;top:15px;right:25px;font-size:32px;color:white;background:none;border:none;cursor:pointer;z-index:100001;';
    lb.appendChild(closeBtn);
    var img = document.createElement('img');
    img.src = src;
    img.style.cssText = 'max-width:90%;max-height:90%;border-radius:8px;box-shadow:0 10px 40px rgba(0,0,0,.6);';
    lb.appendChild(img);
    lb.addEventListener('click', function(){ lb.style.display = 'none'; });
};
</script>
@endpush
