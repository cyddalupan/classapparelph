<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Audit trail entry para sa layout jobs at payouts (Andrew 2026-10-21).
 */
class LayoutJobAudit extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'layout_job_audits';

    protected $fillable = [
        'subject_type', 'subject_id', 'job_no', 'event',
        'changes', 'meta', 'user_id', 'user_name', 'ip', 'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'meta'    => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subject()
    {
        return $this->morphTo(__FUNCTION__, 'subject_type', 'subject_id');
    }

    /** Label ng event para sa UI */
    public function eventLabel(): string
    {
        return [
            'created'   => '🆕 Ginawa',
            'updated'   => '✏️ Binago',
            'deleted'   => '🗑️ Binura',
            'paid'      => '💸 Bayad',
            'verified'  => '✅ Verified',
            'rejected'  => '✗ Rejected',
            'confirmed' => '📥 Natanggap',
            'requested' => '📤 Request',
        ][$this->event] ?? $this->event;
    }
}
