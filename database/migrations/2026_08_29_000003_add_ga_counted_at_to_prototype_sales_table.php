<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Timestamp kung kailan na-tag ang sale as SEWING (or beyond) ng Manager.
     * Once set, permanenteng counted na ang job na ito sa GA Dashboard.
     */
    public function up(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->timestamp('ga_counted_at')->nullable()->after('production_stage');
        });
    }

    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dropColumn('ga_counted_at');
        });
    }
};
