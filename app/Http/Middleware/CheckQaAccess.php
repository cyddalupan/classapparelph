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

        // Production checklist & additional production slip (used inside calendar/kanban modals)
        'api.production.checklist.get',
        'api.production.checklist.save',
        'api.production.additional.get',

        // Nav: Production → Manager List (Class only, scoped in controller)
        'sales.prototype.list',

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

        // Nav: Business → Customers (own-created only, scoped in controller)
        'customers.index',
        'customers.show',

        // Nav: My Sales → My Sales Dashboard, Add New Sale
        'sales.team.dashboard',
        'sales.prototype.create',
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
