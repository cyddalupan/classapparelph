<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * ProductionCheckCountService
 *
 * Read-only helper: binibilang kung ilan na ang na-check na GA / QA1 / QA2
 * checkbox sa Production Slip ng bawat sale, MULA sa `production_checklists`
 * (`items` = main slip, `additional_items` = additional slip).
 *
 * Dalawang bilang kada role:
 *   - rows  : bilang ng na-check na row / kabuuang row (per checkbox)
 *   - pcs   : pirasong tapos / kabuuang piraso (per quantity)
 *
 * Ang QUANTITY ng bawat row ay kinukuha sa SALE SERVICES (source of truth):
 *   - roster row -> `sublimationForm.roster[].qty`   (hal. MEDIUM, qty 3)
 *   - size   row -> `sublimationForm.sizes[].quantity` (nasa label: "MEDIUM ×3")
 * Hindi ito kinukuha sa checklist JSON dahil ang `number` doon ay JERSEY number,
 * hindi quantity — kaya for sale na "M ×" lang ang value pero qty=3, tama pa rin.
 *
 * Sakop din ang "roster-mode vs size-mode": kapag may roster ang isang product,
 * roster table lang ang nire-render (ang size rows ay hindi nakikita/checkable),
 * kaya mga roster row lang ang binibilang. Kapag walang roster, size rows naman.
 *
 * Walang schema change, walang write — puro pagbilang lang.
 */
class ProductionCheckCountService
{
    /** Item types na may GA/QA1/QA2 checkbox sa production slip. */
    public const CHECK_TYPES = ['roster', 'size'];

    /** Blangkong tally (rows = checkbox count; pcs = per-quantity count). */
    public static function empty(): array
    {
        return [
            'ga'  => ['done' => 0, 'total' => 0, 'pcs_done' => 0, 'pcs_total' => 0],
            'qa1' => ['done' => 0, 'total' => 0, 'pcs_done' => 0, 'pcs_total' => 0],
            'qa2' => ['done' => 0, 'total' => 0, 'pcs_done' => 0, 'pcs_total' => 0],
        ];
    }

