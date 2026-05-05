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
        // Try department_id from form, then hidden_department_id, then extract from items_json
        // Get department - try multiple sources
        $deptCode = $request->department_id ?: $request->hidden_department_id;
        
        if (!$deptCode) {
            // Extract from items_json
            $itemsJson = $request->items_json;
            if ($itemsJson) {
                $items = json_decode($itemsJson, true);
                if (is_array($items) && count($items) > 0 && isset($items[0]['department'])) {
                    $deptCode = $items[0]['department'];
                }
            }
        }
        
        if (!$deptCode) {
            $deptCode = 'iprint'; // Default fallback
        }
        
        $department = \DB::table('sales_departments')->where('code', $deptCode)->first();
        
        if (!$department) {
            $department = \DB::table('sales_departments')->where('code', 'iprint')->first();
        }
        
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
            'department_id' => $department->id,
            'department_name' => $department->name,
            'services' => json_encode($request->services),
            'subtotal' => $request->subtotal ?: 0,
            'tax' => $request->tax ?: 0,
            'total_amount' => $request->total_amount ?: 0,
            'deposit_paid' => $request->deposit_paid ?: 0,
            'balance_due' => $balanceDue,
            'payment_method' => $request->payment_method ?: 'cash',
            'payment_owner' => $request->payment_owner ?: 'company',
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
        $customer->total_spent += $request->subtotal ?: 0;
        $customer->notes = $request->customer_notes ?: $customer->notes;
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
            'department_id' => $department->id,
            'title' => 'New Sale: ' . $request->customer_name,
            'description' => 'Services: ' . count($request->services ?? []) . ' items | Total: ₱' . number_format($request->total_amount ?: 0, 2),
            'status' => 'todo',
            'assigned_to' => null,
            'position' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('sales.prototype.create')
            ->with('success', 'Sale saved! It has been added to the Kanban board.');
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
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            abort(404);
        }
        
        // Handle double-encoded JSON (some records store services as a string inside a JSON string)
        $raw = $sale->services;
        $services = json_decode($raw, true);
        if (is_string($services)) {
            $services = json_decode($services, true);
        }
        if (!is_array($services)) {
            $services = [['name' => $raw, 'qty' => 1, 'price' => 0]];
        }
        
        // Normalize: convert flat string arrays to associative format
        $normalized = [];
        foreach ($services as $i => $item) {
            if (is_string($item)) {
                $normalized[] = ['name' => $item, 'qty' => 1, 'price' => 0];
            } else {
                $normalized[] = $item;
            }
        }
        $services = $normalized;
        
        $deptColors = [
            1 => '#0d6efd',
            2 => '#198754',
            3 => '#dc3545',
            4 => '#6f42c1',
            5 => '#fd7e14',
            6 => '#6c757d',
        ];
        $deptLabels = [
            1 => 'iPrint',
            2 => 'Consol',
            3 => 'Cinco',
            4 => 'Class',
            5 => 'MTO',
            6 => 'Other',
        ];
        
        return view('sales.prototype.edit', compact('sale', 'services', 'deptColors', 'deptLabels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $sale = \DB::table('prototype_sales')->find($id);
        if (!$sale) {
            abort(404);
        }
        
        $request->validate([
            'department_id' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);
        
        $items = $request->items;
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['qty'] ?? 1) * ($item['price'] ?? 0);
        }
        
        $totalAmount = $subtotal;
        
        \DB::table('prototype_sales')->where('id', $id)->update([
            'services' => json_encode($items),
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
            'balance_due' => $totalAmount - ($sale->deposit_paid ?? 0),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('sales.prototype.edit', $id)
            ->with('success', 'Order updated successfully! New total: ₱' . number_format($totalAmount, 2));
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
        // Department codes from dropdown
        $deptCodeMap = [
            'iprint' => 1,
            'consol' => 2,
            'cinco'  => 3,
            'class'  => 4,
            'mto'    => 5,
            'other'  => 6,
        ];
        $allowedDepts = array_keys($deptCodeMap);
        $activeDept = $department;
        
        // Default: show ALL departments (no filter) — for admin view
        $showAll = false;
        
        if (!$activeDept || !in_array($activeDept, $allowedDepts)) {
            $showAll = true;
            $activeDept = 'all';
        }
        
        // Kanban columns matching the database ENUM
        $kanbanOrder = ['new', 'design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'];
        $kanbanLabels = [
            'new'                => 'New',
            'design'            => 'Design',
            'production'        => 'Production',
            'quality_check'      => 'Quality Check',
            'ready_for_delivery' => 'Ready for Delivery',
            'delivered'         => 'Delivered',
            'completed'         => 'Completed',
        ];
        $departmentLabels = [
            1 => 'iPrint',
            2 => 'Consol',
            3 => 'Cinco',
            4 => 'Class',
            5 => 'MTO',
            6 => 'Other',
        ];
        $departmentColors = [
            1 => '#0d6efd',
            2 => '#198754',
            3 => '#dc3545',
            4 => '#6f42c1',
            5 => '#fd7e14',
            6 => '#6c757d',
        ];
        
        // Get sales — filter by department if specific, or get ALL
        $query = \App\Models\PrototypeSale::with([])
            ->whereIn('status', ['confirmed', 'in_production', 'pending', 'completed']);
        
        if (!$showAll) {
            $deptId = $deptCodeMap[$activeDept];
            $query->where('department_id', $deptId);
        }
        
        $sales = $query->orderBy('created_at', 'desc')->paginate(100);
        
        // Initialize columns with proper order
        $columns = [];
        foreach ($kanbanOrder as $k) {
            $columns[$k] = [];
        }
        
        foreach ($sales as $sale) {
            $status = $sale->kanban_status ?: 'new';
            if (isset($columns[$status])) {
                $columns[$status][] = $sale;
            }
        }
        
        return view('sales.prototype.kanban', compact(
            'columns', 'activeDept', 'allowedDepts', 'kanbanLabels', 'kanbanOrder',
            'showAll', 'departmentLabels', 'departmentColors'
        ));
    }

    /**
     * Display Manager List page with pipeline progress bar.
     */
    public function list()
    {
        $deptCodeMap = [
            "iprint" => 1,
            "consol" => 2,
            "cinco"  => 3,
            "class"  => 4,
            "mto"    => 5,
            "other"  => 6,
        ];
        $kanbanStatuses = ["new", "design", "production", "quality_check", "ready_for_delivery", "delivered", "completed"];
        $kanbanLabels = [
            "new"                => "New",
            "design"            => "Design",
            "production"        => "Production",
            "quality_check"      => "QC",
            "ready_for_delivery" => "Ready",
            "delivered"         => "Delivered",
            "completed"         => "Completed",
        ];
        $departmentLabels = [
            1 => "iPrint",
            2 => "Consol",
            3 => "Cinco",
            4 => "Class",
            5 => "MTO",
            6 => "Other",
        ];
        $departmentColors = [
            1 => "#0d6efd",
            2 => "#198754",
            3 => "#dc3545",
            4 => "#6f42c1",
            5 => "#fd7e14",
            6 => "#6c757d",
        ];

        $sales = \App\Models\PrototypeSale::whereIn("status", ["confirmed", "in_production", "pending", "completed"])
            ->orderBy("created_at", "desc")
            ->paginate(50);
        
        return view("sales.prototype.list", compact(
            "sales", "kanbanStatuses", "kanbanLabels",
            "departmentLabels", "departmentColors"
        ));
    }

    public function updateKanbanStatus(Request $request, $id)
    {
        $request->validate([
            'kanban_status' => 'required|in:new,design,production,quality_check,ready_for_delivery,delivered,completed'
        ]);
        
        $sale = \App\Models\PrototypeSale::findOrFail($id);
        $sale->kanban_status = $request->kanban_status;
        $sale->save();
        
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', 'Status updated!');
    }

    /**
     * Update sale status.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'kanban_status' => 'required|string'
        ]);

        $sale = \App\Models\PrototypeSale::findOrFail($id);
        $sale->kanban_status = $request->kanban_status;
        $sale->save();

        return response()->json(['success' => true, 'status' => $sale->kanban_status]);
    }

    /**
     * Verify payment for a sale.
     */
    public function verifyPayment(Request $request, $id)
    {
        //
    }
}
