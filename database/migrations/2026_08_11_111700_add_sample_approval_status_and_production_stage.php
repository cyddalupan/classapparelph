<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend kanban_status enum with sample_approval (SAMPLE/APPROVAL column)
        DB::statement("ALTER TABLE prototype_sales MODIFY kanban_status ENUM('new','sample_approval','design','production','quality_check','ready_for_delivery','delivered','completed') NOT NULL DEFAULT 'new'");

        // Store the exact production stage tag (FOR SAMPLE, PRINTING, etc.)
        if (!Schema::hasColumn('prototype_sales', 'production_stage')) {
            Schema::table('prototype_sales', function (Blueprint $table) {
                $table->string('production_stage')->nullable()->after('kanban_status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dropColumn('production_stage');
        });
        DB::statement("ALTER TABLE prototype_sales MODIFY kanban_status ENUM('new','design','production','quality_check','ready_for_delivery','delivered','completed') NOT NULL DEFAULT 'new'");
    }
};
