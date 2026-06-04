@extends('layouts.app')

@section('page-title', 'User Details - ' . $user->name)

@section('content')
<div class="show-user-page">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">
                <i class="fas fa-user-circle"></i>
                User Details
            </h1>
            <p class="page-subtitle">View complete user information</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit User
            </a>
        </div>
    </div>

    <!-- User Profile Card -->
    <div class="profile-card">
        <div class="profile-avatar">
            <div class="avatar-circle" style="background: {{ $user->role === 'admin' ? '#ef4444' : ($user->role === 'sales_agent' ? '#3b82f6' : ($user->role === 'sales_representative' ? '#8b5cf6' : ($user->role === 'staff' ? '#10b981' : '#f59e0b'))) }}">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="avatar-info">
                <h2>{{ $user->name }}</h2>
                @php
                    $roleLabels = [
                        'admin' => 'Administrator',
                        'sales_agent' => 'Sales Agent',
                        'sales_representative' => 'Sales Representative',
                        'staff' => 'Staff',
                        'procurement' => 'Procurement',
                        'customer' => 'Customer',
                    ];
                @endphp
                <span class="role-badge {{ $user->role }}">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</span>
                @if(!$user->is_active)
                <span class="status-badge inactive">Inactive</span>
                @else
                <span class="status-badge active">Active</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
        <!-- Account Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-id-card"></i> Account Information</h3>
            </div>
            <div class="card-body">
                <table class="info-table">
                    <tr>
                        <td class="info-label">Email</td>
                        <td class="info-value">{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Phone</td>
                        <td class="info-value">{{ $user->phone ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Employee ID</td>
                        <td class="info-value">{{ $user->employee_id ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Joined</td>
                        <td class="info-value">{{ $user->created_at->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Last Login</td>
                        <td class="info-value">{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('M d, Y h:i A') : 'Never' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Department Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-briefcase"></i> Employment Details</h3>
            </div>
            <div class="card-body">
                <table class="info-table">
                    <tr>
                        <td class="info-label">Department</td>
                        <td class="info-value">{{ $user->department ?? 'Not set' }}</td>
                    </tr>
                    @if(in_array($user->role, ['sales_agent', 'sales_representative']))
                    <tr>
                        <td class="info-label">Sales Target</td>
                        <td class="info-value">{{ $user->sales_target ? '₱ ' . number_format($user->sales_target, 2) : 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Commission Rate</td>
                        <td class="info-value">{{ $user->commission_rate ? $user->commission_rate . '%' : 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Hire Date</td>
                        <td class="info-value">{{ $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->format('M d, Y') : 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Supervisor</td>
                        <td class="info-value">{{ $user->supervisor ?? 'Not set' }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Sales Stats (for sales roles) -->
        @if(in_array($user->role, ['sales_agent', 'sales_representative']))
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Sales Overview</h3>
            </div>
            <div class="card-body">
                <div class="stats-mini-grid">
                    <div class="stat-mini">
                        <div class="stat-mini-value">{{ $salesCount }}</div>
                        <div class="stat-mini-label">Total Sales</div>
                    </div>
                    <div class="stat-mini" style="background: #eff6ff;">
                        <div class="stat-mini-value" style="color:#2563eb;">{{ $activeSalesCount }}</div>
                        <div class="stat-mini-label" style="color:#3b82f6;">Active Orders</div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('sales.prototype.list') }}?agent={{ $user->id }}" class="btn btn-sm btn-outline">
                        <i class="fas fa-external-link-alt"></i> View Sales
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.show-user-page { padding: 2rem; }

.profile-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e2e8f0;
}

.profile-avatar {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    font-weight: 700;
    flex-shrink: 0;
}

.avatar-info h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    color: #1e293b;
}

.role-badge, .status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-right: 0.5rem;
}

.role-badge.admin { background: #fee2e2; color: #dc2626; }
.role-badge.sales_agent { background: #dbeafe; color: #2563eb; }
.role-badge.sales_representative { background: #ede9fe; color: #7c3aed; }
.role-badge.staff { background: #d1fae5; color: #059669; }
.role-badge.procurement { background: #fef3c7; color: #d97706; }

.status-badge.active { background: #d1fae5; color: #059669; }
.status-badge.inactive { background: #fce4ec; color: #c62828; }

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}

@media (max-width: 768px) {
    .show-user-page { padding: 1rem; }
    .info-grid { grid-template-columns: 1fr; }
    .profile-card { padding: 1.25rem; }
    .profile-avatar { gap: 1rem; }
    .avatar-circle { width: 60px; height: 60px; font-size: 1.5rem; }
    .avatar-info h2 { font-size: 1.25rem; }
}

.info-table { width: 100%; border-collapse: collapse; }
.info-table tr { border-bottom: 1px solid #f1f5f9; }
.info-table tr:last-child { border-bottom: none; }
.info-table td { padding: 0.75rem 0; }
.info-label { font-size: 0.8rem; color: #64748b; width: 40%; font-weight: 500; }
.info-value { font-size: 0.875rem; color: #1e293b; font-weight: 600; }

.stats-mini-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.stat-mini { background: #f8fafc; border-radius: 10px; padding: 1rem; text-align: center; }
.stat-mini-value { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.stat-mini-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; margin-top: 0.25rem; }

.mt-3 { margin-top: 0.75rem; }
.text-center { text-align: center; }

.btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; }
.btn-primary { background: #3b82f6; color: white; }
.btn-primary:hover { background: #2563eb; }
.btn-outline { background: white; color: #475569; border: 1.5px solid #e2e8f0; }
.btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }
.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8rem; }

.card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
.card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0; }
.card-title { margin: 0; font-size: 0.875rem; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 0.5rem; }
.card-body { padding: 1.5rem; }
</style>
@endpush
@endsection
