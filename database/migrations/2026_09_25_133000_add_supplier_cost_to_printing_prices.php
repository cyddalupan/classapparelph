<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('printing_prices', function (Blueprint $table) {
            // Editable costs on the print price row itself (no longer derived from linked product).
            $table->decimal('supplier_cost', 10, 2)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('printing_prices', function (Blueprint $table) {
            $table->dropColumn('supplier_cost');
        });
    }
};
