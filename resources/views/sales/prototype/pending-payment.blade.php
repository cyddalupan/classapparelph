@extends('layouts.app')

@section('title', 'Pending Payment — DISPATCH Orders')

@push('styles')
<style>
    .pp-wrap { padding: 18px 22px 40px; }
    .pp-head { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 6px; }
    .pp-head h1 { font-size: 1.35rem; font-weight: 700; margin: 0; color: #0f172a; }
    .pp-sub { color: #64748b; font-size: .85rem; margin: 2px 0 16px; }
    .pp-back { margin-left: auto; background: #475569; color: #fff; border: none; border-radius: 8px; padding: 8px 14px; font-size: .82rem; font-weight: 600; text-decoration: none; }
    .pp-back:hover { background: #334155; color: #fff; }
    .pp-textbtn { background: #0ea5e9; color: #fff; border: none; border-radius: 8px; padding: 8px 14px; font-size: .82rem; font-weight: 600; cursor: pointer; }
    .pp-textbtn:hover { background: #0284c7; color: #fff; }

    .pp-stats { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
    .pp-stat { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 16px; min-width: 150px; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
    .pp-stat .lbl { font-size: .68rem; letter-spacing: .04em; text-transform: uppercase; color: #94a3b8; font-weight: 700; }
    .pp-stat .val { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin-top: 2px; }
    .pp-stat.warn .val { color: #dc2626; }
    .pp-stat.ok .val { color: #16a34a; }

    .pp-agent { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,.05); }
    .pp-agent-head { display: flex; align-items: center; gap: 10px; background: linear-gradient(135deg,#4f46e5 0%,#6366f1 100%); color: #fff; padding: 10px 16px; }
    .pp-agent-head .nm { font-weight: 700; font-size: .95rem; }
    .pp-agent-head .ct { font-size: .74rem; background: rgba(255,255,255,.22); border-radius: 999px; padding: 2px 10px; font-weight: 600; }
    .pp-agent-head .sub { margin-left: auto; font-size: .78rem; font-weight: 600; }
    .pp-agent-head .sub .u { color: #fecaca; }

    table.pp-tbl { width: 100%; border-collapse: collapse; font-size: .84rem; }
    table.pp-tbl th { background: #f8fafc; color: #475569; text-align: left; padding: 9px 14px; font-weight: 700; font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
    table.pp-tbl th.num, table.pp-tbl td.num { text-align: right; white-space: nowrap; }
    table.pp-tbl td { padding: 9px 14px; border-bottom: 1px solid #f1f5f9; color: #1e293b; }
    table.pp-tbl tbody tr:hover td { background: #f8fafc; }
    table.pp-tbl a.sale-num { color: #4f46e5; font-weight: 600; text-decoration: none; }
    table.pp-tbl a.sale-num:hover { text-decoration: underline; }
    table.pp-tbl tr.subtotal td { background: #f1f5f9; font-weight: 700; color: #0f172a; border-top: 1px solid #e2e8f0; }
    .pp-pend { color: #d97706; font-weight: 600; }
    .pp-ver  { color: #16a34a; font-weight: 600; }
    .pp-unp  { color: #dc2626; font-weight: 700; }
    .pp-zero { color: #cbd5e1; }

    .pp-empty { background: #fff; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 40px; text-align: center; color: #94a3b8; }

    /* KPI cards (potential collectibles) — Andrew 2026-10-15 */
    .pp-kpis { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 18px; }
    .pp-kpi { flex: 1 1 240px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(15,23,42,.05); border-left: 4px solid #94a3b8; }
    .pp-kpi.collect { border-left-color: #dc2626; }
    .pp-kpi.verify { border-left-color: #d97706; }
    .pp-kpi .k-lbl { font-size: .7rem; letter-spacing: .05em; text-transform: uppercase; color: #64748b; font-weight: 700; }
    .pp-kpi .k-val { font-size: 1.5rem; font-weight: 800; margin-top: 3px; }
    .pp-kpi.collect .k-val { color: #dc2626; }
    .pp-kpi.verify .k-val { color: #d97706; }
    .pp-kpi .k-sub { font-size: .76rem; color: #94a3b8; margin-top: 3px; }
    .pp-kpi a { text-decoration: none; }

    /* Filter bar */
    .pp-filters { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 16px; margin-bottom: 16px; display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
    .pp-filters .fld { display: flex; flex-direction: column; gap: 3px; }
    .pp-filters label { font-size: .66rem; letter-spacing: .04em; text-transform: uppercase; color: #94a3b8; font-weight: 700; }
    .pp-filters select, .pp-filters input[type=text] { border: 1px solid #cbd5e1; border-radius: 8px; padding: 7px 10px; font-size: .82rem; color: #1e293b; background: #fff; min-width: 150px; }
    .pp-filters .btn-go { background: #4f46e5; color: #fff; border: none; border-radius: 8px; padding: 8px 16px; font-size: .82rem; font-weight: 600; cursor: pointer; }
    .pp-filters .btn-go:hover { background: #4338ca; }
    .pp-filters .btn-reset { background: #e2e8f0; color: #334155; border: none; border-radius: 8px; padding: 8px 14px; font-size: .82rem; font-weight: 600; text-decoration: none; }
    .pp-filters .btn-reset:hover { background: #cbd5e1; }
    .pp-scope { display: inline-flex; border: 1px solid #cbd5e1; border-radius: 9px; overflow: hidden; }
    .pp-scope a { padding: 7px 14px; font-size: .8rem; font-weight: 600; color: #475569; text-decoration: none; background: #fff; }
    .pp-scope a.on { background: #4f46e5; color: #fff; }
</style>
@endpush

@section('content')
<div class="pp-wrap">
    <div class="pp-head">
        <h1>💳 Pending Payment</h1>
        <button type="button" class="pp-textbtn" id="ppTextBtn" title="Ilista ang buong listahan bilang text (copy-paste)">📋 List as Text</button>
        <a href="{{ route('sales.prototype.list') }}" class="pp-back">← Bumalik sa List</a>
    </div>
    @php $scope = $filters['scope'] ?? 'dispatch'; @endphp
    <div class="pp-sub">
        @if($scope === 'all')
            <b>Lahat ng may balanse</b> (potential collectibles, kahit anong stage) — naka-group per Agent.
        @else
            Mga order na <b>naka-tag na DISPATCH</b> (ready for delivery) — naka-group per Agent.
        @endif
        <b>{{ $orderCount }}</b> order · <b>{{ $agentCount }}</b> agent.
    </div>

    {{-- Potential collectibles KPIs (kaparehong basehan ng Production dashboard) --}}
    <div class="pp-kpis">
        <div class="pp-kpi collect">
            <div class="k-lbl">Pa Sisingilin (Potential Collectibles)</div>
            <div class="k-val">₱{{ number_format($kpi['collectible'], 2) }}</div>
            <div class="k-sub">{{ number_format($kpi['collectible_orders']) }} order(s) may balance pa</div>
        </div>
        <div class="pp-kpi verify">
            <div class="k-lbl">Pending Verification</div>
            <div class="k-val"><a href="{{ url('/sales/verification') }}" style="color:inherit;">₱{{ number_format($kpi['pending_verification'], 2) }}</a></div>
            <div class="k-sub">Naghihintay ng verify</div>
        </div>
    </div>

    {{-- Filter bar --}}
    <form class="pp-filters" method="GET" action="{{ url()->current() }}">
        <div class="fld">
            <label>Scope</label>
            <div class="pp-scope">
                <a href="{{ url()->current() }}?scope=dispatch" class="{{ $scope === 'dispatch' ? 'on' : '' }}">Naka-tag na DISPATCH</a>
                <a href="{{ url()->current() }}?scope=all" class="{{ $scope === 'all' ? 'on' : '' }}">Lahat ng may balanse</a>
            </div>
        </div>
        <input type="hidden" name="scope" value="{{ $scope }}">
        <div class="fld">
            <label>Agent</label>
            <select name="agent_id">
                <option value="">— Lahat —</option>
                @foreach($agentOptions as $a)
                    <option value="{{ $a->sales_agent_id }}" {{ (string)($filters['agent_id'] ?? '') === (string)$a->sales_agent_id ? 'selected' : '' }}>{{ $a->sales_agent_name ?: ('Agent #'.$a->sales_agent_id) }}</option>
                @endforeach
            </select>
        </div>
        <div class="fld">
            <label>Stage</label>
            <select name="stage">
                <option value="">— Lahat —</option>
                @foreach($stageOptions as $st)
                    <option value="{{ $st }}" {{ ($filters['stage'] ?? '') === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        <div class="fld">
            <label>Search</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Customer / Sales# / Agent">
        </div>
        @if($scope === 'all')
        <div class="fld" style="flex-direction:row;align-items:center;gap:6px;">
            <input type="checkbox" name="show_all" id="ppShowAll" value="1" {{ !empty($filters['show_all']) ? 'checked' : '' }}>
            <label for="ppShowAll" style="text-transform:none;font-size:.78rem;color:#475569;">Ipakita lahat (kahit wala nang balanse)</label>
        </div>
        @endif
        <div class="fld">
            <label>&nbsp;</label>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn-go">🔎 I-filter</button>
                <a href="{{ url()->current() }}?scope={{ $scope }}" class="btn-reset">Reset</a>
            </div>
        </div>
    </form>

    <div class="pp-stats">
        <div class="pp-stat"><div class="lbl">Total Amount</div><div class="val">₱{{ number_format($running['total'], 2) }}</div></div>
        <div class="pp-stat"><div class="lbl">Pending Balance</div><div class="val" style="color:#d97706;">₱{{ number_format($running['pending'], 2) }}</div></div>
        <div class="pp-stat ok"><div class="lbl">Verified Balance</div><div class="val">₱{{ number_format($running['verified'], 2) }}</div></div>
        <div class="pp-stat warn"><div class="lbl">Unpaid Balance</div><div class="val">₱{{ number_format($running['unpaid'], 2) }}</div></div>
    </div>

    @forelse($groups as $g)
        <div class="pp-agent">
            <div class="pp-agent-head">
                <span class="nm">👤 {{ $g['agent'] }}</span>
                <span class="ct">{{ count($g['rows']) }} order</span>
                <span class="sub">Unpaid: <span class="u">₱{{ number_format($g['sub']['unpaid'], 2) }}</span></span>
            </div>
            <table class="pp-tbl">
                <thead>
                    <tr>
                        <th>Agent Name</th>
                        <th>SALES#</th>
                        @if(($filters['scope'] ?? 'dispatch') === 'all')<th>Stage</th>@endif
                        <th class="num">Total Amount</th>
                        <th class="num">Pending Balance</th>
                        <th class="num">Verified Balance</th>
                        <th class="num">Unpaid Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($g['rows'] as $r)
                        <tr>
                            <td>{{ $r['agent'] }}</td>
                            <td>
                                <a class="sale-num" href="{{ route('sales.prototype.show', $r['id']) }}" target="_blank">{{ $r['number'] }}</a>
                                @if(!empty($r['customer']))<div style="font-size:.72rem;color:#94a3b8;">{{ $r['customer'] }}</div>@endif
                            </td>
                            @if(($filters['scope'] ?? 'dispatch') === 'all')
                                <td><span style="font-size:.72rem;background:#eef2ff;color:#4338ca;border-radius:6px;padding:2px 8px;font-weight:600;">{{ $r['stage'] ?: '—' }}</span></td>
                            @endif
                            <td class="num">₱{{ number_format($r['total'], 2) }}</td>
                            <td class="num {{ $r['pending'] > 0 ? 'pp-pend' : 'pp-zero' }}">₱{{ number_format($r['pending'], 2) }}</td>
                            <td class="num {{ $r['verified'] > 0 ? 'pp-ver' : 'pp-zero' }}">₱{{ number_format($r['verified'], 2) }}</td>
                            <td class="num {{ $r['has_unpaid'] ? 'pp-unp' : 'pp-zero' }}">₱{{ number_format($r['unpaid'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="subtotal">
                        <td colspan="{{ ($filters['scope'] ?? 'dispatch') === 'all' ? 3 : 2 }}">Subtotal — {{ $g['agent'] }}</td>
                        <td class="num">₱{{ number_format($g['sub']['total'], 2) }}</td>
                        <td class="num">₱{{ number_format($g['sub']['pending'], 2) }}</td>
                        <td class="num">₱{{ number_format($g['sub']['verified'], 2) }}</td>
                        <td class="num">₱{{ number_format($g['sub']['unpaid'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <div class="pp-empty">
            @if(($filters['scope'] ?? 'dispatch') === 'all')
                🎉 Walang order na may balanse sa filter na ito.
            @else
                🎉 Walang DISPATCH order na may pending payment ngayon.
            @endif
        </div>
    @endforelse
</div>

{{-- ===== "List as Text" modal (Andrew 2026-10-09) — display-only, walang DB write ===== --}}
<div class="modal fade" id="ppTextModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📋 Pending Payment — as text</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <textarea id="ppTextArea" class="form-control" rows="18" readonly style="font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12px;white-space:pre;"></textarea>
            </div>
            <div class="modal-footer">
                <span id="ppTextCopied" class="text-success me-auto" style="display:none;font-size:12px;">✓ Copied!</span>
                <button type="button" class="btn btn-primary" id="ppTextCopy">📄 Copy</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var PP_TEXT = @json($ppText);
    var btn = document.getElementById('ppTextBtn');
    if (!btn) { return; }
    var modalEl = document.getElementById('ppTextModal');
    var area = document.getElementById('ppTextArea');

    function openModal() {
        area.value = PP_TEXT || '(walang laman ang listahan)';
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (modalEl && modalEl.style) {
            modalEl.style.display = 'block';
            modalEl.classList.add('show');
        } else {
            window.prompt('Pending Payment — as text (Ctrl/Cmd+C):', area.value);
        }
    }
    btn.addEventListener('click', openModal);

    var copyBtn = document.getElementById('ppTextCopy');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            var done = function () {
                var c = document.getElementById('ppTextCopied');
                if (c) { c.style.display = 'inline'; setTimeout(function () { c.style.display = 'none'; }, 1500); }
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(area.value).then(done).catch(function () { area.select(); document.execCommand('copy'); done(); });
            } else {
                area.select(); document.execCommand('copy'); done();
            }
        });
    }
})();
</script>
@endsection
