<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * DTF bulk discounts: the top tier is open-ended. Andrew confirmed
     * "yes 30 parin" past 500, so 301+ keeps the PHP 30 fixed discount.
     */
    public function up(): void
    {
        DB::table('printing_bulk_discounts')
            ->where('print_type', 'dtf')
            ->where('price_tier', 'sales_team')
            ->where('min_transactions', 301)
            ->update([
                'max_garments' => 9999,
                'max_transactions' => 9999,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('printing_bulk_discounts')
            ->where('print_type', 'dtf')
            ->where('price_tier', 'sales_team')
            ->where('min_transactions', 301)
            ->update([
                'max_garments' => 500,
                'max_transactions' => 500,
                'updated_at' => now(),
            ]);
    }
};
