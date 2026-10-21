<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-agent grouping para sa Team Board (Andrew 2026-10-15).
 * Pinipili ni admin kung saang section/group ilalagay ang bawat agent
 * (hal. "Sales Agent" vs "Agent"), o kung itatago muna sa board.
 * Purely ADDITIVE — hiwalay na table, walang gagalawin sa users/sales.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_board_member_groups')) {
            Schema::create('sales_board_member_groups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->string('group_name')->nullable();
                $table->boolean('visible')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_board_member_groups');
    }
};
