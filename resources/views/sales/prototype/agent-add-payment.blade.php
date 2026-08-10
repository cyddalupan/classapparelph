@extends('layouts.app')

@section('page-title', 'Add Payment')

@section('content')
<div class="agent-payment-page">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">
                <i class="fas fa-money-bill-wave"></i>
                Add Payment
            </h1>
            <p class="page-subtitle">{{ $sale->customer_name }} — {{ $sale->sales_number }}</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('sales.prototype.show', $sale->id) }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Sale
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <ul style="margin:0;padding-left:1.25rem;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Sale Info Summary -->
    <div class="sale-summary">
        <div class="summary-item">
            <span class="summary-label">Total Amount</span>
            <span class="summary-value">₱ {{ number_format($sale->total_amount, 2) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Already Paid</span>
            <span class="summary-value paid">₱ {{ number_format($sale->net_paid ?? 0, 2) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Balance Due</span>
            <span class="summary-value due">₱ {{ number_format($sale->balance_due_computed ?? 0, 2) }}</span>
        </div>
    </div>

    <!-- Payment Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-plus-circle"></i>
                Add Payment Details
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('sales.prototype.agent.payment.store', $sale->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="payment_amount">Payment Amount (₱) <span class="required">*</span></label>
                        <input type="number" id="payment_amount" name="payment_amount" class="form-control @error('payment_amount') is-invalid @enderror" 
                               value="{{ old('payment_amount') }}" step="0.01" min="0.01" placeholder="0.00" required>
                        @error('payment_amount') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="payment_date">Payment Date <span class="required">*</span></label>
                        <input type="date" id="payment_date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" 
                               value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        @error('payment_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="payment_method">Payment Method <span class="required">*</span></label>
                        <select id="payment_method" name="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required onchange="togglePaymentRefFields()">
                            <option value="">Select...</option>
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="gcash" {{ old('payment_method') === 'gcash' ? 'selected' : '' }}>GCash</option>
                            <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="check" {{ old('payment_method') === 'check' ? 'selected' : '' }}>Check</option>
                        </select>
                        @error('payment_method') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group" id="refNumberGroup" style="display:none;">
                        <label for="reference_number">Reference Number</label>
                        <input type="text" id="reference_number" name="reference_number" class="form-control" 
                               value="{{ old('reference_number') }}" placeholder="Transaction reference number">
                    </div>
                </div>

                <div class="form-group" id="screenshotGroup" style="display:none;">
                    <label for="payment_screenshot">Payment Screenshot / Proof</label>
                    <input type="file" id="payment_screenshot" name="payment_screenshot" class="form-control-file @error('payment_screenshot') is-invalid @enderror" accept="image/*">
                    @error('payment_screenshot') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    <small class="text-muted">Upload proof of payment for verification</small>
                </div>

                <div class="form-group">
                    <label for="notes">Notes (optional)</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" 
                              rows="2" placeholder="Any notes about this payment">{{ old('notes') }}</textarea>
                    @error('notes') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-actions">
                    <a href="{{ route('sales.prototype.show', $sale->id) }}" class="btn btn-outline">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Submit Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.agent-payment-page { padding: 2rem; }

.sale-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.summary-item {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
}

.summary-label {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 0.5rem;
}

.summary-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}

.summary-value.paid { color: #059669; }
.summary-value.due { color: #dc2626; }

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}

@media (max-width: 640px) {
    .agent-payment-page { padding: 1rem; }
    .sale-summary { grid-template-columns: 1fr; gap: 0.5rem; }
    .form-row { grid-template-columns: 1fr; gap: 0.75rem; }
    .summary-value { font-size: 1.25rem; }
}

.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; font-size: 0.875rem; font-weight: 600; color: #334155; margin-bottom: 0.375rem; }
.form-group .required { color: #ef4444; }
.text-muted { color: #94a3b8; font-size: 0.8rem; margin-top: 0.25rem; display: block; }

.form-control, .form-control-file {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
    background: white;
    transition: all 0.2s;
}

.form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.form-control.is-invalid { border-color: #ef4444; }

select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    padding-right: 2rem;
}

.form-control-file { padding: 0.5rem 0; }

.invalid-feedback { display: block; font-size: 0.75rem; color: #ef4444; margin-top: 0.25rem; }

.alert-danger { background: #fce4ec; color: #c62828; border: 1px solid #f8bbd0; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; }

.form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #e2e8f0; }

.btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; }
.btn-primary { background: #3b82f6; color: white; }
.btn-primary:hover { background: #2563eb; }
.btn-outline { background: white; color: #475569; border: 1.5px solid #e2e8f0; }
.btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }

.card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
.card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0; }
.card-title { margin: 0; font-size: 0.875rem; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 0.5rem; }
.card-body { padding: 1.5rem; }
</style>
@endpush

@push('scripts')
<script>
function togglePaymentRefFields() {
    const method = document.getElementById('payment_method').value;
    const refGroup = document.getElementById('refNumberGroup');
    const screenshotGroup = document.getElementById('screenshotGroup');
    
    refGroup.style.display = (method === 'gcash' || method === 'bank_transfer') ? 'block' : 'none';
    screenshotGroup.style.display = (method === 'gcash' || method === 'bank_transfer' || method === 'check') ? 'block' : 'none';
}
</script>
@endpush
@endsection
