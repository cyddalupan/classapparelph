<?php

# API: Products for sales box (public)
Route::get("/api/products-for-box/{boxType}", [App\Http\Controllers\ProductPricingController::class, "getProductsForBox"])->name("product-pricing.api.products-for-box");
Route::get("/api/filter-options/{boxType}", [App\Http\Controllers\ProductPricingController::class, "getFilterOptions"])->name("product-pricing.api.filter-options");

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinanceDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// TEST PAGE FOR DEBUGGING
Route::get('/test-navigation', function () {
    return view('test-navigation');
})->name('test-navigation');

Route::middleware('auth')->group(function () {
    // INVENTORY CATEGORY SELECTION PAGE
    Route::get('/inventory/select-category', function () {
        // Log access for debugging
        \Log::info('Category selection page accessed', [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referer' => request()->header('referer'),
            'time' => now()
        ]);
        
        return view('inventory.select-category');
    })->name('inventory.select-category');
    
    // SIMPLE TEST VERSION (no JavaScript)
    Route::get('/inventory/select-category-simple', function () {
        return view('inventory.select-category-simple');
    })->name('inventory.select-category-simple');
    
    // ULTRA SIMPLE VERSION (standalone HTML, no Laravel layout)
    Route::get('/inventory/select-category-test', function () {
        return view('inventory.select-category-ultra-simple');
    })->name('inventory.select-category-test');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Printing Pricing Calculator
    Route::get('/productpricing/printing', [App\Http\Controllers\PrintingPricingController::class, 'index'])->name('printing.pricing');
    Route::get('/productpricing/printing/test', [App\Http\Controllers\PrintingPricingController::class, 'testIndex'])->name('printing.pricing-test');
Route::get('/printing-calculator', function() {
        return view('printing.public_calculator');
    })->name('printing.public');
    
    // Rule Editor
    Route::get('/productpricing/printing/rules', [App\Http\Controllers\PrintingPricingController::class, 'editRules'])->name('printing.rules');
    Route::post('/productpricing/printing/rules/prices', [App\Http\Controllers\PrintingPricingController::class, 'updatePrices'])->name('printing.update-prices');
    Route::post('/productpricing/printing/rules/combos', [App\Http\Controllers\PrintingPricingController::class, 'updateCombos'])->name('printing.update-combos');
    Route::post('/productpricing/printing/rules/bulk', [App\Http\Controllers\PrintingPricingController::class, 'updateBulk'])->name('printing.update-bulk');
    Route::post('/productpricing/printing/calculate', [App\Http\Controllers\PrintingPricingController::class, 'calculate'])->name('printing.calculate');
    Route::post('/productpricing/printing/prices', [App\Http\Controllers\PrintingPricingController::class, 'storePrice'])->name('printing.store-price');
    Route::post('/productpricing/printing/combos', [App\Http\Controllers\PrintingPricingController::class, 'storeComboDiscount'])->name('printing.store-combo');
    Route::post('/productpricing/printing/upgrades', [App\Http\Controllers\PrintingPricingController::class, 'storeSizeUpgrade'])->name('printing.store-upgrade');
    Route::post('/productpricing/printing/bulk', [App\Http\Controllers\PrintingPricingController::class, 'storeBulkDiscount'])->name('printing.store-bulk');
    
    // Add/Delete print prices
    Route::post('/productpricing/printing/prices/add', [App\Http\Controllers\PrintingPricingController::class, 'addPrice'])->name('printing.add-price');
    Route::delete('/productpricing/printing/prices/{id}', [App\Http\Controllers\PrintingPricingController::class, 'deletePrice'])->name('printing.delete-price');
    Route::get("/productpricing/printing/get-product-pricing", [App\Http\Controllers\PrintingPricingController::class, "getProductPricing"])->name("printing.get-product-pricing");
    Route::post('/productpricing/printing/prices/sync', [App\Http\Controllers\PrintingPricingController::class, 'syncFromProductPricing'])->name('printing.sync-prices');
    
    // Pricing Rules Dashboard
    Route::get('/productpricing/rules', [App\Http\Controllers\PricingRulesController::class, 'index'])->name('pricing.rules');
    Route::get('/productpricing/rules/printing', [App\Http\Controllers\PricingRulesController::class, 'printingRules'])->name('pricing.rules.printing');
    Route::get('/productpricing/rules/bulk', [App\Http\Controllers\PricingRulesController::class, 'bulkRules'])->name('pricing.rules.bulk');
    Route::get('/productpricing/rules/sublimation', [App\Http\Controllers\PricingRulesController::class, 'sublimationRules'])->name('pricing.rules.sublimation');
    Route::get('/productpricing/rules/tarpaulin', [App\Http\Controllers\PricingRulesController::class, 'tarpaulinRules'])->name('pricing.rules.tarpaulin');
    Route::get('/productpricing/rules/embroidery', [App\Http\Controllers\PricingRulesController::class, 'embroideryRules'])->name('pricing.rules.embroidery');
    Route::get('/productpricing/rules/sticker', [App\Http\Controllers\PricingRulesController::class, 'stickerRules'])->name('pricing.rules.sticker');
    
    // Admin Routes
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
        
        Route::get('/admin/settings', function () {
            return view('admin.settings');
        })->name('admin.settings');
        
        // Sales Agents Management
        Route::prefix('admin/sales-agents')->name('sales-agents.')->group(function () {
            Route::get('/', [App\Http\Controllers\UserController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\UserController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\UserController::class, 'store'])->name('store');
            Route::get('/{user}', [App\Http\Controllers\UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('destroy');
        });
        
        // API for sales agents dropdown
        Route::get('/api/sales-agents', [App\Http\Controllers\UserController::class, 'apiSalesAgents'])->name('api.sales-agents');
    });
    
    // Business Feature Routes
    Route::get('/orders', function () {
        return view('orders.index');
    })->name('orders.index');
    
    Route::get('/orders/{id}', function ($id) {
        return view('orders.show', ['id' => $id]);
    })->name('orders.show');
    
    // Product Management Routes
    Route::resource('products', \App\Http\Controllers\ProductController::class)->except(['show']);
    
    // Product List Route (Same as Inventory Action - Requires Authentication)
    Route::get('/productlist', function () {
        return view('productlist.index');
    })->name('productlist.index');
    
    // Additional product routes
    Route::get('/products/trashed', [\App\Http\Controllers\ProductController::class, 'trashed'])
        ->name('products.trashed');
    
    Route::post('/products/{product}/restore', [\App\Http\Controllers\ProductController::class, 'restore'])
        ->name('products.restore');
    
    Route::delete('/products/{product}/force-delete', [\App\Http\Controllers\ProductController::class, 'forceDelete'])
        ->name('products.force-delete');
    
    Route::post('/products/{product}/update-stock', [\App\Http\Controllers\ProductController::class, 'updateStock'])
        ->name('products.update-stock');
    
    Route::get('/customers', function () {
        return view('customers.index');
    })->name('customers.index');
    
    Route::get('/customers/{id}', function ($id) {
        return view('customers.show', ['id' => $id]);
    })->name('customers.show');
    
    Route::get('/design-studio', function () {
        return view('design.studio');
    })->name('design.studio');
    
    Route::get('/analytics', function () {
        return view('analytics.dashboard');
    })->name('analytics.dashboard');
    
    Route::get('/inventory', [\App\Http\Controllers\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [\App\Http\Controllers\InventoryController::class, 'create'])->name('inventory.create');
    
    // INVENTORY ACTION - Direct link to create page with shirt category
    Route::get('/inventoryaction', function () {
        // Use the clean inventory creation form
        return view('inventory.create-clean');
    })->name('inventory.action');
    
    // UNIFIED INVENTORY MANAGEMENT - Single page for all inventory operations
    Route::get('/inventory/unified', function () {
        return view('inventory.unified-simple');
    })->name('inventory.unified');
    
    // INVENTORIES PAGE - Main inventory listing with category selection
    Route::get('/inventories', [App\Http\Controllers\InventoriesController::class, 'index'])
        ->name('inventories.index');
    
    // MASTER ITEMS PAGE - Product catalog management (no stock quantities)
    Route::get('/master-items', [App\Http\Controllers\MasterItemsController::class, 'index'])
        ->name('master-items.index');
    Route::get('/master-items/create', [App\Http\Controllers\MasterItemsController::class, 'create'])
        ->name('master-items.create');
    Route::post('/master-items', [App\Http\Controllers\MasterItemsController::class, 'store'])
        ->name('master-items.store');
    Route::get('/master-items/{masterItem}/edit', [App\Http\Controllers\MasterItemsController::class, 'edit'])
        ->name('master-items.edit');
    Route::put('/master-items/{masterItem}', [App\Http\Controllers\MasterItemsController::class, 'update'])
        ->name('master-items.update');
    Route::delete('/master-items/{masterItem}', [App\Http\Controllers\MasterItemsController::class, 'destroy'])
        ->name('master-items.destroy');
    
    // Product Pricing Routes
    Route::get('/productpricing', [App\Http\Controllers\ProductPricingController::class, 'index'])
        ->name('product-pricing.index');
    Route::get('/productpricing/{id}/edit', [App\Http\Controllers\ProductPricingController::class, 'edit'])
        ->name('product-pricing.edit');
    Route::put('/productpricing/{id}', [App\Http\Controllers\ProductPricingController::class, 'update'])
        ->name('product-pricing.update');
    Route::post('/productpricing/bulk-update', [App\Http\Controllers\ProductPricingController::class, 'bulkUpdate'])
        ->name('product-pricing.bulk-update');
    
    // Volume Discount Routes
    Route::get('/productpricing/{id}/volume-discounts', [App\Http\Controllers\ProductPricingController::class, 'volumeDiscounts'])
        ->name('product-pricing.volume-discounts');
    Route::post('/productpricing/{id}/volume-discounts', [App\Http\Controllers\ProductPricingController::class, 'storeVolumeDiscounts'])
        ->name('product-pricing.volume-discounts.store');

    # API: Products for sales box
    
    Route::post('/inventory', [\App\Http\Controllers\InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{inventory}', [\App\Http\Controllers\InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{inventory}/edit', [\App\Http\Controllers\InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{inventory}', [\App\Http\Controllers\InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{inventory}', [\App\Http\Controllers\InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::get('/inventory/trashed', [\App\Http\Controllers\InventoryController::class, 'trashed'])->name('inventory.trashed');
    Route::post('/inventory/{inventory}/restore', [\App\Http\Controllers\InventoryController::class, 'restore'])->name('inventory.restore');
    Route::delete('/inventory/{inventory}/force', [\App\Http\Controllers\InventoryController::class, 'forceDelete'])->name('inventory.forceDelete');
    Route::post('/inventory/{inventory}/stock', [\App\Http\Controllers\InventoryController::class, 'updateStock'])->name('inventory.updateStock');
    
    // New stock adjustment routes
    Route::get('/api/inventory-items', function () {
        // Debug: Log the query
        \Log::info('API /api/inventory-items called');
        
        $query = \App\Models\Inventory::select('id', 'name', 'sku', 'current_stock')
            ->whereNull('deleted_at');
        
        // Add category filter if provided
        if (request()->has('category') && request('category')) {
            $category = request('category');
            $query->where('category', $category);
            \Log::info('API filtering by category: ' . $category);
        }
        
        $items = $query->orderBy('name')->get();
            
        // Debug: Log the count and IDs
        \Log::info('API returning ' . $items->count() . ' items (excluding deleted)' . (request()->has('category') ? ' for category: ' . request('category') : ''));
        \Log::info('Item IDs: ' . $items->pluck('id')->implode(', '));
        
        return response()->json($items)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    })->name('api.inventory.items');
    
    Route::post('/inventory/adjust-stock', function (\Illuminate\Http\Request $request) {
        // Validate request
        $validated = $request->validate([
            'item_id' => 'required|exists:inventories,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:add,deduct',
            'reason' => 'nullable|string|max:500',
        ]);
        
        // Get the inventory item
        $inventory = \App\Models\Inventory::findOrFail($validated['item_id']);
        
        // Check permissions
        if (!\Illuminate\Support\Facades\Gate::allows('manage-inventory')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }
        
        // Calculate new stock
        $oldStock = $inventory->current_stock;
        
        if ($validated['type'] === 'add') {
            $newStock = $oldStock + $validated['quantity'];
        } else {
            // For deduct, ensure we don't go below 0
            $newStock = max(0, $oldStock - $validated['quantity']);
            
            if ($newStock === 0 && $validated['quantity'] > $oldStock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deduct more than available stock. Available: ' . $oldStock
                ], 400);
            }
        }
        
        // Update stock
        $inventory->current_stock = $newStock;
        $inventory->last_restocked_at = now();
        $inventory->save();
        
        // Log the transaction (optional - could create a StockTransaction model)
        // \App\Models\StockTransaction::create([
        //     'inventory_id' => $inventory->id,
        //     'user_id' => auth()->id(),
        //     'type' => $validated['type'],
        //     'quantity' => $validated['quantity'],
        //     'old_stock' => $oldStock,
        //     'new_stock' => $newStock,
        //     'reason' => $validated['reason'] ?? 'Manual adjustment',
        // ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully',
            'data' => [
                'item_id' => $inventory->id,
                'item_name' => $inventory->name,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'adjustment' => $validated['type'] === 'add' ? 
                    '+' . $validated['quantity'] : 
                    '-' . $validated['quantity']
            ]
        ]);
    })->name('inventory.adjust-stock');
    
    Route::get('/production', function () {
        return view('production.tracking');
    })->name('production.tracking');
    
    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');
    
    // Sales and Expenses System
    Route::get('/finance', [FinanceDashboardController::class, 'index'])->name('finance.dashboard');
    
    Route::get('/finance/expenses', [ExpenseController::class, 'index'])->name('finance.expenses');
    Route::post('/finance/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::put('/finance/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/finance/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::post('/finance/expenses/{expense}/mark-paid', [ExpenseController::class, 'markAsPaid'])->name('expenses.mark-paid');
    Route::get('/api/expenses', [ExpenseController::class, 'apiIndex'])->name('expenses.api');
    Route::get('/api/expenses/statistics', [ExpenseController::class, 'statistics'])->name('expenses.statistics');
    
    // Product List API
    Route::get('/api/products-by-category', function (Request $request) {
        $category = $request->query('category');
        
        if (!$category) {
            return response()->json(['error' => 'Category parameter is required'], 400);
        }
        
        // Fetch products by category from inventory table
        $products = \App\Models\Inventory::where('category', $category)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($products);
    });
    
    Route::get('/finance/sales', [App\Http\Controllers\SalesController::class, 'index'])->name('finance.sales');
    Route::get('/finance/sales/create', [App\Http\Controllers\SalesController::class, 'create'])->name('sales.create');
    Route::post('/finance/sales', [App\Http\Controllers\SalesController::class, 'store'])->name('sales.store');
    Route::get('/finance/sales/{sale}', [App\Http\Controllers\SalesController::class, 'show'])->name('sales.show');
    Route::get('/finance/sales/{sale}/edit', [App\Http\Controllers\SalesController::class, 'edit'])->name('sales.edit');
    Route::put('/finance/sales/{sale}', [App\Http\Controllers\SalesController::class, 'update'])->name('sales.update');
    Route::delete('/finance/sales/{sale}', [App\Http\Controllers\SalesController::class, 'destroy'])->name('sales.destroy');
    
    Route::get('/finance/reports', function () {
        return view('finance.reports');
    })->name('finance.reports');
    
    // Sales Agent Routes (Only for sales agents and representatives)
    Route::middleware(['auth'])->group(function () {
        Route::get('/sales/pricing', function () {
            if (!Gate::allows('access-sales-agent')) {
                abort(403, 'Unauthorized access.');
            }
            return view('sales.pricing');
        })->name('sales.pricing');
        
        Route::get('/sales/create-quick', function () {
            if (!Gate::allows('input-sales')) {
                abort(403, 'Unauthorized access.');
            }
            return view('sales.create-quick');
        })->name('sales.create-quick');
        
        Route::post('/sales/quick-store', [App\Http\Controllers\SalesController::class, 'quickStore'])->name('sales.quick-store');
    });
});

require __DIR__.'/auth.php';

// DTF Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dtf/create', [App\Http\Controllers\DtfController::class, 'create'])->name('dtf.create');
    Route::post('/dtf', [App\Http\Controllers\DtfController::class, 'store'])->name('dtf.store');
});

