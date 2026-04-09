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
        
        /* Filter Styles */
        .inventory-filters .card {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 1px solid rgba(0, 0, 0, 0.08);
        }
        
        .inventory-filters .form-label {
            font-weight: 500;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .inventory-filters .btn-group .btn {
            font-size: 0.85rem;
            padding: 0.375rem 0.75rem;
        }
        
        .inventory-filters .btn-check:checked + .btn {
            font-weight: 600;
        }
        
        .inventory-filters .btn-check:checked + .btn-outline-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .inventory-filters .btn-check:checked + .btn-outline-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .inventory-filters .btn-check:checked + .btn-outline-warning {
            background-color: var(--warning-color);
            color: #333;
        }
        
        .inventory-filters .btn-check:checked + .btn-outline-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .inventory-filters .btn-check:checked + .btn-outline-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        /* Filter Badges */
        .filter-badge {
            background-color: rgba(58, 134, 255, 0.1);
            border: 1px solid rgba(58, 134, 255, 0.3);
            color: var(--primary-color);
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .filter-badge .remove-filter {
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .filter-badge .remove-filter:hover {
            opacity: 1;
        }
        
        /* Price Range Inputs */
        .inventory-filters input[type="number"] {
            max-width: 120px;
        }
        
        /* Active Filters Section */
        #active-filters {
            border-top: 1px dashed #dee2e6;
            padding-top: 1rem;
        }
        
        /* Toast Container */
        .toast-container {
            z-index: 1055;
        }
        
        .toast {
            min-width: 300px;
            max-width: 400px;
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
            
            <!-- Inventory Filters -->
            <div class="inventory-filters mb-4" id="inventory-filters" style="display: none;">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Search Bar -->
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Search Items</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" id="search-filter" placeholder="Search by name or SKU...">
                                    <button class="btn btn-outline-secondary" id="clear-search" type="button" title="Clear search">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Type Filter -->
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Item Type</label>
                                <select class="form-select" id="type-filter">
                                    <option value="">All Types</option>
                                    <!-- Types will be populated dynamically -->
                                </select>
                            </div>
                            
                            <!-- Stock Status Filter -->
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Stock Status</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="stock-filter" id="stock-all" value="all" checked>
                                    <label class="btn btn-outline-primary" for="stock-all">All</label>
                                    
                                    <input type="radio" class="btn-check" name="stock-filter" id="stock-in" value="in">
                                    <label class="btn btn-outline-success" for="stock-in">In Stock</label>
                                    
                                    <input type="radio" class="btn-check" name="stock-filter" id="stock-low" value="low">
                                    <label class="btn btn-outline-warning" for="stock-low">Low Stock</label>
                                    
                                    <input type="radio" class="btn-check" name="stock-filter" id="stock-out" value="out">
                                    <label class="btn btn-outline-danger" for="stock-out">Out of Stock</label>
                                </div>
                            </div>
                            
                            <!-- Status Filter -->
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Item Status</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="status-filter" id="status-all" value="all" checked>
                                    <label class="btn btn-outline-secondary" for="status-all">All</label>
                                    
                                    <input type="radio" class="btn-check" name="status-filter" id="status-active" value="active">
                                    <label class="btn btn-outline-success" for="status-active">Active</label>
                                    
                                    <input type="radio" class="btn-check" name="status-filter" id="status-inactive" value="inactive">
                                    <label class="btn btn-outline-secondary" for="status-inactive">Inactive</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Price Range Filter -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Price Range</label>
                                <div class="d-flex align-items-center">
                                    <input type="number" class="form-control form-control-sm me-2" id="price-min" placeholder="Min" min="0" step="0.01">
                                    <span class="text-muted">to</span>
                                    <input type="number" class="form-control form-control-sm ms-2" id="price-max" placeholder="Max" min="0" step="0.01">
                                    <button class="btn btn-sm btn-outline-secondary ms-2" id="clear-price" type="button" title="Clear price filter">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="form-text small">Leave empty for no limit</div>
                            </div>
                            
                            <!-- Sort Options -->
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Sort By</label>
                                <select class="form-select" id="sort-filter">
                                    <option value="name_asc">Name (A-Z)</option>
                                    <option value="name_desc">Name (Z-A)</option>
                                    <option value="price_asc">Price (Low to High)</option>
                                    <option value="price_desc">Price (High to Low)</option>
                                    <option value="stock_asc">Stock (Low to High)</option>
                                    <option value="stock_desc">Stock (High to Low)</option>
                                    <option value="date_desc" selected>Date Added (Newest)</option>
                                    <option value="date_asc">Date Added (Oldest)</option>
                                </select>
                            </div>
                            
                            <!-- Filter Actions -->
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="btn-group w-100">
                                    <button class="btn btn-outline-primary" id="apply-filters">
                                        <i class="fas fa-filter me-1"></i> Apply Filters
                                    </button>
                                    <button class="btn btn-outline-secondary" id="reset-filters">
                                        <i class="fas fa-redo me-1"></i> Reset All
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Active Filters Display -->
                        <div class="row mt-3" id="active-filters" style="display: none;">
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <span class="small text-muted me-2">Active filters:</span>
                                    <div class="d-flex flex-wrap gap-2" id="filter-badges">
                                        <!-- Active filter badges will appear here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
            
            // Filtering elements
            const filtersContainer = document.getElementById('inventory-filters');
            const searchFilter = document.getElementById('search-filter');
            const typeFilter = document.getElementById('type-filter');
            const stockFilterAll = document.getElementById('stock-all');
            const stockFilterIn = document.getElementById('stock-in');
            const stockFilterLow = document.getElementById('stock-low');
            const stockFilterOut = document.getElementById('stock-out');
            const statusFilterAll = document.getElementById('status-all');
            const statusFilterActive = document.getElementById('status-active');
            const statusFilterInactive = document.getElementById('status-inactive');
            const priceMin = document.getElementById('price-min');
            const priceMax = document.getElementById('price-max');
            const sortFilter = document.getElementById('sort-filter');
            const clearSearchBtn = document.getElementById('clear-search');
            const clearPriceBtn = document.getElementById('clear-price');
            const applyFiltersBtn = document.getElementById('apply-filters');
            const resetFiltersBtn = document.getElementById('reset-filters');
            const activeFiltersContainer = document.getElementById('active-filters');
            const filterBadgesContainer = document.getElementById('filter-badges');
            
            // Filter state
            let currentFilters = {
                search: '',
                type: '',
                stock: 'all',
                status: 'all',
                priceMin: null,
                priceMax: null,
                sort: 'date_desc'
            };
            
            // Store loaded items for filtering
            let allItems = [];
            let filteredItems = [];
            
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
                                    editInventoryItem(itemId);
                                });
                            });
                            
                            document.querySelectorAll('.delete-item-btn').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    const itemId = this.getAttribute('data-id');
                                    const itemName = this.closest('tr').querySelector('td:nth-child(2)').textContent.trim();
                                    
                                    if (confirm(`Are you sure you want to delete "${itemName}" (ID: ${itemId})? This action cannot be undone.`)) {
                                        deleteInventoryItem(itemId);
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
            
            // ============================================
            // FILTERING FUNCTIONS
            // ============================================
            
            /**
             * Apply filters to items and update display
             */
            function applyFilters() {
                if (allItems.length === 0) return;
                
                // Get current filter values
                currentFilters.search = searchFilter.value.trim().toLowerCase();
                currentFilters.type = typeFilter.value;
                currentFilters.stock = document.querySelector('input[name="stock-filter"]:checked').value;
                currentFilters.status = document.querySelector('input[name="status-filter"]:checked').value;
                currentFilters.priceMin = priceMin.value ? parseFloat(priceMin.value) : null;
                currentFilters.priceMax = priceMax.value ? parseFloat(priceMax.value) : null;
                currentFilters.sort = sortFilter.value;
                
                // Filter items
                filteredItems = allItems.filter(item => {
                    // Search filter
                    if (currentFilters.search) {
                        const searchInName = item.name?.toLowerCase().includes(currentFilters.search) || false;
                        const searchInSku = item.sku?.toLowerCase().includes(currentFilters.search) || false;
                        if (!searchInName && !searchInSku) return false;
                    }
                    
                    // Type filter
                    if (currentFilters.type && item.type !== currentFilters.type) {
                        return false;
                    }
                    
                    // Stock filter
                    const stock = item.current_stock || 0;
                    switch (currentFilters.stock) {
                        case 'in':
                            if (stock <= 0) return false;
                            break;
                        case 'low':
                            if (stock > 10 || stock <= 0) return false; // Assuming low stock is <= 10
                            break;
                        case 'out':
                            if (stock > 0) return false;
                            break;
                    }
                    
                    // Status filter
                    switch (currentFilters.status) {
                        case 'active':
                            if (!item.is_active) return false;
                            break;
                        case 'inactive':
                            if (item.is_active) return false;
                            break;
                    }
                    
                    // Price filter
                    const price = item.unit_price || 0;
                    if (currentFilters.priceMin !== null && price < currentFilters.priceMin) {
                        return false;
                    }
                    if (currentFilters.priceMax !== null && price > currentFilters.priceMax) {
                        return false;
                    }
                    
                    return true;
                });
                
                // Sort items
                sortItems(filteredItems);
                
                // Update display
                updateTableWithFilteredItems();
                
                // Update active filters display
                updateActiveFiltersDisplay();
            }
            
            /**
             * Sort items based on current sort setting
             */
            function sortItems(items) {
                const [field, direction] = currentFilters.sort.split('_');
                
                items.sort((a, b) => {
                    let aValue, bValue;
                    
                    switch (field) {
                        case 'name':
                            aValue = a.name || '';
                            bValue = b.name || '';
                            break;
                        case 'price':
                            aValue = a.unit_price || 0;
                            bValue = b.unit_price || 0;
                            break;
                        case 'stock':
                            aValue = a.current_stock || 0;
                            bValue = b.current_stock || 0;
                            break;
                        case 'date':
                            aValue = a.created_at || a.id || 0;
                            bValue = b.created_at || b.id || 0;
                            break;
                        default:
                            return 0;
                    }
                    
                    if (direction === 'asc') {
                        return aValue > bValue ? 1 : aValue < bValue ? -1 : 0;
                    } else {
                        return aValue < bValue ? 1 : aValue > bValue ? -1 : 0;
                    }
                });
            }
            
            /**
             * Update table with filtered items
             */
            function updateTableWithFilteredItems() {
                // Clear existing table rows
                tableBody.innerHTML = '';
                
                if (filteredItems.length === 0) {
                    // Show no data message
                    noDataMessage.style.display = 'block';
                    tableWrapper.style.display = 'none';
                    
                    // Update message for filtered results
                    if (Object.values(currentFilters).some(value => 
                        (typeof value === 'string' && value !== '' && value !== 'all') || 
                        (typeof value === 'number' && value !== null)
                    )) {
                        noDataMessage.innerHTML = `
                            <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                            <h5>No matching items found</h5>
                            <p class="text-muted">No items match your current filters. Try adjusting your search criteria.</p>
                            <button class="btn btn-sm btn-outline-primary mt-2" id="reset-filters-from-empty">
                                <i class="fas fa-redo me-1"></i> Reset Filters
                            </button>
                        `;
                        
                        // Add event listener to reset button
                        setTimeout(() => {
                            const resetBtn = document.getElementById('reset-filters-from-empty');
                            if (resetBtn) {
                                resetBtn.addEventListener('click', resetFilters);
                            }
                        }, 100);
                    }
                } else {
                    // Populate table with filtered items
                    filteredItems.forEach(item => {
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
                    noDataMessage.style.display = 'none';
                    
                    // Add event listeners to action buttons
                    document.querySelectorAll('.edit-item-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const itemId = this.getAttribute('data-id');
                            editInventoryItem(itemId);
                        });
                    });
                    
                    document.querySelectorAll('.delete-item-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const itemId = this.getAttribute('data-id');
                            const itemName = this.closest('tr').querySelector('td:nth-child(2)').textContent.trim();
                            
                            if (confirm(`Are you sure you want to delete "${itemName}" (ID: ${itemId})? This action cannot be undone.`)) {
                                deleteInventoryItem(itemId);
                            }
                        });
                    });
                }
                
                // Update item count
                updateItemCount();
            }
            
            /**
             * Update item count display
             */
            function updateItemCount() {
                const totalCount = allItems.length;
                const filteredCount = filteredItems.length;
                const countElement = document.getElementById('current-category-title');
                
                if (countElement) {
                    let countText = `Inventory Items`;
                    
                    if (filteredCount !== totalCount) {
                        countText = `Inventory Items (${filteredCount} of ${totalCount})`;
                    } else if (totalCount > 0) {
                        countText = `Inventory Items (${totalCount})`;
                    }
                    
                    countElement.textContent = countText;
                }
            }
            
            /**
             * Delete an inventory item
             */
            function deleteInventoryItem(itemId) {
                // Show loading state
                const deleteBtn = document.querySelector(`.delete-item-btn[data-id="${itemId}"]`);
                if (deleteBtn) {
                    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    deleteBtn.disabled = true;
                }
                
                // Get CSRF token from meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                // Send DELETE request
                // Use FormData to handle CSRF and method override properly
                const formData = new FormData();
                formData.append('_method', 'DELETE');
                formData.append('_token', csrfToken);
                
                fetch(`/inventory/${itemId}`, {
                    method: 'POST', // Use POST with _method override
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData,
                    credentials: 'same-origin' // Include cookies for authentication
                })
                .then(response => {
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        // If not JSON, get text to analyze what we got
                        return response.text().then(text => {
                            // Analyze the HTML response
                            if (text.includes('login') || text.includes('Login') || text.includes('Sign In')) {
                                throw new Error('AUTH_REQUIRED: Server returned login page. Please log in to delete items.');
                            } else if (text.includes('inventory') || text.includes('Inventory') || text.includes('success')) {
                                // Looks like a success page - treat as success
                                return { success: true, message: 'Item deleted successfully!' };
                            } else {
                                // Unknown HTML response
                                throw new Error(`HTML_RESPONSE: Server returned HTML instead of JSON. Status: ${response.status}. Response starts with: ${text.substring(0, 100)}...`);
                            }
                        });
                    }
                    
                    if (!response.ok) {
                        throw new Error(`HTTP_ERROR: status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Show success message
                    showToast('success', 'Item deleted successfully!', 'The inventory item has been removed.');
                    
                    // Remove item from local arrays
                    allItems = allItems.filter(item => item.id != itemId);
                    filteredItems = filteredItems.filter(item => item.id != itemId);
                    
                    // Update table display
                    updateTableWithFilteredItems();
                    
                    // Update item count
                    updateItemCount();
                })
                .catch(error => {
                    console.error('Error deleting item:', error);
                    
                    // Determine error type and show appropriate message
                    let errorMessage = 'Unable to delete the item. Please try again.';
                    let errorTitle = 'Delete Failed';
                    
                    if (error.message.includes('AUTH_REQUIRED:')) {
                        errorTitle = 'Authentication Required';
                        errorMessage = 'Please log in to delete items. The server returned a login page.';
                    } else if (error.message.includes('HTML_RESPONSE:')) {
                        errorTitle = 'Unexpected Response';
                        errorMessage = 'The server returned an unexpected HTML page instead of JSON data.';
                    } else if (error.message.includes('HTML instead of JSON')) {
                        errorTitle = 'Authentication Required';
                        errorMessage = 'Please log in to delete items. The server returned a login page.';
                    } else if (error.message.includes('403')) {
                        errorTitle = 'Permission Denied';
                        errorMessage = 'You do not have permission to delete inventory items.';
                    } else if (error.message.includes('404')) {
                        errorTitle = 'Item Not Found';
                        errorMessage = 'The item you tried to delete does not exist or has already been deleted.';
                    } else if (error.message.includes('405')) {
                        errorTitle = 'Method Not Allowed';
                        errorMessage = 'The server rejected the delete request. This might be a configuration issue.';
                    }
                    
                    // Show error message
                    showToast('error', errorTitle, errorMessage);
                    
                    // Reset button state
                    if (deleteBtn) {
                        deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
                        deleteBtn.disabled = false;
                    }
                });
            }
            
            /**
             * Show toast notification
             */
            function showToast(type, title, message) {
                // Create toast container if it doesn't exist
                let toastContainer = document.getElementById('toast-container');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.id = 'toast-container';
                    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                    toastContainer.style.zIndex = '1050';
                    document.body.appendChild(toastContainer);
                }
                
                // Create toast element
                const toastId = 'toast-' + Date.now();
                const toast = document.createElement('div');
                toast.id = toastId;
                toast.className = 'toast align-items-center text-bg-' + (type === 'success' ? 'success' : 'danger') + ' border-0';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                
                // Toast content
                toast.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong>${title}</strong><br>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                `;
                
                // Add to container
                toastContainer.appendChild(toast);
                
                // Initialize and show toast
                if (typeof bootstrap !== 'undefined') {
                    const bsToast = new bootstrap.Toast(toast, {
                        autohide: true,
                        delay: 5000
                    });
                    bsToast.show();
                } else {
                    // Fallback: show as regular alert
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        toast.style.transition = 'opacity 0.5s';
                        setTimeout(() => toast.remove(), 500);
                    }, 5000);
                }
                
                // Remove toast from DOM after it's hidden
                toast.addEventListener('hidden.bs.toast', function () {
                    toast.remove();
                });
            }
            
            /**
             * Edit an inventory item - redirect to edit page
             */
            function editInventoryItem(itemId) {
                // Show loading state on the button
                const editBtn = document.querySelector(`.edit-item-btn[data-id="${itemId}"]`);
                if (editBtn) {
                    const originalHtml = editBtn.innerHTML;
                    editBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    
                    // Redirect to edit page after a brief delay to show loading state
                    setTimeout(() => {
                        window.location.href = `/inventory/${itemId}/edit`;
                    }, 300);
                } else {
                    // Direct redirect if button not found
                    window.location.href = `/inventory/${itemId}/edit`;
                }
            }
            
            /**
             * Update active filters display
             */
            function updateActiveFiltersDisplay() {
                // Clear existing badges
                filterBadgesContainer.innerHTML = '';
                
                const activeFilters = [];
                
                // Search filter
                if (currentFilters.search) {
                    activeFilters.push({
                        type: 'search',
                        label: `Search: "${currentFilters.search}"`,
                        value: currentFilters.search
                    });
                }
                
                // Type filter
                if (currentFilters.type) {
                    const typeLabel = typeFilter.options[typeFilter.selectedIndex]?.text || currentFilters.type;
                    activeFilters.push({
                        type: 'type',
                        label: `Type: ${typeLabel}`,
                        value: currentFilters.type
                    });
                }
                
                // Stock filter
                if (currentFilters.stock !== 'all') {
                    let stockLabel = '';
                    switch (currentFilters.stock) {
                        case 'in': stockLabel = 'In Stock'; break;
                        case 'low': stockLabel = 'Low Stock'; break;
                        case 'out': stockLabel = 'Out of Stock'; break;
                    }
                    activeFilters.push({
                        type: 'stock',
                        label: `Stock: ${stockLabel}`,
                        value: currentFilters.stock
                    });
                }
                
                // Status filter
                if (currentFilters.status !== 'all') {
                    let statusLabel = currentFilters.status === 'active' ? 'Active Only' : 'Inactive Only';
                    activeFilters.push({
                        type: 'status',
                        label: `Status: ${statusLabel}`,
                        value: currentFilters.status
                    });
                }
                
                // Price filter
                if (currentFilters.priceMin !== null || currentFilters.priceMax !== null) {
                    let priceLabel = 'Price: ';
                    if (currentFilters.priceMin !== null && currentFilters.priceMax !== null) {
                        priceLabel += `₱${currentFilters.priceMin} - ₱${currentFilters.priceMax}`;
                    } else if (currentFilters.priceMin !== null) {
                        priceLabel += `From ₱${currentFilters.priceMin}`;
                    } else {
                        priceLabel += `Up to ₱${currentFilters.priceMax}`;
                    }
                    activeFilters.push({
                        type: 'price',
                        label: priceLabel,
                        value: { min: currentFilters.priceMin, max: currentFilters.priceMax }
                    });
                }
                
                // Sort filter (if not default)
                if (currentFilters.sort !== 'date_desc') {
                    const sortLabel = sortFilter.options[sortFilter.selectedIndex]?.text || currentFilters.sort;
                    activeFilters.push({
                        type: 'sort',
                        label: `Sorted by: ${sortLabel}`,
                        value: currentFilters.sort
                    });
                }
                
                // Create badges for active filters
                activeFilters.forEach(filter => {
                    const badge = document.createElement('span');
                    badge.className = 'filter-badge';
                    badge.innerHTML = `
                        ${filter.label}
                        <span class="remove-filter" data-type="${filter.type}" title="Remove this filter">
                            <i class="fas fa-times"></i>
                        </span>
                    `;
                    filterBadgesContainer.appendChild(badge);
                });
                
                // Show/hide active filters container
                if (activeFilters.length > 0) {
                    activeFiltersContainer.style.display = 'block';
                    
                    // Add event listeners to remove buttons
                    document.querySelectorAll('.remove-filter').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const filterType = this.getAttribute('data-type');
                            removeFilter(filterType);
                        });
                    });
                } else {
                    activeFiltersContainer.style.display = 'none';
                }
            }
            
            /**
             * Remove a specific filter
             */
            function removeFilter(filterType) {
                switch (filterType) {
                    case 'search':
                        searchFilter.value = '';
                        break;
                    case 'type':
                        typeFilter.value = '';
                        break;
                    case 'stock':
                        stockFilterAll.checked = true;
                        break;
                    case 'status':
                        statusFilterAll.checked = true;
                        break;
                    case 'price':
                        priceMin.value = '';
                        priceMax.value = '';
                        break;
                    case 'sort':
                        sortFilter.value = 'date_desc';
                        break;
                }
                
                applyFilters();
            }
            
            /**
             * Reset all filters to default
             */
            function resetFilters() {
                // Reset form elements
                searchFilter.value = '';
                typeFilter.value = '';
                stockFilterAll.checked = true;
                statusFilterAll.checked = true;
                priceMin.value = '';
                priceMax.value = '';
                sortFilter.value = 'date_desc';
                
                // Reset filter state
                currentFilters = {
                    search: '',
                    type: '',
                    stock: 'all',
                    status: 'all',
                    priceMin: null,
                    priceMax: null,
                    sort: 'date_desc'
                };
                
                // Reset items
                filteredItems = [...allItems];
                
                // Update display
                updateTableWithFilteredItems();
                updateActiveFiltersDisplay();
            }
            
            /**
             * Populate type filter dropdown with unique types from items
             */
            function populateTypeFilter(items) {
                // Clear existing options except "All Types"
                while (typeFilter.options.length > 1) {
                    typeFilter.remove(1);
                }
                
                // Get unique types
                const types = [...new Set(items.map(item => item.type).filter(type => type))].sort();
                
                // Add type options
                types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type;
                    option.textContent = type;
                    typeFilter.appendChild(option);
                });
            }
            
            // ============================================
            // FILTER EVENT LISTENERS
            // ============================================
            
            // Search filter events
            if (searchFilter) {
                searchFilter.addEventListener('input', function() {
                    // Auto-apply search after typing stops (debounce)
                    clearTimeout(searchFilter.debounceTimer);
                    searchFilter.debounceTimer = setTimeout(() => {
                        applyFilters();
                    }, 300);
                });
            }
            
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function() {
                    searchFilter.value = '';
                    applyFilters();
                });
            }
            
            // Type filter event
            if (typeFilter) {
                typeFilter.addEventListener('change', applyFilters);
            }
            
            // Stock filter events
            [stockFilterAll, stockFilterIn, stockFilterLow, stockFilterOut].forEach(radio => {
                if (radio) {
                    radio.addEventListener('change', applyFilters);
                }
            });
            
            // Status filter events
            [statusFilterAll, statusFilterActive, statusFilterInactive].forEach(radio => {
                if (radio) {
                    radio.addEventListener('change', applyFilters);
                }
            });
            
            // Price filter events
            if (priceMin) {
                priceMin.addEventListener('input', function() {
                    clearTimeout(priceMin.debounceTimer);
                    priceMin.debounceTimer = setTimeout(() => {
                        applyFilters();
                    }, 500);
                });
            }
            
            if (priceMax) {
                priceMax.addEventListener('input', function() {
                    clearTimeout(priceMax.debounceTimer);
                    priceMax.debounceTimer = setTimeout(() => {
                        applyFilters();
                    }, 500);
                });
            }
            
            if (clearPriceBtn) {
                clearPriceBtn.addEventListener('click', function() {
                    priceMin.value = '';
                    priceMax.value = '';
                    applyFilters();
                });
            }
            
            // Sort filter event
            if (sortFilter) {
                sortFilter.addEventListener('change', applyFilters);
            }
            
            // Apply filters button
            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', applyFilters);
            }
            
            // Reset filters button
            if (resetFiltersBtn) {
                resetFiltersBtn.addEventListener('click', resetFilters);
            }
            
            // ============================================
            // MODIFIED LOAD INVENTORY FUNCTION
            // ============================================
            
            // Modify the loadInventoryItems function to store items and show filters
            const originalLoadInventoryItems = loadInventoryItems;
            loadInventoryItems = function(category) {
                originalLoadInventoryItems(category);
                
                // The actual loading happens in the fetch promise
                // We need to modify the fetch handler to store items
            };
            
            // Replace the fetch handler in the original function
            // We'll do this by modifying the function directly
            // First, let's get the function as a string and modify it
            const functionString = originalLoadInventoryItems.toString();
            const modifiedFunctionString = functionString.replace(
                /\.then\(items => \{([\s\S]*?items\.forEach\(item => \{)/,
                `.then(items => {
                    // Store items for filtering
                    allItems = items;
                    filteredItems = [...items];
                    
                    // Show filters container
                    if (filtersContainer) {
                        filtersContainer.style.display = 'block';
                    }
                    
                    // Populate type filter dropdown
                    populateTypeFilter(items);
                    
                    // Apply any existing filters from URL
                    applyFiltersFromURL();
                    
                    $1`
            );
            
            // Replace the function
            loadInventoryItems = eval('(' + modifiedFunctionString + ')');
            
            /**
             * Apply filters from URL parameters
             */
            function applyFiltersFromURL() {
                const urlParams = new URLSearchParams(window.location.search);
                
                // Apply filters from URL parameters
                if (urlParams.has('search')) {
                    searchFilter.value = urlParams.get('search');
                }
                
                if (urlParams.has('type')) {
                    typeFilter.value = urlParams.get('type');
                }
                
                if (urlParams.has('stock')) {
                    const stockValue = urlParams.get('stock');
                    const stockRadio = document.querySelector(`input[name="stock-filter"][value="${stockValue}"]`);
                    if (stockRadio) {
                        stockRadio.checked = true;
                    }
                }
                
                if (urlParams.has('status')) {
                    const statusValue = urlParams.get('status');
                    const statusRadio = document.querySelector(`input[name="status-filter"][value="${statusValue}"]`);
                    if (statusRadio) {
                        statusRadio.checked = true;
                    }
                }
                
                if (urlParams.has('price_min')) {
                    priceMin.value = urlParams.get('price_min');
                }
                
                if (urlParams.has('price_max')) {
                    priceMax.value = urlParams.get('price_max');
                }
                
                if (urlParams.has('sort')) {
                    sortFilter.value = urlParams.get('sort');
                }
                
                // Apply filters if any URL parameters were set
                if (Array.from(urlParams.keys()).some(key => 
                    ['search', 'type', 'stock', 'status', 'price_min', 'price_max', 'sort'].includes(key)
                )) {
                    setTimeout(() => applyFilters(), 100);
                }
            }
            
            /**
             * Update URL with current filter state
             */
            function updateURLWithFilters() {
                const urlParams = new URLSearchParams(window.location.search);
                
                // Update or remove search parameter
                if (currentFilters.search) {
                    urlParams.set('search', currentFilters.search);
                } else {
                    urlParams.delete('search');
                }
                
                // Update or remove type parameter
                if (currentFilters.type) {
                    urlParams.set('type', currentFilters.type);
                } else {
                    urlParams.delete('type');
                }
                
                // Update or remove stock parameter
                if (currentFilters.stock !== 'all') {
                    urlParams.set('stock', currentFilters.stock);
                } else {
                    urlParams.delete('stock');
                }
                
                // Update or remove status parameter
                if (currentFilters.status !== 'all') {
                    urlParams.set('status', currentFilters.status);
                } else {
                    urlParams.delete('status');
                }
                
                // Update or remove price parameters
                if (currentFilters.priceMin !== null) {
                    urlParams.set('price_min', currentFilters.priceMin);
                } else {
                    urlParams.delete('price_min');
                }
                
                if (currentFilters.priceMax !== null) {
                    urlParams.set('price_max', currentFilters.priceMax);
                } else {
                    urlParams.delete('price_max');
                }
                
                // Update or remove sort parameter
                if (currentFilters.sort !== 'date_desc') {
                    urlParams.set('sort', currentFilters.sort);
                } else {
                    urlParams.delete('sort');
                }
                
                // Update URL without page reload
                const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
                window.history.replaceState({}, '', newUrl);
            }
            
            // Modify applyFilters to update URL
            const originalApplyFilters = applyFilters;
            applyFilters = function() {
                originalApplyFilters();
                updateURLWithFilters();
            };
            
            // Check if URL has category parameter (using existing urlParams variable)
            const urlCategory = urlParams.get('category');
            if (urlCategory) {
                // Find and click the corresponding category box
                const targetBox = document.querySelector(`.category-box[data-category="${urlCategory}"]`);
                if (targetBox) {
                    targetBox.click();
                }
            }

            // ============================================
            // ENHANCED "ADD NEW ITEM" FUNCTIONALITY
            // ============================================
            
            // Add New Item button click handler - Enhanced for Shirt Products
            // Note: addNewItemBtn is already declared at line 339
            if (addNewItemBtn) {
                addNewItemBtn.addEventListener('click', function() {
                    if (currentCategory) {
                        // Check if current category is "Shirt Products"
                        if (currentCategory === 'Shirt Products') {
                            console.log('Shirt Products category selected - Opening modal');
                            
                            // Open the Add New Shirt Product modal
                            const shirtModalElement = document.getElementById('addShirtProductModal');
                            
                            if (shirtModalElement && typeof bootstrap !== 'undefined') {
                                const addShirtProductModal = new bootstrap.Modal(shirtModalElement);
                                addShirtProductModal.show();
                                console.log('Shirt product modal opened successfully');
                            } else {
                                console.error('Modal element or Bootstrap not found');
                                alert('Error: Could not open the form. Please refresh the page.');
                            }
                        } else {
                            // For other categories, redirect to inventory action page
                            console.log('Non-shirt category selected - Redirecting');
                            window.location.href = `/inventoryaction?category=${encodeURIComponent(currentCategory)}`;
                        }
                    } else {
                        alert('Please select a category first');
                    }
                });
            }

            // Form submission handler for Shirt Products
            const submitNewShirtProductBtn = document.getElementById('submitNewShirtProductBtn');
            if (submitNewShirtProductBtn) {
                submitNewShirtProductBtn.addEventListener('click', function() {
                    console.log('Save Shirt Product button clicked');
                    
                    const form = document.getElementById('addShirtProductForm');
                    if (!form) {
                        console.error('Form not found');
                        alert('Error: Form not found');
                        return;
                    }
                    
                    // Basic form validation
                    if (!form.checkValidity()) {
                        console.log('Form validation failed');
                        form.reportValidity();
                        return;
                    }
                    
                    // Disable button and show loading
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                    this.disabled = true;
                    
                    // Collect form data
                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData.entries());
                    console.log('Form data:', data);
                    
                    // AJAX submission to create shirt product
                    fetch('/inventory/shirt-products', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(result => {
                        console.log('Success:', result);
                        
                        // Re-enable button
                        this.innerHTML = originalText;
                        this.disabled = false;
                        
                        // Show success message
                        alert(result.message + '\n\n' +
                              'SKU: ' + result.data.sku + '\n' +
                              'Product: ' + result.data.name + '\n' +
                              'Price: ₱' + result.data.price + '\n' +
                              'Stock: ' + result.data.stock + '\n' +
                              'ID: ' + result.data.id);
                        
                        // Close modal
                        const modalElement = document.getElementById('addShirtProductModal');
                        if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                            modal.hide();
                        }
                        
                        // Reset form
                        form.reset();
                        
                        // Refresh the inventory list to show the new item
                        console.log('Refreshing inventory list...');
                        // Trigger a click on the current category to reload items
                        const currentCategoryBox = document.querySelector(`.category-box[data-category="${currentCategory}"]`);
                        if (currentCategoryBox) {
                            currentCategoryBox.click();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        
                        // Re-enable button
                        this.innerHTML = originalText;
                        this.disabled = false;
                        
                        // Show error message
                        alert('Error saving shirt product: ' + error.message + '\n\nPlease check the form and try again.');
                    });
                });
            }
            
            // Reset form when modal closes
            const shirtModalElement = document.getElementById('addShirtProductModal');
            if (shirtModalElement) {
                shirtModalElement.addEventListener('hidden.bs.modal', function () {
                    console.log('Modal closed - resetting form');
                    const form = document.getElementById('addShirtProductForm');
                    if (form) {
                        form.reset();
                    }
                });
            }
        });
    </script>

    <!-- Add New Shirt Product Modal -->
    <div class="modal fade" id="addShirtProductModal" tabindex="-1" aria-labelledby="addShirtProductModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addShirtProductModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>Add New Shirt Product
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addShirtProductForm">
                        @csrf
                        
                        <div class="row">
                            <!-- SKU -->
                            <div class="col-md-6 mb-3">
                                <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sku" name="sku" required 
                                       placeholder="e.g., TSHIRT-BLK-M" maxlength="50">
                                <div class="form-text">Unique identifier for this shirt product</div>
                            </div>
                            
                            <!-- Brand -->
                            <div class="col-md-6 mb-3">
                                <label for="brand" class="form-label">Brand <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="brand" name="brand" required 
                                       placeholder="e.g., Nike, Adidas, Uniqlo" maxlength="100">
                                <div class="form-text">Brand of the shirt</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Type -->
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Select type</option>
                                    <option value="T-shirt">T-shirt</option>
                                    <option value="Polo">Polo</option>
                                    <option value="Hoodie">Hoodie</option>
                                    <option value="Sweatshirt">Sweatshirt</option>
                                    <option value="Tank Top">Tank Top</option>
                                    <option value="Long Sleeve">Long Sleeve</option>
                                </select>
                                <div class="form-text">Type of shirt</div>
                            </div>
                            
                            <!-- Color -->
                            <div class="col-md-6 mb-3">
                                <label for="color" class="form-label">Color <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="color" name="color" required 
                                       placeholder="e.g., Black, White, Blue" maxlength="50">
                                <div class="form-text">Color of the shirt</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Size -->
                            <div class="col-md-6 mb-3">
                                <label for="size" class="form-label">Size <span class="text-danger">*</span></label>
                                <select class="form-select" id="size" name="size" required>
                                    <option value="">Select size</option>
                                    <option value="XS">XS</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                    <option value="XXL">XXL</option>
                                    <option value="XXXL">XXXL</option>
                                </select>
                                <div class="form-text">Size of the shirt</div>
                            </div>
                            
                            <!-- Price -->
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price (₱) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="price" name="price" required 
                                       step="0.01" min="0" placeholder="0.00">
                                <div class="form-text">Price per unit in Philippine Peso</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Stock Quantity -->
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Initial Stock <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="stock" name="stock" required 
                                       min="0" placeholder="0" value="0">
                                <div class="form-text">Initial quantity in stock</div>
                            </div>
                            
                            <!-- Supplier -->
                            <div class="col-md-6 mb-3">
                                <label for="supplier" class="form-label">Supplier</label>
                                <input type="text" class="form-control" id="supplier" name="supplier" 
                                       placeholder="Supplier name" maxlength="100">
                                <div class="form-text">Supplier of this shirt</div>
                            </div>
                        </div>
                        
                        <!-- Shop/Location -->
                        <div class="mb-3">
                            <label for="shop" class="form-label">Shop/Location</label>
                            <input type="text" class="form-control" id="shop" name="shop" 
                                   placeholder="e.g., Main Store, Online, Warehouse" maxlength="100">
                            <div class="form-text">Where this shirt is located</div>
                        </div>
                        
                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" 
                                      placeholder="Additional notes about this shirt product"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="submitNewShirtProductBtn">
                        <i class="fas fa-save me-2"></i>Save Shirt Product
                    </button>
                </div>
            </div>
        </div>
    </div>


    @endsection
</body>
</html>