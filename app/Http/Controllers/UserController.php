<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of sales agents.
     */
    public function index()
    {
        $salesAgents = User::whereIn('role', ['sales_agent', 'sales_representative'])
                          ->orderBy('name')
                          ->get();
        
        return view('admin.users.sales-agents', compact('salesAgents'));
    }

    /**
     * Show the form for creating a new sales agent.
     */
    public function create()
    {
        return view('admin.users.create-sales-agent');
    }

    /**
     * Store a newly created sales agent.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:sales_agent,sales_representative'],
            'phone' => ['nullable', 'string', 'max:20'],
            'employee_id' => ['required', 'string', 'max:50', 'unique:users'],
            'department' => ['nullable', 'string', 'max:100'],
            'sales_target' => ['nullable', 'numeric', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'hire_date' => ['nullable', 'date'],
            'supervisor' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'employee_id' => $request->employee_id,
            'department' => $request->department,
            'sales_target' => $request->sales_target,
            'commission_rate' => $request->commission_rate,
            'hire_date' => $request->hire_date,
            'supervisor' => $request->supervisor,
            'is_active' => true,
        ]);

        return redirect()->route('sales-agents.index')
                         ->with('success', 'Sales agent created successfully.');
    }

    /**
     * Display the specified sales agent.
     */
    public function show(User $user)
    {
        // Ensure we're showing a sales agent
        if (!$user->isSalesAgent()) {
            abort(404);
        }
        
        return view('admin.users.show-sales-agent', compact('user'));
    }

    /**
     * Show the form for editing the specified sales agent.
     */
    public function edit(User $user)
    {
        // Ensure we're editing a sales agent
        if (!$user->isSalesAgent()) {
            abort(404);
        }
        
        return view('admin.users.edit-sales-agent', compact('user'));
    }

    /**
     * Update the specified sales agent.
     */
    public function update(Request $request, User $user)
    {
        // Ensure we're updating a sales agent
        if (!$user->isSalesAgent()) {
            abort(404);
        }
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:sales_agent,sales_representative'],
            'phone' => ['nullable', 'string', 'max:20'],
            'employee_id' => ['required', 'string', 'max:50', 'unique:users,employee_id,'.$user->id],
            'department' => ['nullable', 'string', 'max:100'],
            'sales_target' => ['nullable', 'numeric', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'hire_date' => ['nullable', 'date'],
            'supervisor' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'employee_id' => $request->employee_id,
            'department' => $request->department,
            'sales_target' => $request->sales_target,
            'commission_rate' => $request->commission_rate,
            'hire_date' => $request->hire_date,
            'supervisor' => $request->supervisor,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
            
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('sales-agents.index')
                         ->with('success', 'Sales agent updated successfully.');
    }

    /**
     * Remove the specified sales agent.
     */
    public function destroy(User $user)
    {
        // Ensure we're deleting a sales agent
        if (!$user->isSalesAgent()) {
            abort(404);
        }
        
        $user->delete();

        return redirect()->route('sales-agents.index')
                         ->with('success', 'Sales agent deleted successfully.');
    }

    /**
     * Get sales agents for API (for dropdowns in sales forms)
     */
    public function apiSalesAgents()
    {
        $salesAgents = User::whereIn('role', ['sales_agent', 'sales_representative'])
                          ->where('is_active', true)
                          ->orderBy('name')
                          ->get(['id', 'name', 'employee_id', 'role']);
        
        return response()->json($salesAgents);
    }
}
