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
     * Resolve a list of print size ids into the upgraded set using the
     * size-upgrade ladder (e.g. DTF: Logo x2 -> Half A4, Half A4 x2 -> A4, ...).
     *
     * Iterative + remainder aware so chains work: e.g. 4x Logo -> 2x Half A4
     * -> 1x A4, while 3x Logo -> 1x Half A4 + 1x Logo.
     *
     * @return array{0: array<int>, 1: array<int,array>} [upgraded size ids (sorted), upgrade log]
     */
    private function resolveUpgradedSizes($printSizeIds, $printType = 'dtf')
    {
        $rules = PrintingSizeUpgrade::where('print_type', $printType)
            ->where('active', true)
            ->orderBy('from_quantity', 'desc')
            ->orderBy('from_size_id')
            ->get();

        // counts[sizeId] = quantity (sizeId keyed as string by array semantics)
        $counts = [];
        foreach ($printSizeIds as $id) {
            $id = (int) $id;
            $counts[$id] = ($counts[$id] ?? 0) + 1;
        }

        $upgraded = [];
        $surcharge = 0.0;
        if ($rules->isEmpty()) {
            return [array_values($printSizeIds), $upgraded, $surcharge];
        }

        $names = PrintingPrice::where('print_type', $printType)->pluck('name', 'id');

        $changed = true;
        $guard = 0;
        while ($changed && $guard++ < 200) {
            $changed = false;
            foreach ($rules as $rule) {
                $from = (int) $rule->from_size_id;
                $qty = (int) $rule->from_quantity;
                $to = (int) $rule->to_size_id;
                if ($qty < 1) {
                    continue;
                }
                if (($counts[$from] ?? 0) >= $qty) {
                    $times = intdiv($counts[$from], $qty);
                    $counts[$from] -= $times * $qty;
                    $counts[$to] = ($counts[$to] ?? 0) + $times;
                    $ruleSurcharge = floatval($rule->surcharge ?? 0) * $times;
                    $surcharge += $ruleSurcharge;
                    $changed = true;
                    $upgraded[] = [
                        'from' => $names[$from] ?? null,
                        'from_quantity' => $qty,
                        'to' => $names[$to] ?? null,
                        'to_quantity' => $times,
                        'surcharge' => $ruleSurcharge,
                    ];
                }
            }
        }

        // Rebuild the flat list of resulting size ids, keeping selection order where possible
        $result = [];
        foreach ($counts as $id => $c) {
            for ($i = 0; $i < $c; $i++) {
                $result[] = (int) $id;
            }
        }

        return [$result, $upgraded, round($surcharge, 2)];
    }

    /**
     * Apply size upgrades (returns the upgrade log for the breakdown/receipt).
     */
    private function applySizeUpgrades($printSizeIds, $printType = 'dtf')
    {
        [, $upgraded] = $this->resolveUpgradedSizes($printSizeIds, $printType);
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
     * Update the size-upgrade ladder (delete + recreate for this print type).
     */
    public function updateUpgrades(Request $request)
    {
        $request->validate([
            'upgrades' => 'array',
            'upgrades.*.from_size_id' => 'required|exists:printing_prices,id',
            'upgrades.*.from_quantity' => 'required|integer|min:2',
            'upgrades.*.to_size_id' => 'required|exists:printing_prices,id',
            'upgrades.*.surcharge' => 'nullable|numeric|min:0',
        ]);

        $printType = $request->input('print_type', 'dtf');
        $validTypes = ['dtf', 'sublimation', 'silkscreen'];
        if (!in_array($printType, $validTypes)) {
            $printType = 'dtf';
        }

        PrintingSizeUpgrade::where('print_type', $printType)->delete();

        foreach ($request->input('upgrades', []) as $row) {
            PrintingSizeUpgrade::create([
                'from_size_id' => $row['from_size_id'],
                'from_quantity' => $row['from_quantity'],
                'to_size_id' => $row['to_size_id'],
                'surcharge' => $row['surcharge'] ?? 0,
                'auto_apply' => true,
                'active' => true,
                'print_type' => $printType,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Size upgrade rules updated successfully']);
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
            ->orderBy('order')
            ->get();
        
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
        
        // Size upgrade ladder (e.g. Logo x2 -> Half A4 ...)
        $upgrades = PrintingSizeUpgrade::where('print_type', $printType)
            ->where('active', true)
            ->orderBy('from_quantity', 'desc')
            ->orderBy('from_size_id')
            ->get();
        
        return view('printing.edit_rules', compact(
            'prices',
            'comboSalesTeam', 'comboAgent',
            'bulkSalesTeam', 'bulkAgent',
            'upgrades',
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
            'prices.*.supplier_cost' => 'nullable|numeric|min:0',
            'prices.*.price' => 'nullable|numeric|min:0',
            'prices.*.agent_price' => 'nullable|numeric|min:0',
        ]);

        foreach ($request->input('prices') as $priceData) {
            $price = PrintingPrice::find($priceData['id']);
            $updateData = [];

            // Editable costs (manual override on the print price row)
            if (array_key_exists('supplier_cost', $priceData)) {
                $updateData['supplier_cost'] = $priceData['supplier_cost'] === '' || $priceData['supplier_cost'] === null
                    ? null : $priceData['supplier_cost'];
            }
            if (array_key_exists('price', $priceData)) {
                $updateData['price'] = $priceData['price'] === '' || $priceData['price'] === null
                    ? 0 : $priceData['price'];
            }
            if (array_key_exists('agent_price', $priceData)) {
                $updateData['agent_price'] = $priceData['agent_price'] === '' || $priceData['agent_price'] === null
                    ? null : $priceData['agent_price'];
            }

            // Optional link to a master item (kept for the sync feature; no longer shown in UI)
            if (isset($priceData['master_item_id'])) {
                $updateData['master_item_id'] = $priceData['master_item_id'];
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

        // The UI saves one tier at a time; the top-level price_tier identifies
        // which tier's rows are being replaced.  Fall back to the first row's
        // tier so an older client that only sends per-row tiers still works.
        $combosInput = $request->input('combos', []);
        $tier = $request->input('price_tier');
        if (!$tier) {
            $tier = $combosInput[0]['price_tier'] ?? 'sales_team';
        }

        // Delete + re-create atomically, and de-duplicate by unordered pair so
        // overlapping rows can never trip the unique constraint mid-batch.
        DB::transaction(function () use ($printType, $tier, $combosInput) {
            PrintingComboDiscount::where('print_type', $printType)
                ->where('price_tier', $tier)
                ->delete();

            $seen = [];
            foreach ($combosInput as $comboData) {
                $rowTier = $comboData['price_tier'] ?? $tier;
                if ($rowTier !== $tier) {
                    continue; // only persist the tier being edited
                }

                $a = (int) $comboData['size1_id'];
                $b = (int) $comboData['size2_id'];
                $pairKey = $a <= $b ? "{$a}-{$b}" : "{$b}-{$a}";
                if (isset($seen[$pairKey])) {
                    continue; // ignore duplicate / mirror pair
                }
                $seen[$pairKey] = true;

                PrintingComboDiscount::create([
                    'size1_id' => $a,
                    'size2_id' => $b,
                    'discount_type' => 'fixed',
                    'discount_value' => $comboData['discount_value'],
                    'price_tier' => $tier,
                    'active' => true,
                    'print_type' => $printType,
                ]);
            }
        });

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
            'supplier_cost' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'agent_price' => 'nullable|numeric|min:0',
        ]);
        
        $printType = $request->input('print_type', 'dtf');
        $validTypes = ['dtf', 'sublimation', 'silkscreen'];
        if (!in_array($printType, $validTypes)) {
            $printType = 'dtf';
        }
        
        $maxOrder = PrintingPrice::where('print_type', $printType)->max('order') ?? 0;
        
        $data = [
            'name' => $request->input('name'),
            'supplier_cost' => $request->input('supplier_cost'),
            'price' => $request->input('price', 0) ?: 0,
            'agent_price' => $request->input('agent_price'),
            'order' => $maxOrder + 1,
            'active' => true,
            'print_type' => $printType,
            'master_item_id' => $request->input('master_item_id'),
        ];
        
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

        // Size-upgrade ladder (small -> big), used by the modal to auto-combine prints
        $upgrades = PrintingSizeUpgrade::where('print_type', $type)
            ->where('active', true)
            ->orderBy('from_size_id')
            ->get()
            ->map(function ($u) {
                return [
                    'from_size_id' => (int) $u->from_size_id,
                    'from_quantity' => (int) $u->from_quantity,
                    'to_size_id' => (int) $u->to_size_id,
                ];
            });
        
        return response()->json([
            'success' => true,
            'prices' => $prices,
            'combos' => $combos,
            'bulk_tiers' => $bulkTiers,
            'print_types' => $printTypes,
            'upgrades' => $upgrades
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
        
        // Apply size upgrades first (e.g. Logo x2 -> Half A4)
        [$resolvedSizeIds, $upgradeLog, $upgradeSurcharge] = $this->resolveUpgradedSizes($printSizeIds, $printType);

        // Calculate base print cost (sum of the UPGRADED sizes)
        $printCostPerItem = $upgradeSurcharge;
        $sizes = [];
        
        foreach ($resolvedSizeIds as $sizeId) {
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
        
        // Calculate combo discount on the UPGRADED sizes
        $comboDiscount = $this->calculateComboDiscount($resolvedSizeIds, $printType);
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
            'upgrades' => $upgradeLog,
            'upgrade_surcharge' => $upgradeSurcharge,
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
