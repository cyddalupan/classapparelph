<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','staff','customer','sales_agent','sales_representative','procurement','coo') NOT NULL DEFAULT 'customer'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','staff','customer','sales_agent','sales_representative','procurement') NOT NULL DEFAULT 'customer'");
    }
};
