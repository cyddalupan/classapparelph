<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Freebie slips — created ONLY after a freebie request is approved.
     * 1:1 with the approved freebie request; items come from freebie_request_items.
     * status: open | done (done by GA/QA/Manager/admin).
     */
    public function up(): void
    {
        Schema::create('freebie_slips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('freebie_request_id')->unique(); // one slip per approved request
            $table->string('status', 20)->default('open');              // open | done
            $table->unsignedBigInteger('done_by')->nullable();          // users.id (GA/QA/Manager)
            $table->timestamp('done_at')->nullable();
            $table->timestamps();

            $table->index('sale_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freebie_slips');
    }
};
