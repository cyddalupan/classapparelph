<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('printing_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('master_item_id')->nullable()->after('id');
        });

        // Link existing DTF prices to their master items by name match
        $prices = DB::table('printing_prices')->where('print_type', 'dtf')->get();
        foreach ($prices as $price) {
            $paperSize = $price->name;
            // Map printing price names to their master item descriptions
            $masterItem = DB::table('master_items')
                ->where('name', 'PRINTING SERVICES')
                ->where('description', 'LIKE', "%Paper Size: {$paperSize}%")
                ->where('description', 'LIKE', '%DTF%')
                ->first();
            if ($masterItem) {
                DB::table('printing_prices')
                    ->where('id', $price->id)
                    ->update(['master_item_id' => $masterItem->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('printing_prices', function (Blueprint $table) {
            $table->dropColumn('master_item_id');
        });
    }
};
