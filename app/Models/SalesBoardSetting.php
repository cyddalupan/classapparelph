<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Global Team Board controls (single row, id=1) — Andrew 2026-10-15.
 * Pinipili kung ano ang makikita ng mga agent sa Team Board.
 */
class SalesBoardSetting extends Model
{
    protected $fillable = [
        'board_title', 'tagline', 'agents_can_view',
        'show_team_quotas', 'show_leaderboard', 'show_announcements',
        'show_amounts', 'show_own_amount', 'show_rank', 'show_percent',
        'show_own_card', 'show_podium', 'show_badges', 'show_champion', 'show_confetti', 'show_team_total',
        'updated_by',
    ];

    protected $casts = [
        'agents_can_view' => 'boolean',
        'show_team_total' => 'boolean',
        'show_podium' => 'boolean',
        'show_badges' => 'boolean',
        'show_champion' => 'boolean',
        'show_confetti' => 'boolean',
        'show_team_quotas' => 'boolean',
        'show_leaderboard' => 'boolean',
        'show_announcements' => 'boolean',
        'show_amounts' => 'boolean',
        'show_own_amount' => 'boolean',
        'show_rank' => 'boolean',
        'show_percent' => 'boolean',
        'show_own_card' => 'boolean',
    ];

    /**
     * Always return the single settings row, creating defaults if missing.
     */
    /** Memoized para isang query lang bawat request (sidebar + board + settings). */
    protected static ?self $memo = null;

    public static function current(): self
    {
        return static::$memo ??= static::query()->firstOrCreate(['id' => 1], []);
    }

    /** Burahin ang memo kapag na-save (para hindi stale sa loob ng parehong request). */
    public static function forgetMemo(): void
    {
        static::$memo = null;
    }
}
