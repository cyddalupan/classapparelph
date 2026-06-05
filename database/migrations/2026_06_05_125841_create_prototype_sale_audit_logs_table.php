<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prototype_sale_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('prototype_sales')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('action'); // item_added, item_removed, item_modified, size_changed, change_submitted, change_approved, change_rejected, comment_added, status_changed
            $table->text('description'); // human-readable summary
            $table->json('details')->nullable(); // structured data about the change
            $table->timestamps();

            $table->index('sale_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prototype_sale_audit_logs');
    }
};
