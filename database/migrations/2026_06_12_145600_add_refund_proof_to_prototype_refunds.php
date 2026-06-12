<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prototype_refunds', function (Blueprint $table) {
            $table->string('refund_reference', 255)->nullable()->after('refund_account_number');
            $table->string('refund_proof_path', 255)->nullable()->after('refund_reference');
        });
    }

    public function down(): void
    {
        Schema::table('prototype_refunds', function (Blueprint $table) {
            $table->dropColumn(['refund_reference', 'refund_proof_path']);
        });
    }
};
