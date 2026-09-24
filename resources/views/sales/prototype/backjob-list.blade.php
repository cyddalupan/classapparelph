@extends('layouts.app')

@section('title', 'Backjob List — Pending Backjobs')

@push('styles')
<style>
    .main-content, .content-area { min-width: 0; }

    /* Header area — same style as manager order list */
    .list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
        background: linear-gradient(135deg, #1a1a2e 0%, #3a2d6b 55%, #6d28d9 100%);
        border-radius: 14px;
        padding: 20px 24px;
        box-shadow: 0 4px 16px rgba(90, 40, 160, 0.25);
        position: relative;
        overflow: hidden;
    }
    .list-header::before {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
        pointer-events: none;
    }
    .list-header h2 {
        margin: 0;
        color: #ffffff;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 0.3px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .list-header .header-sub {
        color: rgba(255,255,255,0.75);
        font-size: 13px;
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .header-stat {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 10px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        color: #fff;
    }
    .list-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .list-actions .btn {
        border-radius: 10px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        transition: all .15s ease;
    }
    .list-actions .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

    /* Filter bar */
    .filter-bar {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 16px;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 12px 14px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .filter-bar input, .filter-bar select {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 13px;
    }
    .filter-bar input:focus, .filter-bar select:focus {
        border-color: #6d28d9;
        outline: none;
        box-shadow: 0 0 0 3px rgba(109, 40, 217, 0.12);
    }

    /* Table */
    .bj-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .bj-table th {
        background: #f8f9fa;
        padding: 10px 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    .bj-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }
    .bj-table tr:hover td { background: #f5f0ff; }
    .bj-table tr { cursor: pointer; }

    .bj-comment {
        background: #fdf4ff;
        border-left: 3px solid #a855f7;
        border-radius: 6px;
        padding: 8px 10px;
        font-size: 12px;
        line-height: 1.4;
        color: #581c87;
        font-weight: 500;
    }
    .bj-project {
        font-size: 11px;
        color: #6c757d;
        display: block;
        margin-top: 2px;
    }
    .bj-time { font-size: 11px; color: #999; white-space: nowrap; }
    .bj-dept {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
        color: #fff;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    .empty-state i { font-size: 48px; color: #d1c4e9; display: block; margin-bottom: 12px; }

    /* Backjob History (audit trail) */
    .bj-history {
        margin-top: 24px;
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .bj-history-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        flex-wrap: wrap;
    }
    .bj-history-head h5 {
        margin: 0;
        font-weight: 700;
        color: #3a2d6b;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .bj-history table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .bj-history th {
        background: #fff;
        padding: 8px 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
        color: #6c757d;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .3px;
    }
    .bj-history td { padding: 8px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
    .bj-history tr:hover td { background: #faf9ff; }
    .bj-action {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 700;
        color: #fff;
        white-space: nowrap;
    }
    .bj-action.done { background: #198754; }
    .bj-action.deleted { background: #dc3545; }
    .bj-hist-text { max-width: 280px; color: #333; }
    .bj-hist-sub { font-size: 10px; color: #999; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="list-header">
        <div>
            <h2>🔧 Backjob List</h2>
            <div class="header-sub">
                <span class="header-stat">🛠️ {{ count($rows) }} pending backjob(s)</span>
                <span class="header-stat">📦 {{ collect($rows)->pluck('sale_id')->unique()->count() }} order(s)</span>
                <span class="header-stat">💡 mula sa Production Slip & Additional Production Slip</span>
            </div>
        </div>
        <div class="list-actions">
            @if(auth()->user() && auth()->user()->isGa())
                <a href="{{ route('sales.prototype.ga-order-list') }}" class="btn" style="background:#6f42c1;color:#fff;">🎨 GA Job List</a>
                <a href="{{ route('sales.prototype.ga-dashboard') }}" class="btn" style="background:#0dcaf0;color:#fff;">📊 GA Dashboard</a>
            @else
                <a href="{{ route('sales.prototype.list') }}" class="btn" style="background:#0d6efd;color:#fff;">📋 Manager Order List</a>
                <a href="{{ route('sales.prototype.kanban') }}" class="btn" style="background:#6f42c1;color:#fff;">📊 Kanban Board</a>
                <a href="{{ route('sales.prototype.delays') }}" class="btn" style="background:#dc3545;color:#fff;">⚠️ Delay List</a>
            @endif
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <input type="text" id="searchInput" placeholder="Search customer, sales #, comment..." onkeyup="filterTable()" style="min-width:220px;">
        <select id="deptFilter" onchange="filterTable()" title="Filter by department">
            <option value="">All Departments</option>
            @foreach($departmentLabels as $did => $dlabel)
                <option value="{{ $did }}">{{ $dlabel }}</option>
            @endforeach
        </select>
        <select id="agentFilter" onchange="filterTable()" title="Filter by agent">
            <option value="">All Agents</option>
            @php $agents = collect($rows)->pluck('agent')->filter()->unique()->sort(); @endphp
            @foreach($agents as $agent)
                <option value="{{ $agent }}">{{ $agent }}</option>
            @endforeach
        </select>
    </div>

    <div style="background:#fff;border-radius:12px;border:1px solid #e9ecef;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.04);">
        <table class="bj-table" id="bjTable">
            <thead>
                <tr>
                    <th>Mock Up</th>
                    <th>Prio</th>
                    <th>Sales #</th>
                    <th>Backjob Comment</th>
                    <th>Project</th>
                    <th>Added</th>
                    <th>Due / Pickup</th>
                    <th>Customer</th>
                    <th>Agent</th>
                    <th>Department</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr data-dept="{{ $row['department_id'] ?? '' }}" data-agent="{{ $row['agent'] ?? '' }}"
                        onclick="window.location.href='{{ route('sales.prototype.show', $row['sale_id']) }}'">
                        <td>
                            @if(!empty($row['mockup_url']))
                                <img src="{{ $row['mockup_url'] }}" alt="mockup" style="width:72px;height:auto;max-height:80px;object-fit:contain;border-radius:6px;cursor:pointer;" title="Click to open order" onerror="this.style.display='none'">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($row['priority']))
                                <span style="display:inline-block;background:#fff3cd;color:#856404;font-weight:600;font-size:12px;padding:3px 10px;border-radius:12px;">Prio {{ $row['priority'] }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            <strong style="font-size:11px;">{{ $row['sales_number'] }}</strong>
                            @if(!empty($row['created_at']))
                                <div style="font-size:10px;color:#6c757d;white-space:nowrap;">{{ \Carbon\Carbon::parse($row['created_at'])->format('M d, Y') }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="bj-comment">{{ $row['text'] ?: '—' }}</div>
                        </td>
                        <td style="max-width:200px;">
                            <span style="font-size:12px;">{{ \Illuminate\Support\Str::limit($row['project'], 40) }}</span>
                        </td>
                        <td><span class="bj-time">{{ $row['at'] ?: '—' }}</span></td>
                        <td style="white-space:nowrap;">
                            @if(!empty($row['needed_by']))
                                @php
                                    $nb = \Carbon\Carbon::parse($row['needed_by']);
                                    $isOverdue = $nb->lt(\Carbon\Carbon::now());
                                @endphp
                                <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-success' }} d-inline-flex flex-column align-items-start" style="line-height:1.25;">
                                    <span style="font-size:10px;"><i class="fas fa-calendar-day"></i> {{ $nb->format('M d, Y') }}</span>
                                    <span style="font-size:10px;"><i class="fas fa-clock"></i> {{ $nb->format('g:i A') }}</span>
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $row['customer'] ?: '—' }}</td>
                        <td>{{ $row['agent'] ?: '—' }}</td>
                        <td>
                            @php $dlabel = $departmentLabels[$row['department_id']] ?? 'Other'; $dcolor = [1=>"#0d6efd",2=>"#198754",3=>"#dc3545",4=>"#6f42c1",5=>"#fd7e14",6=>"#6c757d"][$row['department_id']] ?? "#6c757d"; @endphp
                            <span class="bj-dept" style="background:{{ $dcolor }};">{{ $dlabel }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <h4>Wala pang pending backjob 🎉</h4>
                                <p>Lahat ng backjob comments sa production slips ay tapos na o wala pang backjob.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 🕘 Backjob History — simpleng audit trail (read-only) -->
    <div class="bj-history">
        <div class="bj-history-head">
            <h5>🕘 Backjob History <span style="font-weight:500;color:#6c757d;font-size:12px;">({{ count($history) }} event{{ count($history) == 1 ? '' : 's' }})</span></h5>
            <input type="text" id="histSearch" placeholder="Search history..." onkeyup="filterHistory()" style="border:1px solid #dee2e6;border-radius:8px;padding:5px 10px;font-size:12px;min-width:200px;">
        </div>
        @if(empty($history))
            <div class="text-center py-4 text-muted"><small>Wala pang backjob history.</small></div>
        @else
        <div style="overflow-x:auto;max-height:440px;overflow-y:auto;">
            <table id="histTable">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Sales #</th>
                        <th>Backjob Comment</th>
                        <th>Project</th>
                        <th>Added</th>
                        <th>Finished / When</th>
                        <th>By</th>
                        <th>Customer</th>
                        <th>Department</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $h)
                    <tr>
                        <td><span class="bj-action {{ $h['action'] }}">{{ $h['action'] === 'deleted' ? '🗑 Deleted' : '✅ Done' }}</span></td>
                        <td style="white-space:nowrap;"><a href="{{ route('sales.prototype.show', $h['sale_id']) }}" style="font-size:11px;font-weight:600;">{{ $h['sales_number'] }}</a></td>
                        <td class="bj-hist-text">{{ $h['text'] ?: '—' }}</td>
                        <td style="max-width:160px;"><span style="font-size:11px;">{{ \Illuminate\Support\Str::limit($h['project'], 30) }}</span></td>
                        <td><span class="bj-hist-sub">{{ $h['added_at'] ?: '—' }}</span></td>
                        <td style="white-space:nowrap;"><span style="font-size:11px;font-weight:600;color:#3a2d6b;">{{ $h['at'] ?: '—' }}</span></td>
                        <td style="white-space:nowrap;font-size:11px;">{{ $h['by'] ?: '—' }}</td>
                        <td style="font-size:11px;">{{ $h['customer'] ?: '—' }}</td>
                        <td>
                            @php $dl = $departmentLabels[$h['department_id']] ?? 'Other'; $dc = [1=>"#0d6efd",2=>"#198754",3=>"#dc3545",4=>"#6f42c1",5=>"#fd7e14",6=>"#6c757d"][$h['department_id']] ?? "#6c757d"; @endphp
                            <span class="bj-dept" style="background:{{ $dc }};">{{ $dl }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function filterTable() {
    var search = (document.getElementById('searchInput').value || '').toLowerCase();
    var dept = document.getElementById('deptFilter').value;
    var agent = document.getElementById('agentFilter').value;
    var rows = document.querySelectorAll('#bjTable tbody tr');
    rows.forEach(function(row) {
        if (row.classList.contains('no-filter')) return;
        var text = row.textContent.toLowerCase();
        var rowDept = row.dataset.dept || '';
        var rowAgent = row.dataset.agent || '';
        var show = true;
        if (search && text.indexOf(search) === -1) show = false;
        if (dept && rowDept !== dept) show = false;
        if (agent && rowAgent !== agent) show = false;
        row.style.display = show ? '' : 'none';
    });
}

function filterHistory() {
    var search = (document.getElementById('histSearch').value || '').toLowerCase();
    var rows = document.querySelectorAll('#histTable tbody tr');
    rows.forEach(function(row) {
        var show = !search || row.textContent.toLowerCase().indexOf(search) !== -1;
        row.style.display = show ? '' : 'none';
    });
}
</script>
@endpush
