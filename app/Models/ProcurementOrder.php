<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementOrder extends Model
{
    protected $fillable = [
        'order_number',
        'department_id',
        'supplier_id',
        'created_by',
        'status',
        'notes',
        'procurement_notes',
        'submitted_at',
        'ordered_at',
        'expected_delivery_at',
        'delivered_at',
        'received_at',
        'received_by',
        'verified_at',
        'verified_by',
        'completed_at',
        'procurement_user_id',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'ordered_at' => 'datetime',
        'expected_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'received_at' => 'datetime',
        'verified_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(ProcurementOrderItem::class);
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\SalesDepartment::class, 'department_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function procurementUser()
    {
        return $this->belongsTo(User::class, 'procurement_user_id');
    }

    public function remarks()
    {
        return $this->hasMany(ProcurementRemark::class, 'procurement_order_id');
    }

    public function notifications()
    {
        return $this->hasMany(ProcurementNotification::class, 'procurement_order_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function unreadNotificationsFor($userId)
    {
        return $this->notifications()->where('to_user_id', $userId)->where('is_read', false);
    }

    public static function generateOrderNumber()
    {
        $prefix = 'PO-' . now()->format('Ymd');
        $last = self::where('order_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        $next = $last ? intval(substr($last->order_number, -3)) + 1 : 1;
        return $prefix . '-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function statusLabel()
    {
        $labels = [
            'draft' => 'Draft',
            'for_approval' => 'For Approval',
            'for_procurement' => 'For Procurement',
            'ordered' => 'Ordered',
            'for_delivery' => 'For Delivery',
            'partial' => 'Partially Received',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'ongoing' => 'Ongoing',
            'preparing' => 'Preparing',
            'delivered' => 'Delivered',
            'for_verification' => 'For Verification',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function statusColor()
    {
        $colors = [
            'draft' => 'secondary',
            'for_approval' => 'warning',
            'for_procurement' => 'info',
            'ordered' => 'primary',
            'for_delivery' => 'success',
            'partial' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            'ongoing' => 'primary',
            'preparing' => 'warning',
            'delivered' => 'success',
            'for_verification' => 'warning',
        ];
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Total quantity across all items
     */
    public function totalQuantity(): int
    {
        return $this->items->sum('quantity_ordered');
    }

    /**
     * Total cost across all items
     */
    public function totalCost(): float
    {
        return $this->items->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity_ordered);
    }
}
