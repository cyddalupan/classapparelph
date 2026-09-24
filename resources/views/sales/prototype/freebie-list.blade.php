@extends('layouts.app')

@section('title', 'Freebie List')

@push('styles')
<style>
    .fb-hero {
        background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 45%, #a855f7 100%);
        border-radius: 16px;
        padding: 20px 26px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(124, 58, 237, .25);
    }
    .fb-hero::after {
        content: "🎁";
        position: absolute;
        right: 18px;
        bottom: -18px;
        font-size: 84px;
        opacity: .16;
        transform: rotate(-8deg);
    }
    .fb-hero h4 { font-weight: 800; letter-spacing: .3px; }
    .fb-hero .sub { opacity: .92; font-size: 12.5px; }
    .fb-stat {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        padding: 14px 18px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .05);
        height: 100%;
    }
    .fb-stat .val { font-size: 24px; font-weight: 800; line-height: 1.1; color: #111827; }
    .fb-stat .lbl { font-size: 11.5px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
    .fb-card {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .04);
        overflow: hidden;
    }
    .fb-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f3f5;
        background: #fafbfc;
    }
    .fb-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 4px 0;
    }
    .fb-muted { color: #6b7280; font-size: .8rem; }
    .fb-notes {
        background: #faf5ff;
        border-left: 3px solid #c4b5fd;
        border-radius: 0 6px 6px 0;
        padding: 6px 10px;
        font-size: .82rem;
        font-style: italic;
        color: #5b21b6;
    }
    .fb-toplist {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .fb-toplist li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 8px 4px;
        border-bottom: 1px dashed #eef0f4;
        font-size: .88rem;
    }
    .fb-toplist li:last-child { border-bottom: none; }
    .fb-rank {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #ede9fe;
        color: #6d28d9;
        font-size: .72rem;
        font-weight: 800;
        flex: 0 0 auto;
    }
    .fb-qty-pill {
        background: #f0fdf4;
        color: #15803d;
        font-weight: 700;
        font-size: .8rem;
        padding: 2px 8px;
        border-radius: 20px;
        white-space: nowrap;
    }
    .fb-status-legend span { font-size: .8rem; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <div class="fb-hero mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-0"><i class="fas fa-gift me-2"></i>Freebie List & Review</h4>
            <div class="sub mt-1">Lahat ng freebie requests — review, filters, at stats kung sino madalas mag-request at anong freebie ang madalas ibigay.</div>
        </div>
        <a href="{{ route('sales.prototype.list') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Manager List</a>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md">
            <div class="fb-stat">
                <div class="val">{{ number_format($totalRequests) }}</div>
                <div class="lbl">Total Requests</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="fb-stat">
                <div class="val text-warning">{{ number_format($pendingCount) }}</div>
                <div class="lbl">Pending ⏳</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="fb-stat">
                <div class="val text-success">{{ number_format($approvedCount) }}</div>
                <div class="lbl">Approved (slips)</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="fb-stat">
                <div class="val" style="color:#d97706;">{{ number_format($awaitingAuditCount) }}</div>
                <div class="lbl">Awaiting Audit 🕵️</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="fb-stat">
                <div class="val text-danger">{{ number_format($rejectedCount) }}</div>
                <div class="lbl">Rejected</div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="fb-stat">
                <div class="val" style="color:#7c3aed;">{{ number_format($givenQty) }}</div>
                <div class="lbl">Total pcs given 🎁</div>
            </div>
        </div>
    </div>

    <!-- Top requesters + Top given freebies -->
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="fb-card">
                <div class="fb-card-header fw-bold"><i class="fas fa-user-tie me-2" style="color:#7c3aed;"></i>Madalas mag-request</div>
                <div class="p-3">
                    @if($topRequesters->isNotEmpty())
                        <ul class="fb-toplist">
                            @foreach($topRequesters as $i => $tr)
                                <li>
                                    <span class="d-flex align-items-center gap-2 min-w-0">
                                        <span class="fb-rank">{{ $i + 1 }}</span>
                                        <span class="text-truncate">{{ $tr->user_name }}</span>
                                    </span>
                                    <span class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <span class="badge bg-secondary">{{ $tr->req_count }} req</span>
                                        <span class="fb-qty-pill">🎁 {{ $tr->given_qty }} pcs</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="fb-muted">Wala pang freebie request.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="fb-card">
                <div class="fb-card-header fw-bold"><i class="fas fa-crown me-2" style="color:#7c3aed;"></i>Madalas ibigay na freebie</div>
                <div class="p-3">
                    @if($topItems->isNotEmpty())
                        <ul class="fb-toplist">
                            @foreach($topItems as $i => $ti)
                                <li>
                                    <span class="d-flex align-items-center gap-2 min-w-0">
                                        <span class="fb-rank">{{ $i + 1 }}</span>
                                        <span class="text-truncate">{{ $ti->description }}</span>
                                    </span>
                                    <span class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <span class="badge bg-secondary">{{ $ti->times }}× ibinigay</span>
                                        <span class="fb-qty-pill">🎁 {{ $ti->total_qty }} pcs</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="fb-muted">Wala pang na-approve na freebie.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('sales.prototype.freebie-list') }}" class="fb-card mb-3">
        <div class="p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>✅ Approved</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Audit</label>
                    <select name="audit" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="awaiting" {{ $audit === 'awaiting' ? 'selected' : '' }}>🕵️ Awaiting Audit</option>
                        <option value="audited" {{ $audit === 'audited' ? 'selected' : '' }}>✅ Audited</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Requester</label>
                    <select name="requester" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($requesters as $reqUser)
                            <option value="{{ $reqUser->user_id }}" {{ $requesterId === (int) $reqUser->user_id ? 'selected' : '' }}>{{ $reqUser->user_name }} ({{ $reqUser->c }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Sales #, customer, item…">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">From</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">To</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
                </div>
            </div>
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-sm" style="background:#7c3aed;color:#fff;"><i class="fas fa-filter me-1"></i>Filter</button>
                @if($status || $audit || $q || $from || $to || $requesterId)
                    <a href="{{ route('sales.prototype.freebie-list') }}" class="btn btn-sm btn-outline-danger"><i class="fas fa-times me-1"></i>Clear</a>
                @endif
            </div>
        </div>
    </form>

    <!-- Requests list -->
    @forelse($requests as $r)
        @php
            $reqDept = $departmentLabels[$r->department_id] ?? null;
            $badgeColor = $r->status === 'approved' ? 'bg-success' : ($r->status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark');
        @endphp
        <div class="fb-card mb-3">
            <div class="fb-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-secondary">#{{ $r->id }}</span>
                    <a href="{{ route('sales.prototype.show', $r->sale_id) }}" target="_blank" class="fw-bold text-decoration-none">{{ $r->sales_number }}</a>
                    <span class="fb-muted">·</span>
                    <span>{{ $r->customer_name }}</span>
                    <span class="badge" style="background:#0ea5e9;color:#fff;" title="Total sales ng project"><i class="fas fa-coins me-1"></i>₱{{ number_format($r->project_total, 2) }}</span>
                    @if($reqDept)
                        <span class="badge bg-secondary">{{ $reqDept }}</span>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge {{ $badgeColor }}">
                        {{ $r->status === 'approved' ? '✅ Approved' : ($r->status === 'rejected' ? '❌ Rejected' : '⏳ Pending') }}
                    </span>
                    @if($r->status === 'approved')
                        @if($r->audited_at)
                            <span class="badge" style="background:#4338ca;color:#fff;" title="Na-audit ni {{ $r->audited_by_name ?? '—' }}"><i class="fas fa-shield-alt me-1"></i>Audited</span>
                        @else
                            <span class="badge bg-warning text-dark" title="Approved — naghihintay ng double-check ng ibang manager/CEO/COO"><i class="fas fa-hourglass-half me-1"></i>Awaiting Audit</span>
                        @endif
                    @endif
                    @if($r->slip_status)
                        <span class="badge {{ $r->slip_status === 'done' ? 'bg-success' : 'bg-danger' }}">
                            {{ $r->slip_status === 'done' ? '🎁 Slip Done' : '🎁 Slip Open' }}
                        </span>
                    @endif
                    <span class="fb-muted"><i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($r->created_at)->format('M d, Y g:i A') }}</span>
                </div>
            </div>
            <div class="p-3">
                <div class="fb-muted mb-2">
                    <i class="fas fa-user me-1"></i><strong>Requested by:</strong> {{ $r->requested_by_name ?? 'Unknown' }}
                    · <span class="text-muted">{{ $r->age_hours }}h ago</span>
                </div>

                @foreach($r->items as $it)
                    <div class="fb-item">
                        <span class="badge bg-secondary flex-shrink-0" style="font-size:.72rem;">{{ $it->quantity }}×</span>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-semibold">{{ $it->description }}</span>
                                @if($it->reference_image_url)
                                    <a href="javascript:void(0)" class="small text-primary" title="View reference image" onclick="openLightbox('{{ $it->reference_image_url }}')"><i class="fas fa-image"></i></a>
                                @endif
                            </div>
                            @if($it->purpose)
                                <div class="fb-muted"><i class="fas fa-comment-dots me-1"></i>{{ $it->purpose }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if($r->notes)
                    <div class="fb-notes mt-2"><i class="fas fa-sticky-note me-1"></i>{{ $r->notes }}</div>
                @endif

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2 pt-2" style="border-top:1px solid #f1f3f5;">
                    <div class="fb-muted">
                        @if($r->status === 'approved')
                            <span><i class="fas fa-check-circle text-success me-1"></i>by {{ $r->approved_by_name ?? '—' }}@if($r->approved_at) · {{ \Carbon\Carbon::parse($r->approved_at)->format('M d, g:i A') }}@endif</span>
                            @if($r->audited_at && $r->audited_by_name)
                                <span class="ms-2"><i class="fas fa-shield-alt" style="color:#4338ca;"></i> Audited by {{ $r->audited_by_name }}@if($r->audited_at) · {{ \Carbon\Carbon::parse($r->audited_at)->format('M d, g:i A') }}@endif</span>
                            @elseif(!$r->audited_at)
                                <span class="ms-2 text-warning"><i class="fas fa-hourglass-half"></i> Waiting for double-check (ibang manager/CEO/COO)</span>
                            @endif
                            @if($r->slip_status === 'done' && $r->slip_done_by_name)
                                <span class="ms-2"><i class="fas fa-gift text-success me-1"></i>Done by {{ $r->slip_done_by_name }}@if($r->slip_done_at) · {{ \Carbon\Carbon::parse($r->slip_done_at)->format('M d, g:i A') }}@endif</span>
                            @endif
                        @elseif($r->status === 'rejected')
                            <span><i class="fas fa-times-circle text-danger me-1"></i>by {{ $r->rejected_by_name ?? '—' }}: {{ $r->rejection_reason ?: 'no reason' }}</span>
                        @else
                            <span class="text-warning"><i class="fas fa-hourglass-half me-1"></i>Waiting for approval</span>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        @php $canAudit = auth()->user() && (auth()->user()->isManager() || auth()->user()->isCoo()); $isCeoAudit = auth()->user() && auth()->user()->isAdmin(); @endphp
                        @if($r->status === 'approved' && !$r->audited_at && $canAudit && ($isCeoAudit || auth()->id() !== (int) $r->approved_by))
                            <button class="btn btn-sm" style="background:#4338ca;color:#fff;" onclick="auditFreebieReq({{ $r->id }}, this)" title="Double-check: i-verify ang approval na ito"><i class="fas fa-shield-alt me-1"></i>Audit</button>
                        @endif
                        @if($r->status === 'pending' && auth()->user() && auth()->user()->isManager())
                            <button class="btn btn-sm btn-success" onclick="approveFreebieReq({{ $r->id }}, this)"><i class="fas fa-check me-1"></i>Approve</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="rejectFreebieReq({{ $r->id }}, this)"><i class="fas fa-times me-1"></i>Reject</button>
                        @endif
                        @if($r->status === 'approved' && $r->slip_status === 'open' && auth()->user() && (auth()->user()->isManager() || auth()->user()->isGa() || auth()->user()->isQa()))
                            <button class="btn btn-sm" style="background:#059669;color:#fff;" onclick="doneFreebieReq({{ $r->id }}, this)"><i class="fas fa-gift me-1"></i>Mark Done</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info"><i class="fas fa-info-circle me-1"></i>Walang nahanap na freebie request.</div>
    @endforelse

    <div class="mt-3">
        {{ $requests->links() }}
    </div>

</div>

<!-- Lightbox -->
<div id="imageLightbox" style="display:none;"></div>

@endsection

@push('scripts')
<script>
function approveFreebieReq(id, btn) {
    if (!confirm('Approve freebie request #' + id + '? Bubuo ito ng Freebie Slip.')) return;
    btn.disabled = true;
    var original = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    fetch('{{ route('sales.prototype.freebie.approve', 'REQUEST_ID') }}'.replace('REQUEST_ID', id), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
        },
        body: JSON.stringify({})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { location.reload(); }
        else { alert(data.error || 'Failed.'); btn.disabled = false; btn.innerHTML = original; }
    })
    .catch(function() { alert('Request failed.'); btn.disabled = false; btn.innerHTML = original; });
}
function rejectFreebieReq(id, btn) {
    var reason = prompt('Ilagay ang dahilan ng pag-reject:');
    if (reason === null) return;
    reason = reason.trim();
    if (!reason) { alert('Kailangan ng dahilan para i-reject.'); return; }
    btn.disabled = true;
    var original = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    fetch('{{ route('sales.prototype.freebie.reject', 'REQUEST_ID') }}'.replace('REQUEST_ID', id), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
        },
        body: JSON.stringify({reason: reason})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { location.reload(); }
        else { alert(data.error || 'Failed.'); btn.disabled = false; btn.innerHTML = original; }
    })
    .catch(function() { alert('Request failed.'); btn.disabled = false; btn.innerHTML = original; });
}
function doneFreebieReq(id, btn) {
    if (!confirm('Mark this freebie slip as done?')) return;
    btn.disabled = true;
    var original = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    fetch('{{ route('sales.prototype.freebie.done', 'REQUEST_ID') }}'.replace('REQUEST_ID', id), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
        },
        body: JSON.stringify({})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { location.reload(); }
        else { alert(data.error || 'Failed.'); btn.disabled = false; btn.innerHTML = original; }
    })
    .catch(function() { alert('Request failed.'); btn.disabled = false; btn.innerHTML = original; });
}
function auditFreebieReq(id, btn) {
    if (!confirm('I-audit (double-check) ang approval ng freebie request #' + id + '? Ikaw ang magbe-verify na karapat-dapat itong i-approve.')) return;
    btn.disabled = true;
    var original = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    fetch('{{ route('sales.prototype.freebie.audit', 'REQUEST_ID') }}'.replace('REQUEST_ID', id), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
        },
        body: JSON.stringify({})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { location.reload(); }
        else { alert(data.error || 'Failed.'); btn.disabled = false; btn.innerHTML = original; }
    })
    .catch(function() { alert('Request failed.'); btn.disabled = false; btn.innerHTML = original; });
}

// Lightbox (same behavior as the sale page)
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
