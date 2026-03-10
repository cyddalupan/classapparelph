<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinanceDashboardController extends Controller
{
    /**
     * Display the finance dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Calculate statistics
        $totalExpenses = Expense::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount');
        
        $pendingExpenses = Expense::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
        
        // For demo - these would come from Sales model
        $totalRevenue = 245800; // Placeholder
        $netProfit = $totalRevenue - $totalExpenses;
        
        // Get recent expenses
        $recentExpenses = Expense::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('finance.dashboard', [
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'pendingExpenses' => $pendingExpenses,
            'expenses' => $recentExpenses,
        ]);
    }
}