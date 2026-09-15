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
            ] + $this->emptyNavCounts());
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
        ] + $this->navCounts($user));
    }

    /**
     * Zeroed placeholder so the layout variables always exist for every role.
     */
    private function emptyNavCounts(): array
    {
        return [
            'navCountApproval' => 0,
            'navCountDelay' => 0,
            'navCountBj' => 0,
            'navCountFeedback' => 0,
            'navCountFreebie' => 0,
            'navCountAddon' => 0,
            'navCountRefund' => 0,
            'navCountSpecialPrice' => 0,
            'navCountDamage' => 0,
            'navCountKanban' => 0,
            'navCountGa' => 0,
            'navCountLayoutPayout' => 0,
            'navCountPaymentVerify' => 0,
            'navCountPaymentReview' => 0,
            'navCountCloseout' => 0,
            'navCountRejected' => 0,
            'navCountAgentDelay' => 0,
        ];
    }

    /**
     * Sidebar nav badge counts — ADDITIVE only.
     *
     * Each count is computed ONLY when the user is authorized to open the page it
     * links to, and uses the SAME scope that page uses (Class dept 4 for
     * prod_manager/QA, "own records" for non-managers, unscoped for admin/COO).
     * That guarantees the navbar badge and the page never disagree, and that no
     * role ever sees a count for data it cannot open.
     */
    private function navCounts($user): array
    {
        $counts = $this->emptyNavCounts();
        if (!$user) {
            return $counts;
        }

        $classScoped = $user->isClassScoped();   // prod_manager | qa → Class dept 4 only
        $isManager = $user->isManager();         // admin | manager | prod_manager
        $isCoo = $user->isCoo();
        $classDept = $classScoped ? 4 : null;

        // ⏳ Pending Approval — page allows isManager() || isCoo()
        if ($isManager || $isCoo) {
            $q = PrototypeSale::where('status', 'pending_approval')->whereNull('archived_at');
            if ($classDept !== null) {
                $q->where('department_id', $classDept);
            }
            $counts['navCountApproval'] = (int) $q->count();
        }

        // ⚠️ Delay List — page allows admin | role=manager | coo | classScoped(prod_manager/qa)
        if ($user->isAdmin() || $user->role === 'manager' || $isCoo || $classScoped) {
            $q = PrototypeSale::where('is_delayed', 1)->whereNull('delay_review_status');
            if ($classDept !== null) {
                $q->where('department_id', $classDept);
            }
            $counts['navCountDelay'] = (int) $q->count();
        }

        // 🔧 Backjob List — page allows admin | role=manager | coo | classScoped
        if ($user->isAdmin() || $user->role === 'manager' || $isCoo || $classScoped) {
            $counts['navCountBj'] = $this->backjobCount($classDept);
        }

        // 📋 Production Feedback — page: managers (+ COO) see ALL; agents/artists see own only.
        // Navbar badge only rendered for manager-level / COO (their nav item).
        if ($isManager || $isCoo) {
            $q = ProductionFeedback::whereIn('status', ['open', 'acknowledged']);
            if ($classDept !== null) {
                $q->whereHas('sale', function ($sub) use ($classDept) {
                    $sub->where('department_id', $classDept);
                });
            }
            $counts['navCountFeedback'] = (int) $q->count();
        }

        // 🎁 Freebie List — page requires isManager() || isCoo() (canApprove)
        if ($isManager || $isCoo) {
            $q = DB::table('freebie_requests')
                ->join('prototype_sales', 'freebie_requests.sale_id', '=', 'prototype_sales.id')
                ->where('freebie_requests.status', 'pending');
            if ($classDept !== null) {
                $q->where('prototype_sales.department_id', $classDept);
            }
            $counts['navCountFreebie'] = (int) $q->count();
        }

        // ➕ Add-ons — pending add-on + change requests (matches SaleAddonController::pendingCount)
        $addonQ = DB::table('sale_addon_requests')
            ->join('prototype_sales', 'sale_addon_requests.sale_id', '=', 'prototype_sales.id')
            ->where('sale_addon_requests.status', 'pending');
        $changeQ = DB::table('prototype_sale_changes')
            ->join('prototype_sales', 'prototype_sale_changes.sale_id', '=', 'prototype_sales.id')
            ->where('prototype_sale_changes.status', 'pending');
        if (!$isManager) {
            $addonQ->where('prototype_sales.sales_agent_id', $user->id);
            $changeQ->where('prototype_sales.sales_agent_id', $user->id);
        }
        if ($classDept !== null) {
            $addonQ->where('prototype_sales.department_id', $classDept);
            $changeQ->where('prototype_sales.department_id', $classDept);
        }
        $counts['navCountAddon'] = (int) ($addonQ->count() + $changeQ->count());

        // 💸 Refunds — page allows isManager() | isCoo() | isCpo() | isCmo()
        if ($isManager || $isCoo || $user->isCpo() || $user->isCmo()) {
            $q = DB::table('prototype_refunds')
                ->join('prototype_sales', 'prototype_refunds.prototype_sale_id', '=', 'prototype_sales.id')
                ->where('prototype_refunds.refund_status', 'pending');
            if ($classDept !== null) {
                $q->where('prototype_sales.department_id', $classDept);
            }
            $counts['navCountRefund'] = (int) $q->count();
        }

        // 🏷️ Special Price — page allows isAdmin() | isCoo(); count = lines not yet "checked"
        if ($user->isAdmin() || $isCoo) {
            $counts['navCountSpecialPrice'] = $this->specialPriceUncheckedCount($classDept);
        }

        // 🛠️ Damage Reports — open reports in the user's own scope (mirrors DamageReportController::index)
        $counts['navCountDamage'] = $this->damageOpenCount($user);

        // 🗂️ Kanban Board — sales with a pending add-on/change request (mirrors PrototypeSalesController::kanban)
        $counts['navCountKanban'] = $this->kanbanPendingCount($user, $classDept);

        // 📋 GA Job List — delayed active jobs (mirrors the GA Job List "Delayed" stat)
        $counts['navCountGa'] = $this->gaDelayedCount($classDept);

        // 🎨 Layout Job List — pending payout requests (matches the page's "N pending" panel)
        if ($user->isAdmin() || $user->isCoo() || $user->isCpo() || $user->isCmo()) {
            $counts['navCountLayoutPayout'] = (int) DB::table('layout_job_payouts')
                ->whereIn('status', ['requested', 'paid'])->count();
        }

        // ✅ Payment Verification — pending payments on the verifier's own accounts
        // (own-account scoped for EVERY role, incl. admin — mirrors paymentVerification())
        $counts['navCountPaymentVerify'] = $this->pendingVerificationCount((int) $user->id);

        // 🧾 Payment Review (accountant) — requests awaiting accountant review
        if ($user->isAccountant() || $user->isAdmin() || $user->isHrAccountantAgent()) {
            $counts['navCountPaymentReview'] = (int) DB::table('payment_review_requests')
                ->where('status', 'requested')->count();
        }

        // 🗄️ Close-out Review (executive) — accepted requests awaiting exec review
        if ($user->isAdmin() || $user->isCoo()) {
            $counts['navCountCloseout'] = (int) DB::table('payment_review_requests')
                ->where('status', 'accepted')->count();
        }

        // ♻️ Rejected & Cancelled — cancelled sales + rejected changes + rejected add-ons
        if ($isManager || $isCoo) {
            $counts['navCountRejected'] = $this->rejectedCancelledCount();
        }

        // ⏰ My Delays (GA) — own delayed jobs
        if ($user->isGa()) {
            $counts['navCountAgentDelay'] = (int) PrototypeSale::where('is_delayed', 1)
                ->where('sales_agent_id', $user->id)->count();
        }

        return $counts;
    }

    /**
     * Open damage reports in the user's scope (mirrors DamageReportController::index).
     * Reviewers (admin/coo/cpo/cmo) see all; others see their shop (+ reports they filed/were tagged in).
     */
    private function damageOpenCount($user): int
    {
        $open = ['submitted', 'under_review', 'issued', 'acknowledged', 'contested'];
        $q = \App\Models\DamageReport::whereIn('status', $open);
        if (in_array($user->role, ['admin', 'coo', 'cpo', 'cmo'], true)) {
            return (int) $q->count();
        }
        $uid = $user->id;
        $managedShop = SalesDepartment::where('manager_id', $uid)->first();
        $q->where(function ($sub) use ($managedShop, $uid) {
            if ($managedShop) {
                $sub->where('shop_id', $managedShop->id);
            }
            $sub->orWhere('reporter_id', $uid)
                ->orWhereHas('accountableUsers', fn ($u) => $u->where('user_id', $uid));
        });
        return (int) $q->count();
    }

    /**
     * Distinct sales with a pending add-on or change request, scoped like the Kanban board
     * (Class dept for classScoped; own sales for agents/staff; all for admin/COO).
     */
    private function kanbanPendingCount($user, ?int $classDept): int
    {
        $sales = PrototypeSale::whereIn('status', ['confirmed', 'in_production', 'pending', 'completed'])
            ->whereNull('archived_at');
        if ($classDept !== null) {
            $sales->where('department_id', $classDept);
        }
        if (!($user->isAdmin() || $user->isCoo() || $user->isClassScoped())) {
            $sales->where('sales_agent_id', $user->id);
        }
        $ids = $sales->pluck('id');
        if ($ids->isEmpty()) {
            return 0;
        }
        $addon = DB::table('sale_addon_requests')->where('status', 'pending')
            ->whereIn('sale_id', $ids)->pluck('sale_id')->all();
        $change = DB::table('prototype_sale_changes')->where('status', 'pending')
            ->whereIn('sale_id', $ids)->pluck('sale_id')->all();
        return count(array_unique(array_merge($addon, $change)));
    }

    /**
     * Delayed active jobs (matches the "Delayed" stat on the GA Job List page).
     * Class dept (4) when classScoped; otherwise all departments.
     */
    private function gaDelayedCount(?int $classDept): int
    {
        $q = PrototypeSale::whereIn('status', ['confirmed', 'in_production', 'pending', 'completed'])
            ->whereNull('archived_at')
            ->where('is_delayed', 1)
            ->whereIn('production_stage', ['FOR SAMPLE', 'FOR APPROVAL', 'FOR FORMAT', 'PRINTING', 'PRESSING', 'CUTTING']);
        if ($classDept !== null) {
            $q->where('department_id', $classDept);
        }
        return (int) $q->count();
    }

    /**
     * Pending payment verifications on the verifier's own payment accounts
     * (mirrors PrototypeSalesController::paymentVerification()'s merged list count).
     */
    private function pendingVerificationCount(int $uid): int
    {
        $payments = DB::table('prototype_payments')
            ->join('payment_accounts', 'prototype_payments.payment_account_id', '=', 'payment_accounts.id')
            ->where('payment_accounts.user_id', $uid)
            ->where('prototype_payments.payment_status', 'pending')
            ->count();

        $initial = DB::table('prototype_sales')
            ->join('payment_accounts', 'prototype_sales.payment_account_id', '=', 'payment_accounts.id')
            ->where('payment_accounts.user_id', $uid)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('prototype_payments')
                  ->whereColumn('prototype_payments.prototype_sale_id', '=', 'prototype_sales.id');
            })
            ->where('prototype_sales.deposit_paid', '>', 0)
            ->whereNull('prototype_sales.deleted_at')
            ->whereNull('prototype_sales.archived_at')
            ->where(function ($q) {
                $q->where('prototype_sales.payment_status', 'pending')
                  ->orWhereNull('prototype_sales.payment_status');
            })
            ->count();

        return (int) ($payments + $initial);
    }

    /**
     * Cancelled sales + rejected change requests + rejected add-on requests
     * (mirrors PrototypeSalesController::rejectedCancelledPage()).
     */
    private function rejectedCancelledCount(): int
    {
        $n = PrototypeSale::where('status', 'cancelled')->whereNull('archived_at')->count();
        $n += DB::table('prototype_sale_changes')->where('status', 'rejected')->count();
        $n += DB::table('sale_addon_requests')->where('status', 'rejected')->count();
        return (int) $n;
    }

    /**
     * Active backjob comments + open freebie slips.
     * Mirrors the backjobCount computation in PrototypeSalesController::list().
     * $classDept = 4 → Class only; null → all departments.
     */
    private function backjobCount(?int $classDept): int
    {
        $count = 0;

        $checklists = ProductionChecklist::where(function ($q) {
            $q->whereNotNull('ga_notes')->where('ga_notes', '!=', '')
              ->orWhereNotNull('additional_comments')->where('additional_comments', '!=', '')
              ->orWhereNotNull('product_comments')->where('product_comments', '!=', '');
        })->get();

        foreach ($checklists as $chk) {
            $sale = DB::table('prototype_sales')->find($chk->sale_id);
            if (!$sale) {
                continue;
            }
            if ($classDept !== null && (int) $sale->department_id !== $classDept) {
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
        $fbQ = DB::table('freebie_slips')
            ->join('prototype_sales', 'freebie_slips.sale_id', '=', 'prototype_sales.id')
            ->where('freebie_slips.status', 'open');
        if ($classDept !== null) {
            $fbQ->where('prototype_sales.department_id', $classDept);
        }
        $count += (int) $fbQ->count();

        return $count;
    }

    /**
     * Special Price review — number of special-price LINES not yet marked "checked".
     * Mirrors the line extraction in PrototypeSalesController::specialPriceList()
     * exactly (sublimationForm.hasSpecialPrice + printing.isSpecialPrice, item-level
     * fallbacks, and the same lineKey derivation), then subtracts the lines present
     * in `prototype_special_price_reviews`.
     */
    private function specialPriceUncheckedCount(?int $classDept): int
    {
        $query = PrototypeSale::whereNull('archived_at')
            ->where(function ($sub) {
                $sub->whereRaw("services LIKE '%hasSpecialPrice%'")
                    ->orWhereRaw("services LIKE '%isSpecialPrice%'")
                    ->orWhereRaw("services LIKE '%specialPriceReason%'");
            });
        if ($classDept !== null) {
            $query->where('department_id', $classDept);
        }

        $sales = $query->get(['id', 'services']);
        if ($sales->isEmpty()) {
            return 0;
        }

        // sale_id => [ lineKey => true ]
        $lineKeys = [];
        foreach ($sales as $sale) {
            $svc = is_string($sale->services) ? json_decode($sale->services, true) : ($sale->services ?? []);
            foreach ((array) $svc as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $sub = $item['sublimationForm'] ?? [];
                $print = $item['printing'] ?? [];

                $subSpecial = !empty($sub['hasSpecialPrice']) || !empty($item['hasSpecialPrice']);
                $printSpecial = !empty($print['isSpecialPrice']) || !empty($item['isSpecialPrice']);

                if ($subSpecial) {
                    $key = (string) ($item['id'] ?? ('n' . crc32(($item['name'] ?? '') . '|Sublimation'))) . '|Sublimation';
                    $lineKeys[$sale->id][$key] = true;
                }
                if ($printSpecial) {
                    $key = (string) ($item['id'] ?? ('n' . crc32(($item['name'] ?? '') . '|Garment Print'))) . '|Garment Print';
                    $lineKeys[$sale->id][$key] = true;
                }
            }
        }

        $total = 0;
        foreach ($lineKeys as $keys) {
            $total += count($keys);
        }
        if ($total === 0) {
            return 0;
        }

        $reviewed = DB::table('prototype_special_price_reviews')
            ->whereIn('sale_id', array_keys($lineKeys))
            ->get()
            ->keyBy(fn ($r) => $r->sale_id . '|' . $r->line_key);

        $unchecked = 0;
        foreach ($lineKeys as $saleId => $keys) {
            foreach ($keys as $key => $_) {
                if (!$reviewed->has($saleId . '|' . $key)) {
                    $unchecked++;
                }
            }
        }

        return $unchecked;
    }
}
