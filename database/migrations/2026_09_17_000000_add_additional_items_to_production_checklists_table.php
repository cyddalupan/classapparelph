<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_checklists', function (Blueprint $table) {
            if (!Schema::hasColumn('production_checklists', 'additional_items')) {
                $table->json('additional_items')->nullable()->after('items');
            }
        });
    }

    public function down(): void
    {
        Schema::table('production_checklists', function (Blueprint $table) {
            if (Schema::hasColumn('production_checklists', 'additional_items')) {
                $table->dropColumn('additional_items');
            }
        });
    }
};
