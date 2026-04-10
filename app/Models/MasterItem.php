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
        // NOTE: Database only has above columns
        // Other fields (size, color, brand, etc.) are stored in description field
        // Do NOT add fields that don't exist in database table!
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
