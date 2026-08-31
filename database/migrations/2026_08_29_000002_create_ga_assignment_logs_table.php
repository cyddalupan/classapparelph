<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ga_assignment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prototype_sale_id');
            $table->string('stage', 50)->default('');       // FOR SAMPLE / FOR APPROVAL / FOR FORMAT / PRINTING
            $table->unsignedBigInteger('user_id');           // GA na may-ari ng claim
            $table->string('action', 30);                    // assigned / unassigned / completed / uncompleted
            $table->unsignedBigInteger('actor_id')->nullable(); // sino gumawa (GA o Manager)
            $table->timestamps();

            $table->index('prototype_sale_id');
            $table->index('user_id');
            $table->index('actor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ga_assignment_logs');
    }
};
