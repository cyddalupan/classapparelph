<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * DTF bulk discounts (sales_team) — FIXED AMOUNT (not percentage):
     *     10-19   ->  PHP  5 fixed
     *     20-35   ->  PHP 10 fixed
     *     36-50   ->  PHP 15 fixed
     *     51-100  ->  PHP 20 fixed
     *    101-300  ->  PHP 25 fixed
     *    301-500  ->  PHP 30 fixed
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
            [10, 19, 5.00],
            [20, 35, 10.00],
            [36, 50, 15.00],
            [51, 100, 20.00],
            [101, 300, 25.00],
            [301, 500, 30.00],
        ];

        foreach ($rows as [$min, $max, $amount]) {
            DB::table('printing_bulk_discounts')->insert([
                'min_garments' => $min,          // kept for backward compatibility
                'max_garments' => $max,
                'min_transactions' => $min,
                'max_transactions' => $max,
                'discount_percent' => 0.00,
                'discount_type' => 'fixed_amount',
                'discount_amount' => $amount,
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
