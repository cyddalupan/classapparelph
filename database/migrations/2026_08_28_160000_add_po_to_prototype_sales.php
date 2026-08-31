<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add PO (purchase order) support to prototype_sales:
     * - po_reference column so the P.O. number survives creation
     * - 'po' value in payment_status enum (PO = no downpayment, no verification needed)
     * - 'po' value in payment_method enum
     */
    public function up(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->string('po_reference')->nullable()->after('reference_number');
        });

        DB::statement("ALTER TABLE prototype_sales MODIFY COLUMN payment_status ENUM(
            'pending',
            'verified',
            'rejected',
            'reject_pending',
            'edit_pending',
            'down_payment_verified',
            'additional_payment_verified',
            'full_payment_verified',
            'po'
        ) DEFAULT 'pending'");

        DB::statement("ALTER TABLE prototype_sales MODIFY COLUMN payment_method ENUM(
            'cash',
            'bank_transfer',
            'gcash',
            'paymaya',
            'credit_card',
            'other',
            'po'
        ) DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE prototype_sales MODIFY COLUMN payment_status ENUM(
            'pending',
            'verified',
            'rejected',
            'reject_pending',
            'edit_pending',
            'down_payment_verified',
            'additional_payment_verified',
            'full_payment_verified'
        ) DEFAULT 'pending'");

        DB::statement("ALTER TABLE prototype_sales MODIFY COLUMN payment_method ENUM(
            'cash',
            'bank_transfer',
            'gcash',
            'paymaya',
            'credit_card',
            'other'
        ) DEFAULT NULL");

        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dropColumn('po_reference');
        });
    }
};
