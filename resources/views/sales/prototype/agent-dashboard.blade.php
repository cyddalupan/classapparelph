@extends('layouts.app')

@section('page-title', 'My Sales — Sales Team')

@push('styles')
<style>
.agent-dashboard { padding: 2rem; max-width: 1200px; margin: 0 auto; }

/* Filter bar */
.filter-bar { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; }
.filter-bar form { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 0.85rem; align-items: end; }
.filter-group { display: flex; flex-direction: column; gap: 0.25rem; }
.filter-group label { font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; }
.filter-group input,
.filter-group select { padding: 0.45rem 0.7rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.85rem; color: #1e293b; background: white; width: 100%; min-width: 0; }
.filter-group input:focus,
.filter-group select:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.15); }
.filter-group.search-field { grid-column: span 2; }
.filter-actions { display: flex; gap: 0.5rem; align-items: center; padding-bottom: 1px; justify-content: flex-end; }
.filter-actions .action-btn { padding: 0.45rem 1rem; }
.active-filter-count { display: inline-flex; align-items: center; gap: 0.3rem; background: #eef2ff; color: #4f46e5; padding: 0.25rem 0.65rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }

/* Pipeline Legend */
.pipeline-legend { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0.85rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap; }
.legend-title { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap; }
.legend-steps { display: flex; align-items: center; gap: 0; flex-wrap: wrap; }
.legend-item { display: flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 500; color: #475569; }
.legend-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; border: 1.5px solid #d1d5db; background: white; }
.legend-dot.completed { background: #22c55e; border-color: #22c55e; }
.legend-dot.active { background: #3b82f6; border-color: #3b82f6; }
.legend-dot.completed-active { background: #22c55e; border-color: #22c55e; box-shadow: 0 0 0 2px rgba(34,197,94,0.2); }
.legend-sep { width: 12px; height: 1.5px; background: #d1d5db; flex-shrink: 0; }

/* Header */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.page-header h1 i { color: #3b82f6; }
.page-subtitle { color: #64748b; font-size: 0.875rem; margin: 0.25rem 0 0 0; }
.header-actions { display: flex; gap: 0.75rem; }

/* Stats row */
.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.stat-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 1.25rem; text-align: center; min-width: 0; }
.stat-card .stat-value { font-size: 1.2rem; font-weight: 700; color: #1e293b; line-height: 1.3; white-space: nowrap; }
.stat-card .stat-label { font-size: 0.8rem; color: #64748b; margin-top: 0.25rem; }
.stat-card.total .stat-value { color: #3b82f6; }
.stat-card.pending .stat-value { color: #f59e0b; }
.stat-card.production .stat-value { color: #8b5cf6; }
.stat-card.completed .stat-value { color: #22c55e; }
.stat-card.deposit .stat-value { color: #10b981; }

/* Sale Card */
.sale-card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 1.25rem; overflow: hidden; transition: box-shadow 0.2s; }
.sale-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
.sale-card-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap; gap: 0.5rem; }
.sale-number { font-weight: 700; color: #3b82f6; font-size: 0.9rem; }
.sale-title { font-weight: 700; color: #1e293b; font-size: 0.95rem; line-height: 1.3; }
.sale-customer { font-weight: 600; color: #1e293b; }
.sale-date { color: #94a3b8; font-size: 0.8rem; }
.sale-id-badge { background: #eef2ff; color: #4f46e5; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }

.sale-card-body { padding: 1rem 1.5rem; }

/* Pipeline progress */
.pipeline { display: flex; align-items: center; gap: 0; margin: 0.75rem 0; }
.pipeline-step { display: flex; align-items: center; }
.pipeline-dot { width: 20px; height: 20px; border-radius: 50%; border: 2px solid #d1d5db; background: #fff; flex-shrink: 0; position: relative; transition: all 0.2s; }
.pipeline-dot.completed { background: #22c55e; border-color: #22c55e; }
.pipeline-dot.active { background: #3b82f6; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.2); }
.pipeline-dot.completed.active { background: #22c55e; border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.2); }
.pipeline-line { width: 28px; height: 2px; background: #d1d5db; flex-shrink: 0; }
.pipeline-line.completed { background: #22c55e; }

.pipeline-labels { display: flex; gap: 0; margin-top: 0.25rem; }
.pipeline-label { font-size: 0.65rem; color: #94a3b8; text-align: center; width: 48px; }
.pipeline-label.done { color: #22c55e; font-weight: 600; }
.pipeline-label.now { color: #3b82f6; font-weight: 700; }

/* Details row */
.sale-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-top: 1rem; }
.detail-item {}
.detail-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8; margin-bottom: 0.15rem; }
.detail-value { font-size: 0.875rem; font-weight: 600; color: #1e293b; }
.detail-value.text-success { color: #22c55e; }
.detail-value.text-warning { color: #f59e0b; }
.detail-value.text-danger { color: #ef4444; }

/* Payment verification badge */
.payment-badge { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
.payment-badge.verified { background: #dcfce7; color: #16a34a; }
.payment-badge.pending { background: #fef3c7; color: #d97706; }
.payment-badge.unpaid { background: #fee2e2; color: #dc2626; }

/* Sale card footer / actions */
.sale-card-footer { display: flex; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #f8fafc; border-top: 1px solid #f1f5f9; flex-wrap: wrap; }
.action-btn { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.85rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 0.15s; border: 1px solid #e2e8f0; background: white; color: #475569; text-decoration: none; }
.action-btn:hover { background: #f1f5f9; border-color: #cbd5e1; }
.action-btn.primary { background: #3b82f6; border-color: #3b82f6; color: white; }
.action-btn.primary:hover { background: #2563eb; }

/* Empty state */
.empty-state { text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; border: 1px solid #e2e8f0; }
.empty-state i { font-size: 3rem; color: #94a3b8; margin-bottom: 1rem; }
.empty-state h3 { font-size: 1.25rem; color: #475569; margin: 0 0 0.5rem 0; }
.empty-state p { color: #94a3b8; margin: 0 0 1.5rem 0; }

@media (max-width: 640px) {
    .agent-dashboard { padding: 1rem; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .sale-details { grid-template-columns: 1fr 1fr; }
}
</style>
@endpush

@section('content')
<div class="agent-dashboard">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h1><i class="fas fa-store"></i> My Sales</h1>
            <p class="page-subtitle">Sales team dashboard — track your orders and payments</p>
        </div>
        <div class="header-actions">
            @if(isset($notifications))
            <div class="notif-wrap" style="position:relative;">
                <button class="action-btn" onclick="toggleNotifPanel()" style="position:relative;" title="Notifications">
                    <i class="fas fa-bell"></i>
                    @if(($unreadCount ?? 0) > 0)
                    <span class="notif-badge" style="position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;border-radius:50%;font-size:11px;min-width:18px;height:18px;display:flex;align-items:center;justify-content:center;padding:0 4px;">{{ $unreadCount }}</span>
                    @endif
                </button>
                <div id="notifPanel" style="display:none;position:absolute;right:0;top:calc(100% + 8px);width:340px;max-height:420px;overflow-y:auto;background:#fff;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,0.18);z-index:1000;border:1px solid #e2e8f0;">
                    <div style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-weight:700;color:#1e293b;display:flex;justify-content:space-between;align-items:center;">
                        <span>🔔 Notifications</span>
                        @if(($unreadCount ?? 0) > 0)
                        <button onclick="markAllRead()" style="border:none;background:#3b82f6;color:#fff;border-radius:6px;padding:4px 10px;font-size:12px;cursor:pointer;">Mark all read</button>
                        @endif
                    </div>
                    @forelse($notifications as $notif)
                    <a href="{{ route('sales.prototype.show', $notif->sale_id) }}" onclick="markNotifRead({{ $notif->id }})" style="display:block;padding:12px 16px;border-bottom:1px solid #f1f5f9;text-decoration:none;background:{{ $notif->is_urgent ? '#fef2f2' : ($notif->is_read ? '#fff' : '#eff6ff') }};border-left:{{ $notif->is_urgent ? '3px solid #dc2626' : '3px solid transparent' }};" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='{{ $notif->is_urgent ? '#fef2f2' : ($notif->is_read ? '#fff' : '#eff6ff') }}'">
                        <div style="font-size:13px;font-weight:600;color:{{ $notif->is_urgent ? '#dc2626' : '#1e293b' }};">{{ $notif->title }}</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px;">{{ $notif->message }}</div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:4px;">from {{ $notif->fromUser->name ?? 'Manager' }} • {{ $notif->created_at->diffForHumans() }}{{ $notif->reminder_count > 1 ? ' • Reminder #' . $notif->reminder_count : '' }}</div>
                    </a>
                    @empty
                    <div style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;">No notifications yet 🎉</div>
                    @endforelse
                </div>
            </div>
            @endif
            <a href="{{ route('sales.prototype.create') }}" class="action-btn primary">
                <i class="fas fa-plus"></i> Add New Sale
            </a>
        </div>
    </div>

    <!-- Pipeline Legend -->
    <div class="pipeline-legend">
        <span class="legend-title">Progress Guide:</span>
        <div class="legend-steps">
            @foreach($statuses as $i => $status)
            @php
                $dotCls = $i === 0 ? 'completed' : ($i === 1 ? 'active' : '');
            @endphp
            <div class="legend-item">
                <span class="legend-dot {{ $dotCls }}"></span>
                {{ $statusLabels[$status] }}
            </div>
            @if(!$loop->last)
            <span class="legend-sep"></span>
            @endif
            @endforeach
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('sales.team.dashboard') }}">
            <div class="filter-group search-field">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Customer / Sales # / Phone">
            </div>
            <div class="filter-group">
                <label for="date_from">Date From</label>
                <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="filter-group">
                <label for="date_to">Date To</label>
                <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="filter-group">
                <label for="payment_status">Payment Status</label>
                <select id="payment_status" name="payment_status">
                    <option value="">All</option>
                    <option value="pending" {{ ($filters['payment_status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="verified" {{ ($filters['payment_status'] ?? '') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="down_payment_verified" {{ ($filters['payment_status'] ?? '') === 'down_payment_verified' ? 'selected' : '' }}>Down Payment Verified</option>
                    <option value="additional_payment_verified" {{ ($filters['payment_status'] ?? '') === 'additional_payment_verified' ? 'selected' : '' }}>Additional Verified</option>
                    <option value="full_payment_verified" {{ ($filters['payment_status'] ?? '') === 'full_payment_verified' ? 'selected' : '' }}>Full Payment Verified</option>
                    <option value="rejected" {{ ($filters['payment_status'] ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="reject_pending" {{ ($filters['payment_status'] ?? '') === 'reject_pending' ? 'selected' : '' }}>Rejection Pending</option>
                    <option value="edit_pending" {{ ($filters['payment_status'] ?? '') === 'edit_pending' ? 'selected' : '' }}>Edit Pending</option>
                    <option value="unpaid" {{ ($filters['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="kanban_status">Order Status</label>
                <select id="kanban_status" name="kanban_status">
                    <option value="">All</option>
                    @foreach($statusLabels as $key => $label)
                    <option value="{{ $key }}" {{ ($filters['kanban_status'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label for="department">Shop/Department</label>
                <select id="department" name="department">
                    <option value="">All</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ ($filters['department'] ?? '') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method">
                    <option value="">All</option>
                    @foreach($paymentMethods as $pm)
                    <option value="{{ $pm }}" {{ ($filters['payment_method'] ?? '') === $pm ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $pm)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="action-btn primary">
                    <i class="fas fa-filter"></i> Apply
                </button>
                <a href="{{ route('sales.team.dashboard') }}" class="action-btn">
                    <i class="fas fa-times"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Stats -->
    @php
        $totalSales   = $sales->count();
        $totalAmount  = $sales->sum('total_amount');
        $totalDeposit = $sales->sum('net_paid');
        $pendingPay   = $sales->where('payment_status', 'pending')->count();
        $inProd       = $sales->where('kanban_status', 'production')->count();
        $completedCnt = $sales->where('kanban_status', 'completed')->count();
    @endphp

    <div class="stats-row">
        <div class="stat-card total">
            <div class="stat-value">{{ $totalSales }}</div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card total">
            <div class="stat-value">{{ number_format($totalPieces, 0) }}</div>
            <div class="stat-label">Total Pieces</div>
        </div>
        <div class="stat-card total">
            <div class="stat-value">₱{{ number_format($totalValue, 0) }}</div>
            <div class="stat-label">Total Value</div>
        </div>
        <div class="stat-card pending">
            <div class="stat-value">₱{{ number_format($totalBalance, 0) }}</div>
            <div class="stat-label">Balance Due</div>
        </div>
        <div class="stat-card pending">
            <div class="stat-value">{{ $pendingPay }}</div>
            <div class="stat-label">Pending Payment</div>
        </div>
        <div class="stat-card production">
            <div class="stat-value">{{ $inProd }}</div>
            <div class="stat-label">In Production</div>
        </div>
        <div class="stat-card completed">
            <div class="stat-value">{{ $completedCnt }}</div>
            <div class="stat-label">Completed</div>
        </div>
    </div>

    <!-- Sales List -->
    @forelse($sales as $sale)
    <div class="sale-card">
        <div class="sale-card-header">
            <div>
                @php
                    // Build descriptive title from services (same as manager order list)
                    $svcItems = is_string($sale->services) ? json_decode($sale->services, true) : ($sale->services ?? []);
                    $descParts = [];
                    $totalQty = 0;
                    foreach ((array)$svcItems as $svc) {
                        if (is_array($svc)) {
                            $descParts[] = \App\Models\PrototypeSale::itemSpecSummary($svc);
                            $totalQty += (int)($svc['quantity'] ?? 1);
                        }
                    }
                    $saleTitle = $descParts ? implode(' + ', $descParts) : $sale->sales_number;
                @endphp
                <div class="sale-title">{{ $saleTitle }}</div>
                <div class="sale-number">{{ $sale->sales_number }}@if($totalQty) · {{ $totalQty }} pcs @endif
                    @if(!empty($sale->customer_name) && !in_array(strtoupper(trim($sale->customer_name)), ['TEST', 'TEST ORDER']))
                        — {{ $sale->customer_name }}
                    @endif
                </div>
            </div>
            <div>
                <span class="sale-date">
                    <i class="far fa-calendar-alt"></i>
                    {{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y') }}
                </span>
                @php
                    // Due date status — same logic as manager order list
                    $dueHiddenStages = ['ready_for_delivery', 'delivered', 'completed'];
                    $dueRaw = $sale->rescheduled_date ?: $sale->estimated_completion_date;
                    $dueCarbon = $dueRaw ? \Carbon\Carbon::parse($dueRaw)->startOfDay() : null;
                    $daysLeft = $dueCarbon ? (int) now()->startOfDay()->diffInDays($dueCarbon, false) : null;
                    $isDelayed = $dueCarbon && $daysLeft !== null && $daysLeft < 0;
                    $isDone = in_array($sale->kanban_status ?? 'new', $dueHiddenStages);
                @endphp
                @if($isDelayed)
                    @if($isDone)
                        <span class="badge bg-secondary" style="margin-left:0.5rem;" title="Delayed order — for review reference">Delayed</span>
                    @else
                        <span class="badge bg-danger" style="margin-left:0.5rem;" title="Due {{ $dueCarbon->format('M d, Y') }}">Delayed · Due {{ abs($daysLeft) }}d ago</span>
                    @endif
                @elseif($dueCarbon)
                    @if($daysLeft === 0)
                        <span class="badge bg-danger" style="margin-left:0.5rem;" title="Due today">Due TODAY</span>
                    @elseif($daysLeft <= 3)
                        <span class="badge bg-warning text-dark" style="margin-left:0.5rem;" title="Due {{ $dueCarbon->format('M d, Y') }}">{{ $daysLeft }}d left</span>
                    @else
                        <span class="badge bg-success" style="margin-left:0.5rem;" title="Due {{ $dueCarbon->format('M d, Y') }}">{{ $daysLeft }}d left</span>
                    @endif
                @endif
                @if($sale->department_name)
                <span class="sale-id-badge" style="margin-left:0.5rem;">{{ $sale->department_name }}</span>
                @endif
            </div>
        </div>

        <div class="sale-card-body">
            <!-- Kanban Progress Bar (matches manager list) -->
            @php
                $currentIdx = array_search($sale->kanban_status ?? 'new', $statuses);
                if ($currentIdx === false) $currentIdx = 0;
                $statusText = $statusLabels[$sale->kanban_status] ?? ucfirst($sale->kanban_status);
            @endphp
            <div style="font-size:0.8rem;font-weight:600;color:#475569;margin-bottom:0.25rem;">
                Status: <span style="color:#3b82f6;">{{ $statusText }}</span>
            </div>
            <div class="pipeline">
                @foreach($statuses as $i => $status)
                    @php
                        $dotClass = '';
                        if ($i < $currentIdx) $dotClass = 'completed';
                        elseif ($i == $currentIdx) $dotClass = 'active';
                        $lineClass = ($i < $currentIdx) ? 'completed' : '';
                    @endphp
                    @if($i > 0)
                    <div class="pipeline-line {{ $lineClass }}"></div>
                    @endif
                    <div class="pipeline-step" title="{{ $statusLabels[$status] }}">
                        <div class="pipeline-dot {{ $dotClass }}"></div>
                    </div>
                @endforeach
            </div>

            <!-- Sale details -->
            <div class="sale-details">
                <div class="detail-item">
                    <div class="detail-label">Total Amount</div>
                    <div class="detail-value">₱{{ number_format($sale->total_amount, 2) }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Net Paid</div>
                    <div class="detail-value text-success">₱{{ number_format($sale->net_paid ?? 0, 2) }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Balance Due</div>
                    <div class="detail-value {{ ($sale->balance_due_computed ?? 0) > 0 ? 'text-warning' : 'text-success' }}">
                        ₱{{ number_format($sale->balance_due_computed ?? 0, 2) }}
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Payment Status</div>
                    <div>
                        @php
                            $pStatus = $sale->payment_status ?? 'unpaid';
                            $pBadge  = match($pStatus) {
                                'verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified' => 'verified',
                                'pending'  => 'pending',
                                'rejected', 'reject_pending' => 'unpaid',
                                'edit_pending' => 'pending',
                                default    => 'unpaid',
                            };
                            $pLabel  = match($pStatus) {
                                'verified'                  => 'Verified',
                                'down_payment_verified'     => 'Down Payment Verified',
                                'additional_payment_verified' => 'Additional Verified',
                                'full_payment_verified'     => 'Full Payment Verified',
                                'pending'                   => 'Pending Verification',
                                'rejected'                  => 'Rejected',
                                'reject_pending'            => 'Rejection Pending',
                                'edit_pending'              => 'Edit Pending',
                                default                     => 'Unpaid',
                            };
                            $pIcon = match($pStatus) {
                                'verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified' => 'fa-check-circle',
                                'pending', 'reject_pending', 'edit_pending' => 'fa-clock',
                                'rejected' => 'fa-times-circle',
                                default    => 'fa-times-circle',
                            };
                        @endphp
                        <span class="payment-badge {{ $pBadge }}">
                            <i class="fas {{ $pIcon }}"></i>
                            {{ $pLabel }}
                        </span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Payment Method</div>
                    <div class="detail-value">{{ ucfirst($sale->payment_method ?? '—') }}</div>
                </div>
            </div>
        </div>

        <!-- Footer actions -->
        <div class="sale-card-footer">
            <a href="{{ route('sales.prototype.show', $sale->id) }}" class="action-btn">
                <i class="fas fa-eye"></i> View Details
            </a>
            @if(($sale->balance_due_computed ?? 0) > 0)
            <button type="button" class="action-btn primary" data-bs-toggle="modal" data-bs-target="#payBalanceModal{{ $sale->id }}">
                <i class="fas fa-money-bill-wave"></i> Pay Balance
            </button>
            @endif
            @if($sale->payment_status === 'pending' && $sale->payment_screenshot_path)
            <button class="action-btn" onclick="showScreenshot('{{ $sale->payment_screenshot_path }}')">
                <i class="fas fa-image"></i> View Proof
            </button>
            @endif
            @if(in_array($sale->payment_status ?? '', ['pending', 'reject_pending', 'edit_pending']))
            @php $verifyCount = $verificationCounts[$sale->id] ?? 0; @endphp
            <button type="button" class="action-btn notify-verifier-btn" data-sale-id="{{ $sale->id }}" data-sale-number="{{ $sale->sales_number }}" onclick="notifyVerifier(this)" title="Notify the verifier to check this pending payment (max once every 24h)">
                <i class="fas fa-bell"></i> Notify Verifier @if($verifyCount > 1) ×{{ $verifyCount }}@endif
            </button>
            @endif
        </div>
    </div>

    <!-- Pay Balance Modal -->
    <div class="modal fade" id="payBalanceModal{{ $sale->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('sales.prototype.agent.payment.store', $sale->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i>Pay Balance — {{ $sale->sales_number }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @php
                            $remainingAmt = $sale->balance_due_computed ?? 0;
                            $netPaidAmt = $sale->net_paid ?? 0;
                        @endphp
                        <div class="row g-3">
                            <!-- Summary -->
                            <div class="col-12">
                                <div class="bg-light p-3 rounded d-flex justify-content-around text-center">
                                    <div>
                                        <small class="text-muted d-block">Total</small>
                                        <strong>₱{{ number_format($sale->total_amount ?? 0, 2) }}</strong>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Paid</small>
                                        <strong class="text-success">₱{{ number_format($netPaidAmt, 2) }}</strong>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Balance</small>
                                        <strong class="text-danger">₱{{ number_format($remainingAmt, 2) }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Amount -->
                            <div class="col-md-6">
                                <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                <input type="number" name="payment_amount" class="form-control pay-balance-amount" step="0.01" min="0.01" max="{{ $remainingAmt }}" value="{{ $remainingAmt }}" required>
                                <input type="hidden" name="payment_type" class="pay-balance-type" value="fullpayment">
                            </div>

                            <!-- Date -->
                            <div class="col-md-6">
                                <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <!-- Account -->
                            <div class="col-md-6">
                                <label class="form-label">Payment Account <span class="text-danger">*</span></label>
                                <select name="payment_account_id" class="form-select" required>
                                    <option value="">Select account...</option>
                                    @foreach(\App\Models\PaymentAccount::where('is_active', true)->get() as $acct)
                                        <option value="{{ $acct->id }}">{{ $acct->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Reference -->
                            <div class="col-md-6">
                                <label class="form-label">Reference Number</label>
                                <input type="text" name="reference_number" class="form-control" placeholder="Transaction ref number">
                            </div>

                            <!-- Screenshot -->
                            <div class="col-12">
                                <label class="form-label">Payment Screenshot / Proof</label>
                                <input type="file" name="payment_screenshot" class="form-control" accept="image/*">
                            </div>

                            <!-- Notes -->
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i>Submit Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h3>No sales yet</h3>
        <p>Start by adding your first sale!</p>
        <a href="{{ route('sales.prototype.create') }}" class="action-btn primary" style="padding:0.6rem 1.5rem;">
            <i class="fas fa-plus"></i> Add New Sale
        </a>
    </div>
    @endforelse

</div>
@endsection

@push('scripts')
<script>
function toggleNotifPanel() {
    var panel = document.getElementById('notifPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('click', function(e) {
    var panel = document.getElementById('notifPanel');
    if (panel && !e.target.closest('.notif-wrap')) {
        panel.style.display = 'none';
    }
});

function markNotifRead(id) {
    fetch('{{ route('sales.prototype.notification-read', ':ID') }}'.replace(':ID', id), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    });
}

function markAllRead() {
    fetch('{{ route('sales.prototype.notifications-read-all') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    }).then(function() { location.reload(); });
}

function showScreenshot(path) {
    const win = window.open('', '_blank', 'width=600,height=700');
    if (!win) { alert('Popup blocked. Please allow popups to view payment proofs.'); return; }
    win.document.write(`
        <html><head><title>Payment Proof</title>
        <style>body{margin:0;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f1f5f9;}
        img{max-width:100%;max-height:100vh;object-fit:contain;border-radius:8px;box-shadow:0 4px 24px rgba(0,0,0,0.12);}</style>
        </head><body><img src="${path}" alt="Payment Screenshot"/></body></html>
    `);
}

function notifyVerifier(btn) {
    var saleId = btn.getAttribute('data-sale-id');
    var saleNumber = btn.getAttribute('data-sale-number');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

    fetch('{{ route('sales.prototype.notify-verifier', ':ID') }}'.replace(':ID', saleId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            showToastMsg('✅ ' + data.message);
            btn.innerHTML = '<i class="fas fa-check"></i> Notified';
            btn.style.borderColor = '#16a34a';
            btn.style.color = '#16a34a';
            setTimeout(function() { location.reload(); }, 1500);
        } else if (data.cooldown) {
            showToastMsg('⏳ ' + data.message, 'warn');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-bell"></i> Notify Verifier';
        } else {
            showToastMsg('❌ ' + (data.message || 'Error notifying verifier'), 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-bell"></i> Notify Verifier';
        }
    })
    .catch(function() {
        showToastMsg('❌ Error notifying verifier', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-bell"></i> Notify Verifier';
    });
}

function showToastMsg(msg, type) {
    var container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.cssText = 'position:fixed;top:16px;right:16px;z-index:99999;display:flex;flex-direction:column;gap:8px;';
        document.body.appendChild(container);
    }
    var el = document.createElement('div');
    var bg = type === 'error' ? '#dc2626' : (type === 'warn' ? '#d97706' : '#16a34a');
    el.style.cssText = 'background:' + bg + ';color:#fff;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,0.2);max-width:340px;';
    el.textContent = msg;
    container.appendChild(el);
    setTimeout(function() { el.remove(); }, 4000);
}

// Auto-set payment_type: fullpayment when amount >= remaining, else additional
function wirePayBalanceType() {
    document.querySelectorAll('.pay-balance-amount').forEach(function(input) {
        var typeInput = input.closest('form').querySelector('.pay-balance-type');
        var maxVal = parseFloat(input.max) || 0;
        if (!typeInput) return;
        var update = function() {
            var val = parseFloat(input.value) || 0;
            typeInput.value = (val >= maxVal - 0.001) ? 'fullpayment' : 'additional';
        };
        input.addEventListener('input', update);
        update();
    });
}
document.addEventListener('DOMContentLoaded', wirePayBalanceType);
</script>
@endpush
