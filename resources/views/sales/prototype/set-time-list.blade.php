@extends('layouts.app')

@section('title', 'Set Time List')

@push('styles')
<style>
    .stl-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 18px; flex-wrap: wrap; gap: 10px;
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #3b82f6 100%);
        border-radius: 14px; padding: 18px 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.18);
    }
    .stl-header h4 { margin: 0; color: #fff; font-weight: 700; display: flex; align-items: center; gap: 10px; }
    .stl-header .stl-sub { color: rgba(255,255,255,0.8); font-size: 13px; margin-top: 3px; }
    .stl-table { font-size: 13px; }
    .stl-table th { white-space: nowrap; background: #f8f9fa; position: sticky; top: 0; z-index: 2; }
    .stl-table td { vertical-align: middle; }
    .stl-row { cursor: grab; }
    .stl-row:hover { background: #eff6ff !important; }
    .stl-row.dragging { opacity: .45; background: #dbeafe !important; }
    .stl-seq {
        display: inline-flex; align-items: center; justify-content: center;
        width: 26px; height: 26px; border-radius: 50%; background: #2563eb; color: #fff;
        font-size: 12px; font-weight: 700;
    }
    .stl-drag { color: #94a3b8; font-size: 15px; }
    .stl-note { max-width: 360px; white-space: pre-wrap; word-break: break-word; }
    .stl-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 10px; }
    .stl-saved {
        position: fixed; top: 20px; right: 20px; z-index: 99999;
        background: #198754; color: #fff; padding: 10px 16px; border-radius: 8px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.25); font-size: 13px; font-weight: 600;
    }
    .stl-hint { font-size: 12px; color: #64748b; }

    /* === Filter toolbar === */
    .stl-filter-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
        box-shadow: 0 2px 10px rgba(15,23,42,0.06); padding: 16px 18px; margin-bottom: 18px;
    }
    .stl-filter-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 12px 14px; align-items: end;
    }
    .stl-field { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
    .stl-field label {
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
        color: #64748b; margin: 0; display: flex; align-items: center; gap: 5px;
    }
    .stl-field .form-control, .stl-field .form-select { border-radius: 9px; font-size: 13px; height: 38px; width: 100%; }
    .stl-search-wrap { position: relative; }
    .stl-search-wrap .fa-magnifying-glass { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 12px; pointer-events: none; }
    .stl-search-wrap input { padding-left: 32px; }
    .stl-filter-actions { display: flex; gap: 8px; align-items: center; grid-column: -1 / 1; justify-content: flex-end; padding-top: 4px; border-top: 1px dashed #eef2f7; margin-top: 4px; padding-top: 12px; }
    .stl-filter-actions .btn { border-radius: 9px; font-weight: 600; height: 38px; display: inline-flex; align-items: center; gap: 6px; }
    .stl-apply { background: #2563eb; color: #fff; border: none; }
    .stl-apply:hover { background: #1d4ed8; color: #fff; }
    .stl-clear { background: #fff; color: #475569; border: 1px solid #cbd5e1; }
    .stl-clear:hover { background: #f1f5f9; }
    /* Applied-filter chips */
    .stl-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
    .stl-chip { background: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe; border-radius: 999px; padding: 3px 10px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
    .stl-chip:hover { background: #e0e7ff; color: #312e81; text-decoration: none; }
    .stl-chip .x { font-weight: 700; opacity: .7; }

    /* === Tabs (Active / Done) === */
    .stl-tabs { display: flex; gap: 6px; margin: 0 0 14px; border-bottom: 2px solid #e5e7eb; }
    .stl-tab {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 9px 18px; font-size: 13px; font-weight: 700; color: #64748b;
        text-decoration: none; border-radius: 10px 10px 0 0; border: 1px solid transparent; border-bottom: none;
        margin-bottom: -2px; transition: all .15s;
    }
    .stl-tab:hover { color: #2563eb; background: #f1f5f9; text-decoration: none; }
    .stl-tab.active { color: #1d4ed8; background: #fff; border-color: #e5e7eb; box-shadow: 0 -2px 8px rgba(15,23,42,.05); }
    .stl-tab .stl-tab-count {
        background: #e2e8f0; color: #334155; font-size: 11px; font-weight: 700;
        border-radius: 999px; padding: 1px 9px; min-width: 22px; text-align: center;
    }
    .stl-tab.active .stl-tab-count { background: #2563eb; color: #fff; }
    .stl-done, .stl-restore { border-width: 1px; }

    /* === Priority select (connected na sa Manager Order List Prio 1..15) === */
    .prio-select option:disabled { color: #b0b7c0; background: #f1f3f5; font-weight: 400; }
    .prio-select option[data-taken="1"]:not(:disabled) { color: #856404; background: #fff3cd; font-weight: 600; }
    .prio-confirm-overlay { display: none; position: fixed; inset: 0; z-index: 12000; background: rgba(15,23,42,0.55); backdrop-filter: blur(4px); align-items: center; justify-content: center; }
    .prio-confirm-overlay.show { display: flex; animation: prioFade .18s ease; }
    @keyframes prioFade { from { opacity: 0; } to { opacity: 1; } }
    .prio-confirm-card { width: 92%; max-width: 430px; background: #fff; border-radius: 16px; box-shadow: 0 24px 64px rgba(0,0,0,0.25); overflow: hidden; animation: prioPop .22s cubic-bezier(.2,.9,.3,1.2); }
    @keyframes prioPop { from { transform: scale(.92) translateY(14px); opacity: 0; } to { transform: none; opacity: 1; } }
    .prio-confirm-head { padding: 24px 24px 0; text-align: center; }
    .prio-confirm-icon { width: 58px; height: 58px; margin: 0 auto 10px; border-radius: 50%; background: linear-gradient(135deg, #fff3cd, #ffe1a1); border: 2px solid #ffd76d; display: flex; align-items: center; justify-content: center; font-size: 28px; }
    .prio-confirm-title { font-size: 18px; font-weight: 800; color: #1f2937; }
    .prio-confirm-sub { font-size: 13px; color: #6b7280; margin-top: 4px; word-break: break-word; }
    .prio-confirm-body { padding: 14px 24px 6px; }
    .prio-confirm-warn { background: #fff7e6; border: 1px solid #ffd76d; border-radius: 10px; padding: 12px 14px; font-size: 13px; color: #7a5b12; line-height: 1.6; }
    .prio-confirm-warn b { color: #b45309; }
    .prio-confirm-foot { display: flex; gap: 10px; padding: 16px 24px 22px; }
    .prio-confirm-btn { flex: 1; padding: 10px 0; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; border: none; transition: all .15s; }
    .prio-confirm-cancel { background: #f3f4f6; color: #4b5563; }
    .prio-confirm-cancel:hover { background: #e5e7eb; }
    .prio-confirm-force { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; box-shadow: 0 4px 14px rgba(217,119,6,.35); }
    .prio-confirm-force:hover { filter: brightness(1.05); }
    .prio-confirm-holder { display: inline-block; background: #fff3cd; color: #856404; font-weight: 700; padding: 1px 8px; border-radius: 6px; font-size: 12.5px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="stl-header">
        <div>
            <h4>🕒 Set Time List</h4>
            <div class="stl-sub">
                {{ $sales->count() }} order(s) ang may set na needed time at reason — i-drag ang ☰ para i-arrange depende sa bigat ng dahilan
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('sales.prototype.list') }}" class="btn btn-sm btn-light" style="border-radius:9px;font-weight:600;">← Manager List</a>
        </div>
    </div>

    @php
        $sort = request('sort', 'arrangement');
        $hasFilters = request()->hasAny(['search','agent','department','status','prio','date_from','date_to','sort']);
    @endphp
    <form method="GET" action="{{ route('sales.prototype.set-time-list') }}" id="stlFilterForm" class="stl-filter-card">
        <div class="stl-filter-grid">
            <div class="stl-field" style="grid-column: span 2;">
                <label><i class="fas fa-magnifying-glass"></i> Search</label>
                <div class="stl-search-wrap">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" name="search" class="form-control" placeholder="Customer name o Sales #" value="{{ request('search') }}">
                </div>
            </div>

            <div class="stl-field">
                <label><i class="fas fa-user"></i> Agent</label>
                <select name="agent" class="form-select" onchange="this.form.submit()">
                    <option value="">All Agents</option>
                    @foreach($agents as $a)
                        <option value="{{ $a->sales_agent_id }}" {{ (string) request('agent') === (string) $a->sales_agent_id ? 'selected' : '' }}>{{ $a->sales_agent_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="stl-field">
                <label><i class="fas fa-building"></i> Department</label>
                <select name="department" class="form-select" onchange="this.form.submit()">
                    <option value="">All Departments</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->department_id }}" {{ (string) request('department') === (string) $d->department_id ? 'selected' : '' }}>{{ $d->department_name ?: ('Dept ' . $d->department_id) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="stl-field">
                <label><i class="fas fa-tags"></i> Production Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    @foreach(['HOLD','FOR SAMPLE','FOR FORMAT','PRESSING','QA','DISPATCH','UNPAID','DONE'] as $stg)
                        <option value="{{ $stg }}" {{ strtoupper((string) request('status')) === $stg ? 'selected' : '' }}>{{ $stg }}</option>
                    @endforeach
                </select>
            </div>

            <div class="stl-field">
                <label><i class="fas fa-star"></i> Prio</label>
                <select name="prio" class="form-select" onchange="this.form.submit()">
                    <option value="">All Prio</option>
                    <option value="1" {{ request('prio') === '1' ? 'selected' : '' }}>⭐ May Prio</option>
                    <option value="none" {{ request('prio') === 'none' ? 'selected' : '' }}>— Wala pang Prio</option>
                </select>
            </div>

            <div class="stl-field">
                <label><i class="fas fa-calendar-day"></i> Needed — From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" onchange="this.form.submit()">
            </div>

            <div class="stl-field">
                <label><i class="fas fa-calendar-check"></i> Needed — To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" onchange="this.form.submit()">
            </div>

            <div class="stl-field">
                <label><i class="fas fa-arrow-down-wide-short"></i> Sort By</label>
                <select name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="arrangement" {{ $sort === 'arrangement' ? 'selected' : '' }}>Arrangement (drag)</option>
                    <option value="needed_asc" {{ $sort === 'needed_asc' ? 'selected' : '' }}>Needed date ↑ (pinakauna)</option>
                    <option value="needed_desc" {{ $sort === 'needed_desc' ? 'selected' : '' }}>Needed date ↓ (pinakahuli)</option>
                    <option value="prio" {{ $sort === 'prio' ? 'selected' : '' }}>Prio</option>
                    <option value="sales_number" {{ $sort === 'sales_number' ? 'selected' : '' }}>Sales #</option>
                    <option value="customer" {{ $sort === 'customer' ? 'selected' : '' }}>Customer</option>
                    <option value="agent" {{ $sort === 'agent' ? 'selected' : '' }}>Agent</option>
                </select>
            </div>

            <div class="stl-filter-actions">
                <button type="submit" class="btn stl-apply"><i class="fas fa-filter"></i> Apply Filters</button>
                @if($hasFilters)
                    <a href="{{ route('sales.prototype.set-time-list') }}" class="btn stl-clear" title="Clear all filters"><i class="fas fa-rotate-left"></i> Clear</a>
                @endif
            </div>
        </div>

        @if($hasFilters)
            <div class="stl-chips">
                @if(request('search'))<a class="stl-chip" href="{{ request()->fullUrlWithQuery(['search'=>null]) }}">🔍 “{{ request('search') }}” <span class="x">✕</span></a>@endif
                @if(request('agent'))<a class="stl-chip" href="{{ request()->fullUrlWithQuery(['agent'=>null]) }}">👤 {{ optional($agents->firstWhere('sales_agent_id', (int) request('agent')))->sales_agent_name ?? 'Agent' }} <span class="x">✕</span></a>@endif
                @if(request('department'))<a class="stl-chip" href="{{ request()->fullUrlWithQuery(['department'=>null]) }}">🏢 {{ optional($departments->firstWhere('department_id', (int) request('department')))->department_name ?? 'Dept' }} <span class="x">✕</span></a>@endif
                @if(request('status'))<a class="stl-chip" href="{{ request()->fullUrlWithQuery(['status'=>null]) }}">🏷️ {{ request('status') }} <span class="x">✕</span></a>@endif
                @if(request('prio') === '1')<a class="stl-chip" href="{{ request()->fullUrlWithQuery(['prio'=>null]) }}">⭐ May Prio <span class="x">✕</span></a>@endif
                @if(request('prio') === 'none')<a class="stl-chip" href="{{ request()->fullUrlWithQuery(['prio'=>null]) }}">— Wala pang Prio <span class="x">✕</span></a>@endif
                @if(request('date_from'))<a class="stl-chip" href="{{ request()->fullUrlWithQuery(['date_from'=>null]) }}">📅 from {{ request('date_from') }} <span class="x">✕</span></a>@endif
                @if(request('date_to'))<a class="stl-chip" href="{{ request()->fullUrlWithQuery(['date_to'=>null]) }}">📅 to {{ request('date_to') }} <span class="x">✕</span></a>@endif
                @if(request('sort') && request('sort') !== 'arrangement')<a class="stl-chip" href="{{ request()->fullUrlWithQuery(['sort'=>null]) }}">↕️ Sort: {{ request('sort') }} <span class="x">✕</span></a>@endif
            </div>
        @endif
    </form>

    @php $baseQs = request()->except('tab'); @endphp
    <div class="stl-tabs">
        <a href="{{ request()->fullUrlWithQuery(['tab' => null]) }}" class="stl-tab {{ ($tab ?? 'active') === 'active' ? 'active' : '' }}">
            <i class="fas fa-list-check"></i> Active <span class="stl-tab-count">{{ $activeCount ?? 0 }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'done']) }}" class="stl-tab {{ ($tab ?? 'active') === 'done' ? 'active' : '' }}">
            <i class="fas fa-circle-check"></i> Done <span class="stl-tab-count">{{ $doneCount ?? 0 }}</span>
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div style="padding:8px 14px;" class="stl-hint">
                @if(($tab ?? 'active') === 'done')
                    ✅ Mga na-Done sa Set Time List — nakatago lang dito para mabawasan ang active list. I-click ang <strong>Restore</strong> kapag nagkamali. <em>Hindi ito nakakaapekto sa Manager Order List.</em>
                @else
                    💡 Kapag inayos mo ang listahan, awtomatikong nase-save ang arrangement — babalik ito sa susunod na bisita. I-click ang <strong>Done</strong> kapag tapos na para mawala sa active list.
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-hover stl-table mb-0" id="setTimeTable">
                    <thead>
                        <tr>
                            <th style="width:52px;">#</th>
                            <th style="width:84px;">Mock Up</th>
                            <th style="width:120px;">⭐ Prio</th>
                            <th>Sales #</th>
                            <th>Customer</th>
                            <th>Agent</th>
                            <th>Needed Date &amp; Time</th>
                            <th>Reason / Note</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th style="width:110px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="setTimeBody">
                        @forelse($sales as $sale)
                            @php
                                // Mock up thumbnail (light): main image (is_main) o first; url string o ['url'].
                                $mkImgs = is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? []);
                                $mkMain = null;
                                foreach ((array) $mkImgs as $m) {
                                    if (is_array($m) && !empty($m['is_main'])) { $mkMain = $m; break; }
                                }
                                if (!$mkMain && !empty($mkImgs)) $mkMain = $mkImgs[0];
                                $mkUrl = is_string($mkMain) ? $mkMain : ($mkMain['url'] ?? '');
                            @endphp
                            <tr class="stl-row" draggable="true" data-sale-id="{{ $sale->id }}">
                                <td>
                                    <span class="d-inline-flex align-items-center gap-2">
                                        <i class="fas fa-grip-vertical stl-drag" title="Drag to arrange"></i>
                                        <span class="stl-seq">{{ $loop->iteration }}</span>
                                    </span>
                                </td>
                                <td style="padding:4px;">
                                    @if($mkUrl)
                                        <img src="{{ $mkUrl }}" alt="mockup" loading="lazy" decoding="async" style="width:64px;height:64px;object-fit:contain;border-radius:6px;background:#f8f9fa;border:1px solid #e5e7eb;" title="Mock up" onerror="this.style.display='none'">
                                    @else
                                        <span class="text-muted" style="font-size:11px;">—</span>
                                    @endif
                                </td>
                                <td onclick="event.stopPropagation();" style="white-space:nowrap;">
                                    @php $prio = $sale->priority; @endphp
                                    <select class="form-select form-select-sm prio-select" data-sale-id="{{ $sale->id }}" data-current="{{ $prio ?? '' }}" onclick="event.stopPropagation()" style="font-size:11px;min-width:112px;padding:1px 4px;{{ $prio ? 'background:#fff3cd;color:#856404;font-weight:600;' : '' }}" title="Priority tag (kapareho ng Manager Order List) — Prio 1..15; nagamit na sa ibang order ang may (Taken)">
                                        <option value="" {{ !$prio ? 'selected' : '' }}>Prio —</option>
                                        @for($i = 1; $i <= ($priorityMax ?? 15); $i++)
                                            @php
                                                $prioTaken = isset($usedPriorities[$i]) && $usedPriorities[$i] !== $sale->sales_number;
                                            @endphp
                                            <option value="{{ $i }}" {{ (int) $prio === $i ? 'selected' : '' }} {{ ($prioTaken && !($canForcePriority ?? false)) ? 'disabled' : '' }} {{ $prioTaken ? 'data-taken="1" data-holder="' . e($usedPriorities[$i]) . '"' : '' }}>{{ $prioTaken ? 'Prio ' . $i . ' (Taken' . (($canForcePriority ?? false) ? ' — click para i-force' : '') . ')' : 'Prio ' . $i }}</option>
                                        @endfor
                                    </select>
                                    <div class="stl-prio-stamp" style="font-size:10px;color:#6c757d;margin-top:2px;{{ empty($sale->priority_set_at) ? 'display:none;' : '' }}">{{ $sale->priority_set_at ? '🕒 ' . \Carbon\Carbon::parse($sale->priority_set_at)->format('M j, g:i A') : '' }}</div>
                                </td>
                                <td>
                                    <strong class="stl-number">{{ $sale->sales_number ?: '#' . $sale->id }}</strong>
                                    <div style="font-size:11px;color:#6c757d;">{{ $sale->created_at ? \Carbon\Carbon::parse($sale->created_at)->format('M d, Y') : '' }}</div>
                                </td>
                                <td>{{ $sale->customer_name ?: '—' }}</td>
                                <td style="font-size:12px;color:#6c757d;">@if($sale->sales_agent_name)<x-user-chip :user="$sale->salesAgent" :name="$sale->sales_agent_name" :size="18" />@else —@endif</td>
                                <td>
                                    @if(!empty($sale->needed_by))
                                        <span class="badge bg-success d-inline-flex flex-column align-items-start" style="line-height:1.3;">
                                            <span style="font-size:11px;"><i class="fas fa-calendar-day"></i> {{ \Carbon\Carbon::parse($sale->needed_by)->format('M d, Y') }}</span>
                                            <span style="font-size:11px;"><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($sale->needed_by)->format('g:i A') }}</span>
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="stl-note">
                                    @if(!empty($sale->time_note))
                                        {{ $sale->time_note }}
                                    @else
                                        <span class="text-muted">— walang note —</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-secondary stl-badge">{{ $sale->department_name ?: '—' }}</span></td>
                                <td>
                                    @php $curStage = $sale->production_stage ?: ($statusToStage[$sale->kanban_status ?? 'new'] ?? 'HOLD'); @endphp
                                    <span class="badge bg-light text-dark stl-badge" title="Production status — kapareho ng Manager List">{{ $curStage }}</span>
                                    @if(($tab ?? 'active') === 'done' && !empty($dispatchAt[$sale->id] ?? null))
                                        <div style="font-size:10px;color:#6c757d;margin-top:2px;white-space:nowrap;" title="Oras kung kailan na-tag na DISPATCH">🕒 DISPATCH: {{ \Carbon\Carbon::parse($dispatchAt[$sale->id])->format('M j, g:i A') }}</div>
                                    @endif
                                </td>
                                <td style="white-space:nowrap;">
                                    <a href="{{ route('sales.prototype.show', $sale->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:11px;padding:2px 8px;">View</a>
                                    @if(($tab ?? 'active') === 'done')
                                        @if(!in_array($curStage, ['DISPATCH', 'DONE'], true) && !empty($sale->set_time_done_at))
                                            <button type="button" class="btn btn-sm btn-outline-success stl-restore" data-sale-id="{{ $sale->id }}" style="font-size:11px;padding:2px 8px;" title="I-restore pabalik sa active list">↩ Restore</button>
                                        @else
                                            <span class="text-muted" style="font-size:10px;" title="Awtomatikong nailipat sa Done dahil DISPATCH na">auto ({{ $curStage }})</span>
                                        @endif
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-success stl-done" data-sale-id="{{ $sale->id }}" style="font-size:11px;padding:2px 8px;" title="Tapos na — itago sa Done (Set Time List lang)">✔ Done</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    @if(($tab ?? 'active') === 'done')
                                        Wala pang naka-Done sa Set Time List.
                                    @else
                                        Wala pang nag-set ng time. Kapag nag-set ng needed time at reason ang mga agent, lalabas dito.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var tbody = document.getElementById('setTimeBody');
    if (!tbody) return;
    var dragEl = null;

    function renumber() {
        tbody.querySelectorAll('.stl-row').forEach(function (row, i) {
            var seq = row.querySelector('.stl-seq');
            if (seq) seq.textContent = i + 1;
        });
    }

    function toast(msg, ok) {
        var t = document.createElement('div');
        t.className = 'stl-saved';
        if (!ok) t.style.background = '#dc3545';
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function () { t.remove(); }, 2500);
    }

    function saveOrder() {
        var order = Array.prototype.map.call(tbody.querySelectorAll('.stl-row'), function (r) {
            return r.getAttribute('data-sale-id');
        });
        fetch('{{ route('sales.prototype.set-time-list.reorder') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}'
            },
            body: JSON.stringify({ order: order })
        })
        .then(function (r) { return r.json().catch(function () { return {}; }); })
        .then(function (d) {
            if (d && d.success) { toast('✅ Arrangement saved'); }
            else { toast('⚠️ ' + ((d && d.message) || 'Save failed'), false); }
        })
        .catch(function () { toast('❌ Network error — hindi na-save', false); });
    }

    tbody.addEventListener('dragstart', function (e) {
        var row = e.target.closest('.stl-row');
        if (!row) return;
        // Huwag magsimula ng drag kapag ang kinlick ay dropdown/link/button (para hindi makagambala).
        if (e.target.closest('select, a, button, .prio-stamp')) {
            e.preventDefault();
            return;
        }
        dragEl = row;
        row.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        try { e.dataTransfer.setData('text/plain', row.getAttribute('data-sale-id')); } catch (err) {}
    });

    tbody.addEventListener('dragover', function (e) {
        if (!dragEl) return;
        e.preventDefault();
        var row = e.target.closest('.stl-row');
        if (!row || row === dragEl) return;
        var rect = row.getBoundingClientRect();
        var after = (e.clientY - rect.top) > (rect.height / 2);
        if (after) { row.parentNode.insertBefore(dragEl, row.nextSibling); }
        else { row.parentNode.insertBefore(dragEl, row); }
    });

    tbody.addEventListener('dragend', function () {
        if (!dragEl) return;
        dragEl.classList.remove('dragging');
        dragEl = null;
        renumber();
        saveOrder();
    });

    // === PRIORITY DROPDOWN — tag Prio 1-15 (kapareho ng Manager Order List) ===
    // Nakakabit na ito sa totoong `priority` slots (hindi na sa set_time_prio reminder).
    var priorityMax = @json($priorityMax ?? 15);
    var canForcePriority = @json($canForcePriority ?? false);

    // Custom styled confirm dialog (kapareho ng Manager Order List)
    function prioForceDialog(prio, holder, onYes) {
        var ov = document.getElementById('prioForceOverlay');
        if (ov) ov.remove();
        ov = document.createElement('div');
        ov.id = 'prioForceOverlay';
        ov.className = 'prio-confirm-overlay';
        ov.innerHTML =
            '<div class="prio-confirm-card">' +
                '<div class="prio-confirm-head">' +
                    '<div class="prio-confirm-icon">⚡</div>' +
                    '<div class="prio-confirm-title">Force insert Prio ' + prio + '?</div>' +
                    '<div class="prio-confirm-sub">Taken na ang slot na ito &mdash; hawak ni <span class="prio-confirm-holder">' + (holder || 'isa pang order') + '</span></div>' +
                '</div>' +
                '<div class="prio-confirm-body">' +
                    '<div class="prio-confirm-warn">⚠️ Kapag itinuloy: uurong ng <b>+1</b> ang lahat ng may Prio &ge; <b>' + prio + '</b>, at ang kasalukuyang <b>Prio ' + priorityMax + '</b> ay mawawalan ng tag. Ang order na ito ang kukuha ng Prio <b>' + prio + '</b>.</div>' +
                '</div>' +
                '<div class="prio-confirm-foot">' +
                    '<button type="button" class="prio-confirm-btn prio-confirm-cancel">Cancel</button>' +
                    '<button type="button" class="prio-confirm-btn prio-confirm-force">⚡ Force Insert</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(ov);
        requestAnimationFrame(function() { ov.classList.add('show'); });
        var done = false;
        function close(result) {
            if (done) return;
            done = true;
            ov.classList.remove('show');
            setTimeout(function() { ov.remove(); if (result && onYes) onYes(); }, 150);
        }
        ov.addEventListener('click', function(e) { if (e.target === ov) close(false); });
        ov.querySelector('.prio-confirm-cancel').addEventListener('click', function() { close(false); });
        ov.querySelector('.prio-confirm-force').addEventListener('click', function() { close(true); });
    }

    function sendPriority(saleId, sel, oldPrio, prio, force) {
        var csrf = document.querySelector('meta[name="csrf-token"]');
        sel.disabled = true;
        fetch('/sales/prototype/' + saleId + '/priority', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf ? csrf.content : ''
            },
            body: JSON.stringify({ priority: prio, force: force })
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
                toast(res.data.message || '✅ Priority saved');
                // AUTO-PROMOTE / force shift: i-refresh agad ang lahat ng dropdown (no reload)
                applyPriorityMap(res.data.priority_map);
            } else if (!force && res.data && res.data.can_force && canForcePriority) {
                sel.value = oldPrio;
                var holder = res.data.holder || 'isa pang order';
                prioForceDialog(prio, holder, function() {
                    sendPriority(saleId, sel, oldPrio, prio, true);
                });
            } else {
                sel.value = oldPrio;
                toast('⚠️ ' + (res.data.message || 'Failed to save priority.'), false);
            }
        })
        .catch(function() {
            sel.disabled = false;
            sel.value = oldPrio;
            toast('❌ Network error. Please try again.', false);
        });
    }

    document.addEventListener('change', function(e) {
        var sel = e.target.closest('.prio-select');
        if (!sel) return;
        var saleId = sel.getAttribute('data-sale-id');
        var oldPrio = sel.getAttribute('data-current');
        var prio = sel.value;
        var opt = sel.options[sel.selectedIndex];
        var isTaken = prio && opt && opt.getAttribute('data-taken') === '1';
        if (isTaken) {
            if (!canForcePriority) {
                sel.value = oldPrio;
                toast('⚠️ Taken na ang Prio ' + prio + ' — Manager/CEO/COO lang ang pwedeng mag-force insert.', false);
                return;
            }
            var holder = (opt.getAttribute('data-holder') || 'isa pang order');
            prioForceDialog(prio, holder, function() {
                sendPriority(saleId, sel, oldPrio, prio, true);
            });
            return;
        }
        sendPriority(saleId, sel, oldPrio, prio, false);
    });

    // === INSTANT PRIO UI UPDATE (auto-clear sa DISPATCH + auto-promote) — no reload ===
    // Ang priority_map ay [sale_id => priority|null] na galing sa server response.
    function applyPriorityMap(priorityMap) {
        if (!priorityMap) return;
        var used = {};
        Object.keys(priorityMap).forEach(function(saleId) {
            var prio = priorityMap[saleId];
            if (prio) used[prio] = saleId;
            var sel = document.querySelector('.prio-select[data-sale-id="' + saleId + '"]');
            if (!sel) return;
            sel.value = prio ? String(prio) : '';
            sel.setAttribute('data-current', prio ? String(prio) : '');
            if (prio) {
                sel.style.background = '#fff3cd';
                sel.style.color = '#856404';
                sel.style.fontWeight = '600';
            } else {
                sel.style.background = '';
                sel.style.color = '';
                sel.style.fontWeight = '';
            }
        });
        // Rebuild "Taken" options batay sa bagong map
        document.querySelectorAll('.prio-select').forEach(function(s) {
            var sid = s.getAttribute('data-sale-id');
            Array.prototype.forEach.call(s.options, function(opt) {
                if (!opt.value) return;
                var n = parseInt(opt.value, 10);
                var taken = used[n] && used[n] !== sid;
                opt.disabled = taken && !canForcePriority;
                if (taken) {
                    opt.setAttribute('data-taken', '1');
                    var holderSel = document.querySelector('.prio-select[data-sale-id="' + used[n] + '"]');
                    var holderSn = '';
                    if (holderSel) {
                        var hrow = holderSel.closest('tr');
                        if (hrow) {
                            var link = hrow.querySelector('a[href*="/sales/prototype/"]');
                            if (link) holderSn = link.textContent.trim();
                        }
                    }
                    opt.setAttribute('data-holder', holderSn);
                    opt.textContent = canForcePriority ? 'Prio ' + n + ' (Taken — click para i-force)' : 'Prio ' + n + ' (Taken)';
                } else {
                    opt.removeAttribute('data-taken');
                    opt.removeAttribute('data-holder');
                    opt.textContent = 'Prio ' + n;
                }
            });
        });
    }

    // === Done / Restore (Set Time List lang — hindi nakakaapekto sa Manager Order List) ===
    function stlSetDone(btn, saleId, done) {
        btn.disabled = true;
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        fetch('/sales/prototype/' + saleId + '/set-time-done', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}'
            },
            body: JSON.stringify({ done: done ? 1 : 0 })
        })
        .then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, data: d }; }); })
        .then(function (res) {
            if (res.ok && res.data.success) {
                var row = btn.closest('tr');
                if (row) {
                    row.style.transition = 'opacity .25s';
                    row.style.opacity = '0';
                    setTimeout(function () { location.reload(); }, 300);
                } else {
                    location.reload();
                }
            } else {
                btn.disabled = false;
                btn.innerHTML = orig;
                toast('⚠️ ' + (res.data.message || 'Failed.'), false);
            }
        })
        .catch(function () { btn.disabled = false; btn.innerHTML = orig; toast('❌ Network error. Please try again.', false); });
    }

    document.addEventListener('click', function (e) {
        var doneBtn = e.target.closest('.stl-done');
        if (doneBtn) {
            e.preventDefault(); e.stopPropagation();
            var sid = doneBtn.getAttribute('data-sale-id');
            if (confirm('I-Done ang order na ito?\n\nMawawala ito sa active list at mapupunta sa Done tab (Set Time List lang).\nHindi maaapektuhan ang Manager Order List.\n\nPwede pang i-restore kapag nagkamali.')) {
                stlSetDone(doneBtn, sid, true);
            }
            return;
        }
        var restoreBtn = e.target.closest('.stl-restore');
        if (restoreBtn) {
            e.preventDefault(); e.stopPropagation();
            stlSetDone(restoreBtn, restoreBtn.getAttribute('data-sale-id'), false);
            return;
        }
    });

    // Touch devices: long-press fallback is not supported natively; leave a hint in UI.
})();
</script>
@endpush
