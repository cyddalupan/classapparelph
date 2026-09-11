<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user pricing tier.
 *
 *   'sales' (default) → gumagamit ng S Sales Price (price column)
 *   'agent'           → gumagamit ng AGENT Price (agent_price column), fallback sa sales kung blank
 *
 * Additive lang: lahat ng existing users ay mananatiling 'sales' (walang pagbabago sa behavior).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'price_tier')) {
                $table->string('price_tier', 20)->default('sales')->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'price_tier')) {
                $table->dropColumn('price_tier');
            }
        });
    }
};
