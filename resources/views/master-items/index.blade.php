@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary-color: #3a86ff;
        --secondary-color: #8338ec;
        --success-color: #06d6a0;
        --warning-color: #ffd166;
        --danger-color: #ef476f;
        --light-color: #f8f9fa;
        --dark-color: #212529;
        --border-color: #dee2e6;
    }
    
    .page-header {
        background: white;
        color: var(--dark-color);
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 15px 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-bottom: 4px solid var(--primary-color);
    }
    
    .category-box {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 3px solid transparent;
        height: 100%;
        min-height: 140px;
    }
    
    .category-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-color: #3a86ff;
    }
    
    .category-box.selected {
        border-color: #3a86ff;
        background-color: rgba(58, 134, 255, 0.05);
    }
    
    .category-box .card-title {
        font-weight: 600;
        color: #212529;
        text-align: center !important;
    }
    
    .category-box .card-text {
        font-size: 0.85rem;
        color: #6c757d;
        text-align: center;
    }
    
    .category-box .badge-container {
        text-align: center;
    }
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="page-header">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="page-title">Master Product List</h1>
                            <p class="page-subtitle">Centralized product management for all shops</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-inline-block bg-white text-dark rounded-pill px-3 py-1 mb-2">
                                <i class="fas fa-database me-1 text-primary"></i>
                                <strong>{{ $totalActiveItems }}</strong> active products
                            </div>
                            <div class="d-inline-block bg-white text-dark rounded-pill px-3 py-1 ms-2 mb-2">
                                <i class="fas fa-archive me-1 text-secondary"></i>
                                <strong>{{ $totalItemsIncludingDeleted }}</strong> total in catalog
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Category Selection Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-3">Select Category</h4>
                <div class="row g-3">
                    <!-- Shirt Products -->
                    <div class="col-md-4 col-lg-2-4">
                        <div class="category-box card border-3 mx-2" data-category="Shirt Products" id="box-shirt">
                            <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                                <div class="mb-2">
                                    <i class="fas fa-tshirt fa-2x text-primary"></i>
                                </div>
                                <h6 class="card-title mb-1 text-center">Shirt Products</h6>
                                <p class="card-text text-muted small mb-0">T-shirts, polo, hoodies</p>
                                <div class="mt-2 badge-container">
                                    <span class="badge bg-primary">{{ $categoryCounts['shirt'] }} products</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Other Products -->
                    <div class="col-md-4 col-lg-2-4">
                        <div class="category-box card border-3 mx-2" data-category="Other Products" id="box-uncategorized">
                            <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                                <div class="mb-2">
                                    <i class="fas fa-gift fa-2x text-info"></i>
                                </div>
                                <h6 class="card-title mb-1 text-center">Other Products</h6>
                                <p class="card-text text-muted small mb-0">Mugs, totebags, lanyards, etc.</p>
                                <div class="mt-2 badge-container">
                                    <span class="badge bg-info">{{ $categoryCounts['uncategorized'] }} products</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Machine and Equipments -->
                    <div class="col-md-4 col-lg-2-4">
                        <div class="category-box card border-3 mx-2" data-category="Machine and Equipments" id="box-machine">
                            <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                                <div class="mb-2">
                                    <i class="fas fa-tools fa-2x text-danger"></i>
                                </div>
                                <h6 class="card-title mb-1 text-center">Machines & Equipment</h6>
                                <p class="card-text text-muted small mb-0">Tools, machines, equipment</p>
                                <div class="mt-2 badge-container">
                                    <span class="badge bg-danger">{{ $categoryCounts['machines'] }} products</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Garment Materials -->
                    <div class="col-md-4 col-lg-2-4">
                        <div class="category-box card border-3 mx-2" data-category="Garment Materials" id="box-garment">
                            <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                                <div class="mb-2">
                                    <i class="fas fa-cut fa-2x text-success"></i>
                                </div>
                                <h6 class="card-title mb-1 text-center">Garment Materials</h6>
                                <p class="card-text text-muted small mb-0">Fabrics, threads, accessories</p>
                                <div class="mt-2 badge-container">
                                    <span class="badge bg-success">{{ $categoryCounts['materials'] }} products</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Printing and Office Supplies -->
                    <div class="col-md-4 col-lg-2-4">
                        <div class="category-box card border-3 mx-2" data-category="Printing and Office Supplies" id="box-office">
                            <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                                <div class="mb-2">
                                    <i class="fas fa-print fa-2x text-warning"></i>
                                </div>
                                <h6 class="card-title mb-1 text-center">Printing & Office</h6>
                                <p class="card-text text-muted small mb-0">Ink, paper, office supplies</p>
                                <div class="mt-2 badge-container">
                                    <span class="badge bg-warning">{{ $categoryCounts['printing'] }} products</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Master Items Display Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Master Products</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <a href="{{ route('master-items.create') }}?category={{ urlencode($selectedCategory) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> 
                            @php
                                // Map categories to specific button text
                                $buttonText = [
                                    'Shirt Products' => 'Add New Shirt',
                                    'Other Products' => 'Add New Other Product',
                                    'Machine and Equipments' => 'Add New Machine/Equipment',
                                    'Garment Materials' => 'Add New Material',
                                    'Printing and Office Supplies' => 'Add New Printing Supply'
                                ];
                                echo $buttonText[$selectedCategory] ?? 'Add New Product';
                            @endphp
                        </a>
                        <span class="ms-2 text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Will create product in <strong>{{ $selectedCategory }}</strong> category
                        </span>
                    </div>
                </div>
                
                @if(count($masterItems) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($masterItems as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>
                                        <strong>{{ $item->name }}</strong>
                                        <div class="small text-muted">{{ $item->category }}</div>
                                    </td>
                                    <td>
                                        @if($item->sku)
                                            <span class="badge bg-info">{{ $item->sku }}</span>
                                        @else
                                            <span class="text-muted">No SKU</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->description)
                                            <div class="small">{{ Str::limit($item->description, 50) }}</div>
                                        @else
                                            <span class="text-muted">No description</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->unit_price)
                                            <strong>₱{{ number_format($item->unit_price, 2) }}</strong>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('master-items.edit', $item->id) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('master-items.destroy', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Delete this product?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <div class="mt-3 text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Showing {{ count($masterItems) }} products in <strong>{{ $selectedCategory }}</strong> category
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5>No Products in {{ $selectedCategory }}</h5>
                        <p class="text-muted">No products found in this category yet.</p>
                        <a href="{{ route('master-items.create') }}?category={{ urlencode($selectedCategory) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> 
                            @php
                                // Map categories to specific button text
                                $buttonText = [
                                    'Shirt Products' => 'Add First Shirt',
                                    'Other Products' => 'Add First Other Product',
                                    'Machine and Equipments' => 'Add First Machine/Equipment',
                                    'Garment Materials' => 'Add First Material',
                                    'Printing and Office Supplies' => 'Add First Printing Supply'
                                ];
                                echo $buttonText[$selectedCategory] ?? 'Add First Product';
                            @endphp
                        </a>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="mt-4">
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i> About Master Products</h6>
                <p class="mb-0">
                    <strong>Master Products</strong> define WHAT items exist in your catalog. Shops will select from this list 
                    when adding items to their inventory. No stock quantities are managed here - only product definitions.
                    This is Phase 1 of the system. Phase 2 will connect this catalog to the shop inventory system.
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Category box click events
            const categoryBoxes = document.querySelectorAll('.category-box');
            
            categoryBoxes.forEach(box => {
                box.addEventListener('click', function() {
                    // Remove selected class from all boxes
                    categoryBoxes.forEach(b => b.classList.remove('selected'));
                    
                    // Add selected class to clicked box
                    this.classList.add('selected');
                    
                    // Get the category
                    const category = this.getAttribute('data-category');
                    
                    // Redirect to same page with category filter
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('category', category);
                    window.location.href = currentUrl.toString();
                });
            });
            
            // Auto-select the box for current category
            const currentCategory = "{{ $selectedCategory }}";
            const categoryMap = {
                'Shirt Products': 'box-shirt',
                'Other Products': 'box-uncategorized',
                'Machine and Equipments': 'box-machine',
                'Garment Materials': 'box-garment',
                'Printing and Office Supplies': 'box-office'
            };
            
            if (categoryMap[currentCategory]) {
                document.getElementById(categoryMap[currentCategory]).classList.add('selected');
            } else {
                // Default to first category
                document.getElementById('box-shirt').classList.add('selected');
            }
        });
    </script>

@endsection
