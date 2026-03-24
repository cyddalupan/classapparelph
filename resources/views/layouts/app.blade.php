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
                display: flex;
                align-items: center;
                padding: 0.5rem 1rem;
                color: #64748b;
                text-decoration: none;
                transition: all 0.2s;
                font-size: 0.875rem;
                gap: 0.5rem;
            }
            
            .nav-dropdown-item:hover {
                background: #f1f5f9;
                color: #2563eb;
            }
            
            .nav-dropdown-item.active {
                background: #eff6ff;
                color: #2563eb;
                font-weight: 500;
            }
            
            .nav-dropdown-item i {
                width: 16px;
                text-align: center;
                font-size: 0.875rem;
                flex-shrink: 0;
            }
            
            .nav-dropdown-item span {
                flex: 1;
            }
            
            /* Mobile responsive */
            @media (max-width: 768px) {
                .nav-dropdown-menu {
                    position: static;
                    box-shadow: none;
                    border: none;
                    border-left: 3px solid #2563eb;
                    margin-left: 2rem;
                    margin-top: 0;
                    margin-bottom: 0.5rem;
                }
            }
        </style>
    </head>
    <body class="app-body">
        <div class="app-container">
            <!-- Sidebar Navigation -->
            <aside class="sidebar" id="sidebar">
                <!-- Logo -->
                <div class="sidebar-header">
                    <a href="{{ route('dashboard') }}" class="sidebar-logo">
                        <div class="logo-icon">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <span class="logo-text">CLASS Apparel PH</span>
                    </a>
                    <button class="sidebar-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <!-- User Profile -->
                <div class="sidebar-user">
                    <div class="user-avatar">
                        @auth
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        @else
                        G
                        @endauth
                    </div>
                    <div class="user-info">
                        <div class="user-name">
                            @auth
                            {{ Auth::user()->name }}
                            @else
                            Guest
                            @endauth
                        </div>
                        <div class="user-role">
                            @auth
                                @if(Auth::user()->isAdmin())
                                <span class="role-badge admin">Administrator</span>
                                @elseif(Auth::user()->isSalesAgent())
                                <span class="role-badge sales-agent">Sales Agent</span>
                                @elseif(Auth::user()->isSalesRepresentative())
                                <span class="role-badge sales-rep">Sales Representative</span>
                                @elseif(Auth::user()->isStaff())
                                <span class="role-badge staff">Staff</span>
                                @elseif(Auth::user()->isCustomer())
                                <span class="role-badge customer">Customer</span>
                                @else
                                <span class="role-badge user">User</span>
                                @endif
                            @else
                            <span class="role-badge user">Guest</span>
                            @endauth
                        </div>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="sidebar-nav">
                    <!-- Main Navigation -->
                    <div class="nav-section">
                        <div class="nav-section-title">Main</div>
                        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </div>

                    @auth
                    <!-- Show Business Operations only for NON-SALES AGENTS -->
                    @if(!Auth::user()->isSalesAgent() && !Auth::user()->isSalesRepresentative())
                    <!-- Business Operations -->
                    <div class="nav-section">
                        <div class="nav-section-title">Business</div>
                        <a href="{{ route('orders.index') }}" class="nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="nav-text">Orders</span>
                            <span class="nav-badge">24</span>
                        </a>
                        <a href="{{ route('products.index') }}" class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                            <i class="fas fa-money-bill-wave"></i>
                            <span class="nav-text">Product Pricing</span>
                        </a>
                        <a href="{{ route('customers.index') }}" class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i>
                            <span class="nav-text">Customers</span>
                        </a>
                        <div class="nav-item nav-dropdown {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                            <div class="nav-dropdown-toggle">
                                <i class="fas fa-boxes"></i>
                                <span class="nav-text">Inventory Management</span>
                                <i class="fas fa-chevron-down nav-dropdown-arrow"></i>
                            </div>
                            <div class="nav-dropdown-menu">
                                <a href="{{ route('inventory.index') }}" class="nav-dropdown-item {{ request()->routeIs('inventory.index') ? 'active' : '' }}">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>Dashboard</span>
                                </a>
                                <a href="{{ route('inventory.action') }}" class="nav-dropdown-item {{ request()->routeIs('inventory.action') ? 'active' : '' }}">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Inventory Action</span>
                                </a>
                                <a href="{{ route('products.index') }}" class="nav-dropdown-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                                    <i class="fas fa-cube"></i>
                                    <span>Product</span>
                                </a>
                            </div>
                        </div>
                        <a href="{{ route('production.tracking') }}" class="nav-item {{ request()->routeIs('production.*') ? 'active' : '' }}">
                            <i class="fas fa-cogs"></i>
                            <span class="nav-text">Production</span>
                        </a>
                    </div>
                    @endif

                    <!-- Sales Agent Navigation (Only for Sales Agents/Reps) -->
                    @if(Auth::user()->isSalesAgent() || Auth::user()->isSalesRepresentative())
                    <div class="nav-section">
                        <div class="nav-section-title">Sales Operations</div>
                        <a href="{{ route('sales.pricing') }}" class="nav-item {{ request()->routeIs('sales.pricing') ? 'active' : '' }}">
                            <i class="fas fa-tags"></i>
                            <span class="nav-text">Product Pricing</span>
                        </a>
                        <a href="{{ route('sales.create-quick') }}" class="nav-item {{ request()->routeIs('sales.create-quick') ? 'active' : '' }}">
                            <i class="fas fa-plus-circle"></i>
                            <span class="nav-text">Add Sales</span>
                            <span class="nav-badge new">NEW</span>
                        </a>
                    </div>
                    @endif

                    <!-- Show other sections only for NON-SALES AGENTS -->
                    @if(!Auth::user()->isSalesAgent() && !Auth::user()->isSalesRepresentative())
                    <!-- Design & Creative -->
                    <div class="nav-section">
                        <div class="nav-section-title">Design</div>
                        <a href="{{ route('design.studio') }}" class="nav-item {{ request()->routeIs('design.*') ? 'active' : '' }}">
                            <i class="fas fa-paint-brush"></i>
                            <span class="nav-text">Design Studio</span>
                        </a>
                    </div>

                    <!-- Analytics & Reports -->
                    <div class="nav-section">
                        <div class="nav-section-title">Analytics</div>
                        <a href="{{ route('analytics.dashboard') }}" class="nav-item {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            <span class="nav-text">Analytics</span>
                        </a>
                        <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="fas fa-file-alt"></i>
                            <span class="nav-text">Reports</span>
                        </a>
                    </div>

                    <!-- Finance -->
                    <div class="nav-section">
                        <div class="nav-section-title">Finance</div>
                        <a href="{{ route('finance.dashboard') }}" class="nav-item {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-chart-pie"></i>
                            <span class="nav-text">Finance Dashboard</span>
                        </a>
                        <a href="{{ route('finance.expenses') }}" class="nav-item {{ request()->routeIs('finance.expenses') ? 'active' : '' }}">
                            <i class="fas fa-money-bill-wave"></i>
                            <span class="nav-text">Expenses</span>
                        </a>
                        <a href="{{ route('finance.sales') }}" class="nav-item {{ request()->routeIs('finance.sales') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            <span class="nav-text">Sales</span>
                        </a>
                        <a href="{{ route('finance.reports') }}" class="nav-item {{ request()->routeIs('finance.reports') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span class="nav-text">Financial Reports</span>
                        </a>
                    </div>

                    <!-- Administration (Admin Only) -->
                    @if(Auth::user()->isAdmin())
                    <div class="nav-section">
                        <div class="nav-section-title">Administration</div>
                        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-cogs"></i>
                            <span class="nav-text">Admin Dashboard</span>
                        </a>
                        <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                            <i class="fas fa-sliders-h"></i>
                            <span class="nav-text">System Settings</span>
                        </a>
                    </div>
                    @endif
                    @endif

                    <!-- User Account -->
                    <div class="nav-section">
                        <div class="nav-section-title">Account</div>
                        <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <i class="fas fa-user"></i>
                            <span class="nav-text">Profile</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="logout-form">
                            @csrf
                            <button type="submit" class="nav-item logout-btn">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="nav-text">Log Out</span>
                            </button>
                        </form>
                    </div>
                    @else
                    <!-- Public Navigation -->
                    <div class="nav-section">
                        <div class="nav-section-title">Access</div>
                        <a href="{{ route('login') }}" class="nav-item {{ request()->routeIs('login') ? 'active' : '' }}">
                            <i class="fas fa-sign-in-alt"></i>
                            <span class="nav-text">Login</span>
                        </a>
                        <a href="{{ route('register') }}" class="nav-item {{ request()->routeIs('register') ? 'active' : '' }}">
                            <i class="fas fa-user-plus"></i>
                            <span class="nav-text">Register</span>
                        </a>
                    </div>
                    @endauth
                </nav>

                <!-- Sidebar Footer -->
                <div class="sidebar-footer">
                    <div class="system-status">
                        <div class="status-indicator active"></div>
                        <span>System Online</span>
                    </div>
                    <div class="sidebar-version">v1.0.0</div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="main-content" id="mainContent">
                <!-- Top Header Bar -->
                <header class="top-header">
                    <div class="header-left">
                        <button class="mobile-menu-btn" onclick="toggleSidebar()">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="page-title">
                            @yield('page-title', 'Dashboard')
                        </div>
                    </div>
                    
                    @auth
                    <div class="header-right">
                        <!-- Quick Actions -->
                        <div class="quick-actions">
                            <button class="header-btn" title="Search">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="header-btn" title="Notifications">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge">3</span>
                            </button>
                            <button class="header-btn" title="Help">
                                <i class="fas fa-question-circle"></i>
                            </button>
                        </div>

                        <!-- User Menu -->
                        <div class="user-menu">
                            <button class="user-menu-toggle" onclick="toggleUserMenu()">
                                <div class="user-avatar-small">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span class="user-name-short">{{ Auth::user()->name }}</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            
                            <div class="user-menu-dropdown" id="userMenu">
                                <div class="user-menu-header">
                                    <div class="user-avatar-medium">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="user-name-medium">{{ Auth::user()->name }}</div>
                                        <div class="user-email">{{ Auth::user()->email }}</div>
                                    </div>
                                </div>
                                
                                <div class="user-menu-items">
                                    <a href="{{ route('profile.edit') }}" class="user-menu-item">
                                        <i class="fas fa-user"></i>
                                        Profile Settings
                                    </a>
                                    
                                    @if(Auth::user()->isAdmin())
                                    <a href="{{ route('admin.settings') }}" class="user-menu-item">
                                        <i class="fas fa-cog"></i>
                                        System Settings
                                    </a>
                                    @endif
                                    
                                    <div class="user-menu-divider"></div>
                                    
                                    <form method="POST" action="{{ route('logout') }}" class="logout-form">
                                        @csrf
                                        <button type="submit" class="user-menu-item logout-btn">
                                            <i class="fas fa-sign-out-alt"></i>
                                            Log Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endauth
                </header>

                <!-- Page Content -->
                <main class="content-area">
                    <!-- Page Header Slot -->
                    @isset($header)
                        <div class="page-header">
                            {{ $header }}
                        </div>
                    @endisset

                    <!-- Main Content Slot -->
                    <div class="page-content">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>

        <script>
            // Sidebar toggle functionality
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                
                // Save state to localStorage
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }

            // User menu toggle
            function toggleUserMenu() {
                const userMenu = document.getElementById('userMenu');
                userMenu.style.display = userMenu.style.display === 'block' ? 'none' : 'block';
            }

            // Close user menu when clicking outside
            document.addEventListener('click', function(e) {
                const userMenu = document.getElementById('userMenu');
                const userToggle = document.querySelector('.user-menu-toggle');
                
                if (userMenu && !userMenu.contains(e.target) && !userToggle.contains(e.target)) {
                    userMenu.style.display = 'none';
                }
            });

            // Initialize sidebar state from localStorage
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                const userMenu = document.getElementById('userMenu');
                
                // Only collapse if explicitly saved in localStorage
                const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                
                if (sidebarCollapsed) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                }

                // Close user menu on page load
                if (userMenu) {
                    userMenu.style.display = 'none';
                }
                
                // On mobile, show sidebar by default (not collapsed)
                if (window.innerWidth < 768) {
                    // On mobile, we want it collapsed by default
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                }
            });

            // Auto-hide sidebar on mobile after navigation
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        const sidebar = document.getElementById('sidebar');
                        const mainContent = document.getElementById('mainContent');
                        sidebar.classList.add('collapsed');
                        mainContent.classList.add('expanded');
                    }
                });
            });

            // Inventory dropdown toggle
            document.querySelectorAll('.nav-dropdown-toggle').forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    console.log('Dropdown toggle clicked');
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const dropdown = this.closest('.nav-dropdown');
                    console.log('Toggling dropdown active state');
                    dropdown.classList.toggle('active');
                    
                    // Close other dropdowns
                    document.querySelectorAll('.nav-dropdown').forEach(other => {
                        if (other !== dropdown) {
                            other.classList.remove('active');
                        }
                    });
                    
                    // Debug: log current state
                    console.log('Dropdown active:', dropdown.classList.contains('active'));
                    console.log('Dropdown menu:', dropdown.querySelector('.nav-dropdown-menu'));
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.nav-dropdown')) {
                    document.querySelectorAll('.nav-dropdown').forEach(dropdown => {
                        dropdown.classList.remove('active');
                    });
                }
            });

            // Responsive behavior - update on resize
            window.addEventListener('resize', function() {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                
                if (window.innerWidth < 768) {
                    // On mobile, collapse sidebar
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                } else {
                    // On desktop, expand sidebar (unless user collapsed it)
                    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                    if (!sidebarCollapsed) {
                        sidebar.classList.remove('collapsed');
                        mainContent.classList.remove('expanded');
                    }
                }
            });
        </script>
        
        <!-- Bootstrap 5 JavaScript Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
        
        @stack('scripts')
    </body>
</html>