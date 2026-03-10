@extends('layouts.app')

@section('page-title', 'Sales Agents Management')

@section('content')
<div class="admin-users-page">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">
                <i class="fas fa-users"></i>
                Sales Agents Management
            </h1>
            <p class="page-subtitle">Manage sales agents and representatives</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('sales-agents.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Sales Agent
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #3b82f6;">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $salesAgents->where('role', 'sales_agent')->count() }}</div>
                <div class="stat-label">Sales Agents</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #8b5cf6;">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $salesAgents->where('role', 'sales_representative')->count() }}</div>
                <div class="stat-label">Sales Representatives</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #10b981;">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $salesAgents->where('is_active', true)->count() }}</div>
                <div class="stat-label">Active Agents</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #f59e0b;">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">₱ {{ number_format($salesAgents->sum('sales_target'), 2) }}</div>
                <div class="stat-label">Total Sales Target</div>
            </div>
        </div>
    </div>

    <!-- Sales Agents Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                Sales Agents List
            </h3>
            <div class="card-actions">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchAgents" placeholder="Search agents...">
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table" id="salesAgentsTable">
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
                        @forelse($salesAgents as $agent)
                        <tr>
                            <td>
                                <div class="employee-id">
                                    <span class="badge badge-secondary">{{ $agent->employee_id }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar-small">
                                        {{ strtoupper(substr($agent->name, 0, 1)) }}
                                    </div>
                                    <div class="user-details">
                                        <div class="user-name">{{ $agent->name }}</div>
                                        @if($agent->supervisor)
                                        <div class="user-supervisor">
                                            <small>Supervisor: {{ $agent->supervisor }}</small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $agent->email }}</td>
                            <td>
                                @if($agent->role === 'sales_agent')
                                <span class="badge badge-primary">Sales Agent</span>
                                @else
                                <span class="badge badge-purple">Sales Representative</span>
                                @endif
                            </td>
                            <td>{{ $agent->department ?? 'N/A' }}</td>
                            <td>
                                @if($agent->sales_target)
                                <span class="amount">₱ {{ number_format($agent->sales_target, 2) }}</span>
                                @else
                                <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            <td>
                                @if($agent->commission_rate)
                                <span class="badge badge-success">{{ $agent->commission_rate }}%</span>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($agent->is_active)
                                <span class="badge badge-success">Active</span>
                                @else
                                <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('sales-agents.show', $agent) }}" class="btn btn-icon btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('sales-agents.edit', $agent) }}" class="btn btn-icon btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('sales-agents.destroy', $agent) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this sales agent?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h4>No sales agents found</h4>
                                    <p class="text-muted">Add your first sales agent to get started.</p>
                                    <a href="{{ route('sales-agents.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Sales Agent
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.admin-users-page {
    padding: 2rem;
}

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
}

.user-details {
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: #1e293b;
}

.user-supervisor {
    font-size: 0.75rem;
    color: #64748b;
}

.badge-purple {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
}

.employee-id .badge {
    font-family: 'Monaco', 'Courier New', monospace;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.amount {
    font-weight: 600;
    color: #1e293b;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-state i {
    opacity: 0.5;
}

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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchAgents');
    const table = document.getElementById('salesAgentsTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const text = row.textContent.toLowerCase();
            
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
});
</script>
@endsection