<?php

namespace App\Observers;

use App\Models\LayoutJob;
use App\Support\LayoutAudit;

/**
 * Automatic audit logging para sa bawat pagbabago ng layout job (Andrew 2026-10-21).
 * Kahit anong code path ang mag-update (controller, tinker, bulk), naka-log lahat.
 */
class LayoutJobObserver
{
    public function created(LayoutJob $job): void
    {
        LayoutAudit::fromModel($job, 'created');
    }

    public function updated(LayoutJob $job): void
    {
        LayoutAudit::fromModel($job, 'updated');
    }

    public function deleted(LayoutJob $job): void
    {
        LayoutAudit::fromModel($job, 'deleted');
    }
}
