<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrototypeSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('sales.prototype.index');
    }

    /**
     * Show cart-based order creation form.
     */
    public function cartCreate()
    {
        return view('sales.prototype.cart-create');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = \DB::table('sales_departments')->where('is_active', true)->get();
        $marketplaceOptions = \App\Models\Customer::getMarketplaceOptions();
        return view('sales.prototype.create', compact('departments', 'marketplaceOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate customer data
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email',
            'marketplace' => 'nullable|string',
        ]);
        
        // Use existing customer_id if provided, otherwise find/create
        if ($request->customer_id) {
            $customer = \App\Models\Customer::find($request->customer_id);
            if (!$customer) {
                return back()->with('error', 'Customer not found. Please save customer first.');
            }
        } else {
            // Find or create customer
            $customer = \App\Models\Customer::firstOrCreate(
                ['phone' => $request->customer_phone],
                [
                    'name' => $request->customer_name,
                    'email' => $request->customer_email,
                    'marketplace' => $request->marketplace,
                    'location' => $request->customer_address,
                    'company' => $request->customer_company,
                    'created_by' => auth()->id(),
                ]
            );
        }
        
        // If customer already exists, update their info if provided
        if ($customer->wasRecentlyCreated === false) {
            // Update customer info if new data is provided
            $updates = [];
            if ($request->customer_email && !$customer->email) {
                $updates['email'] = $request->customer_email;
            }
            if ($request->marketplace && !$customer->marketplace) {
                $updates['marketplace'] = $request->marketplace;
            }
            if ($request->customer_address && !$customer->location) {
                $updates['location'] = $request->customer_address;
            }
            if ($request->customer_company && !$customer->company) {
                $updates['company'] = $request->customer_company;
            }
            if (!empty($updates)) {
                $customer->update($updates);
            }
        }
        
        // Generate sales number
        $salesNumber = 'SALE-' . date('Ymd') . '-' . strtoupper(uniqid());
        
        // Get department name
        $department = \DB::table('sales_departments')->find($request->department_id);
        
        // Calculate balance due
        $balanceDue = $request->total_amount - $request->deposit_paid;
        
        // Create sale
        $saleId = \DB::table('prototype_sales')->insertGetId([
            'sales_number' => $salesNumber,
            'customer_id' => $customer->id,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'customer_address' => $request->customer_address,
            'sales_agent_id' => auth()->id(),
            'sales_agent_name' => auth()->user()->name,
            'department_id' => $request->department_id,
            'department_name' => $department->name,
            'services' => json_encode($request->services),
            'subtotal' => $request->subtotal,
            'tax' => $request->tax,
            'total_amount' => $request->total_amount,
            'deposit_paid' => $request->deposit_paid,
            'balance_due' => $balanceDue,
            'payment_method' => $request->payment_method,
            'payment_owner' => $request->payment_owner,
            'payment_status' => 'pending',
            'customer_notes' => $request->customer_notes,
            'internal_notes' => $request->internal_notes,
            'estimated_completion_date' => $request->estimated_completion_date,
            'kanban_status' => 'new',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Update customer LTV stats
        $customer->total_orders += 1;
        $customer->total_spent += $request->total_amount;
        $customer->average_order_value = $customer->total_spent / $customer->total_orders;
        
        if (!$customer->first_order_date) {
            $customer->first_order_date = now();
        }
        $customer->last_order_date = now();
        
        $customer->updateTier();
        $customer->save();
        
        // Create KANBAN item
        \DB::table('sales_kanban_items')->insert([
            'sale_id' => $saleId,
            'department_id' => $request->department_id,
            'title' => 'New Sale: ' . $request->customer_name,
            'description' => 'Services: ' . count($request->services) . ' items | Total: ₱' . number_format($request->total_amount, 2),
            'status' => 'todo',
            'assigned_to' => null,
            'position' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('sales.prototype.show', $saleId)
            ->with('success', 'Sale created successfully! It has been added to the KANBAN board.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            abort(404);
        }
        
        $services = json_decode($sale->services, true);
        $kanbanItem = \DB::table('sales_kanban_items')->where('sale_id', $id)->first();
        
        return view('sales.prototype.show', compact('sale', 'services', 'kanbanItem'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Display KANBAN board for sales.
     */
    public function kanban($department = null)
    {
        //
    }

    /**
     * Update sale status.
     */
    public function updateStatus(Request $request, $id)
    {
        //
    }

    /**
     * Verify payment for a sale.
     */
    public function verifyPayment(Request $request, $id)
    {
        //
    }
}
