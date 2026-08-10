<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sale_notifications', function (Blueprint $table) {
            $table->boolean('is_urgent')->default(false)->after('type');
            $table->unsignedInteger('reminder_count')->default(1)->after('is_urgent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_notifications', function (Blueprint $table) {
            $table->dropColumn(['is_urgent', 'reminder_count']);
        });
    }
};
