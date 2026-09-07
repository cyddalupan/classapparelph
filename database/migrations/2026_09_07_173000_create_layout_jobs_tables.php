<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Standalone Layout Job (pre-sale / sale-linked) — bayad or libre
        Schema::create('layout_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_no', 30)->unique(); // LJ-2026-0001
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable(); // fallback kung walang customer record
            $table->text('description')->nullable();     // anong layout/design
            $table->string('reference_image_path')->nullable(); // design reference image
            $table->enum('type', ['paid', 'free'])->default('free');

            // Amount — bayad: fee; libre: null hanggang lagyan ng approver
            $table->decimal('amount', 12, 2)->nullable();
            $table->unsignedBigInteger('amount_set_by')->nullable();
            $table->timestamp('amount_set_at')->nullable();

            // Payment fields (bayad lang)
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('payment_screenshot_path')->nullable();
            $table->enum('payment_status', ['pending', 'verified', 'rejected'])->nullable();
            $table->unsignedBigInteger('payment_verified_by')->nullable();
            $table->timestamp('payment_verified_at')->nullable();
            $table->text('payment_reject_reason')->nullable();

            // GA tag
            $table->unsignedBigInteger('ga_user_id')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();

            // Work status — GA marks done
            $table->enum('status', ['open', 'done'])->default('open');
            $table->unsignedBigInteger('done_by')->nullable();
            $table->timestamp('done_at')->nullable();

            // GA payout request (total layout singil sa company)
            $table->unsignedBigInteger('payout_id')->nullable(); // layout_job_payouts.id
            $table->timestamp('payout_requested_at')->nullable();

            // Sale link — bayad lang ang nalilink
            $table->unsignedBigInteger('sale_id')->nullable(); // prototype_sales.id
            $table->timestamp('linked_to_sale_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('ga_user_id');
            $table->index('status');
            $table->index('type');
            $table->index('customer_id');
            $table->index('sale_id');
            $table->index('payout_id');
        });

        // GA payout/credit ledger — GA sumisingil sa company for done layout jobs
        Schema::create('layout_job_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ga_user_id');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['requested', 'paid', 'verified', 'rejected'])->default('requested');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->text('request_notes')->nullable();

            // Approver uploads payment proof
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamps();

            $table->index('ga_user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layout_job_payouts');
        Schema::dropIfExists('layout_jobs');
    }
};
