<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinanceDashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
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
    
    Route::get('/products', function () {
        return view('products.index');
    })->name('products.index');
    
    Route::get('/products/{id}', function ($id) {
        return view('products.show', ['id' => $id]);
    })->name('products.show');
    
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
    
    Route::get('/inventory', function () {
        return view('inventory.index');
    })->name('inventory.index');
    
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
        Route::get('/sales/products', function () {
            if (!Gate::allows('access-sales-agent')) {
                abort(403, 'Unauthorized access.');
            }
            return view('sales.products');
        })->name('sales.products');
        
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
