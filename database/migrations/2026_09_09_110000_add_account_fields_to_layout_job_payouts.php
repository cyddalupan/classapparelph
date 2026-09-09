<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Partial payout request: account details ng layout-doer (kung saan padadalhan)
        Schema::table('layout_job_payouts', function (Blueprint $table) {
            $table->string('account_name')->nullable()->after('request_notes');
            $table->string('account_number')->nullable()->after('account_name');
            $table->string('account_proof_path')->nullable()->after('account_number');
        });
    }

    public function down(): void
    {
        Schema::table('layout_job_payouts', function (Blueprint $table) {
            $table->dropColumn(['account_name', 'account_number', 'account_proof_path']);
        });
    }
};
