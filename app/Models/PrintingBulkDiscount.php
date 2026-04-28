<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintingBulkDiscount extends Model
{
    use HasFactory;
    
    protected $fillable = ['min_garments', 'max_garments', 'min_transactions', 'max_transactions', 'discount_percent', 'discount_type', 'discount_amount', 'count_combo_as_one', 'active', 'price_tier', 'print_type'];
    
    protected $casts = [
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'count_combo_as_one' => 'boolean',
        'active' => 'boolean'
    ];
}