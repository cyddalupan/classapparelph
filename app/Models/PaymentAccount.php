<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    protected $fillable = [
        'name',
        'provider',
        'account_number',
        'user_id',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prototypeSales()
    {
        return $this->hasMany(\App\Models\PrototypeSale::class, 'payment_account_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(PaymentAuditLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
