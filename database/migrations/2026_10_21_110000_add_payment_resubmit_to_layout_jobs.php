<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Layout Job — payment re-upload after rejection (Andrew 2026-10-21).
 *
 * Kapag na-reject ang payment, dapat may chance ang creator na mag-upload ulit
 * ng proof/ref. Ini-archive namin ang huling reject reason para may audit trail
 * kahit i-reset ang payment_status pabalik sa pending.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layout_jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('layout_jobs', 'payment_resubmit_count')) {
                $table->unsignedSmallInteger('payment_resubmit_count')->default(0)->after('payment_reject_reason');
            }
            if (!Schema::hasColumn('layout_jobs', 'payment_last_reject_reason')) {
                $table->text('payment_last_reject_reason')->nullable()->after('payment_resubmit_count');
            }
            if (!Schema::hasColumn('layout_jobs', 'payment_last_reject_at')) {
                $table->timestamp('payment_last_reject_at')->nullable()->after('payment_last_reject_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('layout_jobs', function (Blueprint $table) {
            foreach (['payment_last_reject_at', 'payment_last_reject_reason', 'payment_resubmit_count'] as $col) {
                if (Schema::hasColumn('layout_jobs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
