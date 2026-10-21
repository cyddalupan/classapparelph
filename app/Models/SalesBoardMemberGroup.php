<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-agent group assignment sa Team Board (Andrew 2026-10-15).
 */
class SalesBoardMemberGroup extends Model
{
    protected $fillable = ['user_id', 'group_name', 'visible', 'sort_order'];

    protected $casts = [
        'visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Auto-suggested group base sa position, kung wala pang manual assignment.
     * "Sales Agent" at "Agent" ay magkaibang grupo.
     */
    public static function suggestGroup(?string $position): string
    {
        $p = strtoupper(trim((string) $position));

        if ($p === '') {
            return 'Ungrouped';
        }
        if (str_contains($p, 'SALES AGENT')) {
            return 'Sales Agent';
        }
        if (str_contains($p, 'AGENT')) {
            return 'Agent';
        }

        return 'Ungrouped';
    }
}
