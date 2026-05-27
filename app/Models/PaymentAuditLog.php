<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAuditLog extends Model
{
    protected $fillable = [
        'prototype_sale_id',
        'payment_account_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'remarks',
    ];

    public function prototypeSale()
    {
        return $this->belongsTo(\App\Models\PrototypeSale::class, 'prototype_sale_id');
    }

    public function paymentAccount()
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
