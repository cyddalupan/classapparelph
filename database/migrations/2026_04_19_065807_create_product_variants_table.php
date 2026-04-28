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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            
            // Link to parent product
            $table->foreignId('master_item_id')
                ->constrained('master_items')
                ->onDelete('cascade');
            
            // Variant attributes
            $table->string('size')->nullable();          // S, M, L, XL
            $table->string('brand')->nullable();         // Yalex, Gildan, etc.
            $table->string('color')->nullable();         // Red, Blue, Black
            $table->string('material')->nullable();      // Cotton, Polyester
            
            // Unique identifiers
            $table->string('sku')->unique()->nullable(); // YAL-RND-S-RED
            $table->string('barcode')->nullable();
            
            // Pricing and inventory
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Soft deletes
            $table->softDeletes();
            
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['master_item_id', 'is_active']);
            $table->index(['size', 'brand', 'color']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
