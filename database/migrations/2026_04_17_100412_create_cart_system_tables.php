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
        // Cart table - stores active shopping carts
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index(); // For guest carts
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Sales agent
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('status', ['active', 'abandoned', 'converted'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // Cart items table - individual items in cart
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('sales_departments')->onDelete('cascade');
            $table->string('product_type'); // garment, tarpaulin, embroidery, etc.
            $table->json('product_details'); // Brand, size, print area, dimensions, etc.
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Orders table - created when cart is converted to order
        Schema::create('prototype_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Sales agent
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->enum('payment_method', ['gcash', 'bank_transfer', 'cash', 'credit_card', 'check']);
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->decimal('deposit_paid', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2);
            $table->text('payment_notes')->nullable();
            $table->string('payment_screenshot_path')->nullable();
            $table->enum('payment_owner', ['company', 'owner_personal', 'sales_agent', 'department_head'])->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('payment_verified_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->date('estimated_completion_date')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'in_production', 'completed', 'cancelled'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        // Order items table - links to department-specific workflows
        Schema::create('prototype_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('prototype_orders')->onDelete('cascade');
            $table->foreignId('cart_item_id')->constrained('cart_items')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('sales_departments')->onDelete('cascade');
            $table->string('item_number'); // Department-specific item number (IPR-001, CON-001, etc.)
            $table->string('product_type');
            $table->json('product_details');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'design', 'production', 'quality_check', 'ready', 'delivered'])->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // KANBAN items table - for department workflow boards
        Schema::create('prototype_kanban_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('prototype_order_items')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('sales_departments')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('column', ['todo', 'design', 'production', 'quality', 'ready', 'delivered'])->default('todo');
            $table->integer('position')->default(0);
            $table->json('attachments')->nullable(); // Mockup images, reference photos
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prototype_kanban_items');
        Schema::dropIfExists('prototype_order_items');
        Schema::dropIfExists('prototype_orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};