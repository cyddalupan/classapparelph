<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Layout Job — "Hindi Tumuloy" (client did not push through).
 *
 * Additive lang: hindi ginagalaw ang `status` enum (open|done). Ang pagkansela
 * ay naka-marka sa `cancelled_at` (+ sino at bakit). Kapag may `cancelled_at`,
 * ituturing na CANCELLED ang job at hindi na kailangan ng sale link.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layout_jobs', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('linked_to_sale_at');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancel_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::table('layout_jobs', function (Blueprint $table) {
            $table->dropColumn(['cancel_reason', 'cancelled_by', 'cancelled_at']);
        });
    }
};
