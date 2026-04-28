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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            
            // Customer Information (6 fields from Andrew)
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('marketplace')->nullable(); // Facebook, Instagram, Walk-in, Referral, etc.
            $table->string('email')->nullable();
            $table->text('location')->nullable(); // Address/City
            $table->string('company')->nullable();
            
            // LTV Tracking Fields
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->decimal('average_order_value', 10, 2)->default(0);
            $table->date('first_order_date')->nullable();
            $table->date('last_order_date')->nullable();
            $table->string('customer_tier')->default('bronze'); // bronze, silver, gold, platinum
            
            // Additional useful fields
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('created_by')->nullable(); // User who created this customer
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
