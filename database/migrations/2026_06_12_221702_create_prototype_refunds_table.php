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
        Schema::create('prototype_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prototype_sale_id')->constrained('prototype_sales')->onDelete('cascade');
            $table->decimal('refund_amount', 12, 2)->default(0.00);
            $table->enum('refund_reason', ['reprocess_overpayment', 'cancellation', 'other'])->default('other');
            $table->text('reason_details')->nullable();
            $table->enum('refund_method', ['cash', 'bank_transfer', 'gcash', 'paymaya', 'credit_card', 'other'])->nullable();
            $table->foreignId('refund_account_id')->nullable()->constrained('payment_accounts')->onDelete('set null');
            $table->string('refund_account_name')->nullable();
            $table->string('refund_account_number')->nullable();
            $table->enum('refund_status', ['pending', 'approved', 'completed', 'rejected'])->default('pending');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prototype_refunds');
    }
};
