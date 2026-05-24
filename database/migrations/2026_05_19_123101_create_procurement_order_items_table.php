<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procurement_order_id');
            $table->unsignedBigInteger('master_item_id')->nullable();
            $table->string('item_name');
            $table->string('sku')->nullable();
            $table->integer('quantity_ordered')->default(1);
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->string('status')->default('pending'); // pending, ordered, partial, received, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('procurement_order_id')->references('id')->on('procurement_orders')->cascadeOnDelete();
            $table->foreign('master_item_id')->references('id')->on('master_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_order_items');
    }
};
