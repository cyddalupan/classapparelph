<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sublimation_prices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['garment', 'fabric', 'part'])->default('garment');
            $table->string('tab_group')->default('shirt'); // shirt, pillow, etc.
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('agent_price', 10, 2)->nullable();
            $table->integer('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sublimation_prices');
    }
};
