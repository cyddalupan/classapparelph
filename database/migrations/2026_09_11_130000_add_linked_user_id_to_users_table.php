<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Linked accounts (isang tao, dalawang account — hal. Sales Agent + Agent).
 *
 *   users.linked_user_id → ang "kabila" na account ng parehong tao.
 *   Isang login lang; pwedeng mag-switch sa naka-link na account sa profile menu.
 *
 * Additive lang: default NULL = walang link (walang pagbabago sa ibang users).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'linked_user_id')) {
                $table->unsignedBigInteger('linked_user_id')->nullable()->after('price_tier');
                $table->index('linked_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'linked_user_id')) {
                $table->dropIndex(['linked_user_id']);
                $table->dropColumn('linked_user_id');
            }
        });
    }
};
