<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * DTF-only: allow a flat surcharge on a size-upgrade rule, and seed the
     * agreed "4x Logo = A4 + 10" rule (4 small prints cost a bit more than a
     * single A4 because it's still 4 physical presses).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('printing_size_upgrades', 'surcharge')) {
            Schema::table('printing_size_upgrades', function (Blueprint $table) {
                $table->decimal('surcharge', 10, 2)->default(0)->after('to_size_id');
            });
        }

        // Seed 4x Logo -> A4 (+10) for DTF, sales_team tier, only if missing
        $exists = DB::table('printing_size_upgrades')
            ->where('print_type', 'dtf')
            ->where('from_size_id', 1)
            ->where('from_quantity', 4)
            ->where('to_size_id', 3)
            ->exists();

        if (!$exists) {
            DB::table('printing_size_upgrades')->insert([
                'from_size_id' => 1,
                'from_quantity' => 4,
                'to_size_id' => 3,
                'surcharge' => 10.00,
                'auto_apply' => 1,
                'active' => 1,
                'print_type' => 'dtf',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('printing_size_upgrades')
            ->where('print_type', 'dtf')
            ->where('from_size_id', 1)
            ->where('from_quantity', 4)
            ->where('to_size_id', 3)
            ->delete();

        if (Schema::hasColumn('printing_size_upgrades', 'surcharge')) {
            Schema::table('printing_size_upgrades', function (Blueprint $table) {
                $table->dropColumn('surcharge');
            });
        }
    }
};
