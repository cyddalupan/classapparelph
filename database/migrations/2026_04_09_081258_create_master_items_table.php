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
        Schema::create('master_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->nullable();
            
            // Category-specific fields (nullable for flexibility)
            // Shirt Products fields
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('material')->nullable();
            $table->string('brand')->nullable();
            
            // Machine & Equipment fields
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('warranty_period')->nullable();
            $table->string('power_requirement')->nullable();
            
            // Garment Materials fields
            $table->string('fabric_type')->nullable();
            $table->string('weight')->nullable();
            $table->string('width')->nullable();
            $table->string('color_fastness')->nullable();
            
            // Printing & Office Supplies fields
            $table->string('paper_type')->nullable();
            $table->string('paper_size')->nullable();
            $table->string('ink_type')->nullable();
            $table->integer('yield')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_items');
    }
};
