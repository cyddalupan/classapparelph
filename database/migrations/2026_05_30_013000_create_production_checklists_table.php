<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id')->unique();
            $table->json('items')->nullable(); // [{type, label, value, status}]
            $table->boolean('ga_done')->default(false);
            $table->timestamp('ga_done_at')->nullable();
            $table->text('ga_notes')->nullable();
            $table->boolean('qa1_done')->default(false);
            $table->timestamp('qa1_done_at')->nullable();
            $table->text('qa1_notes')->nullable();
            $table->boolean('press_done')->default(false);
            $table->timestamp('press_done_at')->nullable();
            $table->boolean('qa2_done')->default(false);
            $table->timestamp('qa2_done_at')->nullable();
            $table->text('qa2_notes')->nullable();
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('prototype_sales')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_checklists');
    }
};
