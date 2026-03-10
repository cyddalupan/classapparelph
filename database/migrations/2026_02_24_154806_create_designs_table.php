<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['custom', 'template'])->default('custom');
            $table->string('file_path'); // Path to design file
            $table->string('file_format'); // png, jpg, svg, ai, psd
            $table->integer('file_size')->nullable(); // in bytes
            $table->json('colors_used')->nullable(); // JSON array of colors
            $table->integer('width')->nullable(); // in pixels
            $table->integer('height')->nullable(); // in pixels
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'archived'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_public')->default(false); // Can be used as template
            $table->decimal('template_price', 10, 2)->nullable(); // If sold as template
            $table->json('metadata')->nullable(); // JSON for additional data
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'status']);
            $table->index(['type', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designs');
    }
};
