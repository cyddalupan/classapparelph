<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\SalesDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Get or create cart for current session/user
    public function getCart(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        return response()->json([
            'cart' => $cart,
            'items' => $cart->items()->with('department')->get(),
            'totals' => [
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'total' => $cart->total,
            ],
            'department_breakdown' => $cart->getDepartmentBreakdown(),
        ]);
    }

    // Add item to cart
    public function addItem(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:sales_departments,id',
            'product_type' => 'required|in:garment,tarpaulin,embroidery,cutting,sewing,design',
            'product_details' => 'required|array',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $cart = $this->getOrCreateCart($request);

        $item = $cart->addItem([
            'department_id' => $request->department_id,
            'product_type' => $request->product_type,
            'product_details' => $request->product_details,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'item' => $item->load('department'),
            'cart' => $cart->fresh(),
        ]);
    }

    // Update item quantity
    public function updateQuantity(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getOrCreateCart($request);
        $item = $cart->updateItemQuantity($itemId, $request->quantity);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Quantity updated',
            'item' => $item,
            'cart' => $cart->fresh(),
        ]);
    }

    // Remove item from cart
    public function removeItem(Request $request, $itemId)
    {
        $cart = $this->getOrCreateCart($request);
        $cart->removeItem($itemId);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart' => $cart->fresh(),
        ]);
    }

    // Set customer for cart
    public function setCustomer(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $cart = $this->getOrCreateCart($request);
        $cart->update(['customer_id' => $request->customer_id]);

        return response()->json([
            'success' => true,
            'message' => 'Customer set for cart',
            'cart' => $cart->fresh()->load('customer'),
        ]);
    }

    // Get cart summary
    public function getSummary(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        
        if (!$cart->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a customer first',
            ], 400);
        }

        if ($cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'cart' => $cart->load(['customer', 'items.department']),
            'totals' => [
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'total' => $cart->total,
            ],
            'department_breakdown' => $cart->getDepartmentBreakdown(),
        ]);
    }

    // Clear cart
    public function clearCart(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        $cart->items()->delete();
        $cart->calculateTotals();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
            'cart' => $cart->fresh(),
        ]);
    }

    // Get departments for product type
    public function getDepartments($productType)
    {
        $departments = SalesDepartment::all();
        
        // Filter departments based on product type (you can customize this logic)
        $suitableDepartments = $departments->filter(function ($department) use ($productType) {
            // Example logic: garment printing goes to iPrint, tarpaulin to Consol, etc.
            // You can customize this based on your business rules
            if ($productType === 'garment') {
                return in_array($department->code, ['IPR', 'CLS', 'MTO']);
            } elseif ($productType === 'tarpaulin') {
                return in_array($department->code, ['CON', 'OTH']);
            } elseif ($productType === 'embroidery') {
                return in_array($department->code, ['CIN', 'CLS']);
            }
            return true; // Show all departments for other types
        });

        return response()->json([
            'success' => true,
            'departments' => $suitableDepartments,
        ]);
    }

    // Helper: Get or create cart
    private function getOrCreateCart(Request $request)
    {
        $sessionId = $request->session()->getId();
        $userId = Auth::id();
        
        // Try to find existing active cart
        $cart = Cart::active()
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->when(!$userId, function ($query) use ($sessionId) {
                return $query->where('session_id', $sessionId);
            })
            ->first();

        // Create new cart if none exists
        if (!$cart) {
            $cart = Cart::create([
                'session_id' => $userId ? null : $sessionId,
                'user_id' => $userId,
                'status' => 'active',
            ]);
        }

        return $cart;
    }
}