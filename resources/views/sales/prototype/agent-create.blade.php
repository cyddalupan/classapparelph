@extends('layouts.app')

@section('page-title', 'Add Sale')

@section('content')
<div class="agent-create-sale-page">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">
                <i class="fas fa-plus-circle"></i>
                Add New Sale
            </h1>
            <p class="page-subtitle">Submit a simplified sales record</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('sales.prototype.list') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to My Sales
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

    <!-- Simplified Sale Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-invoice"></i>
                Sale Information
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('sales.prototype.agent.store') }}" method="POST" enctype="multipart/form-data" class="agent-sale-form">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="customer_name">Customer Name <span class="required">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" 
                               value="{{ old('customer_name') }}" required placeholder="Enter customer name">
                        @error('customer_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="customer_phone">Customer Phone</label>
                        <input type="text" id="customer_phone" name="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" 
                               value="{{ old('customer_phone') }}" placeholder="e.g., 09123456789">
                        @error('customer_phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="services">Services / Items Ordered <span class="required">*</span></label>
                    <textarea id="services" name="services" class="form-control @error('services') is-invalid @enderror" 
                              rows="3" required placeholder="Describe what the customer ordered">{{ old('services') }}</textarea>
                    @error('services') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="total_amount">Total Amount (₱) <span class="required">*</span></label>
                        <input type="number" id="total_amount" name="total_amount" class="form-control @error('total_amount') is-invalid @enderror" 
                               value="{{ old('total_amount') }}" step="0.01" min="0" required placeholder="0.00">
                        @error('total_amount') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="deposit_paid">Deposit / Initial Payment (₱)</label>
                        <input type="number" id="deposit_paid" name="deposit_paid" class="form-control @error('deposit_paid') is-invalid @enderror" 
                               value="{{ old('deposit_paid', '0') }}" step="0.01" min="0" placeholder="0.00">
                        @error('deposit_paid') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notes / Special Instructions</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" 
                              rows="2" placeholder="Any special instructions or notes">{{ old('notes') }}</textarea>
                    @error('notes') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="department_id">Department <span class="required">*</span></label>
                        <select id="department_id" name="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                            <option value="">Select department...</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('department_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" class="form-control" onchange="togglePaymentFields()">
                            <option value="">Select payment method...</option>
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="gcash" {{ old('payment_method') === 'gcash' ? 'selected' : '' }}>GCash</option>
                            <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="check" {{ old('payment_method') === 'check' ? 'selected' : '' }}>Check</option>
                        </select>
                    </div>
                </div>

                <div id="paymentFields" class="form-row" style="display:none;">
                    <div class="form-group">
                        <label for="payment_screenshot">Payment Screenshot/Proof</label>
                        <input type="file" id="payment_screenshot" name="payment_screenshot" class="form-control-file @error('payment_screenshot') is-invalid @enderror" accept="image/*">
                        @error('payment_screenshot') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        <small class="text-muted">Upload proof of payment (optional)</small>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('sales.prototype.list') }}" class="btn btn-outline">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Submit Sale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.agent-create-sale-page { padding: 2rem; }

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}

@media (max-width: 640px) {
    .agent-create-sale-page { padding: 1rem; }
    .form-row { grid-template-columns: 1fr; gap: 0.75rem; }
}

.form-group { margin-bottom: 1.25rem; }

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.375rem;
}

.form-group .required { color: #ef4444; }
.text-muted { color: #94a3b8; font-size: 0.8rem; margin-top: 0.25rem; display: block; }

.form-control {
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

textarea.form-control { resize: vertical; min-height: 60px; }

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

.form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0; }

.btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; }
.btn-primary { background: #3b82f6; color: white; }
.btn-primary:hover { background: #2563eb; }
.btn-outline { background: white; color: #475569; border: 1.5px solid #e2e8f0; }
.btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }
.btn-lg { padding: 0.75rem 1.5rem; font-size: 1rem; }

.card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
.card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0; }
.card-title { margin: 0; font-size: 0.875rem; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 0.5rem; }
.card-body { padding: 1.5rem; }
</style>
@endpush

@push('scripts')
<script>
function togglePaymentFields() {
    const method = document.getElementById('payment_method').value;
    const fields = document.getElementById('paymentFields');
    fields.style.display = (method && method !== 'cash') ? 'block' : 'none';
}
</script>
@endpush
@endsection
