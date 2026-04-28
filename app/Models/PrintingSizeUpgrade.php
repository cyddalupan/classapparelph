<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintingSizeUpgrade extends Model
{
    use HasFactory;
    
    protected $fillable = ['from_size_id', 'from_quantity', 'to_size_id', 'auto_apply', 'active'];
    
    protected $casts = [
        'auto_apply' => 'boolean',
        'active' => 'boolean'
    ];
    
    /**
     * Original size to upgrade from
     */
    public function fromSize()
    {
        return $this->belongsTo(PrintingPrice::class, 'from_size_id');
    }
    
    /**
     * Upgraded size
     */
    public function toSize()
    {
        return $this->belongsTo(PrintingPrice::class, 'to_size_id');
    }
}