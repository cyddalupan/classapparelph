<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SalesAgentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        // Check if user is a sales agent or sales representative
        $user = Auth::user();
        if (!$user->isSalesAgent() && !$user->isSalesRepresentative()) {
            // If not a sales agent, redirect to dashboard with error message
            return redirect()->route('dashboard')->with('error', 'You do not have permission to access sales agent features.');
        }
        
        return $next($request);
    }
}
