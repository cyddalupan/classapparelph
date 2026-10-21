<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Announcements / "news" na ipinapakita sa Team Board (Andrew 2026-10-15).
 * Purely ADDITIVE. Admin-only sa pag-post (enforced sa controller).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_announcements')) {
            Schema::create('sales_announcements', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('body')->nullable();
                $table->string('type', 30)->default('news'); // news | motivation | alert
                $table->boolean('pinned')->default(false);
                $table->boolean('active')->default(true);
                $table->timestamp('publish_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['active', 'pinned'], 'sales_announcements_active_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_announcements');
    }
};
