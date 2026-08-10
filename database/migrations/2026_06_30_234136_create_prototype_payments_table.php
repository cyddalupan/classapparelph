<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prototype_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prototype_sale_id');
            $table->string('payment_type')->default('additional'); // additional, fullpayment, down_payment
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->nullable(); // cash, gcash, bank_transfer, check
            $table->unsignedBigInteger('payment_account_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->string('payment_status', 50)->default('pending'); // pending, verified, rejected, down_payment_verified, additional_payment_verified, full_payment_verified
            $table->string('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('prototype_sale_id')->references('id')->on('prototype_sales')->onDelete('cascade');
            $table->foreign('payment_account_id')->references('id')->on('payment_accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prototype_payments');
    }
};
