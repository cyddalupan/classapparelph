<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Layout Job — "Date ng Payment" (bayad na layout).
 *
 * Additive lang: bagong nullable na column. Hindi ginagalaw ang existing
 * fields/logic. Ang date na ito ay ang petsa kung kailan nagbayad si client,
 * hindi ang verification date (nasa `payment_verified_at` iyon).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layout_jobs', function (Blueprint $table) {
            $table->date('payment_date')->nullable()->after('payment_reference');
        });
    }

    public function down(): void
    {
        Schema::table('layout_jobs', function (Blueprint $table) {
            $table->dropColumn('payment_date');
        });
    }
};
