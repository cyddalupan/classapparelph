<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_item_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('action_type'); // add_stock, deduct_stock, item_created, item_deleted, item_updated
            $table->string('item_name');
            $table->string('sku')->nullable();
            $table->string('category')->nullable();
            $table->decimal('old_value', 12, 3)->nullable();
            $table->decimal('new_value', 12, 3)->nullable();
            $table->decimal('quantity', 12, 3)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('master_item_id')->references('id')->on('master_items')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('sales_departments')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_activity_logs');
    }
};
