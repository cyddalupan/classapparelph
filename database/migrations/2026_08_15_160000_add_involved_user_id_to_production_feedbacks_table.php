<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Single feedback record per action: the primary recipient (to_user_id)
     * plus an optional involved/tagged user (involved_user_id, e.g. an Artist).
     * Both see the SAME feedback entry — no more duplicate records.
     */
    public function up(): void
    {
        Schema::table('production_feedbacks', function (Blueprint $table) {
            $table->unsignedBigInteger('involved_user_id')->nullable()->after('to_user_id');
            $table->foreign('involved_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('production_feedbacks', function (Blueprint $table) {
            $table->dropForeign(['involved_user_id']);
            $table->dropColumn('involved_user_id');
        });
    }
};
