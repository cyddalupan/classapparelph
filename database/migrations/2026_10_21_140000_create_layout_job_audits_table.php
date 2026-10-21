<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail para sa Layout Jobs + Payouts (Andrew 2026-10-21).
 *
 * Bawat galaw (retag/undo, amount change, payment verify/reject, cancel,
 * payout request/pay/confirm) ay naka-log dito — sino, kailan, at ano ang
 * luma → bago. Ito ang magiging source of truth ng board members.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('layout_job_audits')) {
            return;
        }

        Schema::create('layout_job_audits', function (Blueprint $table) {
            $table->id();
            // 'job' = layout_jobs, 'payout' = layout_job_payouts
            $table->string('subject_type', 20)->default('job');
            $table->unsignedBigInteger('subject_id');
            $table->string('job_no', 30)->nullable();   // snapshot: kahit ma-delete ang subject
            $table->string('event', 40);                 // created|updated|deleted|paid|verified|rejected|confirmed
            $table->json('changes')->nullable();         // [{field,label,old,new}]
            $table->json('meta')->nullable();            // extra context (reason, payout id, etc.)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 120)->nullable(); // snapshot ng pangalan
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['subject_type', 'subject_id'], 'lja_subject_idx');
            $table->index('job_no', 'lja_job_no_idx');
            $table->index('created_at', 'lja_created_idx');
            $table->index('user_id', 'lja_user_idx');
            $table->index('event', 'lja_event_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layout_job_audits');
    }
};
