<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('prototype_sales', 'payment_account_id')) {
                $table->foreignId('payment_account_id')->nullable()->after('payment_owner')->constrained('payment_accounts')->nullOnDelete();
            }
            if (!Schema::hasColumn('prototype_sales', 'payment_date')) {
                $table->date('payment_date')->nullable()->after('payment_account_id');
            }
            if (!Schema::hasColumn('prototype_sales', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('payment_date');
            }
            if (!Schema::hasColumn('prototype_sales', 'verify_requested_at')) {
                $table->timestamp('verify_requested_at')->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('prototype_sales', 'verify_requested_by')) {
                $table->foreignId('verify_requested_by')->nullable()->after('verify_requested_at')->constrained('users')->nullOnDelete();
            }
        });

        // Migrate existing payment_owner values to payment_account_id
        // "company" → Company Cash (id: 4)
        \Illuminate\Support\Facades\DB::table('prototype_sales')
            ->where('payment_owner', 'company')
            ->whereNull('payment_account_id')
            ->update(['payment_account_id' => 4]);
    }

    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dropForeign(['payment_account_id']);
            $table->dropForeign(['verify_requested_by']);
            $table->dropColumn([
                'payment_account_id',
                'payment_date',
                'reference_number',
                'verify_requested_at',
                'verify_requested_by'
            ]);
        });
    }
};
