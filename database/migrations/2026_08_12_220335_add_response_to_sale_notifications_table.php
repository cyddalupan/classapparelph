<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sale_notifications', function (Blueprint $table) {
            $table->text('response')->nullable()->after('message');
            $table->timestamp('responded_at')->nullable()->after('response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_notifications', function (Blueprint $table) {
            $table->dropColumn(['response', 'responded_at']);
        });
    }
};
