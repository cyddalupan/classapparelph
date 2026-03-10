<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpenseCRUDTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_expense_via_form()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Create expense data
        $expenseData = [
            'date' => '2026-02-28',
            'amount' => 1500.75,
            'category' => 'supplies',
            'status' => 'pending',
            'description' => 'Test expense via form',
            'vendor' => 'Test Vendor',
            'payment_method' => 'cash',
            'receipt_number' => 'TEST-001',
            'notes' => 'Test notes'
        ];

        // Submit form via POST
        $response = $this->post('/finance/expenses', $expenseData);
        
        // Should redirect back to expenses page
        $response->assertRedirect('/finance/expenses');
        
        // Check expense was created in database
        $this->assertDatabaseHas('expenses', [
            'description' => 'Test expense via form',
            'amount' => 1500.75,
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function user_can_view_their_expenses()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Create some test expenses
        Expense::factory()->count(3)->create([
            'user_id' => $user->id
        ]);

        // Visit expenses page
        $response = $this->get('/finance/expenses');
        
        // Assert page loads successfully
        $response->assertStatus(200);
        
        // Should see expenses data
        $response->assertSee('Expenses');
        $response->assertSee('Add Expense');
    }

    /** @test */
    public function user_can_update_expense()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Create a test expense
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000.00,
            'status' => 'pending'
        ]);

        // Update expense via PUT (include all required fields)
        $updateData = [
            'date' => '2026-02-28',
            'amount' => 1500.00,
            'category' => 'supplies',
            'status' => 'paid',
            'description' => 'Updated description'
        ];

        $response = $this->put("/finance/expenses/{$expense->id}", $updateData);
        
        // Should redirect back
        $response->assertRedirect('/finance/expenses');
        
        // Check expense was updated in database
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'amount' => 1500.00,
            'status' => 'paid',
            'description' => 'Updated description'
        ]);
    }

    /** @test */
    public function user_can_delete_expense()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Create a test expense
        $expense = Expense::factory()->create([
            'user_id' => $user->id
        ]);

        // Delete expense via DELETE
        $response = $this->delete("/finance/expenses/{$expense->id}");
        
        // Should redirect back
        $response->assertRedirect('/finance/expenses');
        
        // Check expense was deleted from database
        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id
        ]);
    }

    /** @test */
    public function user_can_mark_expense_as_paid()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Create a test expense with pending status
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        // Mark as paid via POST
        $response = $this->post("/finance/expenses/{$expense->id}/mark-paid");
        
        // Should redirect back
        $response->assertRedirect('/finance/expenses');
        
        // Check expense status was updated to paid
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'paid'
        ]);
    }

    /** @test */
    public function user_cannot_access_other_users_expenses()
    {
        // Create two users
        $user1 = User::factory()->create([
            'email' => 'user1@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $user2 = User::factory()->create([
            'email' => 'user2@classapparelph.com',
            'password' => bcrypt('password123')
        ]);

        // Create expense for user1
        $expense = Expense::factory()->create([
            'user_id' => $user1->id,
            'description' => 'User1 Expense'
        ]);

        // Authenticate as user2
        $this->actingAs($user2);

        // Try to update user1's expense
        $response = $this->put("/finance/expenses/{$expense->id}", [
            'description' => 'Hacked by User2'
        ]);
        
        // Should return 403 or 404 (not found/forbidden)
        $response->assertStatus(403);
        
        // Expense should not be updated
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'description' => 'User1 Expense' // Original description unchanged
        ]);
    }

    /** @test */
    public function api_returns_expenses_json()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Create some test expenses
        Expense::factory()->count(5)->create([
            'user_id' => $user->id
        ]);

        // Call API endpoint
        $response = $this->getJson('/api/expenses');
        
        // Should return JSON with expenses
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'date', 'amount', 'category', 'status', 'description']
            ]
        ]);
        
        // Should have 5 expenses
        $response->assertJsonCount(5, 'data');
    }

    /** @test */
    public function api_returns_expense_statistics()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Create test expenses with different statuses
        Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000.00,
            'status' => 'pending'
        ]);
        
        Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 2000.00,
            'status' => 'paid'
        ]);
        
        Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 1500.00,
            'status' => 'paid'
        ]);

        // Call statistics API
        $response = $this->getJson('/api/expenses/statistics');
        
        // Should return JSON with statistics
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_expenses',
            'total_amount',
            'pending_count',
            'paid_count',
            'overdue_count',
            'by_category'
        ]);
        
        // Check totals
        $response->assertJson([
            'total_expenses' => 3,
            'total_amount' => 4500.00,
            'pending_count' => 1,
            'paid_count' => 2,
            'overdue_count' => 0
        ]);
    }
}