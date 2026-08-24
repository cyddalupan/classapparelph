<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Damage System — core tables.
     * damage_reports: the report itself (shop-scoped, optionally linked to a sale).
     * damage_report_users: pivot — accountable user(s) + their share/amount + acknowledge state.
     * damage_report_comments: timeline (edits, review notes, appeals, replies).
     */
    public function up(): void
    {
        Schema::create('damage_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_no')->unique(); // DMG-20260824-XXXXXX
            $table->unsignedBigInteger('shop_id'); // sales_departments.id (iPrint/Class/Consol/...)
            $table->unsignedBigInteger('sale_id')->nullable(); // prototype_sales.id — tagged during manager edit
            $table->unsignedBigInteger('reporter_id'); // who filed the report
            $table->unsignedBigInteger('reviewer_id')->nullable(); // who reviewed/issued it
            $table->string('category')->default('other');
            $table->string('severity')->default('minor'); // minor / major / critical
            $table->string('status')->default('submitted'); // submitted/under_review/issued/acknowledged/contested/resolved/dismissed
            $table->text('description');
            $table->decimal('damage_amount', 12, 2)->nullable(); // set at review time only
            $table->integer('points')->default(0); // severity points, set at review
            $table->string('evidence_path')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('shop_id')->references('id')->on('sales_departments');
            $table->foreign('sale_id')->references('id')->on('prototype_sales')->onDelete('set null');
            $table->foreign('reporter_id')->references('id')->on('users');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'shop_id']);
        });

        Schema::create('damage_report_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('damage_report_id');
            $table->unsignedBigInteger('user_id'); // accountable user
            $table->decimal('amount_share', 12, 2)->default(0);
            $table->string('acknowledge_status')->default('pending'); // pending/acknowledged/contested
            $table->text('reply')->nullable(); // user's reply when acknowledging/contesting
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->foreign('damage_report_id')->references('id')->on('damage_reports')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['damage_report_id', 'user_id']);
        });

        Schema::create('damage_report_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('damage_report_id');
            $table->unsignedBigInteger('user_id');
            $table->text('comment');
            $table->timestamps();

            $table->foreign('damage_report_id')->references('id')->on('damage_reports')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_report_comments');
        Schema::dropIfExists('damage_report_users');
        Schema::dropIfExists('damage_reports');
    }
};
