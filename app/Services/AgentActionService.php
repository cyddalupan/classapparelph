<?php

namespace App\Services;

use App\Models\ProductionFeedback;
use App\Models\PrototypeSale;
use App\Models\SaleNotification;
use App\Models\User;

/**
 * Computes the "Action Required" blockers for a Sales Agent.
 *
 * A sales agent must clear these before they can open
 *  - My Sales Dashboard (/sales/team)
 *  - Create Sales       (/sales/prototype/create)
 *
 * Blockers (all LIVE checks, hindi stale):
 *   1. Unanswered URGENT notifications  (is_urgent = 1 AND response IS NULL)
 *   2. Open production feedback addressed to / involving the agent
 *   3. Active sales missing the File Screenshot photo
 *
 * NOTE (Andrew 2026-09-19): the "Approved Sample Color" blocker was REMOVED from this gate.
 *     Nakadepende ito sa client approval ng sample — kung minsan matagal mag-approve ang client,
 *     hindi makapag-upload agad ang agent at nahaharangan ang account. File Screenshot lang
 *     ang hihingin dito. (Nananatili pa rin ang Approved Sample Color bilang opsyonal na upload.)
 *
 * Purely additive — walang binabago sa existing logic. (Andrew 2026-09-18)
 */
class AgentActionService
{
    /** Sales counted as "active" (still in progress). */
    public const ACTIVE_STATUSES = ['confirmed', 'in_production', 'pending', 'completed'];

    /** Closed-out statuses — hindi na dapat harangin ang agent. */
    public const DEAD_STATUSES = ['cancelled', 'completed'];

    /** Stages / kanban na tapos na — hindi na counted para sa missing photos. */
    public const DONE_STAGES = ['DISPATCH', 'UNPAID', 'DONE'];
    public const DONE_KANBAN = ['ready_for_delivery', 'delivered', 'completed'];

    public static function isAgent(?User $user): bool
    {
        return $user !== null && $user->isSalesAgent();
    }

    /**
     * @return array{urgent: \Illuminate\Support\Collection, feedback: \Illuminate\Support\Collection, missing: array, counts: array{urgent:int,feedback:int,missing:int}, total:int}
     */
    public static function blockers(User $user): array
    {
        // (1) Unanswered URGENT notifications.
        //     - hindi kasama ang archived / cancelled / completed na sale
        //     - isang sagot lang kada sale: ang PINAKA-BAGONG reminder lang ang blocker
        //       ("hindi kailangan sagutin ang luma" — superseded na). (Andrew 2026-09-18)
        $urgent = SaleNotification::with(['sale', 'fromUser'])
            ->where('to_user_id', $user->id)
            ->where('is_urgent', true)
            ->whereNull('response')
            ->whereHas('sale', function ($q) {
                $q->whereNull('archived_at')
                  ->whereNotIn('status', self::DEAD_STATUSES);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('sale_id')   // ordered desc → first per sale = latest
            ->values();

        // (2) Open production feedback addressed to (or involving) the agent.
        //     Hindi rin kasama ang archived / cancelled / completed na sale.
        $feedback = ProductionFeedback::with(['sale', 'fromUser'])
            ->where('status', 'open')
            ->where(function ($q) use ($user) {
                $q->where('to_user_id', $user->id)
                  ->orWhere('involved_user_id', $user->id);
            })
            ->whereHas('sale', function ($q) {
                $q->whereNull('archived_at')
                  ->whereNotIn('status', self::DEAD_STATUSES);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // (3) Active sales missing the File Screenshot photo.
        //     (Approved Sample Color blocker removed 2026-09-19 — depende sa client approval.)
        $sales = PrototypeSale::where('sales_agent_id', $user->id)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->whereNull('archived_at')
            ->where(function ($q) {
                $q->whereNull('production_stage')
                  ->orWhereNotIn('production_stage', self::DONE_STAGES);
            })
            ->where(function ($q) {
                $q->whereNull('kanban_status')
                  ->orWhereNotIn('kanban_status', self::DONE_KANBAN);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $missing = [];
        foreach ($sales as $sale) {
            $di = self::designImages($sale);
            $hasFile  = collect($di)->contains(fn ($d) => ($d['type'] ?? '') === 'file_screenshot');
            // Approved Sample Color is intentionally NOT checked here (Andrew 2026-09-19):
            // the client may take long to approve the sample, so it must not block the account.
            if (! $hasFile) {
                $missing[] = ['sale' => $sale, 'kind' => 'file_screenshot', 'label' => 'Missing File Photo', 'icon' => '📄'];
            }
        }

        $counts = [
            'urgent'   => $urgent->count(),
            'feedback' => $feedback->count(),
            'missing'  => count($missing),
        ];

        return [
            'urgent'   => $urgent,
            'feedback' => $feedback,
            'missing'  => $missing,
            'counts'   => $counts,
            'total'    => $counts['urgent'] + $counts['feedback'] + $counts['missing'],
        ];
    }

    /** Number of blockers only (cheap-ish; still loads rows for accuracy). */
    public static function total(User $user): int
    {
        return self::blockers($user)['total'];
    }

    /** design_images → array (handles both array cast and raw JSON string). */
    public static function designImages(PrototypeSale $sale): array
    {
        $di = $sale->design_images;
        if (is_string($di)) {
            $di = json_decode($di, true) ?: [];
        }
        return is_array($di) ? $di : [];
    }
}
