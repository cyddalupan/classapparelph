<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, we need to modify the enum constraint
        // Since MySQL doesn't support modifying ENUM directly, we'll use DB::statement
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'staff', 'customer', 'sales_agent', 'sales_representative') DEFAULT 'customer'");
        
        // Add additional fields for sales agents
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->after('role');
            $table->string('department')->nullable()->after('employee_id');
            $table->decimal('sales_target', 15, 2)->nullable()->after('department');
            $table->decimal('commission_rate', 5, 2)->nullable()->after('sales_target');
            $table->date('hire_date')->nullable()->after('commission_rate');
            $table->string('supervisor')->nullable()->after('hire_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'staff', 'customer') DEFAULT 'customer'");
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['employee_id', 'department', 'sales_target', 'commission_rate', 'hire_date', 'supervisor']);
        });
    }
};
