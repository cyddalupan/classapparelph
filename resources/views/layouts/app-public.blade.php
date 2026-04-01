<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CLASS Apparel PH') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
        
        <!-- Custom Styles for Inventory Dropdown -->
        <style>
            .nav-dropdown {
                position: relative;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.75rem 1.5rem;
                color: #64748b;
                text-decoration: none;
                transition: all 0.2s;
                border-left: 3px solid transparent;
            }
            
            .nav-dropdown:hover {
                background: #f1f5f9;
                color: #2563eb;
                border-left-color: #2563eb;
            }
            
            .nav-dropdown.active {
                background: #eff6ff;
                color: #2563eb;
                border-left-color: #2563eb;
                font-weight: 600;
            }
            
            .nav-dropdown-toggle {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                flex: 1;
                cursor: pointer;
            }
            
            .nav-dropdown i:first-child {
                width: 20px;
                text-align: center;
                font-size: 1.125rem;
                flex-shrink: 0;
            }
            
            /* Ensure dropdown menu is visible even when parent scrolls */
            .nav-dropdown-menu {
                position: absolute;
                z-index: 9999;
            }
            
            .nav-dropdown-arrow {
                margin-left: auto;
                font-size: 0.75rem;
                transition: transform 0.2s;
                flex-shrink: 0;
            }
            
            .nav-dropdown.active .nav-dropdown-arrow {
                transform: rotate(180deg);
            }
            
            .nav-dropdown-menu {
                display: none;
                position: absolute;
                top: calc(100% + 0.25rem);
                left: 0;
                background: white;
                border: 1px solid #e2e8f0;
                border-radius: 0.375rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                padding: 0.25rem 0;
                z-index: 9999;
                min-width: 220px;
                max-width: 250px;
            }
            
            .nav-dropdown.active .nav-dropdown-menu {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
            
            .nav-dropdown-item {
                display: block;
                padding: 0.5rem 1rem;
                color: #334155;
                text-decoration: none;
                transition: all 0.2s;
                border-left: 3px solid transparent;
            }
            
            .nav-dropdown-item:hover {
                background: #f1f5f9;
                color: #2563eb;
                border-left-color: #2563eb;
            }
            
            .nav-dropdown-item.active {
                background: #eff6ff;
                color: #2563eb;
                border-left-color: #2563eb;
                font-weight: 600;
            }
            
            /* Public layout specific styles */
            .public-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 1.5rem 0;
                margin-bottom: 2rem;
            }
            
            .public-sidebar {
                background: #f8fafc;
                border-right: 1px solid #e2e8f0;
                min-height: calc(100vh - 80px);
            }
            
            .public-content {
                padding: 2rem;
                background: white;
                min-height: calc(100vh - 80px);
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div id="app">
            <!-- Header -->
            <header class="public-header">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Logo -->
                        <div class="d-flex align-items-center">
                            <a href="/" class="text-white text-decoration-none">
                                <i class="fas fa-tshirt fa-2x me-3"></i>
                                <span class="fs-3 fw-bold">CLASS Apparel PH</span>
                            </a>
                        </div>
                        
                        <!-- Navigation -->
                        <nav class="d-flex align-items-center">
                            <a href="/" class="text-white text-decoration-none mx-3">
                                <i class="fas fa-home me-1"></i> Home
                            </a>
                            <a href="/inventoryaction" class="text-white text-decoration-none mx-3">
                                <i class="fas fa-boxes me-1"></i> Inventory Action
                            </a>
                            <a href="/productlist" class="text-white text-decoration-none mx-3">
                                <i class="fas fa-list me-1"></i> Product List
                            </a>
                            <a href="/login" class="btn btn-light btn-sm ms-3">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </nav>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main>
                <div class="container-fluid">
                    <div class="row">
                        <!-- Sidebar (Simplified for Public) -->
                        <div class="col-md-3 col-lg-2 d-none d-md-block public-sidebar">
                            <div class="sidebar-sticky pt-3">
                                <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                                    <span>PUBLIC NAVIGATION</span>
                                </h6>
                                <ul class="nav flex-column mb-2">
                                    <li class="nav-item">
                                        <a class="nav-link" href="/">
                                            <i class="fas fa-home"></i>
                                            Home
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/inventoryaction">
                                            <i class="fas fa-boxes"></i>
                                            Inventory Action
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link active" href="/productlist">
                                            <i class="fas fa-list"></i>
                                            Product List
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/about">
                                            <i class="fas fa-info-circle"></i>
                                            About
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/contact">
                                            <i class="fas fa-envelope"></i>
                                            Contact
                                        </a>
                                    </li>
                                </ul>
                                
                                <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                                    <span>CATEGORIES</span>
                                </h6>
                                <ul class="nav flex-column mb-2">
                                    <li class="nav-item">
                                        <a class="nav-link" href="#box-shirt">
                                            <i class="fas fa-tshirt text-primary"></i>
                                            Shirt Products
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#box-other">
                                            <i class="fas fa-box text-success"></i>
                                            Other Items
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#box-garment">
                                            <i class="fas fa-cut" style="color: #6f42c1;"></i>
                                            Garment Materials
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#box-office">
                                            <i class="fas fa-print text-warning"></i>
                                            Printing & Office
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#box-machine">
                                            <i class="fas fa-tools text-danger"></i>
                                            Machine & Equipment
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Page Content -->
                        <div class="col-md-9 col-lg-10 public-content">
                            <!-- Page Header Slot -->
                            @if(isset($header))
                                <div class="page-header mb-4">
                                    {{ $header }}
                                </div>
                            @endif

                            <!-- Main Content Slot -->
                            <div class="page-content">
                                {{ $slot }}
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="border-top mt-5 py-4">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>CLASS Apparel PH</h5>
                            <p class="text-muted">Professional apparel and inventory management system.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="text-muted mb-0">
                                &copy; {{ date('Y') }} CLASS Apparel PH. All rights reserved.
                            </p>
                            <p class="text-muted small">
                                <a href="/privacy" class="text-muted me-3">Privacy Policy</a>
                                <a href="/terms" class="text-muted me-3">Terms of Service</a>
                                <a href="/contact" class="text-muted">Contact Us</a>
                            </p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Sidebar toggle for mobile
            function toggleSidebar() {
                const sidebar = document.querySelector('.public-sidebar');
                sidebar.classList.toggle('d-none');
            }
            
            // Auto-refresh for product updates
            setTimeout(function() {
                window.location.reload();
            }, 30000);
        </script>
        
        @stack('scripts')
    </body>
</html>