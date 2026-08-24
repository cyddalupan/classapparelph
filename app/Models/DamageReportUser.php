<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageReportUser extends Model
{
    protected $table = 'damage_report_users';

    protected $fillable = [
        'damage_report_id',
        'user_id',
        'amount_share',
        'acknowledge_status',
        'reply',
        'acknowledged_at',
    ];

    protected $casts = [
        'amount_share' => 'float',
        'acknowledged_at' => 'datetime',
    ];

    public const ACK_STATUSES = [
        'pending' => 'Pending',
        'acknowledged' => 'Acknowledged',
        'contested' => 'Contested',
    ];

    public function report()
    {
        return $this->belongsTo(DamageReport::class, 'damage_report_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
