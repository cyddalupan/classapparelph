<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_audit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_id')->nullable()->after('prototype_sale_id');
        });
    }

    public function down(): void
    {
        Schema::table('payment_audit_logs', function (Blueprint $table) {
            $table->dropColumn('payment_id');
        });
    }
};
