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
        Schema::create('volume_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_item_id')->constrained('master_items')->onDelete('cascade');
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->nullable()->comment('NULL means unlimited/unbounded');
            $table->decimal('price_per_unit', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Ensure no overlapping quantity ranges for the same item
            $table->unique(['master_item_id', 'min_quantity'], 'unique_min_per_item');
            $table->index(['master_item_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volume_discounts');
    }
};