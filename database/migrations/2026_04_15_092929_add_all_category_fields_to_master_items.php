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
        Schema::table('master_items', function (Blueprint $table) {
            // Shirt Products
            $table->string('brand')->nullable();
            $table->string('shirt_type')->nullable();
            $table->string('color')->nullable();
            $table->string('material')->nullable();
            $table->string('size')->nullable();
            
            // Other Products
            $table->string('other_product_type')->nullable();
            $table->string('other_material')->nullable();
            $table->string('other_color')->nullable();
            
            // Machine & Equipment
            $table->string('machine_type')->nullable();
            $table->text('specifications')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('warranty_period')->nullable();
            $table->string('power_requirement')->nullable();
            
            // Garment Materials
            $table->string('fabric_type')->nullable();
            $table->string('weight')->nullable();
            $table->string('width')->nullable();
            $table->string('color_fastness')->nullable();
            $table->string('material_type')->nullable();
            $table->string('material_name')->nullable();
            $table->string('material_sku')->nullable();
            $table->string('material_brand')->nullable();
            $table->string('material_color')->nullable();
            
            // Printing & Office Supplies
            $table->string('paper_type')->nullable();
            $table->string('paper_size')->nullable();
            $table->string('ink_type')->nullable();
            $table->integer('yield')->nullable();
            $table->string('printing_brand')->nullable();
            $table->string('printing_product_type')->nullable();
            $table->text('printing_description')->nullable();
            $table->decimal('printing_price', 10, 2)->nullable();
            $table->integer('printing_quantity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_items', function (Blueprint $table) {
            // Shirt Products
            $table->dropColumn(['brand', 'shirt_type', 'color', 'material', 'size']);
            
            // Other Products
            $table->dropColumn(['other_product_type', 'other_material', 'other_color']);
            
            // Machine & Equipment
            $table->dropColumn(['machine_type', 'specifications', 'model', 'serial_number', 'warranty_period', 'power_requirement']);
            
            // Garment Materials
            $table->dropColumn(['fabric_type', 'weight', 'width', 'color_fastness', 'material_type', 'material_name', 'material_sku', 'material_brand', 'material_color']);
            
            // Printing & Office Supplies
            $table->dropColumn(['paper_type', 'paper_size', 'ink_type', 'yield', 'printing_brand', 'printing_product_type', 'printing_description', 'printing_price', 'printing_quantity']);
        });
    }
};
