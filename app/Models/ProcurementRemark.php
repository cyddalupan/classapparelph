<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementRemark extends Model
{
    protected $fillable = [
        'procurement_order_id',
        'user_id',
        'remark',
        'type',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(ProcurementOrder::class, 'procurement_order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
