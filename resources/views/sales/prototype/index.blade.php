@extends('layouts.app')

@section('title', 'Prototype Sales System')

@push('styles')
<style>
    .prototype-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 4rem 2rem;
        border-radius: 15px;
        margin-bottom: 3rem;
        text-align: center;
    }
    
    .feature-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
    }
    
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .feature-icon {
        font-size: 3rem;
        margin-bottom: 1.5rem;
        color: #667eea;
    }
    
    .department-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: bold;
        margin: 0.25rem;
    }
    
    .department-iprint { background: #4CAF50; color: white; }
    .department-consol { background: #2196F3; color: white; }
    .department-cinco { background: #FF9800; color: white; }
    .department-class { background: #9C27B0; color: white; }
    .department-mto { background: #F44336; color: white; }
    .department-other { background: #607D8B; color: white; }
    
    .cta-button {
        padding: 1rem 2rem;
        font-size: 1.2rem;
        border-radius: 50px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        font-weight: bold;
        transition: all 0.3s;
    }
    
    .cta-button:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <!-- Hero Section -->
    <div class="prototype-hero">
        <h1 class="display-4 mb-3">🚀 Prototype Sales System</h1>
        <p class="lead mb-4">A comprehensive sales system with multi-department workflow, KANBAN boards, payment verification, and customer management.</p>
        <a href="{{ route('sales.prototype.create') }}" class="btn cta-button">
            <i class="fas fa-plus-circle me-2"></i> Create New Sale
        </a>
        <a href="{{ route('sales.prototype.kanban') }}" class="btn btn-light ms-3">
            <i class="fas fa-columns me-2"></i> View KANBAN Board
        </a>
    </div>
    
    <!-- Features Grid -->
    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <div class="card feature-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <h4 class="card-title">Multi-Department Workflow</h4>
                    <p class="card-text">Sales automatically routed to appropriate departments:</p>
                    <div class="mt-3">
                        <span class="department-badge department-iprint">iPrint</span>
                        <span class="department-badge department-consol">Consol</span>
                        <span class="department-badge department-cinco">Cinco</span>
                        <span class="department-badge department-class">Class</span>
                        <span class="department-badge department-mto">Made to Order</span>
                        <span class="department-badge department-other">Other</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card feature-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="card-title">Customer Management</h4>
                    <p class="card-text">Shared company database + individual agent customer lists. Track customer history and preferences.</p>
                    <ul class="list-unstyled text-start mt-3">
                        <li><i class="fas fa-check text-success me-2"></i> Company-wide customer database</li>
                        <li><i class="fas fa-check text-success me-2"></i> Agent-specific customer lists</li>
                        <li><i class="fas fa-check text-success me-2"></i> Sales history tracking</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card feature-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="card-title">Payment Verification</h4>
                    <p class="card-text">Secure payment verification with screenshot upload and owner assignment.</p>
                    <ul class="list-unstyled text-start mt-3">
                        <li><i class="fas fa-check text-success me-2"></i> Screenshot upload</li>
                        <li><i class="fas fa-check text-success me-2"></i> Payment owner assignment</li>
                        <li><i class="fas fa-check text-success me-2"></i> Verification workflow</li>
                        <li><i class="fas fa-check text-success me-2"></i> Audit trail</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- More Features -->
    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <div class="card feature-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon">
                        <i class="fas fa-columns"></i>
                    </div>
                    <h4 class="card-title">KANBAN Boards</h4>
                    <p class="card-text">Visual workflow management for each department with drag-and-drop functionality.</p>
                    <ul class="list-unstyled text-start mt-3">
                        <li><i class="fas fa-check text-success me-2"></i> Department-specific boards</li>
                        <li><i class="fas fa-check text-success me-2"></i> Drag-and-drop status updates</li>
                        <li><i class="fas fa-check text-success me-2"></i> Image attachments</li>
                        <li><i class="fas fa-check text-success me-2"></i> Real-time updates</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card feature-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <h4 class="card-title">Mockup & Image Management</h4>
                    <p class="card-text">Upload and manage mockup images, reference photos, and design files.</p>
                    <ul class="list-unstyled text-start mt-3">
                        <li><i class="fas fa-check text-success me-2"></i> Multiple image upload</li>
                        <li><i class="fas fa-check text-success me-2"></i> Organized galleries</li>
                        <li><i class="fas fa-check text-success me-2"></i> KANBAN visibility</li>
                        <li><i class="fas fa-check text-success me-2"></i> Version control</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card feature-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4 class="card-title">Analytics & Reporting</h4>
                    <p class="card-text">Comprehensive sales analytics, department performance, and customer insights.</p>
                    <ul class="list-unstyled text-start mt-3">
                        <li><i class="fas fa-check text-success me-2"></i> Sales performance</li>
                        <li><i class="fas fa-check text-success me-2"></i> Department efficiency</li>
                        <li><i class="fas fa-check text-success me-2"></i> Customer analytics</li>
                        <li><i class="fas fa-check text-success me-2"></i> Revenue tracking</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header bg-white">
            <h4 class="mb-0">Quick Actions</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <a href="{{ route('sales.prototype.create') }}" class="btn btn-primary w-100">
                        <i class="fas fa-plus-circle me-2"></i> New Sale
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="{{ route('sales.prototype.kanban') }}" class="btn btn-info w-100">
                        <i class="fas fa-columns me-2"></i> KANBAN Board
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="#" class="btn btn-success w-100">
                        <i class="fas fa-users me-2"></i> Customers
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="#" class="btn btn-warning w-100">
                        <i class="fas fa-chart-bar me-2"></i> Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status -->
    <div class="card mt-4">
        <div class="card-header bg-white">
            <h4 class="mb-0">System Status</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Database Structure</h5>
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 90%;" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100">
                            90% Complete
                        </div>
                    </div>
                    <p class="text-muted">Tables created: Sales, Departments, KANBAN items, Customer relationships</p>
                </div>
                <div class="col-md-6">
                    <h5>Features Implementation</h5>
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 70%;" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                            70% Complete
                        </div>
                    </div>
                    <p class="text-muted">Core features: ✅ Multi-department, ✅ Customer DB, ⏳ Payment verification, ⏳ KANBAN</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Simple animation for feature cards
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.feature-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.5s, transform 0.5s';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>
@endpush