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
        'quantity',
        'involved_position',
        'involved_name',
        'damage_amount',
        'points',
        'evidence_path',
        'review_notes',
        'acknowledged_at',
        'resolved_at',
    ];

    protected $casts = [
        'damage_amount' => 'float',
        'quantity' => 'integer',
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

    /**
     * Severity bands base sa damage amount (₱). Andrew 2026-10-01.
     *   Minor    : 1 – 1,000
     *   Major    : 1,001 – 10,000
     *   Critical : 10,001 – pataas
     * Kapag may amount, DITO na nakabase ang severity (naka-lock) — hindi na manual.
     */
    public const SEVERITY_BANDS = [
        'minor'    => [1, 1000],
        'major'    => [1001, 10000],
        'critical' => [10001, null],
    ];

    /**
     * Severity mula sa amount. Null kung walang amount (<= 0) — ibig sabihin, manual pa.
     */
    public static function severityForAmount($amount): ?string
    {
        $amount = (float) $amount;
        if ($amount <= 0) {
            return null;
        }
        if ($amount <= 1000) {
            return 'minor';
        }
        if ($amount <= 10000) {
            return 'major';
        }
        return 'critical';
    }

    /** Literal na band label para sa display, hal. "₱1 – ₱1,000". */
    public static function bandLabel(string $severity): string
    {
        $b = self::SEVERITY_BANDS[$severity] ?? null;
        if (!$b) {
            return '';
        }
        return '₱' . number_format($b[0]) . ($b[1] === null ? '+' : ' – ₱' . number_format($b[1]));
    }

    public const STATUSES = [
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'issued' => 'Issued',
        'acknowledged' => 'Acknowledged',
        'contested' => 'Contested',
        'resolved' => 'Resolved',
        'dismissed' => 'Dismissed',
    ];

    // Open (still active) statuses — used for duplicate detection
    public const OPEN_STATUSES = ['submitted', 'under_review', 'issued', 'acknowledged', 'contested'];

    public function isOpen()
    {
        return in_array($this->status, self::OPEN_STATUSES);
    }

    /**
     * Nai-review na ba ito? (na-issue o lampas pa, o may reviewer na naitala)
     * Ginagamit sa list cards bilang indicator badge. Andrew 2026-10-01.
     */
    public function isReviewed(): bool
    {
        return $this->reviewer_id !== null
            || !in_array($this->status, ['submitted', 'under_review'], true);
    }

    /** May naitalang damage amount na (> 0). */
    public function hasAmount(): bool
    {
        return (float) ($this->damage_amount ?? 0) > 0;
    }

    /**
     * Naka-lock ba ang severity dahil may amount? (base sa SEVERITY_BANDS)
     */
    public function severityIsLocked(): bool
    {
        return $this->hasAmount();
    }

    /** Ang severity na dapat naka-set base sa amount (null kung wala). */
    public function derivedSeverity(): ?string
    {
        return self::severityForAmount($this->damage_amount);
    }

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
}
