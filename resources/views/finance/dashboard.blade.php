@extends('layouts.app')

@section('page-title', 'Finance Dashboard')

@section('content')
<div class="finance-dashboard-simple">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="header-left">
            <h1 class="page-title">
                <i class="fas fa-chart-line"></i>
                Finance Dashboard
            </h1>
            <p class="page-subtitle">Track sales, expenses, and financial performance</p>
        </div>
        <div class="header-actions">
            <a href="#add-expense-form" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Expense
            </a>
            <a href="/finance/sales/create" class="btn btn-success">
                <i class="fas fa-plus"></i> Add Sale
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card revenue">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">₱ {{ number_format($totalRevenue ?? 0, 2) }}</div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 12.5% from last month
                </div>
            </div>
        </div>

        <div class="stat-card expenses">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">₱ {{ number_format($totalExpenses ?? 0, 2) }}</div>
                <div class="stat-label">Total Expenses</div>
                <div class="stat-change negative">
                    <i class="fas fa-arrow-up"></i> 8.2% from last month
                </div>
            </div>
        </div>

        <div class="stat-card profit">
            <div class="stat-icon">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">₱ {{ number_format($netProfit ?? 0, 2) }}</div>
                <div class="stat-label">Net Profit</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 15.3% from last month
                </div>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $pendingExpenses ?? 0 }}</div>
                <div class="stat-label">Pending Expenses</div>
                <div class="stat-change">
                    <a href="/finance/expenses?status=pending" class="view-link">View All</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Expense Form (Inline, no modal) -->
    <div class="content-card" id="add-expense-form">
        <div class="card-header">
            <h3><i class="fas fa-plus"></i> Add New Expense</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="/finance/expenses" class="expense-form">
                @csrf
                
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
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="date">Date *</label>
                        <input type="date" id="date" name="date" required 
                               value="{{ old('date', date('Y-m-d')) }}" class="form-control">
                        @error('date')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="amount">Amount (₱) *</label>
                        <input type="number" id="amount" name="amount" 
                               step="0.01" min="0.01" required placeholder="0.00" 
                               value="{{ old('amount') }}" class="form-control">
                        @error('amount')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required class="form-control">
                            <option value="">Select Category</option>
                            <option value="software" {{ old('category') == 'software' ? 'selected' : '' }}>Software</option>
                            <option value="hardware" {{ old('category') == 'hardware' ? 'selected' : '' }}>Hardware</option>
                            <option value="marketing" {{ old('category') == 'marketing' ? 'selected' : '' }}>Marketing</option>
                            <option value="office" {{ old('category') == 'office' ? 'selected' : '' }}>Office Supplies</option>
                            <option value="travel" {{ old('category') == 'travel' ? 'selected' : '' }}>Travel</option>
                            <option value="utilities" {{ old('category') == 'utilities' ? 'selected' : '' }}>Utilities</option>
                            <option value="salaries" {{ old('category') == 'salaries' ? 'selected' : '' }}>Salaries</option>
                            <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('category')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required class="form-control">
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="overdue" {{ old('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                        @error('status')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" 
                                  required placeholder="Enter expense description" 
                                  class="form-control" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="vendor">Vendor</label>
                        <input type="text" id="vendor" name="vendor" 
                               placeholder="Vendor name" value="{{ old('vendor') }}" 
                               class="form-control">
                        @error('vendor')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" class="form-control">
                            <option value="">Select Method</option>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                            <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="gcash" {{ old('payment_method') == 'gcash' ? 'selected' : '' }}>GCash</option>
                            <option value="paypal" {{ old('payment_method') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                        </select>
                        @error('payment_method')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="receipt_number">Receipt Number</label>
                        <input type="text" id="receipt_number" name="receipt_number" 
                               placeholder="Receipt #" value="{{ old('receipt_number') }}" 
                               class="form-control">
                        @error('receipt_number')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" 
                                  placeholder="Additional notes" 
                                  class="form-control" rows="2">{{ old('notes') }}</textarea>
                        @error('notes')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">
                        Clear Form
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Expenses -->
    <div class="content-card">
        <div class="card-header">
            <h3><i class="fas fa-receipt"></i> Recent Expenses</h3>
            <a href="/finance/expenses" class="btn-link">View All</a>
        </div>
        <div class="card-body">
            @if($expenses->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($expense->date)->format('M d, Y') }}</td>
                            <td>{{ $expense->description }}</td>
                            <td>
                                <span class="category-badge">{{ ucfirst($expense->category) }}</span>
                            </td>
                            <td class="text-danger">-₱ {{ number_format($expense->amount, 2) }}</td>
                            <td>
                                <span class="status-badge {{ $expense->status }}">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="/finance/expenses/{{ $expense->id }}/edit" class="btn-icon" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="/finance/expenses/{{ $expense->id }}" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this expense?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @if($expense->status == 'pending')
                                    <form method="POST" action="/finance/expenses/{{ $expense->id }}/mark-paid" class="inline-form">
                                        @csrf
                                        <button type="submit" class="btn-icon" title="Mark as Paid">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <h4>No expenses found</h4>
                <p>Add your first expense above to get started</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="content-card">
        <div class="card-header">
            <h3><i class="fas fa-shopping-cart"></i> Recent Sales</h3>
            <a href="/finance/sales" class="btn-link">View All</a>
        </div>
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <h4>Sales feature coming soon</h4>
                <p>Sales tracking will be available in the next update</p>
            </div>
        </div>
    </div>
</div>

<style>
/* Finance Dashboard Simple Styles */
.finance-dashboard-simple {
    padding: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

/* Header */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-left .page-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.header-left .page-title i {
    color: #3b82f6;
}

.header-left .page-subtitle {
    color: #64748b;
    font-size: 0.95rem;
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.header-actions .btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.95rem;
    text-decoration: none;
}

.header-actions .btn-primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.header-actions .btn-primary:hover {
    background: linear-gradient(135deg, #2563eb, #1e40af);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.header-actions .btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.header-actions .btn-success:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.stat-card.revenue {
    border-top: 4px solid #10b981;
}

.stat-card.expenses {
    border-top: 4px solid #ef4444;
}

.stat-card.profit {
    border-top: 4px solid #3b82f6;
}

.stat-card.pending {
    border-top: 4px solid #f59e0b;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    font-size: 1.25rem;
}

.stat-card.revenue .stat-icon {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.stat-card.expenses .stat-icon {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.stat-card.profit .stat-icon {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.stat-card.pending .stat-icon {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.stat-content .stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.stat-content .stat-label {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.stat-content .stat-change {
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.stat-content .stat-change.positive {
    color: #10b981;
}

.stat-content .stat-change.negative {
    color: #ef4444;
}

.stat-content .view-link {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.stat-content .view-link:hover {
    color: #1d4ed8;
    text-decoration: underline;
}

/* Content Cards */
.content-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    margin-bottom: 1.5rem;
}

.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.card-header .btn-link {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: color 0.2s;
}

.card-header .btn-link:hover {
    color: #1d4ed8;
    text-decoration: underline;
}

.card-body {
    padding: 1.5rem;
}

/* Alerts */
.alert {
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.alert i {
    font-size: 1.25rem;
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.alert-success {
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.2);
    color: #065f46;
}

.alert-success i {
    color: #10b981;
}

.alert-danger {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    color: #991b1b;
}

.alert-danger i {
    color: #ef4444;
}

.alert-danger ul {
    margin: 0.5rem 0 0 0;
    padding-left: 1.25rem;
}

.alert-danger li {
    margin-bottom: 0.25rem;
}

/* Forms */
.expense-form {
    margin: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
    font-size: 0.9rem;
}

.form-group label::after {
    content: ' *';
    color: #ef4444;
    opacity: 0.8;
}

.form-group label:has(+ :not([required]))::after {
    content: '';
}

.form-control {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.95rem;
    color: #374151;
    transition: all 0.2s;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-control::placeholder {
    color: #9ca3af;
}

textarea.form-control {
    min-height: 80px;
    resize: vertical;
}

.error-message {
    color: #ef4444;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.form-actions .btn {
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-actions .btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.form-actions .btn-secondary:hover {
    background: #e5e7eb;
}

.form-actions .btn-primary {
    background: #3b82f6;
    color: white;
}

.form-actions .btn-primary:hover {
    background: #2563eb;
}

/* Tables */
.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #f8fafc;
}

.data-table th {
    padding: 0.875rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #475569;
    font-size: 0.875rem;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    color: #334155;
    font-size: 0.9rem;
}

.data-table tbody tr:hover {
    background: #f8fafc;
}

.data-table .category-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
    background: rgba(59, 130, 246, 0.1);
    color: #1d4ed8;
}

.data-table .status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
}

.status-badge.pending {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
}

.status-badge.paid {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.status-badge.overdue {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

.data-table .action-buttons {
    display: flex;
    gap: 0.5rem;
}

.action-buttons .btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: white;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    text-decoration: none;
}

.action-buttons .btn-icon:hover {
    background: #f8fafc;
    color: #3b82f6;
    border-color: #cbd5e1;
}

.action-buttons .inline-form {
    display: inline;
    margin: 0;
}

.action-buttons .inline-form button {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: white;
    color: #64748b;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    padding: 0;
}

.action-buttons .inline-form button:hover {
    background: #f8fafc;
    color: #ef4444;
    border-color: #cbd5e1;
}

/* Empty States */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #64748b;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h4 {
    margin: 0 0 0.5rem 0;
    color: #475569;
}

.empty-state p {
    margin: 0;
    font-size: 0.95rem;
}

/* Utility Classes */
.text-success { color: #10b981; }
.text-danger { color: #ef4444; }
.text-warning { color: #f59e0b; }
.text-info { color: #3b82f6; }

/* Mobile Responsive */
@media (max-width: 768px) {
    .finance-dashboard-simple {
        padding: 1rem;
    }
    
    .dashboard-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .header-actions {
        justify-content: stretch;
    }
    
    .header-actions .btn {
        flex: 1;
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .data-table {
        font-size: 0.85rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.75rem 0.5rem;
    }
    
    .action-buttons {
        flex-wrap: wrap;
    }
    
    .action-buttons .btn-icon,
    .action-buttons .inline-form button {
        width: 28px;
        height: 28px;
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .header-left .page-title {
        font-size: 1.5rem;
    }
    
    .stat-content .stat-value {
        font-size: 1.5rem;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .card-header .btn-link {
        align-self: flex-start;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
</div>

@endsection