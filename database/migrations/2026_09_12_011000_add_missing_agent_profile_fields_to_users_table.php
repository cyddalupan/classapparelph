<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The 2026_03_02_161540 migration was supposed to add these sales-agent fields
 * to `users`, but on the live DB the columns are missing (that migration is
 * marked "Ran"), which made User Management search/create/update return 500:
 * "Unknown column 'employee_id'" etc.
 *
 * This migration (re)adds them, idempotently, so it is safe whether or not they
 * already exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'employee_id')) {
                $table->string('employee_id')->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('employee_id');
            }
            if (! Schema::hasColumn('users', 'sales_target')) {
                $table->decimal('sales_target', 15, 2)->nullable()->after('department');
            }
            if (! Schema::hasColumn('users', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)->nullable()->after('sales_target');
            }
            if (! Schema::hasColumn('users', 'hire_date')) {
                $table->date('hire_date')->nullable()->after('commission_rate');
            }
            if (! Schema::hasColumn('users', 'supervisor')) {
                $table->string('supervisor')->nullable()->after('hire_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('users', 'employee_id') ? 'employee_id' : null,
                Schema::hasColumn('users', 'department') ? 'department' : null,
                Schema::hasColumn('users', 'sales_target') ? 'sales_target' : null,
                Schema::hasColumn('users', 'commission_rate') ? 'commission_rate' : null,
                Schema::hasColumn('users', 'hire_date') ? 'hire_date' : null,
                Schema::hasColumn('users', 'supervisor') ? 'supervisor' : null,
            ]));
        });
    }
};
