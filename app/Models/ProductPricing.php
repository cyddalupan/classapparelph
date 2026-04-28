<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPricing extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'master_item_id',
        'price_tier',
        'base_price',
        'markup_percentage',
        'markup_amount',
        'final_price',
        'created_by',
        'updated_by',
        'is_active',
        'notes',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'base_price' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'markup_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the master item that owns the pricing.
     */
    public function masterItem(): BelongsTo
    {
        return $this->belongsTo(MasterItem::class, 'master_item_id');
    }
    
    /**
     * Get the user who created this pricing.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the user who last updated this pricing.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    /**
     * Calculate markup amount and final price.
     */
    public function calculatePrices(): void
    {
        if ($this->base_price && $this->markup_percentage) {
            $this->markup_amount = $this->base_price * ($this->markup_percentage / 100);
            $this->final_price = $this->base_price + $this->markup_amount;
        } elseif ($this->base_price) {
            // If no markup, final price = base price
            $this->final_price = $this->base_price;
            $this->markup_amount = 0;
        }
    }
    
    /**
     * Scope for active pricings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope for specific price tier.
     */
    public function scopeTier($query, $tier)
    {
        return $query->where('price_tier', $tier);
    }
}
