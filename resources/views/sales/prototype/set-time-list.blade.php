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
                <label><i class="fas fa-flag"></i> Prio Reminder</label>
                <select name="prio" class="form-select" onchange="this.form.submit()">
                    <option value="">All Prio Reminder</option>
                    <option value="1" {{ request('prio') === '1' ? 'selected' : '' }}>✅ Pinaprio pa</option>
                    <option value="0" {{ request('prio') === '0' ? 'selected' : '' }}>❌ Hindi na</option>
                    <option value="none" {{ request('prio') === 'none' ? 'selected' : '' }}>— Wala pang sagot</option>
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
                    <option value="prio" {{ $sort === 'prio' ? 'selected' : '' }}>Prio Reminder</option>
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
                @if(request('prio') === '1')<a class="stl-chip" href="{{ request()->fullUrlWithQuery(['prio'=>null]) }}">✅ Pinaprio pa <span class="x">✕</span></a>@endif
                @if(request('prio') === '0')<a class="stl-chip" href="{{ request()->fullUrlWithQuery(['prio'=>null]) }}">❌ Hindi na <span class="x">✕</span></a>@endif
                @if(request('prio') === 'none')<a class="stl-chip" href="{{ request()->fullUrlWithQuery(['prio'=>null]) }}">— Wala pang sagot <span class="x">✕</span></a>@endif
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
                            <th style="width:120px;">Prio Reminder</th>
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
                                    @php $sp = $sale->set_time_prio; @endphp
                                    <select class="form-select form-select-sm stl-prio-select" data-sale-id="{{ $sale->id }}" data-current="{{ $sp === 1 ? '1' : ($sp === 0 ? '0' : '') }}" style="font-size:11px;min-width:112px;padding:1px 4px;{{ $sp === 1 ? 'background:#d1e7dd;color:#0f5132;font-weight:600;' : ($sp === 0 ? 'background:#f8d7da;color:#842029;' : '') }}" title="Reminder lang (SEPARATE sa Manager List): pinaprio pa ba ni Manager ang order na ito?">
                                        <option value="" {{ $sp === null || $sp === '' ? 'selected' : '' }}>— Reminder —</option>
                                        <option value="1" {{ $sp === 1 ? 'selected' : '' }}>✅ Pinaprio pa</option>
                                        <option value="0" {{ $sp === 0 ? 'selected' : '' }}>❌ Hindi na</option>
                                    </select>
                                    <div class="stl-prio-stamp" style="font-size:10px;color:#6c757d;margin-top:2px;{{ empty($sale->set_time_prio_at) ? 'display:none;' : '' }}">{{ $sale->set_time_prio_at ? '🕒 ' . \Carbon\Carbon::parse($sale->set_time_prio_at)->format('M j, g:i A') : '' }}</div>
                                </td>
                                <td>
                                    <strong class="stl-number">{{ $sale->sales_number ?: '#' . $sale->id }}</strong>
                                    <div style="font-size:11px;color:#6c757d;">{{ $sale->created_at ? \Carbon\Carbon::parse($sale->created_at)->format('M d, Y') : '' }}</div>
                                </td>
                                <td>{{ $sale->customer_name ?: '—' }}</td>
                                <td style="font-size:12px;color:#6c757d;">{{ $sale->sales_agent_name ?: '—' }}</td>
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
                                </td>
                                <td style="white-space:nowrap;">
                                    <a href="{{ route('sales.prototype.show', $sale->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:11px;padding:2px 8px;">View</a>
                                    @if(($tab ?? 'active') === 'done')
                                        <button type="button" class="btn btn-sm btn-outline-success stl-restore" data-sale-id="{{ $sale->id }}" style="font-size:11px;padding:2px 8px;" title="I-restore pabalik sa active list">↩ Restore</button>
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

    // === Prio Reminder (SEPARATE sa Manager List Prio 1..15) ===
    // Simpleng reminder lang: "pinaprio pa ba ni Manager ang order na ito?"
    // Hindi nito ginagamit ang unique Prio slots — sariling flag sa set_time_prio.
    function stlPrioStampText(iso) {
        if (!iso) return '';
        var d = new Date(iso.replace(' ', 'T'));
        if (isNaN(d.getTime())) return '🕒 ' + iso;
        var mo = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var h = d.getHours(); var ap = h >= 12 ? 'PM' : 'AM'; h = h % 12; if (h === 0) h = 12;
        var mm = ('0' + d.getMinutes()).slice(-2);
        return '🕒 ' + mo[d.getMonth()] + ' ' + d.getDate() + ', ' + h + ':' + mm + ' ' + ap;
    }

    function stlPrioStyle(sel, val) {
        sel.style.background = ''; sel.style.color = ''; sel.style.fontWeight = '';
        if (val === '1') { sel.style.background = '#d1e7dd'; sel.style.color = '#0f5132'; sel.style.fontWeight = '600'; }
        else if (val === '0') { sel.style.background = '#f8d7da'; sel.style.color = '#842029'; }
    }

    document.addEventListener('change', function (e) {
        var sel = e.target.closest('.stl-prio-select');
        if (!sel) return;
        var saleId = sel.getAttribute('data-sale-id');
        var oldVal = sel.getAttribute('data-current') || '';
        var val = sel.value;
        sel.disabled = true;
        fetch('/sales/prototype/' + saleId + '/set-time-prio', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}'
            },
            body: JSON.stringify({ set_time_prio: val === '' ? null : parseInt(val, 10) })
        })
        .then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, data: d }; }); })
        .then(function (res) {
            sel.disabled = false;
            if (res.ok && res.data.success) {
                sel.setAttribute('data-current', val);
                stlPrioStyle(sel, val);
                var row = sel.closest('tr');
                var st = row ? row.querySelector('.stl-prio-stamp') : null;
                if (st) {
                    if (res.data.set_time_prio_at) { st.textContent = stlPrioStampText(res.data.set_time_prio_at); st.style.display = ''; }
                    else { st.textContent = ''; st.style.display = 'none'; }
                }
                toast('✅ ' + (res.data.message || 'Reminder saved'));
            } else {
                sel.value = oldVal;
                toast('⚠️ ' + (res.data.message || 'Failed to save.'), false);
            }
        })
        .catch(function () { sel.disabled = false; sel.value = oldVal; toast('❌ Network error. Please try again.', false); });
    });

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
