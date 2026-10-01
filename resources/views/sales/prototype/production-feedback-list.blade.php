@extends('layouts.app')

@section('title', 'Production Feedback')

@push('styles')
<style>
    .main-content, .content-area { min-width: 0; }

    /* Header — same gradient style as Backjob List, amber/orange theme */
    .pf-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
        background: linear-gradient(135deg, #3b2505 0%, #92400e 55%, #d97706 100%);
        border-radius: 14px;
        padding: 20px 24px;
        box-shadow: 0 4px 16px rgba(217, 119, 6, 0.25);
        position: relative;
        overflow: hidden;
    }
    .pf-header::before {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
        pointer-events: none;
    }
    .pf-header::after {
        content: '📋';
        position: absolute;
        right: 18px;
        bottom: -14px;
        font-size: 72px;
        opacity: 0.12;
        transform: rotate(-10deg);
    }
    .pf-header h2 {
        margin: 0;
        color: #ffffff;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 0.3px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .pf-header .pf-sub {
        color: rgba(255,255,255,0.75);
        font-size: 13px;
        margin-top: 3px;
        max-width: 560px;
    }
    .pf-stat {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 10px;
        padding: 8px 14px;
        text-align: center;
        backdrop-filter: blur(4px);
        min-width: 84px;
    }
    .pf-stat .num { font-size: 1.35rem; font-weight: 800; line-height: 1; }
    .pf-stat .lbl { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; }

    /* Status pills — cleaner look */
    .stat-pill {
        padding: 7px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        transition: all .15s ease;
        border: 1.5px solid transparent;
    }
    .stat-pill:hover { transform: translateY(-1px); box-shadow: 0 3px 10px rgba(0,0,0,0.12); }

    /* Table — same as Backjob List */
    .pipeline-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .pipeline-table th {
        background: #fffbeb;
        padding: 10px 12px;
        text-align: left;
        font-weight: 700;
        color: #78350f;
        border-bottom: 2px solid #fde68a;
        white-space: nowrap;
    }
    .pipeline-table td {
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }
    .pipeline-table tbody tr { cursor: pointer; }
    .pipeline-table tbody tr:hover td { background: #fffbeb; }
    .pipeline-table tbody tr:last-child td { border-bottom: none; }

    .pf-feedback-box {
        background: #fff7ed;
        border-left: 3px solid #f59e0b;
        border-radius: 6px;
        padding: 8px 10px;
        font-size: 12px;
        line-height: 1.4;
        color: #7c2d12;
        font-weight: 500;
    }
    .pf-meta { font-size: 11px; color: #94a3b8; }
    .pf-involved { color: #d97706; }

    /* Filter bar (agent/category) */
    .pf-filter-bar {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 10px 14px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        margin-bottom: 16px;
    }
    .pf-filter-bar select:focus {
        border-color: #d97706;
        box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.12);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    .empty-state i { font-size: 48px; color: #fcd34d; display: block; margin-bottom: 12px; }

    /* ---- Agent stats dashboard ---- */
    .pf-stats-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        margin-bottom: 16px;
        overflow: hidden;
    }
    .pf-stats-card > summary {
        list-style: none;
        cursor: pointer;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        color: #78350f;
        background: #fffbeb;
        border-bottom: 1px solid #fde68a;
    }
    .pf-stats-card > summary::-webkit-details-marker { display: none; }
    .pf-stats-card > summary .chev { margin-left: auto; transition: transform .18s ease; font-size: 12px; color: #b45309; }
    .pf-stats-card[open] > summary .chev { transform: rotate(180deg); }
    .pf-mini {
        flex: 1;
        min-width: 92px;
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 10px;
        padding: 10px 12px;
        text-align: center;
    }
    .pf-mini .n { font-size: 1.4rem; font-weight: 800; line-height: 1; }
    .pf-mini .l { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.7px; color: #64748b; margin-top: 3px; }
    .pf-rank { width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; }
    .pf-rate-track { background: #eef2f7; border-radius: 20px; height: 8px; width: 90px; overflow: hidden; display: inline-block; vertical-align: middle; }
    .pf-rate-fill { height: 100%; border-radius: 20px; background: linear-gradient(90deg,#34d399,#059669); }
    .pf-agent-row:hover td { background: #fffbeb; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="pf-header">
        <div>
            <h2 class="mb-1"><i class="fas fa-clipboard-check me-2"></i>Production Feedback</h2>
            <div class="pf-sub">
                @if($canViewAll ?? $isManager)
                Lahat ng feedback na binigay sa mga sales agents — para ma-track ang production delays dulot ng kulang na impormasyon.
                @else
                Feedback mula sa manager tungkol sa production delays — i-check at i-resolve para magpatuloy ang production.
                @endif
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="pf-stat">
                <div class="num">{{ array_sum($statusCounts) }}</div>
                <div class="lbl">Total</div>
            </div>
            <div class="pf-stat">
                <div class="num" style="color:#7ef0a3;">{{ $statusCounts['resolved'] ?? 0 }}</div>
                <div class="lbl">Resolved</div>
            </div>
            @if(($canViewAll ?? $isManager))
            <a href="{{ route('sales.prototype.production-feedback.dashboard', request()->filled('category') ? ['category' => request('category')] : []) }}" class="btn btn-sm btn-light fw-semibold" style="border-radius:10px;">
                <i class="fas fa-chart-pie me-1"></i> Dashboard
            </a>
            @endif
            <a href="{{ $isManager ? (auth()->user()->isProdManager() ? route('sales.prototype.list') : route('sales.prototype.dashboard')) : (($isArtist ?? false) ? route('dashboard') : (($canViewAll ?? false) ? route('sales.prototype.list') : route('sales.team.dashboard'))) }}" class="btn btn-sm text-white" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Status pills -->
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <a href="{{ route('sales.prototype.production-feedback.list') }}" class="stat-pill text-decoration-none" style="background:{{ !request('status') ? '#1e293b' : '#f1f5f9' }};color:{{ !request('status') ? '#fff' : '#334155' }};">All ({{ array_sum($statusCounts) }})</a>
        @foreach(['open' => '#d97706', 'acknowledged' => '#2563eb', 'resolved' => '#059669'] as $st => $color)
        <a href="{{ route('sales.prototype.production-feedback.list', ['status' => $st]) }}" class="stat-pill text-decoration-none" style="background:{{ request('status') === $st ? $color : '#f1f5f9' }};color:{{ request('status') === $st ? '#fff' : '#334155' }};">{{ ucfirst($st) }} ({{ $statusCounts[$st] ?? 0 }})</a>
        @endforeach
    </div>

    <!-- Filters -->
    @if($canViewAll ?? $isManager)
    <form method="GET" class="pf-filter-bar">
        <div class="row g-2">
            <div class="col-auto">
                <select name="agent_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All agents / artists</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach(\App\Models\ProductionFeedback::CATEGORIES as $val => $label)
                    <option value="{{ $val }}" {{ request('category') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
    @endif

    <!-- Agent stats dashboard (manager / CEO / COO only) -->
    @if(($canViewAll ?? $isManager) && ($agentStats ?? collect())->count())
    <details class="pf-stats-card" open>
        <summary>
            <i class="fas fa-chart-bar"></i> Agent Stats — feedback per sales agent / artist
            <span class="chev"><i class="fas fa-chevron-down"></i></span>
        </summary>
        <div class="p-3">
            <div class="d-flex gap-2 flex-wrap mb-3">
                <div class="pf-mini"><div class="n" style="color:#1e293b;">{{ $agentStatsTotals['agents'] }}</div><div class="l">Agents w/ feedback</div></div>
                <div class="pf-mini"><div class="n" style="color:#1e293b;">{{ $agentStatsTotals['total'] }}</div><div class="l">Total</div></div>
                <div class="pf-mini"><div class="n" style="color:#d97706;">{{ $agentStatsTotals['open'] }}</div><div class="l">Open</div></div>
                <div class="pf-mini"><div class="n" style="color:#2563eb;">{{ $agentStatsTotals['acknowledged'] }}</div><div class="l">Acknowledged</div></div>
                <div class="pf-mini"><div class="n" style="color:#059669;">{{ $agentStatsTotals['resolved'] }}</div><div class="l">Resolved</div></div>
                <div class="pf-mini"><div class="n" style="color:#059669;">{{ $agentStatsTotals['resolve_rate'] }}%</div><div class="l">Overall resolve rate</div></div>
            </div>
            <div style="overflow-x:auto;">
                <table class="pipeline-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Agent / Artist</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Open</th>
                            <th class="text-center">Acknowledged</th>
                            <th class="text-center">Resolved</th>
                            <th>Resolve rate</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agentStats as $i => $stat)
                        @php
                            $medal = $i === 0 ? '#fbbf24' : ($i === 1 ? '#cbd5e1' : ($i === 2 ? '#d97706' : '#f1f5f9'));
                            $medalText = $i < 3 ? '#1e293b' : '#64748b';
                            $isActive = (string) request('agent_id') === (string) $stat->user_id;
                        @endphp
                        <tr class="pf-agent-row" style="cursor:pointer;{{ $isActive ? 'background:#fffbeb;' : '' }}"
                            onclick="window.location.href='{{ route('sales.prototype.production-feedback.list', array_filter(['agent_id' => $stat->user_id, 'category' => request('category'), 'status' => request('status')])) }}'">
                            <td><span class="pf-rank" style="background:{{ $medal }};color:{{ $medalText }};">{{ $i + 1 }}</span></td>
                            <td style="white-space:nowrap;"><strong>{{ $stat->name }}</strong>@if($isActive) <span class="badge bg-warning text-dark ms-1">filtered</span>@endif</td>
                            <td class="text-center"><strong>{{ $stat->total }}</strong></td>
                            <td class="text-center" style="color:#d97706;font-weight:700;">{{ $stat->open }}</td>
                            <td class="text-center" style="color:#2563eb;font-weight:700;">{{ $stat->acknowledged }}</td>
                            <td class="text-center" style="color:#059669;font-weight:700;">{{ $stat->resolved }}</td>
                            <td style="white-space:nowrap;">
                                <span class="pf-rate-track"><span class="pf-rate-fill" style="width:{{ $stat->resolve_rate }}%;"></span></span>
                                <span style="font-size:12px;font-weight:700;color:#059669;margin-left:6px;">{{ $stat->resolve_rate }}%</span>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('sales.prototype.production-feedback.list', array_filter(['agent_id' => $stat->user_id, 'category' => request('category'), 'status' => request('status')])) }}"
                                   class="btn btn-sm btn-outline-warning" onclick="event.stopPropagation();" title="I-filter ang listahan sa agent na ito">
                                    <i class="fas fa-filter"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pf-meta mt-2">Naka-sort sa pinaka-maraming feedback. I-click ang row para i-filter ang listahan sa agent na 'yon.</div>
        </div>
    </details>
    @endif

    <!-- Table -->
    <div style="overflow-x:auto;">
        <table class="pipeline-table">
            <thead>
                <tr>
                    <th>Sales #</th>
                    <th>Mock Up</th>
                    <th>Category</th>
                    <th>Feedback</th>
                    <th>Status</th>
                    @if($canViewAll ?? $isManager)
                    <th>Recipient</th>
                    @endif
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feedbacks as $fb)
                    @php
                        $sale = $fb->sale;
                        // Mock up thumbnail (same logic as manager order list)
                        $mockups = $sale ? (is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? [])) : [];
                        $mainMockup = null;
                        foreach ((array)$mockups as $m) {
                            if (is_array($m) && !empty($m['is_main'])) { $mainMockup = $m; break; }
                        }
                        if (!$mainMockup && !empty($mockups)) $mainMockup = $mockups[0];
                        $firstMockupUrl = is_string($mainMockup) ? $mainMockup : ($mainMockup['url'] ?? '');
                    @endphp
                    <tr onclick="window.location.href='{{ route('sales.prototype.show', $fb->sale_id) }}'">
                        <td style="max-width:130px;white-space:nowrap;">
                            <strong style="font-size:12px;">{{ $sale->sales_number ?? ('Sale #' . $fb->sale_id) }}</strong>
                            @if($sale)
                            <div style="font-size:10px;color:#6c757d;">{{ $sale->customer_name ?: '—' }}</div>
                            @endif
                        </td>
                        <td>
                            @if($firstMockupUrl)
                                <img src="{{ $firstMockupUrl }}" alt="mockup" style="width:72px;height:auto;max-height:80px;object-fit:contain;border-radius:6px;cursor:pointer;" title="Click to open order" onerror="this.style.display='none'">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ \App\Models\ProductionFeedback::CATEGORIES[$fb->category] ?? $fb->category }}</span>
                        </td>
                        <td style="max-width:280px;">
                            <div style="font-size:12px;line-height:1.35;overflow:hidden;text-overflow:ellipsis;" title="{{ $fb->message }}">{{ \Illuminate\Support\Str::limit($fb->message, 80) }}</div>
                            <div style="font-size:10px;color:#94a3b8;margin-top:2px;">from {{ $fb->fromUser?->display_label ?? 'Manager' }}
                            @php
                                // Dynamic "Involved" — depends on who's viewing:
                                // Agent views → the tagged Artist shows as Involved.
                                // Artist views → the Agent shows as Involved.
                                $involvedLabel = null;
                                if ($fb->involved_user_id) {
                                    if (auth()->id() === (int) $fb->involved_user_id) {
                                        $involvedLabel = $fb->toUser?->display_label; // artist sees the agent
                                    } else {
                                        $involvedLabel = $fb->involvedUser?->display_label; // agent/manager sees the artist
                                    }
                                }
                            @endphp
                            @if($involvedLabel)
                                <span style="color:#d97706;"> • Involved: {{ $involvedLabel }}</span>
                            @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background:{{ $fb->status === 'resolved' ? '#059669' : ($fb->status === 'acknowledged' ? '#2563eb' : '#d97706') }};color:#fff;">{{ ucfirst($fb->status) }}</span>
                            @if($fb->resolved_at)
                            <div style="font-size:10px;color:#94a3b8;margin-top:2px;">{{ $fb->resolved_at->diffForHumans() }}</div>
                            @endif
                            @if($fb->acknowledgement)
                            <div style="font-size:10px;color:#059669;margin-top:3px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $fb->acknowledgement }}">
                                <i class="fas fa-comment-dots me-1"></i>{{ $fb->acknowledgement }}
                            </div>
                            @endif
                        </td>
                        @if($canViewAll ?? $isManager)
                        <td style="white-space:nowrap;">
                            @if($fb->toUser)
                                {{ $fb->toUser->display_label }}
                            @else
                                <span class="badge badge-danger" title="This user was deleted by a manager/admin."><i class="fas fa-user-slash me-1"></i>Deleted user</span>
                            @endif
                            @if($fb->involvedUser)
                                <div style="font-size:10px;color:#d97706;">+ {{ $fb->involvedUser->display_label }}</div>
                            @endif
                        </td>
                        @endif
                        <td style="white-space:nowrap;font-size:12px;">{{ $fb->created_at->format('M d, Y') }}<div style="font-size:10px;color:#94a3b8;">{{ $fb->created_at->format('h:i A') }}</div></td>
                        <td onclick="event.stopPropagation();">
                            @php $isFbRecipient = ($fb->to_user_id === (auth()->id() ?? 0) || $fb->involved_user_id === (auth()->id() ?? 0)); @endphp
                            @if($fb->status === 'open' && $isFbRecipient)
                            <button class="btn btn-sm btn-outline-primary" onclick="updateFeedback({{ $fb->id }}, 'acknowledged')">Acknowledge</button>
                            @endif
                            @if($fb->status === 'acknowledged' && ($canResolve ?? false))
                            <button class="btn btn-sm btn-outline-success" onclick="updateFeedback({{ $fb->id }}, 'resolved')">Resolve</button>
                            @endif
                            @if(($canResolve ?? false) && $fb->status !== 'resolved')
                            <button class="btn btn-sm btn-outline-warning ms-1" onclick="renotifyFeedback({{ $fb->id }}, this)" title="I-notify ulit ang recipient — Manager / CEO / COO"><i class="fas fa-bell"></i></button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-clipboard-check"></i>
                            <p class="mt-2 mb-0">Wala pang production feedback.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $feedbacks->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateFeedback(feedbackId, status) {
    var ack = '';
    if (status === 'acknowledged') {
        ack = prompt('Mag-iwan ng acknowledgement note bago i-acknowledge ang feedback:');
        if (ack === null) return; // cancelled
        ack = ack.trim();
        if (!ack) { alert('Kailangan ng acknowledgement note para i-acknowledge.'); return; }
    }
    fetch('{{ route('sales.prototype.production-feedback.status', 'FEEDBACK_ID') }}'.replace('FEEDBACK_ID', feedbackId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({status: status, acknowledgement: ack})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { location.reload(); }
        else { alert(data.message || 'Failed to update.'); }
    })
    .catch(function() { alert('Request failed.'); });
}

function renotifyFeedback(feedbackId, btn) {
    if (btn) { btn.disabled = true; }
    fetch('{{ route('sales.prototype.production-feedback.notify', 'FEEDBACK_ID') }}'.replace('FEEDBACK_ID', feedbackId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { alert(data.message || 'Reminder sent. 🔔'); location.reload(); }
        else { alert(data.message || 'Failed to send reminder.'); if (btn) { btn.disabled = false; } }
    })
    .catch(function() { alert('Request failed.'); if (btn) { btn.disabled = false; } });
}
</script>
@endpush
