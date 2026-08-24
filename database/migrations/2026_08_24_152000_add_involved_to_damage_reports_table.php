<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('damage_reports', function (Blueprint $table) {
            // Position ng involved (e.g. Presser) — si manager ang magko-complete kung kulang
            $table->string('involved_position')->nullable()->after('quantity');
            // Pangalan ng involved kung kilala (optional)
            $table->string('involved_name')->nullable()->after('involved_position');
        });
    }

    public function down(): void
    {
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->dropColumn(['involved_position', 'involved_name']);
        });
    }
};
