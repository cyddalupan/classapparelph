<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit / double-check layer for approved freebie requests.
     * After a Manager/CEO/COO approves, a DIFFERENT approver must audit
     * (verify) the approval before it is considered fully cleared.
     * Additive only — existing columns/flows untouched.
     */
    public function up(): void
    {
        Schema::table('freebie_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('audited_by')->nullable()->after('approved_at');
            $table->timestamp('audited_at')->nullable()->after('audited_by');
        });
    }

    public function down(): void
    {
        Schema::table('freebie_requests', function (Blueprint $table) {
            $table->dropColumn(['audited_by', 'audited_at']);
        });
    }
};
