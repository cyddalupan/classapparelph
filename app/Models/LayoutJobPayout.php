<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayoutJobPayout extends Model
{
    protected $table = 'layout_job_payouts';

    protected $fillable = [
        'ga_user_id', 'amount', 'status',
        'requested_by', 'requested_at', 'request_notes',
        'payment_method', 'payment_reference', 'payment_proof_path',
        'paid_by', 'paid_at',
        'verified_by', 'verified_at', 'reject_reason',
    ];

    protected $casts = [
        'amount' => 'float',
        'requested_at' => 'datetime',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function gaUser()
    {
        return $this->belongsTo(User::class, 'ga_user_id');
    }

    public function jobs()
    {
        return $this->hasMany(LayoutJob::class, 'payout_id');
    }
}
