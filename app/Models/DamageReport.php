<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageReport extends Model
{
    protected $fillable = [
        'report_no',
        'shop_id',
        'sale_id',
        'reporter_id',
        'reviewer_id',
        'category',
        'severity',
        'status',
        'description',
        'damage_amount',
        'points',
        'evidence_path',
        'review_notes',
        'acknowledged_at',
        'resolved_at',
    ];

    protected $casts = [
        'damage_amount' => 'float',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public const CATEGORIES = [
        'production_error' => 'Production Error',
        'rework' => 'Rework',
        'quality_issue' => 'Quality Issue',
        'mishandling' => 'Mishandling',
        'missing_file' => 'Missing File',
        'wrong_file_sent' => 'Wrong File Sent',
        'no_response' => 'No Response',
        'other' => 'Other',
    ];

    public const SEVERITIES = [
        'minor' => 'Minor',
        'major' => 'Major',
        'critical' => 'Critical',
    ];

    public const SEVERITY_POINTS = [
        'minor' => 1,
        'major' => 2,
        'critical' => 3,
    ];

    public const STATUSES = [
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'issued' => 'Issued',
        'acknowledged' => 'Acknowledged',
        'contested' => 'Contested',
        'resolved' => 'Resolved',
        'dismissed' => 'Dismissed',
    ];

    public function shop()
    {
        return $this->belongsTo(SalesDepartment::class, 'shop_id');
    }

    public function sale()
    {
        return $this->belongsTo(PrototypeSale::class, 'sale_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function accountableUsers()
    {
        return $this->hasMany(DamageReportUser::class);
    }

    public function comments()
    {
        return $this->hasMany(DamageReportComment::class)->orderBy('created_at');
    }

    public function isOpen()
    {
        return !in_array($this->status, ['resolved', 'dismissed']);
    }
}
