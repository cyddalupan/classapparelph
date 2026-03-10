<x-app-layout>
    <x-slot name="header">
        <div class="dashboard-header-content">
            <h1 class="dashboard-title">
                <i class="fas fa-file-alt"></i>
                Reports
            </h1>
            <p class="dashboard-subtitle">Generate and export business reports</p>
        </div>
    </x-slot>

    <div class="dashboard-content">
        <div class="placeholder-container">
            <div class="placeholder-icon">
                <i class="fas fa-file-alt fa-4x"></i>
            </div>
            <h2 class="placeholder-title">Business Reports</h2>
            <p class="placeholder-text">
                Generate comprehensive business reports for sales, production, inventory, 
                and financial analysis. Export reports in multiple formats.
            </p>
            <div class="placeholder-features">
                <div class="feature-card">
                    <i class="fas fa-chart-bar"></i>
                    <h3>Sales Reports</h3>
                    <p>Daily, weekly, monthly sales analysis</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-cogs"></i>
                    <h3>Production Reports</h3>
                    <p>Production efficiency and output</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-boxes"></i>
                    <h3>Inventory Reports</h3>
                    <p>Stock levels and turnover rates</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>Financial Reports</h3>
                    <p>Profit & loss, revenue, expenses</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>