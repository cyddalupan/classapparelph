<x-app-layout>
    @section('page-title', 'Finance Dashboard')
    
    <x-slot name="header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-chart-line"></i>
                Finance Dashboard
            </h1>
            <p class="page-subtitle">Track your revenue, expenses, and financial performance.</p>
        </div>
    </x-slot>

    <div class="page-content">
        <!-- Empty Finance Dashboard State -->
        <div class="empty-dashboard-state">
            <div class="empty-icon">
                <i class="fas fa-chart-line fa-4x"></i>
            </div>
            <h2 class="empty-title">Finance Dashboard is Empty</h2>
            <p class="empty-description">
                This finance dashboard has been cleared for new setup. 
                Add your financial data, configure accounting settings, or import existing records to get started.
            </p>
            
            <!-- Empty Finance Stats -->
            <div class="stats-grid finance-stats">
                <div class="stat-card revenue">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">₱ 0.00</div>
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-change">
                            <span class="text-muted">No data</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card expense">
                    <div class="stat-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">₱ 0.00</div>
                        <div class="stat-label">Total Expenses</div>
                        <div class="stat-change">
                            <span class="text-muted">No data</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card profit">
                    <div class="stat-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">₱ 0.00</div>
                        <div class="stat-label">Net Profit</div>
                        <div class="stat-change">
                            <span class="text-muted">No data</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">0</div>
                        <div class="stat-label">Pending Expenses</div>
                        <div class="stat-change">
                            <span class="text-muted">No data</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Setup Instructions -->
            <div class="setup-instructions">
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-rocket"></i>
                            Get Started with Finance
                        </h3>
                    </div>
                    <div class="card-body">
                        <p>To set up your finance dashboard, you can:</p>
                        <div class="setup-options">
                            <div class="setup-option">
                                <i class="fas fa-file-import"></i>
                                <h4>Import Financial Data</h4>
                                <p>Import bank statements, invoices, and expense records</p>
                            </div>
                            <div class="setup-option">
                                <i class="fas fa-calculator"></i>
                                <h4>Configure Accounting</h4>
                                <p>Set up chart of accounts, tax rates, and financial periods</p>
                            </div>
                            <div class="setup-option">
                                <i class="fas fa-plus-circle"></i>
                                <h4>Add Transactions</h4>
                                <p>Start adding revenue and expense transactions manually</p>
                            </div>
                        </div>
                        
                        <div class="setup-actions">
                            <a href="#" class="btn btn-primary disabled">
                                <i class="fas fa-cog"></i> Finance Settings (Coming Soon)
                            </a>
                            <a href="{{ route('finance.expenses') }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add Expense
                            </a>
                            <a href="{{ route('finance.reports') }}" class="btn btn-info">
                                <i class="fas fa-chart-bar"></i> View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty Content Sections -->
            <div class="content-grid">
                <!-- Revenue vs Expenses Chart (Empty) -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar"></i>
                            Revenue vs Expenses
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="empty-chart-state">
                            <i class="fas fa-chart-bar fa-3x"></i>
                            <h4>No Financial Data</h4>
                            <p>Add revenue and expense transactions to see charts here.</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions (Empty) -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exchange-alt"></i>
                            Recent Transactions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="empty-table-state">
                            <i class="fas fa-exchange-alt fa-3x"></i>
                            <h4>No Transactions Yet</h4>
                            <p>Start by adding your first financial transaction.</p>
                            <a href="{{ route('finance.transactions.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add First Transaction
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Expense Categories (Empty) -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie"></i>
                            Expense Categories
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="empty-chart-state">
                            <i class="fas fa-chart-pie fa-3x"></i>
                            <h4>No Expense Data</h4>
                            <p>Add expense transactions to see category breakdowns.</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary (Empty) -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-invoice-dollar"></i>
                            Financial Summary
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="empty-summary-state">
                            <i class="fas fa-file-invoice-dollar fa-3x"></i>
                            <h4>No Summary Available</h4>
                            <p>Financial summary will appear once you have transaction data.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Setup Completion -->
            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-check-circle text-success"></i> Finance Setup Complete</h3>
                </div>
                <div class="card-body">
                    <p>Your finance dashboard has been cleared and is ready for new setup. The financial system is now in a clean state.</p>
                    <div class="setup-status">
                        <div class="status-item status-complete">
                            <i class="fas fa-check-circle"></i>
                            <span>Finance dashboard cleared</span>
                        </div>
                        <div class="status-item status-complete">
                            <i class="fas fa-check-circle"></i>
                            <span>Sample financial data removed</span>
                        </div>
                        <div class="status-item status-pending">
                            <i class="fas fa-clock"></i>
                            <span>Awaiting your financial data</span>
                        </div>
                        <div class="status-item status-pending">
                            <i class="fas fa-clock"></i>
                            <span>Ready for accounting setup</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>