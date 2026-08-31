<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ga_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prototype_sale_id');
            $table->string('stage', 50)->default(''); // FOR SAMPLE / FOR APPROVAL / FOR FORMAT / PRINTING
            $table->unsignedBigInteger('user_id');      // assigned GA
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('prototype_sale_id');
            $table->index('user_id');
            $table->unique(['prototype_sale_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ga_assignments');
    }
};
