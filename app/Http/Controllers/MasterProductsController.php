<?php

namespace App\Http\Controllers;

use App\Models\MasterItem;
use App\Models\ProductPricing;
use Illuminate\Http\Request;

/**
 * Master Products & Pricing — unified hub (Phase 1: read-only draft).
 *
 * Additive only: this controller does NOT touch MasterItemsController or
 * ProductPricingController. It simply presents the catalog (master-items)
 * together with per-tier pricing + Sales Box assignment (product-pricing)
 * in a single listing so the two pages can eventually be merged.
 *
 * No write operations here yet (Phase 1 = view only).
 */
class MasterProductsController extends Controller
{
    public function edit($id)
    {
        $item = MasterItem::with(['productPricings', 'volumeDiscounts'])->findOrFail($id);

        // Pricing tiers (same shape as the Product Pricing edit screen).
        $supplierPricing = $item->productPricings->firstWhere('price_tier', 'supplier_cost');
        $salesPricing    = $item->productPricings->firstWhere('price_tier', 'sales_team');
        $agentPricing    = $item->productPricings->firstWhere('price_tier', 'agent_cost');

        return view('master-products.edit', compact('item', 'supplierPricing', 'salesPricing', 'agentPricing'));
    }

    public function index(Request $request)
    {
        $category   = $request->input('category');
        $search     = $request->input('search');
        $priceTier  = $request->input('price_tier', 'supplier_cost');
        $brand      = $request->input('brand');
        $shirtType  = $request->input('shirt_type');
        $color      = $request->input('color');
        $salesBox   = $request->input('sales_box');

        // Base query — same filters as the existing Product Pricing page.
        $query = MasterItem::with([
            'productPricings' => function ($q) {
                $q->where('is_active', true);
            },
            'volumeDiscounts' => function ($q) {
                $q->where('is_active', true);
            },
        ]);

        if ($category)  { $query->where('category', $category); }
        if ($brand)     { $query->where('brand', $brand); }
        if ($shirtType) { $query->where('shirt_type', $shirtType); }
        if ($color)     { $query->where('color', $color); }
        if ($salesBox)  { $query->where('sales_box', $salesBox); }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('sku', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $items = $query->orderBy('name')->paginate(25)->withQueryString();

        // Category counts (from Master Items page).
        $categoryCounts = [
            'shirt'         => MasterItem::where('category', 'Shirt Products')->count(),
            'uncategorized' => MasterItem::where('category', 'Other Products')->count(),
            'machines'      => MasterItem::where('category', 'Machine and Equipments')->count(),
            'materials'     => MasterItem::where('category', 'Garment Materials')->count(),
            'printing'      => MasterItem::where('category', 'Printing and Office Supplies')->count(),
        ];

        // Dashboard stats (from Product Pricing page).
        $stats = [
            'total_items'           => MasterItem::count(),
            'items_without_pricing' => MasterItem::whereDoesntHave('productPricings', function ($q) {
                $q->where('is_active', true);
            })->count(),
            'recently_updated'      => ProductPricing::where('updated_at', '>=', now()->subDays(7))->count(),
        ];

        // Distinct filter options.
        $categories  = MasterItem::distinct()->pluck('category')->filter()->sort()->values();
        $brands      = MasterItem::whereNotNull('brand')->distinct()->pluck('brand')->filter()->sort()->values();
        $shirtTypes  = MasterItem::whereNotNull('shirt_type')->distinct()->pluck('shirt_type')->filter()->sort()->values();
        $colors      = MasterItem::whereNotNull('color')->distinct()->pluck('color')->filter()->sort()->values();

        // Sales Box values actually present in the catalog.
        $salesBoxes  = MasterItem::whereNotNull('sales_box')
            ->where('sales_box', '!=', '')
            ->distinct()->pluck('sales_box')->sort()->values();

        return view('master-products.index', compact(
            'items', 'stats', 'categoryCounts',
            'categories', 'category', 'search', 'priceTier',
            'brands', 'brand', 'shirtTypes', 'shirtType', 'colors', 'color',
            'salesBoxes', 'salesBox'
        ));
    }
}
