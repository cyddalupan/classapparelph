@extends('layouts.app')

@section('page-title', 'Action Required — My Sales')

@push('styles')
<style>
.ar-page { padding: 2rem; max-width: 1000px; margin: 0 auto; }
.ar-hero { background: linear-gradient(135deg, #7f1d1d, #b91c1c); color: #fff; border-radius: 16px; padding: 1.6rem 1.8rem; margin-bottom: 1.5rem; box-shadow: 0 8px 30px rgba(185,28,28,.25); }
.ar-hero h1 { font-size: 1.5rem; font-weight: 800; margin: 0 0 .35rem; display: flex; align-items: center; gap: .6rem; }
.ar-hero p { margin: 0; font-size: .9rem; opacity: .92; line-height: 1.5; }
.ar-counts { display: flex; flex-wrap: wrap; gap: .6rem; margin-top: 1rem; }
.ar-count { background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.25); border-radius: 10px; padding: .5rem .85rem; font-size: .8rem; font-weight: 600; display: flex; align-items: center; gap: .4rem; }
.ar-count b { font-size: 1.05rem; }
.ar-section { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 1.25rem; overflow: hidden; }
.ar-section > header { display: flex; align-items: center; justify-content: space-between; gap: .6rem; padding: .95rem 1.2rem; border-bottom: 1px solid #f1f5f9; background: #f8fafc; }
.ar-section > header h2 { font-size: .95rem; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: .5rem; }
.ar-pill { font-size: .72rem; font-weight: 700; border-radius: 999px; padding: .18rem .6rem; }
.ar-pill.red { background: #fee2e2; color: #b91c1c; }
.ar-pill.blue { background: #dbeafe; color: #1d4ed8; }
.ar-pill.amber { background: #fef3c7; color: #b45309; }
.ar-pill.green { background: #dcfce7; color: #15803d; }
.ar-empty { padding: 1.4rem 1.2rem; color: #15803d; font-size: .88rem; font-weight: 600; display: flex; align-items: center; gap: .5rem; }
.ar-item { padding: 1.05rem 1.2rem; border-bottom: 1px solid #f1f5f9; }
.ar-item:last-child { border-bottom: none; }
.ar-item-top { display: flex; justify-content: space-between; align-items: flex-start; gap: .75rem; flex-wrap: wrap; }
.ar-title { font-size: .92rem; font-weight: 700; color: #b91c1c; }
.ar-title.plain { color: #1e293b; }
.ar-msg { font-size: .85rem; color: #475569; margin-top: .3rem; line-height: 1.5; }
.ar-meta { font-size: .75rem; color: #94a3b8; margin-top: .4rem; }
.ar-sale-link { font-size: .78rem; font-weight: 600; color: #2563eb; text-decoration: none; white-space: nowrap; }
.ar-sale-link:hover { text-decoration: underline; }
.ar-actions { margin-top: .8rem; display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; }
.ar-actions textarea { flex: 1 1 380px; min-width: 0; padding: .55rem .7rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: .85rem; resize: vertical; }
.ar-btn { border: none; border-radius: 8px; padding: .55rem 1.05rem; font-size: .83rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: .4rem; }
.ar-btn.danger { background: #dc2626; color: #fff; }
.ar-btn.danger:hover { background: #b91c1c; }
.ar-btn.primary { background: #2563eb; color: #fff; }
.ar-btn.primary:hover { background: #1d4ed8; }
.ar-btn[disabled] { opacity: .6; cursor: not-allowed; }
.ar-toast { position: fixed; top: 1.2rem; right: 1.2rem; background: #1e293b; color: #fff; padding: .7rem 1.1rem; border-radius: 10px; font-size: .85rem; z-index: 3000; box-shadow: 0 10px 30px rgba(0,0,0,.25); display: none; }
</style>
@endpush

@section('content')
<div class="ar-page">
    <div class="ar-hero">
        <h1>⚠️ Action Required</h1>
        <p>
            Bago ka makapasok sa <b>My Sales Dashboard</b> at <b>Create Sales</b>, kailangang tapusin muna ang mga
            item sa ibaba. Sagutin ang urgent notifications, i-acknowledge ang production feedback, at i-upload ang
            kulang na photos. Kapag <b>zero na</b>, awtomatikong mabubuksan ulit ang dashboard.
        </p>
        <div class="ar-counts">
            <span class="ar-count">⏰ Urgent <b>{{ $counts['urgent'] }}</b></span>
            <span class="ar-count">🔧 Feedback <b>{{ $counts['feedback'] }}</b></span>
            <span class="ar-count">📸 Missing photos <b>{{ $counts['missing'] }}</b></span>
            <span class="ar-count">🧮 Total <b>{{ $total }}</b></span>
        </div>
    </div>

    {{-- 1) URGENT NOTIFICATIONS --}}
    <div class="ar-section">
        <header>
            <h2>⏰ Urgent notifications needing a response</h2>
            <span class="ar-pill {{ $counts['urgent'] ? 'red' : 'green' }}">{{ $counts['urgent'] }} left</span>
        </header>
        @forelse($urgent as $n)
            <div class="ar-item" id="urgent-{{ $n->id }}">
                <div class="ar-item-top">
                    <div>
                        <div class="ar-title">{{ $n->title }}</div>
                        <div class="ar-msg">{{ $n->message }}</div>
                        <div class="ar-meta">
                            🧾 {{ $n->sale->sales_number ?? ('Sale #' . $n->sale_id) }}
                            · from {{ $n->fromUser->display_label ?? 'Manager' }}
                            · {{ $n->created_at?->diffForHumans() }}
                            @if(($n->reminder_count ?? 1) > 1) · Reminder #{{ $n->reminder_count }} @endif
                        </div>
                    </div>
                    <a class="ar-sale-link" href="{{ route('sales.prototype.show', $n->sale_id) }}" target="_blank" rel="noopener">Open sale ↗</a>
                </div>
                <div class="ar-actions">
                    <textarea id="urgent-text-{{ $n->id }}" rows="2" maxlength="1000" placeholder="Isulat ang dahilan / sagot... (mapo-post sa Comments section ng sale, makikita ng lahat)"></textarea>
                    <button type="button" class="ar-btn danger" onclick="submitUrgent({{ $n->id }}, this)">
                        <i class="fas fa-paper-plane"></i> Send Reason
                    </button>
                </div>
            </div>
        @empty
            <div class="ar-empty">✅ Walang pending urgent notification.</div>
        @endforelse
    </div>

    {{-- 2) PRODUCTION FEEDBACK --}}
    <div class="ar-section">
        <header>
            <h2>🔧 Production feedback to acknowledge</h2>
            <span class="ar-pill {{ $counts['feedback'] ? 'blue' : 'green' }}">{{ $counts['feedback'] }} left</span>
        </header>
        @forelse($feedback as $fb)
            <div class="ar-item" id="feedback-{{ $fb->id }}">
                <div class="ar-item-top">
                    <div>
                        <div class="ar-title plain">
                            {{ \App\Models\ProductionFeedback::CATEGORIES[$fb->category] ?? $fb->category }}
                        </div>
                        <div class="ar-msg">{{ $fb->message }}</div>
                        <div class="ar-meta">
                            🧾 {{ $fb->sale->sales_number ?? ('Sale #' . $fb->sale_id) }}
                            · from {{ $fb->fromUser->display_label ?? 'Manager' }}
                            · {{ $fb->created_at?->diffForHumans() }}
                        </div>
                    </div>
                    <a class="ar-sale-link" href="{{ route('sales.prototype.show', $fb->sale_id) }}" target="_blank" rel="noopener">Open sale ↗</a>
                </div>
                <div class="ar-actions">
                    <button type="button" class="ar-btn primary" onclick="ackFeedback({{ $fb->id }}, this)">
                        <i class="fas fa-check"></i> Acknowledge
                    </button>
                </div>
            </div>
        @empty
            <div class="ar-empty">✅ Walang open production feedback.</div>
        @endforelse
    </div>

    {{-- 3) MISSING PHOTOS --}}
    <div class="ar-section">
        <header>
            <h2>📸 Missing File Photo</h2>
            <span class="ar-pill {{ $counts['missing'] ? 'amber' : 'green' }}">{{ $counts['missing'] }} left</span>
        </header>
        @forelse($missing as $m)
            <div class="ar-item">
                <div class="ar-item-top">
                    <div>
                        <div class="ar-title plain">{{ $m['icon'] }} {{ $m['label'] }}</div>
                        <div class="ar-msg">
                            🧾 {{ $m['sale']->sales_number ?? ('Sale #' . $m['sale']->id) }}
                            · {{ $m['sale']->customer_name ?? '—' }}
                        </div>
                        <div class="ar-meta">I-upload ang kulang na photo sa loob ng sale (may Upload button sa show page).</div>
                    </div>
                    <a class="ar-sale-link" href="{{ route('sales.prototype.show', $m['sale']->id) }}" target="_blank" rel="noopener">Upload now ↗</a>
                </div>
            </div>
        @empty
            <div class="ar-empty">✅ Kumpleto ang File Photo sa lahat ng active sales mo.</div>
        @endforelse
    </div>
</div>

<div class="ar-toast" id="arToast"></div>
@endsection

@push('scripts')
<script>
var AR_CSRF = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

function arToast(msg, ok) {
    var t = document.getElementById('arToast');
    t.textContent = msg;
    t.style.background = ok === false ? '#7f1d1d' : '#1e293b';
    t.style.display = 'block';
    clearTimeout(window.__arT);
    window.__arT = setTimeout(function () { t.style.display = 'none'; }, 3500);
}

function submitUrgent(id, btn) {
    var ta = document.getElementById('urgent-text-' + id);
    var text = (ta.value || '').trim();
    if (!text) { arToast('⚠️ Isulat muna ang reason.', false); return; }
    btn.disabled = true;
    var old = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    fetch('{{ route('sales.prototype.respond-urgent', ':ID') }}'.replace(':ID', id), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': AR_CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ response: text })
    })
    .then(function (r) { return r.json(); })
    .then(function (d) {
        if (d.success) {
            arToast('✅ ' + (d.message || 'Response posted.'));
            var el = document.getElementById('urgent-' + id);
            if (el) el.style.opacity = .45;
            setTimeout(function () { location.reload(); }, 900);
        } else {
            arToast('❌ ' + (d.message || 'Error sending response'), false);
            btn.disabled = false; btn.innerHTML = old;
        }
    })
    .catch(function () { arToast('❌ Error sending response', false); btn.disabled = false; btn.innerHTML = old; });
}

function ackFeedback(id, btn) {
    var ack = prompt('Mag-iwan ng acknowledgement note bago i-acknowledge ang feedback:');
    if (ack === null) return;
    ack = ack.trim();
    if (!ack) { arToast('⚠️ Kailangan ng acknowledgement note.', false); return; }
    btn.disabled = true;
    var old = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    fetch('{{ route('sales.prototype.production-feedback.status', 'FEEDBACK_ID') }}'.replace('FEEDBACK_ID', id), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': AR_CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ status: 'acknowledged', acknowledgement: ack })
    })
    .then(function (r) { return r.json(); })
    .then(function (d) {
        if (d.success) {
            arToast('✅ Feedback acknowledged.');
            var el = document.getElementById('feedback-' + id);
            if (el) el.style.opacity = .45;
            setTimeout(function () { location.reload(); }, 900);
        } else {
            arToast('❌ ' + (d.message || 'Error'), false);
            btn.disabled = false; btn.innerHTML = old;
        }
    })
    .catch(function () { arToast('❌ Error', false); btn.disabled = false; btn.innerHTML = old; });
}
</script>
@endpush
