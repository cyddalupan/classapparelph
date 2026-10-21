<?php

namespace App\Observers;

use App\Models\LayoutJobPayout;
use App\Support\LayoutAudit;

/**
 * Automatic audit logging para sa mga payout request (Andrew 2026-10-21).
 */
class LayoutJobPayoutObserver
{
    public function created(LayoutJobPayout $payout): void
    {
        LayoutAudit::fromModel($payout, 'created');
    }

    public function updated(LayoutJobPayout $payout): void
    {
        LayoutAudit::fromModel($payout, 'updated');
    }

    public function deleted(LayoutJobPayout $payout): void
    {
        LayoutAudit::fromModel($payout, 'deleted');
    }
}
