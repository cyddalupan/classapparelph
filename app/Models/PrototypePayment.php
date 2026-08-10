<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrototypePayment extends Model
{
    protected $fillable = [
        'prototype_sale_id',
        'payment_type',
        'amount',
        'payment_method',
        'payment_account_id',
        'reference_number',
        'screenshot_path',
        'payment_status',
        'verified_by',
        'verified_at',
        'payment_date',
        'notes',
    ];

    public function sale()
    {
        return $this->belongsTo(PrototypeSale::class, 'prototype_sale_id');
    }

    public function paymentAccount()
    {
        return $this->belongsTo(PaymentAccount::class, 'payment_account_id');
    }

    public function isVerified()
    {
        return in_array($this->payment_status, ['verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified']);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->whereIn('payment_status', ['verified', 'down_payment_verified', 'additional_payment_verified', 'full_payment_verified']);
    }
}
