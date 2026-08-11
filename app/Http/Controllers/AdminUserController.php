<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminUserController extends Controller
{
    /**
     * Display all users with optional role filter.
     */
    public function index(Request $request)
    {
        $roleFilter = $request->get('role', 'all');
        $search = $request->get('search');

        $query = User::query();

        if ($roleFilter !== 'all') {
            $query->where('role', $roleFilter);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(20);

        $roleCounts = [
            'all' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'sales_agent' => User::where('role', 'sales_agent')->count(),
            'sales_representative' => User::where('role', 'sales_representative')->count(),
            'staff' => User::where('role', 'staff')->count(),
            'procurement' => User::where('role', 'procurement')->count(),
            'customer' => User::where('role', 'customer')->count(),
        ];

        $user = auth()->user();

        return view('admin.users.index', compact('users', 'roleFilter', 'search', 'roleCounts', 'user'));
    }

    /**
     * Show create user form.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,sales_agent,sales_representative,staff,procurement'],
            'phone' => ['nullable', 'string', 'max:20'],
            'employee_id' => ['nullable', 'string', 'max:50', 'unique:users'],
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

        return redirect()->route('admin.users.index')
                         ->with('success', 'User created successfully.');
    }

    /**
     * Show user details.
     */
    public function show(User $user)
    {
        // Get sales count for this user
        $salesCount = \DB::table('prototype_sales')
            ->where('sales_agent_id', $user->id)
            ->count();

        $activeSalesCount = \DB::table('prototype_sales')
            ->where('sales_agent_id', $user->id)
            ->whereIn('kanban_status', ['new', 'sample_approval', 'design', 'production', 'quality_check'])
            ->count();

        return view('admin.users.show', compact('user', 'salesCount', 'activeSalesCount'));
    }

    /**
     * Show edit user form.
     */
    public function edit(User $user)
    {
        // Prevent editing own admin account
        if ($user->id === auth()->id() && $user->isAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Edit your own profile from the Profile page instead.');
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:admin,sales_agent,sales_representative,staff,procurement'],
            'phone' => ['nullable', 'string', 'max:20'],
            'employee_id' => ['nullable', 'string', 'max:50', 'unique:users,employee_id,'.$user->id],
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
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users.index')
                         ->with('success', 'User updated successfully.');
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        return redirect()->route('admin.users.index')
                         ->with('success', 'User status updated.');
    }
}
