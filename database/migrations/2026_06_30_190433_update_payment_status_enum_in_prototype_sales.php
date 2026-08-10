<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add new granular payment statuses to prototype_sales.
     */
    public function up(): void
    {
        // MySQL doesn't allow adding values to ENUM with regular Schema builder
        // We use raw statement for the ENUM modification
        DB::statement("ALTER TABLE prototype_sales MODIFY COLUMN payment_status ENUM(
            'pending',
            'verified',
            'rejected',
            'down_payment_verified',
            'additional_payment_verified',
            'full_payment_verified'
        ) DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE prototype_sales MODIFY COLUMN payment_status ENUM(
            'pending',
            'verified',
            'rejected'
        ) DEFAULT 'pending'");
    }
};
