<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extra display toggles para sa pimp-up Team Board (Andrew 2026-10-15):
 * podium (top 3), badges, champion banner, confetti. ADDITIVE lang.
 */
return new class extends Migration
{
    public function up(): void
    {
        $add = function (string $col) {
            if (Schema::hasTable('sales_board_settings') && !Schema::hasColumn('sales_board_settings', $col)) {
                Schema::table('sales_board_settings', function (Blueprint $table) use ($col) {
                    $table->boolean($col)->default(true);
                });
            }
        };
        foreach (['show_podium', 'show_badges', 'show_champion', 'show_confetti'] as $col) {
            $add($col);
        }
    }

    public function down(): void
    {
        foreach (['show_podium', 'show_badges', 'show_champion', 'show_confetti'] as $col) {
            if (Schema::hasTable('sales_board_settings') && Schema::hasColumn('sales_board_settings', $col)) {
                Schema::table('sales_board_settings', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }
};
