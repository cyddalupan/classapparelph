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
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('printing_method', ['screen', 'dtg', 'sublimation', 'vinyl', 'embroidery', 'other']);
            $table->string('job_ticket_number')->unique()->nullable();
            $table->enum('status', ['pending', 'prepress', 'printing', 'curing', 'quality_check', 'packaging', 'completed', 'cancelled'])->default('pending');
            $table->integer('quantity');
            $table->integer('good_quantity')->nullable(); // Passed quality check
            $table->integer('defective_quantity')->default(0);
            $table->text('defective_reason')->nullable();
            $table->json('materials_used')->nullable(); // JSON of materials and quantities
            $table->decimal('material_cost', 10, 2)->default(0);
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->text('production_notes')->nullable();
            $table->timestamp('scheduled_start_at')->nullable();
            $table->timestamp('actual_start_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('quality_check_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('quality_checked_at')->nullable();
            $table->text('quality_notes')->nullable();
            $table->timestamps();
            
            $table->index(['order_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['job_ticket_number']);
            $table->index(['scheduled_start_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
