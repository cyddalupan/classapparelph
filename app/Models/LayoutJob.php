<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayoutJob extends Model
{
    protected $table = 'layout_jobs';

    protected $fillable = [
        'job_no', 'customer_id', 'customer_name', 'description', 'reference_image_path',
        'type', 'amount', 'amount_set_by', 'amount_set_at',
        'payment_method', 'payment_reference', 'payment_screenshot_path',
        'payment_status', 'payment_verified_by', 'payment_verified_at', 'payment_reject_reason',
        'ga_user_id', 'assigned_by', 'assigned_at',
        'status', 'done_by', 'done_at',
        'payout_id', 'payout_requested_at',
        'sale_id', 'linked_to_sale_at',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'amount_set_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'assigned_at' => 'datetime',
        'done_at' => 'datetime',
        'payout_requested_at' => 'datetime',
        'linked_to_sale_at' => 'datetime',
    ];

    /* ------------------------------------------------------------------
     * Relationships
     * ---------------------------------------------------------------- */

    public function gaUser()
    {
        return $this->belongsTo(User::class, 'ga_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sale()
    {
        return $this->belongsTo(PrototypeSale::class, 'sale_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function payout()
    {
        return $this->belongsTo(LayoutJobPayout::class, 'payout_id');
    }

    /* ------------------------------------------------------------------
     * Helpers
     * ---------------------------------------------------------------- */

    public function isPaid(): bool
    {
        return $this->type === 'paid';
    }

    public function isFree(): bool
    {
        return $this->type === 'free';
    }

    /** Job "earns" for the GA: bayad = verified payment; libre = may amount na */
    public function isEarning(): bool
    {
        if ($this->isPaid()) {
            return $this->payment_status === 'verified';
        }
        return $this->amount !== null;
    }

    public function displayCustomer(): string
    {
        return $this->customer_name ?: ($this->customer ? $this->customer->name : '—');
    }

    public function displayAmount(): ?float
    {
        if ($this->amount !== null) {
            return (float) $this->amount;
        }
        if ($this->isPaid() && $this->payment_status === 'verified') {
            return (float) $this->amount; // paid fee na-verify na
        }
        return null;
    }

    public function statusLabel(): string
    {
        if ($this->status === 'done') {
            return 'done';
        }
        if ($this->isPaid() && $this->payment_status === 'pending') {
            return 'payment_pending';
        }
        if ($this->isPaid() && $this->payment_status === 'rejected') {
            return 'payment_rejected';
        }
        return 'open';
    }

    public static function nextJobNo(): string
    {
        $year = now()->year;
        $prefix = 'LJ-' . $year . '-';
        $last = static::where('job_no', 'like', $prefix . '%')
            ->orderByDesc('job_no')
            ->value('job_no');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
