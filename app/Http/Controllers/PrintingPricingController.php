<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrintingPrice;
use App\Models\PrintingComboDiscount;
use App\Models\PrintingSizeUpgrade;
use App\Models\PrintingBulkDiscount;
use App\Models\MasterItem;
use Illuminate\Support\Facades\DB;

class PrintingPricingController extends Controller
{
    /**
     * Display the printing pricing calculator
     */
    public function index(Request $request)
    {
        $printType = $request->input('type', 'dtf');
        
        $validTypes = ['dtf', 'sublimation', 'silkscreen'];
        if (!in_array($printType, $validTypes)) {
            $printType = 'dtf';
        }
        
        $user = auth()->user();
        $priceField = 'price';
        $userRole = null;
        
        if ($user && $user->role === 'sales_agent') {
            $priceField = 'agent_price';
            $userRole = 'sales_agent';
        }
        
        $prices = PrintingPrice::where('print_type', $printType)
            ->where('active', true)
            ->orderBy('order')->get();
        
        $comboDiscounts = PrintingComboDiscount::where('print_type', $printType)
            ->with(['size1', 'size2'])
            ->get();
        
        $sizeUpgrades = PrintingSizeUpgrade::where('print_type', $printType)
            ->with(['fromSize', 'toSize'])
            ->get();
        
        $bulkDiscounts = PrintingBulkDiscount::where('print_type', $printType)
            ->orderBy('min_garments')->get();
        
        return view('printing.pricing_simple', compact('prices', 'comboDiscounts', 'sizeUpgrades', 'bulkDiscounts', 'printType', 'priceField', 'userRole'));
    }
    
    /**
     * Test page with server-side calculation
     */
    public function testIndex(Request $request)
    {
        $printType = $request->input('type', 'dtf');
        
        $validTypes = ['dtf', 'sublimation', 'silkscreen'];
        if (!in_array($printType, $validTypes)) {
            $printType = 'dtf';
        }
        
        $user = auth()->user();
        $priceField = 'price';
        $userRole = null;
        
        if ($user && $user->role === 'sales_agent') {
            $priceField = 'agent_price';
            $userRole = 'sales_agent';
        }
        
        $prices = PrintingPrice::where('print_type', $printType)
            ->where('active', true)
            ->orderBy('order')->get();
        
        $comboDiscounts = PrintingComboDiscount::where('print_type', $printType)
            ->with(['size1', 'size2'])
            ->get();
        
        $sizeUpgrades = PrintingSizeUpgrade::where('print_type', $printType)
            ->with(['fromSize', 'toSize'])
            ->get();
        
        $bulkDiscounts = PrintingBulkDiscount::where('print_type', $printType)
            ->orderBy('min_garments')->get();
        
        return view('printing.pricing_test', compact('prices', 'comboDiscounts', 'sizeUpgrades', 'bulkDiscounts', 'printType', 'priceField', 'userRole'));
    }
    
