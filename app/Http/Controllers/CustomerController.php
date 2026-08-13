<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    // Show customer list page
    public function index()
    {
        $customers = Customer::with('creator')
            ->orderBy('total_spent', 'desc')
            ->paginate(20);

        // Outstanding balance per customer (how much is still collectible)
        $outstanding = DB::table('prototype_sales')
            ->whereNull('archived_at')
            ->where('balance_due', '>', 0)
            ->whereIn('customer_id', $customers->pluck('id'))
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(balance_due) as total_outstanding')
            ->pluck('total_outstanding', 'customer_id');

        return view('customers.index', compact('customers', 'outstanding'));
    }

    // Show customer detail page (NEW — dedicated profile page)
    public function show($id)
    {
        $customer = Customer::with('creator')->findOrFail($id);
        
        // Get orders from prototype_sales table
        $orders = DB::table('prototype_sales')
            ->where('customer_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $orderCount = $orders->count();
        $totalSpent = $orders->sum('subtotal');

        // Outstanding balance — how much is still collectible from this customer
        $outstandingBalance = DB::table('prototype_sales')
            ->where('customer_id', $id)
            ->whereNull('archived_at')
            ->where('balance_due', '>', 0)
            ->sum('balance_due');
        
        // Get products purchased from prototype_order_items via prototype_orders
        $recentItems = DB::table('prototype_order_items')
            ->join('prototype_orders', 'prototype_order_items.order_id', '=', 'prototype_orders.id')
            ->where('prototype_orders.customer_id', $id)
            ->orderBy('prototype_order_items.created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('customers.show', compact('customer', 'orders', 'orderCount', 'totalSpent', 'recentItems', 'outstandingBalance'));
    }

    // Save customer (create or update)
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'marketplace' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'company' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if customer already exists by phone
        $customer = Customer::where('phone', $request->phone)->first();

        if ($customer) {
            // Update existing customer
            $customer->update($request->only(['name', 'email', 'marketplace', 'address', 'company']));
            
            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully',
                'customer' => $customer->load('creator')
            ]);
        } else {
            // Create new customer
            $customer = Customer::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'marketplace' => $request->marketplace,
                'address' => $request->address,
                'company' => $request->company,
                'customer_tier' => 'bronze',
                'total_orders' => 0,
                'total_spent' => 0,
                'average_order_value' => 0,
                'first_order_date' => null,
                'last_order_date' => null,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'customer' => $customer->load('creator')
            ]);
        }
    }

    // API: Get customer by ID (AJAX endpoint)
    public function apiShow($id)
    {
        $customer = Customer::with('creator')->find($id);
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    // Search customers with filters
    public function search(Request $request)
    {
        $query = Customer::with('creator');
        
        // Text search
        if ($q = $request->get('q')) {
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('company', 'like', "%{$q}%")
                    ->orWhere('customer_id_number', 'like', "%{$q}%");
            });
        }
        
        // Tier filter
        if ($tier = $request->get('tier')) {
            $query->where('customer_tier', $tier);
        }
        
        // Total spent range
        if ($minSpent = $request->get('min_spent')) {
            $query->where('total_spent', '>=', (float) $minSpent);
        }
        if ($maxSpent = $request->get('max_spent')) {
            $query->where('total_spent', '<=', (float) $maxSpent);
        }
        
        // Last order date range
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('last_order_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('last_order_date', '<=', $dateTo);
        }
        
        // Marketplace filter
        if ($marketplace = $request->get('marketplace')) {
            $query->where('marketplace', $marketplace);
        }
        
        $customers = $query->orderBy('total_spent', 'desc')
            ->limit(50)
            ->get();
        
        return response()->json([
            'success' => true,
            'customers' => $customers
        ]);
    }

    // Get customer orders (API)
    public function orders($id)
    {
        $customer = Customer::find($id);
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }
        
        $orders = DB::table('prototype_sales')
            ->where('customer_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }

    // Check if customer exists by phone
    public function checkByPhone(Request $request)
    {
        $phone = $request->get('phone');
        
        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number is required'
            ], 400);
        }
        
        $customer = Customer::where('phone', $phone)->with('creator')->first();
        
        return response()->json([
            'success' => true,
            'exists' => $customer ? true : false,
            'customer' => $customer
        ]);
    }

    // Update customer data (from AJAX edit)
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'nullable|email|max:255',
            'marketplace' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $customer->update($request->only(['name', 'phone', 'email', 'marketplace', 'location', 'company', 'notes']));
        
        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'customer' => $customer->fresh()->load('creator')
        ]);
    }
}
