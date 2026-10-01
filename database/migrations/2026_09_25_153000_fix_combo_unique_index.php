<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * printing_combo_discounts had a GLOBAL unique index on (size1_id, size2_id)
     * with no print_type / price_tier in the key, while updateCombos() does a
     * delete-then-reinsert scoped by (print_type, price_tier).  Saving the DTF
     * rules page therefore blew up with:
     *   1062 Duplicate entry '1-2' for key '..._size1_id_size2_id_unique'
     *
     * A combo is only unique within its print_type + price_tier, so replace the
     * index with the correct composite key.  Also keep a plain index on
     * size1_id so the existing foreign key stays satisfied.
     */
    public function up(): void
    {
        // 1) The foreign key on size1_id leans on the old unique index, so give
        //    it its own index first, otherwise MySQL refuses the drop (err 1553).
        Schema::table('printing_combo_discounts', function (Blueprint $table) {
            $table->index('size1_id', 'printing_combo_discounts_size1_id_index');
        });

        // 2) Drop the over-broad global unique (size1_id, size2_id).
        Schema::table('printing_combo_discounts', function (Blueprint $table) {
            $table->dropUnique('printing_combo_discounts_size1_id_size2_id_unique');
        });

        // 3) Scope the uniqueness to the print_type + price_tier it really belongs to.
        Schema::table('printing_combo_discounts', function (Blueprint $table) {
            $table->unique(
                ['print_type', 'price_tier', 'size1_id', 'size2_id'],
                'printing_combo_discounts_scope_pair_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('printing_combo_discounts', function (Blueprint $table) {
            $table->dropUnique('printing_combo_discounts_scope_pair_unique');
            $table->unique(['size1_id', 'size2_id']);
        });

        Schema::table('printing_combo_discounts', function (Blueprint $table) {
            $table->dropIndex('printing_combo_discounts_size1_id_index');
        });
    }
};
