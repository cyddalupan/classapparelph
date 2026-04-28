<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'master_item_id',
        'size',
        'brand',
        'color',
        'material',
        'sku',
        'barcode',
        'unit_price',
        'stock_quantity',
        'is_active',
    ];
    
    /**
     * Get the parent product.
     */
    public function masterItem()
    {
        return $this->belongsTo(MasterItem::class);
    }
    
    /**
     * Get the pricing tiers for this variant.
     */
    public function productPricings()
    {
        return $this->hasMany(ProductPricing::class, 'master_item_id', 'master_item_id');
    }
    
    /**
     * Get the volume discounts for this variant.
     */
    public function volumeDiscounts()
    {
        return $this->hasMany(VolumeDiscount::class, 'master_item_id', 'master_item_id');
    }
    
    /**
     * Scope to get active variants.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Get the display name for this variant.
     */
    public function getDisplayNameAttribute()
    {
        $parts = [];
        if ($this->size) $parts[] = $this->size;
        if ($this->brand) $parts[] = $this->brand;
        if ($this->color) $parts[] = $this->color;
        if ($this->material) $parts[] = $this->material;
        
        return $this->masterItem->name . ' - ' . implode(' - ', $parts);
    }
}
