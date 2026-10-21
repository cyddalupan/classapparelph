{{--
    Layout Job / Layout Job List nav item.
    Andrew 2026-10-21: dating naka-lock sa admin (greyed out) — binuksan na ulit
    para sa lahat ng user.

    Params:
      $ljRouteName (string) e.g. 'sales.layout-jobs' | 'sales.layout-jobs.all'
      $ljRouteIs   (string) routeIs pattern e.g. 'sales.layout-jobs*'
      $ljLabel     (string) 'Layout Job' | 'Layout Job List'
      $ljPayout    (int|null) optional payout badge count
--}}
@php
    $ljPayout = $ljPayout ?? 0;
@endphp
<a href="{{ route($ljRouteName) }}" class="nav-item {{ request()->routeIs($ljRouteIs) ? 'active' : '' }}">
    <i class="fas fa-palette"></i>
    <span class="nav-text">{{ $ljLabel }}</span>
    @if($ljPayout > 0)<span style="margin-left:auto;font-size:10px;font-weight:700;background:#c026d3;color:#fff;padding:1px 7px;border-radius:10px;">{{ $ljPayout }}</span>@endif
</a>
