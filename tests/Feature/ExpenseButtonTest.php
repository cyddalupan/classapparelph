<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpenseButtonTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function add_expense_button_exists_on_expenses_page()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Visit the expenses page
        $response = $this->get('/finance/expenses');
        
        // Assert page loads successfully
        $response->assertStatus(200);
        
        // Assert the "Add Expense" button exists
        $response->assertSee('Add Expense');
        $response->assertSee('id="add-expense-btn"', false); // Check for button ID
    }

    /** @test */
    public function add_expense_button_opens_modal_not_shows_alert()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Visit the expenses page
        $response = $this->get('/finance/expenses');
        
        // Assert page loads successfully
        $response->assertStatus(200);
        
        // Check that the button does NOT have onclick="alert()" (failing test)
        // This test will FAIL initially because button shows alert
        $response->assertDontSee('onclick="alert(', false);
        
        // Check that button has proper modal opening function
        $response->assertSee('showAddExpenseModal()', false);
    }

    /** @test */
    public function add_expense_modal_exists_with_form()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Visit the expenses page
        $response = $this->get('/finance/expenses');
        
        // Assert page loads successfully
        $response->assertStatus(200);
        
        // Check that modal exists
        $response->assertSee('id="add-expense-modal"', false);
        
        // Check that form exists within modal
        $response->assertSee('id="expense-form"', false);
        
        // Check for required form fields
        $response->assertSee('name="amount"', false);
        $response->assertSee('name="category"', false);
        $response->assertSee('name="description"', false);
        $response->assertSee('name="date"', false);
    }

    /** @test */
    public function add_expense_button_has_correct_styling()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Visit the expenses page
        $response = $this->get('/finance/expenses');
        
        // Assert page loads successfully
        $response->assertStatus(200);
        
        // Check button has correct classes for styling
        $response->assertSee('btn-primary', false);
        $response->assertSee('btn-add-expense', false);
    }

    /** @test */
    public function javascript_function_exists_to_open_modal()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Visit the expenses page
        $response = $this->get('/finance/expenses');
        
        // Assert page loads successfully
        $response->assertStatus(200);
        
        // Check that JavaScript function exists in page
        $response->assertSee('function showAddExpenseModal()', false);
    }
}