<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Blocks CMO users from routes that are NOT in their allowed navigation.
 * Same blocklist as CPO, PLUS:
 *  - inventory.unified (Inventory Management is grayed out for CMO)
 *  - sales.cash-flow (was not in CMO nav; now allowed per request)
 *  - production feedback: .list allowed (My Sales), .store/.status blocked (like CPO)
 */
class CheckCmoAccess
{
    /**
     * Route names a CMO may NOT access (blocklist).
     */
    protected array $blockedRoutes = [
        // Orders
        'orders.index', 'orders.show',

        // Product Pricing & Printing Calculator
        'product-pricing.index', 'product-pricing.edit', 'product-pricing.volume-discounts',
        'printing.pricing', 'printing.pricing-test', 'printing.public', 'printing.rules',
        'printing.calculate', 'printing.store-price', 'printing.store-combo',
        'printing.store-upgrade', 'printing.store-bulk', 'printing.add-price',
        'printing.delete-price', 'printing.get-product-pricing', 'printing.sync-prices',
        'printing.update-prices', 'printing.update-combos', 'printing.update-bulk',
        'pricing.rules', 'pricing.rules.printing', 'pricing.rules.bulk',
        'pricing.rules.sublimation', 'pricing.rules.tarpaulin', 'pricing.rules.embroidery',
        'pricing.rules.sticker', 'pricing.rules.sublimation.prices',
        'pricing.rules.sublimation.bulk', 'pricing.rules.sublimation.add-price',
        'pricing.rules.sublimation.delete-price', 'pricing.rules.sublimation.connect',
        'pricing.rules.sublimation.disconnect',

        // Design, Analytics, Reports
        'design.studio', 'analytics.dashboard', 'reports.index',

        // Finance (all)
        'finance.dashboard', 'finance.expenses', 'finance.sales', 'finance.reports',

        // Sales pages NOT in CMO nav (kanban, manager list, delays, etc.)
        'sales.prototype.kanban', 'sales.prototype.list',
        'sales.prototype.delays', 'sales.prototype.delay-review',
        'sales.prototype.archived', 'sales.prototype.archive', 'sales.prototype.restore',
        'sales.prototype.edit-items', 'sales.prototype.submit-change',
        'sales.prototype.approve-change', 'sales.prototype.reject-change',
        'sales.prototype.addon.all-pending', 'sales.prototype.addon.pending',
        'sales.prototype.addon.request', 'sales.prototype.addon.approve',
        'sales.prototype.addon.reject',
        'sales.prototype.production-feedback.store',
        'sales.prototype.production-feedback.status',
        'sales.prototype.audit-history',
        'sales.prototype.edit', 'sales.prototype.update', 'sales.prototype.destroy',
        'sales.prototype.print-slip', 'sales.prototype.print-slip.pdf',
        'sales.destroy', 'sales.create', 'sales.edit', 'sales.show', 'sales.store',
        'sales.update', 'sales.audit-logs',

        // Profile (grayed out for CMO)
        'profile.edit', 'profile.update', 'profile.avatar.update', 'profile.destroy',

        // Procurement (not in CMO nav)
        'procurement.dashboard', 'procurement.analytics', 'procurement.orders.index',
        'procurement.orders.create', 'procurement.orders.show', 'procurement.orders.store',
        'procurement.orders.substitute-item', 'procurement.orders.notify',
        'procurement.orders.remark', 'procurement.orders.supplier-availability',
        'procurement.orders.verify', 'procurement.suppliers.index',
        'procurement.suppliers.create', 'procurement.suppliers.store',
        'procurement.suppliers.edit', 'procurement.suppliers.update',
        'procurement.suppliers.destroy',

        // Extra sales entry pages (not in CMO nav)
        'sales.pricing', 'sales.create-quick', 'sales.quick-store',

        // Inventory — ALL pages for CMO (Inventory Management is grayed out)
        'inventory.unified', 'inventory.select-category', 'inventory.select-category-simple',
        'inventory.select-category-test', 'inventory.create', 'inventory.action',
        'inventory.list', 'inventory.index', 'inventory.edit', 'inventory.show',
        'inventory.store', 'inventory.update', 'inventory.destroy', 'inventory.restore',
        'inventory.trashed', 'inventory.forceDelete', 'inventory.adjust-stock',
        'inventory.updateStock', 'inventory.shirt-products.store',
        'inventories.index', 'master-items.index', 'master-items.create',
        'master-items.edit', 'master-items.store', 'master-items.update',
        'master-items.destroy', 'dtf.create', 'dtf.store', 'test-navigation',

        // Products
        'products.index', 'products.create', 'products.store', 'products.edit',
        'products.update', 'products.destroy', 'products.trashed', 'products.restore',
        'products.force-delete', 'products.update-stock', 'productlist',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isCmo()) {
            $route = $request->route();
            $name = $route ? $route->getName() : '';

            if ($name !== null && in_array($name, $this->blockedRoutes, true)) {
                abort(403, 'Unauthorized access.');
            }
        }

        return $next($request);
    }
}
