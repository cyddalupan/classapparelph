@extends('layouts.app')

@section('page-title', 'Finance Dashboard')

@section('content')
<div class="finance-dashboard">
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
            <button id="add-expense-btn" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Expense
            </button>
            <button id="add-sale-btn" class="btn btn-success">
                <i class="fas fa-plus"></i> Add Sale
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card revenue">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="total-revenue">₱ 0</div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-change" id="revenue-change">
                    <i class="fas fa-minus"></i> Loading...
                </div>
            </div>
        </div>

        <div class="stat-card expenses">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="total-expenses">₱ 0</div>
                <div class="stat-label">Total Expenses</div>
                <div class="stat-change" id="expenses-change">
                    <i class="fas fa-minus"></i> Loading...
                </div>
            </div>
        </div>

        <div class="stat-card profit">
            <div class="stat-icon">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="net-profit">₱ 0</div>
                <div class="stat-label">Net Profit</div>
                <div class="stat-change" id="profit-change">
                    <i class="fas fa-minus"></i> Loading...
                </div>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="pending-expenses">0</div>
                <div class="stat-label">Pending Expenses</div>
                <div class="stat-change">
                    <a href="/finance/expenses?status=pending" class="view-link">View All</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="dashboard-content">
        <!-- Recent Expenses -->
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-receipt"></i> Recent Expenses</h3>
                <a href="/finance/expenses" class="btn-link">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="data-table" id="recent-expenses-table">
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
                            <!-- Will be populated by JavaScript -->
                            <tr class="loading-row">
                                <td colspan="6">
                                    <div class="loading-spinner">
                                        <i class="fas fa-spinner fa-spin"></i> Loading expenses...
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-shopping-cart"></i> Recent Sales</h3>
                <a href="/finance/sales" class="btn-link">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="data-table" id="recent-sales-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Will be populated by JavaScript -->
                            <tr class="loading-row">
                                <td colspan="6">
                                    <div class="loading-spinner">
                                        <i class="fas fa-spinner fa-spin"></i> Loading sales...
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar"></i> Monthly Overview</h3>
                <div class="chart-controls">
                    <select id="chart-period" class="form-select">
                        <option value="30">Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="180">Last 6 Months</option>
                        <option value="365">Last Year</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal-overlay" id="add-expense-modal">
    <div class="modal-container">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Add New Expense</h3>
            <button class="modal-close" onclick="closeModal('add-expense-modal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="expense-form" method="POST" action="/finance/expenses">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label for="expense-date">Date *</label>
                        <input type="date" id="expense-date" name="date" required 
                               value="{{ date('Y-m-d') }}" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="expense-amount">Amount (₱) *</label>
                        <input type="number" id="expense-amount" name="amount" 
                               step="0.01" min="0.01" required placeholder="0.00" 
                               class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="expense-category">Category *</label>
                        <select id="expense-category" name="category" required class="form-control">
                            <option value="">Select Category</option>
                            <option value="software">Software</option>
                            <option value="hardware">Hardware</option>
                            <option value="marketing">Marketing</option>
                            <option value="office">Office Supplies</option>
                            <option value="travel">Travel</option>
                            <option value="utilities">Utilities</option>
                            <option value="salaries">Salaries</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="expense-status">Status *</label>
                        <select id="expense-status" name="status" required class="form-control">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="expense-description">Description *</label>
                        <textarea id="expense-description" name="description" 
                                  required placeholder="Enter expense description" 
                                  class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="expense-vendor">Vendor</label>
                        <input type="text" id="expense-vendor" name="vendor" 
                               placeholder="Vendor name" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="expense-payment-method">Payment Method</label>
                        <select id="expense-payment-method" name="payment_method" class="form-control">
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="gcash">GCash</option>
                            <option value="paypal">PayPal</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="expense-receipt">Receipt Number</label>
                        <input type="text" id="expense-receipt" name="receipt_number" 
                               placeholder="Receipt #" class="form-control">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="expense-notes">Notes</label>
                        <textarea id="expense-notes" name="notes" 
                                  placeholder="Additional notes" 
                                  class="form-control" rows="2"></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('add-expense-modal')">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Sale Modal -->
