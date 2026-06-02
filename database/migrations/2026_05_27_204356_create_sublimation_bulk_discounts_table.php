<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sublimation_bulk_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('min_qty');
            $table->integer('max_qty')->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sublimation_bulk_discounts');
    }
};
