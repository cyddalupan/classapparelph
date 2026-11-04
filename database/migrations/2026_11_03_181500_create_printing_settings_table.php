<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('print_type', 30)->unique();
            // When true, bulk discounts only apply if the user picked A4 or A3 prints.
            $table->boolean('bulk_requires_large_size')->default(false);
            // Comma-separated size names treated as "large" (default: A3,A4)
            $table->string('bulk_gate_sizes', 120)->default('A3,A4');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printing_settings');
    }
};
