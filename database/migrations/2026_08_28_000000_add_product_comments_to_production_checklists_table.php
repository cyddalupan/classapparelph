<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_checklists', function (Blueprint $table) {
            $table->text('product_comments')->nullable()->after('additional_comments');
        });
    }

    public function down(): void
    {
        Schema::table('production_checklists', function (Blueprint $table) {
            $table->dropColumn('product_comments');
        });
    }
};
