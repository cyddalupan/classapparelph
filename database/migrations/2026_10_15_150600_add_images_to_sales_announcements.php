<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pictures para sa announcements (Andrew 2026-10-15).
 * JSON array ng mga storage-relative path (hal. ["announcements/abc.jpg"]).
 * ADDITIVE lang — nullable, hindi nakakaapekto sa existing rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_announcements') && !Schema::hasColumn('sales_announcements', 'images')) {
            Schema::table('sales_announcements', function (Blueprint $table) {
                $table->json('images')->nullable()->after('body');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_announcements') && Schema::hasColumn('sales_announcements', 'images')) {
            Schema::table('sales_announcements', function (Blueprint $table) {
                $table->dropColumn('images');
            });
        }
    }
};
