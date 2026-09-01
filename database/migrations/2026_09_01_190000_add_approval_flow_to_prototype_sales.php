<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'pending_approval' to the status enum (raw SQL — no doctrine/dbal installed)
        DB::statement("ALTER TABLE prototype_sales MODIFY COLUMN status ENUM('draft','pending','pending_approval','confirmed','in_production','completed','cancelled') NOT NULL DEFAULT 'draft'");

        // Approval workflow metadata (requested time + who requested)
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->timestamp('approval_requested_at')->nullable()->after('status');
            $table->unsignedBigInteger('approval_requested_by')->nullable()->after('approval_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('prototype_sales', function (Blueprint $table) {
            $table->dropColumn(['approval_requested_at', 'approval_requested_by']);
        });
        DB::statement("ALTER TABLE prototype_sales MODIFY COLUMN status ENUM('draft','pending','confirmed','in_production','completed','cancelled') NOT NULL DEFAULT 'draft'");
    }
};
