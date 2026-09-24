<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * priority_set_at — timestamp kung kailan huling na-tag/changed ang PRIO ng sale
 * (mula sa dropdown sa Manager List / Set Time List). (Andrew 2026-09-24)
 *
 * Additive lang: bagong nullable column, walang binabago sa existing data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('prototype_sales', 'priority_set_at')) {
                $table->timestamp('priority_set_at')->nullable()->after('priority');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            if (Schema::hasColumn('prototype_sales', 'priority_set_at')) {
                $table->dropColumn('priority_set_at');
            }
        });
    }
};
