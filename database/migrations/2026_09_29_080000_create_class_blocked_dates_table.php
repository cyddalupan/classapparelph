<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class department "closed dates" — per-date ON/OFF switch controlled by the
 * Prod Manager / CEO / COO. When a date is CLOSED, no NEW Class sale may be
 * created for that date (server-side gate in PrototypeSalesController::store()).
 * Existing sales are unaffected.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('class_blocked_dates')) {
            Schema::create('class_blocked_dates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('department_id')->default(4);
                $table->date('date');
                $table->boolean('is_closed')->default(true);
                $table->string('reason')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->unique(['department_id', 'date']);
                $table->index(['department_id', 'is_closed', 'date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_blocked_dates');
    }
};
