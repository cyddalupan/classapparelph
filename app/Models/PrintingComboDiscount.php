<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintingComboDiscount extends Model
{
    use HasFactory;
    
    protected $fillable = ['size1_id', 'size2_id', 'discount_type', 'discount_value', 'active'];
    
    protected $casts = [
        'discount_value' => 'decimal:2',
        'active' => 'boolean'
    ];
    
    /**
     * First size in the combo
     */
    public function size1()
    {
        return $this->belongsTo(PrintingPrice::class, 'size1_id');
    }
    
    /**
     * Second size in the combo
     */
    public function size2()
    {
        return $this->belongsTo(PrintingPrice::class, 'size2_id');
    }
}