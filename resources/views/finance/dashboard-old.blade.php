@extends('layouts.app')

@section('page-title', 'Finance Dashboard')

@section('content')
<div class="page-header">
    <div class="header-content">
        <h1 class="page-title">
            <i class="fas fa-chart-line"></i>
            Finance Dashboard
        </h1>
        <p class="page-subtitle">Track sales, expenses, and financial performance</p>
    </div>
    <div class="header-actions">
        <button id="add-expense-btn" class="btn btn-primary btn-add-expense" onclick="showAddExpenseModal()">
            <i class="fas fa-plus"></i> Add Expense
        </button>
        <button class="btn btn-success" onclick="showAddSaleModal()">
            <i class="fas fa-plus"></i> Add Sale
        </button>
    </div>
</div>

<!-- Quick Stats -->
<div class="stats-grid">
    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">₱ 245,800</div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> 12.5% from last month
            </div>
        </div>
    </div>

    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">₱ 89,450</div>
            <div class="stat-label">Total Expenses</div>
            <div class="stat-change negative">
                <i class="fas fa-arrow-up"></i> 8.2% from last month
            </div>
        </div>
    </div>

    <div class="stat-card primary">
        <div class="stat-icon">
            <i class="fas fa-coins"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">₱ 156,350</div>
            <div class="stat-label">Net Profit</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> 15.3% from last month
            </div>
        </div>
    </div>

    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">187</div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> 24 from last week
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="section">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-chart-bar"></i>
            Financial Overview
        </h2>
        <div class="section-actions">
            <select class="select-input">
                <option value="7d">Last 7 Days</option>
                <option value="30d" selected>Last 30 Days</option>
                <option value="90d">Last 90 Days</option>
                <option value="1y">Last Year</option>
            </select>
        </div>
    </div>
    
    <div class="charts-container">
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Revenue vs Expenses</h3>
                <div class="chart-legend">
                    <div class="legend-item">
                        <span class="legend-color revenue"></span>
                        <span>Revenue</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color expense"></span>
                        <span>Expenses</span>
                    </div>
                </div>
            </div>
            <div class="chart-placeholder">
                <div class="placeholder-text">
                    <i class="fas fa-chart-line"></i>
                    <p>Revenue vs Expenses Chart</p>
                    <small>Interactive chart will be displayed here</small>
                </div>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Profit Margin Trend</h3>
                <div class="chart-legend">
                    <div class="legend-item">
                        <span class="legend-color profit"></span>
                        <span>Profit Margin</span>
                    </div>
                </div>
            </div>
            <div class="chart-placeholder">
                <div class="placeholder-text">
                    <i class="fas fa-percentage"></i>
                    <p>Profit Margin Chart</p>
                    <small>Interactive chart will be displayed here</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="section">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-history"></i>
            Recent Transactions
        </h2>
        <div class="section-actions">
            <a href="{{ route('finance.expenses') }}" class="btn btn-outline">View All Expenses</a>
            <a href="{{ route('finance.sales') }}" class="btn btn-outline">View All Sales</a>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Feb 28, 2026</td>
                    <td>Custom T-Shirt Order #1001</td>
                    <td><span class="badge sale">Sale</span></td>
                    <td>T-Shirt Printing</td>
                    <td class="amount positive">₱ 2,450</td>
                    <td><span class="status completed">Completed</span></td>
                    <td>
                        <button class="btn-icon" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Feb 27, 2026</td>
                    <td>Ink Cartridge Purchase</td>
                    <td><span class="badge expense">Expense</span></td>
                    <td>Supplies</td>
                    <td class="amount negative">₱ 8,750</td>
                    <td><span class="status pending">Pending</span></td>
                    <td>
                        <button class="btn-icon" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Feb 26, 2026</td>
                    <td>Bulk Hoodie Order #1002</td>
                    <td><span class="badge sale">Sale</span></td>
                    <td>Hoodie Printing</td>
                    <td class="amount positive">₱ 15,800</td>
                    <td><span class="status completed">Completed</span></td>
                    <td>
                        <button class="btn-icon" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Feb 25, 2026</td>
                    <td>Electricity Bill</td>
                    <td><span class="badge expense">Expense</span></td>
                    <td>Utilities</td>
                    <td class="amount negative">₱ 3,250</td>
                    <td><span class="status paid">Paid</span></td>
                    <td>
                        <button class="btn-icon" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Feb 24, 2026</td>
                    <td>Logo Design Service</td>
                    <td><span class="badge sale">Sale</span></td>
                    <td>Design Services</td>
                    <td class="amount positive">₱ 5,000</td>
                    <td><span class="status completed">Completed</span></td>
                    <td>
                        <button class="btn-icon" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Top Categories -->
