<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrototypeRefund extends Model
{
    protected $fillable = [
        'prototype_sale_id',
        'refund_amount',
        'refund_reason',
        'reason_details',
        'refund_method',
        'refund_account_id',
        'refund_account_name',
        'refund_account_number',
        'refund_status',
        'requested_by',
        'approved_by',
        'completed_by',
        'approved_at',
        'completed_at',
        'admin_notes',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(PrototypeSale::class, 'prototype_sale_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function refundAccount()
    {
        return $this->belongsTo(PaymentAccount::class, 'refund_account_id');
    }
}
