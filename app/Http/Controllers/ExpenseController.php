<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = Expense::where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        $totalExpenses = Expense::where('user_id', Auth::id())->sum('amount');
        $pendingExpenses = Expense::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->sum('amount');
        $paidExpenses = Expense::where('user_id', Auth::id())
            ->where('status', 'paid')
            ->sum('amount');
            
        $categories = [
            'supplies' => 'Supplies',
            'utilities' => 'Utilities',
            'salaries' => 'Salaries',
            'marketing' => 'Marketing',
            'equipment' => 'Equipment',
            'rent' => 'Rent',
            'transportation' => 'Transportation',
            'other' => 'Other'
        ];
        
        $statuses = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'overdue' => 'Overdue'
        ];
        
        $paymentMethods = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'gcash' => 'GCash',
            'maya' => 'Maya',
            'paypal' => 'PayPal',
            'other' => 'Other'
        ];
        
        return view('finance.expenses', compact(
            'expenses', 
            'totalExpenses',
            'pendingExpenses',
            'paidExpenses',
            'categories',
            'statuses',
            'paymentMethods'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Log the incoming request for debugging
        \Log::info('Expense store request received', [
            'data' => $request->all(),
            'date_value' => $request->input('date'),
            'amount_value' => $request->input('amount'),
            'user_id' => auth()->id()
        ]);
        
        // Use custom validation to handle date parsing
        $validator = Validator::make($request->all(), [
            'date' => ['required', function ($attribute, $value, $fail) {
                // Try multiple date formats
                $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y-m-d H:i:s', 'Y-m-d H:i'];
                foreach ($formats as $format) {
                    $date = \DateTime::createFromFormat($format, $value);
                    if ($date && $date->format($format) === $value) {
                        return; // Date is valid
                    }
                }
                $fail('The date format is invalid. Please use YYYY-MM-DD format.');
            }],
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string',
            'status' => 'required|string',
            'description' => 'required|string|max:500',
            'vendor' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string',
            'receipt_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        if ($validator->fails()) {
            \Log::error('Expense validation failed', [
                'errors' => $validator->errors()->toArray(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }
        
        $validated = $validator->validated();
        
        // Parse date to ensure YYYY-MM-DD format for database
        $dateInput = $validated['date'];
        $parsedDate = null;
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y-m-d H:i:s', 'Y-m-d H:i'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateInput);
            if ($date && $date->format($format) === $dateInput) {
                $parsedDate = $date->format('Y-m-d');
                break;
            }
        }
        
        // Use parsed date for database
        $validated['date'] = $parsedDate ?: $dateInput;
        
        // Ensure amount is properly formatted for database
        $amount = $validated['amount'];
        if (is_string($amount)) {
            // Remove any commas or other non-numeric characters except decimal point
            $amount = preg_replace('/[^\d\.]/', '', $amount);
            $validated['amount'] = (float) $amount;
        }
        
        $expense = Expense::create([
            'user_id' => Auth::id(),
            'date' => $validated['date'],
            'amount' => $validated['amount'],
            'category' => $validated['category'],
            'status' => $validated['status'],
            'description' => $validated['description'],
            'vendor' => $validated['vendor'],
            'payment_method' => $validated['payment_method'],
            'receipt_number' => $validated['receipt_number'],
            'notes' => $validated['notes']
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense added successfully!',
                'expense' => $expense
            ]);
        }
        
        return redirect()->route('finance.expenses')
            ->with('success', 'Expense added successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        // Check if user owns this expense
        if ($expense->user_id !== Auth::id()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string',
            'status' => 'required|string',
            'description' => 'required|string|max:500',
            'vendor' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string',
            'receipt_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $expense->update($validated);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully!',
                'expense' => $expense->fresh()
            ]);
        }
        
        return redirect()->route('finance.expenses')
            ->with('success', 'Expense updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        // Check if user owns this expense
        if ($expense->user_id !== Auth::id()) {
            abort(403);
        }
        
        $expense->delete();
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense deleted successfully!'
            ]);
        }
        
        return redirect()->route('finance.expenses')
            ->with('success', 'Expense deleted successfully!');
    }
    
    /**
     * Mark expense as paid
     */
    public function markAsPaid(Expense $expense)
    {
        // Check if user owns this expense
        if ($expense->user_id !== Auth::id()) {
            abort(403);
        }
        
        $expense->update(['status' => 'paid']);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense marked as paid!',
                'expense' => $expense->fresh()
            ]);
        }
        
        return redirect()->route('finance.expenses')
            ->with('success', 'Expense marked as paid!');
    }
    
    /**
     * Get expenses for API (for charts, etc.)
     */
    public function apiIndex(Request $request)
    {
        $query = Expense::where('user_id', Auth::id());
        
        // Apply filters
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        
        $expenses = $query->orderBy('date', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $expenses
        ]);
    }
    
    /**
     * Get expense statistics
     */
    public function statistics()
    {
        $total = Expense::where('user_id', Auth::id())->sum('amount');
        $pending = Expense::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->sum('amount');
        $paid = Expense::where('user_id', Auth::id())
            ->where('status', 'paid')
            ->sum('amount');
        $overdue = Expense::where('user_id', Auth::id())
            ->where('status', 'overdue')
            ->sum('amount');
            
        // Monthly breakdown
        $monthly = Expense::where('user_id', Auth::id())
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();
            
        // Category breakdown
        $byCategory = Expense::where('user_id', Auth::id())
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'pending' => $pending,
                'paid' => $paid,
                'overdue' => $overdue,
                'monthly' => $monthly,
                'by_category' => $byCategory
            ]
        ]);
    }
}