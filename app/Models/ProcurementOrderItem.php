<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementOrderItem extends Model
{
    protected $fillable = [
        'procurement_order_id',
        'master_item_id',
        'item_name',
        'sku',
        'quantity_ordered',
        'quantity_received',
        'unit_price',
        'status',
        'notes',
    ];

    public function order()
    {
        return $this->belongsTo(ProcurementOrder::class, 'procurement_order_id');
    }

    public function masterItem()
    {
        return $this->belongsTo(MasterItem::class, 'master_item_id');
    }
}
