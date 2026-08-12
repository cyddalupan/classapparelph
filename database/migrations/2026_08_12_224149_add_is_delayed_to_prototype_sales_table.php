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
            $table->boolean('is_delayed')->default(false)->after('rescheduled_date');
            $table->timestamp('delayed_at')->nullable()->after('is_delayed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dropColumn(['is_delayed', 'delayed_at']);
        });
    }
};
