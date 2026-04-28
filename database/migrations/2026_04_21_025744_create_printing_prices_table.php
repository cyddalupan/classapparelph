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
        Schema::create('printing_prices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50); // Logo, Half A4, A4, A3, A2
            $table->decimal('price', 10, 2); // 45.00, 60.00, etc.
            $table->integer('order')->default(0); // Display order
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        
        Schema::create('printing_combo_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size1_id')->constrained('printing_prices');
            $table->foreignId('size2_id')->constrained('printing_prices');
            $table->enum('discount_type', ['fixed', 'percent'])->default('fixed');
            $table->decimal('discount_value', 10, 2); // 30.00 or 10.00
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            // Ensure unique combo
            $table->unique(['size1_id', 'size2_id']);
        });
        
        Schema::create('printing_size_upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_size_id')->constrained('printing_prices');
            $table->integer('from_quantity')->default(2); // 2, 3, etc.
            $table->foreignId('to_size_id')->constrained('printing_prices');
            $table->boolean('auto_apply')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        
        Schema::create('printing_bulk_discounts', function (Blueprint $table) {
            $table->id();
            $table->integer('min_garments'); // 10
            $table->integer('max_garments'); // 24
            $table->decimal('discount_percent', 5, 2); // 5.00, 10.00, etc.
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            // Ensure no overlapping ranges
            $table->unique(['min_garments', 'max_garments']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printing_bulk_discounts');
        Schema::dropIfExists('printing_size_upgrades');
        Schema::dropIfExists('printing_combo_discounts');
        Schema::dropIfExists('printing_prices');
    }
};