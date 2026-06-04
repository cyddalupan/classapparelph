@extends('layouts.app')

@section('page-title', 'User Management')

@section('content')
<div class="admin-users-page">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">
                <i class="fas fa-users-cog"></i>
                User Management
            </h1>
            <p class="page-subtitle">Manage all users, roles, and permissions</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add User
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card {{ $roleFilter === 'all' ? 'active' : '' }}" onclick="filterRole('all')" style="cursor:pointer;">
            <div class="stat-icon" style="background: #6366f1;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $roleCounts['all'] }}</div>
                <div class="stat-label">All Users</div>
            </div>
        </div>
        <div class="stat-card {{ $roleFilter === 'admin' ? 'active' : '' }}" onclick="filterRole('admin')" style="cursor:pointer;">
            <div class="stat-icon" style="background: #ef4444;">
                <i class="fas fa-crown"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $roleCounts['admin'] }}</div>
                <div class="stat-label">Admins</div>
            </div>
        </div>
        <div class="stat-card {{ $roleFilter === 'sales_agent' ? 'active' : '' }}" onclick="filterRole('sales_agent')" style="cursor:pointer;">
            <div class="stat-icon" style="background: #3b82f6;">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $roleCounts['sales_agent'] }}</div>
                <div class="stat-label">Sales Agents</div>
            </div>
        </div>
        <div class="stat-card {{ $roleFilter === 'sales_representative' ? 'active' : '' }}" onclick="filterRole('sales_representative')" style="cursor:pointer;">
            <div class="stat-icon" style="background: #8b5cf6;">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $roleCounts['sales_representative'] }}</div>
                <div class="stat-label">Sales Reps</div>
            </div>
        </div>
        <div class="stat-card {{ $roleFilter === 'staff' ? 'active' : '' }}" onclick="filterRole('staff')" style="cursor:pointer;">
            <div class="stat-icon" style="background: #10b981;">
                <i class="fas fa-user-md"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $roleCounts['staff'] }}</div>
                <div class="stat-label">Staff</div>
            </div>
        </div>
        <div class="stat-card {{ $roleFilter === 'procurement' ? 'active' : '' }}" onclick="filterRole('procurement')" style="cursor:pointer;">
            <div class="stat-icon" style="background: #f59e0b;">
                <i class="fas fa-truck"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $roleCounts['procurement'] }}</div>
                <div class="stat-label">Procurement</div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                Users List
            </h3>
            <div class="card-actions">
                <form method="GET" action="{{ route('admin.users.index') }}" class="search-form">
                    @if($roleFilter !== 'all')
                    <input type="hidden" name="role" value="{{ $roleFilter }}">
                    @endif
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search users..." value="{{ $search }}">
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Sales Target</th>
                            <th>Commission</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                        <tr class="{{ $u->id === auth()->id() ? 'current-user-row' : '' }}">
                            <td>
                                <div class="employee-id">
                                    <span class="badge badge-secondary">{{ $u->employee_id ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar-small">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div class="user-details">
                                        <div class="user-name">
                                            {{ $u->name }}
                                            @if($u->id === auth()->id())
                                            <span class="badge badge-info badge-sm">You</span>
                                            @endif
                                        </div>
                                        @if($u->supervisor)
                                        <div class="user-supervisor">
                                            <small>Supervisor: {{ $u->supervisor }}</small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $u->email }}</td>
                            <td>
                                @php
                                    $roleStyles = [
                                        'admin' => 'badge-danger',
                                        'sales_agent' => 'badge-primary',
                                        'sales_representative' => 'badge-purple',
                                        'staff' => 'badge-success',
                                        'procurement' => 'badge-warning',
                                        'customer' => 'badge-secondary',
                                    ];
                                    $roleLabels = [
                                        'admin' => 'Admin',
                                        'sales_agent' => 'Sales Agent',
                                        'sales_representative' => 'Sales Rep',
                                        'staff' => 'Staff',
                                        'procurement' => 'Procurement',
                                        'customer' => 'Customer',
                                    ];
                                @endphp
                                <span class="badge {{ $roleStyles[$u->role] ?? 'badge-secondary' }}">
                                    {{ $roleLabels[$u->role] ?? ucfirst($u->role) }}
                                </span>
                            </td>
                            <td>{{ $u->department ?? 'N/A' }}</td>
                            <td>
                                @if($u->sales_target)
                                <span class="amount">₱ {{ number_format($u->sales_target, 2) }}</span>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($u->commission_rate)
                                <span class="badge badge-success">{{ $u->commission_rate }}%</span>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($u->is_active)
                                <span class="badge badge-success">Active</span>
                                @else
                                <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.users.show', $u) }}" class="btn btn-icon btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($u->id !== auth()->id() || !$u->isAdmin())
                                    <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-icon btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.users.toggle-active', $u) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-icon {{ $u->is_active ? 'btn-secondary' : 'btn-success' }}" 
                                                title="{{ $u->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas {{ $u->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                        </button>
                                    </form>
                                    @if($u->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Delete user {{ $u->name }}? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h4>No users found</h4>
                                    <p class="text-muted">No users match your filter.</p>
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add User
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                {{ $users->appends(['role' => $roleFilter, 'search' => $search])->links() }}
            </div>
        </div>
    </div>
</div>

<style>
.admin-users-page {
    padding: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 2px solid #e2e8f0;
    transition: all 0.2s;
}

.stat-card:hover, .stat-card.active {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.stat-card.active {
    background: #eff6ff;
}

.stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.stat-icon i {
    font-size: 1.25rem;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.7rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.alert {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}
.alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.alert-danger { background: #fce4ec; color: #c62828; border: 1px solid #f8bbd0; }

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar-small {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.user-details { flex: 1; min-width: 0; }
.user-name { font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-supervisor { font-size: 0.75rem; color: #64748b; }

.current-user-row {
    background: #f0f9ff;
}

.badge-purple {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
}
.badge-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.badge-sm {
    font-size: 0.625rem;
    padding: 1px 6px;
    margin-left: 4px;
}

.action-buttons {
    display: flex;
    gap: 0.35rem;
    flex-wrap: nowrap;
}

.btn-icon {
    width: 30px;
    height: 30px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 0.75rem;
    transition: all 0.2s;
}

.btn-icon:hover { transform: translateY(-1px); }

.btn-info { background: #e0f2fe; color: #0369a1; }
.btn-warning { background: #fef3c7; color: #b45309; }
.btn-danger { background: #fce4ec; color: #c62828; }
.btn-success { background: #dcfce7; color: #166534; }
.btn-secondary { background: #f1f5f9; color: #475569; }

.employee-id .badge {
    font-family: 'Monaco', 'Courier New', monospace;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.amount { font-weight: 600; color: #1e293b; }

.search-form { display: flex; }
.search-box {
    position: relative;
    width: 250px;
}
.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
}
.search-box input {
    width: 100%;
    padding: 0.5rem 1rem 0.5rem 2.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all 0.2s;
}
.search-box input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.empty-state { text-align: center; padding: 3rem 1rem; }

.pagination-wrapper {
    margin-top: 1rem;
    display: flex;
    justify-content: center;
}

@media (max-width: 768px) {
    .admin-users-page { padding: 1rem; }
    .stats-grid { grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
    .stat-card { padding: 0.75rem; }
    .stat-icon { width: 36px; height: 36px; }
    .stat-icon i { font-size: 1rem; }
    .stat-value { font-size: 1rem; }
    .search-box { width: 180px; }
}
</style>

<script>
function filterRole(role) {
    const url = new URL(window.location.href);
    url.searchParams.set('role', role);
    url.searchParams.delete('search');
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit search on Enter
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });
    }
});
</script>
@endsection
