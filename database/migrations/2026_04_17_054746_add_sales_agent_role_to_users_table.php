<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the role enum to include sales_agent
        \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','staff','customer','sales_agent','sales_representative') NOT NULL DEFAULT 'customer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum
        \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','staff','customer') NOT NULL DEFAULT 'customer'");
    }
};
