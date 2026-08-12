<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleNotification extends Model
{
    protected $fillable = [
        'sale_id',
        'from_user_id',
        'to_user_id',
        'type',
        'is_urgent',
        'reminder_count',
        'title',
        'message',
        'response',
        'responded_at',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_urgent' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(PrototypeSale::class, 'sale_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
