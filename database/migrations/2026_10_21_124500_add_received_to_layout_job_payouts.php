<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Layout Payout — GA receipt confirmation (Andrew 2026-10-21).
 *
 * Kapag na-mark na 'paid' ng approver (may proof), pwedeng i-confirm ng
 * layout doer na pumasok/natanggap na niya ang pera → status 'verified'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layout_job_payouts', function (Blueprint $table) {
            if (!Schema::hasColumn('layout_job_payouts', 'received_by')) {
                $table->unsignedBigInteger('received_by')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('layout_job_payouts', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('received_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('layout_job_payouts', function (Blueprint $table) {
            foreach (['received_at', 'received_by'] as $col) {
                if (Schema::hasColumn('layout_job_payouts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
