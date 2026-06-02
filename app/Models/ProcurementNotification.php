<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementNotification extends Model
{
    protected $fillable = [
        'procurement_order_id',
        'from_user_id',
        'to_user_id',
        'type',
        'title',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(ProcurementOrder::class, 'procurement_order_id');
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
