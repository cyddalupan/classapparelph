@extends('layouts.app')

@section('title', 'GA Job List')

@push('styles')
<style>
    .main-content, .content-area { min-width: 0; }

    /* ── GA Header ─────────────────────────────── */
    .ga-hero {
        background: linear-gradient(135deg, #6f42c1 0%, #8e5bd8 45%, #d63384 100%);
        border-radius: 16px;
        padding: 22px 26px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(111, 66, 193, .28);
    }
    .ga-hero::after {
        content: "";
        position: absolute;
        right: -40px;
        top: -60px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(255,255,255,.18) 0%, transparent 70%);
        pointer-events: none;
    }
    .ga-hero h4 { font-weight: 800; letter-spacing: .3px; }
    .ga-hero .sub { opacity: .92; font-size: 12.5px; }

    /* ── Stats ─────────────────────────────────── */
    .ga-stat {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        padding: 14px 18px;
        box-shadow: 0 2px 10px rgba(17,24,39,.05);
        display: flex;
        align-items: center;
        gap: 14px;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .ga-stat:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(17,24,39,.10); }
    .ga-stat .ico {
        width: 44px; height: 44px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; color: #fff;
        flex-shrink: 0;
    }
    .ga-stat .val { font-size: 22px; font-weight: 800; line-height: 1.1; color: #111827; }
    .ga-stat .lbl { font-size: 11.5px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
    .ga-stat .pcs { font-size: 10.5px; color: #6f42c1; font-weight: 700; margin-top: 1px; }

    /* ── Filter bar ────────────────────────────── */
    .ga-filterbar {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        padding: 14px 16px;
        box-shadow: 0 2px 10px rgba(17,24,39,.05);
    }
    .ga-filterbar .form-control,
    .ga-filterbar .form-select {
        border-radius: 9px;
        font-size: 13px;
        border-color: #e5e7eb;
        padding: 7px 12px;
    }
    .ga-filterbar .form-control:focus,
    .ga-filterbar .form-select:focus {
        border-color: #8e5bd8;
        box-shadow: 0 0 0 .2rem rgba(111,66,193,.12);
    }
    .ga-toggle-btn {
        border-radius: 9px;
        font-size: 12.5px;
        font-weight: 600;
        padding: 7px 14px;
        border: 1.5px solid #e5e7eb;
        background: #fff;
        color: #6b7280;
        cursor: pointer;
        transition: all .15s ease;
    }
    .ga-toggle-btn.active {
        border-color: #dc3545;
        background: #fff5f5;
        color: #dc3545;
    }
    .ga-toggle-btn.active-prio {
        border-color: #fd7e14;
        background: #fff8f0;
        color: #e8590c;
    }
    .ga-clear-btn {
        border-radius: 9px;
        font-size: 12.5px;
        font-weight: 600;
        padding: 7px 14px;
        border: 1.5px solid #e5e7eb;
        background: #fff;
        color: #6b7280;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .ga-clear-btn:hover { background: #f8f9fa; color: #111827; }
    .ga-clear-active {
        border-color: #dc3545;
        color: #dc3545;
        background: #fff5f5;
        font-weight: 700;
    }
    .ga-clear-active:hover { background: #dc3545; color: #fff; border-color: #dc3545; }

    /* ── Table ─────────────────────────────────── */
    .ga-table-card {
        border-radius: 14px;
        border: 1px solid #eef0f4;
        box-shadow: 0 2px 10px rgba(17,24,39,.05);
        overflow: hidden;
    }
    .pipeline-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .pipeline-table thead th {
        background: #faf9ff;
        color: #6b7280;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        padding: 12px 14px;
        border-bottom: 2px solid #efeaff;
        white-space: nowrap;
    }
    .pipeline-table tbody td { padding: 12px 14px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .pipeline-table tbody tr { transition: background .12s ease; }
    .pipeline-table tbody tr:hover td { background: #faf7ff; }
    .pipeline-table tbody tr:last-child td { border-bottom: none; }

    .stage-badge {
        display: inline-block;
        font-size: 10.5px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        color: #fff;
        white-space: nowrap;
        letter-spacing: .3px;
    }
    .dept-badge {
        display: inline-block;
        font-size: 10.5px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        color: #fff;
        white-space: nowrap;
    }
    .mock-thumb {
        width: 74px;
        height: 64px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #eef0f4;
        background: #fafafa;
        box-shadow: 0 2px 6px rgba(17,24,39,.08);
        display: block;
    }
    .mock-empty {
        width: 74px; height: 64px;
        border-radius: 10px;
        background: #f8f9fa;
        border: 1px dashed #d1d5db;
        display: flex; align-items: center; justify-content: center;
        color: #c0c4cc; font-size: 18px;
    }
    .prio-chip {
        display: inline-block;
        font-size: 10px;
        font-weight: 800;
        background: #fff3cd;
        color: #856404;
        border-radius: 8px;
        padding: 2px 7px;
        margin-top: 4px;
    }
    .prio-chip.p1 { background: #dc3545; color: #fff; }
    .prio-chip.p2 { background: #fd7e14; color: #fff; }
    .prio-chip.p3 { background: #ffc107; color: #111827; }

    /* ── GA Assignment ─────────────────────────── */
    .ga-assign-cell { min-width: 200px; }
    .ga-assignee {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 10px;
        background: #f0fdf4;
        color: #15803d;
        margin-bottom: 5px;
        border: 1px solid #bbf7d0;
        min-height: 34px;
    }
    .ga-assignee .avatar {
        width: 24px; height: 24px;
        border-radius: 50%;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .ga-assignee .stage-tag {
        font-size: 12.5px;
        opacity: .85;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-left: auto;
    }
    .ga-avatar-img {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        border: 1.5px solid #86efac;
        background: #fff;
    }
    .ga-complete-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        font-size: 12px;
        font-weight: 800;
        color: #16a34a;
        background: #fff;
        border: 2px solid #86efac;
        border-radius: 50%;
        cursor: pointer;
        flex-shrink: 0;
        transition: all .15s ease;
    }
    .ga-complete-btn:hover { background: #16a34a; color: #fff; border-color: #16a34a; }
    .ga-claim-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 700;
        padding: 8px 14px;
        border-radius: 10px;
        border: 2px dashed #c4b5fd;
        background: #faf5ff;
        color: #7c3aed;
        cursor: pointer;
        transition: all .15s ease;
        margin: 2px 4px 2px 0;
        min-height: 38px;
    }
    .ga-claim-btn:hover { background: #7c3aed; color: #fff; border-style: solid; }
    .ga-claim-btn.claimed {
        border: 2px solid #86efac;
        background: #f0fdf4;
        color: #15803d;
        border-style: solid;
    }
    .ga-claim-btn.claimed:hover { background: #dcfce7; color: #166534; }
    .ga-claim-btn .fa-check { font-size: 12px; }
    .ga-claim-btn .fa-hand-paper { font-size: 13px; }
    .ga-claim-btn .stage-tag {
        font-size: 11.5px;
        opacity: .9;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    /* ── DONE badge ── */
    .done-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        font-size: 12px;
        font-weight: 800;
        background: #16a34a;
        color: #fff;
        border-radius: 50%;
        flex-shrink: 0;
        margin-left: 0;
    }
    /* ── Blackout: tapos nang stages ── */
    .ga-claim-btn.is-past,
    .ga-manager-assign.is-past {
        opacity: .6;
        filter: grayscale(.8);
        pointer-events: none;
        cursor: not-allowed;
        border-style: solid;
        border-color: #e5e7eb;
        background: #f9fafb;
        color: #9ca3af;
    }
    .ga-assignee.is-past {
        opacity: .85;
        filter: none;
        background: #f0fdf4;
        color: #15803d;
    }
    .ga-assignee.is-past .avatar { background: #9ca3af; }
    .ga-unassign-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        font-size: 15px;
        font-weight: 800;
        color: #dc3545;
        background: #fff;
        border: 1.5px solid #fecaca;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        margin-left: 6px;
        flex-shrink: 0;
        transition: all .15s ease;
    }
    .ga-unassign-link:hover { background: #dc3545; color: #fff; border-color: #dc3545; }
    .ga-myjobs-btn {
        border-radius: 9px;
        font-size: 12.5px;
        font-weight: 700;
        padding: 7px 14px;
        border: 1.5px solid #c4b5fd;
        background: #fff;
        color: #7c3aed;
        cursor: pointer;
        transition: all .15s ease;
    }
    .ga-myjobs-btn.active-mine {
        background: linear-gradient(135deg, #6f42c1, #8e5bd8);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 4px 12px rgba(111,66,193,.3);
    }
    .delayed-chip {
        display: inline-block;
        font-size: 10px;
        font-weight: 800;
        background: #fdecea;
        color: #dc3545;
        border-radius: 8px;
        padding: 2px 7px;
        margin-top: 4px;
    }
    .sales-no { font-size: 12px; font-weight: 700; color: #111827; }
    .sales-date { font-size: 10.5px; color: #9ca3af; }

    .empty-state { text-align: center; padding: 56px 20px; }
    .empty-state .ico { font-size: 44px; color: #d1d5db; margin-bottom: 12px; }
    .empty-state h6 { color: #6b7280; font-weight: 700; }
    .empty-state p { color: #9ca3af; font-size: 13px; max-width: 420px; margin: 0 auto; }

    /* ── Custom confirm modal ── */
    .ga-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(17, 24, 39, .55);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1050;
        padding: 16px;
    }
    .ga-modal-overlay.show { display: flex; }
    .ga-modal {
        background: #fff;
        border-radius: 20px;
        max-width: 400px;
        width: 100%;
        padding: 28px 26px 22px;
        text-align: center;
        box-shadow: 0 24px 60px rgba(17, 24, 39, .28);
        transform: translateY(14px) scale(.96);
        opacity: 0;
        transition: all .2s ease;
    }
    .ga-modal-overlay.show .ga-modal {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    .ga-modal-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        font-size: 26px;
    }
    .ga-modal-icon.icon-success { background: #f0fdf4; color: #16a34a; border: 2px solid #86efac; }
    .ga-modal-icon.icon-danger { background: #fdecea; color: #dc3545; border: 2px solid #fecaca; }
    .ga-modal-icon.icon-warn { background: #fff8f0; color: #e8590c; border: 2px solid #ffd8a8; }
    .ga-modal h5 { font-weight: 800; color: #111827; margin-bottom: 6px; font-size: 17px; }
    .ga-modal .ga-modal-msg { font-size: 13.5px; color: #6b7280; line-height: 1.55; margin-bottom: 20px; }
    .ga-modal .ga-modal-msg strong { color: #374151; }
    .ga-modal-btns { display: flex; gap: 10px; }
    .ga-modal-btns .btn {
        flex: 1;
        border-radius: 12px;
        font-weight: 700;
        font-size: 13.5px;
        padding: 10px 14px;
        border: none;
        cursor: pointer;
        transition: all .15s ease;
    }
    .ga-modal-btns .btn-ga-cancel {
        background: #f3f4f6;
        color: #6b7280;
    }
    .ga-modal-btns .btn-ga-cancel:hover { background: #e5e7eb; color: #374151; }
    .ga-modal-btns .btn-ga-confirm {
        background: linear-gradient(135deg, #16a34a, #22c55e);
        color: #fff;
        box-shadow: 0 6px 16px rgba(22, 163, 74, .3);
    }
    .ga-modal-btns .btn-ga-confirm:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(22, 163, 74, .4); }
    .ga-modal-btns .btn-ga-confirm.danger {
        background: linear-gradient(135deg, #dc3545, #f0746d);
        box-shadow: 0 6px 16px rgba(220, 53, 69, .3);
    }
    .ga-modal-btns .btn-ga-confirm.danger:hover { box-shadow: 0 8px 20px rgba(220, 53, 69, .4); }
    .ga-modal-btns .btn-ga-confirm.warn {
        background: linear-gradient(135deg, #e8590c, #fd7e14);
        box-shadow: 0 6px 16px rgba(232, 89, 12, .3);
    }
    .ga-modal-btns .btn-ga-confirm.warn:hover { box-shadow: 0 8px 20px rgba(232, 89, 12, .4); }
    .ga-modal-stage-chip {
        display: inline-block;
        font-size: 10.5px;
        font-weight: 800;
        padding: 3px 12px;
        border-radius: 20px;
        color: #fff;
        background: #6f42c1;
        margin-bottom: 12px;
        letter-spacing: .4px;
        text-transform: uppercase;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- ── Hero header ── --}}
    <div class="ga-hero mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-1">🎨 GA Job List</h4>
            <div class="sub">Orders tagged <b>FOR SAMPLE</b> / <b>FOR FORMAT</b> / <b>PRINTING</b> — read-only view</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('sales.prototype.priority-slideshow') }}" class="btn btn-sm fw-semibold" style="border-radius:10px;background:#fff;border:none;color:#6f42c1;font-weight:700;" title="I-play sa screen ang mockups ayon sa priority (1,2,3...)">
                <i class="fas fa-play-circle me-1"></i> Play Mockup Slideshow
            </a>
            <a href="{{ route('sales.prototype.backjobs') }}" class="btn btn-sm fw-semibold" style="border-radius:10px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.35);color:#fff;">
                <i class="fas fa-tools me-1"></i> Backjob List
            </a>
            <a href="{{ route('sales.prototype.ga-dashboard') }}" class="btn btn-sm fw-semibold" style="border-radius:10px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.35);color:#fff;">
                <i class="fas fa-chart-bar me-1"></i> GA Dashboard
            </a>
        </div>
    </div>

    {{-- ── Stats row ── --}}
    @php
        $stageCounts = [];
        $stagePcs = [];
        foreach ($sales->items() as $s) {
            $sl = $s->production_stage ?: ($statusToStage[$s->kanban_status ?? 'new'] ?? 'HOLD');
            $stageCounts[$sl] = ($stageCounts[$sl] ?? 0) + 1;
            // Bilang ng pcs (total quantity) per stage (Andrew 2026-09-18)
            $svcItems = is_string($s->services) ? json_decode($s->services, true) : ($s->services ?? []);
            $pcs = 0;
            foreach ((array) $svcItems as $svc) {
                if (is_array($svc)) $pcs += (int) ($svc['quantity'] ?? 1);
            }
            $stagePcs[$sl] = ($stagePcs[$sl] ?? 0) + $pcs;
        }
        $delayedCount = collect($sales->items())->where('is_delayed', 1)->count();
        $prioCount = collect($sales->items())->whereNotNull('priority')->count();
        $stageColors = [
            'FOR SAMPLE'   => ['#fd7e14', '🎯'],
            'FOR APPROVAL' => ['#e8590c', '📝'],
            'FOR FORMAT'   => ['#6f42c1', '🎨'],
            'PRINTING'     => ['#0d6efd', '🖨️'],
            'PRESSING'     => ['#198754', '🧵'],
            'CUTTING'      => ['#e8590c', '✂️'],
        ];
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="ga-stat">
                <div class="ico" style="background:linear-gradient(135deg,#6f42c1,#8e5bd8);"><i class="fas fa-palette"></i></div>
                <div>
                    <div class="val" id="gaStatTotal">{{ $sales->total() }}</div>
                    <div class="lbl">Total Jobs</div>
                </div>
            </div>
        </div>
        @foreach(['FOR SAMPLE' => 'Sample', 'FOR FORMAT' => 'Format', 'PRINTING' => 'Printing'] as $st => $lbl)
        <div class="col-6 col-md-4 col-xl-2">
            <div class="ga-stat">
                <div class="ico" style="background:{{ $stageColors[$st][0] }};">{{ $stageColors[$st][1] }}</div>
                <div>
                    <div class="val" data-ga-stage="{{ $st }}">{{ $stageCounts[$st] ?? 0 }}</div>
                    <div class="lbl">{{ $lbl }}</div>
                    <div class="pcs">{{ number_format($stagePcs[$st] ?? 0) }} pcs</div>
                </div>
            </div>
        </div>
        @endforeach
        <div class="col-6 col-md-4 col-xl-2">
            <div class="ga-stat">
                <div class="ico" style="background:linear-gradient(135deg,#198754,#4cc98e);"><i class="fas fa-check-double"></i></div>
                <div>
                    <div class="val">{{ $completedCount }}</div>
                    <div class="lbl">Completed (SEWING+)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="ga-stat">
                <div class="ico" style="background:linear-gradient(135deg,#dc3545,#f0746d);"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="val">{{ $delayedCount }}</div>
                    <div class="lbl">Delayed</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filter bar ── --}}
    <form method="GET" action="{{ route('sales.prototype.ga-order-list') }}" class="ga-filterbar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">🔍 Search</label>
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Sales # o customer...">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Stage</label>
                <select name="stage" class="form-select">
                    <option value="">All Stages</option>
                    @foreach(array_keys($prodStageMap) as $stageName)
                        @if(in_array($stageName, ['FOR SAMPLE','FOR APPROVAL','FOR FORMAT','PRINTING','PRESSING','CUTTING']))
                        <option value="{{ $stageName }}" {{ $stage === $stageName ? 'selected' : '' }}>{{ $stageName }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Department</label>
                <select name="dept" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departmentLabels as $did => $dname)
                    <option value="{{ $did }}" {{ (string)$dept === (string)$did ? 'selected' : '' }}>{{ $dname }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">⏰ Due date</label>
                <select name="due" class="form-select">
                    <option value="">All due dates</option>
                    <option value="overdue" {{ $dueFilter === 'overdue' ? 'selected' : '' }}>🔴 Overdue</option>
                    <option value="today" {{ $dueFilter === 'today' ? 'selected' : '' }}>🟠 Due today</option>
                    <option value="soon" {{ $dueFilter === 'soon' ? 'selected' : '' }}>🟡 Due in 3 days</option>
                    <option value="week" {{ $dueFilter === 'week' ? 'selected' : '' }}>🔵 Due this week</option>
                    <option value="none" {{ $dueFilter === 'none' ? 'selected' : '' }}>⚪ No due date</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">↕ Sort due date</label>
                <select name="due_sort" class="form-select">
                    <option value="">Default</option>
                    <option value="asc" {{ $dueSort === 'asc' ? 'selected' : '' }}>↑ Earliest first (asc)</option>
                    <option value="desc" {{ $dueSort === 'desc' ? 'selected' : '' }}>↓ Latest first (desc)</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Date from</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Date to</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2 justify-content-md-end">
                <button type="submit" class="btn btn-primary btn-sm px-3" style="background:linear-gradient(135deg,#6f42c1,#8e5bd8);border:none;border-radius:9px;font-weight:600;">Filter</button>
                <a href="{{ route('sales.prototype.ga-order-list') }}" class="ga-clear-btn {{ ($q !== '' || $stage !== '' || $dept !== '' || $dateFrom !== '' || $dateTo !== '' || $delayedOnly || $priorityOnly || $myJobs || filled($gaFilter) || $dueFilter !== '' || $dueSort !== '') ? 'ga-clear-active' : '' }}" title="Reset lahat ng filter"><i class="fas fa-undo"></i> Reset</a>
            </div>
            <div class="col-12 d-flex gap-2 flex-wrap pt-1">
                <button type="submit" name="my_jobs" value="1" class="ga-myjobs-btn {{ $myJobs ? 'active-mine' : '' }}" onclick="this.form.my_jobs.value = this.classList.contains('active-mine') ? '' : '1'"><i class="fas fa-user-check"></i> My Jobs</button>
                <button type="submit" name="delayed" value="1" class="ga-toggle-btn {{ $delayedOnly ? 'active' : '' }}" onclick="this.form.delayed.value = this.classList.contains('active') ? '' : '1'">⏰ Delayed only</button>
                <button type="submit" name="priority" value="1" class="ga-toggle-btn {{ $priorityOnly ? 'active-prio' : '' }}" onclick="this.form.priority.value = this.classList.contains('active-prio') ? '' : '1'">⭐ With priority</button>
            </div>
            @if($gaUsers->count() > 0)
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">👥 Assigned GA</label>
                <select name="ga" class="form-select" onchange="this.form.submit()">
                    <option value="">All GAs</option>
                    @foreach($gaUsers as $gu)
                    <option value="{{ $gu->id }}" {{ (string)$gaFilter === (string)$gu->id ? 'selected' : '' }}>{{ $gu->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
    </form>

    {{-- ── Table ── --}}
    <div class="ga-table-card">
        <div class="table-responsive" style="max-height:calc(100vh - 300px);overflow:auto;">
            <table class="pipeline-table" id="gaOrderTable">
                <thead>
                    <tr>
                        <th>Sales #</th>
                        <th>Mock Up</th>
                        <th>Description</th>
                        <th class="text-center">Qty</th>
                        <th>Production Status</th>
                        <th>Due Date</th>
                        <th>Department</th>
                        <th>Customer</th>
                        <th>GA Assignment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        @php
                            $stageLabel = $sale->production_stage ?: ($statusToStage[$sale->kanban_status ?? 'new'] ?? 'HOLD');
                            $deptName = $departmentLabels[$sale->department_id] ?? 'Other';
                            $deptColor = $departmentColors[$sale->department_id] ?? '#6c757d';

                            // Mock up thumbnail
                            $mockups = is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? []);
                            $mainMockup = null;
                            foreach ($mockups as $m) {
                                if (is_array($m) && !empty($m['is_main'])) { $mainMockup = $m; break; }
                            }
                            if (!$mainMockup && !empty($mockups)) $mainMockup = $mockups[0];
                            $firstMockupUrl = is_string($mainMockup) ? $mainMockup : ($mainMockup['url'] ?? '');

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

                            $stageBg = $stageColors[$stageLabel][0] ?? ($stageLabel === 'FOR SAMPLE' || $stageLabel === 'FOR APPROVAL' ? '#fd7e14' : '#0d6efd');

                            // GA assignments for this sale
                            $saleAssigns = $assignments[$sale->id] ?? collect();
                            $assignMap = [];
                            foreach ($saleAssigns as $asg) {
                                $assignMap[$asg->stage] = $asg;
                            }
                            $currentUser = auth()->user();

                            // Production Status editor roles: CEO/COO/Prod Manager/QA lang ang may dropdown
                            $isEditor = $currentUser && ($currentUser->isAdmin() || $currentUser->isCoo() || $currentUser->isProdManager() || $currentUser->isQa());
                            // Photo-lock override (same rule as updateStatus server-side): admin/manager/class-scoped lang ang nakaka-bypass
                            $canOverridePhotos = $currentUser && ($currentUser->isAdmin() || $currentUser->role === 'manager' || $currentUser->isClassScoped());
                            $dImgs = is_string($sale->design_images) ? (json_decode($sale->design_images, true) ?: []) : ($sale->design_images ?: []);
                            $hasFileShot = collect($dImgs)->contains('type', 'file_screenshot');
                            $hasColorShot = collect($dImgs)->contains('type', 'sample_color');
                            // PAYMENT LOCK (restored 2026-09-05): hindi ma-DONE habang may balance (kahit ₱1)
                            $balanceDue = (float) $sale->balance_due_computed;

                            // DUE DATE (Andrew 2026-09-10): effective = rescheduled_date kung meron, else estimated_completion_date.
                            // Ipakita dito para hindi na kailangan pang pumunta sa Calendar.
                            $effDue = $sale->rescheduled_date ?: $sale->estimated_completion_date;
                            $dueInfo = null;
                            if ($effDue) {
                                $dueD = \Carbon\Carbon::parse($effDue)->startOfDay();
                                $todayD = \Carbon\Carbon::now()->startOfDay();
                                $diffDays = (int) floor(($dueD->getTimestamp() - $todayD->getTimestamp()) / 86400);
                                if ($diffDays < 0)       { $dueInfo = ['#dc3545', '🔴 ' . abs($diffDays) . 'd overdue']; }
                                elseif ($diffDays === 0) { $dueInfo = ['#fd7e14', '🟠 Due today']; }
                                elseif ($diffDays <= 3)  { $dueInfo = ['#f59e0b', '🟡 ' . $diffDays . 'd left']; }
                                elseif ($diffDays <= 7)  { $dueInfo = ['#0ea5e9', '🔵 ' . $diffDays . 'd left']; }
                                else                     { $dueInfo = ['#10b981', '🟢 ' . $diffDays . 'd left']; }
                            }
                        @endphp
                        <tr data-sale-id="{{ $sale->id }}">
                            <td style="max-width:150px;">
                                <div class="sales-no">{{ $sale->sales_number }}</div>
                                <div class="sales-date">{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y') }}</div>
                                @if($sale->is_delayed)<span class="delayed-chip">⏰ Delayed</span>@endif
                                @if(!empty($sale->priority))<span class="prio-chip p{{ $sale->priority <= 3 ? $sale->priority : '3' }}">⭐ Prio {{ $sale->priority }}</span>@endif
                            </td>
                            <td>
                                @if($firstMockupUrl)
                                    <img src="{{ $firstMockupUrl }}" alt="mockup" class="mock-thumb" onerror="this.outerHTML='<div class=\'mock-empty\'><i class=\'fas fa-image\'></i></div>'">
                                @else
                                    <div class="mock-empty"><i class="fas fa-image"></i></div>
                                @endif
                            </td>
                            <td style="max-width:260px;">
                                <div style="font-size:12px;line-height:1.4;overflow:hidden;text-overflow:ellipsis;" title="{{ $description }}">{{ \Illuminate\Support\Str::limit($description, 60) }}</div>
                                @if($isEditor)
                                    <select class="form-select form-select-sm prod-status-select" data-sale-id="{{ $sale->id }}" data-current="{{ $stageLabel }}" title="Production Status — ilipat ang order sa susunod na stage" style="font-size:11px;min-width:160px;max-width:100%;padding:2px 6px;margin-top:4px;">
                                        @foreach($prodStageMap as $st => $stStatus)
                                            <option value="{{ $st }}" data-status="{{ $stStatus }}" {{ $stageLabel === $st ? 'selected' : '' }}
                                                @if($stStatus === 'completed' && $balanceDue > 0) disabled title="🔒 May pending balance (₱{{ number_format($balanceDue, 2) }}) — bayaran muna bago i-DONE"
                                                @elseif(!$canOverridePhotos && !$hasFileShot && $stStatus === 'sample_approval') disabled title="🔒 Kulang File Screenshot"
                                                @elseif(!$canOverridePhotos && $hasFileShot && !$hasColorShot && in_array($stStatus, ['design','production','quality_check','ready_for_delivery','delivered','completed'], true)) disabled title="🔒 Kulang Approved Sample Color"
                                                @endif>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>
                            <td class="text-center" style="font-weight:700;color:#374151;">{{ $totalQty ?: '—' }}</td>
                            <td>
                                <span class="stage-badge" style="background:{{ $stageBg }};">{{ $stageLabel }}</span>
                            </td>
                            <td style="white-space:nowrap;">
                                @if($effDue)
                                    <div style="font-weight:600;font-size:12px;color:#374151;">{{ \Carbon\Carbon::parse($effDue)->format('M d, Y') }}</div>
                                    <span class="badge" style="background:{{ $dueInfo[0] }};font-size:10px;">{{ $dueInfo[1] }}</span>
                                    @if($sale->rescheduled_date && $sale->estimated_completion_date && \Carbon\Carbon::parse($sale->rescheduled_date)->ne($sale->estimated_completion_date))
                                        <div style="font-size:10px;color:#9ca3af;">orig: {{ \Carbon\Carbon::parse($sale->estimated_completion_date)->format('M d') }}</div>
                                    @endif
                                @else
                                    <span class="text-muted" style="font-size:11px;">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="dept-badge" style="background:{{ $deptColor }};">{{ $deptName }}</span>
                            </td>
                            <td style="max-width:180px;">
                                <div style="font-size:12px;color:#374151;">{{ $sale->customer_name ?? '—' }}</div>
                            </td>
                            <td class="ga-assign-cell">
                                @php
                                    // Stage lock: stages BEFORE the current production stage are done → blacked out
                                    $stageOrder = ['FOR SAMPLE' => 0, 'FOR APPROVAL' => 1, 'FOR FORMAT' => 2, 'PRINTING' => 3, 'PRESSING' => 4, 'CUTTING' => 5];
                                    $curIdx = $stageOrder[$stageLabel] ?? 0;

                                    // First-name collision map: show first name lang kung walang kapangalan
                                    $firstNameCounts = [];
                                    foreach ($gaUsers as $gu) {
                                        $fn = strtolower(trim(explode(' ', trim($gu->name))[0] ?? ''));
                                        $firstNameCounts[$fn] = ($firstNameCounts[$fn] ?? 0) + 1;
                                    }
                                    $nameOf = function ($full) use ($firstNameCounts) {
                                        $full = trim($full ?: 'GA');
                                        $first = explode(' ', $full)[0] ?? $full;
                                        return ($firstNameCounts[strtolower($first)] ?? 1) > 1 ? $full : $first;
                                    };
                                @endphp
                                @foreach(['FOR SAMPLE' => 'Sample', 'FOR FORMAT' => 'Format', 'PRINTING' => 'Print'] as $stName => $stLbl)
                                    @php
                                        $asg = $assignMap[$stName] ?? null;
                                        $isPast = ($stageOrder[$stName] ?? 0) < $curIdx;
                                    @endphp
                                    @if($asg)
                                        @php
                                            $asgUser = $asg->user;
                                            $asgName = $asgUser->name ?? 'GA';
                                            $asgAvatar = $asgUser->avatar_url ?? null;
                                            $asgDone = !empty($asg->completed_at);
                                        @endphp
                                        <div class="ga-assignee {{ $isPast ? 'is-past' : '' }} {{ $asgDone ? 'is-done' : '' }}" title="{{ $stName }} — {{ $asgName }}{{ $asgDone ? ' (DONE)' : '' }}">
                                            @if($asgDone || $isPast)
                                                <span class="done-badge"><i class="fas fa-check"></i></span>
                                            @elseif($currentUser && ($currentUser->isManager() || $currentUser->isQa() || $asg->user_id === $currentUser->id))
                                                <span class="ga-complete-btn" data-action="complete" data-sale="{{ $sale->id }}" data-stage="{{ $stName }}" title="Markahan bilang DONE"><i class="fas fa-check"></i></span>
                                            @endif
                                            @if($asgAvatar)
                                                <img src="{{ $asgAvatar }}" alt="{{ $asgName }}" class="ga-avatar-img">
                                            @else
                                                <span class="avatar">{{ strtoupper(substr($asgName, 0, 1)) }}</span>
                                            @endif
                                            <span>{{ $nameOf($asgName) }}</span>
                                            <span class="stage-tag">{{ $stLbl }}</span>
                                            @if(!$isPast && $currentUser && ($currentUser->isManager() || $currentUser->isQa() || $asg->user_id === $currentUser->id))
                                                <span class="ga-unassign-link" data-action="unassign" data-sale="{{ $sale->id }}" data-stage="{{ $stName }}" title="Alisin ang assignment">✕</span>
                                            @endif
                                        </div>
                                    @elseif($currentUser && ($currentUser->isManager() || $currentUser->isQa()) && $gaUsers->count() > 0)
                                        <div style="margin-bottom:4px;">
                                            <select class="form-select form-select-sm ga-manager-assign {{ $isPast ? 'is-past' : '' }}" data-sale="{{ $sale->id }}" data-stage="{{ $stName }}" {{ $isPast ? 'disabled' : '' }} style="font-size:12px;padding:5px 8px;width:100%;border-radius:9px;">
                                                <option value="">{{ $isPast ? '✓ ' . $stLbl . ' — done' : 'Assign ' . $stLbl . ' →' }}</option>
                                                @foreach($gaUsers as $gu)
                                                <option value="{{ $gu->id }}">{{ $gu->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @elseif($currentUser && $currentUser->isGa())
                                        <button class="ga-claim-btn {{ $isPast ? 'is-past' : '' }}" data-action="assign" data-sale="{{ $sale->id }}" data-stage="{{ $stName }}" {{ $isPast ? 'disabled' : '' }}
                                            title="{{ $isPast ? 'Tapos na ang ' . $stLbl . ' stage — hindi na pwedeng i-claim' : $stLbl . ' — i-claim itong stage' }}">
                                            @if($isPast)
                                                <i class="fas fa-check"></i> <span class="stage-tag">{{ $stLbl }} DONE</span>
                                            @else
                                                <i class="fas fa-hand-paper"></i> <span class="stage-tag">{{ $stLbl }}</span>
                                            @endif
                                        </button>
                                    @else
                                        <span class="text-muted" style="font-size:11px;">—</span>
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-state">
                                <div class="ico"><i class="fas fa-palette"></i></div>
                                <h6>Walang nahanap na jobs</h6>
                                <p>Subukan mong baguhin ang filters o i-clear ang lahat para makita ang lahat ng GA jobs.</p>
                                <a href="{{ route('sales.prototype.ga-order-list') }}" class="btn btn-sm btn-outline-secondary mt-2">Clear filters</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $sales->links() }}
    </div>

    {{-- ── Activity History ── --}}
    <div class="ga-table-card mt-4">
        <div class="d-flex align-items-center gap-2 px-3 py-3" style="border-bottom:1px solid #efeaff;background:#faf9ff;">
            <i class="fas fa-history" style="color:#6f42c1;"></i>
            <h6 class="mb-0 fw-bold" style="color:#374151;">Activity History</h6>
            <span class="badge ms-auto" style="background:#efeaff;color:#6f42c1;font-size:11px;font-weight:700;">Huling {{ $activityLogs->count() }} na galaw</span>
        </div>
        @if($activityLogs->isEmpty())
            <div class="empty-state" style="padding:32px 20px;">
                <div class="ico"><i class="fas fa-clipboard-list"></i></div>
                <h6>Wala pang activity</h6>
                <p>Dito makikita ang lahat ng assign, unassign, at DONE marks — para sigurado kung sino ang gumalaw.</p>
            </div>
        @else
            <div class="table-responsive" style="max-height:380px;overflow:auto;">
                <table class="pipeline-table">
                    <thead>
                        <tr>
                            <th>Kailan</th>
                            <th>Sales #</th>
                            <th>Stage</th>
                            <th>GA</th>
                            <th>Action</th>
                            <th>Ginawa ni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activityLogs as $log)
                            @php
                                $actionMeta = [
                                    'assigned'    => ['label' => 'Claimed / Assigned', 'color' => '#7c3aed', 'bg' => '#faf5ff'],
                                    'unassigned'  => ['label' => 'Unassigned',          'color' => '#dc3545', 'bg' => '#fdecea'],
                                    'completed'   => ['label' => '✓ DONE',              'color' => '#15803d', 'bg' => '#f0fdf4'],
                                    'uncompleted' => ['label' => 'DONE binalik',        'color' => '#e8590c', 'bg' => '#fff8f0'],
                                ];
                                $am = $actionMeta[$log->action] ?? ['label' => ucfirst($log->action), 'color' => '#374151', 'bg' => '#f3f4f6'];
                            @endphp
                            <tr>
                                <td style="white-space:nowrap;font-size:12px;color:#6b7280;">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                                <td style="font-size:12px;font-weight:700;color:#111827;">{{ $log->sale?->sales_number ?? '—' }}</td>
                                <td>
                                    <span class="stage-badge" style="background:{{ $stageColors[$log->stage][0] ?? '#6c757d' }};font-size:10px;">{{ $log->stage }}</span>
                                </td>
                                <td style="font-size:12.5px;font-weight:600;color:#374151;">
                                    <span class="avatar" style="display:inline-flex;width:20px;height:20px;border-radius:50%;background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;font-size:9px;font-weight:800;align-items:center;justify-content:center;margin-right:6px;">{{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}</span>
                                    {{ $log->user?->name ?? '—' }}
                                </td>
                                <td>
                                    <span class="dept-badge" style="background:{{ $am['color'] }};font-size:10.5px;">{{ $am['label'] }}</span>
                                </td>
                                <td style="font-size:12px;color:#6b7280;">{{ $log->actor?->name ?? ($log->actor_id ? 'User #' . $log->actor_id : '—') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ── Custom confirm modal ── --}}
<div class="ga-modal-overlay" id="gaModalOverlay" onclick="if(event.target===this) gaCloseModal()">
    <div class="ga-modal">
        <div class="ga-modal-icon" id="gaModalIcon"><i class="fas fa-check"></i></div>
        <div><span class="ga-modal-stage-chip" id="gaModalStage"></span></div>
        <h5 id="gaModalTitle">Confirm</h5>
        <div class="ga-modal-msg" id="gaModalMsg"></div>
        <div class="ga-modal-btns">
            <button class="btn btn-ga-cancel" onclick="gaCloseModal()">Cancel</button>
            <button class="btn btn-ga-confirm" id="gaModalConfirm">Confirm</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    async function doAction(action, saleId, stage, userId) {
        const url = action === 'assign'
            ? '/sales/prototype/' + saleId + '/ga-assign'
            : (action === 'complete'
                ? '/sales/prototype/' + saleId + '/ga-complete'
                : '/sales/prototype/' + saleId + '/ga-unassign');
        const body = action === 'assign'
            ? { stage: stage, user_id: userId || '' }
            : { stage: stage };
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed.');
            }
        } catch (e) {
            alert('Request failed.');
        }
    }

    let pendingAction = null;

    window.gaOpenModal = function (opts) {
        const overlay = document.getElementById('gaModalOverlay');
        const icon = document.getElementById('gaModalIcon');
        const stage = document.getElementById('gaModalStage');
        const title = document.getElementById('gaModalTitle');
        const msg = document.getElementById('gaModalMsg');
        const confirmBtn = document.getElementById('gaModalConfirm');

        icon.className = 'ga-modal-icon icon-' + (opts.icon || 'success');
        icon.innerHTML = '<i class="fas fa-' + (opts.fa || 'check') + '"></i>';
        stage.textContent = opts.stage || '';
        stage.style.display = opts.stage ? '' : 'none';
        title.textContent = opts.title || 'Confirm';
        msg.innerHTML = opts.msg || '';
        confirmBtn.className = 'btn btn-ga-confirm' + (opts.btnClass ? ' ' + opts.btnClass : '');
        confirmBtn.textContent = opts.btnLabel || 'Confirm';
        pendingAction = opts.onConfirm || null;

        overlay.classList.add('show');
        confirmBtn.focus();
    };

    window.gaCloseModal = function () {
        document.getElementById('gaModalOverlay').classList.remove('show');
        pendingAction = null;
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') gaCloseModal();
    });

    document.getElementById('gaModalConfirm').addEventListener('click', function () {
        if (pendingAction) pendingAction();
        gaCloseModal();
    });

    document.addEventListener('click', function (e) {
        const el = e.target.closest('[data-action]');
        if (!el) return;
        const saleId = el.getAttribute('data-sale');
        const stage = el.getAttribute('data-stage');
        const action = el.getAttribute('data-action');
        if (action === 'assign') {
            const userId = el.getAttribute('data-user') || '';
            doAction('assign', saleId, stage, userId);
        } else if (action === 'complete') {
            const isDone = el.closest('.ga-assignee')?.classList.contains('is-done');
            if (isDone) {
                gaOpenModal({
                    icon: 'warn',
                    fa: 'undo',
                    stage: stage,
                    title: 'Bawiin ang DONE?',
                    msg: 'Babalik sa <strong>in-progress</strong> ang stage na ito. Sigurado ka bang hindi pa talaga tapos?',
                    btnLabel: 'Oo, bawiin',
                    btnClass: 'warn',
                    onConfirm: () => doAction('complete', saleId, stage, '')
                });
            } else {
                gaOpenModal({
                    icon: 'success',
                    fa: 'check',
                    stage: stage,
                    title: 'Markahan bilang DONE?',
                    msg: 'Iko-confirm na tapos mo na ang <strong>' + stage + '</strong> stage sa job na ito.',
                    btnLabel: 'Oo, DONE na',
                    btnClass: '',
                    onConfirm: () => doAction('complete', saleId, stage, '')
                });
            }
        } else if (action === 'unassign') {
            gaOpenModal({
                icon: 'danger',
                fa: 'times',
                stage: stage,
                title: 'Alisin ang assignment?',
                msg: 'Tatanggalin mo ang GA sa <strong>' + stage + '</strong> stage. Pwedeng mag-claim ulit ang iba.',
                btnLabel: 'Oo, alisin',
                btnClass: 'danger',
                onConfirm: () => doAction('unassign', saleId, stage)
            });
        }
    });

    document.addEventListener('change', function (e) {
        const sel = e.target.closest('.ga-manager-assign');
        if (!sel) return;
        const saleId = sel.getAttribute('data-sale');
        const stage = sel.getAttribute('data-stage');
        if (sel.value) {
            doAction('assign', saleId, stage, sel.value);
        }
    });

    // Clickable row → Sales Information page (skip clicks on buttons/selects/links)
    document.addEventListener('click', function (e) {
        if (e.target.closest('button, a, select, .ga-assign-cell, [data-action], .ga-manager-assign, input, label')) return;
        const row = e.target.closest('tr[data-sale-id]');
        if (!row) return;
        const saleId = row.getAttribute('data-sale-id');
        window.location.href = '/sales/prototype/' + saleId;
    });

    // === PRODUCTION STATUS DROPDOWN (Description cell) — same endpoint as manager order list ===
    const GA_FILTER_STAGES = ['FOR SAMPLE', 'FOR APPROVAL', 'FOR FORMAT', 'PRINTING', 'PRESSING', 'CUTTING'];
    const STAGE_BG = Object.assign({}, @json($stageColors), {
        'HOLD': ['#6c757d', ''], 'SEWING': ['#198754', ''], 'QA': ['#6f42c1', ''],
        'DISPATCH': ['#fd7e14', ''], 'UNPAID': ['#dc3545', ''], 'DONE': ['#198754', '']
    });
    // === In-place stat counters (para hindi na mag-full reload kapag nagpalit ng production status) ===
    function gaAdjustCounts(removeDelta, newStage, oldStage) {
        var readInt = function (el) { return parseInt((el.textContent || '').replace(/[^0-9]/g, ''), 10) || 0; };
        var totalEl = document.getElementById('gaStatTotal');
        if (totalEl && removeDelta < 0) totalEl.textContent = Math.max(0, readInt(totalEl) + removeDelta);
        var bump = function (stage, d) {
            var el = document.querySelector('[data-ga-stage="' + stage + '"]');
            if (el) el.textContent = Math.max(0, readInt(el) + d);
        };
        if (oldStage && oldStage !== newStage) bump(oldStage, -1);
        if (newStage && oldStage !== newStage) bump(newStage, 1);
    }

    document.addEventListener('change', function (e) {
        const sel = e.target.closest('.prod-status-select');
        if (!sel) return;
        const saleId = sel.getAttribute('data-sale-id');
        const stage = sel.value;
        const newStatus = sel.options[sel.selectedIndex].getAttribute('data-status');
        const oldStage = sel.getAttribute('data-current');
        const row = sel.closest('tr[data-sale-id]');
        sel.disabled = true;
        fetch('/sales/prototype/' + saleId + '/update-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ kanban_status: newStatus, production_stage: stage })
        })
        .then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, data: d }; }); })
        .then(function (res) {
            sel.disabled = false;
            if (res.ok && res.data.success) {
                sel.setAttribute('data-current', stage);
                const badge = row ? row.querySelector('.stage-badge') : null;
                if (badge) {
                    badge.textContent = stage;
                    if (STAGE_BG[stage] && STAGE_BG[stage][0]) badge.style.background = STAGE_BG[stage][0];
                }
                const urlParams = new URLSearchParams(window.location.search);
                const hasStageFilter = !!urlParams.get('stage');
                if (GA_FILTER_STAGES.indexOf(stage) === -1 || hasStageFilter) {
                    // Umalis sa GA scope / may stage filter → alisin na lang in place (fade),
                    // HINDI na kailangan i-refresh ang buong page (Andrew 2026-09-24).
                    if (row) {
                        row.style.transition = 'opacity .3s';
                        row.style.opacity = '0';
                        setTimeout(function () {
                            row.remove();
                            gaAdjustCounts(-1, stage, oldStage);
                        }, 320);
                    }
                } else {
                    // Nanatili sa scope → i-update lang ang stage counters in place.
                    gaAdjustCounts(0, stage, oldStage);
                }
            } else {
                sel.value = oldStage;
                alert('⚠️ ' + (res.data.message || 'Failed to update status.'));
            }
        })
        .catch(function () {
            sel.disabled = false;
            sel.value = oldStage;
            alert('❌ Network error. Please try again.');
        });
    });
})();
</script>
@endpush
