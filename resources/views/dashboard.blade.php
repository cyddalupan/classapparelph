<x-app-layout>
    @section('page-title', 'Dashboard')
    
    <x-slot name="header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </h1>
            <p class="page-subtitle">Welcome back, {{ Auth::user()->name }}! Here's what's happening with your business.</p>
        </div>
    </x-slot>

    <div class="page-content">
        <!-- Empty Dashboard State -->
        <div class="empty-dashboard-state">
            <div class="empty-icon">
                <i class="fas fa-tachometer-alt fa-4x"></i>
            </div>
            <h2 class="empty-title">Dashboard is Empty</h2>
            <p class="empty-description">
                This dashboard has been cleared for new setup. 
                Add your business data, configure settings, or import existing data to get started.
            </p>
            
            <!-- Empty Stats Placeholder -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #2563eb, #7c3aed);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Today's Orders</div>
                    </div>
                    <div class="stat-trend">
                        <span class="text-muted">No data</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">₱0</div>
                        <div class="stat-label">Revenue Today</div>
                    </div>
                    <div class="stat-trend">
                        <span class="text-muted">No data</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">0</div>
                        <div class="stat-label">In Production</div>
                    </div>
                    <div class="stat-trend">
                        <span class="text-muted">No data</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Pending Orders</div>
                    </div>
                    <div class="stat-trend">
                        <span class="text-muted">No data</span>
                    </div>
                </div>
            </div>

            <!-- Setup Instructions -->
            <div class="setup-instructions">
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-rocket"></i>
                            Get Started
                        </h3>
                    </div>
                    <div class="card-body">
                        <p>To set up your dashboard, you can:</p>
                        <div class="setup-options">
                            <div class="setup-option">
                                <i class="fas fa-database"></i>
                                <h4>Import Data</h4>
                                <p>Import existing business data from CSV or Excel files</p>
                            </div>
                            <div class="setup-option">
                                <i class="fas fa-cog"></i>
                                <h4>Configure Settings</h4>
                                <p>Set up your business preferences and system configuration</p>
                            </div>
                            <div class="setup-option">
                                <i class="fas fa-plus-circle"></i>
                                <h4>Add Data Manually</h4>
                                <p>Start adding orders, products, and customers manually</p>
                            </div>
                        </div>
                        
                        <div class="setup-actions">
                            <a href="#" class="btn btn-primary disabled">
                                <i class="fas fa-cog"></i> Settings (Coming Soon)
                            </a>
                            <a href="{{ route('inventory.create') }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add Inventory Item
                            </a>
                            <a href="#" class="btn btn-info disabled">
                                <i class="fas fa-shopping-cart"></i> Create Order (Coming Soon)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty Content Sections -->
            <div class="content-grid">
                <!-- Recent Orders (Empty) -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            Recent Orders
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="empty-table-state">
                            <i class="fas fa-clipboard-list fa-3x"></i>
                            <h4>No Orders Yet</h4>
                            <p>Start by creating your first order to see data here.</p>
                            <a href="#" class="btn btn-primary disabled">
                                <i class="fas fa-plus"></i> Create First Order (Coming Soon)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions (Empty) -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="#" class="quick-action disabled">
                                <div class="action-icon" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="action-info">
                                    <div class="action-title">New Order</div>
                                    <div class="action-desc">Create a new t-shirt order (Coming Soon)</div>
                                </div>
                            </a>
                            
                            <a href="{{ route('inventory.create') }}" class="quick-action">
                                <div class="action-icon" style="background: linear-gradient(135deg, #7c3aed, #6d28d9);">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div class="action-info">
                                    <div class="action-title">Add Inventory</div>
                                    <div class="action-desc">Add new inventory items</div>
                                </div>
                            </a>
                            
                            <a href="{{ route('analytics.dashboard') }}" class="quick-action">
                                <div class="action-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="action-info">
                                    <div class="action-title">Analytics</div>
                                    <div class="action-desc">View analytics dashboard</div>
                                </div>
                            </a>
                            
                            <a href="#" class="quick-action disabled">
                                <div class="action-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <div class="action-info">
                                    <div class="action-title">Settings</div>
                                    <div class="action-desc">System configuration (Coming Soon)</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Production Status (Empty) -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-industry"></i>
                            Production Status
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="empty-production-state">
                            <i class="fas fa-industry fa-3x"></i>
                            <h4>No Production Data</h4>
                            <p>Production status will appear here once you start processing orders.</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity (Empty) -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bell"></i>
                            Recent Activity
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="empty-activity-state">
                            <i class="fas fa-bell fa-3x"></i>
                            <h4>No Recent Activity</h4>
                            <p>Activity feed will show updates as you use the system.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Setup Completion -->
            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-check-circle text-success"></i> Setup Complete</h3>
                </div>
                <div class="card-body">
                    <p>Your dashboard has been cleared and is ready for new setup. The system is now in a clean state.</p>
                    <div class="setup-status">
                        <div class="status-item status-complete">
                            <i class="fas fa-check-circle"></i>
                            <span>Dashboard cleared</span>
                        </div>
                        <div class="status-item status-complete">
                            <i class="fas fa-check-circle"></i>
                            <span>Sample data removed</span>
                        </div>
                        <div class="status-item status-pending">
                            <i class="fas fa-clock"></i>
                            <span>Awaiting your data</span>
                        </div>
                        <div class="status-item status-pending">
                            <i class="fas fa-clock"></i>
                            <span>Ready for configuration</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>