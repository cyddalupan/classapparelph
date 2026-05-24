<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_tracked_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('master_item_id')->nullable();
            $table->string('item_name');
            $table->string('sku')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('department_name')->nullable();
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('prototype_sales')->cascadeOnDelete();
            $table->foreign('master_item_id')->references('id')->on('master_items')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('sales_departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_tracked_items');
    }
};
