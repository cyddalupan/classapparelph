<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Payment review requests (balance close-out): Sales Agent requests a review
     * when the remaining balance can't be zeroed via normal payment (EWT/taxes/
     * bawas). Accountant verifies proof then ACCEPTS (zeroes balance → unlocks
     * DONE) or REJECTS (reason required). Accepted requests go to CEO/COO
     * review queue — pending CEO/COO review blocks archiving. Full amount only
     * (no partial), proof image required. Pattern: special price / freebie list.
     *
     * Statuses: requested → accepted → reviewed  (accountant accept)
     *           requested → rejected             (accountant reject)
     */
    public function up(): void
    {
        Schema::create('payment_review_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prototype_sale_id');
            $table->unsignedBigInteger('requested_by');              // users.id (sales agent)
            $table->text('reason');                                  // bakit hindi nag-zero (EWT/taxes/bawas)
            $table->decimal('amount', 12, 2)->default(0);            // FULL remaining balance (no partial)
            $table->string('reference_number')->nullable();
            $table->string('proof_image');                           // REQUIRED upload (BIR 2307 / screenshot / resibo)
            $table->string('status', 20)->default('requested');      // requested | accepted | rejected | reviewed
            $table->unsignedBigInteger('accountant_id')->nullable(); // users.id (accountant na nag-verify)
            $table->text('accountant_note')->nullable();             // REJECT: required reason; ACCEPT: optional note
            $table->timestamp('accountant_action_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();   // users.id (CEO/COO)
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('prototype_sale_id');
            $table->index('requested_by');
            $table->index('status');
            $table->index('accountant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_review_requests');
    }
};
