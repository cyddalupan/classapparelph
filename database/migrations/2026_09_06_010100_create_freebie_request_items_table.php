<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Line items of a freebie request (multiple per request):
     * item type/name, quantity, purpose, optional reference image.
     */
    public function up(): void
    {
        Schema::create('freebie_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('freebie_request_id');
            $table->string('description', 255);          // e.g. Free Tshirt / Banner
            $table->integer('quantity')->default(1);
            $table->string('purpose', 500)->nullable();  // "para saan"
            $table->string('reference_image', 500)->nullable(); // storage path
            $table->timestamps();

            $table->index('freebie_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freebie_request_items');
    }
};
