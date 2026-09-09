<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sale split across multiple calendar dates (Class big projects).
     * When split_start_date & split_end_date are set, the sale's quantity is
     * divided evenly across every date in that inclusive range for the
     * Class calendar display + day-load math. Original estimated_completion_date
     * / rescheduled_date stay untouched (sales page + kanban unaffected).
     */
    public function up(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->date('split_start_date')->nullable()->after('rescheduled_date');
            $table->date('split_end_date')->nullable()->after('split_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dropColumn(['split_start_date', 'split_end_date']);
        });
    }
};
