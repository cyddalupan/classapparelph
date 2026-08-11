<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add archived_at column — when a completed project is archived from the
     * kanban board, the timestamp is stored here. Archived projects no longer
     * appear on the kanban board and are viewable on the archive page.
     */
    public function up(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('actual_completion_date');
        });
    }

    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
    }
};
