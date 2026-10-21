<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Global controls para sa Team Board (Andrew 2026-10-15) — isang row lang (id=1).
 * Pinipili ni admin kung ANO ang makikita ng mga agent: team quotas, leaderboard,
 * announcements, exact amounts, rank. Purely ADDITIVE.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_board_settings')) {
            Schema::create('sales_board_settings', function (Blueprint $table) {
                $table->id();
                $table->string('board_title')->default('Team Board');
                $table->string('tagline')->nullable();
                $table->boolean('show_team_quotas')->default(true);
                $table->boolean('show_leaderboard')->default(true);
                $table->boolean('show_announcements')->default(true);
                $table->boolean('show_amounts')->default(true);
                $table->boolean('show_own_amount')->default(true);
                $table->boolean('show_rank')->default(true);
                $table->boolean('show_percent')->default(true);
                $table->boolean('show_own_card')->default(true);
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_board_settings');
    }
};
