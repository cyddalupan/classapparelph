<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // Trimmer/QA (role `trimmer_qa`) — walang access sa My Sales Dashboard
        // (403 doon), kaya deretso sa QA Job List. (Andrew 2026-10-23)
        if ($user && $user->isTrimmerQa()) {
            return redirect()->route('sales.prototype.qa-job-list');
        }

        // QA/Sales Agent have NO dashboard access — send them straight to My Sales
        // (their landing page) instead of the empty Dashboard.
        if ($user && ($user->isQa() || $user->isSalesAgent())) {
            return redirect()->intended(route('sales.team.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
