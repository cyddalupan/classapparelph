<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Set Time List — "Done" place (Andrew 2026-09-24).
 *
 * Kapag ni-Done ng Manager ang isang order DITO sa Set Time List, wala na ito
 * sa active list pero nandoon lang sa "Done" tab ng Set Time List para mabawasan
 * ang listahan. Pwedeng i-restore kapag nagkamali.
 *
 * IMPORTANT: HINDI nito ginagalaw ang Manager Order List / production status —
 * hiwalay na flag lang ito para sa Set Time List.
 *
 * - set_time_done_at  : timestamp kung kailan ni-Done sa Set Time List (NULL = active)
 * - set_time_done_by  : user id ng nag-Done (para may record)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('prototype_sales', 'set_time_done_at')) {
                $table->timestamp('set_time_done_at')->nullable()->after('set_time_prio_at');
            }
            if (!Schema::hasColumn('prototype_sales', 'set_time_done_by')) {
                $table->unsignedBigInteger('set_time_done_by')->nullable()->after('set_time_done_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            if (Schema::hasColumn('prototype_sales', 'set_time_done_by')) {
                $table->dropColumn('set_time_done_by');
            }
            if (Schema::hasColumn('prototype_sales', 'set_time_done_at')) {
                $table->dropColumn('set_time_done_at');
            }
        });
    }
};
