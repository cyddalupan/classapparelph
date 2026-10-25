<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Travel time (WH → CLASS) para sa ETA/explanation sa Progress modal.
 * Default 120 minutes (2 hours) — Andrew 2026-10-22.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wh_class_schedule', function (Blueprint $table) {
            $table->unsignedInteger('travel_minutes')->default(120)->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('wh_class_schedule', function (Blueprint $table) {
            $table->dropColumn('travel_minutes');
        });
    }
};
