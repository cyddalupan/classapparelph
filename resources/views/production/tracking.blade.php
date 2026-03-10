<x-app-layout>
    <x-slot name="header">
        <div class="dashboard-header-content">
            <h1 class="dashboard-title">
                <i class="fas fa-cogs"></i>
                Production
            </h1>
            <p class="dashboard-subtitle">Track production workflow and manage manufacturing</p>
        </div>
    </x-slot>

    <div class="dashboard-content">
        <div class="placeholder-container">
            <div class="placeholder-icon">
                <i class="fas fa-cogs fa-4x"></i>
            </div>
            <h2 class="placeholder-title">Production Tracking</h2>
            <p class="placeholder-text">
                Monitor your t-shirt production workflow from design to shipping. 
                Track order progress, manage production schedules, and optimize manufacturing efficiency.
            </p>
            <div class="placeholder-features">
                <div class="feature-card">
                    <i class="fas fa-project-diagram"></i>
                    <h3>Workflow Management</h3>
                    <p>Track orders through production stages</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Production Scheduling</h3>
                    <p>Schedule and manage production runs</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-user-tie"></i>
                    <h3>Team Management</h3>
                    <p>Assign tasks to production staff</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chart-pie"></i>
                    <h3>Efficiency Analytics</h3>
                    <p>Monitor production performance</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>