<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
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
                'customer' => $customer
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
                'customer_tier' => 'bronze', // Default tier
                'total_orders' => 0,
                'total_spent' => 0,
                'average_order_value' => 0,
                'first_order_date' => null,
                'last_order_date' => null,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'customer' => $customer
            ]);
        }
    }

    // Search customers
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Search query must be at least 3 characters'
            ], 400);
        }
        
        $customers = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('company', 'like', "%{$query}%")
            ->orderBy('total_spent', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json([
            'success' => true,
            'customers' => $customers
        ]);
    }

    // Get customer by ID
    public function show($id)
    {
        $customer = Customer::find($id);
        
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
        
        $customer = Customer::where('phone', $phone)->first();
        
        return response()->json([
            'success' => true,
            'exists' => $customer ? true : false,
            'customer' => $customer
        ]);
    }
}