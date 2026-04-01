<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Action - Class Apparel PH</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .page-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .page-subtitle {
            color: #7f8c8d;
            margin-bottom: 0;
        }
        
        .page-actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .category-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            border-color: #0d6efd;
        }
        
        .category-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .category-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .category-description {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 20px;
            line-height: 1.4;
        }
        
        .category-badge {
            margin-bottom: 15px;
        }
        
        .btn-category {
            width: 100%;
            padding: 10px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-boxes me-2"></i>
                    Inventory Action
                </h1>
                <p class="page-subtitle">Choose a category for your new inventory item</p>
            </div>
            <div class="page-actions">
                <a href="/inventory/select-category" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Add New Item
                </a>
            </div>
        </div>
    </div>
    
    <!-- Category Selection -->
    <div class="container">
        <div class="category-grid">
            <!-- Shirt Products -->
            <div class="category-card">
                <div class="category-icon text-primary">
                    <i class="fas fa-tshirt"></i>
                </div>
                <h3 class="category-title">Shirt Products</h3>
                <p class="category-description">
                    T-shirts, polo shirts, hoodies, and other apparel items
                </p>
                <div class="category-badge">
                    <span class="badge bg-primary">Apparel</span>
                </div>
                <a href="/inventory/create?category=shirt" class="btn btn-outline-primary btn-category">
                    <i class="fas fa-chevron-right me-2"></i> Select Shirt Products
                </a>
                <div class="mt-3">
                    <a href="/inventory/create?category=shirt&action=add" class="btn btn-primary btn-sm me-2">
                        <i class="fas fa-plus me-1"></i> Add Shirt
                    </a>
                    <button class="btn btn-danger btn-sm" onclick="alert('Deduct feature coming soon')">
                        <i class="fas fa-minus me-1"></i> Deduct Shirt
                    </button>
                </div>
            </div>
            
            <!-- Other Items -->
            <div class="category-card">
                <div class="category-icon text-success">
                    <i class="fas fa-box"></i>
                </div>
                <h3 class="category-title">Other Items</h3>
                <p class="category-description">
                    Miscellaneous inventory items and general products
                </p>
                <div class="category-badge">
                    <span class="badge bg-success">General</span>
                </div>
                <a href="/inventory/create?category=other" class="btn btn-outline-success btn-category">
                    <i class="fas fa-chevron-right me-2"></i> Select Other Items
                </a>
            </div>
            
            <!-- Office Supplies -->
            <div class="category-card">
                <div class="category-icon text-warning">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3 class="category-title">Office Supplies</h3>
                <p class="category-description">
                    Paper, pens, folders, and other office materials
                </p>
                <div class="category-badge">
                    <span class="badge bg-warning">Office</span>
                </div>
                <a href="/inventory/create?category=office" class="btn btn-outline-warning btn-category">
                    <i class="fas fa-chevron-right me-2"></i> Select Office Supplies
                </a>
            </div>
            
            <!-- Machine and Equipment -->
            <div class="category-card">
                <div class="category-icon text-danger">
                    <i class="fas fa-tools"></i>
                </div>
                <h3 class="category-title">Machine and Equipment</h3>
                <p class="category-description">
                    Printers, computers, tools, and other equipment
                </p>
                <div class="category-badge">
                    <span class="badge bg-danger">Equipment</span>
                </div>
                <a href="/inventory/create?category=machine" class="btn btn-outline-danger btn-category">
                    <i class="fas fa-chevron-right me-2"></i> Select Machine & Equipment
                </a>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="alert alert-info mt-5">
            <h5><i class="fas fa-info-circle me-2"></i> How to Use:</h5>
            <ol class="mb-0">
                <li>Click any category card to select it</li>
                <li>For <strong>Shirt Products</strong>, use "Add Shirt" or "Deduct Shirt" buttons</li>
                <li>For other categories, click the main select button</li>
                <li>You'll be taken to the appropriate form</li>
            </ol>
        </div>
    </div>
    
    <!-- Simple JavaScript for interactivity -->
    <script>
        // Simple category card highlighting
        document.addEventListener('DOMContentLoaded', function() {
            const categoryCards = document.querySelectorAll('.category-card');
            
            categoryCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Don't trigger if clicking on a button inside
                    if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON') {
                        return;
                    }
                    
                    // Remove highlight from all cards
                    categoryCards.forEach(c => {
                        c.style.borderColor = 'transparent';
                        c.style.backgroundColor = 'white';
                    });
                    
                    // Highlight clicked card
                    this.style.borderColor = '#0d6efd';
                    this.style.backgroundColor = '#f8f9ff';
                });
            });
            
            console.log('Ultra-simple inventory page loaded successfully!');
            console.log('All elements should be visible in DOM');
        });
    </script>
</body>
</html>