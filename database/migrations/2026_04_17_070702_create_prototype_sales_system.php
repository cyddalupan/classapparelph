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
        // Departments table
        Schema::create('sales_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // iPrint, Consol, Cinco, Class, Made to Order, Other
            $table->string('code')->unique(); // iprint, consol, cinco, class, mto, other
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Enhanced sales table with all your requirements
        Schema::create('prototype_sales', function (Blueprint $table) {
            $table->id();
            $table->string('sales_number')->unique();
            
            // Customer information
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->text('customer_address')->nullable();
            
            // Sales agent information
            $table->foreignId('sales_agent_id')->constrained('users');
            $table->string('sales_agent_name');
            
            // Department assignment
            $table->foreignId('department_id')->constrained('sales_departments');
            $table->string('department_name');
            
            // Service details
            $table->json('services'); // Array of services with quantities, prices, etc.
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('deposit_paid', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2);
            
            // Payment verification
            $table->enum('payment_method', ['cash', 'bank_transfer', 'gcash', 'paymaya', 'credit_card', 'other']);
            $table->string('payment_owner')->nullable(); // Who owns the payment account
            $table->string('payment_screenshot_path')->nullable(); // Screenshot upload
            $table->enum('payment_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            
            // Mockup/images
            $table->json('mockup_images')->nullable(); // Array of mockup image paths
            $table->json('reference_images')->nullable(); // Array of reference image paths
            
            // KANBAN status
            $table->enum('kanban_status', ['new', 'design', 'production', 'quality_check', 'ready_for_delivery', 'delivered', 'completed'])->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users'); // Who's working on it
            
            // Timeline
            $table->timestamp('estimated_completion_date')->nullable();
            $table->timestamp('actual_completion_date')->nullable();
            
            // Notes
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'pending', 'confirmed', 'in_production', 'completed', 'cancelled'])->default('draft');
            
            $table->timestamps();
            $table->softDeletes();
        });

        // KANBAN board items
        Schema::create('sales_kanban_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('prototype_sales');
            $table->foreignId('department_id')->constrained('sales_departments');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'review', 'done'])->default('todo');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->integer('position')->default(0); // For drag-and-drop ordering
            $table->json('attachments')->nullable(); // Images, files
            $table->timestamps();
        });

        // Customer-agent relationships (for agent-specific customer database)
        Schema::create('customer_agent_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('agent_id')->constrained('users');
            $table->text('notes')->nullable();
            $table->integer('total_sales')->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['customer_id', 'agent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_agent_relationships');
        Schema::dropIfExists('sales_kanban_items');
        Schema::dropIfExists('prototype_sales');
        Schema::dropIfExists('sales_departments');
    }
};
