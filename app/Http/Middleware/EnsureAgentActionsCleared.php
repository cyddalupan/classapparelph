<?php

namespace App\Http\Middleware;

use App\Services\AgentActionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Holds a Sales Agent's "My Sales Dashboard" and "Create Sales" pages
 * while they still have Action-Required items (urgent notifications,
 * open production feedback, missing File/Approved photos).
 *
 * Non-agents (admin/COO/manager/QA/prod_manager/etc.) pass through untouched.
 * AJAX / JSON requests and the action page itself are never blocked,
 * so walang redirect loop. (Andrew 2026-09-18)
 */
class EnsureAgentActionsCleared
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only sales agents are gated.
        if (! AgentActionService::isAgent($user)) {
            return $next($request);
        }

        // Never block the action page itself, nor AJAX/JSON (inline actions).
        if ($request->routeIs('sales.team.action-required') || $request->ajax() || $request->expectsJson()) {
            return $next($request);
        }

        if (AgentActionService::total($user) > 0) {
            return redirect()->route('sales.team.action-required');
        }

        return $next($request);
    }
}
