<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Single global WH → CLASS delivery schedule (2026-10-22, Andrew).
 * One row only (id = 1): date/time kung kailan ipapadala ang parts from
 * Warehouse to Class. Additive — walang existing column na ginagalaw.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wh_class_schedule', function (Blueprint $table) {
            $table->id();
            $table->dateTime('scheduled_at')->nullable();
            $table->string('note')->nullable();
            $table->unsignedBigInteger('set_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wh_class_schedule');
    }
};
