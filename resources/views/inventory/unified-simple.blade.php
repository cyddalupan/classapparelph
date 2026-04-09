@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-boxes me-2"></i>
                        Unified Inventory Management
                    </h4>
                    <p class="text-muted mb-0">All inventory operations in one place</p>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>System Under Construction</strong> - The unified inventory system is being built. 
                        Basic structure is ready. Full functionality coming soon.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-list-alt fa-3x text-success mb-3"></i>
                                    <h5>Master List</h5>
                                    <p class="text-muted">Central product catalog</p>
                                    <a href="/master-items" class="btn btn-outline-success">Go to Master List</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-eye fa-3x text-primary mb-3"></i>
                                    <h5>View Items</h5>
                                    <p class="text-muted">Browse inventory (read-only)</p>
                                    <a href="/inventories?mode=view" class="btn btn-outline-primary">View Only</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-cogs fa-3x text-warning mb-3"></i>
                                    <h5>Manage Items</h5>
                                    <p class="text-muted">View, edit, add, or delete items</p>
                                    <a href="/inventories" class="btn btn-outline-warning">Go to Manage</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-bar fa-3x text-info mb-3"></i>
                                    <h5>Reports</h5>
                                    <p class="text-muted">Analytics & insights</p>
                                    <button class="btn btn-outline-info" disabled>Coming Soon</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-rocket me-2"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Inventory Categories</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Shirt Products
                                            <span class="badge bg-primary rounded-pill">22 items</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Pants Products
                                            <span class="badge bg-primary rounded-pill">0 items</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Shoes Products
                                            <span class="badge bg-primary rounded-pill">0 items</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Accessories Products
                                            <span class="badge bg-primary rounded-pill">0 items</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Others Products
                                            <span class="badge bg-primary rounded-pill">15 items</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>System Status</h6>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>Database Connected</strong>
                                        <p class="mb-0 small">Inventory system is operational</p>
                                    </div>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>Master Catalog Active</strong>
                                        <p class="mb-0 small">3 products in master list</p>
                                    </div>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>Simplified System</strong>
                                        <p class="mb-0 small">Streamlined: View & Manage (includes adding)</p>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="fas fa-bell me-2"></i>
                                        <strong>Notifications</strong>
                                        <p class="mb-0 small">Email notifications enabled</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <p class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Last updated: {{ now()->format('F j, Y g:i A') }}
                        </p>
                        <div class="btn-group">
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-1"></i> Refresh
                            </button>
                            <a href="/admin/dashboard" class="btn btn-outline-secondary">
                                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                            </a>
                            <a href="/inventories" class="btn btn-outline-secondary">
                                <i class="fas fa-list-alt me-1"></i> Full Inventory List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Unified Inventory Dashboard loaded');
    
    // Simple refresh functionality
    document.querySelectorAll('.btn-outline-primary, .btn-outline-success, .btn-outline-warning').forEach(btn => {
        btn.addEventListener('click', function(e) {
            console.log('Navigating to:', this.href);
        });
    });
});
</script>

<style>
.card {
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.list-group-item {
    transition: background-color 0.2s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>
@endsection