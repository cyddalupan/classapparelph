<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Announcement / news shown on the Team Board (Andrew 2026-10-15).
 */
class SalesAnnouncement extends Model
{
    protected $fillable = [
        'title', 'body', 'images', 'type', 'pinned', 'active',
        'publish_at', 'expires_at', 'created_by',
    ];

    protected $casts = [
        'images' => 'array',
        'pinned' => 'boolean',
        'active' => 'boolean',
        'publish_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
