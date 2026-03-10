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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['t-shirt', 'hoodie', 'cap', 'jacket', 'polo', 'other']);
            $table->json('available_colors')->nullable(); // JSON array of color codes
            $table->json('available_sizes')->nullable(); // JSON array of sizes
            $table->decimal('base_price', 10, 2);
            $table->decimal('printing_cost_per_color', 10, 2)->default(0);
            $table->string('material')->nullable();
            $table->string('brand')->nullable();
            $table->string('sku')->unique()->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('reorder_level')->default(10);
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('specifications')->nullable(); // JSON for additional specs
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
