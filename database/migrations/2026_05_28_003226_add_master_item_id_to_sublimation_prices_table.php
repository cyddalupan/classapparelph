<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sublimation_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('master_item_id')->nullable()->after('id');
            $table->foreign('master_item_id')->references('id')->on('master_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sublimation_prices', function (Blueprint $table) {
            $table->dropForeign(['master_item_id']);
            $table->dropColumn('master_item_id');
        });
    }
};
