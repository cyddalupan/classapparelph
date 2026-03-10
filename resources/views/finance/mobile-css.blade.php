<style>
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
        min-width: 600px;
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
        min-width: 700px;
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