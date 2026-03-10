<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinanceDashboardButtonTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function add_expense_button_exists_on_finance_dashboard()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Visit the finance dashboard
        $response = $this->get('/finance');
        
        // Assert page loads successfully
        $response->assertStatus(200);
        
        // Assert the "Add Expense" button exists
        $response->assertSee('Add Expense');
        $response->assertSee('id="add-expense-btn"', false); // Check for button ID
    }

    /** @test */
    public function add_expense_button_opens_modal_not_shows_alert_on_dashboard()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Visit the finance dashboard
        $response = $this->get('/finance');
        
        // Assert page loads successfully
        $response->assertStatus(200);
        
        // Check that the button does NOT have onclick="alert()" 
        $response->assertDontSee('onclick="alert(', false);
        
        // Check that button has proper modal opening function
        $response->assertSee('showAddExpenseModal()', false);
    }

    /** @test */
    public function add_expense_modal_exists_on_dashboard()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Visit the finance dashboard
        $response = $this->get('/finance');
        
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
    public function javascript_functions_exist_on_dashboard()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Visit the finance dashboard
        $response = $this->get('/finance');
        
        // Assert page loads successfully
        $response->assertStatus(200);
        
        // Check that JavaScript functions exist
        $response->assertSee('function showAddExpenseModal()', false);
        $response->assertSee('function closeAddExpenseModal()', false);
        $response->assertSee('function submitExpense(event)', false);
    }

    /** @test */
    public function modal_close_event_listener_exists()
    {
        // Create a user and authenticate
        $user = User::factory()->create([
            'email' => 'test@classapparelph.com',
            'password' => bcrypt('password123')
        ]);
        
        $this->actingAs($user);

        // Visit the finance dashboard
        $response = $this->get('/finance');
        
        // Assert page loads successfully
        $response->assertStatus(200);
        
        // Check that modal close event listener exists
        $response->assertSee('modal.addEventListener(\'click\'', false);
    }
}