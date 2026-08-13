<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('prototype_sales')->cascadeOnDelete();
            $table->foreignId('from_user_id')->constrained('users'); // manager who gave feedback
            $table->foreignId('to_user_id')->constrained('users');   // sales agent receiving it
            $table->string('category'); // missing_file, wrong_file_sent, no_response, incomplete_info, other
            $table->text('message');
            $table->string('status')->default('open'); // open -> acknowledged -> resolved
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['sale_id']);
            $table->index(['to_user_id', 'status']);
            $table->index(['from_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_feedbacks');
    }
};
