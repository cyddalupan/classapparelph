@php $sale = $line['sale']; @endphp
<div class="sp-card mb-3">
    <div class="sp-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('sales.prototype.show', $sale->id) }}" class="fw-bold text-decoration-none">{{ $sale->sales_number }}</a>
            <span class="text-muted small">·</span>
            <span>{{ $sale->customer_name }}</span>
            @if(isset($departmentLabels[$sale->department_id]))
                <span class="badge bg-secondary">{{ $departmentLabels[$sale->department_id] }}</span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge {{ $sale->status === 'completed' ? 'bg-success' : ($sale->status === 'confirmed' ? 'bg-primary' : 'bg-warning text-dark') }}">{{ strtoupper($sale->status) }}</span>
            <span class="text-muted small"><i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y') }}</span>
        </div>
    </div>
    <div class="p-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
            <div>
                <span class="kind-badge {{ $line['kind'] === 'Sublimation' ? 'kind-sublimation' : 'kind-garment' }}">{{ $line['kind'] }}</span>
                <span class="fw-semibold ms-2">{{ $line['itemName'] }}</span>
                @if($line['project'])
                    <div class="sp-muted mt-1"><i class="fas fa-briefcase me-1"></i>{{ $line['project'] }}</div>
                @endif
                <div class="sp-muted mt-1"><i class="fas fa-user-tie me-1"></i><strong>Sales Agent:</strong> {{ $line['agent'] !== '' ? $line['agent'] : '—' }}</div>
            </div>
            <div class="text-end">
                <div class="fw-bold text-warning" style="font-size:1.05rem;">
                    @if($line['price'] !== null && $line['price'] !== '')
                        ₱{{ number_format((float)$line['price'], 2) }}@if($line['kind'] === 'Sublimation')/pc @endif
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
                <div class="sp-muted">Qty: {{ $line['qty'] }}</div>
            </div>
        </div>
        <div class="sp-reason {{ $line['reason'] === '' ? 'empty' : '' }}">
            <i class="fas fa-comment-dots me-1"></i>
            <strong>Reason:</strong>
            {{ $line['reason'] !== '' ? $line['reason'] : '⚠️ Walang reason na nilagay!' }}
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
            @if($line['reviewed'])
                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Checked by {{ $line['reviewed']['by'] }} · {{ $line['reviewed']['at'] }}</span>
            @else
                <span></span>
            @endif
            <button type="button" class="btn btn-sm {{ $line['reviewed'] ? 'btn-outline-secondary' : 'btn-success' }} sp-review-btn" data-sale="{{ $sale->id }}" data-key="{{ $line['lineKey'] }}">
                <i class="fas {{ $line['reviewed'] ? 'fa-undo' : 'fa-check' }} me-1"></i>{{ $line['reviewed'] ? 'Uncheck' : 'Mark as checked' }}
            </button>
        </div>
    </div>
</div>
