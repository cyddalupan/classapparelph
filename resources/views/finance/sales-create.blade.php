@extends('layouts.app')

@section('page-title', 'Add New Sale')

@section('content')
<div class="page-header">
    <div class="header-content">
        <h1 class="page-title">
            <i class="fas fa-plus-circle"></i>
            Add New Sale
        </h1>
        <p class="page-subtitle">Record a new sales transaction</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('finance.sales') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Sales
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> Please fix the following errors:
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="content-card">
    <form action="{{ route('sales.store') }}" method="POST" class="form-grid">
        @csrf
        
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-calendar-alt"></i> Sale Information
            </h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="sale_date" class="form-label">
                        <i class="fas fa-calendar"></i> Sale Date *
                    </label>
                    <input type="date" id="sale_date" name="sale_date" 
                           value="{{ old('sale_date', date('Y-m-d')) }}"
                           class="form-control @error('sale_date') is-invalid @enderror" required>
                    @error('sale_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="product_name" class="form-label">
                        <i class="fas fa-box"></i> Product Name *
                    </label>
                    <input type="text" id="product_name" name="product_name" 
                           value="{{ old('product_name') }}"
                           class="form-control @error('product_name') is-invalid @enderror" 
                           placeholder="Enter product name" required>
                    @error('product_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="quantity" class="form-label">
                        <i class="fas fa-hashtag"></i> Quantity *
                    </label>
                    <input type="number" id="quantity" name="quantity" 
                           value="{{ old('quantity', 1) }}" min="1"
                           class="form-control @error('quantity') is-invalid @enderror" required>
                    @error('quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="unit_price" class="form-label">
                        <i class="fas fa-money-bill-wave"></i> Unit Price (₱) *
                    </label>
                    <input type="number" id="unit_price" name="unit_price" 
                           value="{{ old('unit_price') }}" step="0.01" min="0.01"
                           class="form-control @error('unit_price') is-invalid @enderror" 
                           placeholder="0.00" required>
                    @error('unit_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-group">
                <label for="product_description" class="form-label">
                    <i class="fas fa-align-left"></i> Product Description
                </label>
                <textarea id="product_description" name="product_description" 
                          class="form-control @error('product_description') is-invalid @enderror"
                          rows="3" placeholder="Optional product description">{{ old('product_description') }}</textarea>
                @error('product_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-user"></i> Customer Information
            </h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="customer_name" class="form-label">
                        <i class="fas fa-user"></i> Customer Name *
                    </label>
                    <input type="text" id="customer_name" name="customer_name" 
                           value="{{ old('customer_name') }}"
                           class="form-control @error('customer_name') is-invalid @enderror" 
                           placeholder="Enter customer name" required>
                    @error('customer_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="customer_email" class="form-label">
                        <i class="fas fa-envelope"></i> Customer Email
                    </label>
                    <input type="email" id="customer_email" name="customer_email" 
                           value="{{ old('customer_email') }}"
                           class="form-control @error('customer_email') is-invalid @enderror" 
                           placeholder="customer@example.com">
                    @error('customer_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="customer_phone" class="form-label">
                        <i class="fas fa-phone"></i> Customer Phone
                    </label>
                    <input type="tel" id="customer_phone" name="customer_phone" 
                           value="{{ old('customer_phone') }}"
                           class="form-control @error('customer_phone') is-invalid @enderror" 
                           placeholder="+63 912 345 6789">
                    @error('customer_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-credit-card"></i> Payment Information
            </h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="payment_method" class="form-label">
                        <i class="fas fa-wallet"></i> Payment Method *
                    </label>
                    <select id="payment_method" name="payment_method" 
                            class="form-control @error('payment_method') is-invalid @enderror" required>
                        <option value="">Select payment method</option>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="gcash" {{ old('payment_method') == 'gcash' ? 'selected' : '' }}>GCash</option>
                        <option value="paypal" {{ old('payment_method') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                        <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="payment_status" class="form-label">
                        <i class="fas fa-check-circle"></i> Payment Status *
                    </label>
                    <select id="payment_status" name="payment_status" 
                            class="form-control @error('payment_status') is-invalid @enderror" required>
                        <option value="">Select payment status</option>
                        <option value="pending" {{ old('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partially_paid" {{ old('payment_status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="overdue" {{ old('payment_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="cancelled" {{ old('payment_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('payment_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="sale_status" class="form-label">
                        <i class="fas fa-truck"></i> Sale Status *
                    </label>
                    <select id="sale_status" name="sale_status" 
                            class="form-control @error('sale_status') is-invalid @enderror" required>
                        <option value="">Select sale status</option>
                        <option value="pending" {{ old('sale_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ old('sale_status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ old('sale_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="shipped" {{ old('sale_status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ old('sale_status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ old('sale_status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="refunded" {{ old('sale_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                    @error('sale_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-sticky-note"></i> Additional Information
            </h3>
            
            <div class="form-group">
                <label for="notes" class="form-label">
                    <i class="fas fa-edit"></i> Notes
                </label>
                <textarea id="notes" name="notes" 
                          class="form-control @error('notes') is-invalid @enderror"
                          rows="4" placeholder="Any additional notes about this sale">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Save Sale
            </button>
            <a href="{{ route('finance.sales') }}" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<style>
    .content-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-top: 1.5rem;
    }
    
    .form-grid {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }
    
    .form-section {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1.5rem;
        background: #f8f9fa;
    }
    
    .form-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #3498db;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-control {
        padding: 0.75rem 1rem;
        border: 1px solid #ced4da;
        border-radius: 6px;
        font-size: 1rem;
        transition: all 0.2s;
    }
    
    .form-control:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        outline: none;
    }
    
    .form-control.is-invalid {
        border-color: #e74c3c;
    }
    
    .invalid-feedback {
        color: #e74c3c;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
        margin-top: 1rem;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .content-card {
            padding: 1.5rem;
            margin: 1rem;
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .form-section {
            padding: 1rem;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .form-actions .btn {
            width: 100%;
        }
    }
</style>
@endsection