    /**
     * Calculate printing price
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'garments' => 'required|array|min:1',
            'garments.*.prints' => 'required|array|min:1',
        ]);
        
        $garments = $request->input('garments');
        $printType = $request->input('type', 'dtf');
        
        $user = auth()->user();
        $priceField = 'price';
        
        if ($user && $user->role === 'sales_agent') {
            $priceField = 'agent_price';
        }
        
        $total = 0;
        $breakdown = [];
        
        foreach ($garments as $index => $garment) {
            $garmentTotal = 0;
            $garmentPrints = [];
            
            foreach ($garment['prints'] as $printSizeId) {
                $price = PrintingPrice::where('print_type', $printType)->find($printSizeId);
                if ($price) {
                    $unitPrice = $price->$priceField ?? $price->price;
                    $garmentTotal += $unitPrice;
                    $garmentPrints[] = [
                        'size' => $price->name,
                        'price' => $unitPrice
                    ];
                }
            }
            
            $comboDiscount = $this->calculateComboDiscount($garment['prints'], $printType);
            $garmentTotal -= $comboDiscount;
            
            $upgradedPrints = $this->applySizeUpgrades($garment['prints'], $printType);
            
            $breakdown[] = [
                'garment_number' => $index + 1,
                'prints' => $garmentPrints,
                'subtotal' => $garmentTotal,
                'combo_discount' => $comboDiscount,
                'upgraded_prints' => $upgradedPrints
            ];
            
            $total += $garmentTotal;
        }
        
        // Count transactions (for now, each garment = 1 transaction)
        // TODO: Implement count_combo_as_one logic
        $transactionCount = count($garments);
        
        $bulkDiscount = $this->calculateBulkDiscount($transactionCount, $total, $printType);
        $total -= $bulkDiscount;
        
        return response()->json([
            'success' => true,
            'total' => number_format($total, 2),
            'breakdown' => $breakdown,
            'bulk_discount' => number_format($bulkDiscount, 2),
            'garment_count' => count($garments),
            'transaction_count' => $transactionCount
        ]);
    }
    
    /**
     * Calculate combo discount for a garment
     */
    private function calculateComboDiscount($printSizeIds, $printType = 'dtf')
    {
        $discount = 0;
        $user = auth()->user();
        $priceTier = ($user && $user->role === 'sales_agent') ? 'agent' : 'sales_team';
        
        $comboDiscounts = PrintingComboDiscount::where('print_type', $printType)
            ->where('price_tier', $priceTier)
            ->where('active', true)
            ->get();
        
        foreach ($comboDiscounts as $combo) {
            if (in_array($combo->size1_id, $printSizeIds) && 
                in_array($combo->size2_id, $printSizeIds)) {
                
                if ($combo->discount_type === 'fixed') {
                    $discount += $combo->discount_value;
                }
            }
        }
        
        return $discount;
    }
    
    /**
     * Apply size upgrades
     */
    private function applySizeUpgrades($printSizeIds, $printType = 'dtf')
    {
        $upgraded = [];
        $sizeCounts = array_count_values($printSizeIds);
        
        $upgradeRules = PrintingSizeUpgrade::where('print_type', $printType)->get();
        
        foreach ($upgradeRules as $rule) {
            $fromSizeId = $rule->from_size_id;
            $requiredQuantity = $rule->from_quantity;
            
            if (isset($sizeCounts[$fromSizeId]) && 
                $sizeCounts[$fromSizeId] >= $requiredQuantity) {
                
                for ($i = 0; $i < $requiredQuantity; $i++) {
                    $key = array_search($fromSizeId, $printSizeIds);
                    if ($key !== false) {
                        unset($printSizeIds[$key]);
                    }
                }
                
                $printSizeIds[] = $rule->to_size_id;
                
                $upgraded[] = [
                    'from' => $rule->fromSize->name,
                    'from_quantity' => $requiredQuantity,
                    'to' => $rule->toSize->name,
                    'to_quantity' => 1
                ];
                
                $sizeCounts = array_count_values($printSizeIds);
            }
        }
        
        return $upgraded;
    }
    
    /**
     * Calculate bulk discount (transaction-based)
     */
    private function calculateBulkDiscount($transactionCount, $subtotal, $printType = 'dtf')
    {
        $user = auth()->user();
        $priceTier = ($user && $user->role === 'sales_agent') ? 'agent' : 'sales_team';
        
        $bulkDiscount = PrintingBulkDiscount::where('print_type', $printType)
            ->where('price_tier', $priceTier)
            ->where('min_transactions', '<=', $transactionCount)
            ->where('max_transactions', '>=', $transactionCount)
            ->first();
        
        if ($bulkDiscount) {
            if ($bulkDiscount->discount_type === 'fixed_amount') {
                return $bulkDiscount->discount_amount;
            } else {
                return $subtotal * ($bulkDiscount->discount_percent / 100);
            }
        }
        
        return 0;
    }
    
