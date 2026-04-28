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
        Schema::create('product_pricings', function (Blueprint $table) {
            $table->id();
            
            // Link to master item
            $table->foreignId('master_item_id')
                  ->constrained('master_items')
                  ->onDelete('cascade');
            
            // Price tier: supplier_cost, sales_team, agent_cost
            $table->enum('price_tier', ['supplier_cost', 'sales_team', 'agent_cost'])
                  ->default('supplier_cost');
            
            // Pricing details
            $table->decimal('base_price', 10, 2)->nullable()->comment('Original cost from master item');
            $table->decimal('markup_percentage', 5, 2)->nullable()->comment('Markup % for agents');
            $table->decimal('markup_amount', 10, 2)->nullable()->comment('Calculated markup amount');
            $table->decimal('final_price', 10, 2)->nullable()->comment('base_price + markup_amount');
            
            // User who created/updated
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['master_item_id', 'price_tier', 'is_active']);
            $table->index(['price_tier', 'is_active']);
            $table->index('final_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_pricings');
    }
};
