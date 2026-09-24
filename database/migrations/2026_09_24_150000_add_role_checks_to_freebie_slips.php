<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Freebie Slip — per-role Done checkboxes (GA / QA / Manager).
 *
 * Andrew (2026-09-24): sa Production Slip modal, bagong "Freebie Slip" tab.
 * Ang Done ay hindi button kundi CHECKBOX — tatlo: GA, QA, Manager.
 * Bawat role nagtitick ng sarili niya (may who/when). Ang slip ay "done"
 * (green / wala na sa backjob) kapag lahat ng tatlo ay nakatick.
 *
 * Additive lang — hindi ginalaw ang existing `status` / `done_by` / `done_at`
 * (ginagamit pa rin bilang aggregate kapag kumpleto na ang tatlo).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freebie_slips', function (Blueprint $table) {
            // GA checkbox
            $table->unsignedBigInteger('ga_done_by')->nullable()->after('done_at');
            $table->timestamp('ga_done_at')->nullable()->after('ga_done_by');
            // QA checkbox
            $table->unsignedBigInteger('qa_done_by')->nullable()->after('ga_done_at');
            $table->timestamp('qa_done_at')->nullable()->after('qa_done_by');
            // Manager checkbox
            $table->unsignedBigInteger('mgr_done_by')->nullable()->after('qa_done_at');
            $table->timestamp('mgr_done_at')->nullable()->after('mgr_done_by');
        });
    }

    public function down(): void
    {
        Schema::table('freebie_slips', function (Blueprint $table) {
            $table->dropColumn(['ga_done_by', 'ga_done_at', 'qa_done_by', 'qa_done_at', 'mgr_done_by', 'mgr_done_at']);
        });
    }
};
