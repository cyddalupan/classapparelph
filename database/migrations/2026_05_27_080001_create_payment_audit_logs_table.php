<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prototype_sale_id')->constrained('prototype_sales')->cascadeOnDelete();
            $table->foreignId('payment_account_id')->nullable()->constrained('payment_accounts')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // who did the action
            $table->string('action'); // verified, rejected, re_tagged, edited_ref, edited_date, requested_verify
            $table->string('old_value')->nullable(); // previous value (old account name, old ref, old date)
            $table->string('new_value')->nullable(); // new value
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('prototype_sale_id');
            $table->index('payment_account_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_audit_logs');
    }
};
