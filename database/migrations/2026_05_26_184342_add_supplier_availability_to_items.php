<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('procurement_order_items', 'qty_from_supplier')) {
                $table->integer('qty_from_supplier')->nullable()->after('quantity_ordered')
                    ->comment('Supplier availability — procurement enters this after checking with supplier');
            }
            if (!Schema::hasColumn('procurement_order_items', 'supplier_notes')) {
                $table->text('supplier_notes')->nullable()->after('qty_verified')
                    ->comment('Procurement notes about supply issues, brand substitutions, etc.');
            }
        });
    }

    public function down(): void
    {
        Schema::table('procurement_order_items', function (Blueprint $table) {
            $table->dropColumn(['qty_from_supplier', 'supplier_notes']);
        });
    }
};
