<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->string('status')->default('draft'); // draft, for_approval, for_procurement, ordered, for_delivery, partial, completed, cancelled
            $table->text('notes')->nullable();
            $table->text('procurement_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('expected_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->unsignedBigInteger('procurement_user_id')->nullable();
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('sales_departments')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('procurement_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_orders');
    }
};
