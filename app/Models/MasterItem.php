<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterItem extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'category',
        'description',
        'unit_price',
        'sku',
        'barcode',
        'created_by',
        // Shirt Products fields
        'size',
        'color',
        'material',
        'brand',
        // Machine & Equipment fields
        'model',
        'serial_number',
        'warranty_period',
        'power_requirement',
        // Garment Materials fields
        'fabric_type',
        'weight',
        'width',
        'color_fastness',
        // Printing & Office Supplies fields
        'paper_type',
        'paper_size',
        'ink_type',
        'yield',
    ];
    
    protected $casts = [
        'unit_price' => 'decimal:2'
    ];
    
    /**
     * Get the user who created this master item
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the inventory items linked to this master item
     */
    public function inventoryItems()
    {
        return $this->hasMany(Inventory::class, 'master_item_id');
    }
}
