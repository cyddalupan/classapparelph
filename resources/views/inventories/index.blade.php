<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inventory Management - Class Apparel PH</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            color: #333;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
        }
        
        .page-title {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .page-subtitle {
            opacity: 0.9;
            font-weight: 400;
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
            border-color: var(--primary-color);
        }
        
        .category-box.selected {
            border-color: var(--primary-color);
            background-color: rgba(58, 134, 255, 0.05);
        }
        
        .category-box .card-title {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .category-box .card-text {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .inventory-table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-top: 2rem;
            display: none;
        }
        
        .inventory-table-container.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .table th {
            font-weight: 600;
            color: var(--dark-color);
            border-top: none;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .badge-category {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .loading-spinner.active {
            display: block;
        }
        
        .no-data-message {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .action-buttons .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2a75ff;
            border-color: #2a75ff;
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
            color: #333;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
    </style>
</head>
<body>
    @extends('layouts.app')
    
    @section('content')
    <div class="page-header">
        <div class="container">
            <h1 class="page-title">Inventory Management</h1>
            <p class="page-subtitle">Browse and manage inventory by category</p>
        </div>
    </div>
    
    <div class="container">
        <!-- Category Selection Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-3">Select Category</h4>
                <div class="row g-3">
                    <!-- Shirt Products -->
                    <div class="col-md-4 col-lg-2-4">
                        <div class="category-box card border-3 mx-2" data-category="Shirt Products" id="box-shirt">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="mb-2">
                                    <i class="fas fa-tshirt fa-2x text-primary"></i>
                                </div>
                                <h6 class="card-title mb-1">Shirt Products</h6>
                                <p class="card-text text-muted small mb-0">T-shirts, polo, hoodies</p>
                                <div class="mt-2">
                                    <span class="badge bg-primary">24 items</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Uncategorized -->
                    <div class="col-md-4 col-lg-2-4">
                        <div class="category-box card border-3 mx-2" data-category="Uncategorized" id="box-uncategorized">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="mb-2">
                                    <i class="fas fa-question-circle fa-2x text-secondary"></i>
                                </div>
                                <h6 class="card-title mb-1">Uncategorized</h6>
                                <p class="card-text text-muted small mb-0">Items without category</p>
                                <div class="mt-2">
                                    <span class="badge bg-secondary">8 items</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Machine and Equipments -->
                    <div class="col-md-4 col-lg-2-4">
                        <div class="category-box card border-3 mx-2" data-category="Machine and Equipments" id="box-machine">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="mb-2">
                                    <i class="fas fa-tools fa-2x text-danger"></i>
                                </div>
                                <h6 class="card-title mb-1">Machines & Equipment</h6>
                                <p class="card-text text-muted small mb-0">Tools, machines, equipment</p>
                                <div class="mt-2">
                                    <span class="badge bg-danger">2 items</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Garment Materials (Coming Soon) -->
                    <div class="col-md-4 col-lg-2-4">
                        <div class="category-box card border-3 mx-2" data-category="Garment Materials" id="box-garment" style="opacity: 0.6;">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="mb-2">
                                    <i class="fas fa-cut fa-2x text-success"></i>
                                </div>
                                <h6 class="card-title mb-1">Garment Materials</h6>
                                <p class="card-text text-muted small mb-0">(Coming Soon)</p>
                                <div class="mt-2">
                                    <span class="badge bg-success">0 items</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Printing and Office Supplies (Coming Soon) -->
                    <div class="col-md-4 col-lg-2-4">
                        <div class="category-box card border-3 mx-2" data-category="Printing and Office Supplies" id="box-office" style="opacity: 0.6;">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="mb-2">
                                    <i class="fas fa-print fa-2x text-warning"></i>
                                </div>
                                <h6 class="card-title mb-1">Printing & Office</h6>
                                <p class="card-text text-muted small mb-0">(Coming Soon)</p>
                                <div class="mt-2">
                                    <span class="badge bg-warning">0 items</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Inventory Display Section -->
        <div class="inventory-table-container" id="inventory-table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 id="current-category-title">Inventory Items</h4>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="add-new-item-btn">
                        <i class="fas fa-plus me-1"></i> Add New Item
                    </button>
                    <button class="btn btn-success" id="refresh-items-btn">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                </div>
            </div>
            
            <!-- Loading Spinner -->
            <div class="loading-spinner" id="loading-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading inventory items...</p>
            </div>
            
            <!-- No Data Message -->
            <div class="no-data-message" id="no-data-message" style="display: none;">
                <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                <h5>No inventory items found</h5>
                <p class="text-muted">No items found in this category. Add some items to get started.</p>
            </div>
            
            <!-- Inventory Table -->
            <div class="table-responsive" id="inventory-table-wrapper" style="display: none;">
                <table class="table table-hover" id="inventory-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Unit Price</th>
                            <th>Current Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inventory-table-body">
                        <!-- Inventory items will be loaded here via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Inventory page loaded');
            
            // Check if we're in view-only mode (from URL parameter)
            const urlParams = new URLSearchParams(window.location.search);
            const isViewOnlyMode = urlParams.get('mode') === 'view';
            
            // Category selection - declare all variables first
            const categoryBoxes = document.querySelectorAll('.category-box');
            const inventoryContainer = document.getElementById('inventory-table-container');
            const currentCategoryTitle = document.getElementById('current-category-title');
            const loadingSpinner = document.getElementById('loading-spinner');
            const noDataMessage = document.getElementById('no-data-message');
            const tableWrapper = document.getElementById('inventory-table-wrapper');
            const tableBody = document.getElementById('inventory-table-body');
            const refreshBtn = document.getElementById('refresh-items-btn');
            const addNewItemBtn = document.getElementById('add-new-item-btn');
            
            if (isViewOnlyMode) {
                console.log('View-only mode enabled - edit/delete buttons will be hidden');
                // Update page title for view-only mode
                document.title = 'View Inventory - Class Apparel PH';
                const pageTitle = document.querySelector('.page-title');
                if (pageTitle) {
                    pageTitle.textContent = 'View Inventory (Read-Only)';
                }
                
                // Hide "Add New Item" button in view-only mode
                if (addNewItemBtn) {
                    addNewItemBtn.style.display = 'none';
                }
            }
            
            let currentCategory = null;
            
            // Category box click handler
            categoryBoxes.forEach(box => {
                box.addEventListener('click', function() {
                    // Remove selected class from all boxes
                    categoryBoxes.forEach(b => b.classList.remove('selected'));
                    
                    // Add selected class to clicked box
                    this.classList.add('selected');
                    
                    // Get category
                    const category = this.getAttribute('data-category');
                    currentCategory = category;
                    
                    // Update title
                    currentCategoryTitle.textContent = `${category} Inventory`;
                    
                    // Show inventory container
                    inventoryContainer.classList.add('active');
                    
                    // Load inventory items
                    loadInventoryItems(category);
                });
            });
            
            // Load inventory items function
            function loadInventoryItems(category) {
                // Show loading, hide table and no data message
                loadingSpinner.classList.add('active');
                tableWrapper.style.display = 'none';
                noDataMessage.style.display = 'none';
                
                // Clear existing table rows
                tableBody.innerHTML = '';
                
                // Fetch inventory items from API
                fetch(`/api/products-by-category?category=${encodeURIComponent(category)}`)
                    .then(response => {
                        // Check if response is JSON
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            // If not JSON, might be redirect to login page
                            throw new Error('Authentication required or server error. Please check if you are logged in.');
                        }
                        
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(items => {
                        console.log('Loaded inventory items:', items);
                        
                        // Hide loading spinner
                        loadingSpinner.classList.remove('active');
                        
                        if (items.length === 0) {
                            // Show no data message
                            noDataMessage.style.display = 'block';
                        } else {
                            // Populate table
                            items.forEach(item => {
                                const row = document.createElement('tr');
                                
                                // Determine status badge
                                let statusBadge = '';
                                if (item.is_active) {
                                    statusBadge = '<span class="badge bg-success">Active</span>';
                                } else {
                                    statusBadge = '<span class="badge bg-secondary">Inactive</span>';
                                }
                                
                                // Format price
                                const formattedPrice = new Intl.NumberFormat('en-PH', {
                                    style: 'currency',
                                    currency: 'PHP'
                                }).format(item.unit_price || 0);
                                
                                // Format stock
                                const formattedStock = item.current_stock || 0;
                                
                                // Check if we should show action buttons
                                const showActionButtons = !isViewOnlyMode;
                                
                                row.innerHTML = `
                                    <td><strong>${item.sku || 'N/A'}</strong></td>
                                    <td>${item.name || 'Unnamed Item'}</td>
                                    <td><span class="badge-category badge">${item.category || 'Uncategorized'}</span></td>
                                    <td>${item.type || 'N/A'}</td>
                                    <td>${formattedPrice}</td>
                                    <td><span class="badge ${formattedStock > 0 ? 'bg-success' : 'bg-danger'}">${formattedStock}</span></td>
                                    <td>${statusBadge}</td>
                                    <td>
                                        ${showActionButtons ? `
                                        <button class="btn btn-sm btn-warning edit-item-btn" data-id="${item.id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-item-btn" data-id="${item.id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        ` : `
                                        <span class="text-muted small">View only</span>
                                        `}
                                    </td>
                                `;
                                
                                tableBody.appendChild(row);
                            });
                            
                            // Show table
                            tableWrapper.style.display = 'block';
                            
                            // Add event listeners to action buttons
                            document.querySelectorAll('.edit-item-btn').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    const itemId = this.getAttribute('data-id');
                                    alert(`Edit item ${itemId} - To be implemented`);
                                });
                            });
                            
                            document.querySelectorAll('.delete-item-btn').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    const itemId = this.getAttribute('data-id');
                                    if (confirm(`Are you sure you want to delete item ${itemId}?`)) {
                                        alert(`Delete item ${itemId} - To be implemented`);
                                    }
                                });
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error loading inventory items:', error);
                        loadingSpinner.classList.remove('active');
                        
                        let errorMessage = `Failed to load inventory items: ${error.message}`;
                        if (error.message.includes('Authentication required')) {
                            errorMessage = 'Authentication required. Please <a href="/login" class="alert-link">log in</a> to view inventory.';
                        }
                        
                        noDataMessage.innerHTML = `
                            <i class="fas fa-exclamation-triangle fa-3x mb-3 text-danger"></i>
                            <h5>Error Loading Items</h5>
                            <p class="text-muted">${errorMessage}</p>
                        `;
                        noDataMessage.style.display = 'block';
                    });
            }
            
            // Refresh button click handler
            refreshBtn.addEventListener('click', function() {
                if (currentCategory) {
                    loadInventoryItems(currentCategory);
                } else {
                    alert('Please select a category first');
                }
            });
            
            // Add new item button click handler
            addNewItemBtn.addEventListener('click', function() {
                if (currentCategory) {
                    // Redirect to inventory action page with category pre-selected
                    window.location.href = `/inventoryaction?category=${encodeURIComponent(currentCategory)}`;
                } else {
                    alert('Please select a category first');
                }
            });
            
            // Check if URL has category parameter (using existing urlParams variable)
            const urlCategory = urlParams.get('category');
            if (urlCategory) {
                // Find and click the corresponding category box
                const targetBox = document.querySelector(`.category-box[data-category="${urlCategory}"]`);
                if (targetBox) {
                    targetBox.click();
                }
            }
        });
    </script>
    @endsection
</body>
</html>