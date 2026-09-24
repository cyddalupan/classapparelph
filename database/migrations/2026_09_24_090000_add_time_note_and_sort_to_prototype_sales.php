<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Set Time feature additions (Andrew 2026-09-24):
 *
 * - time_note        : reason/note na ini-type ng agent/user kapag nag-set ng needed time.
 * - set_time_sort    : custom arrangement order ng Manager sa bagong "Set Time List" page
 *                      (drag-and-drop). Nullable — kung null, hindi pa ina-arrange (falls back
 *                      sa needed_by ASC sa list).
 *
 * Additive lang: bagong nullable columns, walang binabago sa existing columns/data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('prototype_sales', 'time_note')) {
                $table->text('time_note')->nullable()->after('needed_by');
            }
            if (!Schema::hasColumn('prototype_sales', 'set_time_sort')) {
                $table->integer('set_time_sort')->nullable()->after('time_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            if (Schema::hasColumn('prototype_sales', 'set_time_sort')) {
                $table->dropColumn('set_time_sort');
            }
            if (Schema::hasColumn('prototype_sales', 'time_note')) {
                $table->dropColumn('time_note');
            }
        });
    }
};
