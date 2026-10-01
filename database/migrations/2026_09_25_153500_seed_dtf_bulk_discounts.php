<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * DTF bulk discounts (sales_team).
     *
     * DTF had a single stray rule ("10-19 txns = PHP 10 fixed") that did not
     * match the volume ladder used by the other print types.  Align DTF with the
     * house standard:
     *     10-24  ->  5%
     *     25-49  -> 10%
     *     50+    -> 15%
     */
    public function up(): void
    {
        $printType = 'dtf';
        $tier = 'sales_team';

        DB::table('printing_bulk_discounts')
            ->where('print_type', $printType)
            ->where('price_tier', $tier)
            ->delete();

        $now = now();
        $rows = [
            ['min' => 10, 'max' => 24, 'percent' => 5.00],
            ['min' => 25, 'max' => 49, 'percent' => 10.00],
            ['min' => 50, 'max' => 9999, 'percent' => 15.00],
        ];

        foreach ($rows as $r) {
            DB::table('printing_bulk_discounts')->insert([
                'min_garments' => $r['min'],       // kept for backward compatibility
                'max_garments' => $r['max'],
                'min_transactions' => $r['min'],
                'max_transactions' => $r['max'],
                'discount_percent' => $r['percent'],
                'discount_type' => 'percentage',
                'discount_amount' => 0.00,
                'count_combo_as_one' => true,
                'active' => true,
                'price_tier' => $tier,
                'print_type' => $printType,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('printing_bulk_discounts')
            ->where('print_type', 'dtf')
            ->where('price_tier', 'sales_team')
            ->delete();
    }
};
