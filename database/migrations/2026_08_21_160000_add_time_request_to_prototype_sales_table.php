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
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->timestamp('time_requested_at')->nullable()->after('delay_feedback_updated_at');
            $table->unsignedBigInteger('time_requested_by')->nullable()->after('time_requested_at');
            $table->timestamp('needed_by')->nullable()->after('time_requested_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dropColumn(['time_requested_at', 'time_requested_by', 'needed_by']);
        });
    }
};
