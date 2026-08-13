<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionFeedback extends Model
{
    protected $fillable = [
        'sale_id',
        'from_user_id',
        'to_user_id',
        'category',
        'message',
        'status',
        'acknowledged_at',
        'resolved_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public const CATEGORIES = [
        'missing_file' => 'Missing File',
        'wrong_file_sent' => 'Wrong File Sent',
        'no_response' => 'No Response',
        'incomplete_info' => 'Incomplete Info',
        'production_error' => 'Production Error',
        'other' => 'Other',
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
