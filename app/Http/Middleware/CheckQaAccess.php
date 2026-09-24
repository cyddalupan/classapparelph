<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Strict ALLOWLIST for the QA / Sales Agent (qa) role.
 *
 * Only routes in the allowed list are accessible; EVERYTHING else returns 403 —
 * even if the URL is known. This mirrors Andrew's spec: "pag wala sa list ng
 * nav bar, hindi nila ma-access".
 *
 * QA is configured like the Class Production Manager (prod_manager) but with
 * these features REMOVED:
 *   ❌ Delay List
 *   ❌ Production Feedback list page (can still store feedback per sale)
 *   ❌ Bell/notify buttons on Manager Order List
 *   ❌ Approve reprocess / additional (add-on) approval
 *   ❌ Request refund
 *   ❌ Dashboard access
 *   ❌ Calendar drag & drop (view only)
 *
 * ADDED (sales agent side):
 *   ✅ Customers
 *   ✅ Add New Sale
 *   ✅ My Sales Dashboard
 *
 * All sales access is scoped to the Class department (department_id = 4)
 * inside the controllers.
 */
class CheckQaAccess
{
    /**
     * Route names a QA / Sales Agent MAY access (allowlist).
     */
    protected array $allowedRoutes = [
        // Nav: Production → Kanban Board (Class only, scoped in controller)
        'sales.prototype.kanban',
        'sales.prototype.update-status',
        'sales.prototype.priority',
        'sales.prototype.details',
        'sales.prototype.show',

        // Production slip (print & PDF) — Class only, scoped in controller
        'sales.prototype.print-slip',
        'sales.prototype.print-slip.pdf',

        // Audit history (read-only logs, used by show page comments/audit section)
        'sales.prototype.audit-history',

        // Uploads on the Sale Show page (File Screenshot / Approved Sample Color /
        // Mockups) — QA/Sales Agent can only manage their OWN sales
        // (ownership is enforced in the controller)
        'sales.prototype.upload-design-image',
        'sales.prototype.delete-design-image',
        'sales.prototype.upload-mockup',
        'sales.prototype.delete-mockup',
        'sales.prototype.set-main-mockup',

        // Pending add-on count badge (kanban/card headers)
        'sales.prototype.addon.pending-count',

        // Production checklist & additional production slip (used inside calendar/kanban modals)
        'api.production.checklist.get',
        'api.production.checklist.save',
        'api.production.additional.get',

        // Nav: Production → Manager List (Class only, scoped in controller)
        'sales.prototype.list',

        // Nav: Production → Backjob List (Class only, scoped in controller)
        'sales.prototype.backjobs',

        // Nav: Production → Calendar (VIEW ONLY — NO reschedule/drag)
        'sales.prototype.calendar',
        'sales.prototype.calendar-data',

        // Nav: Production → GA Job List (view; assign/complete gated in controller)
        'sales.prototype.ga-order-list',
        'sales.prototype.ga-assign',
        'sales.prototype.ga-unassign',
        'sales.prototype.ga-complete',

        // Feedback per sale (store allowed — list page & status changes NOT allowed)
        'sales.prototype.production-feedback.store',

        // Comments & notifications
        'sales.prototype.add-comment',
        'sales.prototype.notification-read',
        'sales.prototype.notifications-read-all',

        // Urgent notification response (modal reason → posted to sale comments)
        'sales.prototype.respond-urgent',

        // Freebie Slips — QA/Sales Agent can view + add freebie requests & mark slips done
        // (Class-scoped; request gating also in FreebieSlipController)
        'sales.prototype.freebie.pending',
        'sales.prototype.freebie.request',
        'sales.prototype.freebie.done',
        'sales.prototype.freebie.check',

        // Damage Reports — QA/Sales Agent can FILE + manage their own/reported damage reports
        // (same self-service set as the Prod Manager fix). Reviewer-only routes
        // (damage.review / damage.resolve / damage.dismiss) are EXCLUDED — the
        // controller's isReviewer() still guards them.
        'damage.index',
        'damage.create',
        'damage.store',
        'damage.show',
        'damage.update',
        'damage.comment',
        'damage.acknowledge',
        'damage.contest',

        // Layout Jobs — QA/Sales Agent: view list + create (tag GA) + link sa sale
        'sales.layout-jobs',
        'sales.layout-jobs.create',
        'sales.layout-jobs.store',
        'sales.layout-jobs.link-sale',

        // Profile picture upload (avatar boxes on sidebar & top-right)
        'profile.avatar.update',

        // Nav: Business → Customers (own-created only, scoped in controller)
        'customers.index',
        'customers.show',

        // Nav: My Sales → My Sales Dashboard, Add New Sale
        'sales.team.dashboard',
        'sales.prototype.dashboard',

        // My Sales → My Delays (own delays only, scoped in controller)
        'sales.team.delays',

        // My Sales → Production Feedback list (own/Class feedback, scoped in controller)
        'sales.prototype.production-feedback.list',
        'sales.prototype.create',
        'api.sublimation-prices',
        'product-pricing.api.products-for-box',
        'product-pricing.api.filter-options',
        'api.printing.options',
        'sales.prototype.day-load',
        'api.customers.check',
        'api.customers.search',
        'api.customers.show',
        'api.customers.save',
        'sales.prototype.store',
        'sales.prototype.agent.payment',
        'sales.prototype.agent.payment.store',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isQa()) {
            $route = $request->route();
            $name = $route ? $route->getName() : '';

            if ($name === null || !in_array($name, $this->allowedRoutes, true)) {
                abort(403, 'Unauthorized access.');
            }
        }

        return $next($request);
    }
}
