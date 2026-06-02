<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionChecklist extends Model
{
    protected $fillable = [
        'sale_id',
        'items',
        'ga_done',
        'ga_done_at',
        'ga_notes',
        'qa1_done',
        'qa1_done_at',
        'qa1_notes',
        'press_done',
        'press_done_at',
        'qa2_done',
        'qa2_done_at',
        'qa2_notes',
    ];

    protected $casts = [
        'items' => 'array',
        'ga_done' => 'boolean',
        'qa1_done' => 'boolean',
        'press_done' => 'boolean',
        'qa2_done' => 'boolean',
        'ga_done_at' => 'datetime',
        'qa1_done_at' => 'datetime',
        'press_done_at' => 'datetime',
        'qa2_done_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(\App\Models\PrototypeSale::class, 'sale_id');
    }
}
