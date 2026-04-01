<x-app-layout>
    @section('page-title', 'Inventory Action')
    
    {{-- Cache control headers to prevent browser caching issues --}}
    @php
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
    @endphp
    
    <x-slot name="header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-boxes"></i>
                Inventory Action
            </h1>
            <p class="page-subtitle">Choose a category for your new inventory item</p>
        </div>
    </x-slot>

    <div class="page-content">
        <!-- Action buttons in upper right corner -->
        <div class="page-actions">
            <a href="{{ route('inventory.select-category') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Item
            </a>
        </div>
        
        <!-- Main content area -->
        <div class="main-content">
            <!-- Category Selection Section -->
            <div class="category-selection-section">
                <div class="row justify-content-center">
                    <!-- Shirt Products Card -->
                    <div class="col-md-5 col-lg-3 mb-4">
                        <div class="category-card card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="category-icon mb-3">
                                    <i class="fas fa-tshirt fa-3x text-primary"></i>
                                </div>
                                <h5 class="category-title mb-2">Shirt Products</h5>
                                <p class="category-description text-muted small">
                                    T-shirts, polo shirts, hoodies, and other apparel items
                                </p>
                                <div class="category-badge mt-3">
                                    <span class="badge bg-primary">Apparel</span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 text-center">
                                <a href="{{ route('inventory.create') }}?category=shirt" class="btn btn-outline-primary btn-sm">
                                    Select <i class="fas fa-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Other Items Card -->
                    <div class="col-md-5 col-lg-3 mb-4">
                        <div class="category-card card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="category-icon mb-3">
                                    <i class="fas fa-box fa-3x text-success"></i>
                                </div>
                                <h5 class="category-title mb-2">Other Items</h5>
                                <p class="category-description text-muted small">
                                    Miscellaneous inventory items and general products
                                </p>
                                <div class="category-badge mt-3">
                                    <span class="badge bg-success">General</span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 text-center">
                                <a href="{{ route('inventory.create') }}?category=other" class="btn btn-outline-success btn-sm">
                                    Select <i class="fas fa-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Office Supplies Card -->
                    <div class="col-md-5 col-lg-3 mb-4">
                        <div class="category-card card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="category-icon mb-3">
                                    <i class="fas fa-clipboard-list fa-3x text-warning"></i>
                                </div>
                                <h5 class="category-title mb-2">Office Supplies</h5>
                                <p class="category-description text-muted small">
                                    Paper, pens, folders, and other office materials
                                </p>
                                <div class="category-badge mt-3">
                                    <span class="badge bg-warning">Office</span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 text-center">
                                <a href="{{ route('inventory.create') }}?category=office" class="btn btn-outline-warning btn-sm">
                                    Select <i class="fas fa-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Machine and Equipment Card -->
                    <div class="col-md-5 col-lg-3 mb-4">
                        <div class="category-card card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="category-icon mb-3">
                                    <i class="fas fa-tools fa-3x text-danger"></i>
                                </div>
                                <h5 class="category-title mb-2">Machine and Equipment</h5>
                                <p class="category-description text-muted small">
                                    Printers, computers, tools, and other equipment
                                </p>
                                <div class="category-badge mt-3">
                                    <span class="badge bg-danger">Equipment</span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 text-center">
                                <a href="{{ route('inventory.create') }}?category=machine" class="btn btn-outline-danger btn-sm">
                                    Select <i class="fas fa-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .category-card {
            transition: all 0.3s ease;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        
        .category-icon {
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .category-title {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .category-description {
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .page-actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 2rem;
        }
    </style>
</x-app-layout>