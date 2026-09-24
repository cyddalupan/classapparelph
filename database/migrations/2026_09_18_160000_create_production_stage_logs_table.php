<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * production_stage_logs — timeline ng bawat pagbabago ng production_stage ng isang sale.
 *
 * Additive lang: bagong table, walang binabago sa existing tables.
 * Pinapagana nito ang Stage Timing para sa LAHAT ng stage (kasama ang SEWING at QA),
 * hindi lang sa GA stages (FOR SAMPLE / FOR FORMAT / PRINTING) na nasa ga_assignment_logs.
 *
 * (Andrew 2026-09-18)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('production_stage_logs')) {
            return;
        }

        Schema::create('production_stage_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prototype_sale_id');
            $table->string('from_stage')->nullable();
            $table->string('to_stage')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamps();

            $table->index('prototype_sale_id', 'psl_sale_idx');
            $table->index('to_stage', 'psl_to_stage_idx');
            $table->index('created_at', 'psl_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_stage_logs');
    }
};
