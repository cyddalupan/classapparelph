<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Drew GCash", "Jemel GCash"
            $table->string('provider')->default('gcash'); // gcash, bank, cash, maya
            $table->string('account_number')->nullable(); // optional: actual account #
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // who owns/verifies this account
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Insert default accounts
        DB::table('payment_accounts')->insert([
            [
                'name' => 'Drew GCash',
                'provider' => 'gcash',
                'user_id' => 1, // Admin User (Andrew)
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jemel GCash',
                'provider' => 'gcash',
                'user_id' => 5, // Procurement (Chief of Procurement)
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'AJ GCash',
                'provider' => 'gcash',
                'user_id' => null, // no specific user yet
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Company Cash',
                'provider' => 'cash',
                'user_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Company Bank',
                'provider' => 'bank_transfer',
                'user_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_accounts');
    }
};
