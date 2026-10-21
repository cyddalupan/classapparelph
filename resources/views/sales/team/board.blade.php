@extends('layouts.app')

@section('page-title', 'Team Board')

@push('styles')
<style>
:root{ --tb-bg1:#4f46e5; --tb-bg2:#7c3aed; --tb-gold:#f59e0b; }
.tb-wrap{ padding:1.5rem; max-width:1200px; margin:0 auto; }
.tb-hero{
  background:linear-gradient(135deg,var(--tb-bg1),var(--tb-bg2));
  color:#fff; border-radius:18px; padding:1.5rem 1.75rem; position:relative; overflow:hidden;
  box-shadow:0 12px 30px rgba(79,70,229,.35);
}
.tb-hero:after{content:"";position:absolute;right:-60px;top:-60px;width:220px;height:220px;background:rgba(255,255,255,.08);border-radius:50%;}
.tb-hero h1{font-size:1.75rem;font-weight:800;margin:0;letter-spacing:.3px;display:flex;align-items:center;gap:.6rem;}
.tb-tagline{opacity:.95;margin-top:.45rem;font-size:1.15rem;font-weight:900;letter-spacing:.08em;text-align:center;text-transform:uppercase;}
.tb-period{display:inline-flex;align-items:center;gap:.4rem;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.25);border-radius:999px;padding:.25rem .8rem;font-size:.8rem;font-weight:700;margin-top:.7rem;}
.tb-team{margin-top:1rem;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.28);border-radius:14px;padding:.9rem 1.1rem;max-width:640px;}
.tb-team .lbl{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;opacity:.9;}
.tb-team .row{display:flex;align-items:baseline;gap:.6rem;flex-wrap:wrap;margin-top:.15rem;}
.tb-team .big{font-size:1.9rem;font-weight:900;line-height:1;}
.tb-team .slash{opacity:.75;font-weight:800;font-size:1rem;}
.tb-team .pct{margin-left:auto;font-weight:900;background:rgba(0,0,0,.22);border-radius:999px;padding:.2rem .7rem;font-size:.85rem;}
.tb-team .bar{height:12px;background:rgba(255,255,255,.25);border-radius:999px;overflow:hidden;margin-top:.5rem;}
.tb-team .bar > span{display:block;height:100%;border-radius:999px;background:#fff;transition:width .6s ease;}
.tb-team.over{background:linear-gradient(135deg,rgba(245,158,11,.4),rgba(251,191,36,.22));border-color:#fcd34d;}
.tb-team.over .bar > span{background:linear-gradient(90deg,#fde68a,#fbbf24);}
.tb-team .overmsg{margin-top:.45rem;font-weight:900;color:#fffbeb;}
.tb-team .overmsg.soft{font-weight:700;opacity:.9;}
.tb-live{position:absolute;top:1.1rem;right:1.2rem;display:inline-flex;align-items:center;gap:.4rem;background:rgba(0,0,0,.22);border-radius:999px;padding:.25rem .7rem;font-size:.72rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;}
.tb-dot{width:8px;height:8px;border-radius:50%;background:#34d399;box-shadow:0 0 0 0 rgba(52,211,153,.7);animation:tbpulse 1.8s infinite;}
@keyframes tbpulse{0%{box-shadow:0 0 0 0 rgba(52,211,153,.7)}70%{box-shadow:0 0 0 10px rgba(52,211,153,0)}100%{box-shadow:0 0 0 0 rgba(52,211,153,0)}}

.tb-section{margin-top:1.5rem;}
.tb-section h2{font-size:1.05rem;font-weight:800;color:#1e293b;display:flex;align-items:center;gap:.5rem;margin:0 0 .75rem;}

/* Champion banner */
.tb-champ{background:linear-gradient(135deg,#f59e0b,#fbbf24);color:#7c2d12;border-radius:16px;padding:1rem 1.25rem;display:flex;align-items:center;gap:.9rem;box-shadow:0 10px 24px rgba(245,158,11,.35);margin-top:1.25rem;position:relative;overflow:hidden;}
.tb-champ:after{content:"";position:absolute;right:-40px;top:-40px;width:150px;height:150px;background:rgba(255,255,255,.18);border-radius:50%;}
.tb-champ .cr{font-size:2.1rem;}
.tb-champ .lbl{font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;opacity:.85;}
.tb-champ .nm{font-size:1.25rem;font-weight:900;}
.tb-champ .amt{margin-left:auto;font-size:1.3rem;font-weight:900;white-space:nowrap;}

/* Me card */
.tb-mecard{background:#fff;border:2px solid #e0e7ff;border-radius:18px;padding:1.4rem 1.5rem;box-shadow:0 8px 22px rgba(79,70,229,.10);position:relative;}
.tb-mecard.win{border-color:#fcd34d;background:linear-gradient(180deg,#fffbeb,#fff);}
.tb-mecard h3{margin:0;font-size:1.15rem;font-weight:800;color:#312e81;display:flex;align-items:center;gap:.5rem;}
.tb-big{font-size:2.4rem;font-weight:900;color:#4f46e5;line-height:1.05;}
.tb-big small{font-size:1rem;font-weight:700;color:#94a3b8;}
.tb-sub{color:#64748b;font-size:.85rem;font-weight:600;}
.tb-bar{height:16px;background:#eef2ff;border-radius:999px;overflow:hidden;margin-top:.5rem;}
.tb-bar > span{display:block;height:100%;border-radius:999px;background:linear-gradient(90deg,#6366f1,#8b5cf6);transition:width .6s ease;}
.tb-bar > span.hit{background:linear-gradient(90deg,#f59e0b,#fbbf24);}
.tb-motive{margin-top:.9rem;background:#f5f3ff;border:1px dashed #c4b5fd;color:#5b21b6;border-radius:12px;padding:.7rem .9rem;font-weight:700;font-size:.92rem;}

/* Podium */
.tb-podium{display:flex;align-items:flex-end;justify-content:center;gap:1rem;margin-top:.5rem;flex-wrap:nowrap;}
.tb-pod{background:#fff;border-radius:16px 16px 0 0;border:1px solid #e2e8f0;padding:1rem .8rem;text-align:center;flex:1 1 0;max-width:240px;box-shadow:0 -4px 16px rgba(30,41,59,.08);position:relative;}
.tb-pod .rsl{font-size:.68rem;font-weight:900;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;}
.tb-pod.p1{border-color:#fcd34d;background:linear-gradient(180deg,#fffbeb,#fff);padding-top:1.5rem;}
.tb-pod.p1 .rsl{color:#b45309;}
.tb-pod .med{font-size:1.9rem;line-height:1;}
.tb-pod .tb-av{width:52px;height:52px;margin:.35rem auto .45rem;font-size:1.05rem;}
.tb-pod.p1 .tb-av{width:66px;height:66px;font-size:1.3rem;box-shadow:0 0 0 4px rgba(251,191,36,.28);}
.tb-pod .nm{font-weight:800;color:#1e293b;font-size:.88rem;line-height:1.15;}
.tb-pod .amt{font-weight:900;color:#4f46e5;font-size:1.05rem;margin-top:.2rem;}
.tb-pod .base{border-radius:0 0 12px 12px;}
.tb-pod.p1 .base{height:30px;background:linear-gradient(180deg,#f59e0b,#fbbf24);}
.tb-pod.p2 .base{height:20px;background:linear-gradient(180deg,#94a3b8,#cbd5e1);}
.tb-pod.p3 .base{height:12px;background:linear-gradient(180deg,#d97706,#f59e0b);}

/* Grid / cards */
.tb-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:1rem;}
.tb-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1rem 1.1rem;box-shadow:0 3px 10px rgba(30,41,59,.05);position:relative;}
.tb-card.me{border:2px solid #4f46e5;box-shadow:0 6px 18px rgba(79,70,229,.18);}
.tb-card .hd{display:flex;align-items:center;gap:.55rem;}
.tb-av{width:38px;height:38px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-weight:900;color:#fff;font-size:.88rem;background:#6366f1;flex:0 0 auto;overflow:hidden;}
.tb-av img{width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;}
.tb-pod .tb-av{overflow:hidden;}
.tb-pod .tb-av img{width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;}
.tb-champ-av{width:54px;height:54px;border-radius:50%;overflow:hidden;flex:0 0 auto;border:3px solid rgba(255,255,255,.75);box-shadow:0 2px 8px rgba(124,45,18,.25);position:relative;z-index:1;}
.tb-champ-av img{width:100%;height:100%;object-fit:cover;display:block;}
.tb-mecard .tb-av{width:44px;height:44px;font-size:1rem;}
.tb-card .nm{font-weight:800;color:#1e293b;font-size:.95rem;line-height:1.15;}
.tb-card .posn{color:#94a3b8;font-size:.7rem;font-weight:600;}
.tb-rank{position:absolute;top:.7rem;right:.8rem;font-size:.72rem;font-weight:800;color:#94a3b8;background:#f1f5f9;border-radius:999px;padding:.1rem .5rem;}
.tb-amt{font-size:1.3rem;font-weight:900;color:#0f172a;margin-top:.5rem;}
.tb-amt small{font-size:.8rem;color:#94a3b8;font-weight:700;}
.tb-pct{font-size:.78rem;font-weight:800;color:#4f46e5;}
.tb-bar.sm{height:9px;margin-top:.4rem;}
.tb-badges{margin-top:.5rem;display:flex;flex-wrap:wrap;gap:.3rem;}
.tb-badge{display:inline-flex;align-items:center;gap:.2rem;font-size:.68rem;font-weight:800;border-radius:999px;padding:.12rem .55rem;}
.tb-badge.crown{background:#fef3c7;color:#92400e;}
.tb-badge.mover{background:#dcfce7;color:#166534;}
.tb-badge.streak{background:#fee2e2;color:#b91c1c;}

/* Announcements */
.tb-ann{background:linear-gradient(135deg,#fff7ed,#fffbeb);border:1px solid #fed7aa;border-left:6px solid #f59e0b;border-radius:12px;padding:.8rem 1rem;margin-bottom:.6rem;}
.tb-ann.pinned{border-left-color:#dc2626;background:linear-gradient(135deg,#fef2f2,#fff1f2);}
.tb-ann .t{font-weight:800;color:#7c2d12;display:flex;align-items:center;gap:.4rem;}
.tb-ann .b{color:#44403c;font-size:.9rem;margin-top:.25rem;white-space:pre-line;}
.tb-ann .w{color:#a8a29e;font-size:.72rem;margin-top:.3rem;}
.tb-ann-imgs{display:flex;gap:.45rem;flex-wrap:wrap;margin-top:.55rem;}
.tb-ann-imgs a{display:block;line-height:0;}
.tb-ann-imgs img{width:110px;height:110px;object-fit:cover;border-radius:10px;border:1px solid #fed7aa;transition:transform .15s ease;}
.tb-ann-imgs img:hover{transform:scale(1.04);}
.tb-empty{color:#94a3b8;font-style:italic;}
.tb-foot{color:#94a3b8;font-size:.75rem;text-align:center;margin:1.5rem 0 .5rem;}

/* FAB settings */
.tb-fab{position:fixed;right:1.5rem;bottom:1.5rem;z-index:50;}
.tb-fab-btn{display:inline-flex;align-items:center;gap:.5rem;background:#4f46e5;color:#fff;text-decoration:none;font-weight:800;font-size:.9rem;padding:.7rem 1.15rem;border-radius:999px;box-shadow:0 10px 24px rgba(79,70,229,.45);}
.tb-fab-btn:hover{background:#4338ca;color:#fff;transform:translateY(-1px);}

/* Confetti */
.tb-confetti{position:fixed;inset:0;pointer-events:none;overflow:hidden;z-index:60;}
.tb-conf{position:absolute;width:9px;height:14px;opacity:.95;animation:tbfall linear forwards;}
@keyframes tbfall{to{transform:translateY(110vh) rotate(760deg);opacity:1;}}

@media(max-width:600px){.tb-big{font-size:1.9rem;}.tb-podium{gap:.4rem;}.tb-pod .nm{font-size:.75rem;}}
</style>
@endpush

@section('content')
<div class="tb-wrap">
  <div class="tb-hero">
    <div class="tb-live"><span class="tb-dot"></span> Live</div>
    <h1>🏆 {{ $settings['board_title'] ?: 'Team Board' }}</h1>
    @if(!empty($settings['tagline']))<div class="tb-tagline">{{ $settings['tagline'] }}</div>@endif
    <div class="tb-period">📅 {{ $period['label'] }}</div>
    <div id="tb-team-total"></div>
  </div>

  <div id="tb-live-root"></div>

  @if(!empty($is_admin))
  <div class="tb-fab">
    <a href="{{ route('sales.team.board.settings') }}" class="tb-fab-btn" title="Team Board Control">
      <i class="fas fa-sliders-h"></i> <span>Settings</span>
    </a>
  </div>
  @endif

  <div class="tb-foot">Auto-update bawat 30s · Huling update: <span id="tb-updated">{{ $generated_at }}</span></div>
</div>
@endsection

@push('scripts')
<script>
window.TB_INIT = {!! json_encode([
  'period' => $period,
  'settings' => $settings,
  'agents' => $agents,
  'groups' => $groups,
  'team' => $team,
  'announcements' => $announcements,
  'champion_id' => $champion_id,
  'champion_name' => $champion_name,
  'champion_amount' => $champion_amount,
  'champion_initials' => $champion_initials ?? null,
  'champion_avatar' => $champion_avatar ?? null,
  'top_mover_id' => $top_mover_id,
  'top_mover_name' => $top_mover_name,
  'top_mover_delta' => $top_mover_delta,
  'me_id' => $me_id,
  'generated_at' => $generated_at,
]) !!};
window.TB_DATA_URL = "{{ route('sales.team.board.data') }}";
</script>
<script>
(function(){
  var MEDALS = {1:'🥇',2:'🥈',3:'🥉'};
  var confettiFired = false;

  function peso(n){ return '₱' + Number(n||0).toLocaleString('en-PH',{minimumFractionDigits:0,maximumFractionDigits:0}); }
  function esc(s){ return String(s==null?'':s).replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }
  function av(name, initials, cls, avatar){
    if(avatar){
      return '<span class="tb-av '+(cls||'')+'"><img src="'+esc(avatar)+'" alt="'+esc(name||'')+'" loading="lazy"></span>';
    }
    return '<span class="tb-av '+(cls||'')+'">'+esc(initials||'?')+'</span>';
  }
  function motivate(pct, hasQuota){
    if(!hasQuota) return 'Set na ang quota mo — simulan nang umarangkada! 🚀';
    if(pct>=100) return 'SOBRANG GALING! Na-hit mo na ang quota mo — legendary! 🏆🎉';
    if(pct>=80) return 'Ang lapit mo na! Konti na lang, i-push mo pa! 💪';
    if(pct>=50) return 'Kalahati na! Tuloy lang ang momentum — kaya mo \'to! 🔥';
    if(pct>=25) return 'Magandang simula! Bawat order, malaking hakbang. Keep going! ✨';
    return 'Simula pa lang — ang bawat benta mo ay progreso. Laban! 🌱';
  }
  function bar(pct,hit){ return '<div class="tb-bar"><span class="'+(hit?'hit':'')+'" style="width:'+Math.min(100,pct)+'%"></span></div>'; }

  function badges(a, d){
    if(!d.settings.show_badges) return '';
    var h = '<div class="tb-badges">';
    if(a.rank===1) h += '<span class="tb-badge crown">🏆 Champion</span>';
    if(a.user_id===d.top_mover_id) h += '<span class="tb-badge mover">🔥 Top Mover +'+peso(d.top_mover_delta)+'</span>';
    if(a.streak>=2) h += '<span class="tb-badge streak">⚡ '+a.streak+'-month streak</span>';
    h += '</div>';
    return h;
  }

  function renderChampion(d){
    if(!d.settings.show_champion || !d.champion_id) return '';
    var cav = d.champion_avatar
      ? '<span class="tb-champ-av"><img src="'+esc(d.champion_avatar)+'" alt="'+esc(d.champion_name)+'" loading="lazy"></span>'
      : '<span class="cr">🏆</span>';
    return '<div class="tb-champ">'+cav+'<div><div class="lbl">🏆 Champion ngayong '+esc(d.period.label)+'</div>'+
      '<div class="nm">'+esc(d.champion_name)+'</div></div>'+
      '<div class="amt">'+peso(d.champion_amount)+'</div></div>';
  }

  function renderTeamTotal(d){
    var el = document.getElementById('tb-team-total');
    if(!el) return;
    if(!d.settings.show_team_total || !d.team){ el.innerHTML=''; return; }
    var t=d.team, over=t.over;
    var h='<div class="tb-team '+(over?'over':'')+'">';
    h+='<div class="lbl">🎯 Team Total — '+esc(d.period.label)+'</div>';
    h+='<div class="row"><span class="big">'+peso(t.revenue)+'</span><span class="slash">/ '+peso(t.quota)+'</span>';
    if(t.quota>0) h+='<span class="pct">'+t.pct+'%</span>';
    h+='</div>';
    if(t.quota>0) h+='<div class="bar"><span style="width:'+Math.min(100,t.pct)+'%"></span></div>';
    if(over) h+='<div class="overmsg">🔥 OVER QUOTA! +'+peso(t.over_amount)+' lampas sa target — ang galing ng team!</div>';
    else if(t.quota>0) h+='<div class="overmsg soft">Kulang na lang '+peso(t.remaining)+' para ma-hit ang team target. Kaya niyo \'to!</div>';
    else h+='<div class="overmsg soft">Wala pang naka-set na team quota.</div>';
    h+='</div>';
    el.innerHTML=h;
  }

  function renderMe(d, me){
    if(!me || !d.settings.show_own_card) return '';
    var hit = me.pct>=100;
    var showAmt = d.settings.show_own_amount;
    var html = '<div class="tb-section"><h2>⭐ Ako</h2>';
    html += '<div class="tb-mecard '+(hit?'win':'')+'">';
    html += '<h3>'+av(me.name, me.initials,'',me.avatar)+(hit?' <span>🎉</span>':'')+esc(me.name)+'</h3>';
    if(showAmt){
      html += '<div class="tb-big" style="margin-top:.5rem;">'+peso(me.revenue)+' <small>/ '+peso(me.quota)+'</small></div>';
    } else {
      html += '<div class="tb-sub" style="margin-top:.5rem;">Revenue this month: '+peso(me.revenue)+'</div>';
    }
    if(d.settings.show_percent){ html += '<div class="tb-pct">'+me.pct+'% '+(hit?'— HIT! 🎉':'ng quota')+'</div>'; }
    if(showAmt && me.quota>0){ html += bar(me.pct,hit); }
    if(me.quota>0 && me.remaining>0 && showAmt){
      html += '<div class="tb-sub" style="margin-top:.4rem;">Kulang na lang '+peso(me.remaining)+' — kaya mo \'to!</div>';
    }
    html += badges(me, d);
    html += '<div class="tb-motive">'+motivate(me.pct, me.has_quota)+'</div>';
    if(d.settings.show_rank && me.rank){ html += '<div class="tb-sub" style="margin-top:.5rem;">Rank mo ngayon: #'+me.rank+' sa buong team</div>'; }
    html += '</div></div>';
    return html;
  }

  function renderAnn(d){
    if(!d.settings.show_announcements) return '';
    if(!d.announcements || !d.announcements.length) return '';
    var h = '<div class="tb-section"><h2>📣 Announcements</h2>';
    d.announcements.forEach(function(a){
      h += '<div class="tb-ann '+(a.pinned?'pinned':'')+'">';
      h += '<div class="t">'+(a.pinned?'📌 ':'')+(a.type==='alert'?'⚠️ ':a.type==='motivation'?'💡 ':'📰 ')+esc(a.title)+'</div>';
      if(a.body) h += '<div class="b">'+esc(a.body)+'</div>';
      if(a.images && a.images.length){
        h += '<div class="tb-ann-imgs">';
        a.images.forEach(function(u){
          h += '<a href="'+esc(u)+'" target="_blank"><img src="'+esc(u)+'" alt="announcement"></a>';
        });
        h += '</div>';
      }
      h += '<div class="w">'+esc(a.when||'')+'</div></div>';
    });
    h += '</div>';
    return h;
  }

  function pod(agent, place, d){
    if(!agent) return '<div class="tb-pod p'+place+'" style="visibility:hidden;"></div>';
    var isMe = agent.user_id===d.me_id;
    return '<div class="tb-pod p'+place+'">'+
      '<div class="rsl">#'+place+'</div>'+
      '<div class="med">'+MEDALS[place]+'</div>'+
      av(agent.name, agent.initials,'',agent.avatar)+
      '<div class="nm">'+esc(agent.name)+(isMe?' <span style="color:#4f46e5;">(Ako)</span>':'')+'</div>'+
      '<div class="amt">'+peso(agent.revenue)+'</div>'+
      '<div class="base"></div></div>';
  }

  function renderPodium(d){
    if(!d.settings.show_podium) return '';
    var top = (d.agents||[]).filter(function(a){return a.visible && a.rank;}).slice(0,3);
    if(!top.length) return '';
    return '<div class="tb-section"><h2>🥇 Podium</h2><div class="tb-podium">'+
      pod(top[1],2,d) + pod(top[0],1,d) + pod(top[2],3,d) +
      '</div></div>';
  }

  function agentCard(a, d){
    var hit = a.pct>=100;
    var isMe = (a.user_id===d.me_id);
    var h = '<div class="tb-card '+(isMe?'me':'')+'">';
    if(d.settings.show_rank && a.rank) h += '<div class="tb-rank">#'+a.rank+'</div>';
    h += '<div class="hd">'+av(a.name,a.initials,'',a.avatar)+'<div><div class="nm">'+esc(a.name)+(isMe?' <span style="color:#4f46e5;">(Ako)</span>':'')+'</div>'+
         (a.position?'<div class="posn">'+esc(a.position)+'</div>':'')+'</div></div>';
    if(d.settings.show_amounts || isMe){
      h += '<div class="tb-amt">'+peso(a.revenue)+' <small>/ '+peso(a.quota)+'</small></div>';
    }
    if(d.settings.show_percent){ h += '<div class="tb-pct">'+a.pct+'%</div>'; }
    if(a.has_quota) h += bar(a.pct,hit).replace('tb-bar','tb-bar sm');
    h += badges(a, d);
    h += '</div>';
    return h;
  }

  function renderBoard(d){
    if(!d.settings.show_leaderboard && !d.settings.show_team_quotas) return '';
    var groups = d.groups || [];
    if(!groups.length) return '';
    var h = '';
    groups.forEach(function(g){
      h += '<div class="tb-section"><h2>🏅 '+(g.name==='Ungrouped'?'Iba pa':esc(g.name))+'</h2><div class="tb-grid">';
      (g.agents||[]).forEach(function(a){ h += agentCard(a, d); });
      h += '</div></div>';
    });
    return h;
  }

  function confetti(){
    if(confettiFired) return;
    confettiFired = true;
    var colors = ['#4f46e5','#f59e0b','#ec4899','#10b981','#3b82f6','#fbbf24'];
    var layer = document.createElement('div');
    layer.className = 'tb-confetti';
    document.body.appendChild(layer);
    for(var i=0;i<80;i++){
      var p = document.createElement('div');
      p.className = 'tb-conf';
      p.style.left = Math.random()*100 + 'vw';
      p.style.top = (-10 - Math.random()*20) + 'vh';
      p.style.background = colors[Math.floor(Math.random()*colors.length)];
      p.style.animationDuration = (2.5 + Math.random()*2) + 's';
      p.style.animationDelay = (Math.random()*0.6) + 's';
      p.style.transform = 'rotate('+Math.random()*360+'deg)';
      layer.appendChild(p);
    }
    setTimeout(function(){ layer.remove(); }, 6000);
  }

  function render(d){
    var root = document.getElementById('tb-live-root');
    var me = (d.agents||[]).find(function(a){return a.user_id===d.me_id;});
    root.innerHTML = renderChampion(d) + renderMe(d,me) + renderAnn(d) + renderPodium(d) + renderBoard(d);
    renderTeamTotal(d);
    document.getElementById('tb-updated').textContent = d.generated_at;
    if(d.settings.show_confetti && me && me.pct>=100){ confetti(); }
  }

  render(window.TB_INIT);

  var lastName = JSON.stringify({
    a: window.TB_INIT.agents, g: window.TB_INIT.groups, s: window.TB_INIT.settings,
    an: window.TB_INIT.announcements, c: window.TB_INIT.champion_id, tm: window.TB_INIT.top_mover_id, tt: window.TB_INIT.team
  });

  function tick(){
    fetch(window.TB_DATA_URL, {headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){ return r.json(); })
      .then(function(d){
        var key = JSON.stringify({
          a: d.agents, g: d.groups, s: d.settings,
          an: d.announcements, c: d.champion_id, tm: d.top_mover_id, tt: d.team
        });
        // Kung walang nagbago, i-update lang ang timestamp — walang flicker/re-render.
        if(key === lastName){
          var u = document.getElementById('tb-updated');
          if(u) u.textContent = d.generated_at;
          return;
        }
        lastName = key;
        render(d);
      })
      .catch(function(){});
  }
  setInterval(tick, 30000);
})();
</script>
@endpush
