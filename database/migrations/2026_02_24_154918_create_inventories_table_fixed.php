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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique()->nullable();
            $table->string('type'); // Changed from enum to string
            $table->string('category'); // fabric, ink, thread, packaging, etc.
            $table->text('description')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->string('unit_of_measure'); // pieces, meters, liters, kg, etc.
            $table->decimal('current_stock', 12, 3)->default(0);
            $table->decimal('minimum_stock', 12, 3)->default(0);
            $table->decimal('reorder_quantity', 12, 3)->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->string('supplier_sku')->nullable();
            $table->json('specifications')->nullable();
            $table->string('storage_location')->nullable();
            $table->date('last_restocked_at')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['type', 'category']);
            $table->index(['sku']);
            $table->index(['supplier_id']);
            $table->index(['current_stock', 'minimum_stock']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};