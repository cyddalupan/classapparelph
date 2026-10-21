@extends('layouts.app')

@section('page-title', 'Team Board Control')

@push('styles')
<style>
.tb-set{ padding:1.5rem; max-width:1100px; margin:0 auto; }
.tb-set h1{font-size:1.5rem;font-weight:800;color:#1e293b;margin:0 0 .25rem;display:flex;align-items:center;gap:.5rem;}
.tb-set .lead{color:#64748b;font-size:.9rem;margin-bottom:1.25rem;}
.tb-panel{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1.25rem 1.4rem;margin-bottom:1.25rem;box-shadow:0 3px 10px rgba(30,41,59,.05);}
.tb-panel h2{font-size:1.05rem;font-weight:800;color:#312e81;margin:0 0 .9rem;display:flex;align-items:center;gap:.5rem;}
.tb-ok{background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;border-radius:10px;padding:.7rem 1rem;margin-bottom:1rem;font-weight:600;}
.tb-field{margin-bottom:.8rem;}
.tb-field label{display:block;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.25rem;}
.tb-field input[type=text],.tb-field textarea,.tb-field input[type=number]{width:100%;padding:.5rem .7rem;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;}
.tb-toggle{display:flex;align-items:center;gap:.6rem;padding:.45rem 0;border-bottom:1px dashed #eef2f7;font-size:.9rem;color:#334155;}
.tb-toggle:last-child{border-bottom:0;}
.tb-toggle input{width:18px;height:18px;}
.tb-table{width:100%;border-collapse:collapse;font-size:.9rem;}
.tb-table th{text-align:left;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:#64748b;padding:.5rem .4rem;border-bottom:2px solid #e2e8f0;}
.tb-table td{padding:.45rem .4rem;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.tb-table input[type=number]{width:150px;padding:.45rem .6rem;border:1px solid #d1d5db;border-radius:8px;text-align:right;font-weight:700;}
.tb-btn{display:inline-flex;align-items:center;gap:.4rem;background:#4f46e5;color:#fff;border:0;border-radius:10px;padding:.55rem 1.1rem;font-weight:700;font-size:.9rem;cursor:pointer;text-decoration:none;}
.tb-btn:hover{background:#4338ca;color:#fff;}
.tb-btn.gray{background:#e2e8f0;color:#334155;}
.tb-btn.gray:hover{background:#cbd5e1;color:#334155;}
.tb-btn.sm{padding:.35rem .7rem;font-size:.78rem;border-radius:8px;}
.tb-btn.red{background:#dc2626;}
.tb-btn.red:hover{background:#b91c1c;}
.tb-annrow{border:1px solid #e2e8f0;border-left:5px solid #f59e0b;border-radius:10px;padding:.7rem .9rem;margin-bottom:.6rem;display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;}
.tb-annrow.off{opacity:.55;border-left-color:#94a3b8;}
.tb-annrow .t{font-weight:800;color:#1e293b;}
.tb-annrow .b{color:#475569;font-size:.85rem;margin-top:.2rem;white-space:pre-line;}
.tb-annrow .meta{color:#94a3b8;font-size:.72rem;margin-top:.3rem;}
.tb-pill{display:inline-block;font-size:.68rem;font-weight:800;border-radius:999px;padding:.1rem .5rem;background:#eef2ff;color:#4338ca;text-transform:uppercase;letter-spacing:.04em;}
.tb-pill.pin{background:#fef3c7;color:#92400e;}
.tb-pill.off{background:#f1f5f9;color:#64748b;}
</style>
@endpush

@section('content')
<div class="tb-set">
  <h1>🏆 Team Board Control</h1>
  <div class="lead">Dito mo kontrolado ang Team Board: quota ng bawat agent, announcements, at kung ano ang makikita nila. Period: <strong>{{ $period['label'] }}</strong>.</div>

  @if(session('ok'))
    <div class="tb-ok">✅ {{ session('ok') }}</div>
  @endif

  {{-- Access gate --}}
  <div class="tb-panel" style="border:2px solid #fca5a5;background:#fff7f7;">
    <h2>🔒 Access — Sino ang makakapasok?</h2>
    <form method="POST" action="{{ route('sales.team.board.settings.save') }}">
      @csrf
      <input type="hidden" name="board_title" value="{{ $settings->board_title }}">
      <input type="hidden" name="tagline" value="{{ $settings->tagline }}">
      @foreach(['show_own_card','show_team_quotas','show_leaderboard','show_rank','show_amounts','show_own_amount','show_percent','show_announcements'] as $k)
        @if($settings->$k)<input type="hidden" name="{{ $k }}" value="1">@endif
      @endforeach
      <label class="tb-toggle" style="border:0;font-size:1rem;">
        <input type="checkbox" name="agents_can_view" value="1" {{ $settings->agents_can_view ? 'checked' : '' }}>
        <span><strong>Buksan sa mga Sales Agent</strong> — kapag ON, makikita at ma-a-access na nila ang Team Board.</span>
      </label>
      <div style="margin-top:.4rem;font-size:.85rem;color:{{ $settings->agents_can_view ? '#065f46' : '#b91c1c' }};font-weight:700;">
        Status ngayon: {{ $settings->agents_can_view ? '🟢 BUKAS — visible na sa agents' : '🔴 SARADO — ikaw (admin) lang ang may access' }}
      </div>
      <div style="margin-top:.8rem;"><button class="tb-btn" type="submit">💾 I-save ang Access</button></div>
    </form>
  </div>

  {{-- Grouping --}}
  <div class="tb-panel">
    <h2>👥 Grouping — Sino ang magkakasama?</h2>
    <div class="lead" style="margin-top:-.4rem;margin-bottom:.9rem;">Itakda kung saang section lalabas ang bawat agent. Hal. ilagay sa <strong>Sales Agent</strong> o <strong>Agent</strong>. Kapag blangko, awtomatikong base sa position nila. I-uncheck ang <strong>Show</strong> para itago sandali sa board.</div>
    <form method="POST" action="{{ route('sales.team.board.groups.save') }}">
      @csrf
      <table class="tb-table">
        <thead><tr><th>Agent</th><th>Position</th><th>Group</th><th style="text-align:center;">Show</th></tr></thead>
        <tbody>
          @foreach($agents as $a)
          @php
            $mg = $memberGroups->get($a->id);
            $current = $mg && $mg->group_name ? $mg->group_name : '';
            $isVisible = $mg ? (bool) $mg->visible : true;
            $suggested = \App\Models\SalesBoardMemberGroup::suggestGroup($a->position);
          @endphp
          <tr>
            <td style="font-weight:700;">{{ $a->name }}</td>
            <td style="color:#64748b;">{{ $a->position ?: '—' }}</td>
            <td><input type="text" list="tb-group-names" name="groups[{{ $a->id }}]" value="{{ $current }}" placeholder="{{ $suggested }}" style="width:180px;padding:.4rem .6rem;border:1px solid #d1d5db;border-radius:8px;"></td>
            <td style="text-align:center;"><input type="checkbox" name="visible[{{ $a->id }}]" value="1" {{ $isVisible ? 'checked' : '' }} style="width:18px;height:18px;"></td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <datalist id="tb-group-names">
        <option value="Sales Agent"></option>
        <option value="Agent"></option>
      </datalist>
      <div style="margin-top:1rem;"><button class="tb-btn" type="submit">💾 I-save ang Grouping</button></div>
    </form>
  </div>

  {{-- Team total quota --}}
  <div class="tb-panel" style="border:2px solid #a7f3d0;background:#f0fdf9;">
    <h2>🎯 Team Total Quota — {{ $period['label'] }}</h2>
    <div class="lead" style="margin-top:-.4rem;margin-bottom:.9rem;">Kabuuang target ng buong team para sa buwan. Ito ang lalabas sa taas ng Team Board (“total over quota”).</div>
    <form method="POST" action="{{ route('sales.team.board.team-quota.save') }}">
      @csrf
      <div class="tb-field" style="max-width:280px;">
        <label>Team Quota (₱) para sa {{ $period['label'] }}</label>
        <input type="number" step="1" min="0" name="team_quota" value="{{ $teamQuota && $teamQuota->quota_amount !== null ? (int) $teamQuota->quota_amount : '' }}" placeholder="0" style="font-weight:800;text-align:right;">
      </div>
      <div style="margin-top:.4rem;"><button class="tb-btn" type="submit">💾 I-save ang Team Quota</button></div>
    </form>
  </div>

  {{-- Quotas --}}
  <div class="tb-panel">
    <h2>🎯 Quota ng bawat Agent — {{ $period['label'] }}</h2>
    <form method="POST" action="{{ route('sales.team.board.quotas.save') }}">
      @csrf
      <table class="tb-table">
        <thead><tr><th>Agent</th><th>Position</th><th style="text-align:right;">Quota (₱)</th></tr></thead>
        <tbody>
          @foreach($agents as $a)
          @php $q = $quotas[$a->id]->quota_amount ?? ''; @endphp
          <tr>
            <td style="font-weight:700;">{{ $a->name }}</td>
            <td style="color:#64748b;">{{ $a->position ?: '—' }}</td>
            <td style="text-align:right;">
              <input type="number" step="1" min="0" name="quotas[{{ $a->id }}]" value="{{ $q !== '' ? (int) $q : '' }}" placeholder="0">
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div style="margin-top:1rem;"><button class="tb-btn" type="submit">💾 I-save ang Quotas</button></div>
    </form>
  </div>

  {{-- Settings --}}
  <div class="tb-panel">
    <h2>⚙️ Ano ang makikita ng Agents</h2>
    <form method="POST" action="{{ route('sales.team.board.settings.save') }}">
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="tb-field">
          <label>Board Title</label>
          <input type="text" name="board_title" value="{{ $settings->board_title }}" maxlength="80">
        </div>
        <div class="tb-field">
          <label>Tagline (inspirational)</label>
          <input type="text" name="tagline" value="{{ $settings->tagline }}" maxlength="200" placeholder="e.g. Bawat benta, tagumpay ng lahat!">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 1.5rem;">
        @php
          $toggles = [
            'show_own_card' => 'Ipakita ang sariling quota card',
            'show_team_quotas' => 'Ipakita ang quota ng buong team',
            'show_leaderboard' => 'Ipakita ang leaderboard',
            'show_podium' => 'Ipakita ang podium (Top 3)',
            'show_badges' => 'Ipakita ang badges (champion, top mover, streak)',
            'show_champion' => 'Ipakita ang monthly champion banner',
            'show_confetti' => 'Confetti kapag naka-hit ng 100%',
            'show_team_total' => 'Ipakita ang team total (buong buwan) sa taas',
            'show_rank' => 'Ipakita ang rank (#1, #2...)',
            'show_amounts' => 'Ipakita ang exact ₱ ng iba',
            'show_own_amount' => 'Ipakita ang sariling exact ₱',
            'show_percent' => 'Ipakita ang progress %',
            'show_announcements' => 'Ipakita ang announcements',
          ];
        @endphp
        @foreach($toggles as $key => $label)
        <label class="tb-toggle">
          <input type="checkbox" name="{{ $key }}" value="1" {{ $settings->$key ? 'checked' : '' }}>
          <span>{{ $label }}</span>
        </label>
        @endforeach
      </div>
      <div style="margin-top:1rem;"><button class="tb-btn" type="submit">💾 I-save ang Settings</button></div>
      <input type="hidden" name="agents_can_view" value="{{ $settings->agents_can_view ? '1' : '' }}">
    </form>
  </div>

  {{-- Announcements --}}
  <div class="tb-panel">
    <h2>📣 Announcements</h2>
    <form method="POST" action="{{ route('sales.team.board.announcement.post') }}" enctype="multipart/form-data" style="margin-bottom:1.25rem;">
      @csrf
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;">
        <div class="tb-field">
          <label>Title</label>
          <input type="text" name="title" maxlength="200" required placeholder="e.g. New promo this week!">
        </div>
        <div class="tb-field">
          <label>Type</label>
          <select name="type" style="width:100%;padding:.5rem .7rem;border:1px solid #d1d5db;border-radius:8px;">
            <option value="news">📰 News</option>
            <option value="motivation">💡 Motivation</option>
            <option value="alert">⚠️ Alert</option>
          </select>
        </div>
      </div>
      <div class="tb-field">
        <label>Body (optional)</label>
        <textarea name="body" rows="2" maxlength="2000" placeholder="Detalye..."></textarea>
      </div>
      <div class="tb-field">
        <label>Pictures (optional) — hanggang 5, max 5MB each</label>
        <input type="file" name="images[]" id="tb-ann-images" accept="image/*" multiple>
        <div id="tb-ann-preview" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem;"></div>
      </div>
      <label class="tb-toggle" style="border:0;"><input type="checkbox" name="pinned" value="1"><span>📌 I-pin sa taas</span></label>
      <div style="margin-top:.6rem;"><button class="tb-btn" type="submit">📣 I-post</button></div>
    </form>

    @forelse($announcements as $a)
      <div class="tb-annrow {{ $a->active ? '' : 'off' }}">
        <div style="min-width:0;">
          <div class="t">
            {{ $a->title }}
            <span class="tb-pill">{{ $a->type }}</span>
            @if($a->pinned)<span class="tb-pill pin">📌 Pinned</span>@endif
            @if(!$a->active)<span class="tb-pill off">Hidden</span>@endif
          </div>
          @if($a->body)<div class="b">{{ $a->body }}</div>@endif
          @if(!empty($a->images))
            <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.5rem;">
              @foreach($a->images as $img)
                <a href="{{ \Illuminate\Support\Facades\Storage::url($img) }}" target="_blank">
                  <img src="{{ \Illuminate\Support\Facades\Storage::url($img) }}" alt="announcement image" style="height:64px;width:64px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;">
                </a>
              @endforeach
            </div>
          @endif
          <div class="meta">Posted {{ optional($a->created_at)->diffForHumans() }}</div>
        </div>
        <div style="display:flex;gap:.4rem;flex-wrap:wrap;justify-content:flex-end;">
          <form method="POST" action="{{ route('sales.team.board.announcement.pin', $a->id) }}">@csrf<button class="tb-btn gray sm" type="submit">{{ $a->pinned ? 'Unpin' : 'Pin' }}</button></form>
          <form method="POST" action="{{ route('sales.team.board.announcement.toggle', $a->id) }}">@csrf<button class="tb-btn gray sm" type="submit">{{ $a->active ? 'Hide' : 'Show' }}</button></form>
          <form method="POST" action="{{ route('sales.team.board.announcement.delete', $a->id) }}" onsubmit="return confirm('Delete this announcement?');">@csrf @method('DELETE')<button class="tb-btn red sm" type="submit">Delete</button></form>
        </div>
      </div>
    @empty
      <div style="color:#94a3b8;font-style:italic;">Wala pang announcement.</div>
    @endforelse
  </div>

  <div style="margin-bottom:2rem;"><a href="{{ route('sales.team.board') }}" class="tb-btn gray">👀 Tingnan ang Team Board</a></div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  var input = document.getElementById('tb-ann-images');
  var prev = document.getElementById('tb-ann-preview');
  if(!input || !prev) return;
  input.addEventListener('change', function(){
    prev.innerHTML = '';
    var files = Array.prototype.slice.call(input.files || []).slice(0,5);
    if((input.files||[]).length > 5){
      alert('Hanggang 5 pictures lang. Ang unang 5 ang isasama.');
    }
    files.forEach(function(f){
      if(!f.type || f.type.indexOf('image/')!==0) return;
      var img = document.createElement('img');
      img.src = URL.createObjectURL(f);
      img.style.cssText = 'height:64px;width:64px;object-fit:cover;border-radius:8px;border:1px solid #cbd5e1;';
      prev.appendChild(img);
    });
  });
})();
</script>
@endpush
