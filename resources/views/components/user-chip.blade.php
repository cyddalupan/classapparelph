{{-- User chip: profile picture + pangalan (first name lang). Reusable kahit saan may user name. Usage: <x-user-chip :user="$u" :size="18" /> o <x-user-chip name="Juan Dela Cruz" />. Walang avatar -> initials. --}}
@props([
    'user' => null,
    'name' => null,
    'size' => 20,
    'showName' => true,
])

@php
    $first = null;
    if ($user) {
        $first = trim((string) ($user->first_name ?? ''));
        if ($first === '') {
            $first = trim(explode(' ', trim((string) $user->name))[0]);
        }
    } elseif ($name !== null && trim((string) $name) !== '') {
        $first = trim(explode(' ', trim((string) $name))[0]);
    }
    $avatar = $user ? ($user->avatar_url ?? null) : null;
    $initial = ($first !== null && $first !== '') ? mb_strtoupper(mb_substr($first, 0, 1)) : null;
    $size = max(12, (int) $size);
    $fontSize = max(8, (int) round($size * 0.45));
@endphp

@if($initial !== null)
<span class="user-chip" style="display:inline-flex;align-items:center;gap:4px;vertical-align:middle;line-height:1;">
    <span class="user-chip-av" style="width:{{ $size }}px;height:{{ $size }}px;border-radius:50%;overflow:hidden;display:inline-flex;align-items:center;justify-content:center;background:#e9ecef;color:#6c757d;flex:0 0 auto;border:1px solid #dee2e6;">
        @if($avatar)
            <img src="{{ $avatar }}" alt="{{ $first }}" loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block;">
        @else
            <span style="font-size:{{ $fontSize }}px;font-weight:600;">{{ $initial }}</span>
        @endif
    </span>
    @if($showName)
    <span class="user-chip-name">{{ $first }}</span>
    @endif
</span>
@endif
