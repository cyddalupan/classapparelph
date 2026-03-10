<x-app-layout>
    <x-slot name="header">
        <div class="dashboard-header-content">
            <h1 class="dashboard-title">
                <i class="fas fa-chart-line"></i>
                Analytics
            </h1>
            <p class="dashboard-subtitle">Business intelligence and performance metrics</p>
        </div>
    </x-slot>

    <div class="dashboard-content">
        <div class="placeholder-container">
            <div class="placeholder-icon">
                <i class="fas fa-chart-line fa-4x"></i>
            </div>
            <h2 class="placeholder-title">Business Analytics</h2>
            <p class="placeholder-text">
                Track your business performance with detailed analytics and reports. 
                Monitor sales, customer behavior, production efficiency, and more.
            </p>
            <div class="placeholder-features">
                <div class="feature-card">
                    <i class="fas fa-chart-bar"></i>
                    <h3>Sales Reports</h3>
                    <p>Detailed sales analysis and trends</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users"></i>
                    <h3>Customer Analytics</h3>
                    <p>Customer behavior and demographics</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-cogs"></i>
                    <h3>Production Metrics</h3>
                    <p>Production efficiency and capacity</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>Financial Reports</h3>
                    <p>Revenue, expenses, and profitability</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>