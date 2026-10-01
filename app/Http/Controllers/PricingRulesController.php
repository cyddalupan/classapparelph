<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrintingPrice;
use App\Models\PrintingComboDiscount;
use App\Models\PrintingBulkDiscount;
use App\Models\SublimationPrice;
use App\Models\SublimationBulkDiscount;
use App\Models\SublimationConnectedProduct;
use App\Models\MasterItem;
use App\Models\ProductPricing;
use Illuminate\Support\Facades\DB;

class PricingRulesController extends Controller
{

    /**
     * Display pricing rules dashboard.
     */
    public function index()
    {
        // Other Items / Products stats (sales_box = 'other')
        $otherItemsCount = MasterItem::where('sales_box', 'other')->whereNull('deleted_at')->count();
        $otherPricedCount = MasterItem::where('sales_box', 'other')->whereNull('deleted_at')
            ->whereHas('productPricings', function ($q) {
                $q->where('is_active', true)
                  ->where('price_tier', 'supplier_cost')
                  ->where('base_price', '>', 0);
            })->count();

        // Get all services with their configuration status
        $services = [
            'printing' => [
                'name' => 'Garment Printing',
                'icon' => 'fas fa-tshirt',
                'description' => 'T-shirt, hoodie, and apparel printing',
                'configured' => PrintingPrice::count() > 0,
                'price_count' => PrintingPrice::count(),
                'combo_count' => PrintingComboDiscount::count(),
                'bulk_count' => PrintingBulkDiscount::count(),
                'edit_route' => 'printing.rules',
                'color' => 'primary',
            ],
            'bulk' => [
                'name' => 'Bulk Order Rules',
                'icon' => 'fas fa-layer-group',
                'description' => 'Quantity-based discounts for all products',
                'configured' => false,
                'price_count' => 0,
                'combo_count' => 0,
                'bulk_count' => 0,
                'edit_route' => '#',
                'color' => 'success',
            ],
            'sublimation' => [
                'name' => 'Full Sublimation',
                'icon' => 'fas fa-paint-roller',
                'description' => 'Full garment dye sublimation printing',
                'configured' => \App\Models\SublimationPrice::count() > 0,
                'price_count' => \App\Models\SublimationPrice::count(),
                'combo_count' => 0,
                'bulk_count' => \App\Models\SublimationBulkDiscount::count(),
                'edit_route' => 'pricing.rules.sublimation',
                'color' => 'info',
            ],
            'other' => [
                'name' => 'Other Items / Products',
                'icon' => 'fas fa-box-open',
                'description' => 'Non-apparel items (mugs, tumblers, and other products)',
                'configured' => $otherPricedCount > 0,
                'price_count' => $otherPricedCount,
                'combo_count' => 0,
                'bulk_count' => 0,
                'edit_route' => 'pricing.rules.other',
                'always_link' => true,
                'link_label' => 'Set / Edit Prices',
                'color' => 'warning',
            ],
            'embroidery' => [
                'name' => 'Embroidery',
                'icon' => 'fas fa-thread',
                'description' => 'Thread embroidery on caps, jackets, bags',
                'configured' => false,
                'price_count' => 0,
                'combo_count' => 0,
                'bulk_count' => 0,
                'edit_route' => '#',
                'color' => 'danger',
            ],
            'sticker' => [
                'name' => 'Sticker & Decal',
                'icon' => 'fas fa-sticky-note',
                'description' => 'Vinyl stickers, decals, and labels',
                'configured' => false,
                'price_count' => 0,
                'combo_count' => 0,
                'bulk_count' => 0,
                'edit_route' => '#',
                'color' => 'secondary',
            ],
        ];

        return view('pricing-rules.index', compact('services', 'otherItemsCount', 'otherPricedCount'));
    }

    /**
     * Display garment printing rules.
     */
    public function printingRules()
    {
        // This redirects to the existing printing rules editor
        return redirect()->route('printing.rules');
    }

    /**
     * Display bulk order rules.
     */
    public function bulkRules()
    {
        return view('pricing-rules.bulk');
    }

