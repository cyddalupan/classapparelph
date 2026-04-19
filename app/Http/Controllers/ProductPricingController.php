<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterItem;
use App\Models\ProductPricing;
use App\Models\VolumeDiscount;
use Illuminate\Support\Facades\DB;

class ProductPricingController extends Controller
{
    /**
     * Display product pricing dashboard.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $category = $request->input('category');
        $search = $request->input('search');
        $priceTier = $request->input('price_tier', 'supplier_cost');
        
        // Start query
        $query = MasterItem::with(['productPricings' => function($q) use ($priceTier) {
            $q->where('price_tier', $priceTier)->where('is_active', true);
        }]);
        
        // Apply filters
        if ($category) {
            $query->where('category', $category);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('sku', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        // Get items with pagination
        $items = $query->orderBy('name')->paginate(25);
        
        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        // Get unique categories for filter dropdown
        $categories = MasterItem::distinct()->pluck('category')->filter()->sort()->values();
        
        return view('product-pricing.index', compact('items', 'stats', 'categories', 'category', 'search', 'priceTier'));
    }

    /**
     * Get dashboard statistics.
     */
    private function getDashboardStats()
    {
        // Calculate average margin
        $avgMargin = ProductPricing::where('is_active', true)
            ->whereNotNull('markup_percentage')
            ->avg('markup_percentage');
        
        // Calculate profit potential (sum of markup_amount * estimated quantity)
        $profitPotential = ProductPricing::where('is_active', true)
            ->whereNotNull('markup_amount')
            ->sum('markup_amount') * 10; // Assuming 10 units per product
        
        // Count low margin alerts (margin < 20%)
        $lowMarginAlerts = ProductPricing::where('is_active', true)
            ->whereNotNull('markup_percentage')
            ->where('markup_percentage', '<', 20)
            ->count();
        
        $stats = [
            'total_items' => MasterItem::count(),
            'active_pricings' => ProductPricing::where('is_active', true)->count(),
            'items_without_pricing' => MasterItem::whereDoesntHave('productPricings', function($q) {
                $q->where('is_active', true);
            })->count(),
            'recently_updated' => ProductPricing::where('updated_at', '>=', now()->subDays(7))->count(),
            'avg_margin' => $avgMargin ? round($avgMargin, 2) : 0,
            'profit_potential' => $profitPotential ? round($profitPotential, 2) : 0,
            'low_margin_alerts' => $lowMarginAlerts,
        ];
        
        return $stats;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = MasterItem::with(['productPricings', 'volumeDiscounts'])->findOrFail($id);
        
        // Get existing pricing for each tier
        $pricingTiers = [
            'supplier_cost' => $item->productPricings->firstWhere('price_tier', 'supplier_cost'),
            'sales_team' => $item->productPricings->firstWhere('price_tier', 'sales_team'),
            'agent_cost' => $item->productPricings->firstWhere('price_tier', 'agent_cost'),
        ];
        
        return view('product-pricing.edit', compact('item', 'pricingTiers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $item = MasterItem::findOrFail($id);
        $userId = auth()->id();
        
        DB::transaction(function() use ($request, $item, $userId) {
            // Update master item
            $item->update([
                'name' => $request->input('name'),
                'category' => $request->input('category'),
                'description' => $request->input('description'),
                'unit_price' => $request->input('unit_price'),
                'sku' => $request->input('sku'),
                'barcode' => $request->input('barcode'),
                'sales_box' => $request->input('sales_box'),
                'updated_by' => $userId,
            ]);
            
            // Update or create pricing for each tier
            foreach (['supplier_cost', 'sales_team', 'agent_cost'] as $tier) {
                $basePrice = $request->input("{$tier}_base_price");
                $markupPercentage = $request->input("{$tier}_markup_percentage");
                $markupAmount = $request->input("{$tier}_markup_amount");
                $finalPrice = $request->input("{$tier}_final_price");
                
                if ($basePrice !== null) {
                    ProductPricing::updateOrCreate(
                        [
                            'master_item_id' => $item->id,
                            'price_tier' => $tier
                        ],
                        [
                            'base_price' => $basePrice,
                            'markup_percentage' => $markupPercentage,
                            'markup_amount' => $markupAmount,
                            'final_price' => $finalPrice,
                            'is_active' => true,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]
                    );
                }
            }
        });
        
        return redirect()->route('product-pricing.index')
            ->with('success', 'Product pricing updated successfully.');
    }

    /**
     * Bulk update product pricing.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'price_tier' => 'required|in:supplier_cost,sales_team,agent_cost',
            'update_type' => 'required|in:percentage,amount,set_price',
            'update_value' => 'required|numeric|min:0',
        ]);
        
        $userId = auth()->id();
        $updatedCount = 0;
        
        DB::transaction(function() use ($request, $userId, &$updatedCount) {
            foreach ($request->input('items') as $itemId) {
                $item = MasterItem::find($itemId);
                if (!$item) continue;
                
                $pricing = ProductPricing::firstOrNew([
                    'master_item_id' => $itemId,
                    'price_tier' => $request->input('price_tier')
                ]);
                
                $basePrice = $pricing->base_price ?: $item->unit_price ?: 0;
                
                switch ($request->input('update_type')) {
                    case 'percentage':
                        $markupPercentage = $request->input('update_value');
                        $markupAmount = $basePrice * ($markupPercentage / 100);
                        $finalPrice = $basePrice + $markupAmount;
                        break;
                        
                    case 'amount':
                        $markupAmount = $request->input('update_value');
                        $markupPercentage = $basePrice > 0 ? ($markupAmount / $basePrice) * 100 : 0;
                        $finalPrice = $basePrice + $markupAmount;
                        break;
                        
                    case 'set_price':
                        $finalPrice = $request->input('update_value');
                        $markupAmount = $finalPrice - $basePrice;
                        $markupPercentage = $basePrice > 0 ? ($markupAmount / $basePrice) * 100 : 0;
                        break;
                }
                
                $pricing->fill([
                    'base_price' => $basePrice,
                    'markup_percentage' => $markupPercentage,
                    'markup_amount' => $markupAmount,
                    'final_price' => $finalPrice,
                    'is_active' => true,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ])->save();
                
                $updatedCount++;
            }
        });
        
        return redirect()->route('product-pricing.index')
            ->with('success', "Successfully updated {$updatedCount} product(s).");
    }

    /**
     * Show volume discounts for a product.
     */
    public function volumeDiscounts($id)
    {
        $item = MasterItem::findOrFail($id);
        $discounts = $item->volumeDiscounts()->where('is_active', true)->orderBy('min_quantity')->get();
        
        return view('product-pricing.volume-discounts', compact('item', 'discounts'));
    }

    /**
     * Store volume discounts for a product.
     */
    public function storeVolumeDiscounts(Request $request, $id)
    {
        $item = MasterItem::findOrFail($id);
        $userId = auth()->id();
        
        DB::transaction(function() use ($request, $item, $userId) {
            // Deactivate all existing discounts
            VolumeDiscount::where('master_item_id', $item->id)->update(['is_active' => false]);
            
            // Create new discounts
            $discounts = $request->input('discounts', []);
            foreach ($discounts as $discountData) {
                if (empty($discountData['min_quantity']) || empty($discountData['price_per_unit'])) {
                    continue;
                }
                
                $discount = new VolumeDiscount([
                    'master_item_id' => $item->id,
                    'min_quantity' => $discountData['min_quantity'],
                    'max_quantity' => $discountData['max_quantity'] ?? null,
                    'price_per_unit' => $discountData['price_per_unit'],
                    'is_active' => true,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                
                $discount->save();
            }
        });
        
        return redirect()->route('product-pricing.edit', $item->id)
            ->with('success', 'Volume discounts saved successfully.');
    }

    /**
     * API: Get products for sales box
     */
    public function getProductsForBox($boxType)
    {
        // Get master items for this sales box
        $items = MasterItem::where('sales_box', $boxType)
            ->with(['productPricings', 'volumeDiscounts'])
            ->orderBy('name')
            ->get();

        // Format response
        $products = $items->map(function($item) {
            // Find sales_team pricing
            $pricing = $item->productPricings->firstWhere('price_tier', 'sales_team');
            if (!$pricing) {
                $pricing = $item->productPricings->first();
            }
            
            return [
                'id' => $item->id,
                'name' => $item->name,
                'brand' => $item->brand,
                'size' => $item->size,
                'color' => $item->color,
                'material' => $item->material,
                'category' => $item->category,
                'base_price' => $pricing ? $pricing->final_price : 0,
                'volume_discounts' => $item->volumeDiscounts->where('is_active', true)->map(function($discount) {
                    return [
                        'min_quantity' => $discount->min_quantity,
                        'price_per_unit' => $discount->price_per_unit
                    ];
                })->values(),
                'description' => $item->description
            ];
        })->filter(function($product) {
            return $product['base_price'] > 0; // Only return products with price
        })->values();

        return response()->json($products);
    }
}