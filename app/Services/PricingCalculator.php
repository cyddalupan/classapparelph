<?php

namespace App\Services;

class PricingCalculator
{
    /**
     * Calculate garment printing pricing (PHP version of the JS logic in create.blade.php)
     */
    public static function calculatePrintPricing(array $printSizes, int $totalQty, array $pricingData): array
    {
        $prices = $pricingData['prices'] ?? [];
        $combos = $pricingData['combos'] ?? [];
        $bulkTiers = $pricingData['bulk_tiers'] ?? [];

        // Calculate print cost per item
        $printCostPerItem = 0;
        $selectedSizeDetails = [];
        foreach ($printSizes as $sizeId) {
            foreach ($prices as $p) {
                if ((int)$p['id'] === (int)$sizeId) {
                    $printCostPerItem += (float)$p['price'];
                    $selectedSizeDetails[] = $p;
                    break;
                }
            }
        }

        // Combo discount
        $comboDiscount = 0;
        $comboDetails = [];
        foreach ($combos as $c) {
            if (in_array((int)$c['size1_id'], array_map('intval', $printSizes)) &&
                in_array((int)$c['size2_id'], array_map('intval', $printSizes))) {
                $comboDetails[] = $c;
            }
        }
        if (!empty($comboDetails)) {
            usort($comboDetails, fn($a, $b) => $b['discount'] - $a['discount']);
            $comboDiscount = (float)$comboDetails[0]['discount'];
            $comboDetails = [$comboDetails[0]];
        }

        $printCostAfterCombo = $printCostPerItem - $comboDiscount;
        $subtotal = $printCostAfterCombo * $totalQty;

        // Bulk discount
        $bulkDiscount = 0;
        $bulkLabel = '';
        for ($bi = count($bulkTiers) - 1; $bi >= 0; $bi--) {
            $tier = $bulkTiers[$bi];
            if ($totalQty >= (int)$tier['min'] && $totalQty <= (int)$tier['max']) {
                if (($tier['type'] ?? '') === 'percentage') {
                    $bulkDiscount = $subtotal * ((float)$tier['percent'] / 100);
                    $bulkLabel = $tier['label'] ?? '';
                } elseif (($tier['type'] ?? '') === 'fixed') {
                    $bulkDiscount = (float)$tier['amount'] * $totalQty;
                    $bulkLabel = $tier['label'] ?? '';
                }
                break;
            }
        }

        $total = $subtotal - $bulkDiscount;

        return [
            'printCostPerItem' => $printCostPerItem,
            'comboDiscount' => $comboDiscount,
            'bulkDiscount' => $bulkDiscount,
            'bulkLabel' => $bulkLabel,
            'printSubtotal' => $total,
            'subtotal' => $subtotal,
            'selectedSizeDetails' => $selectedSizeDetails,
        ];
    }

    /**
     * Merge existing items with add-on items and recalculate totals.
     * Returns updated services JSON array.
     */
    public static function mergeAndRecalculate(array $existingItems, array $addonItems, array $pricingData): array
    {
        $merged = [];

        // Index existing items by key: brand|size|color
        foreach ($existingItems as $item) {
            $key = ($item['name'] ?? '') . '|' . ($item['subItems'][0]['brand'] ?? '') . '|' . ($item['subItems'][0]['size'] ?? '') . '|' . ($item['subItems'][0]['color'] ?? '');
            if (isset($merged[$key])) {
                // Merge quantities
                $merged[$key]['totalQty'] += (int)($item['totalQty'] ?? 0);
                $merged[$key]['subItems'][0]['qty'] += (int)($item['subItems'][0]['qty'] ?? 0);
            } else {
                $merged[$key] = $item;
            }
        }

        // Merge addon items
        foreach ($addonItems as $item) {
            $key = ($item['name'] ?? '') . '|' . ($item['subItems'][0]['brand'] ?? '') . '|' . ($item['subItems'][0]['size'] ?? '') . '|' . ($item['subItems'][0]['color'] ?? '');
            if (isset($merged[$key])) {
                // Merge
                $merged[$key]['totalQty'] += (int)($item['totalQty'] ?? 0);
                $merged[$key]['subItems'][0]['qty'] += (int)($item['subItems'][0]['qty'] ?? 0);
            } else {
                $merged[$key] = $item;
            }
        }

        // Recalculate pricing for each merged item
        $result = [];
        foreach ($merged as $item) {
            $item['totalQty'] = (int)($item['totalQty'] ?? 0);
            $item['subItems'][0]['qty'] = (int)($item['subItems'][0]['qty'] ?? 0);

            // Recalculate garment price
            $unitPrice = (float)($item['subItems'][0]['price'] ?? 0);
            $totalProductPrice = $unitPrice * $item['totalQty'];
            $item['totalProductPrice'] = $totalProductPrice;

            // Recalculate print pricing if present
            if (!empty($item['printing']) && !empty($item['printing']['printSizes'])) {
                $printSizes = array_map('intval', $item['printing']['printSizes']);
                $printCalc = self::calculatePrintPricing($printSizes, $item['totalQty'], $pricingData);

                $item['printing']['printCostPerItem'] = $printCalc['printCostPerItem'];
                $item['printing']['printQty'] = $item['totalQty'];
                $item['printing']['printSubtotal'] = $printCalc['printSubtotal'];
                $item['printing']['comboDiscount'] = $printCalc['comboDiscount'];
                $item['printing']['bulkDiscount'] = $printCalc['bulkDiscount'];
                $item['printing']['comboDetails'] = $printCalc['comboDetails'] ?? [];
            }

            // Special price override check
            if (!empty($item['printing']['isSpecialPrice'])) {
                // Keep the special total as-is
                continue;
            }

            $item['totalPrice'] = $totalProductPrice + ($item['printing']['printSubtotal'] ?? 0);
            $item['totalPricePerUnit'] = ($item['totalQty'] > 0) ? $item['totalPrice'] / $item['totalQty'] : 0;

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Calculate total from merged items
     */
    public static function calculateGrandTotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            // Use totalPrice or calculate from product + print
            if (isset($item['totalPrice'])) {
                $total += (float)$item['totalPrice'];
            } else {
                $unitPrice = (float)($item['subItems'][0]['price'] ?? 0);
                $total += $unitPrice * (int)($item['totalQty'] ?? 0);
                if (!empty($item['printing']['printSubtotal'])) {
                    $total += (float)$item['printing']['printSubtotal'];
                }
            }
        }
        return $total;
    }
}
