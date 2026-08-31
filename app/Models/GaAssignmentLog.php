<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaAssignmentLog extends Model
{
    protected $table = 'ga_assignment_logs';

    protected $fillable = [
        'prototype_sale_id',
        'stage',
        'user_id',
        'action',
        'actor_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function sale()
    {
        return $this->belongsTo(PrototypeSale::class, 'prototype_sale_id');
    }
}
