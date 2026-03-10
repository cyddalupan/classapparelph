@extends('layouts.app')

@section('page-title', 'Sales')

@section('content')
<div class="page-header">
    <div class="header-content">
        <h1 class="page-title">
            <i class="fas fa-chart-line"></i>
            Sales
        </h1>
        <p class="page-subtitle">Track and manage sales transactions</p>
    </div>
    <div class="header-actions">
        <!-- SIMPLE LINK - NO JAVASCRIPT - WILL DEFINITELY WORK -->
        <a href="/finance/sales/create" class="btn btn-success" style="text-decoration: none;">
            <i class="fas fa-plus"></i> Add Sale
        </a>
        <button class="btn btn-outline" onclick="exportSales()">
            <i class="fas fa-download"></i> Export
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

<!-- Sales Summary -->
<div class="stats-grid">
    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">₱ {{ number_format($totalSales, 2) }}</div>
            <div class="stat-label">Total Sales</div>
            <div class="stat-change positive">
                <i class="fas fa-chart-line"></i> Real-time data
            </div>
        </div>
    </div>

    <div class="stat-card primary">
        <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $totalOrders }}</div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-change positive">
                <i class="fas fa-chart-line"></i> Real-time data
            </div>
        </div>
    </div>

    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $completedSales }}</div>
            <div class="stat-label">Completed Sales</div>
            <div class="stat-change positive">
                <i class="fas fa-chart-line"></i> Real-time data
            </div>
        </div>
    </div>

    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">₱ {{ number_format($pendingPayments, 2) }}</div>
            <div class="stat-label">Pending Payments</div>
            <div class="stat-change warning">
                <i class="fas fa-exclamation-circle"></i> Requires attention
            </div>
        </div>
    </div>
</div>

