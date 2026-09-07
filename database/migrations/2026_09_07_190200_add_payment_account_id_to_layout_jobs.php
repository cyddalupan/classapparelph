<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layout_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_account_id')->nullable()->after('payment_method');
            $table->index('payment_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('layout_jobs', function (Blueprint $table) {
            $table->dropIndex(['payment_account_id']);
            $table->dropColumn('payment_account_id');
        });
    }
};
