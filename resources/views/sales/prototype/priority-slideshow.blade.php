<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Priority Mockup Slideshow</title>
<style>
    * { box-sizing: border-box; }
    html, body { margin: 0; height: 100%; background: #0b0f14; color: #fff;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; overflow: hidden; }
    #deck { position: relative; width: 100vw; height: 100vh; display: flex; align-items: center; justify-content: center; }
    .slide { position: absolute; inset: 0; display: none; align-items: center; justify-content: center; padding: 40px 40px 110px; }
    .slide.active { display: flex; animation: fade .45s ease; }
    @keyframes fade { from { opacity: 0; transform: scale(.99); } to { opacity: 1; transform: scale(1); } }
    .frame { display: flex; width: 100%; height: 100%; gap: 32px; align-items: center; justify-content: center; }
    .img-wrap { flex: 1 1 auto; height: 100%; display: flex; align-items: center; justify-content: center; min-width: 0; }
    .img-wrap img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 14px;
        box-shadow: 0 20px 60px rgba(0,0,0,.6); background: #141a22; }
    .no-img { color: #64748b; font-size: 20px; border: 2px dashed #334155; border-radius: 14px; padding: 60px 80px; }
    .meta { flex: 0 0 380px; display: flex; flex-direction: column; gap: 14px; justify-content: center; }
    .prio-chip { display: inline-flex; align-items: center; gap: 10px; align-self: flex-start;
        background: linear-gradient(135deg,#6f42c1,#8e5bd8); border-radius: 999px; padding: 8px 20px;
        font-weight: 800; font-size: 22px; letter-spacing: .5px; box-shadow: 0 8px 20px rgba(111,66,193,.4); }
    .prio-chip.none { background: #334155; }
    .sale-no { font-size: 15px; color: #94a3b8; font-weight: 600; letter-spacing: .5px; word-break: break-all; }
    .customer { font-size: 40px; font-weight: 800; line-height: 1.1; }
    .rows { display: flex; flex-direction: column; gap: 10px; margin-top: 6px; }
    .row { display: flex; justify-content: space-between; gap: 16px; padding: 12px 16px; background: #141a22;
        border: 1px solid #1f2937; border-radius: 12px; font-size: 17px; }
    .row .k { color: #94a3b8; font-weight: 600; }
    .row .v { font-weight: 700; }
    .qty-big { font-size: 26px; color: #38bdf8; }
    /* controls */
    #bar { position: fixed; left: 0; right: 0; bottom: 0; height: 84px; display: flex; align-items: center;
        justify-content: center; gap: 14px; background: linear-gradient(to top, rgba(0,0,0,.85), rgba(0,0,0,0)); }
    .cbtn { background: rgba(255,255,255,.1); color: #fff; border: 1px solid rgba(255,255,255,.18);
        border-radius: 10px; padding: 10px 16px; font-size: 16px; font-weight: 600; cursor: pointer; }
    .cbtn:hover { background: rgba(255,255,255,.22); }
    .cbtn.play { background: linear-gradient(135deg,#6f42c1,#8e5bd8); border: none; }
    #counter { font-size: 15px; font-weight: 700; color: #cbd5e1; min-width: 70px; text-align: center; }
    #progress { position: fixed; top: 0; left: 0; height: 4px; background: linear-gradient(90deg,#6f42c1,#38bdf8); width: 0%; transition: width .1s linear; }
    .empty { text-align: center; color: #94a3b8; }
    .empty h2 { font-size: 28px; color: #e2e8f0; }
    a.back { position: fixed; top: 14px; right: 16px; color: #94a3b8; text-decoration: none; font-size: 14px;
        background: rgba(255,255,255,.08); padding: 7px 12px; border-radius: 8px; }
    a.back:hover { color: #fff; background: rgba(255,255,255,.16); }
    .dots { position: fixed; bottom: 92px; left: 0; right: 0; display: flex; gap: 7px; justify-content: center; flex-wrap: wrap; padding: 0 20px; }
    .dot { width: 9px; height: 9px; border-radius: 50%; background: rgba(255,255,255,.25); cursor: pointer; }
    .dot.on { background: #8e5bd8; transform: scale(1.25); }
    .dot.hasprio { box-shadow: 0 0 0 2px rgba(142,91,216,.5); }
</style>
</head>
<body>
<div id="progress"></div>
<a class="back" href="{{ route('sales.prototype.ga-order-list') }}">← Back to GA list</a>

@if(empty($slides))
    <div id="deck"><div class="empty">
        <h2>Walang maipapakitang job 😅</h2>
        <p>{{ $scope === 'prio' ? 'Walang WIP job na may priority pa. Subukan ang "Lahat ng WIP".' : 'Walang WIP job sa active stages.' }}</p>
        <p><a class="cbtn" style="text-decoration:none;" href="{{ route('sales.prototype.priority-slideshow') }}?scope=all">Ipakita lahat ng WIP</a></p>
    </div></div>
@else
<div id="deck">
    @foreach($slides as $i => $s)
        <div class="slide {{ $i === 0 ? 'active' : '' }}" data-idx="{{ $i }}">
            <div class="frame">
                <div class="img-wrap">
                    @if(!empty($s['img']))
                        <img src="{{ $s['img'] }}" alt="Mockup {{ $s['sales_number'] }}">
                    @else
                        <div class="no-img">Walang mockup na naka-upload</div>
                    @endif
                </div>
                <div class="meta">
                    <span class="prio-chip {{ $s['priority'] ? '' : 'none' }}">
                        {{ $s['priority'] ? '★ PRIORITY ' . $s['priority'] : '· Walang priority' }}
                    </span>
                    <div class="sale-no">{{ $s['sales_number'] }}</div>
                    <div class="customer">{{ $s['customer'] ?: '—' }}</div>
                    <div class="rows">
                        <div class="row"><span class="k">Dami (Qty)</span><span class="v qty-big">{{ $s['qty'] ?: '—' }}</span></div>
                        <div class="row"><span class="k">Department</span><span class="v">{{ $s['dept'] }}</span></div>
                        <div class="row"><span class="k">Stage</span><span class="v">{{ $s['stage'] }}</span></div>
                        <div class="row"><span class="k">Due date</span><span class="v">{{ $s['due'] ?: '—' }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="dots" id="dots"></div>

<div id="bar">
    <button class="cbtn" id="prev">⏮ Prev</button>
    <button class="cbtn play" id="play">⏸ Pause</button>
    <button class="cbtn" id="next">Next ⏭</button>
    <span id="counter"></span>
    <button class="cbtn" id="speed" title="Palitan ang bilis">⏱ 6s</button>
    <button class="cbtn" id="scope" data-scope="{{ $scope }}">{{ $scope === 'prio' ? '★ Priority only' : '☰ Lahat ng WIP' }}</button>
    <button class="cbtn" id="fs">⛶ Fullscreen</button>
</div>

<script>
(function () {
    const slides = Array.from(document.querySelectorAll('.slide'));
    const total  = slides.length;
    if (!total) return;

    let idx = 0;
    let playing = true;
    let delay = 6000;
    let timer = null;
    let tStart = 0;

    const counter = document.getElementById('counter');
    const playBtn = document.getElementById('play');
    const dotsEl  = document.getElementById('dots');
    const progEl  = document.getElementById('progress');

    // dots
    slides.forEach((sl, i) => {
        const d = document.createElement('div');
        d.className = 'dot' + (sl.querySelector('.prio-chip') && !sl.querySelector('.prio-chip.none') ? ' hasprio' : '');
        d.addEventListener('click', () => go(i));
        dotsEl.appendChild(d);
    });
    const dots = Array.from(dotsEl.children);

    function render() {
        slides.forEach((sl, i) => sl.classList.toggle('active', i === idx));
        dots.forEach((d, i) => d.classList.toggle('on', i === idx));
        counter.textContent = (idx + 1) + ' / ' + total;
    }
    function go(i) { idx = ((i % total) + total) % total; render(); restart(); }
    function next() { go(idx + 1); }
    function prev() { go(idx - 1); }

    function tick() {
        const pct = Math.min(100, ((Date.now() - tStart) / delay) * 100);
        progEl.style.width = pct + '%';
        if (pct >= 100) { next(); }
    }
    function startTimer() {
        clearInterval(timer);
        tStart = Date.now();
        timer = setInterval(tick, 50);
    }
    function stopTimer() { clearInterval(timer); timer = null; }
    function restart() { progEl.style.width = '0%'; if (playing) startTimer(); }

    function setPlaying(v) {
        playing = v;
        playBtn.textContent = playing ? '⏸ Pause' : '▶ Play';
        if (playing) startTimer(); else stopTimer();
    }

    document.getElementById('next').onclick = next;
    document.getElementById('prev').onclick = prev;
    playBtn.onclick = () => setPlaying(!playing);

    document.getElementById('speed').onclick = function () {
        delay = delay === 6000 ? 3000 : (delay === 3000 ? 10000 : 6000);
        this.textContent = '⏱ ' + (delay / 1000) + 's';
        restart();
    };
    document.getElementById('scope').onclick = function () {
        const cur = this.dataset.scope;
        window.location = '{{ route('sales.prototype.priority-slideshow') }}?scope=' + (cur === 'prio' ? 'all' : 'prio');
    };
    document.getElementById('fs').onclick = function () {
        if (!document.fullscreenElement) { document.documentElement.requestFullscreen?.(); }
        else { document.exitFullscreen?.(); }
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight') next();
        else if (e.key === 'ArrowLeft') prev();
        else if (e.key === ' ') { e.preventDefault(); setPlaying(!playing); }
        else if (e.key === 'Escape' && document.fullscreenElement) document.exitFullscreen?.();
    });

    render();
    setPlaying(true);
})();
</script>
@endif
</body>
</html>
