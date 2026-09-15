<?php

namespace App\Http\View\Composers;

use App\Models\ProcurementNotification;
use App\Models\ProcurementOrder;
use App\Models\ProductionChecklist;
use App\Models\ProductionFeedback;
use App\Models\PrototypeSale;
use App\Models\SalesDepartment;
use App\Models\SaleNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NotificationComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();
        if (!$user) {
            $view->with([
                'navUnreadNotifications' => collect(),
                'navNotificationCount' => 0,
                'navPendingVerifications' => 0,
            ] + $this->emptyProdManagerCounts());
            return;
        }

        // Procurement notifications for current user
        $notifications = ProcurementNotification::with(['order', 'fromUser'])
            ->where('to_user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->take(20)
            ->get();

        // Pending verifications for managers
        $managedDeptIds = SalesDepartment::where('manager_id', $user->id)->pluck('id');
        $pendingCount = ProcurementOrder::where('status', 'for_verification')
            ->whereIn('department_id', $managedDeptIds)
            ->count();

        // Unread payment verification requests (from agents) for this user
        $saleVerificationNotifs = SaleNotification::with(['sale', 'fromUser'])
            ->where('to_user_id', $user->id)
            ->where('type', 'verification_request')
            ->where('is_read', false)
            ->latest()
            ->take(10)
            ->get();

        $view->with([
            'navUnreadNotifications' => $notifications,
            'navNotificationCount' => $notifications->count(),
            'navPendingVerifications' => $pendingCount,
            'navSaleVerificationNotifs' => $saleVerificationNotifs,
            'navSaleVerificationCount' => $saleVerificationNotifs->count(),
        ] + $this->prodManagerNavCounts($user));
    }

    /**
     * Zeroed placeholder so the layout variables always exist (non-prod_manager roles).
     */
    private function emptyProdManagerCounts(): array
    {
        return [
            'navProdApprovalCount' => 0,
            'navProdDelayCount' => 0,
            'navProdBjCount' => 0,
            'navProdFeedbackCount' => 0,
            'navProdFreebieCount' => 0,
            'navProdAddonCount' => 0,
            'navProdRefundCount' => 0,
        ];
    }

    /**
     * Class Production Manager (prod_manager) sidebar badge counts — ADDITIVE only.
     * Every count is Class-scoped (department_id = 4) and mirrors the exact
     * definitions used by the corresponding Manager Order List page so the
     * navbar badge and the page header badge always agree.
     * Runs ONLY for prod_manager; all other roles keep the zeroed defaults.
     */
    private function prodManagerNavCounts($user): array
    {
        $counts = $this->emptyProdManagerCounts();
        if (!$user || !$user->isProdManager()) {
            return $counts;
        }

        $class = 4;

        // ⏳ Pending Approval — same as the Manager List panel (active, not archived)
        $counts['navProdApprovalCount'] = (int) PrototypeSale::where('status', 'pending_approval')
            ->whereNull('archived_at')
            ->where('department_id', $class)
            ->count();

        // ⚠️ Delay List — ONLY delays still needing review (matches delayList/list())
        $counts['navProdDelayCount'] = (int) PrototypeSale::where('is_delayed', 1)
            ->whereNull('delay_review_status')
            ->where('department_id', $class)
            ->count();

        // 📋 Production Feedback — open + acknowledged (matches feedback list scope)
        $counts['navProdFeedbackCount'] = (int) ProductionFeedback::whereIn('status', ['open', 'acknowledged'])
            ->whereHas('sale', function ($q) use ($class) {
                $q->where('department_id', $class);
            })
            ->count();

        // 🎁 Freebie List — pending requests (matches FreebieSlipController::pendingCount)
        $counts['navProdFreebieCount'] = (int) DB::table('freebie_requests')
            ->join('prototype_sales', 'freebie_requests.sale_id', '=', 'prototype_sales.id')
            ->where('freebie_requests.status', 'pending')
            ->where('prototype_sales.department_id', $class)
            ->count();

        // ➕ Add-ons — pending add-on + change requests (matches SaleAddonController::pendingCount)
        $addonCount = (int) DB::table('sale_addon_requests')
            ->join('prototype_sales', 'sale_addon_requests.sale_id', '=', 'prototype_sales.id')
            ->where('sale_addon_requests.status', 'pending')
            ->where('prototype_sales.department_id', $class)
            ->count();
        $changeCount = (int) DB::table('prototype_sale_changes')
            ->join('prototype_sales', 'prototype_sale_changes.sale_id', '=', 'prototype_sales.id')
            ->where('prototype_sale_changes.status', 'pending')
            ->where('prototype_sales.department_id', $class)
            ->count();
        $counts['navProdAddonCount'] = $addonCount + $changeCount;

        // 💸 Refunds — pending refunds (matches refundList rows)
        $counts['navProdRefundCount'] = (int) DB::table('prototype_refunds')
            ->join('prototype_sales', 'prototype_refunds.prototype_sale_id', '=', 'prototype_sales.id')
            ->where('prototype_refunds.refund_status', 'pending')
            ->where('prototype_sales.department_id', $class)
            ->count();

        // 🔧 Backjob List — same FIFO logic as PrototypeSalesController::list() backjobCount
        $counts['navProdBjCount'] = $this->classBackjobCount($class);

        return $counts;
    }

    /**
     * Active backjob comments + open freebie slips, Class-scoped.
     * Mirrors the backjobCount computation in PrototypeSalesController::list().
     */
    private function classBackjobCount(int $class): int
    {
        $count = 0;

        $checklists = ProductionChecklist::where(function ($q) {
            $q->whereNotNull('ga_notes')->where('ga_notes', '!=', '')
              ->orWhereNotNull('additional_comments')->where('additional_comments', '!=', '')
              ->orWhereNotNull('product_comments')->where('product_comments', '!=', '');
        })->get();

        foreach ($checklists as $chk) {
            $sale = DB::table('prototype_sales')->find($chk->sale_id);
            if (!$sale || (int) $sale->department_id !== $class) {
                continue;
            }

            $hasActive = false;

            // Main slip comments (a flat list)
            foreach (json_decode($chk->ga_notes ?? '', true) ?: [] as $c) {
                if (is_array($c) && empty($c['deleted']) && empty($c['done'])) {
                    $hasActive = true;
                    break;
                }
            }

            // Additional + product comments (keyed by item id)
            if (!$hasActive) {
                foreach (['additional_comments', 'product_comments'] as $field) {
                    foreach (json_decode($chk->{$field} ?? '', true) ?: [] as $comments) {
                        if (!is_array($comments)) {
                            continue;
                        }
                        foreach ($comments as $c) {
                            if (is_array($c) && empty($c['deleted']) && empty($c['done'])) {
                                $hasActive = true;
                                break 2;
                            }
                        }
                    }
                    if ($hasActive) {
                        break;
                    }
                }
            }

            if ($hasActive) {
                $count++;
            }
        }

        // 🎁 Open freebie slips also count once each (consistent with list())
        $count += (int) DB::table('freebie_slips')
            ->join('prototype_sales', 'freebie_slips.sale_id', '=', 'prototype_sales.id')
            ->where('freebie_slips.status', 'open')
            ->where('prototype_sales.department_id', $class)
            ->count();

        return $count;
    }
}
