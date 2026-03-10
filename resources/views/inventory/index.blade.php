<x-app-layout>
    <x-slot name="header">
        <div class="dashboard-header-content">
            <h1 class="dashboard-title">
                <i class="fas fa-boxes"></i>
                Inventory
            </h1>
            <p class="dashboard-subtitle">Manage stock levels and inventory tracking</p>
        </div>
    </x-slot>

    <div class="dashboard-content">
        <div class="placeholder-container">
            <div class="placeholder-icon">
                <i class="fas fa-boxes fa-4x"></i>
            </div>
            <h2 class="placeholder-title">Inventory Management</h2>
            <p class="placeholder-text">
                Track your t-shirt inventory, monitor stock levels, receive low stock alerts, 
                and manage supplier relationships.
            </p>
            <div class="placeholder-features">
                <div class="feature-card">
                    <i class="fas fa-box"></i>
                    <h3>Stock Tracking</h3>
                    <p>Real-time inventory tracking</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-bell"></i>
                    <h3>Stock Alerts</h3>
                    <p>Automatic low stock notifications</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-truck"></i>
                    <h3>Supplier Management</h3>
                    <p>Manage supplier information</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-exchange-alt"></i>
                    <h3>Stock Transfers</h3>
                    <p>Track inventory movements</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>