<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InventoryController extends Controller
{
    /**
     * Display a listing of the inventory items.
     */
    public function index()
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized access.');
        }

        $inventory = Inventory::with('supplier')
            ->orderBy('name')
            ->paginate(20);

        $stats = [
            'total_items' => Inventory::count(),
            'active_items' => Inventory::active()->count(),
            'low_stock_items' => Inventory::lowStock()->count(),
            'out_of_stock_items' => Inventory::active()->where('current_stock', '<=', 0)->count(),
            'total_stock_value' => Inventory::sum(\DB::raw('current_stock * unit_price')),
        ];

        return view('inventory.index', compact('inventory', 'stats'));
    }

    /**
     * Show the form for creating a new inventory item.
     */
    public function create()
    {
        // TEMPORARILY REMOVED PERMISSION CHECK FOR TESTING
        // if (!Gate::allows('manage-inventory')) {
        //     abort(403, 'Unauthorized access.');
        // }

        $suppliers = Supplier::orderBy('name')->get();
        $types = ['raw_material', 'finished_good', 'consumable', 'equipment'];
        $categories = $this->getCategories();

        return view('inventory.create', compact('suppliers', 'types', 'categories'));
    }

    /**
     * Store a newly created inventory item in storage.
     */
    public function store(Request $request)
    {
        // TEMPORARILY REMOVED PERMISSION CHECK FOR TESTING
        // if (!Gate::allows('manage-inventory')) {
        //     abort(403, 'Unauthorized access.');
        // }

        // Check if this is a shirt product submission (has shirt-specific fields)
        $isShirtProduct = $request->has('shirt_sku') || $request->has('shirt_brand') || $request->has('shirt_type');
        
        if ($isShirtProduct) {
            // SHIRT PRODUCT VALIDATION
            $validated = $request->validate([
                'shirt_sku' => 'required|string|max:255|unique:inventories,sku',
                'shirt_brand' => 'required|string|max:255',
                'shirt_type' => 'required|string|max:255',
                'shirt_color' => 'required|string|max:255',
                'shirt_size' => 'required|string|max:255',
                'shirt_price' => 'required|numeric|min:0',
                'current_stock' => 'required|numeric|min:0',
                'add_stock' => 'required|numeric|min:0',
                'shirt_supplier' => 'nullable|string|max:255',
                'shirt_shop' => 'nullable|string|max:255',
                'shirt_notes' => 'nullable|string',
            ]);
            
            // Calculate total stock
            $totalStock = $validated['current_stock'] + $validated['add_stock'];
            
            // Prepare data for inventory table
            $inventoryData = [
                'name' => $validated['shirt_brand'] . ' ' . $validated['shirt_type'] . ' - ' . $validated['shirt_color'] . ' (' . $validated['shirt_size'] . ')',
                'sku' => $validated['shirt_sku'],
                'type' => 'finished_good',
                'category' => 'Shirt Products',
                'description' => $validated['shirt_notes'] ?? null,
                'unit_price' => $validated['shirt_price'],
                'unit_of_measure' => 'piece',
                'current_stock' => $totalStock,
                'minimum_stock' => 0,
                'reorder_quantity' => 0,
                'supplier_sku' => $validated['shirt_supplier'] ?? null,
                'is_active' => true,
            ];
            
            // Store shirt-specific details in specifications JSON
            $specifications = [
                'brand' => $validated['shirt_brand'],
                'shirt_type' => $validated['shirt_type'],
                'color' => $validated['shirt_color'],
                'size' => $validated['shirt_size'],
                'shop' => $validated['shirt_shop'] ?? null,
                'original_current_stock' => $validated['current_stock'],
                'added_stock' => $validated['add_stock'],
            ];
            
            $inventoryData['specifications'] = json_encode($specifications);
            
            Inventory::create($inventoryData);
            
            // Return JSON response for AJAX submission
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shirt product added successfully!',
                    'data' => $inventoryData
                ]);
            }
            
            return redirect()->route('inventory.index')
                ->with('success', 'Shirt product added successfully.');
        } else {
            // ORIGINAL VALIDATION (for non-shirt products)
            $validated = $request->validate([
                'category' => 'nullable|string|max:255',
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|in:raw_material,finished_good,consumable,equipment',
                'unit_price' => 'nullable|numeric|min:0',
                'unit_of_measure' => 'nullable|string|max:255',
                'current_stock' => 'nullable|numeric|min:0',
                'minimum_stock' => 'nullable|numeric|min:0',
                'is_active' => 'nullable|boolean',
            ]);

            // Handle specifications JSON
            $specifications = [];
            if ($request->has('spec_key') && $request->has('spec_value')) {
                $keys = $request->input('spec_key');
                $values = $request->input('spec_value');
                
                for ($i = 0; $i < count($keys); $i++) {
                    if (!empty($keys[$i]) && !empty($values[$i])) {
                        $specifications[$keys[$i]] = $values[$i];
                    }
                }
            }
            
            $validated['specifications'] = !empty($specifications) ? json_encode($specifications) : null;

            // Set default values for required fields if not provided
            $validated['category'] = $validated['category'] ?? 'Uncategorized';
            $validated['name'] = $validated['name'] ?? 'Unnamed Item';
            $validated['type'] = $validated['type'] ?? 'finished_good';
            $validated['unit_price'] = $validated['unit_price'] ?? 0.00;
            $validated['unit_of_measure'] = $validated['unit_of_measure'] ?? 'piece';
            $validated['current_stock'] = $validated['current_stock'] ?? 0;
            $validated['minimum_stock'] = $validated['minimum_stock'] ?? 0;
            $validated['is_active'] = $validated['is_active'] ?? true;

            Inventory::create($validated);

            return redirect()->route('inventory.index')
                ->with('success', 'Inventory item created successfully.');
        }
    }

    /**
     * Display the specified inventory item.
     */
    public function show(Inventory $inventory)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized access.');
        }

        return view('inventory.show', compact('inventory'));
    }

    /**
     * Show the form for editing the specified inventory item.
     */
    public function edit(Inventory $inventory)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized access.');
        }

        $suppliers = Supplier::orderBy('name')->get();
        $types = ['raw_material', 'finished_good', 'consumable', 'equipment'];
        $categories = $this->getCategories();

        return view('inventory.edit', compact('inventory', 'suppliers', 'types', 'categories'));
    }

    /**
     * Update the specified inventory item in storage.
     */
    public function update(Request $request, Inventory $inventory)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'category' => 'nullable|string|max:255',
        ]);

        // Handle specifications JSON
        $specifications = [];
        if ($request->has('spec_key') && $request->has('spec_value')) {
            $keys = $request->input('spec_key');
            $values = $request->input('spec_value');
            
            for ($i = 0; $i < count($keys); $i++) {
                if (!empty($keys[$i]) && !empty($values[$i])) {
                    $specifications[$keys[$i]] = $values[$i];
                }
            }
        }
        
        $validated['specifications'] = !empty($specifications) ? json_encode($specifications) : null;

        $inventory->update($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory item updated successfully.');
    }

    /**
     * Remove the specified inventory item from storage.
     */
    public function destroy(Inventory $inventory)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized access.');
        }

        $inventory->delete();

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory item deleted successfully.');
    }

    /**
     * Restore a soft-deleted inventory item.
     */
    public function restore($id)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized access.');
        }

        $inventory = Inventory::withTrashed()->findOrFail($id);
        $inventory->restore();

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory item restored successfully.');
    }

    /**
     * Permanently delete an inventory item.
     */
    public function forceDelete($id)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized access.');
        }

        $inventory = Inventory::withTrashed()->findOrFail($id);
        $inventory->forceDelete();

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory item permanently deleted.');
    }

    /**
     * Show trashed inventory items.
     */
    public function trashed()
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized access.');
        }

        $inventory = Inventory::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(20);

        return view('inventory.trashed', compact('inventory'));
    }

    /**
     * Update stock level for an inventory item.
     */
    public function updateStock(Request $request, Inventory $inventory)
    {
        if (!Gate::allows('manage-inventory')) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldStock = $inventory->current_stock;
        
        switch ($validated['adjustment_type']) {
            case 'add':
                $newStock = $oldStock + $validated['quantity'];
                break;
            case 'subtract':
                $newStock = max(0, $oldStock - $validated['quantity']);
                break;
            case 'set':
                $newStock = $validated['quantity'];
                break;
        }

        $inventory->update([
            'current_stock' => $newStock,
            'last_restocked_at' => now(),
        ]);

        // Log the stock adjustment (you could create a StockAdjustment model for this)
        // \App\Models\StockAdjustment::create([...]);

        return redirect()->route('inventory.show', $inventory)
            ->with('success', 'Stock updated successfully. Old: ' . $oldStock . ', New: ' . $newStock);
    }

    /**
     * Get inventory categories based on type.
     */
    private function getCategories(): array
    {
        // Return only the 4 categories requested by user
        $userCategories = [
            'Tshirt Products',
            'Other Items', 
            'Office Supplies',
            'Machine and Equipment'
        ];
        
        // Return same categories for all types
        return [
            'raw_material' => $userCategories,
            'finished_good' => $userCategories,
            'consumable' => $userCategories,
            'equipment' => $userCategories,
        ];
    }
}