<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('procurement_order_items', 'qty_damaged')) {
                $table->integer('qty_damaged')->default(0)->after('quantity_received');
            }
            if (!Schema::hasColumn('procurement_order_items', 'qty_missing')) {
                $table->integer('qty_missing')->default(0)->after('qty_damaged');
            }
            if (!Schema::hasColumn('procurement_order_items', 'qty_verified')) {
                $table->integer('qty_verified')->default(0)->after('qty_missing');
            }
        });

        Schema::table('procurement_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('procurement_orders', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('procurement_orders', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->constrained('users')->after('verified_at');
            }
            if (!Schema::hasColumn('procurement_orders', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('ordered_at');
            }
            if (!Schema::hasColumn('procurement_orders', 'received_by')) {
                $table->foreignId('received_by')->nullable()->constrained('users')->after('received_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('procurement_orders', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropForeign(['received_by']);
            $table->dropColumn(['verified_at', 'verified_by', 'received_at', 'received_by']);
        });
        Schema::table('procurement_order_items', function (Blueprint $table) {
            $table->dropColumn(['qty_damaged', 'qty_missing', 'qty_verified']);
        });
    }
};
