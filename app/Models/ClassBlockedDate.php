<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class department "closed date" switch.
 *
 * Controlled by the Prod Manager / CEO / COO from the Calendar. When a date is
 * CLOSED, new Class sales cannot be created for that date. Existing sales
 * (including pending_approval ones) are never touched.
 */
class ClassBlockedDate extends Model
{
    protected $table = 'class_blocked_dates';

    public const CLASS_DEPT_ID = 4;

    protected $fillable = [
        'department_id',
        'date',
        'is_closed',
        'reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'is_closed' => 'boolean',
    ];

    /**
     * Is the given date closed for the department?
     */
    public static function isDateClosed($date, int $departmentId = self::CLASS_DEPT_ID): bool
    {
        if (!$date) {
            return false;
        }
        $d = \Carbon\Carbon::parse($date)->toDateString();

        return self::where('department_id', $departmentId)
            ->whereDate('date', $d)
            ->where('is_closed', true)
            ->exists();
    }

    /**
     * Return the closed-date row (with reason) for a date, or null if open.
     */
    public static function closedDateInfo($date, int $departmentId = self::CLASS_DEPT_ID)
    {
        if (!$date) {
            return null;
        }
        $d = \Carbon\Carbon::parse($date)->toDateString();

        return self::where('department_id', $departmentId)
            ->whereDate('date', $d)
            ->where('is_closed', true)
            ->first();
    }

    /**
     * List of closed date strings (Y-m-d) between $from and $to (inclusive).
     *
     * @return array<int,string>
     */
    public static function closedDatesBetween($from, $to, int $departmentId = self::CLASS_DEPT_ID): array
    {
        $from = \Carbon\Carbon::parse($from)->toDateString();
        $to = \Carbon\Carbon::parse($to)->toDateString();

        return self::where('department_id', $departmentId)
            ->where('is_closed', true)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->toDateString())
            ->all();
    }
}