<div class="modal-overlay" id="add-sale-modal">
    <div class="modal-container">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Add New Sale</h3>
            <button class="modal-close" onclick="closeModal('add-sale-modal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="sale-form" method="POST" action="/finance/sales">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label for="sale-date">Date *</label>
                        <input type="date" id="sale-date" name="date" required 
                               value="{{ date('Y-m-d') }}" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="sale-amount">Amount (₱) *</label>
                        <input type="number" id="sale-amount" name="amount" 
                               step="0.01" min="0.01" required placeholder="0.00" 
                               class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="sale-customer">Customer Name</label>
                        <input type="text" id="sale-customer" name="customer" 
                               placeholder="Customer name" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="sale-product">Product/Service</label>
                        <input type="text" id="sale-product" name="product" 
                               placeholder="Product or service" class="form-control">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="sale-description">Description *</label>
                        <textarea id="sale-description" name="description" 
                                  required placeholder="Enter sale description" 
                                  class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="sale-status">Status *</label>
                        <select id="sale-status" name="status" required class="form-control">
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sale-payment-method">Payment Method</label>
                        <select id="sale-payment-method" name="payment_method" class="form-control">
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="gcash">GCash</option>
                            <option value="paypal">PayPal</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('add-sale-modal')">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Sale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success/Error Toast -->
<div class="toast-container" id="toast-container"></div>

<style>
/* Finance Dashboard Styles */
.finance-dashboard {
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

/* Dashboard Content */
.dashboard-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.content-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
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
}

.action-buttons .btn-icon:hover {
    background: #f8fafc;
    color: #3b82f6;
    border-color: #cbd5e1;
}

.loading-row td {
    text-align: center;
    padding: 3rem 1rem;
}

.loading-spinner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    color: #64748b;
}

.loading-spinner i {
    font-size: 1.25rem;
}

/* Charts Section */
.charts-section {
    margin-bottom: 2rem;
}

.chart-container {
    height: 300px;
    position: relative;
}

.chart-controls {
    display: flex;
    gap: 0.75rem;
}

.form-select {
    padding: 0.5rem 2rem 0.5rem 0.75rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: white;
    color: #334155;
    font-size: 0.9rem;
    cursor: pointer;
    transition: border-color 0.2s;
}

.form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Modals */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
    animation: fadeIn 0.2s ease-out;
}