// JavaScript Test Route
Route::middleware(['auth'])->get('/test-js', function () {
    return view('products.test-js');
});

// Product List Test Route
Route::middleware(['auth'])->get('/test-productlist', function () {
    return view('products.test-productlist');
});

// Debug Route
Route::middleware(['auth'])->get('/debug-test', function () {
    return view('products.debug');
});

// Show Source Route
Route::middleware(['auth'])->get('/show-source', function () {
    return view('products.show-source');
});

// Simple Test Route
Route::middleware(['auth'])->get('/simple-test', function () {
    return view('products.simple-test');
});

// Inventory Style Test Route
Route::middleware(['auth'])->get('/inventory-style-test', function () {
    return view('products.inventory-style-test');
});

// Fixed Test Route
Route::middleware(['auth'])->get('/fixed-test', function () {
    return view('products.fixed-test');
});
Route::get('/test-modal', function() { return view('test'); });
Route::get('/inventory-clean', function() { return view('inventory.create-clean'); });
Route::post('/inventory/shirt-products', function(Request $request) {

    // Validate the request
    $validated = $request->validate([
        'sku' => 'required|string|max:50|unique:inventories,sku',
        'brand' => 'required|string|max:100',
        'type' => 'required|string|max:50',
        'color' => 'required|string|max:50',
        'size' => 'required|string|max:10',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'supplier' => 'nullable|string|max:100',
        'shop' => 'nullable|string|max:100',
        'notes' => 'nullable|string',
    ]);

    // Create the inventory item
    $inventory = \App\Models\Inventory::create([
        'sku' => $validated['sku'],
        'name' => $validated['brand'] . ' ' . $validated['type'] . ' - ' . $validated['color'] . ' (' . $validated['size'] . ')',
        'description' => $validated['notes'] ?? '',
        'category' => 'Shirt Products',
        'type' => 'finished_good',
        'unit_price' => $validated['price'],
        'unit_of_measure' => 'pieces',
        'current_stock' => $validated['stock'],
        'minimum_stock' => 0,
        'supplier_id' => null,
        'storage_location' => $validated['shop'] ?? 'Main Store',
        'is_active' => true,
    ]);

    // Return success response
    return response()->json([
        'success' => true,
        'message' => 'Shirt product added successfully!',
        'data' => [
            'id' => $inventory->id,
            'sku' => $inventory->sku,
            'name' => $inventory->name,
            'price' => $inventory->unit_price,
            'stock' => $inventory->current_stock,
        ]
    ], 201);
})->name('inventory.shirt-products.store');


