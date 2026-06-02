<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SublimationPrice extends Model
{
    protected $fillable = [
        'name', 'category', 'tab_group', 'price', 'agent_price', 'order', 'active', 'master_item_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'agent_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function masterItem()
    {
        return $this->belongsTo(\App\Models\MasterItem::class, 'master_item_id');
    }

    public function productPricings()
    {
        return $this->hasManyThrough(
            \App\Models\ProductPricing::class,
            \App\Models\MasterItem::class,
            'id',
            'master_item_id',
            'master_item_id',
            'id'
        );
    }

    public function scopeActive($q) { return $q->where('active', true); }
    public function scopeCategory($q, $cat) { return $q->where('category', $cat); }
    public function scopeTabGroup($q, $tab) { return $q->where('tab_group', $tab); }
}
