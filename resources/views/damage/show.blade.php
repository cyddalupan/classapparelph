@extends('layouts.app')

@section('title', 'Damage Report ' . $report->report_no)

@push('styles')
<style>
    .status-badge { font-size: 0.8rem; }
    .timeline { position: relative; padding-left: 20px; }
    .timeline::before { content: ''; position: absolute; left: 6px; top: 4px; bottom: 4px; width: 2px; background: #e9ecef; }
    .timeline-item { position: relative; margin-bottom: 1rem; }
    .timeline-item::before { content: ''; position: absolute; left: -20px; top: 6px; width: 12px; height: 12px; border-radius: 50%; background: #dc3545; border: 2px solid #fff; box-shadow: 0 0 0 1px #dc3545; }
    .ev-img { max-height: 300px; object-fit: contain; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>{{ $report->report_no }}</h4>
            <p class="text-muted mb-0 small">Filed by {{ $report->reporter->name ?? 'Unknown' }} · {{ $report->created_at->format('M d, Y h:i A') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('damage.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
            @if($report->sale)
                <a href="{{ route('sales.prototype.show', $report->sale_id) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i>{{ $report->sale->sales_number }}
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {!! session('error') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Left: report details + actions -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Report Details</span>
                    <span class="badge status-badge bg-{{ $report->status === 'dismissed' ? 'secondary' : ($report->status === 'contested' ? 'danger' : ($report->status === 'resolved' ? 'success' : ($report->status === 'acknowledged' ? 'primary' : ($report->status === 'issued' ? 'warning' : 'info')))) }}">
                        {{ \App\Models\DamageReport::STATUSES[$report->status] ?? $report->status }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4"><small class="text-muted">Shop</small><div class="fw-bold">{{ $report->shop->name ?? '—' }}</div></div>
                        <div class="col-md-4"><small class="text-muted">Severity</small>
                            <div class="fw-bold">{{ ucfirst($report->severity) }} <span class="badge bg-danger ms-1">{{ $report->points }} pt</span></div>
                        </div>
                        <div class="col-md-4"><small class="text-muted">Category</small><div class="fw-bold">{{ \App\Models\DamageReport::CATEGORIES[$report->category] ?? $report->category }}</div></div>
                    </div>
                    @if($report->quantity !== null)
                        <div class="row mb-3">
                            <div class="col-md-4"><small class="text-muted">Quantity Damaged</small>
                                <div class="fw-bold">{{ $report->quantity }} pc{{ $report->quantity > 1 ? 's' : '' }}</div>
                            </div>
                            @if($report->involved_position || $report->involved_name)
                                <div class="col-md-8"><small class="text-muted">Involved</small>
                                    <div class="fw-bold">
                                        {{ $report->involved_name ?: '—' }}
                                        @if($report->involved_position)<span class="badge bg-secondary ms-1">{{ $report->involved_position }}</span>@endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                    @if($report->damage_amount !== null)
                        <div class="row mb-3">
                            <div class="col-md-4"><small class="text-muted">Damage Amount</small>
                                <div class="fw-bold text-danger fs-5">₱{{ number_format($report->damage_amount, 2) }}</div>
                            </div>
                            <div class="col-md-8">
                                <small class="text-muted">Reviewer</small>
                                <div class="fw-bold">{{ $report->reviewer->name ?? '—' }}</div>
                            </div>
                        </div>
                    @endif
                    <div class="mb-3">
                        <small class="text-muted">Description</small>
                        <div class="border rounded p-3 bg-light">{{ nl2br(e($report->description)) }}</div>
                    </div>
                    @if($report->review_notes)
                        <div class="mb-3">
                            <small class="text-muted">Review Notes</small>
                            <div class="border rounded p-3 bg-warning-subtle">{{ nl2br(e($report->review_notes)) }}</div>
                        </div>
                    @endif
                    @if($report->evidence_path)
                        <div class="mb-2">
                            <small class="text-muted d-block mb-2">Evidence</small>
                            <img src="{{ $report->evidence_path }}" class="img-thumbnail ev-img" alt="Evidence" onclick="window.open(this.src,'_blank')" style="cursor:zoom-in;">
                        </div>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Timeline</div>
                <div class="card-body">
                    <div class="timeline">
                        @forelse($report->comments as $comment)
                            <div class="timeline-item">
                                <strong>{{ $comment->user->name ?? 'Unknown' }}</strong>
                                <span class="text-muted small ms-2">{{ $comment->created_at->format('M d, Y h:i A') }}</span>
                                <div class="mt-1">{{ nl2br(e($comment->comment)) }}</div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No activity yet.</p>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('damage.comment', $report->id) }}" class="mt-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="comment" class="form-control" placeholder="Add a comment..." required>
                            <button class="btn btn-outline-primary"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: accountable users + actions -->
        <div class="col-lg-4">
            <!-- Accountable users -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Accountable Users</div>
                <div class="card-body">
                    @if($report->accountableUsers->count() > 0)
                        @foreach($report->accountableUsers as $au)
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <strong>{{ $au->user->name ?? 'Unknown' }}</strong>
                                    <div class="small text-muted">
                                        @if($au->amount_share > 0)
                                            ₱{{ number_format($au->amount_share, 2) }}
                                        @else
                                            No amount
                                        @endif
                                    </div>
                                </div>
                                <span class="badge bg-{{ $au->acknowledge_status === 'acknowledged' ? 'success' : ($au->acknowledge_status === 'contested' ? 'danger' : 'warning') }}">
                                    {{ \App\Models\DamageReportUser::ACK_STATUSES[$au->acknowledge_status] ?? $au->acknowledge_status }}
                                </span>
                            </div>
                            @if($au->reply)
                                <div class="small text-muted bg-light rounded p-2 my-1">{{ $au->reply }}</div>
                            @endif
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No accountable users assigned yet.</p>
                    @endif
                </div>
            </div>

            <!-- Reviewer: issue / resolve / dismiss -->
            @if(in_array(auth()->user()->role, ['admin', 'coo', 'cpo', 'cmo']))
                @if(in_array($report->status, ['submitted', 'under_review']))
                    <div class="card shadow-sm mb-4 border-warning">
                        <div class="card-header bg-warning bg-opacity-10 fw-bold"><i class="fas fa-gavel me-1"></i>Review & Issue</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('damage.review', $report->id) }}">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small">Accountable User(s)</label>
                                    <select name="user_ids[]" class="form-select form-select-sm" multiple required>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Ctrl+click para multiple.</small>
                                </div>
                                <div class="mb-2" id="amountFields"></div>
                                <div class="mb-2">
                                    <label class="form-label small">Total Damage Amount (₱)</label>
                                    <input type="number" step="0.01" min="0" name="damage_amount" class="form-control form-control-sm" placeholder="0.00">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Quantity Damaged</label>
                                    <input type="number" min="1" name="quantity" class="form-control form-control-sm" value="{{ $report->quantity ?? '' }}" placeholder="Ilang pcs ang nadamage">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Severity</label>
                                    <select name="severity" class="form-select form-select-sm">
                                        @foreach(\App\Models\DamageReport::SEVERITIES as $val => $label)
                                            <option value="{{ $val }}" {{ $report->severity === $val ? 'selected' : '' }}>{{ $label }} ({{ \App\Models\DamageReport::SEVERITY_POINTS[$val] }} pt)</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Category</label>
                                    <select name="category" class="form-select form-select-sm">
                                        @foreach(\App\Models\DamageReport::CATEGORIES as $val => $label)
                                            <option value="{{ $val }}" {{ $report->category === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Review Notes</label>
                                    <textarea name="review_notes" class="form-control form-control-sm" rows="2"></textarea>
                                </div>
                                <button class="btn btn-warning btn-sm w-100">Issue Report</button>
                            </form>
                        </div>
                    </div>
                @endif

                @if(in_array($report->status, ['issued', 'acknowledged', 'contested']))
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <form method="POST" action="{{ route('damage.resolve', $report->id) }}" class="mb-2">
                                @csrf
                                <div class="input-group input-group-sm mb-2">
                                    <input type="text" name="review_notes" class="form-control" placeholder="Resolution note (optional)">
                                    <button class="btn btn-success"><i class="fas fa-check"></i> Resolve</button>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('damage.dismiss', $report->id) }}">
                                @csrf
                                <div class="input-group input-group-sm">
                                    <input type="text" name="review_notes" class="form-control" placeholder="Dismiss reason (optional)">
                                    <button class="btn btn-secondary"><i class="fas fa-times"></i> Dismiss</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Shop manager / reporter: edit + tag sale -->
            @if($canEdit && in_array($report->status, ['submitted', 'under_review']))
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold"><i class="fas fa-edit me-1"></i>Edit Report</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('damage.update', $report->id) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small">Tag Sale (Sales Number) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="sale_search" value="{{ $report->sale->sales_number ?? '' }}" placeholder="Search sales number..." {{ $report->sale_id ? '' : 'required' }}>
                                <input type="hidden" name="sale_id" id="sale_id" value="{{ $report->sale_id }}">
                                <div id="sale_result" class="mt-1"></div>
                                <small class="text-muted">Kinakailangan bago ma-review — para ma-trace ang damage at maiwasan ang duplicate reports.</small>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Severity</label>
                                <select name="severity" class="form-select form-select-sm">
                                    @foreach(\App\Models\DamageReport::SEVERITIES as $val => $label)
                                        <option value="{{ $val }}" {{ $report->severity === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Category</label>
                                <select name="category" class="form-select form-select-sm">
                                    @foreach(\App\Models\DamageReport::CATEGORIES as $val => $label)
                                        <option value="{{ $val }}" {{ $report->category === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Quantity Damaged</label>
                                <input type="number" min="1" name="quantity" class="form-control form-control-sm" value="{{ $report->quantity ?? '' }}" placeholder="Ilang pcs ang nadamage">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Involved Position <span class="text-muted">(kung alam)</span></label>
                                <input type="text" name="involved_position" class="form-control form-control-sm" list="positionSuggestions" value="{{ $report->involved_position ?? '' }}" placeholder="e.g. Presser, Sewer, Cutter">
                                <datalist id="positionSuggestions">
                                    @foreach(['Presser', 'Sewer', 'Cutter', 'Production Staff', 'Sales Agent', 'Quality Checker', 'Encoder', 'Driver', 'Other'] as $pos)
                                        <option value="{{ $pos }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Involved Person <span class="text-muted">(kung kilala)</span></label>
                                <input type="text" name="involved_name" class="form-control form-control-sm" value="{{ $report->involved_name ?? '' }}" placeholder="Pangalan ng involved (optional)">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Description</label>
                                <textarea name="description" class="form-control form-control-sm" rows="3" required>{{ $report->description }}</textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">New Evidence (optional)</label>
                                <input type="file" name="evidence" accept="image/*" class="form-control form-control-sm">
                            </div>
                            <button class="btn btn-primary btn-sm w-100">Save &amp; Send to Review</button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Accountable user: acknowledge / contest -->
            @if($isAccountable && $myAccountability && $myAccountability->acknowledge_status === 'pending' && in_array($report->status, ['issued', 'acknowledged', 'contested']))
                <div class="card shadow-sm mb-4 border-info">
                    <div class="card-header bg-info bg-opacity-10 fw-bold"><i class="fas fa-handshake me-1"></i>Your Response Needed</div>
                    <div class="card-body">
                        @if($myAccountability->amount_share > 0)
                            <p class="mb-2">Amount accountable: <strong class="text-danger">₱{{ number_format($myAccountability->amount_share, 2) }}</strong></p>
                        @endif
                        <form method="POST" action="{{ route('damage.acknowledge', $report->id) }}" class="mb-2">
                            @csrf
                            <textarea name="reply" class="form-control form-control-sm mb-2" rows="2" placeholder="Optional comment"></textarea>
                            <button class="btn btn-success btn-sm w-100"><i class="fas fa-check me-1"></i>Acknowledge &amp; Accept</button>
                        </form>
                        <form method="POST" action="{{ route('damage.contest', $report->id) }}">
                            @csrf
                            <textarea name="reply" class="form-control form-control-sm mb-2" rows="2" placeholder="Reason for contesting..." required></textarea>
                            <button class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-times me-1"></i>Contest</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Amount fields per accountable user (review form)
    var userSelect = document.querySelector('select[name="user_ids[]"]');
    var amountFields = document.getElementById('amountFields');
    if (userSelect && amountFields) {
        function renderAmounts() {
            var selected = Array.from(userSelect.selectedOptions);
            amountFields.innerHTML = '';
            selected.forEach(function(opt) {
                var wrap = document.createElement('div');
                wrap.className = 'mb-1';
                var label = document.createElement('label');
                label.className = 'form-label small mb-0';
                label.textContent = 'Amount — ' + opt.textContent.split(' (')[0] + ' (₱)';
                var input = document.createElement('input');
                input.type = 'number';
                input.step = '0.01';
                input.min = '0';
                input.name = 'amounts[]';
                input.className = 'form-control form-control-sm';
                input.placeholder = '0.00';
                wrap.appendChild(label);
                wrap.appendChild(input);
                amountFields.appendChild(wrap);
            });
        }
        userSelect.addEventListener('change', renderAmounts);
    }

    // Sale search
    var saleSearch = document.getElementById('sale_search');
    var saleId = document.getElementById('sale_id');
    var saleResult = document.getElementById('sale_result');
    if (saleSearch) {
        saleSearch.addEventListener('input', function() {
            saleId.value = '';
            var q = this.value.trim();
            if (q.length < 4) { saleResult.innerHTML = ''; return; }
            fetch('/sales/prototype/search?q=' + encodeURIComponent(q))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    saleResult.innerHTML = '';
                    if (data && data.length) {
                        data.slice(0, 5).forEach(function(sale) {
                            var item = document.createElement('div');
                            item.className = 'border rounded p-2 mb-1 small';
                            item.style.cursor = 'pointer';
                            item.innerHTML = '<strong>' + sale.sales_number + '</strong> — ' + (sale.customer_name || '');
                            item.addEventListener('click', function() {
                                saleId.value = sale.id;
                                saleSearch.value = sale.sales_number;
                                saleResult.innerHTML = '<span class="text-success"><i class="fas fa-check me-1"></i>Tagged to ' + sale.sales_number + '</span>';
                            });
                            saleResult.appendChild(item);
                        });
                    } else {
                        saleResult.innerHTML = '<span class="text-muted small">No matching sale found.</span>';
                    }
                })
                .catch(function() { saleResult.innerHTML = ''; });
        });
    }
});
</script>
@endsection