    /**
     * Per-sale counts para sa maraming sale (batched).
     *
     * @param  array<int|string> $saleIds
     * @return array<int, array>
     */
    public static function forSales(array $saleIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $saleIds))));
        if (empty($ids)) {
            return [];
        }

        $out = [];
        foreach ($ids as $id) {
            $out[$id] = self::empty();
        }

        $checklists = DB::table('production_checklists')
            ->whereIn('sale_id', $ids)
            ->get(['sale_id', 'items', 'additional_items']);

        if ($checklists->isEmpty()) {
            return $out;
        }

        // Source data para sa bawat row quantity.
        $sales = DB::table('prototype_sales')
            ->whereIn('id', $ids)
            ->get(['id', 'services'])
            ->keyBy('id');

        $changes = DB::table('prototype_sale_changes')
            ->whereIn('sale_id', $ids)
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('sale_id');

        foreach ($checklists as $row) {
            $saleId = (int) $row->sale_id;
            $sale = $sales[$saleId] ?? null;

            $maps = $sale
                ? self::rowQtyMaps($sale, $changes[$saleId] ?? collect())
                : ['main' => [], 'add' => []];

            $agg = $out[$saleId] ?? self::empty();

            // items = main slip; additional_items = additional slip
            foreach ([['items', $maps['main']], ['additional_items', $maps['add']]] as $pair) {
                [$field, $map] = $pair;
                $raw = $row->{$field};
                $items = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);

                // Pointer kada (product|type) para i-consume ang qty sa tamang pagkakasunod.
                $cursor = [];

                foreach ($items as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $type = $item['type'] ?? '';
                    if (!in_array($type, self::CHECK_TYPES, true)) {
                        continue;
                    }

                    $product = $item['product'] ?? 0;
                    $mapKey = $product;
                    $prodMap = $map[$mapKey] ?? null;

                    // Phantom row: hindi ito nire-render sa slip (hal. size rows ng roster-mode
                    // product), kaya hindi ito kasama sa bilang.
                    if ($prodMap !== null && ($prodMap['mode'] ?? '') !== $type) {
                        continue;
                    }

                    // Quantity: mula sa sale services (source of truth).
                    $qty = null;
                    if ($prodMap !== null) {
                        $key = $mapKey . '|' . $type;
                        $i = $cursor[$key] ?? 0;
                        $qty = $prodMap['qtys'][$i] ?? null;
                        $cursor[$key] = $i + 1;
                    }
                    if ($qty === null) {
                        // Fallback: parse mula sa label/value (size rows may "×N").
                        $qty = self::rowQty($item);
                    }
                    $qty = max(0, (int) $qty);

                    // Rows (checkbox count)
                    $agg['ga']['total']++;
                    $agg['qa1']['total']++;
                    $agg['qa2']['total']++;

                    if (!empty($item['ga_done']))  { $agg['ga']['done']++; }
                    if (!empty($item['qa1_done'])) { $agg['qa1']['done']++; }
                    if (!empty($item['qa2_done'])) { $agg['qa2']['done']++; }

                    // Piraso (per-quantity count)
                    $agg['ga']['pcs_total']  += $qty;
                    $agg['qa1']['pcs_total'] += $qty;
                    $agg['qa2']['pcs_total'] += $qty;

                    if (!empty($item['ga_done'])) {
                        $agg['ga']['pcs_done'] += $qty;
                    }
                    $agg['qa1']['pcs_done'] += self::rowPcsDone($item, 'qa1', $qty);
                    $agg['qa2']['pcs_done'] += self::rowPcsDone($item, 'qa2', $qty);
                }
            }

            $out[$saleId] = $agg;
        }

        return $out;
    }

    /**
     * Counts para sa isang sale lang.
     */
    public static function forSale($saleId): array
    {
        $all = self::forSales([$saleId]);
        return $all[(int) $saleId] ?? self::empty();
    }

    /**
     * Pirasong tapos sa isang row para sa QA1/QA2:
     *  - na-check (`qaN_done`) => buong qty
     *  - kung hindi => ang manual `qaN_count` (partial), naka-cap sa qty
     */
    private static function rowPcsDone(array $item, string $role, int $qty): int
    {
        if (!empty($item[$role . '_done'])) {
            return $qty;
        }

        $count = $item[$role . '_count'] ?? null;
        if ($count === null || $count === '') {
            return 0;
        }

        $n = (int) $count;
        if ($n < 0) {
            $n = 0;
        }

        return $qty > 0 ? min($n, $qty) : $n;
    }

    /**
     * Fallback quantity parse mula sa checklist row mismo (hal. size label "MEDIUM ×3").
     */
    public static function rowQty(array $item): int
    {
        $type = $item['type'] ?? '';
        $candidates = $type === 'size'
            ? [$item['label'] ?? '', $item['value'] ?? '']
            : [$item['value'] ?? '', $item['label'] ?? ''];

        foreach ($candidates as $src) {
            if (is_string($src) && preg_match('/×\s*(\d+)/u', $src, $m)) {
                return (int) $m[1];
            }
        }

        return $type === 'roster' ? 1 : 0;
    }

    /**
     * Bumuo ng per-product row-qty maps (main + additional) na tumutugma sa
     * pagkakasunod-sunod ng mga row sa production slip.
     *
     * @return array{main: array<int, array{mode:string,qtys:array<int,int>}>, add: array<string, array{mode:string,qtys:array<int,int>}>}
     */
    private static function rowQtyMaps(object $sale, $changes): array
    {
        $services = is_string($sale->services)
            ? (json_decode($sale->services, true) ?: [])
            : (is_array($sale->services) ? $sale->services : []);

        $changes = $changes ?: collect();

        // Item IDs na "additional" (kaparehong logic ng main slip builder).
        $originalItemIds = [];
        $reprocessedItemIds = [];
        if ($changes->isNotEmpty()) {
            $firstBefore = json_decode($changes->first()->services_before ?? '[]', true) ?: [];
            $originalItemIds = array_column($firstBefore, 'id');
            foreach ($changes as $c) {
                if (($c->type ?? '') === 'reprocess' && ($c->status ?? '') === 'approved') {
                    foreach (json_decode($c->services_after ?? '[]', true) ?: [] as $a) {
                        if (!empty($a['id'])) {
                            $reprocessedItemIds[] = $a['id'];
                        }
                    }
                }
            }
        }

        // --- MAIN slip: isang "slip" kada sublimationForm item na hindi additional ---
        $main = [];
        $p = 0;
        foreach ($services as $item) {
            if (!isset($item['sublimationForm'])) {
                continue;
            }
            $itemId = $item['id'] ?? null;
            if ($changes->isNotEmpty() && $itemId
                && !in_array($itemId, $originalItemIds)
                && !in_array($itemId, $reprocessedItemIds)) {
                continue; // additional -> hiwalay na slip
            }
            $main[$p] = self::cardRowMap($item['sublimationForm'] ?? []);
            $p++;
        }

        // --- ADDITIONAL slip: mirror ng collectAdditionalProducts (order lang ang kailangan) ---
        $add = [];
        if ($changes->isNotEmpty()) {
            $approvedChanges = $changes->filter(function ($c) {
                if (($c->status ?? '') !== 'approved') return false;
                $before = json_decode($c->services_before ?? '[]', true) ?: [];
                $after = json_decode($c->services_after ?? '[]', true) ?: [];
                return count($after) > count($before);
            })->sortByDesc('created_at');

            $additionalItems = [];
            foreach ($approvedChanges as $change) {
                $before = json_decode($change->services_before ?? '[]', true) ?: [];
                $after = json_decode($change->services_after ?? '[]', true) ?: [];
                $beforeIds = array_column($before, 'id');
                foreach ($after as $it) {
                    if (!in_array($it['id'] ?? null, $beforeIds)) {
                        $additionalItems[] = $it;
                    }
                }
            }

            $additionalFromServices = [];
            foreach ($services as $item) {
                $itemId = $item['id'] ?? null;
                if ($itemId && !in_array($itemId, $originalItemIds) && !in_array($itemId, $reprocessedItemIds)) {
                    $additionalFromServices[] = $item;
                }
            }

            $currentServiceIds = array_column($services, 'id');
            $allAdditional = [];
            $seenIds = [];
            foreach (array_merge($additionalFromServices, $additionalItems) as $item) {
                $itemId = $item['id'] ?? 0;
                if ($itemId && !in_array($itemId, $currentServiceIds)) {
                    continue;
                }
                if (!isset($seenIds[$itemId])) {
                    $seenIds[$itemId] = true;
                    $allAdditional[] = $item;
                }
            }

            foreach ($allAdditional as $i => $item) {
                $add['add:' . $i] = self::cardRowMap($item['sublimationForm'] ?? []);
            }
        }

        return ['main' => $main, 'add' => $add];
    }

    /**
     * Row map ng isang product card (roster-mode o size-mode) + qty kada row.
     *
     * @return array{mode:string,qtys:array<int,int>}
     */
    private static function cardRowMap(array $sf): array
    {
        $rawRoster = $sf['roster'] ?? [];
        if (!empty($rawRoster)) {
            return ['mode' => 'roster', 'qtys' => self::rosterQtys($rawRoster)];
        }

        $sizes = $sf['sizes'] ?? [];
        $named = [];
        $clean = [];
        foreach ($sizes as $s) {
            if (!empty($s['name'])) {
                $named[] = $s;
            } else {
                $clean[] = $s;
            }
        }
        if (!empty($named)) {
            return ['mode' => 'roster', 'qtys' => self::rosterQtys($named)];
        }

        return ['mode' => 'size', 'qtys' => self::sizeQtys($clean)];
    }

    /** Quantity kada roster entry (r.qty -> columns QTY -> 1). */
    private static function rosterQtys(array $roster): array
    {
        $out = [];
        foreach ($roster as $r) {
            $q = null;
            if (isset($r['qty']) && $r['qty'] !== '' && $r['qty'] !== null) {
                $q = (int) $r['qty'];
            } elseif (!empty($r['columns']) && is_array($r['columns'])) {
                foreach ($r['columns'] as $c) {
                    $k = strtoupper(trim((string) ($c[0] ?? '')));
                    if ($k === 'QTY' || $k === 'QUANTITY') {
                        $q = (int) ($c[1] ?? 0);
                        break;
                    }
                }
            }
            if ($q === null) {
                $q = 1; // isang roster entry = isang piraso kapag walang nakalagay
            }
            $out[] = max(0, $q);
        }
        return $out;
    }

    /** Quantity kada size entry (quantity -> qty -> 0). */
    private static function sizeQtys(array $sizes): array
    {
        $out = [];
        foreach ($sizes as $s) {
            $q = (int) ($s['quantity'] ?? $s['qty'] ?? 0);
            $out[] = max(0, $q);
        }
        return $out;
    }
}
