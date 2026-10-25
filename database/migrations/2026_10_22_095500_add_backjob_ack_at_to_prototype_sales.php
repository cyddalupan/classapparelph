<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Dumating na Backjob" acknowledgment sa Manager Order List Progress column (Andrew 2026-10-22).
 * Kapag may bagong backjob na naidagdag pagkatapos ng ack, babalik sa "dumating na ba?" ang badge.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dateTime('backjob_ack_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dropColumn('backjob_ack_at');
        });
    }
};
