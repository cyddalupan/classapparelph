@extends('layouts.app')

@section('title', 'Manager List — All Orders')

@push('styles')
<style>
    /* Allow wide table to shrink flex parents so the page itself never scrolls horizontally */
    .main-content, .content-area { min-width: 0; }

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
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #2563eb 100%);
        border-radius: 14px;
        padding: 20px 24px;
        box-shadow: 0 4px 16px rgba(30, 58, 138, 0.25);
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
        background: rgba(255,255,255,0.14);
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: #fff;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .list-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        position: relative;
        z-index: 1;
    }
    .list-actions .btn {
        border-radius: 8px;
        font-weight: 600;
        padding: 8px 16px;
        font-size: 13px;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    .list-actions .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(0,0,0,0.25);
    }
    .btn-kanban {
        background: linear-gradient(135deg, #312e81 0%, #6366f1 100%);
        border: 1px solid rgba(255,255,255,0.25);
        color: #fff !important;
    }
    .btn-kanban:hover {
        background: linear-gradient(135deg, #4338ca 0%, #818cf8 100%);
        color: #fff !important;
    }
    .btn-new-order {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        border: none;
        color: #fff !important;
    }
    .btn-new-order:hover {
        background: linear-gradient(135deg, #047857 0%, #34d399 100%);
        color: #fff !important;
    }
    .list-actions .btn-pending-hdr {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        border: none;
        color: #fff !important;
    }
    .list-actions .btn-pending-hdr:hover {
        background: linear-gradient(135deg, #b45309 0%, #fbbf24 100%);
        color: #fff !important;
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

    /* Pending notification styles */
    .pending-count-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #dc3545;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        min-width: 20px;
        height: 20px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    tr.has-pending {
        border-left: 4px solid #ffc107 !important;
        background: #fffef5 !important;
    }
    tr.has-pending:hover td {
        background: #fff8e1 !important;
    }
    .pending-row-badge {
        margin-left: 6px;
        font-size: 12px;
        animation: pulse-notif 2s infinite;
        cursor: pointer;
    }
    @keyframes pulse-notif {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    #pendingToggleBtn.active {
        background: #ffca2a;
        box-shadow: 0 0 0 3px rgba(255,193,7,0.4);
    }

    /* Pending Modal */
    .pending-modal-overlay {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(3px);
        animation: fadeIn 0.2s;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .pending-modal-content {
        background: #fff;
        margin: 60px auto;
        max-width: 640px;
        width: 90%;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        overflow: hidden;
        animation: slideUp 0.25s ease-out;
    }
    @keyframes slideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .pending-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 24px;
        border-bottom: 1px solid #eee;
        background: #fffdf0;
    }
    .pending-modal-header h4 {
        margin: 0;
        font-size: 17px;
        font-weight: 600;
    }
    .pending-modal-close {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #999;
        line-height: 1;
        padding: 0 4px;
    }
    .pending-modal-close:hover {
        color: #333;
    }
    .pending-modal-body {
        padding: 8px;
        max-height: 60vh;
        overflow-y: auto;
    }
    .pending-modal-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 10px;
        margin: 4px 0;
        text-decoration: none;
        color: inherit;
        transition: background 0.15s;
        cursor: pointer;
    }
    .pending-modal-item:hover {
        background: #fff8e1;
    }
    .pending-item-left {
        min-width: 130px;
    }
    .pending-item-sale {
        display: block;
        font-weight: 700;
        font-size: 13px;
        color: #0d6efd;
    }
    .pending-item-customer {
        display: block;
        font-size: 11px;
        color: #6c757d;
    }
    .pending-item-summary {
        flex: 1;
        font-size: 12px;
        color: #555;
        line-height: 1.3;
    }
    .pending-item-total {
        text-align: right;
        font-size: 12px;
        white-space: nowrap;
        min-width: 100px;
    }
    .pending-item-arrow {
        color: #ccc;
        font-size: 18px;
        font-weight: 300;
    }
    .pending-modal-item:hover .pending-item-arrow {
        color: #0d6efd;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="list-header">
        <div>
            <h2>📋 Manager's Order List</h2>
            <div class="header-sub">
                <span class="header-stat">📦 {{ $sales->total() }} orders</span>
                @php
                    $headerPending = isset($totalPending) ? (int) $totalPending : 0;
                    $headerActive = $sales->filter(function($s) { return !in_array($s->kanban_status, ['delivered', 'completed', 'cancelled']); })->count();
                    $headerBalance = $sales->filter(function($s) { return $s->balance_due_computed > 0; })->count();
                @endphp
                <span class="header-stat">⚡ {{ $headerActive }} active</span>
                <span class="header-stat">⚠️ {{ $headerBalance }} with balance</span>
                @if($headerPending > 0)
                    <span class="header-stat" style="background:rgba(245,158,11,0.35);">🔔 {{ $headerPending }} pending add-on</span>
                @endif
            </div>
        </div>
        <div class="list-actions">
            <a href="{{ route('sales.prototype.kanban') }}" class="btn btn-kanban">📊 Kanban Board</a>
            <a href="{{ route('sales.prototype.create') }}" class="btn btn-new-order">➕ New Order</a>
            @if(isset($totalPending) && $totalPending > 0)
                <button class="btn btn-pending-hdr" id="pendingToggleBtn" onclick="showPendingModal()" style="position:relative;">
                    🔔 Add-ons <span class="pending-count-badge">{{ $totalPending }}</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <input type="text" id="searchInput" placeholder="Search customer, sales #, phone..." onkeyup="filterTable()">
        <input type="date" id="dateFrom" title="Date from" onchange="filterTable()" style="min-width:140px;">
        <input type="date" id="dateTo" title="Date to" onchange="filterTable()" style="min-width:140px;">
        <select id="stageFilter" onchange="filterTable()" title="Filter by production stage">
            <option value="">All Stages</option>
            @foreach(array_keys($prodStageMap) as $stageName)
                <option value="{{ $stageName }}">{{ $stageName }}</option>
            @endforeach
        </select>
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
            <option value="sample_approval">Sample/Approval</option>
            <option value="design">Design</option>
            <option value="production">Production</option>
            <option value="quality_check">Quality Check</option>
            <option value="ready_for_delivery">Ready for Delivery</option>
            <option value="delivered">Delivered</option>
            <option value="completed">Completed</option>
        </select>
        <select id="paymentFilter" onchange="filterTable()">
            <option value="">All Payments</option>
            <option value="paid">✅ Paid</option>
            <option value="refunded">↩ Refunded</option>
            <option value="pending">⏳ Pending</option>
            <option value="rejected">❌ Rejected</option>
            <option value="balance">⚠️ With Balance Due</option>
        </select>
        <select id="agentFilter" onchange="filterTable()">
            <option value="">All Agents</option>
        </select>
        <select id="photoFilter" onchange="filterTable()">
            <option value="">All Photos</option>
            <option value="missing">⚠️ Missing Photos</option>
            <option value="complete">📄🎨 Complete</option>
        </select>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetFilters()" title="Reset all filters">↺ Reset</button>
        <span class="text-muted" style="font-size:13px;">{{ $sales->total() }} orders</span>
    </div>

    <!-- Pending Changes Modal -->
    @if(isset($pendingChangesList) && $pendingChangesList->count() > 0)
    <div id="pendingModal" class="pending-modal-overlay" onclick="if(event.target===this)closePendingModal()">
        <div class="pending-modal-content">
            <div class="pending-modal-header">
                <h4><i class="fas fa-clock text-warning me-2"></i>Additional Orders Awaiting Approval</h4>
                <button onclick="closePendingModal()" class="pending-modal-close">&times;</button>
            </div>
            <div class="pending-modal-body">
                @foreach($pendingChangesList as $pc)
                <a href="{{ route('sales.prototype.show', $pc->sale_id) }}" class="pending-modal-item">
                    <div class="pending-item-left">
                        <span class="pending-item-sale">{{ $pc->sales_number }}</span>
                        <span class="pending-item-customer">{{ $pc->customer_name ?: '—' }}</span>
                    </div>
                    <div class="pending-item-summary">{{ $pc->change_summary }}</div>
                    <div class="pending-item-total">
                        <span class="text-muted">₱{{ number_format($pc->total_before, 2) }} → </span>
                        <strong>₱{{ number_format($pc->total_after, 2) }}</strong>
                    </div>
                    <div class="pending-item-arrow">→</div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Table -->
    <div style="overflow-x:auto;">
        <table class="pipeline-table" id="orderTable">
            <thead>
                <tr>
                    <th>Priority</th>
                    <th>Sales #</th>
                    <th>Mock Up</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Production Status</th>
                    <th>Photos</th>
                    <th>Total</th>
                    <th>Payment Status</th>
                    <th>Progress</th>
                    <th>Department</th>
                    <th>Customer</th>
                    <th>Agent</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    @php
                        $statusIndex = array_search($sale->kanban_status ?? 'new', $kanbanStatuses);
                        if ($statusIndex === false) $statusIndex = 0;
                        $isActive = false;

                        // Mock up thumbnail
                        $mockups = is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? []);
                        // Main cover = entry with is_main flag, fallback to first
                        $mainMockup = null;
                        foreach ($mockups as $m) {
                            if (is_array($m) && !empty($m['is_main'])) { $mainMockup = $m; break; }
                        }
                        if (!$mainMockup && !empty($mockups)) $mainMockup = $mockups[0];
                        $firstMockup = $mainMockup;
                        $firstMockupUrl = is_string($firstMockup) ? $firstMockup : ($firstMockup['url'] ?? '');

                        // Description + total quantity from services
                        $svcItems = is_string($sale->services) ? json_decode($sale->services, true) : ($sale->services ?? []);
                        $descParts = [];
                        $totalQty = 0;
                        foreach ((array)$svcItems as $svc) {
                            if (is_array($svc)) {
                                $descParts[] = \App\Models\PrototypeSale::itemSpecSummary($svc);
                                $totalQty += (int)($svc['quantity'] ?? 1);
                            }
                        }
                        $description = $descParts ? implode(' + ', $descParts) : '—';

                        // Photos + photo lock
                        $designImgs = $sale->design_images ?? [];
                        $hasFileShot = collect($designImgs)->contains('type', 'file_screenshot');
                        $hasColorShot = collect($designImgs)->contains('type', 'sample_color');
                        $allPhotos = $hasFileShot && $hasColorShot;
                        $photoNotif = $lastNotifs[$sale->id]['photo_reminder'] ?? null;
                        $photoLastAt = $photoNotif['last_at'] ?? null;
                        $photoCount = $photoNotif['reminder_count'] ?? 0;
                        $photoCooldown = $photoLastAt && $photoLastAt->diffInHours(now()) < 24;
                        $photoMinAgo = $photoLastAt ? (int) $photoLastAt->diffInMinutes(now()) : 0;
                        $photoAgoText = $photoMinAgo < 60 ? $photoMinAgo . 'm ago' : round($photoMinAgo / 60) . 'h ago';
                        $canOverride = auth()->user() && (auth()->user()->isAdmin() || auth()->user()->role === 'manager');
                        $lockedStatuses = ['design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'];
                    @endphp
                    <tr data-photos="{{ $allPhotos ? 'complete' : 'missing' }}" data-date="{{ \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d') }}" data-stage="{{ $sale->production_stage ?: ($statusToStage[$sale->kanban_status ?? 'new'] ?? 'HOLD') }}" onclick="window.location.href='{{ route('sales.prototype.show', $sale->id) }}'" class="{{ !empty($pendingCounts[$sale->id]) ? 'has-pending' : '' }}">
                        <td style="text-align:center;">
                            <select class="form-select form-select-sm prio-select" data-sale-id="{{ $sale->id }}" data-current="{{ $sale->priority ?? '' }}" onclick="event.stopPropagation()" style="font-size:12px;min-width:80px;padding:2px 6px;{{ $sale->priority ? 'background:#fff3cd;color:#856404;font-weight:600;' : '' }}">
                                <option value="" {{ !$sale->priority ? 'selected' : '' }}>—</option>
                                <option value="1" {{ $sale->priority === 1 ? 'selected' : '' }}>🔴 Prio 1</option>
                                <option value="2" {{ $sale->priority === 2 ? 'selected' : '' }}>🟠 Prio 2</option>
                                <option value="3" {{ $sale->priority === 3 ? 'selected' : '' }}>🟡 Prio 3</option>
                            </select>
                        </td>
                        <td style="max-width:130px;">
                            @if($sale->is_delayed)
                            <span class="badge bg-danger mb-1" title="Marked delayed {{ $sale->delayed_at ? $sale->delayed_at->format('M d, Y H:i') : '' }}">⚠️ DELAYED</span><br>
                            @endif
                            <strong style="font-size:11px;">{{ $sale->sales_number }}</strong>
                            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y') }}</div>
                            @php
                                // Hide due badge once order is dispatched or done
                                $dueStageLabel = $sale->production_stage ?: ($statusToStage[$sale->kanban_status ?? 'new'] ?? 'HOLD');
                                $dueStage = $prodStageMap[$dueStageLabel] ?? $dueStageLabel;
                                $dueHiddenStages = ['ready_for_delivery', 'delivered', 'completed'];
                                $dueDate = (!in_array($dueStage, $dueHiddenStages)) ? ($sale->rescheduled_date ?: $sale->estimated_completion_date) : null;
                                $dueBadge = null;
                                if ($dueDate) {
                                    $dueCarbon = \Carbon\Carbon::parse($dueDate)->startOfDay();
                                    $daysLeft = (int) now()->startOfDay()->diffInDays($dueCarbon, false);
                                    if ($daysLeft < 0) {
                                        $dueBadge = '<span class="badge bg-danger" title="Due ' . $dueCarbon->format('M d, Y') . '">Due ' . abs($daysLeft) . 'd ago</span>';
                                    } elseif ($daysLeft === 0) {
                                        $dueBadge = '<span class="badge bg-danger" title="Due today">Due TODAY</span>';
                                    } elseif ($daysLeft <= 3) {
                                        $dueBadge = '<span class="badge bg-warning text-dark" title="Due ' . $dueCarbon->format('M d, Y') . '">' . $daysLeft . 'd left</span>';
                                    } else {
                                        $dueBadge = '<span class="badge bg-success" title="Due ' . $dueCarbon->format('M d, Y') . '">' . $daysLeft . 'd left</span>';
                                    }
                                }
                            @endphp
                            @if($dueBadge)
                                <div style="margin-top:2px;">{!! $dueBadge !!}</div>
                            @endif
                            @if(!empty($pendingCounts[$sale->id]))
                                <span class="badge bg-warning text-dark pending-row-badge" title="Has pending changes for approval">🔔</span>
                            @endif
                        </td>
                        <td>
                            @if($firstMockupUrl)
                                <img src="{{ $firstMockupUrl }}" alt="mockup" style="width:72px;height:auto;max-height:80px;object-fit:contain;border-radius:6px;cursor:pointer;" title="Click to open order" onerror="this.style.display='none'">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="max-width:220px;">
                            <div style="font-size:12px;line-height:1.35;overflow:hidden;text-overflow:ellipsis;" title="{{ $description }}">{{ \Illuminate\Support\Str::limit($description, 60) }}</div>
                        </td>
                        <td style="text-align:center;">{{ $totalQty ?: '—' }}</td>
                        <td>
                            <select class="form-select form-select-sm prod-status-select" data-sale-id="{{ $sale->id }}" data-current="{{ $sale->production_stage ?: ($statusToStage[$sale->kanban_status ?? 'new'] ?? 'HOLD') }}" onclick="event.stopPropagation()" style="font-size:12px;min-width:110px;padding:2px 6px;{{ !$allPhotos ? 'background:#e9ecef;color:#adb5bd;cursor:not-allowed;' : '' }}" @if(!$allPhotos) disabled title="🔒 Kulang photos (File Screenshot / Sample Color) — i-move sa kanban board" @endif>
                                @php $currentStage = $sale->production_stage ?: ($statusToStage[$sale->kanban_status ?? 'new'] ?? 'HOLD'); $balanceDue = $sale->balance_due_computed; @endphp
                                @foreach($prodStageMap as $stage => $st)
                                    <option value="{{ $stage }}" data-status="{{ $st }}" {{ $currentStage === $stage ? 'selected' : '' }} @if($st === 'completed' && $balanceDue > 0) disabled title="🔒 May pending balance (₱{{ number_format($balanceDue, 2) }}) — bayaran muna bago i-DONE" @endif>{{ $stage }}</option>
                                @endforeach
                            </select>
                            @if($balanceDue > 0)
                                <div style="font-size:10px;color:#dc3545;margin-top:2px;">🔒 may pending balance (₱{{ number_format($balanceDue, 2) }}) — bayaran muna bago i-DONE</div>
                            @endif
                            @if(!$allPhotos)
                                <div style="font-size:10px;color:#dc3545;margin-top:2px;">🔒 kulang photos — i-move sa kanban board</div>
                            @endif
                        </td>
                        <td>
                            @if($allPhotos)
                                <span class="badge bg-success" title="File screenshot & sample color uploaded">📄🎨 OK</span>
                            @else
                                @if(!$hasFileShot)
                                    <span class="badge bg-warning text-dark" title="Missing file screenshot">📄 Missing</span>
                                @endif
                                @if(!$hasColorShot)
                                    <span class="badge bg-warning text-dark" title="Missing approved sample color">🎨 Missing</span>
                                @endif
                            @endif
                            @if(!$allPhotos)
                                <div style="margin-top:3px;">
                                    @if($photoCooldown)
                                        <span class="badge bg-secondary" title="Last notified {{ $photoLastAt->diffForHumans() }}">🔔 {{ $photoAgoText }}</span>
                                        <button type="button" class="btn btn-sm btn-danger notify-btn" style="width:26px;height:26px;padding:0;font-size:14px;line-height:1;border-radius:5px;margin-left:4px;display:inline-flex;align-items:center;justify-content:center;" data-sale-id="{{ $sale->id }}" data-sale-number="{{ $sale->sales_number }}" data-type="photo_reminder" data-urgent="1" title="🚨 URGENT: Notify agent now (bypasses 24h cooldown)" onclick="event.stopPropagation();notifyAgent(this)">🚨</button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-warning notify-btn" data-sale-id="{{ $sale->id }}" data-sale-number="{{ $sale->sales_number }}" data-type="photo_reminder" title="Notify agent to upload photos{{ $photoCount > 1 ? ' (reminder #'.$photoCount.')' : '' }}" onclick="event.stopPropagation();notifyAgent(this)">🔔{{ $photoCount > 1 ? '×'.$photoCount : '' }}</button>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>₱{{ number_format($sale->total_amount ?? $sale->subtotal ?? 0, 2) }}</td>
                        <td>
                            @php
                                $payNotif = $lastNotifs[$sale->id]['payment_reminder'] ?? null;
                                $payLastAt = $payNotif['last_at'] ?? null;
                                $payCount = $payNotif['reminder_count'] ?? 0;
                                $payCooldown = $payLastAt && $payLastAt->diffInHours(now()) < 24;
                                $payMinAgo = $payLastAt ? (int) $payLastAt->diffInMinutes(now()) : 0;
                                $payAgoText = $payMinAgo < 60 ? $payMinAgo . 'm ago' : round($payMinAgo / 60) . 'h ago';
                            @endphp
                            @if(($sale->balance_due_computed ?? 0) > 0)
                                <span class="badge bg-danger">₱{{ number_format($sale->balance_due_computed, 2) }}</span>
                                @if($payCooldown)
                                    <span class="badge bg-secondary" title="Last notified {{ $payLastAt->diffForHumans() }}">🔔 {{ $payAgoText }}</span>
                                    <button type="button" class="btn btn-sm btn-danger notify-btn" style="width:26px;height:26px;padding:0;font-size:14px;line-height:1;border-radius:5px;margin-left:4px;display:inline-flex;align-items:center;justify-content:center;" data-sale-id="{{ $sale->id }}" data-sale-number="{{ $sale->sales_number }}" data-type="payment_reminder" data-urgent="1" title="🚨 URGENT: Notify agent now (bypasses 24h cooldown)" onclick="event.stopPropagation();notifyAgent(this)">🚨</button>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-danger notify-btn" data-sale-id="{{ $sale->id }}" data-sale-number="{{ $sale->sales_number }}" data-type="payment_reminder" title="Notify agent to collect payment{{ $payCount > 1 ? ' (reminder #'.$payCount.')' : '' }}" onclick="event.stopPropagation();notifyAgent(this)">🔔{{ $payCount > 1 ? '×'.$payCount : '' }}</button>
                                @endif
                            @endif
                            <div style="margin-top:2px;">
                                @if(($sale->total_refunded ?? 0) > 0 && ($sale->balance_due_computed ?? 0) <= 0 && ($sale->net_paid ?? 0) > 0)
                                    <span class="badge bg-info text-dark">↩ Refunded</span>
                                @elseif(($sale->balance_due_computed ?? 0) > 0 && ($sale->net_paid ?? 0) > 0)
                                    <span class="badge bg-warning text-dark" title="May natitira pang bayad: ₱{{ number_format($sale->balance_due_computed, 2) }}">⚠️ Balance</span>
                                @elseif(($sale->balance_due_computed ?? 0) <= 0 && ($sale->net_paid ?? 0) > 0)
                                    <span class="badge bg-success">✅ Paid</span>
                                @elseif($sale->payment_status === 'pending' && $sale->payment_account_id)
                                    <span class="badge bg-warning text-dark">⏳ Pending</span>
                                @elseif($sale->payment_status === 'rejected')
                                    <span class="badge bg-danger">❌ Rejected</span>
                                @else
                                    <span class="badge bg-secondary">—</span>
                                @endif
                                @if(($sale->total_refunded ?? 0) > 0)
                                    <div class="small" style="color:#dc3545;"><i class="fas fa-undo-alt me-1"></i>−₱{{ number_format($sale->total_refunded, 2) }} refunded</div>
                                @endif
                            </div>
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
                        <td>
                            <span class="dept-badge" style="background:{{ $departmentColors[$sale->department_id] ?? '#6c757d' }};">
                                {{ $departmentLabels[$sale->department_id] ?? 'Unknown' }}
                            </span>
                        </td>
                        <td>{{ $sale->customer_name ?: '—' }}</td>
                        <td style="font-size:12px;color:#6c757d;">{{ $sale->sales_agent_name ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" style="text-align:center;padding:40px;color:#6c757d;">
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
function showPendingModal() {
    document.getElementById('pendingModal').style.display = 'block';
}
function closePendingModal() {
    document.getElementById('pendingModal').style.display = 'none';
}

function notifyAgent(btn) {
    var saleId = btn.getAttribute('data-sale-id');
    var saleNumber = btn.getAttribute('data-sale-number');
    var type = btn.getAttribute('data-type');
    var urgent = btn.getAttribute('data-urgent') === '1';

    btn.disabled = true;
    btn.innerHTML = '⏳';

    fetch('{{ route('sales.prototype.notify-agent', ':ID') }}'.replace(':ID', saleId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ type: type, urgent: urgent })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            showToast('✅ ' + data.message);
            btn.innerHTML = '✅';
            btn.classList.remove('btn-outline-warning', 'btn-outline-danger', 'btn-danger');
            btn.classList.add('btn-success');
            setTimeout(function() { location.reload(); }, 1200);
        } else if (data.cooldown) {
            showToast('⏳ ' + data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = urgent ? '🚨' : '🔔';
        } else {
            showToast('⚠️ ' + (data.message || 'Failed to notify agent.'), 'error');
            btn.disabled = false;
            btn.innerHTML = urgent ? '🚨' : '🔔';
        }
    })
    .catch(function() {
        showToast('❌ Network error. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = urgent ? '🚨' : '🔔';
    });
}

function showToast(msg, type) {
    var existing = document.getElementById('notifyToast');
    if (existing) existing.remove();
    var toast = document.createElement('div');
    toast.id = 'notifyToast';
    toast.textContent = msg;
    toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:' + (type === 'error' ? '#dc3545' : '#198754') + ';color:#fff;padding:12px 20px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.2);font-size:14px;font-weight:600;max-width:360px;transition:opacity 0.3s;';
    document.body.appendChild(toast);
    setTimeout(function() { toast.style.opacity = '0'; setTimeout(function() { toast.remove(); }, 300); }, 2500);
}

function togglePendingRows() {
    var btn = document.getElementById('pendingToggleBtn');
    var rows = document.querySelectorAll('#orderTable tbody tr.has-pending');
    var allHidden = true;
    rows.forEach(function(row) { if (row.style.display !== 'none') allHidden = false; });
    
    if (allHidden) {
        // Show only pending rows
        var allRows = document.querySelectorAll('#orderTable tbody tr');
        allRows.forEach(function(r) {
            if (r.querySelector('td[colspan]')) return;
            r.style.display = r.classList.contains('has-pending') ? '' : 'none';
        });
        btn.classList.add('active');
    } else {
        // Show all rows
        var allRows = document.querySelectorAll('#orderTable tbody tr');
        allRows.forEach(function(r) { r.style.display = ''; });
        btn.classList.remove('active');
    }
}

function filterTable() {
    var search = document.getElementById('searchInput').value.toLowerCase();
    var dept = document.getElementById('deptFilter').value;
    var status = document.getElementById('statusFilter').value;
    var payment = document.getElementById('paymentFilter').value;
    var agent = document.getElementById('agentFilter').value;
    var photo = document.getElementById('photoFilter').value;
    var dateFrom = document.getElementById('dateFrom').value;
    var dateTo = document.getElementById('dateTo').value;
    var stage = document.getElementById('stageFilter').value;
    var rows = document.querySelectorAll('#orderTable tbody tr');
    
    rows.forEach(function(row) {
        // Skip empty row
        if (row.querySelector('td[colspan]')) return;
        
        var text = row.textContent.toLowerCase();
        var rowDept = row.querySelector('.dept-badge') ? row.querySelector('.dept-badge').textContent.trim() : '';
        var deptMatch = !dept || rowDept === document.querySelector('#deptFilter option[value="' + dept + '"]').textContent;
        
        var activeDot = row.querySelector('.pipeline-dot.active');
        var statusMatch = !status || (activeDot && activeDot.closest('.pipeline-step').title === document.querySelector('#statusFilter option[value="' + status + '"]').textContent);
        
        // Payment filter: match badge text in the Payment Status column (8th td)
        var payBadge = '';
        var payTd = row.querySelector('td:nth-child(8) .badge');
        if (payTd) payBadge = payTd.textContent.trim();
        var paymentMatch = true;
        if (payment === 'paid') paymentMatch = payBadge.indexOf('Paid') !== -1;
        else if (payment === 'refunded') paymentMatch = payBadge.indexOf('Refunded') !== -1;
        else if (payment === 'pending') paymentMatch = payBadge.indexOf('Pending') !== -1;
        else if (payment === 'rejected') paymentMatch = payBadge.indexOf('Rejected') !== -1;
        else if (payment === 'balance') {
            // With Balance Due: red badge with amount in Payment Status column (8th td)
            var balBadge = row.querySelector('td:nth-child(8) .badge.bg-danger');
            paymentMatch = !!balBadge;
        }
        
        // Agent filter: match agent cell (12th td)
        var rowAgent = row.querySelector('td:nth-child(12)') ? row.querySelector('td:nth-child(12)').textContent.trim() : '';
        var agentMatch = !agent || rowAgent === agent;
        
        // Photo filter: row has data-photos attribute
        var photoStatus = row.getAttribute('data-photos') || '';
        var photoMatch = !photo || photoStatus === photo;
        
        // Date filter: row has data-date attribute (YYYY-MM-DD)
        var rowDate = row.getAttribute('data-date') || '';
        var dateMatch = true;
        if (dateFrom && rowDate < dateFrom) dateMatch = false;
        if (dateTo && rowDate > dateTo) dateMatch = false;
        
        // Stage filter: row has data-stage attribute
        var rowStage = row.getAttribute('data-stage') || '';
        var stageMatch = !stage || rowStage === stage;
        
        var searchMatch = !search || text.indexOf(search) !== -1;
        
        row.style.display = (deptMatch && statusMatch && paymentMatch && agentMatch && photoMatch && dateMatch && stageMatch && searchMatch) ? '' : 'none';
    });
}

// Populate agent filter options from table rows
function populateAgentFilter() {
    var agentSelect = document.getElementById('agentFilter');
    var seen = {};
    document.querySelectorAll('#orderTable tbody tr').forEach(function(row) {
        if (row.querySelector('td[colspan]')) return;
        var a = row.querySelector('td:nth-child(12)') ? row.querySelector('td:nth-child(12)').textContent.trim() : '';
        if (a && !seen[a]) {
            seen[a] = true;
            var opt = document.createElement('option');
            opt.value = a;
            opt.textContent = a;
            agentSelect.appendChild(opt);
        }
    });
}

function resetFilters() {
    ['searchInput', 'deptFilter', 'statusFilter', 'paymentFilter', 'agentFilter', 'photoFilter', 'dateFrom', 'dateTo', 'stageFilter'].forEach(function(id) {
        document.getElementById(id).value = '';
    });
    filterTable();
}

// === PRODUCTION STATUS DROPDOWN — move kanban card ===
document.addEventListener('change', function(e) {
    var sel = e.target.closest('.prod-status-select');
    if (!sel) return;
    var saleId = sel.getAttribute('data-sale-id');
    var opt = sel.options[sel.selectedIndex];
    var stage = opt.value;
    var newStatus = opt.getAttribute('data-status');
    var oldStage = sel.getAttribute('data-current');
    var row = sel.closest('tr');
    sel.disabled = true;
    var csrf = document.querySelector('meta[name="csrf-token"]');
    fetch('/sales/prototype/' + saleId + '/update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf ? csrf.content : ''
        },
        body: JSON.stringify({ kanban_status: newStatus, production_stage: stage })
    })
    .then(function(r) { return r.json().catch(function() { return {}; }).then(function(d) { return { ok: r.ok, data: d }; }); })
    .then(function(res) {
        sel.disabled = false;
        if (res.ok && res.data.success) {
            sel.setAttribute('data-current', stage);
            showToast('✅ Tagged ' + stage + ' → ' + newStatus + ' sa kanban', 'success');
            updatePipelineInRow(row, newStatus);
        } else {
            sel.value = oldStage;
            showToast('⚠️ ' + (res.data.message || 'Failed to update status.'), 'error');
        }
    })
    .catch(function() {
        sel.disabled = false;
        sel.value = oldStage;
        showToast('❌ Network error. Please try again.', 'error');
    });
});

// === PRIORITY DROPDOWN — tag Prio 1/2/3 ===
document.addEventListener('change', function(e) {
    var sel = e.target.closest('.prio-select');
    if (!sel) return;
    var saleId = sel.getAttribute('data-sale-id');
    var oldPrio = sel.getAttribute('data-current');
    var prio = sel.value;
    sel.disabled = true;
    var csrf = document.querySelector('meta[name="csrf-token"]');
    fetch('/sales/prototype/' + saleId + '/priority', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf ? csrf.content : ''
        },
        body: JSON.stringify({ priority: prio })
    })
    .then(function(r) { return r.json().catch(function() { return {}; }).then(function(d) { return { ok: r.ok, data: d }; }); })
    .then(function(res) {
        sel.disabled = false;
        if (res.ok && res.data.success) {
            sel.setAttribute('data-current', prio);
            if (prio) {
                sel.style.background = '#fff3cd';
                sel.style.color = '#856404';
                sel.style.fontWeight = '600';
            } else {
                sel.style.background = '';
                sel.style.color = '';
                sel.style.fontWeight = '';
            }
            showToast(res.data.message || '✅ Priority saved', 'success');
        } else {
            sel.value = oldPrio;
            showToast('⚠️ ' + (res.data.message || 'Failed to save priority.'), 'error');
        }
    })
    .catch(function() {
        sel.disabled = false;
        sel.value = oldPrio;
        showToast('❌ Network error. Please try again.', 'error');
    });
});

function updatePipelineInRow(row, status) {
    var statuses = ['new', 'sample_approval', 'design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'];
    var idx = statuses.indexOf(status);
    if (idx < 0 || !row) return;
    row.querySelectorAll('.pipeline-step').forEach(function(step, i) {
        var dot = step.querySelector('.pipeline-dot');
        dot.classList.remove('completed', 'active');
        if (i < idx) dot.classList.add('completed');
        else if (i === idx) dot.classList.add('active');
    });
    row.querySelectorAll('.pipeline-line').forEach(function(line, i) {
        line.classList.remove('completed');
        if (i < idx) line.classList.add('completed');
    });
}

document.addEventListener('DOMContentLoaded', populateAgentFilter);
</script>
@endpush
