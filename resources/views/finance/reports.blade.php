@extends('layouts.app')

@section('page-title', 'Financial Reports')

@section('content')
<div class="page-header">
    <div class="header-content">
        <h1 class="page-title">
            <i class="fas fa-file-invoice-dollar"></i>
            Financial Reports
        </h1>
        <p class="page-subtitle">Generate and analyze financial reports</p>
    </div>
    <div class="header-actions">
        <button class="btn btn-primary" onclick="exportCSV()">
            <i class="fas fa-download"></i> Export CSV
        </button>
        <button class="btn btn-success" onclick="generatePDF()">
            <i class="fas fa-file-pdf"></i> Export PDF
        </button>
    </div>
</div>

<!-- Report Filters -->
<div class="filters-card">
    <div class="filters-header">
        <h3 class="filters-title">
            <i class="fas fa-filter"></i>
            Report Filters
        </h3>
        <button class="btn btn-text" onclick="clearFilters()">
            Clear All
        </button>
    </div>
    
    <form id="reportFilters" class="filters-grid">
        <div class="filter-group">
            <label class="filter-label">Report Type</label>
            <select class="select-input" id="reportType">
                <option value="summary">Summary Report</option>
                <option value="monthly">Monthly Trends</option>
                <option value="category">Category Analysis</option>
                <option value="profit-loss">Profit & Loss</option>
                <option value="cashflow">Cash Flow</option>
                <option value="custom">Custom Report</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Date Range</label>
            <select class="select-input" id="dateRange">
                <option value="7d">Last 7 Days</option>
                <option value="30d" selected>Last 30 Days</option>
                <option value="90d">Last 90 Days</option>
                <option value="qtd">Quarter to Date</option>
                <option value="ytd">Year to Date</option>
                <option value="custom">Custom Range</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Start Date</label>
            <input type="date" class="input-field" id="startDate" value="2026-02-01">
        </div>

        <div class="filter-group">
            <label class="filter-label">End Date</label>
            <input type="date" class="input-field" id="endDate" value="2026-02-28">
        </div>

        <div class="filter-group">
            <label class="filter-label">Report Format</label>
            <select class="select-input" id="reportFormat">
                <option value="detailed">Detailed</option>
                <option value="summary">Summary</option>
                <option value="charts">Charts Only</option>
                <option value="comparison">Comparison</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Include Data</label>
            <div class="checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" checked> Sales
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" checked> Expenses
                </label>
                <label class="checkbox-label">
                    <input type="checkbox"> Taxes
                </label>
                <label class="checkbox-label">
                    <input type="checkbox"> Profit Margins
                </label>
            </div>
        </div>
    </form>

    <div class="filters-actions">
        <button class="btn btn-primary" onclick="generateReport()">
            <i class="fas fa-chart-bar"></i> Generate Report
        </button>
        <button class="btn btn-outline" onclick="saveReport()">
            <i class="fas fa-save"></i> Save Template
        </button>
    </div>
</div>

