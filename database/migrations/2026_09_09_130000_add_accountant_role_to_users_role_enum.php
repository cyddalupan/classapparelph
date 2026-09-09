<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'accountant' role — payment review/verification queue owner
     * (deduction/close-out requests from Sales Agents).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','staff','customer','sales_agent','sales_representative','procurement','coo','cpo','cmo','prod_manager','ga','qa','accountant') NOT NULL DEFAULT 'customer'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','staff','customer','sales_agent','sales_representative','procurement','coo','cpo','cmo','prod_manager','ga','qa') NOT NULL DEFAULT 'customer'");
    }
};
