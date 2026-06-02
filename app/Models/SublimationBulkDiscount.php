<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SublimationBulkDiscount extends Model
{
    protected $fillable = [
        'name', 'min_qty', 'max_qty', 'discount_percent', 'active'
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'active' => 'boolean',
    ];
}
