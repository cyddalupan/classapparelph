<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Sales Agent quota target for a period (Team Board, Andrew 2026-10-15).
 */
class SalesQuota extends Model
{
    protected $fillable = [
        'user_id', 'period_type', 'period_start', 'period_end',
        'quota_amount', 'note', 'active', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'quota_amount' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
