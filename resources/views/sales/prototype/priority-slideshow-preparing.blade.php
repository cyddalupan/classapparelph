<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ginagawa ang MP4…</title>
<style>
    * { box-sizing: border-box; }
    html, body { margin: 0; height: 100%; background: #0b0f14; color: #fff;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
    .card { width: 100%; max-width: 560px; background: #121820; border: 1px solid #1f2937; border-radius: 18px;
        padding: 36px 32px; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,.5); }
    h1 { font-size: 24px; margin: 0 0 8px; }
    p  { color: #94a3b8; font-size: 15px; line-height: 1.5; margin: 6px 0; }
    .spinner { width: 54px; height: 54px; margin: 6px auto 18px; border-radius: 50%;
        border: 5px solid rgba(142,91,216,.25); border-top-color: #8e5bd8; animation: spin .9s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .bar { height: 12px; background: #1f2937; border-radius: 999px; overflow: hidden; margin: 22px 0 10px; }
    .bar > i { display: block; height: 100%; width: 0%; background: linear-gradient(90deg,#6f42c1,#38bdf8); transition: width .3s ease; }
    .meta { display: flex; justify-content: space-between; font-size: 13px; color: #64748b; }
    .cbtn { display: inline-block; margin-top: 22px; background: rgba(255,255,255,.1); color: #fff; border: 1px solid rgba(255,255,255,.18);
        border-radius: 10px; padding: 11px 18px; font-size: 15px; font-weight: 600; text-decoration: none; }
    .cbtn:hover { background: rgba(255,255,255,.22); }
    .err { color: #fca5a5; }
    .note { font-size: 13px; color: #64748b; margin-top: 18px; }
</style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="spinner" id="spin"></div>
        <h1 id="title">🎬 Ginagawa ang MP4…</h1>
        <p id="sub">Hinihintay lang, {{ $total }} slides ({{ $sec }}s bawat isa).</p>
        <div class="bar"><i id="fill"></i></div>
        <div class="meta"><span id="pct">0%</span><span id="cnt">0 / {{ $total }}</span></div>
        <p class="note">Tumatakbo ito sa background — puwede mong iwanan ang tab, awtomatikong magda-download pagkatapos.</p>
        <a class="cbtn" href="{{ route('sales.prototype.priority-slideshow', ['scope' => $scope]) }}">← Bumalik sa slideshow</a>
    </div>
</div>
<script>
(function () {
    const KEY    = @json($key);
    const STATUS = @json(route('sales.prototype.priority-slideshow.video.status'));
    const total  = {{ (int) $total }};

    const fill  = document.getElementById('fill');
    const pctEl = document.getElementById('pct');
    const cntEl = document.getElementById('cnt');
    const title = document.getElementById('title');
    const sub   = document.getElementById('sub');

    function setProgress(done, tot) {
        tot = tot || total || 1;
        const p = Math.max(0, Math.min(99, Math.round((done / tot) * 100)));
        fill.style.width = p + '%';
        pctEl.textContent = p + '%';
        cntEl.textContent = done + ' / ' + tot;
    }

    async function poll() {
        try {
            const r = await fetch(STATUS + '?key=' + encodeURIComponent(KEY), { headers: { 'Accept': 'application/json' } });
            const j = await r.json();
            if (j.state === 'done') {
                setProgress(total, total);
                title.textContent = '✅ Ready na! Nagda-download…';
                sub.textContent = 'Kung hindi nagsimula ang download, pindutin ang button sa ibaba.';
                window.location = j.url;
                return;
            }
            if (j.state === 'error') {
                document.getElementById('spin').style.display = 'none';
                title.textContent = '⚠️ Hindi nagawa ang video';
                title.classList.add('err');
                sub.textContent = j.error || 'May problema sa pag-render. Subukan muli.';
                return;
            }
            setProgress(j.done || 0, j.total || total);
            setTimeout(poll, 2000);
        } catch (e) {
            setTimeout(poll, 3000);
        }
    }
    poll();
})();
</script>
</body>
</html>
