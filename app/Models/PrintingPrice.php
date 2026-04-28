<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintingPrice extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'price', 'agent_price', 'order', 'active', 'print_type', 'master_item_id'];
    
    protected $casts = [
        'price' => 'decimal:2',
        'agent_price' => 'decimal:2',
        'active' => 'boolean'
    ];
    
    public function masterItem()
    {
        return $this->belongsTo(MasterItem::class, 'master_item_id');
    }
    
    /**
     * Get the sales team price from linked product pricing
     */
    public function getLinkedSalesTeamPrice()
    {
        if (!$this->master_item_id) return $this->price;
        $pp = \App\Models\ProductPricing::where('master_item_id', $this->master_item_id)
            ->where('price_tier', 'sales_team')
            ->where('is_active', true)
            ->first();
        return $pp ? ($pp->final_price ?? $this->price) : $this->price;
    }
    
    /**
     * Get the agent price from linked product pricing
     */
    public function getLinkedAgentPrice()
    {
        if (!$this->master_item_id) return $this->agent_price;
        $pp = \App\Models\ProductPricing::where('master_item_id', $this->master_item_id)
            ->where('price_tier', 'agent_cost')
            ->where('is_active', true)
            ->first();
        return $pp ? ($pp->final_price ?? $pp->base_price ?? $this->agent_price) : $this->agent_price;
    }
    
    /**
     * Get the supplier cost from linked product pricing
     */
    public function getLinkedSupplierCost()
    {
        if (!$this->master_item_id) return null;
        $pp = \App\Models\ProductPricing::where('master_item_id', $this->master_item_id)
            ->where('price_tier', 'supplier_cost')
            ->where('is_active', true)
            ->first();
        return $pp ? ($pp->final_price ?? $pp->base_price) : null;
    }
    
    /**
     * Combo discounts where this size is size1
     */
    public function comboDiscountsAsSize1()
    {
        return $this->hasMany(PrintingComboDiscount::class, 'size1_id');
    }
    
    /**
     * Combo discounts where this size is size2
     */
    public function comboDiscountsAsSize2()
    {
        return $this->hasMany(PrintingComboDiscount::class, 'size2_id');
    }
    
    /**
     * Size upgrades where this is the "from" size
     */
    public function sizeUpgradesFrom()
    {
        return $this->hasMany(PrintingSizeUpgrade::class, 'from_size_id');
    }
    
    /**
     * Size upgrades where this is the "to" size
     */
    public function sizeUpgradesTo()
    {
        return $this->hasMany(PrintingSizeUpgrade::class, 'to_size_id');
    }
}