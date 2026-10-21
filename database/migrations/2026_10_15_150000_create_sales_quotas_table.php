<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sales Agent quota targets for the "Team Board" (Andrew 2026-10-15).
 * Purely ADDITIVE — one row per agent per period. Revenue-based quota.
 * Nothing here affects any existing table or query.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_quotas')) {
            Schema::create('sales_quotas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('period_type', 20)->default('monthly'); // monthly | weekly
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('quota_amount', 12, 2)->default(0);
                $table->string('note')->nullable();
                $table->boolean('active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'period_type', 'period_start'], 'sales_quotas_agent_period_unique');
                $table->index(['period_type', 'period_start', 'active'], 'sales_quotas_period_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_quotas');
    }
};
