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
        Schema::table('prototype_sales', function (Blueprint $table) {
            // Add customer_id foreign key
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            
            // Remove duplicate customer fields (we'll keep them for now as backup)
            // We'll keep customer_name, customer_email, customer_phone for display purposes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};