<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Layout Job — undo/retag lock (Andrew 2026-10-21).
 *
 * Kapag nag-retag/undo ng layout doer ang isang DONE job, bumabalik ito sa 'open'
 * pero naka-lock muna ng 7 araw bago pwedeng i-mark done ulit (anti-abuse sa credit).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layout_jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('layout_jobs', 'retagged_at')) {
                $table->timestamp('retagged_at')->nullable()->after('assigned_at');
            }
            if (!Schema::hasColumn('layout_jobs', 'retagged_by')) {
                $table->unsignedBigInteger('retagged_by')->nullable()->after('retagged_at');
            }
            if (!Schema::hasColumn('layout_jobs', 'retag_locked_until')) {
                $table->timestamp('retag_locked_until')->nullable()->after('retagged_by');
            }
            if (!Schema::hasColumn('layout_jobs', 'retag_count')) {
                $table->unsignedSmallInteger('retag_count')->default(0)->after('retag_locked_until');
            }
        });
    }

    public function down(): void
    {
        Schema::table('layout_jobs', function (Blueprint $table) {
            foreach (['retag_count', 'retag_locked_until', 'retagged_by', 'retagged_at'] as $col) {
                if (Schema::hasColumn('layout_jobs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
