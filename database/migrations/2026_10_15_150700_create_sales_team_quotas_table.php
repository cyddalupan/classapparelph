<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Team total (buong-buwan) quota — Andrew 2026-10-15.
 * Per period (buwan) ang team quota, para malinis kahit magpalit ng buwan.
 * ADDITIVE lang.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_team_quotas')) {
            Schema::create('sales_team_quotas', function (Blueprint $table) {
                $table->id();
                $table->string('period_type')->default('monthly');
                $table->date('period_start');
                $table->date('period_end')->nullable();
                $table->decimal('quota_amount', 14, 2)->default(0);
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->unique(['period_type', 'period_start']);
            });
        }

        if (Schema::hasTable('sales_board_settings') && !Schema::hasColumn('sales_board_settings', 'show_team_total')) {
            Schema::table('sales_board_settings', function (Blueprint $table) {
                $table->boolean('show_team_total')->default(true);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_team_quotas');
        if (Schema::hasTable('sales_board_settings') && Schema::hasColumn('sales_board_settings', 'show_team_total')) {
            Schema::table('sales_board_settings', function (Blueprint $table) {
                $table->dropColumn('show_team_total');
            });
        }
    }
};
