<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrototypeOrder extends Model
{
    protected $table = 'prototype_orders';

    protected $fillable = [
        'prototype_sale_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_price',
        'print_size',
        'mockup_image',
        'department',
        'status',
    ];

    public function sale()
    {
        return $this->belongsTo(PrototypeSale::class, 'prototype_sale_id');
    }
}
