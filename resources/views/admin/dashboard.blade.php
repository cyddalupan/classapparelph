<x-app-layout>
    @section('page-title', 'Admin Dashboard')
    
    <x-slot name="header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-cogs"></i>
                Admin Dashboard
            </h1>
            <p class="page-subtitle">System administration and configuration</p>
        </div>
    </x-slot>

    <div class="page-content">
        <div class="placeholder-container">
            <div class="placeholder-icon">
                <i class="fas fa-cogs fa-4x"></i>
            </div>
            <h2 class="placeholder-title">Admin Dashboard</h2>
            <p class="placeholder-text">
                This is the admin dashboard. Here you can manage system settings, users, permissions, 
                and other administrative functions.
            </p>
            <div class="placeholder-features">
                <div class="feature-card">
                    <i class="fas fa-users-cog"></i>
                    <h3>User Management</h3>
                    <p>Manage users, roles, and permissions</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-sliders-h"></i>
                    <h3>System Settings</h3>
                    <p>Configure application settings</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>System Analytics</h3>
                    <p>View system performance metrics</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-database"></i>
                    <h3>Database Management</h3>
                    <p>Manage database and backups</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>