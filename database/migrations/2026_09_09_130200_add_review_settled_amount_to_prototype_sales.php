<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Accumulated amount settled via accepted payment review requests.
     * balance_due_computed accessor subtracts this so an accepted close-out
     * request zeroes the balance → unlocks DONE. Only ever increased on
     * Accountant ACCEPT (never decreased), so existing raw balance_due /
     * recalcSalePaymentTotals logic stays untouched.
     */
    public function up(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->decimal('review_settled_amount', 12, 2)->default(0)->after('overpayment');
        });
    }

    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dropColumn('review_settled_amount');
        });
    }
};
