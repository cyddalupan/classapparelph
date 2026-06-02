<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sublimation_connected_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_item_id');
            $table->timestamps();

            $table->foreign('master_item_id')
                  ->references('id')
                  ->on('master_items')
                  ->cascadeOnDelete();

            $table->unique('master_item_id');
        });

        // Seed with existing sublimation master items so Andrew doesnt lose them
        DB::table('sublimation_connected_products')->insert([
            ['master_item_id' => 153, 'created_at' => now(), 'updated_at' => now()],
            ['master_item_id' => 169, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sublimation_connected_products');
    }
};
