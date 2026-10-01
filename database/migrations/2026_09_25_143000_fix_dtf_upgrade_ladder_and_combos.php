<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * DTF only. Fix the size-upgrade ladder to the agreed doubling rule:
     *   Logo x2 = Half A4, Half A4 x2 = A4, A4 x2 = A3, A3 x2 = A2
     * and seed the missing combos needed by the upgrade scenarios
     *   (Half A4 + Logo, Half A4 + A4).
     *
     * DTF price ids: 1=Logo, 2=Half A4, 3=A4, 4=A3, 16=A2
     */
    public function up(): void
    {
        // ---- 1. Rebuild DTF upgrade ladder ----
        DB::table('printing_size_upgrades')->where('print_type', 'dtf')->delete();

        $ladder = [
            ['from_size_id' => 1,  'from_quantity' => 2, 'to_size_id' => 2],   // Logo x2 -> Half A4
            ['from_size_id' => 2,  'from_quantity' => 2, 'to_size_id' => 3],   // Half A4 x2 -> A4
            ['from_size_id' => 3,  'from_quantity' => 2, 'to_size_id' => 4],   // A4 x2 -> A3
            ['from_size_id' => 4,  'from_quantity' => 2, 'to_size_id' => 16],  // A3 x2 -> A2
        ];

        foreach ($ladder as $row) {
            DB::table('printing_size_upgrades')->insert(array_merge($row, [
                'auto_apply' => 1,
                'active' => 1,
                'print_type' => 'dtf',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // ---- 2. Seed missing DTF combos (only if the pair does not exist yet) ----
        $combos = [
            ['size1_id' => 2, 'size2_id' => 1, 'discount_value' => 20.00], // Half A4 + Logo
            ['size1_id' => 2, 'size2_id' => 3, 'discount_value' => 25.00], // Half A4 + A4
        ];

        foreach ($combos as $c) {
            $exists = DB::table('printing_combo_discounts')
                ->where('print_type', 'dtf')
                ->where('price_tier', 'sales_team')
                ->where(function ($q) use ($c) {
                    $q->where(function ($q2) use ($c) {
                        $q2->where('size1_id', $c['size1_id'])->where('size2_id', $c['size2_id']);
                    })->orWhere(function ($q2) use ($c) {
                        $q2->where('size1_id', $c['size2_id'])->where('size2_id', $c['size1_id']);
                    });
                })
                ->exists();

            if (!$exists) {
                DB::table('printing_combo_discounts')->insert([
                    'size1_id' => $c['size1_id'],
                    'size2_id' => $c['size2_id'],
                    'discount_type' => 'fixed',
                    'discount_value' => $c['discount_value'],
                    'active' => 1,
                    'price_tier' => 'sales_team',
                    'print_type' => 'dtf',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Restore the previous DTF ladder
        DB::table('printing_size_upgrades')->where('print_type', 'dtf')->delete();
        $previous = [
            ['from_size_id' => 1, 'from_quantity' => 2, 'to_size_id' => 2],
            ['from_size_id' => 1, 'from_quantity' => 3, 'to_size_id' => 3],
            ['from_size_id' => 2, 'from_quantity' => 2, 'to_size_id' => 4],
        ];
        foreach ($previous as $row) {
            DB::table('printing_size_upgrades')->insert(array_merge($row, [
                'auto_apply' => 1, 'active' => 1, 'print_type' => 'dtf',
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        // Remove the combos this migration added (only if they still match our seed values)
        DB::table('printing_combo_discounts')->where('print_type', 'dtf')
            ->where('price_tier', 'sales_team')
            ->where(function ($q) {
                $q->where(function ($q2) { $q2->where('size1_id', 2)->where('size2_id', 1)->where('discount_value', 20.00); })
                  ->orWhere(function ($q2) { $q2->where('size1_id', 2)->where('size2_id', 3)->where('discount_value', 25.00); });
            })->delete();
    }
};
