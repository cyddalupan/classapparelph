<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Freebie requests (customer appreciation items: free tshirt, banner, etc.)
     * submitted by sales → approved/rejected by Manager/CEO/COO.
     * Additive only — does not touch existing tables.
     */
    public function up(): void
    {
        Schema::create('freebie_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('requested_by');          // users.id (sales/staff)
            $table->string('status', 20)->default('pending');    // pending | approved | rejected
            $table->unsignedBigInteger('approved_by')->nullable(); // users.id
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejection_reason', 500)->nullable();
            $table->text('notes')->nullable();                    // overall note (optional)
            $table->timestamps();

            $table->index('sale_id');
            $table->index('requested_by');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freebie_requests');
    }
};
