<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master visibility gate para sa Team Board (Andrew 2026-10-15):
 * habang OFF (default), si ADMIN lang ang makakapasok sa Team Board.
 * Kapag binuksan ni admin (agents_can_view = 1), doon pa lang makikita ng agents.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_board_settings') && !Schema::hasColumn('sales_board_settings', 'agents_can_view')) {
            Schema::table('sales_board_settings', function (Blueprint $table) {
                $table->boolean('agents_can_view')->default(false)->after('tagline');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_board_settings') && Schema::hasColumn('sales_board_settings', 'agents_can_view')) {
            Schema::table('sales_board_settings', function (Blueprint $table) {
                $table->dropColumn('agents_can_view');
            });
        }
    }
};
