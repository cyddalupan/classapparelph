<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add rescheduled_date column — when a project is delayed and moved on the
     * calendar, the new date is stored here WITHOUT touching the original
     * estimated_completion_date (which stays as-is for history).
     */
    public function up(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->date('rescheduled_date')->nullable()->after('estimated_completion_date');
        });
    }

    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dropColumn('rescheduled_date');
        });
    }
};