Route::get('/inventorylist', function() {
    return view('inventory.create-clean');
})->name('inventory.list');

        // Customer API Routes for Prototype
        Route::get("/api/customers/check", function (\Illuminate\Http\Request $request) {
            $phone = $request->query("phone");
            $customer = \App\Models\Customer::where("phone", $phone)->first();
            
            if ($customer) {
                return response()->json([
                    "exists" => true,
                    "customer" => $customer
                ]);
            }
            
            return response()->json(["exists" => false]);
        });
        
        Route::get("/api/customers/search", function (\Illuminate\Http\Request $request) {
            $searchTerm = $request->query("q");
            $customers = \App\Models\Customer::search($searchTerm)
                ->active()
                ->limit(10)
                ->get();
            
            return response()->json(["customers" => $customers]);
        });
        
        Route::get("/api/customers/{id}", function ($id) {
            $customer = \App\Models\Customer::find($id);
            
            if (!$customer) {
                return response()->json(["error" => "Customer not found"], 404);
            }
            
            // Add calculated fields
            $customer->getDaysSinceLastOrder = $customer->getDaysSinceLastOrder();
            
            return response()->json($customer);
        });
        // PROTOTYPE SALES SYSTEM
        Route::get('/sales/prototype', function () {
            if (!Gate::allows('input-sales')) {
                abort(403, 'Unauthorized access.');
            }
            return view('sales.prototype.index');
        })->name('sales.prototype');
        
        Route::get('/sales/prototype/create', [App\Http\Controllers\PrototypeSalesController::class, 'create'])->name('sales.prototype.create');
        Route::post('/sales/prototype', [App\Http\Controllers\PrototypeSalesController::class, 'store'])->name('sales.prototype.store');
        
        // Cart system
        Route::get('/sales/prototype/cart-create', [App\Http\Controllers\PrototypeSalesController::class, 'cartCreate'])->name('sales.prototype.cart-create');
        
        // Garment Test Page
        Route::get('/sales/prototype/garment-test', function () {
            return view('sales.prototype.garment_test');
        })->name('sales.prototype.garment-test');
        
        // Printing API for garment modal
        Route::get('/api/printing/options/{type}', [App\Http\Controllers\PrintingPricingController::class, 'getPrintingOptions'])->name('api.printing.options');
        Route::post('/api/printing/calculate', [App\Http\Controllers\PrintingPricingController::class, 'calculateModal'])->name('api.printing.calculate-modal');
        
        Route::get('/sales/prototype/{id}', [App\Http\Controllers\PrototypeSalesController::class, 'show'])->name('sales.prototype.show');
        Route::get('/sales/prototype/{id}/edit', [App\Http\Controllers\PrototypeSalesController::class, 'edit'])->name('sales.prototype.edit');
        Route::put('/sales/prototype/{id}', [App\Http\Controllers\PrototypeSalesController::class, 'update'])->name('sales.prototype.update');
        Route::delete('/sales/prototype/{id}', [App\Http\Controllers\PrototypeSalesController::class, 'destroy'])->name('sales.prototype.destroy');
        
        // KANBAN routes
        Route::get('/sales/prototype/kanban/{department?}', [App\Http\Controllers\PrototypeSalesController::class, 'kanban'])->name('sales.prototype.kanban');
        Route::post('/sales/prototype/{id}/update-status', [App\Http\Controllers\PrototypeSalesController::class, 'updateStatus'])->name('sales.prototype.update-status');
        
        // Payment verification
        Route::post('/sales/prototype/{id}/verify-payment', [App\Http\Controllers\PrototypeSalesController::class, 'verifyPayment'])->name('sales.prototype.verify-payment');


        



