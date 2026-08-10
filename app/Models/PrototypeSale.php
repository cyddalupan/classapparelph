<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrototypeSale extends Model
{
    use SoftDeletes;

    protected $table = 'prototype_sales';

    protected $fillable = [
        'sales_number',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'sales_agent_id',
        'sales_agent_name',
        'department_id',
        'department_name',
        'services',
        'subtotal',
        'tax',
        'total_amount',
        'deposit_paid',
        'balance_due',
        'payment_method',
        'payment_owner',
        'payment_account_id',
        'payment_date',
        'reference_number',
        'payment_screenshot_path',
        'payment_status',
        'verified_by',
        'verified_at',
        'verify_requested_at',
        'verify_requested_by',
        'mockup_images',
        'reference_images',
        'kanban_status',
        'assigned_to',
        'estimated_completion_date',
        'actual_completion_date',
        'customer_notes',
        'internal_notes',
        'status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'services' => 'array',
        'mockup_images' => 'array',
        'reference_images' => 'array',
        'assigned_to' => 'array',
        'verified_at' => 'datetime',
        'estimated_completion_date' => 'date',
        'actual_completion_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentAccount()
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\PrototypePayment::class, 'prototype_sale_id');
    }

    public function verifiedPayments()
    {
        return $this->hasMany(\App\Models\PrototypePayment::class, 'prototype_sale_id')->verified();
    }

    public function auditLogs()
    {
        return $this->hasMany(PaymentAuditLog::class, 'prototype_sale_id');
    }

    /**
     * Items through the cart - prototype_orders has cart_id, not prototype_sale_id
     * Map via the sales_number or keep empty since prototype_orders has different structure
     */
    public function items()
    {
        // prototype_orders table uses cart_id, not prototype_sale_id
        // Return empty collection to avoid SQL errors
        return $this->hasMany(PrototypeOrder::class, 'id')->whereRaw('1 = 0');
    }
    
    /**
     * Items decoded from the services JSON array
     */
    public function getDecodedServicesAttribute()
    {
        if (is_string($this->services)) {
            return json_decode($this->services, true) ?: [];
        }
        return $this->services ?: [];
    }
}
