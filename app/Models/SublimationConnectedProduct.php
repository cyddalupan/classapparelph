<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SublimationConnectedProduct extends Model
{
    protected $fillable = ['master_item_id'];

    protected $table = 'sublimation_connected_products';

    public function masterItem()
    {
        return $this->belongsTo(MasterItem::class);
    }

    public function productPricings()
    {
        return $this->hasManyThrough(
            ProductPricing::class,
            MasterItem::class,
            'id',            // master_items.id
            'master_item_id', // product_pricings.master_item_id
            'master_item_id', // sublimation_connected_products.master_item_id
            'id'             // master_items.id
        )->where('product_pricings.is_active', true);
    }
}
