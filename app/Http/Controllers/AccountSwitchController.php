<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Account switcher para sa LINKED accounts (isang tao, dalawang account).
 * Halimbawa: Sales Agent (id 14) <-> Agent (id 50) ni Mary Joyce.
 *
 * Isang login lang — hindi na kailangang i-type ulit ang password para lumipat.
 */
class AccountSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $user = Auth::user();
        $target = $user ? $user->linkedUser : null;

        if (!$user || !$target || !$target->is_active) {
            return back()->with('error', 'Walang naka-link na account na pwedeng pasukin.');
        }

        // Lumipat sa naka-link na account (prevent session fixation)
        Auth::login($target);
        $request->session()->regenerate();

        // Role-appropriate landing page ng target account
        if ($target->isSalesAgent() || $target->isSalesRepresentative()) {
            return redirect()->route('sales.team.dashboard');
        }

        return redirect('/');
    }
}
