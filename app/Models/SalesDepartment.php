<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesDepartment extends Model
{
    protected $table = 'sales_departments';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'manager_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function inventoryItems()
    {
        return $this->belongsToMany(MasterItem::class, 'department_master_items', 'department_id', 'master_item_id')
            ->withPivot(['current_stock', 'minimum_stock', 'last_restocked_at']);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function procurementOrders()
    {
        return $this->hasMany(ProcurementOrder::class, 'department_id');
    }
}
