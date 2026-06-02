@extends('layouts.app')

@section('content')
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 mb-0 fw-bold">Add Supplier</h1>
            </div>
            <a href="{{ route('procurement.suppliers.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="container">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('procurement.suppliers.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Supplier Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Terms</label>
                        <select name="payment_terms" class="form-select" required>
                            <option value="cod" {{ old('payment_terms') === 'cod' ? 'selected' : '' }}>COD</option>
                            <option value="net_15" {{ old('payment_terms') === 'net_15' || !old('payment_terms') ? 'selected' : '' }}>Net 15</option>
                            <option value="net_30" {{ old('payment_terms') === 'net_30' ? 'selected' : '' }}>Net 30</option>
                            <option value="net_60" {{ old('payment_terms') === 'net_60' ? 'selected' : '' }}>Net 60</option>
                            <option value="advance" {{ old('payment_terms') === 'advance' ? 'selected' : '' }}>Advance Payment</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
