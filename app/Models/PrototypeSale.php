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
        'design_images',
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
        'design_images' => 'array',
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

    public function refunds()
    {
        return $this->hasMany(\App\Models\PrototypeRefund::class, 'prototype_sale_id');
    }

    public function completedRefunds()
    {
        return $this->refunds()->where('refund_status', 'completed');
    }

    /**
     * Total paid from payments table (excluding rejected), fallback to legacy deposit_paid column.
     */
    public function getTotalPaidAttribute()
    {
        if ($this->relationLoaded('payments')) {
            $paid = $this->payments->whereNotIn('payment_status', ['rejected', 'reject_pending'])->sum('amount');
        } else {
            $paid = $this->payments()->whereNotIn('payment_status', ['rejected', 'reject_pending'])->sum('amount');
        }
        if ($paid <= 0) {
            return (float) ($this->deposit_paid ?? 0);
        }
        return (float) $paid;
    }

    /**
     * Total refunded from completed refunds.
     */
    public function getTotalRefundedAttribute()
    {
        if ($this->relationLoaded('completedRefunds')) {
            $sum = $this->completedRefunds->sum('refund_amount');
        } else {
            $sum = $this->completedRefunds()->sum('refund_amount');
        }
        return (float) $sum;
    }

    /**
     * Net paid = total paid minus completed refunds (never negative).
     */
    public function getNetPaidAttribute()
    {
        return max($this->total_paid - $this->total_refunded, 0);
    }

    /**
     * Balance due computed from net paid (never negative).
     */
    public function getBalanceDueComputedAttribute()
    {
        return max((float) ($this->total_amount ?? 0) - $this->net_paid, 0);
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

    /**
     * Build a production-facing spec summary for a service item.
     * Prefers the sublimation form description (e.g. "TSHIRT VNECK - DRIFIT | RAGLAN"),
     * then garment + fabric + parts, then falls back to the item name.
     */
    public static function itemSpecSummary(array $item): string
    {
        $sf = $item['sublimationForm'] ?? [];
        if (!empty($sf['description']) && is_string($sf['description'])) {
            return trim($sf['description']);
        }
        $bits = [];
        if (!empty($sf['garment']['name'])) $bits[] = $sf['garment']['name'];
        if (!empty($sf['fabric']['name'])) $bits[] = $sf['fabric']['name'];
        foreach (($sf['parts'] ?? []) as $p) {
            if (is_array($p) && !empty($p['name'])) $bits[] = $p['name'];
            elseif (is_string($p) && trim($p) !== '') $bits[] = trim($p);
        }
        if ($bits) return implode(' | ', $bits);
        $pt = $item['productType'] ?? '';
        if ($pt) return ucwords(str_replace('_', ' ', $pt));
        return $item['name'] ?? $item['product_name'] ?? 'Item';
    }
}
