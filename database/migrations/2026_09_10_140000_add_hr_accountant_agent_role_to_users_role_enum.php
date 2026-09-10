<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'hr_accountant_agent' role — HR/Accountant/Agent (combined).
     *
     * Behaves like a Sales Agent (own customers, sales, layout jobs, calendar)
     * AND like the Accountant (payment review queue + payment verification/cash flow).
     * HR is title-only for now (no HR features yet).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','staff','customer','sales_agent','sales_representative','procurement','coo','cpo','cmo','prod_manager','ga','qa','accountant','hr_accountant_agent') NOT NULL DEFAULT 'customer'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','staff','customer','sales_agent','sales_representative','procurement','coo','cpo','cmo','prod_manager','ga','qa','accountant') NOT NULL DEFAULT 'customer'");
    }
};
