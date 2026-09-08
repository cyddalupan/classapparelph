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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

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

            /* Profile pic shape should follow its rounded-square box (2026-09-08) */
            .sidebar-user .user-avatar img {
                border-radius: inherit;
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
                    <div class="user-avatar clickable-avatar" title="Click to change profile picture" onclick="event.stopPropagation(); document.getElementById('avatarInput').click();">
                        @auth
                        @if(Auth::user()->avatar_url)
                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}">
                        @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        @endif
                        @else
                        G
                        @endauth
                    </div>
                    <div class="user-info">
                        <div class="user-name">
                            @auth
                            @if(Auth::user()->isAdmin())
                            <i class="fas fa-crown user-name-icon"></i>
                            @endif
                            {{ Auth::user()->first_name }}
                            @else
                            Guest
                            @endauth
                        </div>
                        <div class="user-role">
                            @auth
                                @if(Auth::user()->position)
                                <span class="role-badge" style="background:rgba(124,58,237,.14);color:#7c3aed;font-weight:700;">{{ Auth::user()->position }}</span>
                                @elseif(Auth::user()->isAdmin())
                                <span class="role-badge admin">Administrator</span>
                                @elseif(Auth::user()->isSalesAgent())
                                <span class="role-badge sales-agent">Sales Agent</span>
                                @elseif(Auth::user()->isSalesRepresentative())
                                <span class="role-badge sales-rep">Sales Representative</span>
                                @elseif(Auth::user()->isStaff())
                                <span class="role-badge staff">Staff</span>
                                @elseif(Auth::user()->isArtist())
                                <span class="role-badge artist">Artist</span>
                                @elseif(Auth::user()->isGa())
                                <span class="role-badge ga">GA/Agent</span>
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
                        @if(!Auth::user()->isQa())
                        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                        @endif
                        @if(!Auth::user()->isQa())
                        <a href="{{ route('damage.index') }}" class="nav-item {{ request()->routeIs('damage.*') ? 'active' : '' }}">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="nav-text">Damage Reports</span>
                        </a>
                        @endif
                    </div>

                    @auth
                    @if(Auth::user()->isProdManager())
                    <!-- Class Production Manager Navigation -->
                    <div class="nav-section">
                        <div class="nav-section-title">Production</div>
                        <a href="{{ route('production.tracking') }}" class="nav-item {{ request()->routeIs('production.tracking') ? 'active' : '' }}">
                            <i class="fas fa-cogs"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                        <a href="{{ route('sales.prototype.kanban') }}" class="nav-item {{ request()->routeIs('sales.prototype.kanban') ? 'active' : '' }}">
                            <i class="fas fa-columns"></i>
                            <span class="nav-text">Kanban Board</span>
                        </a>
                        <a href="{{ route('sales.prototype.list') }}" class="nav-item {{ request()->routeIs('sales.prototype.list') ? 'active' : '' }}">
                            <i class="fas fa-list"></i>
                            <span class="nav-text">Manager List</span>
                        </a>
                        <a href="{{ route('sales.prototype.ga-order-list') }}" class="nav-item {{ request()->routeIs('sales.prototype.ga-order-list') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-list"></i>
                            <span class="nav-text">GA Job List</span>
                        </a>
                        <a href="{{ route('sales.prototype.calendar') }}" class="nav-item {{ request()->routeIs('sales.prototype.calendar') ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="nav-text">Calendar</span>
                        </a>
                    </div>
                    @endif
                    @if(Auth::user()->isQa())
                    <!-- QA / Sales Agent Navigation -->
                    <div class="nav-section">
                        <div class="nav-section-title">Production</div>
                        <a href="{{ route('sales.prototype.kanban') }}" class="nav-item {{ request()->routeIs('sales.prototype.kanban') ? 'active' : '' }}">
                            <i class="fas fa-columns"></i>
                            <span class="nav-text">Kanban Board</span>
                        </a>
                        <a href="{{ route('sales.prototype.list') }}" class="nav-item {{ request()->routeIs('sales.prototype.list') ? 'active' : '' }}">
                            <i class="fas fa-list"></i>
                            <span class="nav-text">Manager List</span>
                        </a>
                        <a href="{{ route('sales.prototype.ga-order-list') }}" class="nav-item {{ request()->routeIs('sales.prototype.ga-order-list') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-list"></i>
                            <span class="nav-text">GA Job List</span>
                        </a>
                        <a href="{{ route('sales.prototype.backjobs') }}" class="nav-item {{ request()->routeIs('sales.prototype.backjobs') ? 'active' : '' }}">
                            <i class="fas fa-tools"></i>
                            <span class="nav-text">Backjob List</span>
                        </a>
                        <a href="{{ route('sales.prototype.calendar') }}" class="nav-item {{ request()->routeIs('sales.prototype.calendar') ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="nav-text">Calendar</span>
                        </a>
                    </div>
                    <div class="nav-section">
                        <div class="nav-section-title">Business</div>
                        <a href="{{ route('customers.index') }}" class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i>
                            <span class="nav-text">Customers</span>
                        </a>
                    </div>
                    <div class="nav-section">
                        <div class="nav-section-title">My Sales</div>
                        <a href="{{ route('sales.team.dashboard') }}" class="nav-item {{ request()->routeIs('sales.team.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-th-list"></i>
                            <span class="nav-text">My Sales Dashboard</span>
                        </a>
                        <a href="{{ route('sales.prototype.create') }}" class="nav-item {{ request()->routeIs('sales.prototype.create') ? 'active' : '' }}">
                            <i class="fas fa-plus-circle"></i>
                            <span class="nav-text">Add New Sale</span>
                        </a>
                        <a href="{{ route('sales.layout-jobs') }}" class="nav-item {{ request()->routeIs('sales.layout-jobs*') ? 'active' : '' }}">
                            <i class="fas fa-palette"></i>
                            <span class="nav-text">Layout Job</span>
                        </a>
                    </div>
                    @endif
                    @if(Auth::user()->isCoo() || Auth::user()->isCpo() || Auth::user()->isCmo())
                    <div class="nav-section">
                        <div class="nav-section-title">Business</div>
                        <a href="{{ route('customers.index') }}" class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i>
                            <span class="nav-text">Customers</span>
                        </a>
                    </div>
                    <div class="nav-section">
                        <div class="nav-section-title">Production</div>
                        <a href="{{ route('production.tracking') }}" class="nav-item {{ request()->routeIs('production.tracking') ? 'active' : '' }}">
                            <i class="fas fa-cogs"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                        @if(Auth::user()->isCpo() || Auth::user()->isCmo())
                        <span class="nav-item nav-disabled">
                            <i class="fas fa-columns"></i>
                            <span class="nav-text">Kanban Board</span>
                            <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Soon</span>
                        </span>
                        <span class="nav-item nav-disabled">
                            <i class="fas fa-list"></i>
                            <span class="nav-text">Manager List</span>
                            <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Soon</span>
                        </span>
                        <a href="{{ route('sales.layout-jobs.all') }}" class="nav-item {{ request()->routeIs('sales.layout-jobs.all') ? 'active' : '' }}">
                            <i class="fas fa-palette"></i>
                            <span class="nav-text">Layout Job List</span>
                        </a>
                        @else
                        <a href="{{ route('sales.prototype.kanban') }}" class="nav-item {{ request()->routeIs('sales.prototype.kanban') ? 'active' : '' }}">
                            <i class="fas fa-columns"></i>
                            <span class="nav-text">Kanban Board</span>
                        </a>
                        <a href="{{ route('sales.prototype.list') }}" class="nav-item {{ request()->routeIs('sales.prototype.list') ? 'active' : '' }}">
                            <i class="fas fa-list"></i>
                            <span class="nav-text">Manager List</span>
                        </a>
                        <a href="{{ route('sales.prototype.ga-order-list') }}" class="nav-item {{ request()->routeIs('sales.prototype.ga-order-list') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-list"></i>
                            <span class="nav-text">GA Job List</span>
                        </a>
                        <a href="{{ route('sales.layout-jobs.all') }}" class="nav-item {{ request()->routeIs('sales.layout-jobs.all') ? 'active' : '' }}">
                            <i class="fas fa-palette"></i>
                            <span class="nav-text">Layout Job List</span>
                        </a>
                        <a href="{{ route('sales.prototype.special-price-list') }}" class="nav-item {{ request()->routeIs('sales.prototype.special-price-list') ? 'active' : '' }}">
                            <i class="fas fa-tags"></i>
                            <span class="nav-text">Special Price</span>
                        </a>
                        @if(auth()->user() && (auth()->user()->isManager() || auth()->user()->isCoo()))
                        <a href="{{ route('sales.prototype.freebie-list') }}" class="nav-item {{ request()->routeIs('sales.prototype.freebie-list') ? 'active' : '' }}">
                            <i class="fas fa-gift"></i>
                            <span class="nav-text">Freebie List</span>
                        </a>
                        @endif
                        @endif
                        <a href="{{ route('sales.prototype.calendar') }}" class="nav-item {{ request()->routeIs('sales.prototype.calendar') ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="nav-text">Calendar</span>
                        </a>
                        <a href="{{ route('sales.prototype.refunds') }}" class="nav-item {{ request()->routeIs('sales.prototype.refunds') ? 'active' : '' }}">
                            <i class="fas fa-undo-alt"></i>
                            <span class="nav-text">Refunds</span>
                        </a>
                        <a href="{{ route('sales.verification') }}" class="nav-item {{ request()->routeIs('sales.verification') ? 'active' : '' }}">
                            <i class="fas fa-check-circle"></i>
                            <span class="nav-text">Payment Verification</span>
                        </a>
                        <a href="{{ route('sales.cash-flow') }}" class="nav-item {{ request()->routeIs('sales.cash-flow') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            <span class="nav-text">Cash Flow</span>
                        </a>
                    </div>
                    <div class="nav-section">
                        <div class="nav-section-title">Supplies</div>
                        @if(Auth::user()->isCmo())
                        <span class="nav-item nav-disabled">
                            <i class="fas fa-boxes"></i>
                            <span class="nav-text">Inventory Management</span>
                            <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Soon</span>
                        </span>
                        @else
                        <a href="{{ route('inventory.unified') }}" class="nav-item {{ request()->routeIs('inventory.unified') ? 'active' : '' }}">
                            <i class="fas fa-boxes"></i>
                            <span class="nav-text">Inventory Management</span>
                        </a>
                        @endif
                    </div>
                    <div class="nav-section">
                        <div class="nav-section-title">My Sales</div>
                        <a href="{{ route('sales.team.dashboard') }}" class="nav-item {{ request()->routeIs('sales.team.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-th-list"></i>
                            <span class="nav-text">My Sales Dashboard</span>
                        </a>
                        <a href="{{ route('sales.prototype.create') }}" class="nav-item {{ request()->routeIs('sales.prototype.create') ? 'active' : '' }}">
                            <i class="fas fa-plus-circle"></i>
                            <span class="nav-text">Add New Sale</span>
                        </a>
                        <a href="{{ route('sales.layout-jobs') }}" class="nav-item {{ request()->routeIs('sales.layout-jobs*') ? 'active' : '' }}">
                            <i class="fas fa-palette"></i>
                            <span class="nav-text">Layout Job</span>
                        </a>
                    </div>
                    <div class="nav-section">
                        <div class="nav-section-title">Design</div>
                        <span class="nav-item nav-disabled">
                            <i class="fas fa-paint-brush"></i>
                            <span class="nav-text">Design Studio</span>
                            <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Soon</span>
                        </span>
                    </div>
                    <div class="nav-section">
                        <div class="nav-section-title">Analytics</div>
                        <span class="nav-item nav-disabled">
                            <i class="fas fa-chart-line"></i>
                            <span class="nav-text">Analytics</span>
                            <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Soon</span>
                        </span>
                        <span class="nav-item nav-disabled">
                            <i class="fas fa-file-alt"></i>
                            <span class="nav-text">Reports</span>
                            <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Soon</span>
                        </span>
                    </div>
                    <div class="nav-section">
                        <div class="nav-section-title">Finance</div>
                        <span class="nav-item nav-disabled">
                            <i class="fas fa-chart-pie"></i>
                            <span class="nav-text">Finance Dashboard</span>
                            <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Soon</span>
                        </span>
                        <span class="nav-item nav-disabled">
                            <i class="fas fa-money-bill-wave"></i>
                            <span class="nav-text">Expenses</span>
                            <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Soon</span>
                        </span>
                        <span class="nav-item nav-disabled">
                            <i class="fas fa-chart-line"></i>
                            <span class="nav-text">Sales</span>
                            <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Soon</span>
                        </span>
                        <span class="nav-item nav-disabled">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span class="nav-text">Financial Reports</span>
                            <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Soon</span>
                        </span>
                    </div>
                    @else
                    <!-- Show Business Operations only for NON-SALES AGENTS -->
                    @if(!Auth::user()->isSalesAgent() && !Auth::user()->isSalesRepresentative() && !Auth::user()->isProcurement() && !Auth::user()->isProdManager() && !Auth::user()->isGa() && !Auth::user()->isQa())
                    <!-- Business Operations (orders, pricing, customers, production — hidden from procurement) -->
                    <div class="nav-section">
                        <div class="nav-section-title">Business</div>
                        <a href="{{ route('orders.index') }}" class="nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="nav-text">Orders</span>
                            <span class="nav-badge">24</span>
                        </a>
                        <a href="{{ route('product-pricing.index') }}" class="nav-item {{ request()->routeIs('product-pricing.*') ? 'active' : '' }}">
                            <i class="fas fa-money-bill-wave"></i>
                            <span class="nav-text">Product Pricing</span>
                        </a>
                        <a href="{{ route('printing.public') }}" class="nav-item {{ request()->routeIs('printing.*') ? 'active' : '' }}">
                            <i class="fas fa-calculator"></i>
                            <span class="nav-text">Pricing Calculator</span>
                        </a>
                        <a href="{{ route('customers.index') }}" class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i>
                            <span class="nav-text">Customers</span>
                        </a>
                    </div>
                    @endif

                    <!-- Production Section -->
                    @if(!Auth::user()->isSalesAgent() && !Auth::user()->isSalesRepresentative() && !Auth::user()->isProcurement() && !Auth::user()->isProdManager() && !Auth::user()->isGa() && !Auth::user()->isQa())
                    <div class="nav-section">
                        <div class="nav-section-title">Production</div>
                        <a href="{{ route('production.tracking') }}" class="nav-item {{ request()->routeIs('production.tracking') ? 'active' : '' }}">
                            <i class="fas fa-cogs"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                        <a href="{{ route('sales.prototype.kanban') }}" class="nav-item {{ request()->routeIs('sales.prototype.kanban') ? 'active' : '' }}">
                            <i class="fas fa-columns"></i>
                            <span class="nav-text">Kanban Board</span>
                        </a>
                        <a href="{{ route('sales.prototype.list') }}" class="nav-item {{ request()->routeIs('sales.prototype.list') ? 'active' : '' }}">
                            <i class="fas fa-list"></i>
                            <span class="nav-text">Manager List</span>
                        </a>
                        @if(Auth::user()->isAdmin())
                        <a href="{{ route('sales.prototype.ga-order-list') }}" class="nav-item {{ request()->routeIs('sales.prototype.ga-order-list') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-list"></i>
                            <span class="nav-text">GA Job List</span>
                        </a>
                        <a href="{{ route('sales.layout-jobs.all') }}" class="nav-item {{ request()->routeIs('sales.layout-jobs.all') ? 'active' : '' }}">
                            <i class="fas fa-palette"></i>
                            <span class="nav-text">Layout Job List</span>
                        </a>
                        <a href="{{ route('sales.prototype.special-price-list') }}" class="nav-item {{ request()->routeIs('sales.prototype.special-price-list') ? 'active' : '' }}">
                            <i class="fas fa-tags"></i>
                            <span class="nav-text">Special Price</span>
                        </a>
                        @if(auth()->user() && (auth()->user()->isManager() || auth()->user()->isCoo()))
                        <a href="{{ route('sales.prototype.freebie-list') }}" class="nav-item {{ request()->routeIs('sales.prototype.freebie-list') ? 'active' : '' }}">
                            <i class="fas fa-gift"></i>
                            <span class="nav-text">Freebie List</span>
                        </a>
                        @endif
                        @endif
                        <a href="{{ route('sales.prototype.calendar') }}" class="nav-item {{ request()->routeIs('sales.prototype.calendar') ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="nav-text">Calendar</span>
                        </a>
                        <a href="{{ route('sales.prototype.refunds') }}" class="nav-item {{ request()->routeIs('sales.prototype.refunds') ? 'active' : '' }}">
                            <i class="fas fa-undo-alt"></i>
                            <span class="nav-text">Refunds</span>
                        </a>
                        <a href="{{ route('sales.verification') }}" class="nav-item {{ request()->routeIs('sales.verification') ? 'active' : '' }}">
                            <i class="fas fa-check-circle"></i>
                            <span class="nav-text">Payment Verification</span>
                        </a>
                        <a href="{{ route('procurement.orders.create') }}" class="nav-item {{ request()->routeIs('procurement.orders.create') || request()->routeIs('procurement.orders.*') ? 'active' : '' }}">
                            <i class="fas fa-plus-circle"></i>
                            <span class="nav-text">Create Order</span>
                            <span class="nav-badge new">NEW</span>
                        </a>
                    </div>
                    @endif

                    <!-- Inventory Management (visible to all including procurement) -->
                    @if(!Auth::user()->isSalesAgent() && !Auth::user()->isSalesRepresentative() && !Auth::user()->isProdManager() && !Auth::user()->isGa() && !Auth::user()->isQa())
                    <div class="nav-section">
                        <div class="nav-section-title">Supplies</div>
                        <a href="{{ route('inventory.unified') }}" class="nav-item {{ request()->routeIs('inventory.unified') ? 'active' : '' }}">
                            <i class="fas fa-boxes"></i>
                            <span class="nav-text">Inventory Management</span>
                        </a>
                    </div>
                    @endif

                    <!-- Sales Agent Navigation (Only for Sales Agents/Reps) -->
                    @if(Auth::user()->isAdmin() || Auth::user()->isSalesAgent() || Auth::user()->isSalesRepresentative())
                    <div class="nav-section">
                        <div class="nav-section-title">My Sales</div>
                        <a href="{{ route('sales.team.dashboard') }}" class="nav-item {{ request()->routeIs('sales.team.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-th-list"></i>
                            <span class="nav-text">My Sales Dashboard</span>
                        </a>
                        <a href="{{ route('sales.prototype.create') }}" class="nav-item {{ request()->routeIs('sales.prototype.create') ? 'active' : '' }}">
                            <i class="fas fa-plus-circle"></i>
                            <span class="nav-text">Add New Sale</span>
                        </a>
                        <a href="{{ route('sales.layout-jobs') }}" class="nav-item {{ request()->routeIs('sales.layout-jobs*') ? 'active' : '' }}">
                            <i class="fas fa-palette"></i>
                            <span class="nav-text">Layout Job</span>
                        </a>
                    </div>
                    @endif

                    <!-- Sales Agent: Business / Production / Design (own-customer scoping, CPO-style calendar) -->
                    @if(Auth::user()->isSalesAgent() || Auth::user()->isSalesRepresentative())
                    <div class="nav-section">
                        <div class="nav-section-title">Business</div>
                        <a href="{{ route('customers.index') }}" class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i>
                            <span class="nav-text">Customers</span>
                        </a>
                    </div>
                    <div class="nav-section">
                        <div class="nav-section-title">Production</div>
                        <a href="{{ route('sales.prototype.calendar') }}" class="nav-item {{ request()->routeIs('sales.prototype.calendar') ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="nav-text">Calendar</span>
                        </a>
                    </div>
                    <div class="nav-section">
                        <div class="nav-section-title">Design</div>
                        <span class="nav-item nav-disabled">
                            <i class="fas fa-paint-brush"></i>
                            <span class="nav-text">Design Studio</span>
                            <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Soon</span>
                        </span>
                    </div>
                    @endif

                    <!-- GA Navigation (Only for GA/Agent users) -->
                    @if(Auth::user()->isGa())
                    @php $gaIsAgent = str_contains(strtolower(Auth::user()->position ?? ''), 'agent'); @endphp
                    @if($gaIsAgent)
                    <div class="nav-section">
                        <div class="nav-section-title">Business</div>
                        <a href="{{ route('customers.index') }}" class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i>
                            <span class="nav-text">Customers</span>
                        </a>
                    </div>
                    @endif
                    <div class="nav-section">
                        <div class="nav-section-title">Production</div>
                        <a href="{{ route('sales.prototype.ga-order-list') }}" class="nav-item {{ request()->routeIs('sales.prototype.ga-order-list') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-list"></i>
                            <span class="nav-text">GA Job List</span>
                        </a>
                        <a href="{{ route('sales.prototype.backjobs') }}" class="nav-item {{ request()->routeIs('sales.prototype.backjobs') ? 'active' : '' }}">
                            <i class="fas fa-tools"></i>
                            <span class="nav-text">Backjob List</span>
                        </a>
                        <a href="{{ route('sales.prototype.calendar') }}" class="nav-item {{ request()->routeIs('sales.prototype.calendar') ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="nav-text">Calendar</span>
                        </a>
                    </div>
                    @if($gaIsAgent)
                    <div class="nav-section">
                        <div class="nav-section-title">My Sales</div>
                        <span class="nav-item nav-disabled">
                            <i class="fas fa-th-list"></i>
                            <span class="nav-text">My Sales Dashboard</span>
                            <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Soon</span>
                        </span>
                        <span class="nav-item nav-disabled">
                            <i class="fas fa-plus-circle"></i>
                            <span class="nav-text">Add New Sale</span>
                            <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Soon</span>
                        </span>
                    </div>
                    @endif
                    <div class="nav-section">
                        <div class="nav-section-title">My Layout</div>
                        <a href="{{ route('sales.layout-jobs') }}" class="nav-item {{ request()->routeIs('sales.layout-jobs') || request()->routeIs('sales.layout-jobs.create') ? 'active' : '' }}">
                            <i class="fas fa-palette"></i>
                            <span class="nav-text">Layout Job</span>
                        </a>
                    </div>
                    <div class="nav-section">
                        <div class="nav-section-title">My Work</div>
                        <a href="{{ route('sales.prototype.production-feedback.list') }}" class="nav-item {{ request()->routeIs('sales.prototype.production-feedback.list') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-check"></i>
                            <span class="nav-text">Production Feedback</span>
                        </a>
                        <a href="{{ route('sales.team.delays') }}" class="nav-item {{ request()->routeIs('sales.team.delays') ? 'active' : '' }}">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="nav-text">My Delays</span>
                        </a>
                    </div>
                    @endif

                    <!-- Artist Navigation (Only for Artists) -->
                    @if(Auth::user()->isArtist())
                    <div class="nav-section">
                        <div class="nav-section-title">My Work</div>
                        <a href="{{ route('sales.prototype.production-feedback.list') }}" class="nav-item {{ request()->routeIs('sales.prototype.production-feedback.list') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-check"></i>
                            <span class="nav-text">Production Feedback</span>
                        </a>
                    </div>
                    @endif

                    <!-- Design & Analytics (hidden from procurement) -->
                    @if(!Auth::user()->isSalesAgent() && !Auth::user()->isSalesRepresentative() && !Auth::user()->isProcurement() && !Auth::user()->isProdManager() && !Auth::user()->isGa() && !Auth::user()->isQa())
                    <div class="nav-section">
                        <div class="nav-section-title">Design</div>
                        <a href="{{ route('design.studio') }}" class="nav-item {{ request()->routeIs('design.*') ? 'active' : '' }}">
                            <i class="fas fa-paint-brush"></i>
                            <span class="nav-text">Design Studio</span>
                        </a>
                    </div>

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
                    @endif

                    <!-- Finance (visible to all including procurement) -->
                    @if(!Auth::user()->isSalesAgent() && !Auth::user()->isSalesRepresentative() && !Auth::user()->isProdManager() && !Auth::user()->isGa() && !Auth::user()->isQa())
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
                        <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="fas fa-users-cog"></i>
                            <span class="nav-text">User Management</span>
                        </a>
                        <a href="{{ route('sales-agents.index') }}" class="nav-item {{ request()->routeIs('sales-agents.*') ? 'active' : '' }}">
                            <i class="fas fa-user-tie"></i>
                            <span class="nav-text">Sales Agents</span>
                        </a>
                        <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                            <i class="fas fa-sliders-h"></i>
                            <span class="nav-text">System Settings</span>
                        </a>
                    </div>
                    @endif
                    @endif
                    @endif

                    <!-- User Account -->
                    <div class="nav-section">
                        <div class="nav-section-title">Account</div>
                        @if(Auth::user()->isCoo() || Auth::user()->isCpo() || Auth::user()->isCmo() || Auth::user()->isProdManager() || Auth::user()->isSalesAgent() || Auth::user()->isSalesRepresentative() || Auth::user()->isGa())
                        <span class="nav-item nav-disabled">
                            <i class="fas fa-user"></i>
                            <span class="nav-text">Profile</span>
                            <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Soon</span>
                        </span>
                        @else
                        <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <i class="fas fa-user"></i>
                            <span class="nav-text">Profile</span>
                        </a>
                        @endif
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
                            <div class="dropdown d-inline-block">
                                <button class="header-btn dropdown-toggle" data-bs-toggle="dropdown" title="Notifications">
                                    <i class="fas fa-bell"></i>
                                    @if($navNotificationCount > 0 || $navPendingVerifications > 0 || ($navSaleVerificationCount ?? 0) > 0)
                                    <span class="notification-badge">{{ $navNotificationCount + $navPendingVerifications + ($navSaleVerificationCount ?? 0) }}</span>
                                    @endif
                                </button>
                                <div class="dropdown-menu dropdown-menu-end shadow" style="min-width: 380px; max-height: 480px; overflow-y: auto;">
                                    @if($navSaleVerificationCount > 0)
                                    <div class="px-3 py-2 border-bottom" style="background:#eff6ff;">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <i class="fas fa-bell text-primary"></i>
                                            <strong class="small">{{ $navSaleVerificationCount }} payment verification request(s)</strong>
                                        </div>
                                        @foreach($navSaleVerificationNotifs as $sv)
                                        <a href="{{ route('sales.verification') }}" class="text-decoration-none d-block py-1" style="border-bottom:1px dashed #dbeafe;">
                                            <div class="small"><strong>{{ $sv->title }}</strong>@if($sv->reminder_count > 1) <span class="badge bg-primary" style="font-size:9px;">Request #{{ $sv->reminder_count }}</span>@endif</div>
                                            <div class="small text-muted text-truncate">{{ $sv->message }}</div>
                                            <small class="text-muted" style="font-size:10px;">{{ $sv->fromUser?->display_label }} &middot; {{ $sv->created_at->diffForHumans() }}</small>
                                        </a>
                                        @endforeach
                                        <a href="{{ route('sales.verification') }}" class="small text-primary text-decoration-none d-block mt-1">Go to Payment Verification &rarr;</a>
                                    </div>
                                    @endif
                                    @if($navPendingVerifications > 0)
                                    <div class="px-3 py-2 bg-warning bg-opacity-10 border-bottom">
                                        <a href="{{ route('procurement.orders.index', ['status' => 'for_verification']) }}" class="text-decoration-none d-flex align-items-center gap-2">
                                            <i class="fas fa-clipboard-check text-warning"></i>
                                            <strong class="small">{{ $navPendingVerifications }} order(s)</strong>
                                            <span class="small text-muted">pending verification</span>
                                            <i class="fas fa-arrow-right ms-auto small text-muted"></i>
                                        </a>
                                    </div>
                                    @endif
                                    <h6 class="dropdown-header small text-muted text-uppercase">Procurement Notifications</h6>
                                    @forelse($navUnreadNotifications as $n)
                                    <a class="dropdown-item" href="{{ route('procurement.orders.show', $n->procurement_order_id) }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div style="max-width: 280px;">
                                                <span class="badge bg-{{ $n->type === 'urgent' ? 'danger' : ($n->type === 'reminder' ? 'warning' : 'info') }} me-1" style="font-size:9px;">{{ ucfirst($n->type) }}</span>
                                                <strong class="small">{{ $n->title }}</strong>
                                                @if($n->message)<p class="mb-0 small text-muted text-truncate">{{ $n->message }}</p>@endif
                                                <small class="text-muted" style="font-size:10px;">{{ $n->fromUser?->display_label }} &middot; {{ $n->created_at->diffForHumans() }}</small>
                                            </div>
                                            <form method="POST" action="{{ route('procurement.notifications.read', $n->id) }}" class="d-inline ms-1">
                                                @csrf @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-link text-muted p-0" title="Mark as read"><i class="fas fa-check-circle"></i></button>
                                            </form>
                                        </div>
                                    </a>
                                    @if(!$loop->last)<hr class="my-1">@endif
                                    @empty
                                    <div class="text-center py-3 text-muted small">
                                        <i class="fas fa-check-circle fa-2x mb-1"></i>
                                        <p class="mb-0">All caught up!</p>
                                    </div>
                                    @endforelse
                                    @if($navNotificationCount > 0)
                                    <div class="text-center py-1 border-top">
                                        <a href="{{ route('procurement.orders.index') }}" class="small text-muted">View all orders</a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <button class="header-btn" title="Help">
                                <i class="fas fa-question-circle"></i>
                            </button>
                        </div>

                        <!-- User Menu -->
                        <div class="user-menu">
                            <button class="user-menu-toggle" onclick="toggleUserMenu()">
                                <div class="user-avatar-small clickable-avatar" title="Click to change profile picture" onclick="event.stopPropagation(); document.getElementById('avatarInput').click();">
                                    @if(Auth::user()->avatar_url)
                                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}">
                                    @else
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    @endif
                                </div>
                                <span class="user-name-short">@if(Auth::user()->position){{ Auth::user()->first_name }} · {{ Auth::user()->position }}@elseif(Auth::user()->isAdmin())Administrator@elseif(Auth::user()->isSalesAgent())Sales Agent@elseif(Auth::user()->isSalesRepresentative())Sales Representative@elseif(Auth::user()->isStaff())Staff@elseif(Auth::user()->isArtist())Artist@elseif(Auth::user()->isCustomer())Customer@else{{ Auth::user()->first_name }}@endif</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            
                            <div class="user-menu-dropdown" id="userMenu">
                                <div class="user-menu-header">
                                    <div class="user-avatar-medium clickable-avatar" title="Click to change profile picture" onclick="event.stopPropagation(); document.getElementById('avatarInput').click();">
                                        @if(Auth::user()->avatar_url)
                                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}">
                                        @else
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="user-name-medium">{{ Auth::user()->name }}</div>
                                        @if(Auth::user()->position)
                                        <div style="font-size:12px;font-weight:700;color:#7c3aed;margin:2px 0;">{{ Auth::user()->position }}</div>
                                        @endif
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

            // Avatar click-to-upload: auto-submit when a file is selected
            function setupAvatarUpload() {
                const avatarForm = document.getElementById('avatarUploadForm');
                const avatarInput = document.getElementById('avatarInput');
                if (!avatarForm || !avatarInput) return;
                avatarInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        avatarForm.submit();
                    }
                });
            }
            document.addEventListener('DOMContentLoaded', setupAvatarUpload);

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
        
        <!-- Hidden avatar upload form (triggered by clicking the avatar box) -->
        @auth
        <form id="avatarUploadForm" action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" style="display:none;">
            @csrf
            <input type="file" name="avatar" id="avatarInput" accept="image/*">
        </form>
        @endauth

        @stack('scripts')
    </body>
</html>