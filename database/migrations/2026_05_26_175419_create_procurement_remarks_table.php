<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_remarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->text('remark');
            $table->string('type')->default('general'); // general, issue, shortage, damage
            $table->boolean('is_internal')->default(false); // internal vs visible to manager
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_remarks');
    }
};
