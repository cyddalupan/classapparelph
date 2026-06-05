<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prototype_sale_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('prototype_sales')->cascadeOnDelete();
            $table->json('services_before');        // snapshot of services before edit
            $table->json('services_after');          // proposed new services
            $table->decimal('total_before', 12, 2)->default(0);
            $table->decimal('total_after', 12, 2)->default(0);
            $table->decimal('deposit_before', 12, 2)->default(0);
            $table->decimal('deposit_after', 12, 2)->default(0);
            $table->text('change_summary')->nullable(); // auto-generated human readable summary
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('submitted_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('sale_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prototype_sale_changes');
    }
};
