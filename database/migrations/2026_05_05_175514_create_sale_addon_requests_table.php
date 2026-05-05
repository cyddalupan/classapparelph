<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_addon_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->text('requested_items'); // JSON: same structure as items_json
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('requested_by')->nullable();    // agent name who requested
            $table->string('approved_by')->nullable();      // who approved/rejected
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('prototype_sales')->onDelete('cascade');
            $table->index('status');
            $table->index('sale_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_addon_requests');
    }
};
