<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryActivityLog extends Model
{
    protected $fillable = [
        'master_item_id',
        'department_id',
        'action_type',
        'item_name',
        'sku',
        'category',
        'old_value',
        'new_value',
        'quantity',
        'user_id',
        'notes',
    ];

    public function masterItem()
    {
        return $this->belongsTo(MasterItem::class);
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\SalesDepartment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