<div class="section">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-tags"></i>
            Top Revenue Categories
        </h2>
    </div>

    <div class="categories-grid">
        <div class="category-card">
            <div class="category-icon">
                <i class="fas fa-tshirt"></i>
            </div>
            <div class="category-content">
                <h3 class="category-title">T-Shirt Printing</h3>
                <div class="category-amount">₱ 128,450</div>
                <div class="category-percentage">52.3% of revenue</div>
            </div>
        </div>

        <div class="category-card">
            <div class="category-icon">
                <i class="fas fa-hoodie"></i>
            </div>
            <div class="category-content">
                <h3 class="category-title">Hoodie Printing</h3>
                <div class="category-amount">₱ 68,900</div>
                <div class="category-percentage">28.1% of revenue</div>
            </div>
        </div>

        <div class="category-card">
            <div class="category-icon">
                <i class="fas fa-paint-brush"></i>
            </div>
            <div class="category-content">
                <h3 class="category-title">Design Services</h3>
                <div class="category-amount">₱ 32,500</div>
                <div class="category-percentage">13.2% of revenue</div>
            </div>
        </div>

        <div class="category-card">
            <div class="category-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="category-content">
                <h3 class="category-title">Merchandise</h3>
                <div class="category-amount">₱ 15,950</div>
                <div class="category-percentage">6.4% of revenue</div>
            </div>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div id="add-expense-modal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-plus"></i>
                Add New Expense
            </h2>
            <button class="modal-close" onclick="closeAddExpenseModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="modal-body">
            <form id="expense-form" onsubmit="submitExpense(event)">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="expenseDate">
                            <i class="fas fa-calendar"></i>
                            Date *
                        </label>
                        <input type="date" id="expenseDate" name="date" class="form-input" required 
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="expenseAmount">
                            <i class="fas fa-money-bill-wave"></i>
                            Amount (₱) *
                        </label>
                        <input type="number" id="expenseAmount" name="amount" class="form-input" 
                               placeholder="0.00" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="expenseCategory">
                            <i class="fas fa-tag"></i>
                            Category *
                        </label>
                        <select id="expenseCategory" name="category" class="form-input" required>
                            <option value="">Select Category</option>
                            <option value="supplies">Supplies</option>
                            <option value="utilities">Utilities</option>
                            <option value="salaries">Salaries</option>
                            <option value="marketing">Marketing</option>
                            <option value="equipment">Equipment</option>
                            <option value="rent">Rent</option>
                            <option value="transportation">Transportation</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="expenseStatus">
                            <i class="fas fa-check-circle"></i>
                            Status *
                        </label>
                        <select id="expenseStatus" name="status" class="form-input" required>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="expenseDescription">
                            <i class="fas fa-file-alt"></i>
                            Description *
                        </label>
                        <textarea id="expenseDescription" name="description" class="form-input" 
                                  rows="3" placeholder="Enter expense description..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="expenseVendor">
                            <i class="fas fa-building"></i>
                            Vendor
                        </label>
                        <input type="text" id="expenseVendor" name="vendor" class="form-input" 
                               placeholder="Vendor name">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="expensePaymentMethod">
                            <i class="fas fa-credit-card"></i>
                            Payment Method
                        </label>
                        <select id="expensePaymentMethod" name="payment_method" class="form-input">
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="gcash">GCash</option>
                            <option value="maya">Maya</option>
                            <option value="paypal">PayPal</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="expenseReceipt">
                            <i class="fas fa-receipt"></i>
                            Receipt Number
                        </label>
                        <input type="text" id="expenseReceipt" name="receipt_number" class="form-input" 
                               placeholder="Optional receipt number">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="expenseNotes">
                            <i class="fas fa-sticky-note"></i>
                            Notes
                        </label>
                        <textarea id="expenseNotes" name="notes" class="form-input" 
                                  rows="2" placeholder="Additional notes..."></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="closeAddExpenseModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add CSS for finance dashboard -->
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: var(--transition);
        border-left: 4px solid;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .stat-card.success {
        border-left-color: var(--success);
    }

    .stat-card.warning {
        border-left-color: #f59e0b;
    }

    .stat-card.primary {
        border-left-color: var(--primary);
    }

    .stat-card.info {
        border-left-color: #3b82f6;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }

    .stat-card.success .stat-icon {
        background: linear-gradient(135deg, var(--success), #059669);
    }

    .stat-card.warning .stat-icon {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .stat-card.primary .stat-icon {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    }

    .stat-card.info .stat-icon {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    }

    .stat-content {
        flex: 1;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--dark);
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--gray);
        margin-bottom: 0.5rem;
    }

    .stat-change {
        font-size: 0.75rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .stat-change.positive {
        color: var(--success);
    }

    .stat-change.negative {
        color: #ef4444;
    }

    .section {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        margin-bottom: 1.5rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .select-input {
        padding: 0.5rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        color: var(--dark);
        font-family: 'Inter', sans-serif;
        font-size: 0.875rem;
        cursor: pointer;
        transition: var(--transition);
    }

    .select-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .charts-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
    }

    .chart-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .chart-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--dark);
    }

    .chart-legend {
        display: flex;
        gap: 1rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: var(--gray);
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 2px;
    }

    .legend-color.revenue {
        background: var(--primary);
    }

    .legend-color.expense {
        background: #ef4444;
    }

    .legend-color.profit {
        background: var(--success);
    }

    .chart-placeholder {
        height: 250px;
        background: white;
        border-radius: 8px;
        border: 2px dashed #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .placeholder-text {
        text-align: center;
        color: var(--gray);
    }

    .placeholder-text i {
        font-size: 3rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }

    .placeholder-text p {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .placeholder-text small {
        font-size: 0.75rem;
    }

    .table-container {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: #f8fafc;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e2e8f0;
    }

    .data-table td {
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
        color: var(--dark);
    }

    .data-table tr:hover {
        background: #f8fafc;
    }

    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .badge.sale {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .badge.expense {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .amount {
        font-weight: 600;
        font-family: 'Inter', sans-serif;
    }

    .amount.positive {
        color: var(--success);
    }

    .amount.negative {
        color: #ef4444;
    }

    .status {
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status.completed {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .status.pending {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .status.paid {
        background: rgba(37, 99, 235, 0.1);
        color: var(--primary);
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: white;
        color: var(--gray);
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-icon:hover {
        background: #f8fafc;
        color: var(--primary);
        border-color: var(--primary);
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .category-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        transition: var(--transition);
        border: 1px solid #e2e8f0;
    }

    .category-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow);
        border-color: var(--primary);
    }

    .category-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin: 0 auto 1rem;
    }

    .category-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .category-amount {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0.25rem;
    }

    .category-percentage {
        font-size: 0.875rem;
        color: var(--gray);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .charts-container {
            grid-template-columns: 1fr;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .section-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .categories-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .categories-grid {
            grid-template-columns: 1fr;
        }

        .data-table {
            font-size: 0.875rem;
        }

        .data-table th,
        .data-table td {
            padding: 0.75rem;
        }
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        padding: 1rem;
        animation: fadeIn 0.3s ease;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-container {
        background: white;
        border-radius: 16px;
        width: 100%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }

    /* Mobile Responsive Design */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
        }
        
        .header-actions {
            width: 100%;
            display: flex;
            gap: 0.5rem;
        }
        
        .header-actions .btn {
            flex: 1;
            min-width: 0;
            padding: 0.75rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
            padding: 0 1rem;
        }
        
        .stat-card {
            padding: 1rem;
        }
        
        .stat-value {
            font-size: 1.5rem;
        }
        
        .chart-container {
            margin: 0 -1rem;
            padding: 1rem;
            border-radius: 0;
        }
        
        .recent-activity {
            margin: 0 -1rem;
            padding: 1rem;
        }
        
        .modal-container {
            max-width: 95vw;
            margin: 0.5rem;
            border-radius: 12px;
        }
        
        .modal-header {
            padding: 1rem;
        }
        
        .modal-body {
            padding: 1rem;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .form-actions {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .form-actions .btn {
            width: 100%;
        }
    }
    
    @media (max-width: 480px) {
        .page-header {
            padding: 0.75rem;
        }
        
        .header-actions {
            flex-direction: column;
        }
        
        .modal-container {
            max-height: 95vh;
            margin: 0.25rem;
        }
        
        .modal-title {
            font-size: 1.25rem;
        }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #f8fafc, white);
        border-radius: 16px 16px 0 0;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .modal-close {
        background: none;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gray);
        cursor: pointer;
        transition: var(--transition);
    }

    .modal-close:hover {
        background: #f1f5f9;
        color: var(--danger);
        transform: rotate(90deg);
    }

    .modal-body {
        padding: 2rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-input {
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 1rem;
        transition: var(--transition);
        background: white;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-input::placeholder {
        color: #94a3b8;
    }

    textarea.form-input {
        min-height: 100px;
        resize: vertical;
    }

    select.form-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px;
        padding-right: 3rem;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .btn-outline {
        background: white;
        border: 2px solid #e2e8f0;
        color: var(--dark);
    }

    .btn-outline:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    /* Modal Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
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

    /* Responsive Modal */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .modal-container {
            max-width: 95%;
            max-height: 95vh;
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }
    }
</style>

<script>
    function showAddExpenseModal() {
        console.log('showAddExpenseModal called');
        const modal = document.getElementById('add-expense-modal');
        console.log('Modal element:', modal);
        
        if (!modal) {
            console.error('❌ Modal not found! Looking for #add-expense-modal');
            alert('Error: Modal not found. Please check console for details.');
            return;
        }
        
        modal.classList.add('active');
        console.log('Added "active" class. Current classes:', modal.className);
        document.body.style.overflow = 'hidden';
        
        // Focus on first input
        setTimeout(() => {
            const descriptionInput = document.getElementById('expenseDescription');
            if (descriptionInput) {
                descriptionInput.focus();
                console.log('Focused on expenseDescription input');
            } else {
                console.error('❌ expenseDescription input not found');
            }
        }, 100);
    }

    function closeAddExpenseModal() {
        console.log('closeAddExpenseModal called');
        const modal = document.getElementById('add-expense-modal');
        console.log('Modal element:', modal);
        
        if (!modal) {
            console.error('❌ Modal not found! Looking for #add-expense-modal');
            return;
        }
        
        modal.classList.remove('active');
        console.log('Removed "active" class. Current classes:', modal.className);
        document.body.style.overflow = '';
        
        // Reset form
        const form = document.getElementById('expense-form');
        if (form) {
            form.reset();
            console.log('Form reset');
        } else {
            console.error('❌ expense-form not found');
        }
        
        const dateInput = document.getElementById('expenseDate');
        if (dateInput) {
            dateInput.value = '<?php echo date('Y-m-d'); ?>';
            console.log('Date reset to today');
        } else {
            console.error('❌ expenseDate input not found');
        }
    }

    function showAddSaleModal() {
        alert('Add Sale modal would open here. This is a placeholder.');
    }

    function submitExpense(event) {
        event.preventDefault();
        console.log('submitExpense called');
        
        // Get form data
        const rawAmount = document.getElementById('expenseAmount').value;
        
        // Parse amount properly - remove any commas, ensure decimal format
        let parsedAmount = parseFloat(rawAmount.replace(/,/g, ''));
        if (isNaN(parsedAmount)) {
            alert('Please enter a valid amount');
            document.getElementById('expenseAmount').focus();
            return;
        }
        
        // Ensure amount has exactly 2 decimal places for database
        parsedAmount = Math.round(parsedAmount * 100) / 100;
        
        const expenseData = {
            date: document.getElementById('expenseDate').value,
            amount: parsedAmount,
            category: document.getElementById('expenseCategory').value,
            status: document.getElementById('expenseStatus').value,
            description: document.getElementById('expenseDescription').value.trim(),
            vendor: document.getElementById('expenseVendor').value.trim(),
            payment_method: document.getElementById('expensePaymentMethod').value,
            receipt_number: document.getElementById('expenseReceipt').value.trim(),
            notes: document.getElementById('expenseNotes').value.trim(),
            _token: document.querySelector('meta[name="csrf-token"]').content
        };

        console.log('Expense data to submit:', expenseData);
        console.log('Amount details:', {
            raw: rawAmount,
            parsed: parsedAmount,
            formatted: parsedAmount.toFixed(2)
        });

        // Validate date format (YYYY-MM-DD)
        const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
        if (!expenseData.date || !dateRegex.test(expenseData.date)) {
            alert('Please enter a valid date in YYYY-MM-DD format');
            document.getElementById('expenseDate').focus();
            return;
        }

        // Validation
        if (!expenseData.description) {
            alert('Please enter a description');
            document.getElementById('expenseDescription').focus();
            return;
        }

        if (!expenseData.amount || expenseData.amount <= 0) {
            alert('Please enter a valid amount');
            document.getElementById('expenseAmount').focus();
            return;
        }

        if (!expenseData.category) {
            alert('Please select a category');
            document.getElementById('expenseCategory').focus();
            return;
        }

        // Show loading state
        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;

        // Submit to backend - Use FormData instead of JSON for better Laravel compatibility
        const formData = new FormData();
        formData.append('date', expenseData.date);
        formData.append('amount', expenseData.amount.toFixed(2)); // Ensure 2 decimal places
        formData.append('category', expenseData.category);
        formData.append('status', expenseData.status);
        formData.append('description', expenseData.description);
        formData.append('vendor', expenseData.vendor || '');
        formData.append('payment_method', expenseData.payment_method || '');
        formData.append('receipt_number', expenseData.receipt_number || '');
        formData.append('notes', expenseData.notes || '');
        formData.append('_token', expenseData._token);

        console.log('FormData entries:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value} (type: ${typeof value})`);
        }

        // Submit to backend
        fetch('/finance/expenses', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': expenseData._token,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status, response.statusText);
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(`Server error: ${response.status} - ${JSON.stringify(err)}`);
                }).catch(() => {
                    throw new Error(`Server error: ${response.status} ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                // Format amount with PHP currency
                const formattedAmount = new Intl.NumberFormat('en-PH', {
                    style: 'currency',
                    currency: 'PHP'
                }).format(expenseData.amount);

                // Show success message
                alert(`Expense added successfully: ${expenseData.description} - ${formattedAmount}`);
                
                // Close modal
                closeAddExpenseModal();
                
                // Reload page to show new expense
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Handle validation errors
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join('\n');
                    throw new Error(`Validation errors:\n${errorMessages}`);
                }
                throw new Error(data.message || 'Failed to save expense');
            }
        })
        .catch(error => {
            console.error('Error saving expense:', error);
            alert(`Error: ${error.message}\n\nPlease check the console for more details.`);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }

    // Initialize chart select
    document.addEventListener('DOMContentLoaded', function() {
        // Close modal on overlay click
        const modal = document.getElementById('add-expense-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAddExpenseModal();
                }
            });
        }

        const timeSelect = document.querySelector('.select-input');
        if (timeSelect) {
            timeSelect.addEventListener('change', function() {
                console.log('Time range changed to:', this.value);
                // In a real app, this would fetch new chart data
            });
        }

        // Add click handlers to table rows
        document.querySelectorAll('.data-table tbody tr').forEach(row => {
            row.addEventListener('click', function(e) {
                if (!e.target.closest('.btn-icon')) {
                    const description = this.cells[1].textContent;
                    console.log('Viewing transaction:', description);
                    // In a real app, this would open a detail view
                }
            });
        });
    });
</script>
@endsection
