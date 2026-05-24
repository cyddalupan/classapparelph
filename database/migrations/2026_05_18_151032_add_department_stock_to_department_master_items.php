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
        Schema::table('department_master_items', function (Blueprint $table) {
            $table->integer('current_stock')->default(0)->after('master_item_id');
            $table->integer('minimum_stock')->default(5)->after('current_stock');
            $table->timestamp('last_restocked_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('department_master_items', function (Blueprint $table) {
            $table->dropColumn(['current_stock', 'minimum_stock', 'last_restocked_at']);
        });
    }
};
