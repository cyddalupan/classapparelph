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
            $table->string('shirt_type')->nullable()->after('brand');
            
            // Other Products
            $table->string('other_product_type')->nullable()->after('shirt_type');
            $table->string('other_material')->nullable()->after('other_product_type');
            $table->string('other_color')->nullable()->after('other_material');
            
            // Machine & Equipment
            $table->string('machine_type')->nullable()->after('power_requirement');
            $table->text('specifications')->nullable()->after('machine_type');
            
            // Garment Materials
            $table->string('material_type')->nullable()->after('color_fastness');
            $table->string('material_name')->nullable()->after('material_type');
            $table->string('material_sku')->nullable()->after('material_name');
            $table->string('material_brand')->nullable()->after('material_sku');
            $table->string('material_color')->nullable()->after('material_brand');
            
            // Printing & Office Supplies
            $table->string('printing_brand')->nullable()->after('yield');
            $table->string('printing_product_type')->nullable()->after('printing_brand');
            $table->text('printing_description')->nullable()->after('printing_product_type');
            $table->decimal('printing_price', 10, 2)->nullable()->after('printing_description');
            $table->integer('printing_quantity')->nullable()->after('printing_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_items', function (Blueprint $table) {
            // Shirt Products
            $table->dropColumn('shirt_type');
            
            // Other Products
            $table->dropColumn('other_product_type');
            $table->dropColumn('other_material');
            $table->dropColumn('other_color');
            
            // Machine & Equipment
            $table->dropColumn('machine_type');
            $table->dropColumn('specifications');
            
            // Garment Materials
            $table->dropColumn('material_type');
            $table->dropColumn('material_name');
            $table->dropColumn('material_sku');
            $table->dropColumn('material_brand');
            $table->dropColumn('material_color');
            
            // Printing & Office Supplies
            $table->dropColumn('printing_brand');
            $table->dropColumn('printing_product_type');
            $table->dropColumn('printing_description');
            $table->dropColumn('printing_price');
            $table->dropColumn('printing_quantity');
        });
    }
};
