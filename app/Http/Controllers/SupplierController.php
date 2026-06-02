<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Gate::allows('manage-inventory') && !auth()->user()->isAdmin() && !auth()->user()->isProcurement()) {
                abort(403, 'Unauthorized.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(20);
        return view('procurement.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('procurement.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'payment_terms' => 'required|in:net_15,net_30,net_60,cod,advance',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['status'] = 'active';

        Supplier::create($validated);

        return redirect()->route('procurement.suppliers.index')
            ->with('success', 'Supplier added successfully!');
    }

    public function edit(Supplier $supplier)
    {
        return view('procurement.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'payment_terms' => 'required|in:net_15,net_30,net_60,cod,advance',
            'status' => 'required|in:active,inactive,blacklisted',
            'notes' => 'nullable|string|max:1000',
        ]);

        $supplier->update($validated);

        return redirect()->route('procurement.suppliers.index')
            ->with('success', 'Supplier updated successfully!');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('procurement.suppliers.index')
            ->with('success', 'Supplier deleted.');
    }
}
