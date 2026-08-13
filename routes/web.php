<?php

# API: Products for sales box (public)
Route::get("/api/products-for-box/{boxType}", [App\Http\Controllers\ProductPricingController::class, "getProductsForBox"])->name("product-pricing.api.products-for-box");
Route::get("/api/filter-options/{boxType}", [App\Http\Controllers\ProductPricingController::class, "getFilterOptions"])->name("product-pricing.api.filter-options");
Route::get("/api/sublimation-prices", [App\Http\Controllers\PricingRulesController::class, 'getSublimationPrices'])->name('api.sublimation-prices');

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinanceDashboardController;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminUserController;
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
    Route::post('/productpricing/rules/sublimation/prices', [App\Http\Controllers\PricingRulesController::class, 'updateSublimationPrices'])->name('pricing.rules.sublimation.prices');
    Route::post('/productpricing/rules/sublimation/bulk', [App\Http\Controllers\PricingRulesController::class, 'updateSublimationBulk'])->name('pricing.rules.sublimation.bulk');
    Route::post('/productpricing/rules/sublimation/prices/add', [App\Http\Controllers\PricingRulesController::class, 'addSublimationPrice'])->name('pricing.rules.sublimation.add-price');
    Route::post('/productpricing/rules/sublimation/prices/delete', [App\Http\Controllers\PricingRulesController::class, 'deleteSublimationPrice'])->name('pricing.rules.sublimation.delete-price');
    Route::post('/productpricing/rules/sublimation/connect', [App\Http\Controllers\PricingRulesController::class, 'addConnectedProduct'])->name('pricing.rules.sublimation.connect');
    Route::delete('/productpricing/rules/sublimation/disconnect/{id}', [App\Http\Controllers\PricingRulesController::class, 'removeConnectedProduct'])->name('pricing.rules.sublimation.disconnect');
    
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
        
        // ======== User Management (All Roles) ========
        Route::prefix('admin/users')->name('admin.users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/create', [AdminUserController::class, 'create'])->name('create');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('toggle-active');
        });
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
    
    Route::get('/customers', [App\Http\Controllers\CustomerController::class, 'index'])->name('customers.index');
    
    Route::get('/customers/{id}', [App\Http\Controllers\CustomerController::class, 'show'])->name('customers.show');
    
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
            'item_id' => 'required|exists:master_items,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:add,deduct',
            'department_id' => 'nullable|exists:sales_departments,id',
            'reason' => 'nullable|string|max:500',
        ]);
        
        $departmentId = $validated['department_id'] ?? null;
        
        // Check permissions
        if (!\Illuminate\Support\Facades\Gate::allows('manage-inventory')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }
        
        // If a specific department is selected, update department-level stock
        if ($departmentId) {
            $deptItem = \Illuminate\Support\Facades\DB::table('department_master_items')
                ->where('master_item_id', $validated['item_id'])
                ->where('department_id', $departmentId)
                ->first();
            
            if (!$deptItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is not assigned to this department.'
                ], 400);
            }
            
            $oldStock = $deptItem->current_stock ?? 0;
            
            if ($validated['type'] === 'add') {
                $newStock = $oldStock + $validated['quantity'];
            } else {
                $newStock = max(0, $oldStock - $validated['quantity']);
                if ($newStock === 0 && $validated['quantity'] > $oldStock) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot deduct more than available stock. Available: ' . $oldStock
                    ], 400);
                }
            }
            
            \Illuminate\Support\Facades\DB::table('department_master_items')
                ->where('master_item_id', $validated['item_id'])
                ->where('department_id', $departmentId)
                ->update([
                    'current_stock' => $newStock,
                    'last_restocked_at' => now(),
                ]);
            
            // Log the activity
            \App\Models\InventoryActivityLog::create([
                'master_item_id' => $validated['item_id'],
                'department_id' => $departmentId,
                'action_type' => $validated['type'] === 'add' ? 'add_stock' : 'deduct_stock',
                'item_name' => \App\Models\MasterItem::find($validated['item_id'])?->name ?? 'Unknown',
                'sku' => \App\Models\MasterItem::find($validated['item_id'])?->sku ?? null,
                'category' => \App\Models\MasterItem::find($validated['item_id'])?->category ?? null,
                'old_value' => $oldStock,
                'new_value' => $newStock,
                'quantity' => $validated['quantity'],
                'user_id' => auth()->id(),
                'notes' => $validated['reason'] ?? null,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Department stock updated successfully',
                'data' => [
                    'item_id' => (int)$validated['item_id'],
                    'department_id' => (int)$departmentId,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'adjustment' => $validated['type'] === 'add' ? 
                        '+' . $validated['quantity'] : 
                        '-' . $validated['quantity']
                ]
            ]);
        }
        
        // No department — update global master_items stock
        $inventory = \App\Models\MasterItem::findOrFail($validated['item_id']);
        
        $oldStock = $inventory->current_stock;
        
        if ($validated['type'] === 'add') {
            $newStock = $oldStock + $validated['quantity'];
        } else {
            $newStock = max(0, $oldStock - $validated['quantity']);
            if ($newStock === 0 && $validated['quantity'] > $oldStock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deduct more than available stock. Available: ' . $oldStock
                ], 400);
            }
        }
        
        $inventory->current_stock = $newStock;
        $inventory->last_restocked_at = now();
        $inventory->save();
        
        // Log the activity
        \App\Models\InventoryActivityLog::create([
            'master_item_id' => $inventory->id,
            'action_type' => $validated['type'] === 'add' ? 'add_stock' : 'deduct_stock',
            'item_name' => $inventory->name,
            'sku' => $inventory->sku,
            'category' => $inventory->category,
            'old_value' => $oldStock,
            'new_value' => $newStock,
            'quantity' => $validated['quantity'],
            'user_id' => auth()->id(),
            'notes' => $validated['reason'] ?? null,
        ]);
        
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
    
    // API: Get recent inventory activity history
    Route::get('/api/inventory-activity', function () {
        $limit = request()->get('limit', 30);
        $departmentId = request()->get('department_id');
        
        $query = \App\Models\InventoryActivityLog::with('user');
        
        // Filter by department if specified
        if ($departmentId && $departmentId !== 'all' && $departmentId !== 'unassigned') {
            $query->where('department_id', $departmentId);
        } elseif ($departmentId === 'unassigned') {
            // Show activities that aren't tied to any department
            $query->whereNull('department_id');
        }
        // 'all' or no filter = show everything
        
        $logs = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                $deptName = null;
                if ($log->department_id) {
                    $dept = \Illuminate\Support\Facades\DB::table('sales_departments')
                        ->where('id', $log->department_id)
                        ->value('name');
                    $deptName = $dept;
                }
                return [
                    'id' => $log->id,
                    'action_type' => $log->action_type,
                    'item_name' => $log->item_name,
                    'sku' => $log->sku,
                    'category' => $log->category,
                    'department_id' => $log->department_id,
                    'department_name' => $deptName,
                    'old_value' => $log->old_value,
                    'new_value' => $log->new_value,
                    'quantity' => $log->quantity,
                    'user_name' => $log->user ? $log->user->name : 'System',
                    'notes' => $log->notes,
                    'created_at' => $log->created_at->diffForHumans(),
                    'created_at_raw' => $log->created_at->toDateTimeString(),
                ];
            });
        
        return response()->json($logs);
    })->name('api.inventory.activity');
    
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
    Route::get('/api/products-by-category-count', function (Request $request) {
        $category = $request->query('category');
        $departmentId = $request->query('department_id');
        $unassigned = $request->query('unassigned');
        
        if (!$category) {
            return response()->json(['error' => 'Category parameter is required'], 400);
        }
        
        $query = \App\Models\MasterItem::where('category', $category)
            ->whereNull('deleted_at');
        
        if ($departmentId) {
            // Count items assigned to specific department
            $query->whereExists(function ($q) use ($departmentId) {
                $q->select(\DB::raw(1))
                  ->from('department_master_items')
                  ->whereColumn('department_master_items.master_item_id', 'master_items.id')
                  ->where('department_master_items.department_id', $departmentId);
            });
        } elseif ($unassigned) {
            // Count items NOT assigned to any department
            $query->whereNotExists(function ($q) {
                $q->select(\DB::raw(1))
                  ->from('department_master_items')
                  ->whereColumn('department_master_items.master_item_id', 'master_items.id');
            });
        }
        
        $count = $query->count();
        
        return response()->json(['count' => $count, 'category' => $category]);
    });
    
    Route::get('/api/products-by-category', function (Request $request) {
        $category = $request->query('category');
        
        if (!$category) {
            return response()->json(['error' => 'Category parameter is required'], 400);
        }
        
        $query = \DB::table('master_items')
            ->leftJoin('department_master_items', 'master_items.id', '=', 'department_master_items.master_item_id')
            ->whereNull('master_items.deleted_at')
            ->where('master_items.category', $category)
            ->select(
                'master_items.id',
                'master_items.name',
                'master_items.sku',
                'master_items.brand',
                'master_items.category',
                'master_items.unit_price',
                'master_items.description',
                \DB::raw('COALESCE(sum(department_master_items.current_stock), master_items.current_stock) as current_stock'),
                \DB::raw('COALESCE(sum(department_master_items.minimum_stock), master_items.minimum_stock) as minimum_stock'),
                'master_items.created_at'
            )
            ->groupBy('master_items.id')
            ->orderBy('master_items.name', 'asc');
        
        // Optional search filter
        $search = $request->query('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('master_items.name', 'like', "%{$search}%")
                  ->orWhere('master_items.sku', 'like', "%{$search}%")
                  ->orWhere('master_items.brand', 'like', "%{$search}%");
            });
        }
        
        $products = $query->get();
        
        // Role-based pricing: non-admin users see sales_team_price instead of unit_price
        $user = $request->user();
        $isNonAdmin = $user && !$user->isAdmin();
        
        if ($isNonAdmin) {
            $products->transform(function ($item) {
                $item->unit_price = $item->sales_team_price ?? $item->unit_price;
                return $item;
            });
        }
        
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
            $query = \App\Models\Customer::active()->with('creator');
            
            // Text search (keep using scopeSearch for fulltext) 
            if ($q = $request->query('q')) {
                $query->search($q);
            }
            
            // Tier filter
            if ($tier = $request->query('tier')) {
                $query->where('customer_tier', $tier);
            }
            
            // Total spent range
            if ($minSpent = $request->query('min_spent')) {
                $query->where('total_spent', '>=', (float) $minSpent);
            }
            if ($maxSpent = $request->query('max_spent')) {
                $query->where('total_spent', '<=', (float) $maxSpent);
            }
            
            // Last order date range
            if ($dateFrom = $request->query('date_from')) {
                $query->whereDate('last_order_date', '>=', $dateFrom);
            }
            if ($dateTo = $request->query('date_to')) {
                $query->whereDate('last_order_date', '<=', $dateTo);
            }
            
            // Marketplace filter
            if ($marketplace = $request->query('marketplace')) {
                $query->where('marketplace', $marketplace);
            }

            // Outstanding balance filter (based on non-archived orders with balance_due > 0)
            if ($balance = $request->query('balance')) {
                if ($balance === 'has') {
                    $query->whereExists(function ($q) {
                        $q->select(\Illuminate\Support\Facades\DB::raw(1))
                            ->from('prototype_sales')
                            ->whereColumn('prototype_sales.customer_id', 'customers.id')
                            ->whereNull('prototype_sales.archived_at')
                            ->where('prototype_sales.balance_due', '>', 0);
                    });
                } elseif ($balance === 'none') {
                    $query->whereNotExists(function ($q) {
                        $q->select(\Illuminate\Support\Facades\DB::raw(1))
                            ->from('prototype_sales')
                            ->whereColumn('prototype_sales.customer_id', 'customers.id')
                            ->whereNull('prototype_sales.archived_at')
                            ->where('prototype_sales.balance_due', '>', 0);
                    });
                }
            }
            
            $customers = $query->orderBy('total_spent', 'desc')
                ->limit(50)
                ->get();

            // Outstanding balance per customer (how much is still collectible)
            $outstanding = \Illuminate\Support\Facades\DB::table('prototype_sales')
                ->whereNull('archived_at')
                ->where('balance_due', '>', 0)
                ->whereIn('customer_id', $customers->pluck('id'))
                ->groupBy('customer_id')
                ->selectRaw('customer_id, SUM(balance_due) as total_outstanding')
                ->pluck('total_outstanding', 'customer_id');

            foreach ($customers as $c) {
                $c->outstanding_balance = (float) ($outstanding[$c->id] ?? 0);
            }
            
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
        Route::put("/api/customers/{id}", [\App\Http\Controllers\CustomerController::class, 'update']);
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
        
        



        // CALENDAR route (MUST be before {id} route)
        Route::get('/sales/prototype/calendar', [App\Http\Controllers\PrototypeSalesController::class, 'calendar'])->name('sales.prototype.calendar');
        Route::post('/sales/prototype/calendar-data', [App\Http\Controllers\PrototypeSalesController::class, 'calendarData'])->name('sales.prototype.calendar-data');
        Route::post('/sales/prototype/{id}/reschedule', [App\Http\Controllers\PrototypeSalesController::class, 'reschedule'])->name('sales.prototype.reschedule');

        // LIST route (MUST be before {id} route)
        Route::get('/sales/prototype/list', [App\Http\Controllers\PrototypeSalesController::class, 'list'])->name('sales.prototype.list');

        // DELAY REVIEW route (manager/admin review of delayed sales feedback)
        Route::get('/sales/prototype/{id}/delay-review', [App\Http\Controllers\PrototypeSalesController::class, 'delayReview'])->name('sales.prototype.delay-review');

        // DELAY LIST route — all delayed sales in one page (with or without feedback)
        Route::get('/sales/prototype/delays', [App\Http\Controllers\PrototypeSalesController::class, 'delayList'])->name('sales.prototype.delays');

        // ARCHIVE routes
        Route::get('/sales/prototype/archived', [App\Http\Controllers\PrototypeSalesController::class, 'archived'])->name('sales.prototype.archived');
        Route::post('/sales/prototype/{id}/archive', [App\Http\Controllers\PrototypeSalesController::class, 'archive'])->name('sales.prototype.archive');
        Route::post('/sales/prototype/{id}/restore', [App\Http\Controllers\PrototypeSalesController::class, 'restore'])->name('sales.prototype.restore');

        // KANBAN routes
        Route::get('/sales/prototype/kanban/{department?}', [App\Http\Controllers\PrototypeSalesController::class, 'kanban'])->name('sales.prototype.kanban');
        Route::post('/sales/prototype/{id}/update-status', [App\Http\Controllers\PrototypeSalesController::class, 'updateStatus'])->name('sales.prototype.update-status');
        Route::post('/sales/prototype/{id}/priority', [App\Http\Controllers\PrototypeSalesController::class, 'updatePriority'])->name('sales.prototype.priority');

        Route::get('/sales/prototype/dashboard', [App\Http\Controllers\PrototypeSalesController::class, 'salesDashboard'])->name('sales.prototype.dashboard');
        Route::get('/sales/prototype/{id}/details', [App\Http\Controllers\PrototypeSalesController::class, 'details'])->name('sales.prototype.details');
        Route::get('/sales/prototype/{id}/print-slip', [App\Http\Controllers\PrototypeSalesController::class, 'printSlip'])->name('sales.prototype.print-slip');
        Route::get('/sales/prototype/{id}/print-slip/pdf', [App\Http\Controllers\PrototypeSalesController::class, 'printSlipPdf'])->name('sales.prototype.print-slip.pdf');
        Route::get('/api/production/checklist/{id}', [App\Http\Controllers\PrototypeSalesController::class, 'getProductionChecklist'])->name('api.production.checklist.get');
        Route::post('/api/production/checklist/{id}/save', [App\Http\Controllers\PrototypeSalesController::class, 'saveProductionChecklist'])->name('api.production.checklist.save');
        Route::get('/api/production/additional/{id}', [App\Http\Controllers\PrototypeSalesController::class, 'getAdditionalProductionChecklist'])->name('api.production.additional.get');
        Route::get('/sales/prototype/{id}', [App\Http\Controllers\PrototypeSalesController::class, 'show'])->name('sales.prototype.show');
        Route::get('/sales/prototype/{id}/edit', [App\Http\Controllers\PrototypeSalesController::class, 'edit'])->name('sales.prototype.edit');
        Route::put('/sales/prototype/{id}', [App\Http\Controllers\PrototypeSalesController::class, 'update'])->name('sales.prototype.update');
        Route::delete('/sales/prototype/{id}', [App\Http\Controllers\PrototypeSalesController::class, 'destroy'])->name('sales.prototype.destroy');

        // ======== Agent Routes (Sales Team Dashboard & Simplified Sales) ========
        Route::get('/sales/team', [App\Http\Controllers\PrototypeSalesController::class, 'agentDashboard'])->middleware('auth')->name('sales.team.dashboard');
        Route::post('/sales/team/{id}/delay', [App\Http\Controllers\PrototypeSalesController::class, 'markDelayed'])->middleware('auth')->name('sales.team.delay');
        Route::get('/sales/prototype/agent/create', [App\Http\Controllers\PrototypeSalesController::class, 'agentCreate'])->name('sales.prototype.agent.create');
        Route::post('/sales/prototype/agent', [App\Http\Controllers\PrototypeSalesController::class, 'agentStore'])->name('sales.prototype.agent.store');
        Route::get('/sales/prototype/{id}/agent/payment', [App\Http\Controllers\PrototypeSalesController::class, 'agentAddPayment'])->name('sales.prototype.agent.payment');
        Route::post('/sales/prototype/{id}/agent/payment', [App\Http\Controllers\PrototypeSalesController::class, 'agentPaymentStore'])->name('sales.prototype.agent.payment.store');
        
        // Payment verification
        Route::post('/sales/prototype/{id}/verify-payment', [App\Http\Controllers\PrototypeSalesController::class, 'verifyPayment'])->name('sales.prototype.verify-payment');
        Route::get('/sales/verification', [App\Http\Controllers\PrototypeSalesController::class, 'paymentVerification'])->name('sales.verification');
        Route::get('/sales/cash-flow', [App\Http\Controllers\PrototypeSalesController::class, 'cashFlow'])->name('sales.cash-flow');
        Route::get('/sales/audit-logs/{saleId?}', [App\Http\Controllers\PrototypeSalesController::class, 'getAuditLogs'])->name('sales.audit-logs');
        Route::get('/sales/account-history/{accountId}', [App\Http\Controllers\PrototypeSalesController::class, 'getAccountHistory'])->name('sales.account-history');
        
        // Add-on request routes
        Route::get('/sales/prototype/addon/pending', [App\Http\Controllers\SaleAddonController::class, 'allPending'])->name('sales.prototype.addon.all-pending');
        Route::get('/sales/prototype/addon/pending-count', [App\Http\Controllers\SaleAddonController::class, 'pendingCount'])->name('sales.prototype.addon.pending-count');
        Route::get('/sales/prototype/{id}/addon/pending', [App\Http\Controllers\SaleAddonController::class, 'pending'])->name('sales.prototype.addon.pending');
        Route::post('/sales/prototype/{id}/addon/request', [App\Http\Controllers\SaleAddonController::class, 'request'])->name('sales.prototype.addon.request');
        Route::post('/sales/prototype/addon/{requestId}/approve', [App\Http\Controllers\SaleAddonController::class, 'approve'])->name('sales.prototype.addon.approve');
        Route::post('/sales/prototype/addon/{requestId}/reject', [App\Http\Controllers\SaleAddonController::class, 'reject'])->name('sales.prototype.addon.reject');
        
        // Edit Transaction (Add/Remove/Change items during production)
        Route::get('/sales/prototype/{id}/edit-items', [App\Http\Controllers\PrototypeSalesController::class, 'editItems'])->name('sales.prototype.edit-items');
        Route::post('/sales/prototype/{id}/submit-change', [App\Http\Controllers\PrototypeSalesController::class, 'submitChange'])->name('sales.prototype.submit-change');
        Route::post('/sales/prototype/change/{changeId}/approve', [App\Http\Controllers\PrototypeSalesController::class, 'approveChange'])->name('sales.prototype.approve-change');
        Route::post('/sales/prototype/change/{changeId}/reject', [App\Http\Controllers\PrototypeSalesController::class, 'rejectChange'])->name('sales.prototype.reject-change');
        Route::post('/sales/prototype/{id}/add-comment', [App\Http\Controllers\PrototypeSalesController::class, 'addComment'])->name('sales.prototype.add-comment');
        Route::get('/sales/prototype/{id}/audit-history', [App\Http\Controllers\PrototypeSalesController::class, 'auditHistory'])->name('sales.prototype.audit-history');
        Route::post('/sales/prototype/{id}/add-product', [App\Http\Controllers\PrototypeSalesController::class, 'addProduct'])->name('sales.prototype.add-product');
        Route::post('/sales/prototype/{id}/reprocess-order', [App\Http\Controllers\PrototypeSalesController::class, 'reprocessOrder'])->name('sales.prototype.reprocess-order');
        Route::post('/sales/prototype/refund/{id}', [App\Http\Controllers\PrototypeSalesController::class, 'submitRefund'])->name('sales.prototype.submit-refund');
        Route::post('/sales/prototype/{id}/upload-design-image', [App\Http\Controllers\PrototypeSalesController::class, 'uploadDesignImage'])->name('sales.prototype.upload-design-image');
        Route::post('/sales/prototype/{id}/design-image/delete', [App\Http\Controllers\PrototypeSalesController::class, 'deleteDesignImage'])->name('sales.prototype.delete-design-image');
        Route::post('/sales/prototype/{id}/upload-mockup', [App\Http\Controllers\PrototypeSalesController::class, 'uploadMockup'])->name('sales.prototype.upload-mockup');
        Route::post('/sales/prototype/{id}/mockup/delete', [App\Http\Controllers\PrototypeSalesController::class, 'deleteMockup'])->name('sales.prototype.delete-mockup');
        Route::post('/sales/prototype/{id}/mockup/set-main', [App\Http\Controllers\PrototypeSalesController::class, 'setMainMockup'])->name('sales.prototype.set-main-mockup');
        Route::post('/sales/prototype/{id}/notify-agent', [App\Http\Controllers\PrototypeSalesController::class, 'notifyAgent'])->name('sales.prototype.notify-agent');
        Route::post('/sales/prototype/notifications/{id}/respond', [App\Http\Controllers\PrototypeSalesController::class, 'respondUrgent'])->name('sales.prototype.respond-urgent');
        Route::post('/sales/prototype/{id}/notify-verifier', [App\Http\Controllers\PrototypeSalesController::class, 'notifyVerifier'])->name('sales.prototype.notify-verifier');
        Route::post('/sales/prototype/notifications/{id}/read', [App\Http\Controllers\PrototypeSalesController::class, 'notificationRead'])->name('sales.prototype.notification-read');
        Route::post('/sales/prototype/notifications/read-all', [App\Http\Controllers\PrototypeSalesController::class, 'notificationsReadAll'])->name('sales.prototype.notifications-read-all');
        Route::post('/sales/prototype/refund/{id}/process', [App\Http\Controllers\PrototypeSalesController::class, 'processRefund'])->name('sales.prototype.process-refund');
        Route::get('/sales/refunds', [App\Http\Controllers\PrototypeSalesController::class, 'refundList'])->name('sales.prototype.refunds');

        // Department Inventory Management (iPrint & others)
        Route::middleware(['auth'])->group(function () {
            Route::get('/api/departments', [App\Http\Controllers\DepartmentInventoryController::class, 'departments'])->name('api.departments.list');
            Route::get('/api/department-inventory', [App\Http\Controllers\DepartmentInventoryController::class, 'departmentItems'])->name('api.department-inventory.items');
            Route::post('/api/department-inventory/assign', [App\Http\Controllers\DepartmentInventoryController::class, 'assign'])->name('api.department-inventory.assign');
            Route::post('/api/department-inventory/remove', [App\Http\Controllers\DepartmentInventoryController::class, 'remove'])->name('api.department-inventory.remove');
        });

        // Procurement Ordering System
        Route::middleware(['auth'])->group(function () {
            Route::get('/procurement/dashboard', [App\Http\Controllers\ProcurementOrderController::class, 'dashboard'])->name('procurement.dashboard');
            Route::get('/procurement/orders', [App\Http\Controllers\ProcurementOrderController::class, 'index'])->name('procurement.orders.index');
            Route::get('/procurement/orders/create', [App\Http\Controllers\ProcurementOrderController::class, 'create'])->name('procurement.orders.create');
            Route::post('/procurement/orders', [App\Http\Controllers\ProcurementOrderController::class, 'store'])->name('procurement.orders.store');
            Route::get('/procurement/orders/{id}', [App\Http\Controllers\ProcurementOrderController::class, 'show'])->name('procurement.orders.show');
            Route::put('/procurement/orders/{id}/status', [App\Http\Controllers\ProcurementOrderController::class, 'updateStatus'])->name('procurement.orders.status');
            Route::post('/procurement/orders/{id}/remark', [App\Http\Controllers\ProcurementOrderController::class, 'addRemark'])->name('procurement.orders.remark');
            Route::post('/procurement/orders/{id}/notify', [App\Http\Controllers\ProcurementOrderController::class, 'notifyManager'])->name('procurement.orders.notify');
            Route::put('/procurement/notifications/{id}/read', [App\Http\Controllers\ProcurementOrderController::class, 'markNotificationRead'])->name('procurement.notifications.read');
            Route::post('/procurement/orders/{id}/supplier-availability', [App\Http\Controllers\ProcurementOrderController::class, 'updateSupplierAvailability'])->name('procurement.orders.supplier-availability');
            Route::post('/procurement/orders/{id}/items/{itemId}/substitute', [App\Http\Controllers\ProcurementOrderController::class, 'substituteItem'])->name('procurement.orders.substitute-item');
            Route::post('/procurement/orders/{id}/verify', [App\Http\Controllers\ProcurementOrderController::class, 'verifyDelivery'])->name('procurement.orders.verify');
            Route::get('/procurement/analytics', [App\Http\Controllers\ProcurementOrderController::class, 'analytics'])->name('procurement.analytics');

            Route::resource('procurement/suppliers', App\Http\Controllers\SupplierController::class)->names([
                'index' => 'procurement.suppliers.index',
                'create' => 'procurement.suppliers.create',
                'store' => 'procurement.suppliers.store',
                'edit' => 'procurement.suppliers.edit',
                'update' => 'procurement.suppliers.update',
                'destroy' => 'procurement.suppliers.destroy',
            ])->except(['show']);
        });


        



