<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replacement / Reprocess process (Andrew 2026-10-26).
     *
     * Flow: USER creates a NORMAL sale, then links it (from the sale page,
     * below Subcon) to a SALES NUMBER + DAMAGE REPORT NO. Andrew approves.
     * On approval the sale is flagged as a replacement and its money columns
     * are zeroed so it never adds to any sales/production totals, while the
     * order still flows through production with a REPLACEMENT badge.
     */
    public function up(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('prototype_sales', 'is_replacement')) {
                $table->boolean('is_replacement')->default(false)->after('kanban_status');
                $table->index('is_replacement');
            }
            if (!Schema::hasColumn('prototype_sales', 'replacement_status')) {
                $table->string('replacement_status', 20)->default('none')->after('is_replacement');
                $table->index('replacement_status');
            }
            if (!Schema::hasColumn('prototype_sales', 'replacement_sales_number')) {
                $table->string('replacement_sales_number')->nullable()->after('replacement_status');
            }
            if (!Schema::hasColumn('prototype_sales', 'replacement_sale_id')) {
                $table->unsignedBigInteger('replacement_sale_id')->nullable()->after('replacement_sales_number');
            }
            if (!Schema::hasColumn('prototype_sales', 'replacement_damage_report_id')) {
                $table->unsignedBigInteger('replacement_damage_report_id')->nullable()->after('replacement_sale_id');
            }
            if (!Schema::hasColumn('prototype_sales', 'replacement_damage_report_no')) {
                $table->string('replacement_damage_report_no')->nullable()->after('replacement_damage_report_id');
            }
            if (!Schema::hasColumn('prototype_sales', 'replacement_requested_by')) {
                $table->unsignedBigInteger('replacement_requested_by')->nullable()->after('replacement_damage_report_no');
            }
            if (!Schema::hasColumn('prototype_sales', 'replacement_requested_at')) {
                $table->timestamp('replacement_requested_at')->nullable()->after('replacement_requested_by');
            }
            if (!Schema::hasColumn('prototype_sales', 'replacement_approved_by')) {
                $table->unsignedBigInteger('replacement_approved_by')->nullable()->after('replacement_requested_at');
            }
            if (!Schema::hasColumn('prototype_sales', 'replacement_approved_at')) {
                $table->timestamp('replacement_approved_at')->nullable()->after('replacement_approved_by');
            }
            if (!Schema::hasColumn('prototype_sales', 'replacement_rejected_at')) {
                $table->timestamp('replacement_rejected_at')->nullable()->after('replacement_approved_at');
            }
            if (!Schema::hasColumn('prototype_sales', 'replacement_reject_reason')) {
                $table->text('replacement_reject_reason')->nullable()->after('replacement_rejected_at');
            }
            if (!Schema::hasColumn('prototype_sales', 'replacement_amount_snapshot')) {
                $table->json('replacement_amount_snapshot')->nullable()->after('replacement_reject_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            foreach ([
                'replacement_amount_snapshot',
                'replacement_reject_reason',
                'replacement_rejected_at',
                'replacement_approved_at',
                'replacement_approved_by',
                'replacement_requested_at',
                'replacement_requested_by',
                'replacement_damage_report_no',
                'replacement_damage_report_id',
                'replacement_sale_id',
                'replacement_sales_number',
                'replacement_status',
                'is_replacement',
            ] as $col) {
                if (Schema::hasColumn('prototype_sales', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
