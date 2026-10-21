<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Strict ALLOWLIST for the Trimmer/QA (role `trimmer_qa`) — Andrew 2026-10-21.
 *
 * Nav spec ni Andrew:
 *   Main       → Dashboard, Damage Reports
 *   Production → QA Job List, Backjob List
 *
 * QA Job List = parang GA Job List ang hitsura, pero ang lumalabas lang ay ang mga
 * sale na naka-tag na SEWING, QA, o DISPATCH. Kapag iba na ang tag (hal. PRINTING),
 * wala na sa listahan.
 *
 * 403 ang LAHAT ng route na wala sa listahan — kahit alam ang URL.
 */
class CheckTrimmerQaAccess
{
    /**
     * Route names a Trimmer/QA MAY access.
     */
    protected array $allowedRoutes = [
        // Nav: Main
        'dashboard',
        'upload.avatar',
        'profile.avatar.update',
        'logout',

        // Nav: Production → QA Job List (SEWING / QA / DISPATCH only)
        'sales.prototype.qa-job-list',

        // Nav: Production → Backjob List
        'sales.prototype.backjobs',

        // Damage Reports (file / view / edit / comment / ack / contest)
        // Reviewer-only actions (review / resolve / dismiss / adjust) are NOT here.
        'damage.index',
        'damage.create',
        'damage.store',
        'damage.show',
        'damage.update',
        'damage.comment',
        'damage.acknowledge',
        'damage.contest',

        // Sale show page (read-only reference mula sa QA Job List / Backjob List)
        'sales.prototype.show',
        'sales.prototype.details',
        'sales.prototype.comments',
        'sales.prototype.audit-history',
        'sales.prototype.print-slip',

        // Production checklist (read-only) para sa mga modal sa listahan
        'api.production.checklist.get',
        'api.production.additional.get',

        // Notifications
        'sales.prototype.notification-read',
        'sales.prototype.notifications-read-all',
        'sales.prototype.add-comment',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isTrimmerQa()) {
            $route = $request->route();
            $name = $route ? $route->getName() : '';

            if ($name === null || ! in_array($name, $this->allowedRoutes, true)) {
                abort(403, 'Unauthorized access.');
            }
        }

        return $next($request);
    }
}
