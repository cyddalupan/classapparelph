<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prototype_sale_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('prototype_sales')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->text('comment');
            $table->timestamps();

            $table->index('sale_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prototype_sale_comments');
    }
};