    /**
     * Save printing price
     */
    public function storePrice(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'order' => 'required|integer'
        ]);
        
        PrintingPrice::create($request->all());
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Save combo discount
     */
    public function storeComboDiscount(Request $request)
    {
        $request->validate([
            'size1_id' => 'required|exists:printing_prices,id',
            'size2_id' => 'required|exists:printing_prices,id',
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0'
        ]);
        
        PrintingComboDiscount::create($request->all());
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Save size upgrade rule
     */
    public function storeSizeUpgrade(Request $request)
    {
        $request->validate([
            'from_size_id' => 'required|exists:printing_prices,id',
            'from_quantity' => 'required|integer|min:2',
            'to_size_id' => 'required|exists:printing_prices,id',
            'auto_apply' => 'boolean'
        ]);
        
        PrintingSizeUpgrade::create($request->all());
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Save bulk discount
     */
    public function storeBulkDiscount(Request $request)
    {
        $request->validate([
            'min_garments' => 'required|integer|min:1',
            'max_garments' => 'required|integer|min:1',
            'discount_percent' => 'required|numeric|min:0|max:100'
        ]);
        
        PrintingBulkDiscount::create($request->all());
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Display rule editor
     */
    public function editRules(Request $request)
    {
        $printType = $request->input('type', 'dtf');
        
        $validTypes = ['dtf', 'sublimation', 'silkscreen'];
        if (!in_array($printType, $validTypes)) {
            $printType = 'dtf';
        }
        
        $prices = PrintingPrice::where('print_type', $printType)
            ->with('masterItem.productPricings')
            ->orderBy('order')
            ->get();
        
        // Get master items with product pricing for dropdown (filter by print type)
        $productTypeMap = [
            'dtf' => 'Product Type: DTF%',
            'sublimation' => 'Product Type: Sublimation%',
            'silkscreen' => 'Product Type: Silkscreen%'
        ];
        
        $searchPattern = $productTypeMap[$printType] ?? 'Product Type: DTF%';
        
        $productPricingOptions = MasterItem::where('description', 'LIKE', $searchPattern)
            ->whereHas('productPricings', function($q) {
                $q->where('is_active', true);
            })
            ->with(['productPricings' => function($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('description')
            ->get(['id', 'description']);
        
        // Combo discounts separated by price_tier
        $comboDiscounts = PrintingComboDiscount::where('print_type', $printType)
            ->with(['size1', 'size2'])
            ->orderBy('price_tier')
            ->get();
        
        $comboSalesTeam = $comboDiscounts->where('price_tier', 'sales_team');
        $comboAgent = $comboDiscounts->where('price_tier', 'agent');
        
        // Bulk discounts separated by price_tier
        $bulkDiscounts = PrintingBulkDiscount::where('print_type', $printType)
            ->orderBy('price_tier')
            ->orderBy('min_garments')
            ->get();
        
        $bulkSalesTeam = $bulkDiscounts->where('price_tier', 'sales_team');
        $bulkAgent = $bulkDiscounts->where('price_tier', 'agent');
        
        return view('printing.edit_rules', compact(
            'prices', 'productPricingOptions',
            'comboSalesTeam', 'comboAgent',
            'bulkSalesTeam', 'bulkAgent',
            'printType'
        ));
    }
    
    /**
     * Update printing prices
     */
    public function updatePrices(Request $request)
    {
        $request->validate([
            'prices' => 'required|array',
            'prices.*.id' => 'required|exists:printing_prices,id',
            'prices.*.master_item_id' => 'nullable|exists:master_items,id',
        ]);
        
        foreach ($request->input('prices') as $priceData) {
            $price = PrintingPrice::find($priceData['id']);
            $updateData = [];
            
            if (isset($priceData['master_item_id'])) {
                $updateData['master_item_id'] = $priceData['master_item_id'];
                
                // Auto-fill prices from linked product pricing
                $item = MasterItem::with(['productPricings' => function($q) {
                    $q->where('is_active', true);
                }])->find($priceData['master_item_id']);
                
                if ($item) {
                    $pricing = $item->productPricings->keyBy('price_tier');
                    
                    if (isset($pricing['sales_team'])) {
                        $updateData['price'] = $pricing['sales_team']->final_price;
                    }
                    if (isset($pricing['agent_cost'])) {
                        $updateData['agent_price'] = $pricing['agent_cost']->final_price;
                    }
                }
            }
            
            if (!empty($updateData)) {
                $price->update($updateData);
            }
        }
        
        return response()->json(['success' => true, 'message' => 'Prices updated successfully']);
    }
    
    /**
     * Update combo discounts
     */
    public function updateCombos(Request $request)
    {
        $request->validate([
            'combos' => 'array',
            'combos.*.id' => 'nullable|exists:printing_combo_discounts,id',
            'combos.*.size1_id' => 'required|exists:printing_prices,id',
            'combos.*.size2_id' => 'required|exists:printing_prices,id',
            'combos.*.discount_value' => 'required|numeric|min:0',
            'combos.*.price_tier' => 'required|in:sales_team,agent',
        ]);
        
        $printType = $request->input('print_type', 'dtf');
        $validTypes = ['dtf', 'sublimation', 'silkscreen'];
        if (!in_array($printType, $validTypes)) {
            $printType = 'dtf';
        }
        
        // Delete combos for this print type and price_tier
        $tier = $request->input('price_tier', 'sales_team');
        PrintingComboDiscount::where('print_type', $printType)
            ->where('price_tier', $tier)
            ->delete();
        
        // Re-create from input
        foreach ($request->input('combos') as $comboData) {
            PrintingComboDiscount::create([
                'size1_id' => $comboData['size1_id'],
                'size2_id' => $comboData['size2_id'],
                'discount_type' => 'fixed',
                'discount_value' => $comboData['discount_value'],
                'price_tier' => $comboData['price_tier'],
                'active' => true,
                'print_type' => $printType,
            ]);
        }
        
        return response()->json(['success' => true, 'message' => ucfirst(str_replace('_', ' ', $tier)) . ' combo discounts updated successfully']);
    }
    
    /**
     * Update bulk discounts
     */
    public function updateBulk(Request $request)
    {
        $request->validate([
            'bulk' => 'array',
            'bulk.*.id' => 'nullable|exists:printing_bulk_discounts,id',
            'bulk.*.min_transactions' => 'required|integer|min:1',
            'bulk.*.max_transactions' => 'required|integer|min:1',
            'bulk.*.discount_type' => 'required|in:percentage,fixed_amount',
            'bulk.*.discount_percent' => 'required_if:discount_type,percentage|numeric|min:0|max:100',
            'bulk.*.discount_amount' => 'required_if:discount_type,fixed_amount|numeric|min:0',
            'bulk.*.count_combo_as_one' => 'boolean',
            'bulk.*.price_tier' => 'required|in:sales_team,agent',
        ]);
        
        $printType = $request->input('print_type', 'dtf');
        $validTypes = ['dtf', 'sublimation', 'silkscreen'];
        if (!in_array($printType, $validTypes)) {
            $printType = 'dtf';
        }
        
        $tier = $request->input('price_tier', 'sales_team');
        PrintingBulkDiscount::where('print_type', $printType)
            ->where('price_tier', $tier)
            ->delete();
        
        foreach ($request->input('bulk') as $bulkData) {
            PrintingBulkDiscount::create([
                'min_garments' => $bulkData['min_transactions'], // Keep for backward compatibility
                'max_garments' => $bulkData['max_transactions'], // Keep for backward compatibility
                'min_transactions' => $bulkData['min_transactions'],
                'max_transactions' => $bulkData['max_transactions'],
                'discount_type' => $bulkData['discount_type'],
                'discount_percent' => $bulkData['discount_percent'] ?? 0,
                'discount_amount' => $bulkData['discount_amount'] ?? 0,
                'count_combo_as_one' => $bulkData['count_combo_as_one'] ?? true,
                'price_tier' => $bulkData['price_tier'],
                'active' => true,
                'print_type' => $printType,
            ]);
        }
        
        return response()->json(['success' => true, 'message' => ucfirst(str_replace('_', ' ', $tier)) . ' bulk discounts updated successfully']);
    }
    
    /**
     * Add a new print price
     */
    public function addPrice(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'master_item_id' => 'nullable|exists:master_items,id',
        ]);
        
        $printType = $request->input('print_type', 'dtf');
        $validTypes = ['dtf', 'sublimation', 'silkscreen'];
        if (!in_array($printType, $validTypes)) {
            $printType = 'dtf';
        }
        
        $maxOrder = PrintingPrice::where('print_type', $printType)->max('order') ?? 0;
        
        $data = [
            'name' => $request->input('name'),
            'price' => 0,
            'agent_price' => 0,
            'order' => $maxOrder + 1,
            'active' => true,
            'print_type' => $printType,
            'master_item_id' => $request->input('master_item_id'),
        ];
        
        // Auto-fill from linked product pricing
        if ($request->input('master_item_id')) {
            $item = MasterItem::with(['productPricings' => function($q) {
                $q->where('is_active', true);
            }])->find($request->input('master_item_id'));
            
            if ($item) {
                $pricing = $item->productPricings->keyBy('price_tier');
                if (isset($pricing['sales_team'])) {
                    $data['price'] = $pricing['sales_team']->final_price;
                }
                if (isset($pricing['agent_cost'])) {
                    $data['agent_price'] = $pricing['agent_cost']->final_price;
                }
            }
        }
        
        $price = PrintingPrice::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Price added successfully',
            'price' => $price
        ]);
    }
    
    /**
     * Delete a printing price
     */
    public function deletePrice($id)
    {
        $price = PrintingPrice::findOrFail($id);
        $price->delete();
        
        return response()->json(['success' => true, 'message' => 'Price deleted successfully']);
    }
    
    /**
     * Sync printing prices from product pricing
     */
    public function syncFromProductPricing(Request $request)
    {
        $preview = $request->input('preview', true);
        $printType = $request->input('print_type', 'dtf');
        
        $syncResults = [];
        $updatedCount = 0;
        $agentUpdatedCount = 0;
        $createdCount = 0;
        
        // Get master items with DTF product type that have active pricing
        $items = MasterItem::where('description', 'LIKE', 'Product Type: DTF%')
            ->whereHas('productPricings', function($q) {
                $q->where('is_active', true);
            })
            ->with(['productPricings' => function($q) {
                $q->where('is_active', true);
            }])
            ->get();
        
        foreach ($items as $item) {
            $sizeName = $this->extractPaperSize($item->description);
            if (!$sizeName) continue;
            
            $pricing = $item->productPricings->keyBy('price_tier');
            $agentPrice = null;
            if (isset($pricing['agent_cost'])) {
                $agentPrice = $pricing['agent_cost']->final_price;
            }
            
            $printPrice = PrintingPrice::where('name', $sizeName)
                ->where('print_type', $printType)
                ->first();
            
            if ($printPrice) {
                $changes = [];
                if ($printPrice->price != $item->final_price) {
                    $changes['price'] = ['old' => $printPrice->price, 'new' => $item->final_price];
                }
                if ($agentPrice !== null && $printPrice->agent_price != $agentPrice) {
                    $changes['agent_price'] = ['old' => $printPrice->agent_price, 'new' => $agentPrice];
                }
                
                if (!empty($changes)) {
                    if (!$preview) {
                        $updateData = [];
                        if (isset($changes['price'])) {
                            $updateData['price'] = $item->final_price;
                            $updatedCount++;
                        }
                        if (isset($changes['agent_price'])) {
                            $updateData['agent_price'] = $agentPrice;
                            $agentUpdatedCount++;
                        }
                        $printPrice->update($updateData);
                    }
                    $syncResults[] = [
                        'action' => $preview ? 'would_update' : 'updated',
                        'name' => $sizeName,
                        'changes' => $changes
                    ];
                }
            } else {
                if (!$preview) {
                    $maxOrder = PrintingPrice::where('print_type', $printType)->max('order') ?? 0;
                    PrintingPrice::create([
                        'name' => $sizeName,
                        'price' => $item->final_price,
                        'agent_price' => $agentPrice,
                        'order' => $maxOrder + 1,
                        'active' => true,
                        'print_type' => $printType,
                    ]);
                    $createdCount++;
                }
                $syncResults[] = [
                    'action' => $preview ? 'would_create' : 'created',
                    'name' => $sizeName,
                    'price' => $item->final_price,
                    'agent_price' => $agentPrice
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => $preview 
                ? 'Preview: ' . count($syncResults) . ' changes would be made'
                : "Synced {$updatedCount} sales team + {$agentUpdatedCount} agent prices, created {$createdCount} new prices",
            'results' => $syncResults,
            'total' => count($syncResults)
        ]);
    }
    
    /**
     * Extract paper size from description
     */
    private function extractPaperSize($description)
    {
        $patterns = [
            '/Paper Size:\s*(.+)/i',
            '/Size:\s*(.+)/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                return trim($matches[1]);
            }
        }
        
        return null;
    }
    
    /**
     * Get product pricing for a master item (AJAX endpoint)
     */
    public function getProductPricing(Request $request)
    {
        $masterItemId = $request->input('master_item_id');
        
        if (!$masterItemId) {
            return response()->json(['pricing' => null]);
        }
        
        $item = MasterItem::where('id', $masterItemId)
            ->whereHas('productPricings', function($q) {
                $q->where('is_active', true);
            })
            ->with(['productPricings' => function($q) {
                $q->where('is_active', true);
            }])
            ->first();
        
        if (!$item) {
            return response()->json(['pricing' => null]);
        }
        
        $pricing = $item->productPricings->keyBy('price_tier');
        
        $result = [];
        if (isset($pricing['supplier_cost'])) {
            $result['supplier_cost'] = $pricing['supplier_cost']->final_price;
        }
        if (isset($pricing['sales_team'])) {
            $result['sales_team'] = $pricing['sales_team']->final_price;
        }
        if (isset($pricing['agent_cost'])) {
            $result['agent_cost'] = $pricing['agent_cost']->final_price;
        }
        
        return response()->json(['pricing' => $result]);
    }

    /**
     * Get printing options for the garment modal (prices, combos, bulk discounts)
     */
    public function getPrintingOptions($type)
    {
        $user = auth()->user();
        $priceTier = ($user && $user->role === 'sales_agent') ? 'agent' : 'sales_team';
        
        // Get print sizes
        $prices = PrintingPrice::where('print_type', $type)
            ->where('active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'price', 'agent_price'])
            ->map(function($p) use ($priceTier) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => $priceTier === 'agent' && $p->agent_price ? floatval($p->agent_price) : floatval($p->price)
                ];
            });
        
        // Get combo discounts for this print type + price tier
        $combos = PrintingComboDiscount::where('print_type', $type)
            ->where('price_tier', $priceTier)
            ->where('active', true)
            ->with(['size1:id,name', 'size2:id,name'])
            ->get()
            ->map(function($c) {
                return [
                    'id' => $c->id,
                    'size1_id' => $c->size1_id,
                    'size2_id' => $c->size2_id,
                    'size1_name' => $c->size1 ? $c->size1->name : null,
                    'size2_name' => $c->size2 ? $c->size2->name : null,
                    'discount' => floatval($c->discount_value)
                ];
            });
        
        // Get bulk discounts for this print type + price tier
        $bulkTiers = PrintingBulkDiscount::where('print_type', $type)
            ->where('price_tier', $priceTier)
            ->where('active', true)
            ->orderBy('min_transactions')
            ->get(['min_transactions', 'max_transactions', 'discount_type', 'discount_percent', 'discount_amount', 'count_combo_as_one'])
            ->map(function($b) {
                // Normalize discount type: DB uses 'fixed_amount', API uses 'fixed' for simplicity
                $typeNormalized = $b->discount_type === 'fixed_amount' ? 'fixed' : $b->discount_type;
                $discountLabel = $typeNormalized === 'percentage' 
                    ? $b->discount_percent . '%' 
                    : '₱' . number_format($b->discount_amount, 2);
                $rangeLabel = $b->min_transactions . ($b->max_transactions >= 9999 ? '+' : '-' . $b->max_transactions);
                return [
                    'min' => $b->min_transactions,
                    'max' => $b->max_transactions,
                    'type' => $typeNormalized,
                    'percent' => floatval($b->discount_percent),
                    'amount' => floatval($b->discount_amount),
                    'count_combo_as_one' => (bool)$b->count_combo_as_one,
                    'label' => $rangeLabel . ' = ' . $discountLabel
                ];
            });
        
        // Get available print types
        $printTypes = PrintingPrice::select('print_type')
            ->distinct()
            ->where('active', true)
            ->pluck('print_type');
        
        return response()->json([
            'success' => true,
            'prices' => $prices,
            'combos' => $combos,
            'bulk_tiers' => $bulkTiers,
            'print_types' => $printTypes
        ]);
    }

    /**
     * Calculate printing cost for the garment modal
     * Accepts: print_type, print_size_ids[], quantity
     */
    public function calculateModal(Request $request)
    {
        $request->validate([
            'print_type' => 'required|string',
            'print_size_ids' => 'required|array|min:1',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $printType = $request->input('print_type');
        $printSizeIds = $request->input('print_size_ids');
        $quantity = $request->input('quantity');
        
        $user = auth()->user();
        $priceTier = ($user && $user->role === 'sales_agent') ? 'agent' : 'sales_team';
        
        // Calculate base print cost (sum of selected sizes)
        $printCostPerItem = 0;
        $sizes = [];
        
        foreach ($printSizeIds as $sizeId) {
            $price = PrintingPrice::where('print_type', $printType)->find($sizeId);
            if ($price) {
                $unitPrice = ($priceTier === 'agent' && $price->agent_price) ? floatval($price->agent_price) : floatval($price->price);
                $printCostPerItem += $unitPrice;
                $sizes[] = [
                    'id' => $price->id,
                    'name' => $price->name,
                    'price' => $unitPrice
                ];
            }
        }
        
        // Calculate combo discount (may apply kahit isang combo lang)
        $comboDiscount = $this->calculateComboDiscount($printSizeIds, $printType);
        $printCostPerItem -= $comboDiscount;
        
        // Group subtotal (before bulk)
        $subtotal = $printCostPerItem * $quantity;
        
        // Calculate bulk discount
        // count_combo_as_one: kahit maraming print sizes = 1 transaction
        $transactionCount = $quantity; // 1 item = 1 transaction regardless of print count
        $bulkDiscount = $this->calculateBulkDiscount($transactionCount, $subtotal, $printType);
        
        $total = $subtotal - $bulkDiscount;
        
        return response()->json([
            'success' => true,
            'sizes' => $sizes,
            'print_cost_per_item' => $printCostPerItem,
            'combo_discount' => $comboDiscount,
            'quantity' => $quantity,
            'transaction_count' => $transactionCount,
            'subtotal' => $subtotal,
            'bulk_discount' => $bulkDiscount,
            'total' => $total
        ]);
    }
}
