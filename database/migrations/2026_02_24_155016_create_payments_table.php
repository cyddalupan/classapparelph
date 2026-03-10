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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('payment_number')->unique();
            $table->enum('method', ['cash', 'credit_card', 'gcash', 'paymaya', 'bank_transfer', 'paypal', 'check']);
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('PHP');
            $table->string('transaction_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('payment_details')->nullable(); // JSON for gateway response, check details, etc.
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['payment_number']);
            $table->index(['invoice_id']);
            $table->index(['order_id']);
            $table->index(['method', 'status']);
            $table->index(['transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
