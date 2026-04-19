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
        'sales_box',
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
    
    /**
     * Get the product pricings for this master item
     */
    public function productPricings()
    {
        return $this->hasMany(ProductPricing::class, 'master_item_id');
    }
    
    /**
     * Get active pricing for a specific tier
     */
    public function getPricingForTier($tier)
    {
        return $this->productPricings()
                    ->where('price_tier', $tier)
                    ->where('is_active', true)
                    ->first();
    }
    
    /**
     * Get supplier cost (your cost)
     */
    public function getSupplierCostAttribute()
    {
        $pricing = $this->getPricingForTier('supplier_cost');
        return $pricing ? $pricing->final_price : $this->unit_price;
    }
    
    /**
     * Get sales team price
     */
    public function getSalesTeamPriceAttribute()
    {
        $pricing = $this->getPricingForTier('sales_team');
        return $pricing ? $pricing->final_price : null;
    }
    
    /**
     * Get agent cost (base for agents to add markup)
     */
    public function getAgentCostAttribute()
    {
        $pricing = $this->getPricingForTier('agent_cost');
        return $pricing ? $pricing->base_price : null;
    }

    /**
     * Get the volume discounts for this master item.
     */
    public function volumeDiscounts()
    {
        return $this->hasMany(VolumeDiscount::class);
    }

    /**
     * Get active volume discounts for this master item.
     */
    public function activeVolumeDiscounts()
    {
        return $this->volumeDiscounts()->active()->orderBy('min_quantity');
    }

    /**
     * Get the price for a specific quantity.
     */
    public function getPriceForQuantity($quantity)
    {
        $discount = $this->volumeDiscounts()
            ->active()
            ->forQuantity($quantity)
            ->first();
        
        if ($discount) {
            return $discount->price_per_unit;
        }
        
        // Fall back to base price from product pricing or unit_price
        return $this->sales_team_price ?? $this->unit_price;
    }

    /**
     * Get volume discount tiers display.
     */
    public function getVolumeDiscountsDisplayAttribute()
    {
        $discounts = $this->activeVolumeDiscounts()->get();
        
        if ($discounts->isEmpty()) {
            return null;
        }
        
        $display = [];
        foreach ($discounts as $discount) {
            $display[] = "{$discount->range_display}: {$discount->price_display}";
        }
        
        return implode(', ', $display);
    }
}
