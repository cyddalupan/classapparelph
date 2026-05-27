@extends('layouts.app')

@section('title', 'Manager List — All Orders')

@push('styles')
<style>
    .pipeline-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .pipeline-table th {
        background: #f8f9fa;
        padding: 10px 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    .pipeline-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }
    .pipeline-table tr:hover td {
        background: #f0f7ff;
    }
    .pipeline-table tr {
        cursor: pointer;
    }

    /* Pipeline line */
    .pipeline {
        display: flex;
        align-items: center;
        gap: 0;
        min-width: 320px;
    }
    .pipeline-step {
        display: flex;
        align-items: center;
    }
    .pipeline-dot {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid #ccc;
        background: #fff;
        flex-shrink: 0;
        transition: all 0.2s;
    }
    .pipeline-dot.completed {
        background: #28a745;
        border-color: #28a745;
    }
    .pipeline-dot.active {
        background: #007bff;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
    }
    .pipeline-dot.completed.active {
        background: #28a745;
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40,167,69,0.25);
    }
    .pipeline-line {
        width: 24px;
        height: 2px;
        background: #ccc;
        flex-shrink: 0;
    }
    .pipeline-line.completed {
        background: #28a745;
    }

    /* Dept badge */
    .dept-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
        color: #fff;
    }

    /* Status label */
    .status-label {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
    }

    /* Header area */
    .list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .list-header h2 {
        margin: 0;
    }
    .list-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    /* Search & filter */
    .filter-bar {
        display: flex;
        gap: 10px;
        margin-bottom: 16px;
        flex-wrap: wrap;
        align-items: center;
    }
    .filter-bar input,
    .filter-bar select {
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 13px;
    }
    .filter-bar input { min-width: 200px; }
</style>
@endpush

@section('content')
<div class="container py-4">

    <!-- Header -->
    <div class="list-header">
        <h2>📋 Manager's Order List</h2>
        <div class="list-actions">
            <a href="{{ route('sales.prototype.kanban') }}" class="btn btn-outline-primary btn-sm">📊 Kanban Board</a>
            <a href="{{ route('sales.prototype.create') }}" class="btn btn-primary btn-sm">➕ New Order</a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <input type="text" id="searchInput" placeholder="Search customer, sales #, phone..." onkeyup="filterTable()">
        <select id="deptFilter" onchange="filterTable()">
            <option value="">All Departments</option>
            <option value="1">iPrint</option>
            <option value="2">Consol</option>
            <option value="3">Cinco</option>
            <option value="4">Class</option>
            <option value="5">MTO</option>
            <option value="6">Other</option>
        </select>
        <select id="statusFilter" onchange="filterTable()">
            <option value="">All Statuses</option>
            <option value="new">New</option>
            <option value="design">Design</option>
            <option value="production">Production</option>
            <option value="quality_check">Quality Check</option>
            <option value="ready_for_delivery">Ready for Delivery</option>
            <option value="delivered">Delivered</option>
            <option value="completed">Completed</option>
        </select>
        <span class="text-muted" style="font-size:13px;">{{ $sales->total() }} orders</span>
    </div>

    <!-- Table -->
    <div style="overflow-x:auto;">
        <table class="pipeline-table" id="orderTable">
            <thead>
                <tr>
                    <th>Sales #</th>
                    <th>Customer</th>
                    <th>Department</th>
                    <th>Total</th>
                    <th>Deposit</th>
                    <th>Payment</th>
                    <th>Progress</th>
                    <th>Agent</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    @php
                        $statusIndex = array_search($sale->kanban_status ?? 'new', $kanbanStatuses);
                        if ($statusIndex === false) $statusIndex = 0;
                        $isActive = false;
                    @endphp
                    <tr onclick="window.location.href='{{ route('sales.prototype.edit', $sale->id) }}'">
                        <td><strong>{{ $sale->sales_number }}</strong></td>
                        <td>{{ $sale->customer_name ?: '—' }}</td>
                        <td>
                            <span class="dept-badge" style="background:{{ $departmentColors[$sale->department_id] ?? '#6c757d' }};">
                                {{ $departmentLabels[$sale->department_id] ?? 'Unknown' }}
                            </span>
                        </td>
                        <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                        <td>₱{{ number_format($sale->deposit_paid, 2) }}</td>
                        <td>
                            @if($sale->payment_status === 'verified')
                                <span class="badge bg-success">✅ Paid</span>
                            @elseif($sale->payment_status === 'pending' && $sale->payment_account_id)
                                <span class="badge bg-warning text-dark">⏳ Pending</span>
                            @elseif($sale->payment_status === 'rejected')
                                <span class="badge bg-danger">❌ Rejected</span>
                            @else
                                <span class="badge bg-secondary">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="pipeline">
                                @foreach($kanbanStatuses as $i => $status)
                                    @php
                                        $dotClass = '';
                                        if ($i < $statusIndex) $dotClass = 'completed';
                                        elseif ($i == $statusIndex) $dotClass = 'active';
                                        $lineClass = ($i < $statusIndex) ? 'completed' : '';
                                    @endphp
                                    @if($i > 0)
                                        <div class="pipeline-line {{ $lineClass }}"></div>
                                    @endif
                                    <div class="pipeline-step" title="{{ $kanbanLabels[$status] }}">
                                        <div class="pipeline-dot {{ $dotClass }}"></div>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td style="font-size:12px;color:#6c757d;">{{ $sale->sales_agent_name ?: '—' }}</td>
                        <td style="font-size:12px;color:#6c757d;white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px;color:#6c757d;">
                            No orders found.
                            <br><br>
                            <a href="{{ route('sales.prototype.create') }}" class="btn btn-primary">➕ Create First Order</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $sales->links() }}
    </div>

</div>
@endsection

@push('scripts')
<script>
function filterTable() {
    var search = document.getElementById('searchInput').value.toLowerCase();
    var dept = document.getElementById('deptFilter').value;
    var status = document.getElementById('statusFilter').value;
    var rows = document.querySelectorAll('#orderTable tbody tr');
    
    rows.forEach(function(row) {
        // Skip empty row
        if (row.querySelector('td[colspan]')) return;
        
        var text = row.textContent.toLowerCase();
        var rowDept = row.querySelector('.dept-badge') ? row.querySelector('.dept-badge').textContent.trim() : '';
        var deptMatch = !dept || rowDept === document.querySelector('#deptFilter option[value="' + dept + '"]').textContent;
        
        var activeDot = row.querySelector('.pipeline-dot.active');
        var statusMatch = !status || (activeDot && activeDot.closest('.pipeline-step').title === document.querySelector('#statusFilter option[value="' + status + '"]').textContent);
        
        var searchMatch = !search || text.indexOf(search) !== -1;
        
        row.style.display = (deptMatch && statusMatch && searchMatch) ? '' : 'none';
    });
}
</script>
@endpush
