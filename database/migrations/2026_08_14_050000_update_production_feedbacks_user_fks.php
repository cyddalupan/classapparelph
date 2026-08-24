<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make production_feedbacks.to_user_id / from_user_id nullable with
     * nullOnDelete so feedback history is preserved (with recipient set to
     * NULL) when an artist/sales agent/manager user is deleted.
     */
    public function up(): void
    {
        Schema::table('production_feedbacks', function (Blueprint $table) {
            $table->dropForeign(['from_user_id']);
            $table->dropForeign(['to_user_id']);

            $table->unsignedBigInteger('from_user_id')->nullable()->change();
            $table->unsignedBigInteger('to_user_id')->nullable()->change();

            $table->foreign('from_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('to_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('production_feedbacks', function (Blueprint $table) {
            $table->dropForeign(['from_user_id']);
            $table->dropForeign(['to_user_id']);

            $table->unsignedBigInteger('from_user_id')->nullable(false)->change();
            $table->unsignedBigInteger('to_user_id')->nullable(false)->change();

            $table->foreign('from_user_id')->references('id')->on('users');
            $table->foreign('to_user_id')->references('id')->on('users');
        });
    }
};
