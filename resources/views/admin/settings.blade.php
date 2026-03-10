<x-app-layout>
    @section('page-title', 'System Settings')
    
    <x-slot name="header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-cog"></i>
                System Settings
            </h1>
            <p class="page-subtitle">Configure application settings and preferences</p>
        </div>
    </x-slot>

    <div class="page-content">
        <div class="placeholder-container">
            <div class="placeholder-icon">
                <i class="fas fa-cog fa-4x"></i>
            </div>
            <h2 class="placeholder-title">System Settings</h2>
            <p class="placeholder-text">
                Configure system-wide settings, preferences, and application configuration.
            </p>
            <div class="settings-grid">
                <div class="settings-card">
                    <h3><i class="fas fa-globe"></i> General Settings</h3>
                    <p>Application name, logo, contact information</p>
                </div>
                <div class="settings-card">
                    <h3><i class="fas fa-credit-card"></i> Payment Settings</h3>
                    <p>Payment gateways, currency, tax settings</p>
                </div>
                <div class="settings-card">
                    <h3><i class="fas fa-shipping-fast"></i> Shipping Settings</h3>
                    <p>Shipping providers, rates, zones</p>
                </div>
                <div class="settings-card">
                    <h3><i class="fas fa-envelope"></i> Email Settings</h3>
                    <p>Email templates, SMTP configuration</p>
                </div>
                <div class="settings-card">
                    <h3><i class="fas fa-bell"></i> Notification Settings</h3>
                    <p>Push notifications, alerts, reminders</p>
                </div>
                <div class="settings-card">
                    <h3><i class="fas fa-lock"></i> Security Settings</h3>
                    <p>Password policies, 2FA, access controls</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>