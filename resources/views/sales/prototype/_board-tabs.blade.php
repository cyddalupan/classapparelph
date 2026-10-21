{{-- Board Member tabs: Sales Overview | Damage Report | Layout Job List (hiwalay na pages, parehong access gate). --}}
@php
    $tabBase = 'text-decoration:none;font-size:12px;font-weight:700;padding:7px 16px;border-radius:8px;';
    $tabOn = $tabBase . 'background:#6d28d9;color:#fff;';
    $tabOff = $tabBase . 'background:transparent;color:#6d28d9;';
@endphp
<div style="display:inline-flex;flex-wrap:wrap;background:#f3e8ff;border:1px solid #e9d5ff;border-radius:10px;padding:4px;gap:4px;margin-bottom:16px;">
    <a href="{{ route('sales.prototype.board') }}"
       style="{{ request()->routeIs('sales.prototype.board') ? $tabOn : $tabOff }}">
        <i class="fas fa-chart-line"></i> Sales Overview
    </a>
    <a href="{{ route('sales.prototype.board.damage') }}"
       style="{{ request()->routeIs('sales.prototype.board.damage*') ? $tabOn : $tabOff }}">
        <i class="fas fa-exclamation-triangle"></i> Damage Report
    </a>
    <a href="{{ route('sales.prototype.board.layout-jobs') }}"
       style="{{ request()->routeIs('sales.prototype.board.layout-jobs*') ? $tabOn : $tabOff }}">
        <i class="fas fa-palette"></i> Layout Job List
    </a>
    <a href="{{ route('sales.prototype.board.special-price') }}"
       style="{{ request()->routeIs('sales.prototype.board.special-price*') ? $tabOn : $tabOff }}">
        <i class="fas fa-tags"></i> Special Price
    </a>
    <a href="{{ route('sales.prototype.board.close-out-review') }}"
       style="{{ request()->routeIs('sales.prototype.board.close-out-review*') ? $tabOn : $tabOff }}">
        <i class="fas fa-clipboard-check"></i> Close Out Review
    </a>
</div>