<!-- Report Preview -->
<div class="section">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-chart-line"></i>
            Report Preview
        </h2>
        <div class="section-actions">
            <button class="btn btn-outline" onclick="refreshPreview()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">₱ 245,800</div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 12.5% from last period
                </div>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">₱ 89,450</div>
                <div class="stat-label">Total Expenses</div>
                <div class="stat-change negative">
                    <i class="fas fa-arrow-up"></i> 8.2% from last period
                </div>
            </div>
        </div>

        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">₱ 156,350</div>
                <div class="stat-label">Net Profit</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 15.3% from last period
                </div>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">63.6%</div>
                <div class="stat-label">Profit Margin</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 3.8% improvement
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-container">
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Monthly Revenue Trend</h3>
                <div class="chart-legend">
                    <div class="legend-item">
                        <span class="legend-color revenue"></span>
                        <span>Revenue</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color target"></span>
                        <span>Target</span>
                    </div>
                </div>
            </div>
            <div class="chart-placeholder">
                <div class="placeholder-text">
                    <i class="fas fa-chart-line"></i>
                    <p>Monthly Revenue Chart</p>
                    <small>Line chart showing revenue trends over time</small>
                </div>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Expense Categories</h3>
                <div class="chart-legend">
                    <div class="legend-item">
                        <span class="legend-color supplies"></span>
                        <span>Supplies</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color salaries"></span>
                        <span>Salaries</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color utilities"></span>
                        <span>Utilities</span>
                    </div>
                </div>
            </div>
            <div class="chart-placeholder">
                <div class="placeholder-text">
                    <i class="fas fa-chart-pie"></i>
                    <p>Expense Categories Chart</p>
                    <small>Pie chart showing expense distribution</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Report -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-table"></i>
                Detailed Report Data
            </h2>
            <div class="section-actions">
                <button class="btn btn-outline" onclick="toggleDetails()">
                    <i class="fas fa-eye"></i> Show/Hide Details
                </button>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Revenue</th>
                        <th>Expenses</th>
                        <th>Profit</th>
                        <th>Margin</th>
                        <th>Orders</th>
                        <th>Avg. Order Value</th>
                        <th>Trend</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Feb 2026</td>
                        <td class="amount positive">₱ 245,800</td>
                        <td class="amount negative">₱ 89,450</td>
                        <td class="amount positive">₱ 156,350</td>
                        <td><span class="status success">63.6%</span></td>
                        <td>187</td>
                        <td class="amount">₱ 1,314</td>
                        <td><span class="trend up">↑ 12.5%</span></td>
                    </tr>
                    <tr>
                        <td>Jan 2026</td>
                        <td class="amount positive">₱ 218,500</td>
                        <td class="amount negative">₱ 82,700</td>
                        <td class="amount positive">₱ 135,800</td>
                        <td><span class="status success">62.1%</span></td>
                        <td>163</td>
                        <td class="amount">₱ 1,340</td>
                        <td><span class="trend up">↑ 8.3%</span></td>
                    </tr>
                    <tr>
                        <td>Dec 2025</td>
                        <td class="amount positive">₱ 201,750</td>
                        <td class="amount negative">₱ 78,300</td>
                        <td class="amount positive">₱ 123,450</td>
                        <td><span class="status success">61.2%</span></td>
                        <td>148</td>
                        <td class="amount">₱ 1,363</td>
                        <td><span class="trend up">↑ 5.2%</span></td>
                    </tr>
                    <tr>
                        <td>Nov 2025</td>
                        <td class="amount positive">₱ 191,800</td>
                        <td class="amount negative">₱ 74,500</td>
                        <td class="amount positive">₱ 117,300</td>
                        <td><span class="status success">61.1%</span></td>
                        <td>142</td>
                        <td class="amount">₱ 1,351</td>
                        <td><span class="trend down">↓ 1.8%</span></td>
                    </tr>
                    <tr>
                        <td>Oct 2025</td>
                        <td class="amount positive">₱ 195,300</td>
                        <td class="amount negative">₱ 75,800</td>
                        <td class="amount positive">₱ 119,500</td>
                        <td><span class="status success">61.2%</span></td>
                        <td>145</td>
                        <td class="amount">₱ 1,347</td>
                        <td><span class="trend up">↑ 3.1%</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Insights & Recommendations -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-lightbulb"></i>
                Insights & Recommendations
            </h2>
        </div>

        <div class="insights-grid">
            <div class="insight-card positive">
                <div class="insight-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="insight-content">
                    <h3 class="insight-title">Revenue Growth Strong</h3>
                    <p class="insight-text">Revenue has grown 12.5% month-over-month, exceeding target by 4.2%.</p>
                    <div class="insight-action">
                        <button class="btn btn-text">View Details</button>
                    </div>
                </div>
            </div>

            <div class="insight-card warning">
                <div class="insight-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="insight-content">
                    <h3 class="insight-title">Expense Increase</h3>
                    <p class="insight-text">Supplies expenses increased by 15.3%. Consider bulk purchasing.</p>
                    <div class="insight-action">
                        <button class="btn btn-text">Review Suppliers</button>
                    </div>
                </div>
            </div>

            <div class="insight-card info">
                <div class="insight-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div class="insight-content">
                    <h3 class="insight-title">Margin Improvement</h3>
                    <p class="insight-text">Profit margin improved to 63.6%, highest in 6 months.</p>
                    <div class="insight-action">
                        <button class="btn btn-text">Analyze Factors</button>
                    </div>
                </div>
            </div>

            <div class="insight-card success">
                <div class="insight-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="insight-content">
                    <h3 class="insight-title">Customer Retention High</h3>
                    <p class="insight-text">83.4% customer retention rate indicates strong satisfaction.</p>
                    <div class="insight-action">
                        <button class="btn btn-text">View Survey</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Reuse existing styles from finance pages */
    .filters-card,
    .filters-header,
    .filters-title,
    .filters-grid,
    .filter-group,
    .filter-label,
    .select-input,
    .filters-actions,
    .stats-grid,
    .stat-card,
    .stat-icon,
    .stat-content,
    .stat-value,
    .stat-label,
    .stat-change,
    .section,
    .section-header,
    .section-title,
    .section-actions,
    .charts-container,
    .chart-card,
    .chart-header,
    .chart-title,
    .chart-legend,
    .legend-item,
    .legend-color,
    .chart-placeholder,
    .placeholder-text,
    .table-container,
    .data-table,
    .amount,
    .status,
    .trend {
        /* These styles are already defined in finance CSS */
    }

    .input-field {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-family: 'Inter', sans-serif;
        color: var(--dark);
        transition: var(--transition);
    }

    .input-field:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--dark);
        cursor: pointer;
    }

    .checkbox-label input[type="checkbox"] {
        width: 16px;
        height: 16px;
        border: 2px solid #e2e8f0;
        border-radius: 4px;
        cursor: pointer;
    }

    .checkbox-label input[type="checkbox"]:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .trend {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
    }

    .trend.up {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .trend.down {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .insights-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .insight-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        border-left: 4px solid;
        transition: var(--transition);
    }

    .insight-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .insight-card.positive {
        border-left-color: var(--success);
    }

    .insight-card.warning {
        border-left-color: #f59e0b;
    }

    .insight-card.info {
        border-left-color: #3b82f6;
    }

    .insight-card.success {
        border-left-color: var(--success);
    }

    .insight-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    .insight-card.positive .insight-icon {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .insight-card.warning .insight-icon {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .insight-card.info .insight-icon {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .insight-card.success .insight-icon {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .insight-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .insight-text {
        font-size: 0.875rem;
        color: var(--gray);
        margin-bottom: 1rem;
        line-height: 1.5;
    }

    .insight-action {
        display: flex;
        justify-content: flex-end;
    }

    .legend-color.target {
        background: #8b5cf6;
    }

    .legend-color.supplies {
        background: #3b82f6;
    }

    .legend-color.salaries {
        background: #f59e0b;
    }

    .legend-color.utilities {
        background: var(--success);
    }

    .status.success {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .insights-grid {
            grid-template-columns: 1fr;
        }

        .filters-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    function exportCSV() {
        alert('Exporting report to CSV... This is a placeholder.');
    }

    function generatePDF() {
        alert('Generating PDF report... This is a placeholder.');
    }

    function clearFilters() {
        document.querySelectorAll('.select-input').forEach(select => {
            select.value = '';
        });
        document.querySelectorAll('input[type="date"]').forEach(input => {
            input.value = '';
        });
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
        });
        alert('Filters cleared.');
    }

    function generateReport() {
        const reportType = document.getElementById('reportType').value;
        const dateRange = document.getElementById('dateRange').value;
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        const format = document.getElementById('reportFormat').value;
        
        console.log('Generating report:', { reportType, dateRange, startDate, endDate, format });
        alert(`Generating ${reportType} report for ${dateRange}... This is a placeholder.`);
    }

    function saveReport() {
        const reportType = document.getElementById('reportType').value;
        alert(`Saving "${reportType}" report template... This is a placeholder.`);
    }

    function refreshPreview() {
        alert('Refreshing report preview... This is a placeholder.');
    }

    function toggleDetails() {
        const detailsSection = document.querySelector('.table-container');
        if (detailsSection) {
            detailsSection.style.display = detailsSection.style.display === 'none' ? 'block' : 'none';
            alert('Toggling detailed view...');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Date range selector logic
        const dateRangeSelect = document.getElementById('dateRange');
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');

        if (dateRangeSelect && startDateInput && endDateInput) {
            dateRangeSelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    startDateInput.disabled = false;
                    endDateInput.disabled = false;
                } else {
                    startDateInput.disabled = true;
                    endDateInput.disabled = true;
                    
                    // Set dates based on selection
                    const today = new Date();
                    const endDate = new Date(today);
                    
                    switch(this.value) {
                        case '7d':
                            startDate.setDate(today.getDate() - 7);
                            break;
                        case '30d':
                            startDate.setDate(today.getDate() - 30);
                            break;
                        case '90d':
                            startDate.setDate(today.getDate() - 90);
                            break;
                        case 'qtd':
                            // Quarter to date logic
                            const quarter = Math.floor((today.getMonth() + 3) / 3);
                            const quarterStartMonth = (quarter - 1) * 3;
                            startDate.setMonth(quarterStartMonth, 1);
                            break;
                        case 'ytd':
                            startDate.setMonth(0, 1);
                            break;
                    }
                }
            });
        }

        // Insight card actions
        document.querySelectorAll('.insight-action .btn-text').forEach(btn => {
            btn.addEventListener('click', function() {
                const insightTitle = this.closest('.insight-card').querySelector('.insight-title').textContent;
                alert(`Action for: ${insightTitle}`);
            });
        });

        // Table row click
        document.querySelectorAll('.data-table tbody tr').forEach(row => {
            row.addEventListener('click', function() {
                const month = this.cells[0].textContent;
                console.log('Viewing details for:', month);
            });
        });
    });
</script>
@endsection