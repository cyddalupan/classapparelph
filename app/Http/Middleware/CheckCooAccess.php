<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Blocks COO users from routes that are NOT in their allowed navigation.
 * Anything grayed out or hidden in the COO navbar must return 403 here,
 * even if the user knows the direct link.
 */
class CheckCooAccess
{
    /**
     * Route names a COO may NOT access (blocklist).
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
        'sales.destroy', 'sales.create', 'sales.edit', 'sales.show', 'sales.store',
        'sales.update', 'sales.audit-logs', 'sales.team.delay',

        // Profile (grayed out for COO)
        'profile.edit', 'profile.update', 'profile.avatar.update', 'profile.destroy',

        // Procurement (not in COO nav)
        'procurement.dashboard', 'procurement.analytics', 'procurement.orders.index',
        'procurement.orders.create', 'procurement.orders.show', 'procurement.orders.store',
        'procurement.orders.substitute-item', 'procurement.orders.notify',
        'procurement.orders.remark', 'procurement.orders.supplier-availability',
        'procurement.orders.verify', 'procurement.suppliers.index',
        'procurement.suppliers.create', 'procurement.suppliers.store',
        'procurement.suppliers.edit', 'procurement.suppliers.update',
        'procurement.suppliers.destroy',

        // Extra sales entry pages (not in COO nav)
        'sales.pricing', 'sales.create-quick', 'sales.quick-store',

        // Inventory pages other than the unified management page
        'inventory.select-category', 'inventory.select-category-simple',
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

        if ($user && $user->isCoo()) {
            $route = $request->route();
            $name = $route ? $route->getName() : '';

            if ($name !== null && in_array($name, $this->blockedRoutes, true)) {
                abort(403, 'Unauthorized access.');
            }
        }

        return $next($request);
    }
}
