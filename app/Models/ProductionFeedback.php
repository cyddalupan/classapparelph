<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIssueCategories;

class ProductionFeedback extends Model
{
    use HasIssueCategories;

    // "feedback" is uncountable in Laravel's pluralizer, so Eloquent would
    // look for `production_feedback` instead of the real table name.
    protected $table = 'production_feedbacks';

    protected $fillable = [
        'sale_id',
        'from_user_id',
        'to_user_id',
        'category',
        'message',
        'acknowledgement',
        'status',
        'acknowledged_at',
        'resolved_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(PrototypeSale::class, 'sale_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * The user involved/tagged alongside the primary recipient
     * (e.g. an Artist tagged when giving feedback to an Agent).
     */
    public function involvedUser()
    {
        return $this->belongsTo(User::class, 'involved_user_id');
    }
}
