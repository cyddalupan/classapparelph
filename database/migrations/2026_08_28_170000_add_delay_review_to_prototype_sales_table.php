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
            $table->string('delay_review_status')->nullable()->after('delay_feedback_updated_at');
            $table->text('delay_review_notes')->nullable()->after('delay_review_status');
            $table->unsignedBigInteger('delay_reviewed_by')->nullable()->after('delay_review_notes');
            $table->timestamp('delay_reviewed_at')->nullable()->after('delay_reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dropColumn(['delay_review_status', 'delay_review_notes', 'delay_reviewed_by', 'delay_reviewed_at']);
        });
    }
};
