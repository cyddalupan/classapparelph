<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CEO/COO review ("checked") state for special-price lines on the
        // Special Price Review page. One row per sale + line (additive only —
        // doesn't touch existing tables or logic).
        Schema::create('prototype_special_price_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->string('line_key', 255);
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['sale_id', 'line_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prototype_special_price_reviews');
    }
};
