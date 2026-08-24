<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageReportComment extends Model
{
    protected $table = 'damage_report_comments';

    protected $fillable = [
        'damage_report_id',
        'user_id',
        'comment',
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