.modal-overlay.active {
    display: flex;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-container {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideUp 0.3s ease-out;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

@keyframes slideUp {
    from { 
        opacity: 0;
        transform: translateY(20px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: white;
    z-index: 10;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modal-close {
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
}

.modal-close:hover {
    background: #f8fafc;
    color: #ef4444;
    border-color: #cbd5e1;
}

.modal-body {
    padding: 1.5rem;
}

/* Forms */
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

.form-actions .btn-success {
    background: #10b981;
    color: white;
}

.form-actions .btn-success:hover {
    background: #059669;
}

/* Toast */
.toast-container {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-width: 350px;
}

.toast {
    padding: 1rem 1.25rem;
    border-radius: 8px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    animation: slideInRight 0.3s ease-out;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.toast.success {
    background: #10b981;
    color: white;
    border-left: 4px solid #059669;
}

.toast.error {
    background: #ef4444;
    color: white;
    border-left: 4px solid #dc2626;
}

.toast.warning {
    background: #f59e0b;
    color: white;
    border-left: 4px solid #d97706;
}

.toast.info {
    background: #3b82f6;
    color: white;
    border-left: 4px solid #1d4ed8;
}

.toast-icon {
    font-size: 1.25rem;
    flex-shrink: 0;
}

.toast-content {
    flex: 1;
}

.toast-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.toast-message {
    font-size: 0.9rem;
    opacity: 0.9;
}

.toast-close {
    background: none;
    border: none;
    color: inherit;
    opacity: 0.7;
    cursor: pointer;
    padding: 0;
    font-size: 1rem;
    transition: opacity 0.2s;
}

.toast-close:hover {
    opacity: 1;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .finance-dashboard {
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
    
    .dashboard-content {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .content-card {
        margin-bottom: 1rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .modal-container {
        margin: 0.5rem;
        max-height: 95vh;
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
    
    .action-buttons .btn-icon {
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

/* Loading States */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.7);
    z-index: 10;
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

.bg-success { background: #10b981; }
.bg-danger { background: #ef4444; }
.bg-warning { background: #f59e0b; }
.bg-info { background: #3b82f6; }
</style>

<script>
// Global variables
let expensesData = [];
let salesData = [];
let monthlyChart = null;

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Initialize event listeners
    initEventListeners();
    
    // Load dashboard data
    loadDashboardData();
    
    // Initialize modals
    initModals();
});

function initEventListeners() {
    // Add Expense button
    document.getElementById('add-expense-btn').addEventListener('click', function() {
        openModal('add-expense-modal');
    });
    
    // Add Sale button
    document.getElementById('add-sale-btn').addEventListener('click', function() {
        openModal('add-sale-modal');
    });
    
    // Expense form submission
    document.getElementById('expense-form').addEventListener('submit', function(e) {
        e.preventDefault();
        submitExpenseForm(this);
    });
    
    // Sale form submission
    document.getElementById('sale-form').addEventListener('submit', function(e) {
        e.preventDefault();
        submitSaleForm(this);
    });
    
    // Chart period change
    document.getElementById('chart-period').addEventListener('change', function() {
        loadMonthlyChart(parseInt(this.value));
    });
}

function initModals() {
    // Close modals when clicking outside
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        
        // Reset form if needed
        if (modalId === 'add-expense-modal') {
            document.getElementById('expense-form').reset();
            document.getElementById('expense-date').value = new Date().toISOString().split('T')[0];
        } else if (modalId === 'add-sale-modal') {
            document.getElementById('sale-form').reset();
            document.getElementById('sale-date').value = new Date().toISOString().split('T')[0];
        }
    }
}

async function loadDashboardData() {
    try {
        // Load statistics
        await loadStatistics();
        
        // Load recent expenses
        await loadRecentExpenses();
        
        // Load recent sales
        await loadRecentSales();
        
        // Load chart
        await loadMonthlyChart(30);
        
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        showToast('error', 'Error', 'Failed to load dashboard data. Please refresh the page.');
    }
}

async function loadStatistics() {
    try {
        const response = await fetch('/api/expenses/statistics', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            
            // Update UI with real data
            document.getElementById('total-revenue').textContent = `₱ ${formatCurrency(data.total_revenue || 0)}`;
            document.getElementById('total-expenses').textContent = `₱ ${formatCurrency(data.total_expenses || 0)}`;
            document.getElementById('net-profit').textContent = `₱ ${formatCurrency(data.net_profit || 0)}`;
            document.getElementById('pending-expenses').textContent = data.pending_expenses || 0;
            
            // Update change indicators
            updateChangeIndicator('revenue-change', data.revenue_change || 0);
            updateChangeIndicator('expenses-change', data.expenses_change || 0);
            updateChangeIndicator('profit-change', data.profit_change || 0);
            
        } else {
            // Use placeholder data if API fails
            setPlaceholderStatistics();
        }
    } catch (error) {
        console.error('Error loading statistics:', error);
        setPlaceholderStatistics();
    }
}

function setPlaceholderStatistics() {
    // Placeholder data for demo
    document.getElementById('total-revenue').textContent = '₱ 245,800';
    document.getElementById('total-expenses').textContent = '₱ 89,450';
    document.getElementById('net-profit').textContent = '₱ 156,350';
    document.getElementById('pending-expenses').textContent = '12';
    
    document.getElementById('revenue-change').innerHTML = '<i class="fas fa-arrow-up"></i> 12.5% from last month';
    document.getElementById('revenue-change').classList.add('positive');
    
    document.getElementById('expenses-change').innerHTML = '<i class="fas fa-arrow-up"></i> 8.2% from last month';
    document.getElementById('expenses-change').classList.add('negative');
    
    document.getElementById('profit-change').innerHTML = '<i class="fas fa-arrow-up"></i> 15.3% from last month';
    document.getElementById('profit-change').classList.add('positive');
}

function updateChangeIndicator(elementId, change) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const icon = change >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
    const text = change >= 0 ? `+${change.toFixed(1)}%` : `${change.toFixed(1)}%`;
    const className = change >= 0 ? 'positive' : 'negative';
    
    element.innerHTML = `<i class="fas ${icon}"></i> ${text} from last month`;
    element.className = 'stat-change ' + className;
}

async function loadRecentExpenses() {
    try {
        const response = await fetch('/api/expenses/recent', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const tableBody = document.querySelector('#recent-expenses-table tbody');
        
        if (response.ok) {
            const data = await response.json();
            expensesData = data;
            
            if (data.length === 0) {
                tableBody.innerHTML = `
                    <tr class="empty-row">
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-receipt"></i>
                                <h4>No expenses found</h4>
                                <p>Add your first expense to get started</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }
            
            // Render expenses
            tableBody.innerHTML = data.map(expense => `
                <tr>
                    <td>${formatDate(expense.date)}</td>
                    <td>${escapeHtml(expense.description)}</td>
                    <td>
                        <span class="category-badge">${formatCategory(expense.category)}</span>
                    </td>
                    <td class="text-danger">-₱ ${formatCurrency(expense.amount)}</td>
                    <td>
                        <span class="status-badge ${expense.status}">${formatStatus(expense.status)}</span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon" onclick="editExpense(${expense.id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" onclick="deleteExpense(${expense.id})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                            ${expense.status === 'pending' ? `
                            <button class="btn-icon" onclick="markExpensePaid(${expense.id})" title="Mark as Paid">
                                <i class="fas fa-check"></i>
                            </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `).join('');
            
        } else {
            // Show error state
            tableBody.innerHTML = `
                <tr class="error-row">
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h4>Failed to load expenses</h4>
                            <p>Please try again later</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error loading recent expenses:', error);
        showToast('error', 'Error', 'Failed to load expenses. Please refresh the page.');
    }
}

async function loadRecentSales() {
    try {
        // This would call your sales API endpoint
        // For now, using placeholder data
        const tableBody = document.querySelector('#recent-sales-table tbody');
        
        // Placeholder sales data
        const placeholderSales = [
            {
                id: 1,
                date: '2026-02-28',
                customer: 'John Smith',
                product: 'Custom T-Shirts (50 pcs)',
                amount: 12500,
                status: 'completed'
            },
            {
                id: 2,
                date: '2026-02-27',
                customer: 'ABC Corporation',
                product: 'Company Polo Shirts',
                amount: 24500,
                status: 'pending'
            },
            {
                id: 3,
                date: '2026-02-26',
                customer: 'Jane Doe',
                product: 'Event T-Shirts',
                amount: 8500,
                status: 'completed'
            }
        ];
        
        salesData = placeholderSales;
        
        tableBody.innerHTML = placeholderSales.map(sale => `
            <tr>
                <td>${formatDate(sale.date)}</td>
                <td>${escapeHtml(sale.customer)}</td>
                <td>${escapeHtml(sale.product)}</td>
                <td class="text-success">+₱ ${formatCurrency(sale.amount)}</td>
                <td>
                    <span class="status-badge ${sale.status}">${formatStatus(sale.status)}</span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon" onclick="editSale(${sale.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon" onclick="deleteSale(${sale.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        
    } catch (error) {
        console.error('Error loading recent sales:', error);
        showToast('error', 'Error', 'Failed to load sales data.');
    }
}

async function loadMonthlyChart(days = 30) {
    try {
        // Destroy existing chart
        if (monthlyChart) {
            monthlyChart.destroy();
        }
        
        // Fetch chart data from API
        const response = await fetch(`/api/expenses/chart?days=${days}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        let chartData;
        if (response.ok) {
            chartData = await response.json();
        } else {
            // Generate placeholder data
            chartData = generatePlaceholderChartData(days);
        }
        
        // Get canvas context
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        
        // Create chart
        monthlyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: chartData.revenue,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Expenses',
                        data: chartData.expenses,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ₱${formatCurrency(context.raw)}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + formatCurrency(value);
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'nearest'
                }
            }
        });
        
    } catch (error) {
        console.error('Error loading chart:', error);
        showToast('error', 'Error', 'Failed to load chart data.');
    }
}

function generatePlaceholderChartData(days) {
    const labels = [];
    const revenue = [];
    const expenses = [];
    
    const today = new Date();
    
    for (let i = days - 1; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);
        
        // Format label
        if (days <= 30) {
            labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        } else {
            labels.push(date.toLocaleDateString('en-US', { month: 'short' }));
        }
        
        // Generate random data with trends
        const baseRevenue = 8000 + Math.random() * 4000;
        const baseExpenses = 3000 + Math.random() * 2000;
        
        // Add some trend
        const trend = i / days;
        revenue.push(Math.round(baseRevenue * (1 + trend * 0.3)));
        expenses.push(Math.round(baseExpenses * (1 + trend * 0.1)));
    }
    
    return { labels, revenue, expenses };
}

async function submitExpenseForm(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    try {
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;
        
        // Get form data
        const formData = new FormData(form);
        
        // Submit to server
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (response.ok) {
            // Success
            showToast('success', 'Success', 'Expense added successfully!');
            closeModal('add-expense-modal');
            
            // Refresh data
            await loadDashboardData();
            
        } else {
            // Show validation errors
            if (result.errors) {
                let errorMessages = [];
                for (const [field, errors] of Object.entries(result.errors)) {
                    errorMessages.push(`${field}: ${errors.join(', ')}`);
                }
                showToast('error', 'Validation Error', errorMessages.join('<br>'));
            } else {
                showToast('error', 'Error', result.message || 'Failed to add expense');
            }
        }
        
    } catch (error) {
        console.error('Error submitting expense:', error);
        showToast('error', 'Error', 'Network error. Please check your connection.');
        
    } finally {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

async function submitSaleForm(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    try {
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;
        
        // Get form data
        const formData = new FormData(form);
        
        // Submit to server (placeholder - you need to implement this endpoint)
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        if (response.ok) {
            // Success
            showToast('success', 'Success', 'Sale added successfully!');
            closeModal('add-sale-modal');
            
            // Refresh data
            await loadRecentSales();
            
        } else {
            // Show error
            const result = await response.json();
            showToast('error', 'Error', result.message || 'Failed to add sale');
        }
        
    } catch (error) {
        console.error('Error submitting sale:', error);
        showToast('error', 'Error', 'Network error. Please check your connection.');
        
    } finally {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// CRUD Operations
async function editExpense(id) {
    try {
        // Fetch expense data
        const response = await fetch(`/api/expenses/${id}`, {
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const expense = await response.json();
            
            // Populate modal with expense data
            document.getElementById('expense-date').value = expense.date;
            document.getElementById('expense-amount').value = expense.amount;
            document.getElementById('expense-category').value = expense.category;
            document.getElementById('expense-status').value = expense.status;
            document.getElementById('expense-description').value = expense.description || '';
            document.getElementById('expense-vendor').value = expense.vendor || '';
            document.getElementById('expense-payment-method').value = expense.payment_method || '';
            document.getElementById('expense-receipt').value = expense.receipt_number || '';
            document.getElementById('expense-notes').value = expense.notes || '';
            
            // Change form to update mode
            const form = document.getElementById('expense-form');
            form.action = `/finance/expenses/${id}`;
            form.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-save"></i> Update Expense';
            
            // Add hidden method field for PUT request
            if (!form.querySelector('input[name="_method"]')) {
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);
            }
            
            // Open modal
            openModal('add-expense-modal');
            
        } else {
            showToast('error', 'Error', 'Failed to load expense data');
        }
    } catch (error) {
        console.error('Error editing expense:', error);
        showToast('error', 'Error', 'Failed to load expense data');
    }
}

async function deleteExpense(id) {
    if (!confirm('Are you sure you want to delete this expense? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch(`/finance/expenses/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            showToast('success', 'Success', 'Expense deleted successfully!');
            await loadDashboardData();
        } else {
            const result = await response.json();
            showToast('error', 'Error', result.message || 'Failed to delete expense');
        }
    } catch (error) {
        console.error('Error deleting expense:', error);
        showToast('error', 'Error', 'Network error. Please try again.');
    }
}

async function markExpensePaid(id) {
    try {
        const response = await fetch(`/finance/expenses/${id}/mark-paid`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            showToast('success', 'Success', 'Expense marked as paid!');
            await loadDashboardData();
        } else {
            const result = await response.json();
            showToast('error', 'Error', result.message || 'Failed to update expense');
        }
    } catch (error) {
        console.error('Error marking expense as paid:', error);
        showToast('error', 'Error', 'Network error. Please try again.');
    }
}

// Sale operations (placeholder - implement as needed)
function editSale(id) {
    showToast('info', 'Info', 'Edit sale functionality coming soon!');
}

function deleteSale(id) {
    if (confirm('Are you sure you want to delete this sale?')) {
        showToast('success', 'Success', 'Sale deleted (placeholder)');
        // In production: Make API call to delete sale
    }
}

// Utility Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}

function formatCategory(category) {
    const categories = {
        'software': 'Software',
        'hardware': 'Hardware',
        'marketing': 'Marketing',
        'office': 'Office Supplies',
        'travel': 'Travel',
        'utilities': 'Utilities',
        'salaries': 'Salaries',
        'other': 'Other'
    };
    return categories[category] || category;
}

function formatStatus(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(type, title, message) {
    const container = document.getElementById('toast-container');
    const toastId = 'toast-' + Date.now();
    
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.id = toastId;
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="${icons[type]}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="removeToast('${toastId}')">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        removeToast(toastId);
    }, 5000);
}

function removeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.style.animation = 'slideInRight 0.3s ease-out reverse';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Set today's date in date inputs
    const today = new Date().toISOString().split('T')[0];
    document.querySelectorAll('input[type="date"]').forEach(input => {
        if (!input.value) {
            input.value = today;
        }
    });
});
</script>

@endsection
