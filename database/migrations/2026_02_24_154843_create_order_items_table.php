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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('design_id')->nullable()->constrained()->onDelete('set null');
            $table->string('product_name'); // Snapshot at time of order
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->integer('print_colors')->default(1); // Number of colors in print
            $table->decimal('printing_cost', 10, 2)->default(0);
            $table->text('design_notes')->nullable();
            $table->string('design_file_path')->nullable(); // Copy of design file for this order
            $table->enum('status', ['pending', 'design_approved', 'in_production', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
            
            $table->index(['order_id', 'product_id']);
            $table->index(['design_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
