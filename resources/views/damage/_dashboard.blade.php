{{--
    Damage Dashboard partial — naka-embed na tab sa loob ng /damage.
    Access: CEO (admin) + COO lang (controller-side gate sa DamageReportController::index()).
    Requires: managedShop,total,byStatus,bySeverity,byShop,shopNames,openCount,reviewedCount,
              pendingReview,resolvedCount,dismissedCount,amountTotal,withAmount,topUsers,
              userNames,recent,trend,trendMax,severityLabels,statusLabels  (i.e. $dashboard[...])
--}}
@php $d = $dashboard; @endphp

<style>
    .dmg-hero {
        background: linear-gradient(135deg, #7f1d1d 0%, #b91c1c 45%, #ef4444 100%);
        border-radius: 16px; padding: 20px 24px; color: #fff; position: relative; overflow: hidden;
        box-shadow: 0 8px 24px rgba(185, 28, 28, .25);
    }
    .dmg-hero::after {
        content: "\f071"; font-family: "Font Awesome 6 Free"; font-weight: 900;
        position: absolute; right: 20px; bottom: -26px; font-size: 96px; opacity: .13; transform: rotate(-8deg);
    }
    .dmg-hero h4 { font-weight: 800; letter-spacing: .3px; }
    .dmg-hero .sub { opacity: .92; font-size: 12.5px; }
    .dmg-stat {
        background: #fff; border: 1px solid #eef0f4; border-radius: 14px; padding: 14px 18px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, .05); height: 100%;
    }
    .dmg-stat .val { font-size: 22px; font-weight: 800; line-height: 1.15; color: #111827; }
    .dmg-stat .lbl { font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
    .dmg-stat .ic { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 15px; }
    .dmg-card { background: #fff; border: 1px solid #eef0f4; border-radius: 14px; box-shadow: 0 2px 10px rgba(17, 24, 39, .04); }
    .dmg-card .hd { padding: 12px 16px; border-bottom: 1px solid #f1f3f5; background: #fafbfc; font-weight: 700; border-radius: 14px 14px 0 0; }
    .bar-track { background: #f1f3f5; border-radius: 8px; height: 10px; overflow: hidden; flex: 1; }
    .bar-fill { height: 100%; border-radius: 8px; }
    .sev-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; }
    .sev-minor { background: #6c757d; } .sev-major { background: #fd7e14; } .sev-critical { background: #dc3545; }
    .trend-col { display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 150px; flex: 1; gap: 6px; }
    .trend-bar { width: 60%; max-width: 46px; background: linear-gradient(180deg, #ef4444, #b91c1c); border-radius: 6px 6px 0 0; min-height: 3px; }
    .trend-lbl { font-size: 11px; color: #6b7280; font-weight: 600; }
    .row-link:hover { background: #fafbfc; }
</style>

<div class="dmg-hero mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h4 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Damage Report Dashboard</h4>
        <div class="sub mt-1">
            Buod ng mga damage incident
            @if($d['managedShop']) · <strong>{{ $d['managedShop']->name }}</strong> @else · lahat ng shop @endif
        </div>
    </div>
</div>

{{-- Stat cards --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="dmg-stat d-flex align-items-center gap-2">
            <div class="ic" style="background:#fee2e2;color:#b91c1c;"><i class="fas fa-clipboard-list"></i></div>
            <div><div class="val">{{ number_format($d['total']) }}</div><div class="lbl">Total Reports</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="dmg-stat d-flex align-items-center gap-2">
            <div class="ic" style="background:#fef3c7;color:#b45309;"><i class="fas fa-folder-open"></i></div>
            <div><div class="val">{{ number_format($d['openCount']) }}</div><div class="lbl">Open / Active</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="dmg-stat d-flex align-items-center gap-2">
            <div class="ic" style="background:#e0f2fe;color:#0369a1;"><i class="fas fa-hourglass-half"></i></div>
            <div><div class="val">{{ number_format($d['pendingReview']) }}</div><div class="lbl">Pending Review</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="dmg-stat d-flex align-items-center gap-2">
            <div class="ic" style="background:#dcfce7;color:#15803d;"><i class="fas fa-check-circle"></i></div>
            <div><div class="val">{{ number_format($d['reviewedCount']) }}</div><div class="lbl">Reviewed</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="dmg-stat d-flex align-items-center gap-2">
            <div class="ic" style="background:#fee2e2;color:#b91c1c;"><i class="fas fa-peso-sign"></i></div>
            <div><div class="val">₱{{ number_format($d['amountTotal'], 2) }}</div><div class="lbl">Kabuuang Halaga</div></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="dmg-stat d-flex align-items-center gap-2">
            <div class="ic" style="background:#ede9fe;color:#6d28d9;"><i class="fas fa-tags"></i></div>
            <div><div class="val">{{ number_format($d['withAmount']) }}</div><div class="lbl">May Amount</div></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Status breakdown --}}
    <div class="col-lg-4">
        <div class="dmg-card h-100">
            <div class="hd"><i class="fas fa-layer-group me-1 text-secondary"></i> Ayon sa Status</div>
            <div class="p-3">
                @php $statusTotal = max(1, $d['byStatus']->sum()); @endphp
                @forelse($d['statusLabels'] as $val => $label)
                    @php $c = (int) ($d['byStatus'][$val] ?? 0); @endphp
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div style="width:120px;" class="small fw-semibold text-truncate">{{ $label }}</div>
                        <div class="bar-track"><div class="bar-fill" style="width:{{ $c / $statusTotal * 100 }}%;background:#b91c1c;"></div></div>
                        <div class="small text-muted" style="width:34px;text-align:right;">{{ $c }}</div>
                    </div>
                @empty
                    <div class="text-muted small">Walang datos.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Severity breakdown --}}
    <div class="col-lg-4">
        <div class="dmg-card h-100">
            <div class="hd"><i class="fas fa-fire me-1 text-secondary"></i> Ayon sa Severity</div>
            <div class="p-3">
                @php $sevTotal = max(1, $d['bySeverity']->sum()); @endphp
                @foreach($d['severityLabels'] as $val => $label)
                    @php $c = (int) ($d['bySeverity'][$val] ?? 0); @endphp
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div style="width:80px;" class="small fw-semibold"><span class="sev-dot sev-{{ $val }}"></span>{{ $label }}</div>
                        <div class="bar-track"><div class="bar-fill" style="width:{{ $c / $sevTotal * 100 }}%;background:{{ $val === 'critical' ? '#dc3545' : ($val === 'major' ? '#fd7e14' : '#6c757d') }};"></div></div>
                        <div class="small text-muted" style="width:34px;text-align:right;">{{ $c }}</div>
                    </div>
                @endforeach
                <hr class="my-2">
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Resolved</span><span class="fw-bold text-success">{{ $d['resolvedCount'] }}</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Dismissed</span><span class="fw-bold text-secondary">{{ $d['dismissedCount'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 6-month trend --}}
    <div class="col-lg-4">
        <div class="dmg-card h-100">
            <div class="hd"><i class="fas fa-chart-line me-1 text-secondary"></i> Trend (6 Buwan)</div>
            <div class="p-3">
                <div class="d-flex align-items-end gap-2" style="height:150px;">
                    @foreach($d['trend'] as $t)
                        <div class="trend-col">
                            <div class="small fw-bold text-muted">{{ $t['count'] ?: '' }}</div>
                            <div class="trend-bar" style="height:{{ $t['count'] / $d['trendMax'] * 100 }}%;"></div>
                            <div class="trend-lbl">{{ $t['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Per shop --}}
    <div class="col-lg-5">
        <div class="dmg-card h-100">
            <div class="hd"><i class="fas fa-store me-1 text-secondary"></i> Ayon sa Shop</div>
            <div class="p-3">
                @php $shopTotal = max(1, $d['byShop']->sum()); @endphp
                @forelse($d['byShop']->sortDesc() as $shopId => $c)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div style="width:130px;" class="small fw-semibold text-truncate">{{ $d['shopNames'][$shopId] ?? 'Shop #'.$shopId }}</div>
                        <div class="bar-track"><div class="bar-fill" style="width:{{ $c / $shopTotal * 100 }}%;background:#0ea5e9;"></div></div>
                        <div class="small text-muted" style="width:34px;text-align:right;">{{ $c }}</div>
                    </div>
                @empty
                    <div class="text-muted small">Walang datos.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Top accountable users --}}
    <div class="col-lg-7">
        <div class="dmg-card h-100">
            <div class="hd"><i class="fas fa-users me-1 text-secondary"></i> Top Accountable (ayon sa halaga)</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">User</th>
                            <th class="small text-center">Reports</th>
                            <th class="small text-end">Amount Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($d['topUsers'] as $u)
                            <tr>
                                <td class="small fw-semibold">{{ $d['userNames'][$u->user_id] ?? ('User #'.$u->user_id) }}</td>
                                <td class="small text-center">{{ $u->reports }}</td>
                                <td class="small text-end fw-bold">₱{{ number_format((float) $u->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted small text-center py-3">Wala pang naka-tag na accountable user.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Recent --}}
<div class="dmg-card mb-3">
    <div class="hd d-flex justify-content-between align-items-center">
        <span><i class="fas fa-clock-rotate-left me-1 text-secondary"></i> Pinakabagong Reports</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="small">Report #</th>
                    <th class="small">Shop</th>
                    <th class="small">Severity</th>
                    <th class="small">Status</th>
                    <th class="small">Reviewed?</th>
                    <th class="small text-end">Amount</th>
                    <th class="small">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($d['recent'] as $r)
                    <tr class="row-link" onclick="window.location='{{ route('damage.show', $r->id) }}'" style="cursor:pointer;">
                        <td class="small fw-bold text-danger">{{ $r->report_no }}</td>
                        <td class="small">{{ $r->shop->name ?? '—' }}</td>
                        <td class="small"><span class="sev-dot sev-{{ $r->severity }}"></span>{{ ucfirst($r->severity) }}</td>
                        <td class="small">{{ $d['statusLabels'][$r->status] ?? $r->status }}</td>
                        <td class="small">
                            @if($r->isReviewed())
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Oo</span>
                            @else
                                <span class="badge bg-warning text-dark">Hindi</span>
                            @endif
                        </td>
                        <td class="small text-end">
                            @if($r->hasAmount())
                                <span class="fw-bold">₱{{ number_format($r->damage_amount, 2) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $r->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Walang damage reports.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
