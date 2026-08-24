<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add acknowledgement note column so agents/artists can leave a note
     * when resolving production feedback.
     */
    public function up(): void
    {
        Schema::table('production_feedbacks', function (Blueprint $table) {
            $table->text('acknowledgement')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('production_feedbacks', function (Blueprint $table) {
            $table->dropColumn('acknowledgement');
        });
    }
};
