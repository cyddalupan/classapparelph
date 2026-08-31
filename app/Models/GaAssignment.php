<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaAssignment extends Model
{
    protected $table = 'ga_assignments';

    protected $fillable = [
        'prototype_sale_id',
        'stage',
        'user_id',
        'assigned_by',
        'assigned_at',
        'completed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sale()
    {
        return $this->belongsTo(PrototypeSale::class, 'prototype_sale_id');
    }
}