    /**
     * Display sublimation rules.
     */
    public function sublimationRules()
    {
        $garments = SublimationPrice::with('masterItem')->where('category', 'garment')->orderBy('order')->get();
        $fabrics = SublimationPrice::with('masterItem')->where('category', 'fabric')->orderBy('order')->get();
        $parts = SublimationPrice::with('masterItem')->where('category', 'part')->orderBy('order')->get();
        $sizes = SublimationPrice::with('masterItem')->where('category', 'size')->orderBy('order')->get();
        $bulkDiscounts = SublimationBulkDiscount::orderBy('min_qty')->get();
        
        // Connected master items — managed by Andrew manually
        $sublimationMasterItems = SublimationConnectedProduct::with(['masterItem.productPricings' => function($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('id')
            ->get()
            ->pluck('masterItem')
            ->filter();
        
        // All master items for the per-row dropdown
        $allMasterItems = MasterItem::whereHas('productPricings', function($q) {
                $q->where('is_active', true);
            })
            ->with(['productPricings' => function($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name')
            ->get(['id', 'name', 'description']);
        
        return view('pricing-rules.sublimation', compact(
            'garments', 'fabrics', 'parts', 'sizes', 'bulkDiscounts', 
            'sublimationMasterItems', 'allMasterItems'
        ));
    }

    /**
     * Display tarpaulin rules.
     */
    public function tarpaulinRules()
    {
        return view('pricing-rules.tarpaulin');
    }

    /**
     * Display Other Items / Products pricing rules (per-item).
     */
    public function otherRules()
    {
        $items = MasterItem::with(['productPricings' => function ($q) {
                $q->where('is_active', true);
            }])
            ->where('sales_box', 'other')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return view('pricing-rules.other', compact('items'));
    }

    /**
     * Save Other Items / Products pricing rules (per-item, 3 tiers).
     */
    public function updateOtherPrices(Request $request)
    {
        $request->validate([
            'prices' => 'array',
            'prices.*.supplier_cost' => 'nullable|numeric|min:0',
            'prices.*.sales_team'    => 'nullable|numeric|min:0',
            'prices.*.agent_cost'    => 'nullable|numeric|min:0',
        ]);

        $prices = $request->input('prices', []);
        $userId = auth()->id();
        $tiers  = ['supplier_cost', 'sales_team', 'agent_cost'];
        $updated = 0;

        DB::transaction(function () use ($prices, $tiers, $userId, &$updated) {
            foreach ($prices as $itemId => $tierData) {
                $item = MasterItem::where('sales_box', 'other')->find($itemId);
                if (!$item || !is_array($tierData)) {
                    continue;
                }
                foreach ($tiers as $tier) {
                    if (!array_key_exists($tier, $tierData)) {
                        continue;
                    }
                    $value = $tierData[$tier];
                    if ($value === '' || $value === null) {
                        continue; // blank = keep current
                    }
                    $value = (float) $value;
                    ProductPricing::updateOrCreate(
                        ['master_item_id' => $item->id, 'price_tier' => $tier],
                        [
                            'base_price'        => $value,
                            'markup_percentage' => null,
                            'markup_amount'     => 0,
                            'final_price'       => $value,
                            'is_active'         => true,
                            'created_by'        => $userId,
                            'updated_by'        => $userId,
                        ]
                    );
                    $updated++;
                }
            }
        });

        return redirect()->route('pricing.rules.other')
            ->with('success', "Other Items pricing saved ({$updated} price(s) updated).");
    }

    /**
     * Display embroidery rules.
     */
    public function embroideryRules()
    {
        return view('pricing-rules.embroidery');
    }

    /**
     * Display sticker rules.
     */
    public function stickerRules()
    {
        return view('pricing-rules.sticker');
    }

    /**
     * Update sublimation prices
     */
    public function updateSublimationPrices(Request $request)
    {
        $prices = $request->input('prices', []);
        foreach ($prices as $id => $data) {
            $item = SublimationPrice::find($id);
            if ($item) {
                $updateData = [
                    'price' => $data['price'] ?? 0,
                    'agent_price' => $data['agent_price'] ?? null,
                ];
                
                // Handle master_item_id linking
                if (isset($data['master_item_id']) && $data['master_item_id']) {
                    $updateData['master_item_id'] = $data['master_item_id'];
                } elseif (isset($data['unlink']) && $data['unlink']) {
                    $updateData['master_item_id'] = null;
                }
                
                $item->update($updateData);
            }
        }
        return redirect()->back()->with('success', 'Sublimation prices updated!');
    }

    /**
     * Update sublimation bulk discounts
     */
    public function updateSublimationBulk(Request $request)
    {
        $discounts = $request->input('discounts', []);
        foreach ($discounts as $id => $data) {
            $item = SublimationBulkDiscount::find($id);
            if ($item) {
                $item->update([
                    'min_qty' => $data['min_qty'] ?? 0,
                    'max_qty' => $data['max_qty'] ?? null,
                    'discount_percent' => $data['discount_percent'] ?? 0,
                    'name' => $data['name'] ?? '',
                ]);
            }
        }
        return redirect()->back()->with('success', 'Bulk discounts updated!');
    }

    /**
     * Add new sublimation price item
     */
    public function addSublimationPrice(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:garment,fabric,part,size',
            'tab_group' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'master_item_id' => 'nullable|exists:master_items,id',
        ]);

        SublimationPrice::create([
            'name' => $request->name,
            'category' => $request->category,
            'tab_group' => $request->tab_group,
            'price' => $request->price,
            'agent_price' => $request->agent_price,
            'master_item_id' => $request->master_item_id,
            'order' => $request->order ?? 0,
        ]);

        return redirect()->back()->with('success', 'Item added!');
    }

    /**
     * Delete a sublimation price item
     */
    public function deleteSublimationPrice(Request $request)
    {
        $item = SublimationPrice::find($request->id);
        if ($item) {
            $item->delete();
            return redirect()->back()->with('success', 'Item deleted!');
        }
        return redirect()->back()->with('error', 'Item not found!');
    }

    /**
     * Add a master item to connected products
     */
    public function addConnectedProduct(Request $request)
    {
        $request->validate([
            'master_item_id' => 'required|exists:master_items,id',
        ]);

        $exists = SublimationConnectedProduct::where('master_item_id', $request->master_item_id)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Product already connected!');
        }

        SublimationConnectedProduct::create([
            'master_item_id' => $request->master_item_id,
        ]);

        return redirect()->back()->with('success', 'Product connected successfully!');
    }

    /**
     * Remove a master item from connected products
     */
    public function removeConnectedProduct($id)
    {
        $item = SublimationConnectedProduct::findOrFail($id);
        $item->delete();
        return redirect()->back()->with('success', 'Product disconnected!');
    }

    /**
     * API: Get all SublimationPrice items grouped by category
     */
    public function getSublimationPrices()
    {
        $items = \App\Models\SublimationPrice::where('active', true)
            ->orderBy('category')
            ->orderBy('order')
            ->get(['id', 'name', 'category', 'price', 'agent_price']);

        // Pricing tier: kung ang naka-login na user ay 'agent' tier, gamitin ang agent_price.
        // WALANG fallback sa sales price — kung 0/blank ang agent_price, 0 ang ipapasa.
        // Default tier = 'sales' (gumagamit ng sales price).
        $user = auth()->user();
        if ($user && method_exists($user, 'usesAgentPricing') && $user->usesAgentPricing()) {
            $items->each(function ($it) {
                $it->setAttribute('price', $it->agent_price ?? 0);
            });
        }

        return response()->json([
            'garments' => $items->where('category', 'garment')->values(),
            'fabrics'  => $items->where('category', 'fabric')->values(),
            'parts'    => $items->where('category', 'part')->values(),
            'sizes'    => $items->where('category', 'size')->values(),
        ]);
    }
}