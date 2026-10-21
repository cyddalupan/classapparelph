{{--
    Layout Job / Layout Job List nav item (temporary lock).
    Andrew 2026-10-21: "i-grey out sa kanilang lahat yung Layout Job List at Layout Job,
    ako muna may access" — only admin gets the working link; everyone else sees a
    greyed-out "Locked" item. Server-side gate: routes group middleware('admin').

    Params:
      $ljRouteName (string) e.g. 'sales.layout-jobs' | 'sales.layout-jobs.all'
      $ljRouteIs   (string) routeIs pattern e.g. 'sales.layout-jobs*'
      $ljLabel     (string) 'Layout Job' | 'Layout Job List'
      $ljPayout    (int|null) optional payout badge count
--}}
@php
    $ljPayout = $ljPayout ?? 0;
    $ljIsAdmin = auth()->check() && auth()->user()->isAdmin();
@endphp
@if($ljIsAdmin)
<a href="{{ route($ljRouteName) }}" class="nav-item {{ request()->routeIs($ljRouteIs) ? 'active' : '' }}">
    <i class="fas fa-palette"></i>
    <span class="nav-text">{{ $ljLabel }}</span>
    @if($ljPayout > 0)<span style="margin-left:auto;font-size:10px;font-weight:700;background:#c026d3;color:#fff;padding:1px 7px;border-radius:10px;">{{ $ljPayout }}</span>@endif
</a>
@else
<span class="nav-item nav-disabled" title="Temporarily locked — admin is still working on this" style="cursor:not-allowed;opacity:.45;">
    <i class="fas fa-palette"></i>
    <span class="nav-text">{{ $ljLabel }}</span>
    <span style="margin-left:auto;font-size:9px;font-weight:600;background:#334155;color:#94a3b8;padding:1px 6px;border-radius:8px;letter-spacing:0.5px;">Locked</span>
</span>
@endif
