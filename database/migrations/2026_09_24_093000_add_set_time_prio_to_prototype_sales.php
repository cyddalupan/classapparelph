<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Set Time List — SEPARATE "Prio Reminder" (Andrew 2026-09-24).
 *
 * Hindi ito ang Manager List na Prio 1..15 (unique slots). Ito ay sariling
 * reminder/flag lang: "pinaprio pa ba ni Manager ang order na ito?"
 *
 * - set_time_prio      : tinyint NULL (1 = pinaprio pa rin, 0 = hindi na, NULL = wala pang sagot)
 * - set_time_prio_at   : timestamp kung kailan huling na-set/changed ang reminder
 *
 * Additive lang: bagong nullable columns, walang binabago sa existing `priority`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('prototype_sales', 'set_time_prio')) {
                $table->tinyInteger('set_time_prio')->nullable()->after('set_time_sort');
            }
            if (!Schema::hasColumn('prototype_sales', 'set_time_prio_at')) {
                $table->timestamp('set_time_prio_at')->nullable()->after('set_time_prio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            if (Schema::hasColumn('prototype_sales', 'set_time_prio_at')) {
                $table->dropColumn('set_time_prio_at');
            }
            if (Schema::hasColumn('prototype_sales', 'set_time_prio')) {
                $table->dropColumn('set_time_prio');
            }
        });
    }
};
