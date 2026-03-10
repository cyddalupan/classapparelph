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
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #2563eb, #7c3aed);">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number">24</div>
                    <div class="stat-label">Today's Orders</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up text-success"></i>
                    <span class="text-success">+12%</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number">₱45,820</div>
                    <div class="stat-label">Revenue Today</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up text-success"></i>
                    <span class="text-success">+8%</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <i class="fas fa-tshirt"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number">156</div>
                    <div class="stat-label">In Production</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up text-success"></i>
                    <span class="text-success">+5%</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number">8</div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-down text-danger"></i>
                    <span class="text-danger">-3%</span>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Recent Orders -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i>
                        Recent Orders
                    </h3>
                    <a href="#" class="card-action">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#ORD-00124</td>
                                    <td>Juan Dela Cruz</td>
                                    <td>Today, 14:30</td>
                                    <td>₱2,450</td>
                                    <td><span class="status-badge status-processing">Processing</span></td>
                                </tr>
                                <tr>
                                    <td>#ORD-00123</td>
                                    <td>Maria Santos</td>
                                    <td>Today, 11:15</td>
                                    <td>₱5,820</td>
                                    <td><span class="status-badge status-pending">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>#ORD-00122</td>
                                    <td>TechCorp PH</td>
                                    <td>Yesterday</td>
                                    <td>₱12,500</td>
                                    <td><span class="status-badge status-completed">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>#ORD-00121</td>
                                    <td>Startup Manila</td>
                                    <td>Feb 23</td>
                                    <td>₱3,250</td>
                                    <td><span class="status-badge status-shipped">Shipped</span></td>
                                </tr>
                                <tr>
                                    <td>#ORD-00120</td>
                                    <td>St. Mary's Academy</td>
                                    <td>Feb 22</td>
                                    <td>₱8,750</td>
                                    <td><span class="status-badge status-completed">Completed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="#" class="quick-action">
                            <div class="action-icon" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="action-info">
                                <div class="action-title">New Order</div>
                                <div class="action-desc">Create a new t-shirt order</div>
                            </div>
                        </a>
                        
                        <a href="#" class="quick-action">
                            <div class="action-icon" style="background: linear-gradient(135deg, #7c3aed, #6d28d9);">
                                <i class="fas fa-palette"></i>
                            </div>
                            <div class="action-info">
                                <div class="action-title">Design Studio</div>
                                <div class="action-desc">Create custom designs</div>
                            </div>
                        </a>
                        
                        <a href="#" class="quick-action">
                            <div class="action-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="action-info">
                                <div class="action-title">Analytics</div>
                                <div class="action-desc">View sales reports</div>
                            </div>
                        </a>
                        
                        <a href="#" class="quick-action">
                            <div class="action-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="action-info">
                                <div class="action-title">Settings</div>
                                <div class="action-desc">System configuration</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Production Status -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-industry"></i>
                        Production Status
                    </h3>
                </div>
                <div class="card-body">
                    <div class="production-stats">
                        <div class="production-stage">
                            <div class="stage-info">
                                <div class="stage-title">Design Approval</div>
                                <div class="stage-count">24 orders</div>
                            </div>
                            <div class="stage-progress">
                                <div class="progress-bar" style="width: 80%; background: #2563eb;"></div>
                            </div>
                        </div>
                        
                        <div class="production-stage">
                            <div class="stage-info">
                                <div class="stage-title">Printing</div>
                                <div class="stage-count">48 orders</div>
                            </div>
                            <div class="stage-progress">
                                <div class="progress-bar" style="width: 65%; background: #7c3aed;"></div>
                            </div>
                        </div>
                        
                        <div class="production-stage">
                            <div class="stage-info">
                                <div class="stage-title">Quality Check</div>
                                <div class="stage-count">32 orders</div>
                            </div>
                            <div class="stage-progress">
                                <div class="progress-bar" style="width: 45%; background: #10b981;"></div>
                            </div>
                        </div>
                        
                        <div class="production-stage">
                            <div class="stage-info">
                                <div class="stage-title">Packaging</div>
                                <div class="stage-count">16 orders</div>
                            </div>
                            <div class="stage-progress">
                                <div class="progress-bar" style="width: 30%; background: #f59e0b;"></div>
                            </div>
                        </div>
                        
                        <div class="production-stage">
                            <div class="stage-info">
                                <div class="stage-title">Shipping</div>
                                <div class="stage-count">8 orders</div>
                            </div>
                            <div class="stage-progress">
                                <div class="progress-bar" style="width: 90%; background: #ef4444;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell"></i>
                        Recent Activity
                    </h3>
                </div>
                <div class="card-body">
                    <div class="activity-feed">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-shopping-cart text-primary"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">New order received from <strong>Juan Dela Cruz</strong></div>
                                <div class="activity-time">10 minutes ago</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">Order <strong>#ORD-00120</strong> completed and shipped</div>
                                <div class="activity-time">1 hour ago</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user-plus text-info"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">New customer registered: <strong>Maria Santos</strong></div>
                                <div class="activity-time">2 hours ago</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">Low stock alert for <strong>White Cotton T-Shirts</strong></div>
                                <div class="activity-time">3 hours ago</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-chart-line text-purple"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">Weekly sales report generated</div>
                                <div class="activity-time">5 hours ago</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Features Announcement -->
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-rocket text-primary"></i> New Features Available!</h3>
            </div>
            <div class="card-body">
                <p>We've added new features to help you manage your t-shirt printing business:</p>
                <div class="features-grid">
                    <div class="feature-item">
                        <i class="fas fa-shopping-cart"></i>
                        <div>
                            <h4>Orders Management</h4>
                            <p>Track and manage customer orders</p>
                            <a href="{{ route('orders.index') }}" class="feature-link">Go to Orders →</a>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-tshirt"></i>
                        <div>
                            <h4>Product Catalog</h4>
                            <p>Manage your t-shirt products</p>
                            <a href="{{ route('products.index') }}" class="feature-link">Go to Products →</a>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-users"></i>
                        <div>
                            <h4>Customer Management</h4>
                            <p>Manage customer relationships</p>
                            <a href="{{ route('customers.index') }}" class="feature-link">Go to Customers →</a>
                        </div>
                    </div>
                    @if(Auth::user()->isAdmin())
                    <div class="feature-item">
                        <i class="fas fa-cogs"></i>
                        <div>
                            <h4>Admin Panel</h4>
                            <p>System administration tools</p>
                            <a href="{{ route('admin.dashboard') }}" class="feature-link">Go to Admin →</a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>