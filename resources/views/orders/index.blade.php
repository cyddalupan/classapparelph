<x-app-layout>
    @section('page-title', 'Orders')
    
    <x-slot name="header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-shopping-cart"></i>
                Orders
            </h1>
            <p class="page-subtitle">Manage customer orders and track order status</p>
        </div>
    </x-slot>

    <div class="page-content">
        <div class="page-header">
            <div class="header-actions">
                <button class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Order
                </button>
                <button class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <button class="btn btn-secondary">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>

        <div class="orders-container">
            <div class="orders-filters">
                <div class="filter-group">
                    <label>Status</label>
                    <div class="filter-chips">
                        <span class="chip active">All (156)</span>
                        <span class="chip">Pending (8)</span>
                        <span class="chip">Processing (24)</span>
                        <span class="chip">In Production (48)</span>
                        <span class="chip">Shipped (32)</span>
                        <span class="chip">Completed (44)</span>
                    </div>
                </div>
                <div class="filter-group">
                    <label>Date Range</label>
                    <input type="text" class="date-input" placeholder="Select date range" readonly>
                </div>
            </div>

            <div class="orders-table-container">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#ORD-00123</td>
                            <td>John Smith</td>
                            <td>Feb 24, 2026</td>
                            <td>₱1,850</td>
                            <td><span class="status-badge processing">Processing</span></td>
                            <td>
                                <button class="btn-icon"><i class="fas fa-eye"></i></button>
                                <button class="btn-icon"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-00122</td>
                            <td>Maria Garcia</td>
                            <td>Feb 24, 2026</td>
                            <td>₱2,450</td>
                            <td><span class="status-badge pending">Pending</span></td>
                            <td>
                                <button class="btn-icon"><i class="fas fa-eye"></i></button>
                                <button class="btn-icon"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-00121</td>
                            <td>Robert Johnson</td>
                            <td>Feb 23, 2026</td>
                            <td>₱3,200</td>
                            <td><span class="status-badge in-production">In Production</span></td>
                            <td>
                                <button class="btn-icon"><i class="fas fa-eye"></i></button>
                                <button class="btn-icon"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-00120</td>
                            <td>Sarah Williams</td>
                            <td>Feb 23, 2026</td>
                            <td>₱1,950</td>
                            <td><span class="status-badge shipped">Shipped</span></td>
                            <td>
                                <button class="btn-icon"><i class="fas fa-eye"></i></button>
                                <button class="btn-icon"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-00119</td>
                            <td>Michael Brown</td>
                            <td>Feb 22, 2026</td>
                            <td>₱4,500</td>
                            <td><span class="status-badge completed">Completed</span></td>
                            <td>
                                <button class="btn-icon"><i class="fas fa-eye"></i></button>
                                <button class="btn-icon"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <button class="pagination-btn disabled"><i class="fas fa-chevron-left"></i></button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn">2</button>
                <button class="pagination-btn">3</button>
                <button class="pagination-btn">4</button>
                <button class="pagination-btn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</x-app-layout>