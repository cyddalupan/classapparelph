<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List - CLASS Apparel PH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .category-box {
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 12px;
            border: 3px solid #dee2e6;
        }
        .category-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .product-list-container {
            max-height: 300px;
            overflow-y: auto;
        }
        .product-list-container::-webkit-scrollbar {
            width: 6px;
        }
        .product-list-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .product-list-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4"><i class="fas fa-tshirt"></i> Product List</h1>
        <p class="text-muted mb-4">Manage product and Inventory</p>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Products are categorized into 5 boxes matching Inventory Action. Items added in Inventory Action appear here automatically.
        </div>
        
        <!-- Five Category Boxes -->
        <div class="row g-4">
            <!-- Box 1: Shirt Products -->
            <div class="col-md-2 col-sm-6">
                <div class="category-box card h-100">
                    <div class="card-body text-center">
                        <div class="category-icon mb-3">
                            <i class="fas fa-tshirt fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title fw-bold">Shirt Products</h5>
                        <p class="card-text text-muted small">T-shirts, polos, dress shirts</p>
                        
                        <!-- Item Count -->
                        <div class="item-count mb-3">
                            <span class="badge bg-primary rounded-pill fs-6">
                                TEST: 5 items
                            </span>
                        </div>
                        
                        <!-- Product List -->
                        <div class="product-list-container">
                            <div class="text-center py-4">
                                <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No shirt products yet</p>
                                <small>Add items in Inventory Action</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box 2: Other Items -->
            <div class="col-md-2 col-sm-6">
                <div class="category-box card h-100">
                    <div class="card-body text-center">
                        <div class="category-icon mb-3">
                            <i class="fas fa-box fa-3x text-success"></i>
                        </div>
                        <h5 class="card-title fw-bold">Other Items</h5>
                        <p class="card-text text-muted small">Miscellaneous inventory items</p>
                        
                        <div class="item-count mb-3">
                            <span class="badge bg-success rounded-pill fs-6">
                                TEST: 3 items
                            </span>
                        </div>
                        
                        <div class="product-list-container">
                            <div class="text-center py-4">
                                <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No other items yet</p>
                                <small>Add items in Inventory Action</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box 3: Garment Materials -->
            <div class="col-md-2 col-sm-6">
                <div class="category-box card h-100">
                    <div class="card-body text-center">
                        <div class="category-icon mb-3">
                            <i class="fas fa-cut fa-3x" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title fw-bold">Garment Materials</h5>
                        <p class="card-text text-muted small">Fabric, thread, buttons, zippers</p>
                        
                        <div class="item-count mb-3">
                            <span class="badge rounded-pill fs-6" style="background-color: #6f42c1;">
                                TEST: 8 items
                            </span>
                        </div>
                        
                        <div class="product-list-container">
                            <div class="text-center py-4">
                                <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No garment materials yet</p>
                                <small>Add items in Inventory Action</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box 4: Printing and Office Supplies -->
            <div class="col-md-2 col-sm-6">
                <div class="category-box card h-100">
                    <div class="card-body text-center">
                        <div class="category-icon mb-3">
                            <i class="fas fa-print fa-3x text-warning"></i>
                        </div>
                        <h5 class="card-title fw-bold">Printing & Office</h5>
                        <p class="card-text text-muted small">Paper, ink, pens, office items</p>
                        
                        <div class="item-count mb-3">
                            <span class="badge bg-warning rounded-pill fs-6">
                                TEST: 12 items
                            </span>
                        </div>
                        
                        <div class="product-list-container">
                            <div class="text-center py-4">
                                <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No office supplies yet</p>
                                <small>Add items in Inventory Action</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box 5: Machine and Equipment -->
            <div class="col-md-2 col-sm-6">
                <div class="category-box card h-100">
                    <div class="card-body text-center">
                        <div class="category-icon mb-3">
                            <i class="fas fa-tools fa-3x text-danger"></i>
                        </div>
                        <h5 class="card-title fw-bold">Machine & Equipment</h5>
                        <p class="card-text text-muted small">Printers, sewing machines, tools</p>
                        
                        <div class="item-count mb-3">
                            <span class="badge bg-danger rounded-pill fs-6">
                                TEST: 2 items
                            </span>
                        </div>
                        
                        <div class="product-list-container">
                            <div class="text-center py-4">
                                <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No machines/equipment yet</p>
                                <small>Add items in Inventory Action</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Summary -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col">
                                <h3 class="text-primary">30</h3>
                                <p class="text-muted mb-0">Total Products</p>
                            </div>
                            <div class="col">
                                <h3 class="text-success">₱45,678.90</h3>
                                <p class="text-muted mb-0">Total Inventory Value</p>
                            </div>
                            <div class="col">
                                <h3 class="text-info">150</h3>
                                <p class="text-muted mb-0">Total Stock Units</p>
                            </div>
                            <div class="col">
                                <h3 class="text-warning">5</h3>
                                <p class="text-muted mb-0">Categories</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <p class="text-muted"><strong>TEST PAGE:</strong> This is a simple test without Laravel layout components.</p>
            <p class="text-muted">If you see 5 boxes here, then the problem is with the <code>&lt;x-app-layout&gt;</code> component.</p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>