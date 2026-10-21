<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Team-wide (buong-buwan) quota — Andrew 2026-10-15.
 */
class SalesTeamQuota extends Model
{
    protected $fillable = ['period_type', 'period_start', 'period_end', 'quota_amount', 'updated_by'];

    protected $casts = [
        'quota_amount' => 'float',
    ];

    public static function forPeriod(string $periodStart, string $periodType = 'monthly'): ?self
    {
        return static::query()
            ->where('period_type', $periodType)
            ->whereDate('period_start', $periodStart)
            ->first();
    }
}
