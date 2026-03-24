<x-app-layout>
    @section('page-title', 'Inventory Management')
    
    <x-slot name="header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-boxes"></i>
                Inventory Management
            </h1>
            <p class="page-subtitle">Track stock levels, manage inventory, and receive alerts</p>
        </div>
    </x-slot>

    @section('content')
    <div class="page-content">
        <!-- Empty Inventory State -->
        <div class="empty-inventory-state">
            <div class="empty-icon">
                <i class="fas fa-boxes fa-4x"></i>
            </div>
            <h2 class="empty-title">Inventory Management is Empty</h2>
            <p class="empty-description">
                This inventory management system has been cleared for new setup. 
                Add your inventory items, configure stock settings, or import existing inventory data to get started.
            </p>
            
            <!-- Empty Inventory Stats -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="stat-title">Total Items</h6>
                                    <h3 class="stat-value">0</h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-boxes text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="stat-title">Active Items</h6>
                                    <h3 class="stat-value">0</h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="stat-title">Low Stock</h6>
                                    <h3 class="stat-value text-warning">0</h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="stat-title">Total Value</h6>
                                    <h3 class="stat-value">₱0.00</h3>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-money-bill-wave text-info"></i>
                                </div>
                            </div>
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
                            Get Started with Inventory
                        </h3>
                    </div>
                    <div class="card-body">
                        <p>To set up your inventory management, you can:</p>
                        <div class="setup-options">
                            <div class="setup-option">
                                <i class="fas fa-file-import"></i>
                                <h4>Import Inventory</h4>
                                <p>Import existing inventory data from CSV or Excel files</p>
                            </div>
                            <div class="setup-option">
                                <i class="fas fa-cog"></i>
                                <h4>Configure Settings</h4>
                                <p>Set up categories, units of measure, and stock alerts</p>
                            </div>
                            <div class="setup-option">
                                <i class="fas fa-plus-circle"></i>
                                <h4>Add Items Manually</h4>
                                <p>Start adding inventory items one by one</p>
                            </div>
                        </div>
                        
                        <div class="setup-actions">
                            <a href="{{ route('inventory.select-category') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Inventory Item
                            </a>
                            <a href="#" class="btn btn-secondary disabled">
                                <i class="fas fa-cog"></i> Inventory Settings (Coming Soon)
                            </a>
                            <a href="#" classbtn btn-info disabled">
                                <i class="fas fa-file-import"></i> Import Data (Coming Soon)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty Inventory Table -->
            <div class="card">
                <div class="card-body">
                    <div class="empty-table-state">
                        <i class="fas fa-clipboard-list fa-3x"></i>
                        <h4>No Inventory Items Yet</h4>
                        <p>Start by adding your first inventory item to see data here.</p>
                        <a href="{{ route('inventory.select-category') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Item
                        </a>
                    </div>
                </div>
            </div>

            <!-- Setup Completion -->
            <div class="content-card mt-4">
                <div class="card-header">
                    <h3><i class="fas fa-check-circle text-success"></i> Inventory Setup Complete</h3>
                </div>
                <div class="card-body">
                    <p>Your inventory management system has been cleared and is ready for new setup. The inventory is now in a clean state.</p>
                    <div class="setup-status">
                        <div class="status-item status-complete">
                            <i class="fas fa-check-circle"></i>
                            <span>Inventory cleared</span>
                        </div>
                        <div class="status-item status-complete">
                            <i class="fas fa-check-circle"></i>
                            <span>Sample data removed</span>
                        </div>
                        <div class="status-item status-pending">
                            <i class="fas fa-clock"></i>
                            <span>Awaiting your inventory data</span>
                        </div>
                        <div class="status-item status-pending">
                            <i class="fas fa-clock"></i>
                            <span>Ready for stock management setup</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
</x-app-layout>