<!-- Sales Table -->
<div class="section">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-list"></i>
            Recent Sales
        </h2>
        <div class="section-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search sales..." class="search-input">
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                    <td>#{{ str_pad($sale->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $sale->customer_name }}</td>
                    <td>{{ $sale->product_name }}</td>
                    <td>{{ $sale->quantity }}</td>
                    <td class="amount positive">₱ {{ number_format($sale->total_amount, 2) }}</td>
                    <td>
                        @php
                            $statusClass = 'pending';
                            if ($sale->sale_status == 'completed') $statusClass = 'completed';
                            elseif ($sale->sale_status == 'processing') $statusClass = 'processing';
                            elseif ($sale->sale_status == 'shipped') $statusClass = 'shipped';
                            elseif ($sale->sale_status == 'delivered') $statusClass = 'delivered';
                            elseif ($sale->sale_status == 'cancelled') $statusClass = 'cancelled';
                            elseif ($sale->sale_status == 'refunded') $statusClass = 'refunded';
                        @endphp
                        <span class="status {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $sale->sale_status)) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('sales.show', $sale) }}" class="btn-icon" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('sales.edit', $sale) }}" class="btn-icon" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('sales.destroy', $sale) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon" title="Delete" onclick="return confirm('Are you sure you want to delete this sale?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-shopping-cart fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No sales records found. <a href="{{ route('sales.create') }}">Add your first sale</a></p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($sales->hasPages())
        <div class="pagination">
            <div class="pagination-info">
                Showing {{ $sales->firstItem() }} to {{ $sales->lastItem() }} of {{ $sales->total() }} entries
            </div>
            <div class="pagination-links">
                {{ $sales->links('pagination::simple') }}
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    /* Reuse styles from expenses page */
    .stats-grid,
    .section,
    .table-container,
    .data-table,
    .search-box,
    .search-input,
    .amount,
    .status,
    .btn-icon {
        /* These styles are already defined in expenses.css */
    }
    
    /* ============================================
       COMPREHENSIVE MOBILE CSS FOR FINANCE PAGES
       ============================================ */

    /* Base mobile styles (applies to all screens ≤768px) */
    @media (max-width: 768px) {
        /* 1. PAGE HEADER - Stack vertically */
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
        }
        
        .header-content {
            width: 100%;
        }
        
        .header-actions {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .header-actions .btn {
            width: 100%;
            justify-content: center;
        }
        
        /* 2. PAGE TITLES - Adjust font sizes */
        .page-title {
            font-size: 1.5rem;
            line-height: 1.3;
        }
        
        .page-subtitle {
            font-size: 0.9rem;
        }
        
        /* 3. STATS GRID - Single column */
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
            padding: 1rem;
        }
        
        .stat-card {
            padding: 1.25rem;
        }
        
        .stat-value {
            font-size: 1.5rem;
        }
        
        /* 4. FILTERS - Stack vertically */
        .filters-card {
            padding: 1rem;
            margin: 1rem;
        }
        
        .filters-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .filter-group {
            width: 100%;
        }
        
        .filters-actions {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .filters-actions .btn {
            width: 100%;
        }
        
        /* 5. TABLES - Horizontal scrolling */
        .table-container {
            overflow-x: auto;
            margin: 0 -1rem;
            padding: 0 1rem;
        }
        
        .data-table {
            width: 100%;
            font-size: 0.85rem;
        }
        
        .data-table th,
        .data-table td {
            padding: 0.75rem 0.5rem;
            white-space: nowrap;
        }
        
        /* 6. ACTION BUTTONS - Adjust size */
        .action-buttons {
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .btn-icon {
            width: 32px;
            height: 32px;
            font-size: 0.85rem;
        }
        
        /* 7. FORMS - Single column */
        .form-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .form-group.full-width {
            grid-column: 1;
        }
        
        .form-actions {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .form-actions .btn {
            width: 100%;
        }
        
        /* 8. MODALS - Full screen on mobile */
        .modal-container {
            width: 95%;
            max-width: 95%;
            max-height: 85vh;
            margin: 0.5rem;
            border-radius: 12px;
        }
        
        .modal-header {
            padding: 1rem 1.25rem;
        }
        
        .modal-body {
            padding: 1.25rem;
        }
        
        .modal-title {
            font-size: 1.25rem;
        }
        
        /* 9. CHARTS & GRAPHS - Adjust sizing */
        .chart-container {
            height: 250px;
            padding: 1rem;
        }
        
        /* 10. SEARCH BOX - Full width */
        .search-box {
            width: 100%;
            margin: 0 1rem;
        }
        
        .search-input {
            width: 100%;
        }
        
        /* 11. SECTION LAYOUTS - Stack vertically */
        .insights-grid,
        .categories-breakdown,
        .report-grid,
        .sales-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        /* 12. CARD LAYOUTS - Adjust padding */
        .section,
        .content-card,
        .report-card {
            padding: 1rem;
            margin: 1rem 0;
        }
        
        .card-header {
            padding: 1rem;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        /* 13. EMPTY STATES - Adjust sizing */
        .empty-state {
            padding: 2rem 1rem;
        }
        
        .empty-state i {
            font-size: 2.5rem;
        }
        
        .empty-state h4 {
            font-size: 1.25rem;
        }
        
        /* 14. PAGINATION - Stack buttons */
        .pagination {
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem;
        }
        
        .pagination .btn {
            min-width: 40px;
            height: 40px;
            padding: 0.5rem;
        }
        
        /* 15. STATUS BADGES - Adjust sizing */
        .status-badge,
        .category-badge {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        /* 16. AMOUNT DISPLAY - Adjust font sizes */
        .amount {
            font-size: 0.95rem;
        }
        
        .amount.large {
            font-size: 1.25rem;
        }
        
        /* 17. DATE PICKERS - Full width */
        .date-range-picker {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .date-input {
            width: 100%;
        }
        
        /* 18. TOOLTIPS - Adjust for touch */
        [title] {
            position: relative;
        }
        
        [title]:hover::after {
            content: attr(title);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            white-space: nowrap;
            z-index: 1000;
            margin-bottom: 0.5rem;
        }
        
        /* 19. DROPDOWNS - Full width */
        .select-input,
        .form-control {
            width: 100%;
        }
        
        /* 20. BUTTON GROUPS - Stack vertically */
        .btn-group {
            flex-direction: column;
            width: 100%;
        }
        
        .btn-group .btn {
            width: 100%;
            border-radius: 6px;
            margin: 0.25rem 0;
        }
    }

    /* Extra small screens (≤480px) */
    @media (max-width: 480px) {
        /* Even more compact styles */
        .page-title {
            font-size: 1.25rem;
        }
        
        .stat-card {
            padding: 1rem;
        }
        
        .stat-value {
            font-size: 1.25rem;
        }
        
        .data-table {
            width: 100%;
            font-size: 0.8rem;
        }
        
        .modal-container {
            width: 98%;
            max-width: 98%;
            margin: 0.25rem;
            border-radius: 8px;
        }
        
        .chart-container {
            height: 200px;
        }
        
        .btn {
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }
        
        .btn-icon {
            width: 28px;
            height: 28px;
            font-size: 0.8rem;
        }
        
        /* Hide less important columns on very small screens */
        .data-table th:nth-child(4),
        .data-table td:nth-child(4),
        .data-table th:nth-child(5),
        .data-table td:nth-child(5) {
            display: none;
        }
    }

    /* Tablet screens (769px to 1024px) */
    @media (min-width: 769px) and (max-width: 1024px) {
        /* Adjust for tablet */
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .filters-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .form-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .modal-container {
            max-width: 90%;
        }
    }

    /* Touch device optimizations */
    @media (hover: none) and (pointer: coarse) {
        /* Improve touch targets */
        .btn,
        .btn-icon,
        .select-input,
        .form-control,
        .pagination .btn {
            min-height: 44px;
            min-width: 44px;
        }
        
        /* Increase spacing for touch */
        .action-buttons {
            gap: 0.75rem;
        }
        
        /* Prevent text selection on interactive elements */
        .btn,
        .btn-icon,
        .select-input {
            user-select: none;
            -webkit-user-select: none;
        }
        
        /* Improve scrolling */
        .table-container {
            -webkit-overflow-scrolling: touch;
        }
    }

    /* Print styles */
    @media print {
        .header-actions,
        .filters-card,
        .search-box,
        .action-buttons,
        .btn,
        .modal-overlay {
            display: none !important;
        }
        
        .page-header {
            flex-direction: row;
        }
        
        .data-table {
            min-width: auto;
            font-size: 10pt;
        }
        
        .stats-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        @media (max-width: 768px) {
            .stat-card,
            .content-card,
            .modal-container {
                background: #1e293b;
                border-color: #334155;
            }
            
            .data-table {
                background: #1e293b;
                color: #e2e8f0;
            }
            
            .data-table th {
                background: #0f172a;
                color: #cbd5e1;
            }
            
            .data-table tr:hover {
                background: #334155;
            }
        }
    }

    /* Animation for mobile transitions */
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    /* Utility classes for mobile */
    .mobile-only {
        display: none;
    }

    .mobile-hidden {
        display: block;
    }

    @media (max-width: 768px) {
        .mobile-only {
            display: block;
        }
        
        .mobile-hidden {
            display: none;
        }
        
        /* Add bottom padding for mobile safe areas */
        .finance-content {
            padding-bottom: env(safe-area-inset-bottom, 1rem);
        }
    }

    /* Fix for iOS Safari */
    @supports (-webkit-touch-callout: none) {
        @media (max-width: 768px) {
            .modal-container {
                max-height: -webkit-fill-available;
            }
            
            .table-container {
                -webkit-overflow-scrolling: touch;
            }
        }
    }

    /* Fix for Android Chrome */
    @supports not (-webkit-touch-callout: none) {
        @media (max-width: 768px) {
            .modal-container {
                max-height: calc(100vh - 2rem);
            }
        }
    }
</style>

<script>
    function exportSales() {
        alert('Exporting sales to CSV... This is a placeholder.');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                console.log('Searching sales for:', this.value);
            });
        }

        // Table actions
        document.querySelectorAll('.data-table tbody tr').forEach(row => {
            const viewBtn = row.querySelector('.btn-icon[title="View Details"]');
            const invoiceBtn = row.querySelector('.btn-icon[title="Invoice"]');

            if (viewBtn) {
                viewBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const orderNum = row.cells[1].textContent;
                    alert(`Viewing sale: ${orderNum}`);
                });
            }

            if (invoiceBtn) {
                invoiceBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const orderNum = row.cells[1].textContent;
                    alert(`Generating invoice for: ${orderNum}`);
                });
            }
        });
    });
</script>
@endsection