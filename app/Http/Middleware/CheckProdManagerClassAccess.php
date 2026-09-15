<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Strict ALLOWLIST for the Class Production Manager (prod_manager) role.
 *
 * Only routes in the allowed list are accessible; EVERYTHING else returns 403 —
 * even if the URL is known. This matches Andrew's spec: "pag wala sa list ng
 * nav bar, hindi nila ma-access".
 *
 * Allowed routes mirror the prod_manager nav bar:
 *   Main      → Dashboard
 *   Production→ Dashboard (production.tracking), Kanban Board, Manager List, Calendar
 *   Account   → Profile (Soon, blocked), Log Out
 *
 * Functional routes required by those pages (kanban modal/drag, calendar data,
 * sale view, feedback & delay for Class) are also allowed but scoped to the
 * Class department inside the controllers.
 */
class CheckProdManagerClassAccess
{
    /**
     * Route names a Class Production Manager MAY access (allowlist).
     */
    protected array $allowedRoutes = [
        // Nav: Main
        'dashboard',

        // Nav: Production → Dashboard
        'production.tracking',

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

        // Pending Approval page + approve/reject (Class overload) — Class only, scoped in controller
        'sales.prototype.pending-approvals',
        'sales.prototype.approve-overload',
        'sales.prototype.reject-overload',
        'sales.prototype.day-load',

        // Nav: Production → Calendar (Class only, scoped in controller)
        'sales.prototype.calendar',
        'sales.prototype.calendar-data',
        'sales.prototype.reschedule',

        // Nav: Production → GA Order List (view + assign/unassign/complete GA jobs)
        'sales.prototype.ga-order-list',
        'sales.prototype.ga-assign',
        'sales.prototype.ga-unassign',
        'sales.prototype.ga-complete',

        // Rejected & Cancelled review page + restore (Class-scoped in controller).
        // Added 2026-09-15 together with the Manager Order List sidebar shortcuts.
        'sales.prototype.rejected-cancelled',
        'sales.prototype.restore-cancelled',
        'sales.prototype.restore-change',

        // Nav: Production → Priority Mockup Slideshow (read-only; Class only, scoped in controller)
        'sales.prototype.priority-slideshow',
        'sales.prototype.priority-slideshow.data',

        // Nav: Production → Backjob List (Class only, scoped in controller)
        'sales.prototype.backjobs',

        // Feedback & delays for Class sales (explicitly allowed by Andrew)
        'sales.prototype.production-feedback.list',
        'sales.prototype.production-feedback.store',
        'sales.prototype.production-feedback.status',
        'sales.prototype.production-feedback.notify',
        'sales.prototype.delays',
        'sales.prototype.delay-review',
        'sales.prototype.delay-review.submit',

        // Notify agent (🔔/🚨 buttons on Manager Order List) — Class only, scoped in controller
        'sales.prototype.notify-agent',

        // Request Time (⏰ per-sale button on Manager List + Request Time All)
        'sales.prototype.request-time',
        'sales.prototype.request-time-all',

        // Add-ons (🔔 Add-ons button & approve/reject) — Class only, scoped in controller
        'sales.prototype.addon.all-pending',
        'sales.prototype.addon.pending-count',
        'sales.prototype.addon.pending',
        'sales.prototype.addon.approve',
        'sales.prototype.addon.reject',

        // Change requests / reprocess (approve & reject) — Class only, scoped in controller
        'sales.prototype.approve-change',
        'sales.prototype.reject-change',
        'sales.prototype.reprocess-order',
        'sales.prototype.edit-items',
        'sales.prototype.submit-change',

        // Refund request & processing — Class only, scoped in controller
        'sales.prototype.submit-refund',
        'sales.prototype.process-refund',
        'sales.prototype.refunds',

        // Freebie Slips (🎁 section sa sale show page + Freebie List page)
        // Class only, scoped in FreebieSlipController (dept 4) — same as add-ons/refunds
        'sales.prototype.freebie-list',
        'sales.prototype.freebie.pending',
        'sales.prototype.freebie.all-pending',
        'sales.prototype.freebie.pending-count',
        'sales.prototype.freebie.request',
        'sales.prototype.freebie.approve',
        'sales.prototype.freebie.reject',
        'sales.prototype.freebie.audit',
        'sales.prototype.freebie.done',

        // Comments & notifications
        'sales.prototype.add-comment',
        'sales.prototype.audit-history',

        // Profile picture upload (avatar boxes on sidebar & top-right)
        'profile.avatar.update',
        'sales.prototype.notification-read',
        'sales.prototype.notifications-read-all',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isProdManager()) {
            $route = $request->route();
            $name = $route ? $route->getName() : '';

            if ($name === null || !in_array($name, $this->allowedRoutes, true)) {
                abort(403, 'Unauthorized access.');
            }
        }

        return $next($request);
    }
}
