<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $categories = ['supplies', 'utilities', 'salaries', 'marketing', 'equipment', 'rent', 'transportation', 'other'];
        $statuses = ['pending', 'paid', 'overdue'];
        $paymentMethods = ['cash', 'bank_transfer', 'credit_card', 'gcash', 'maya', 'paypal', 'other'];
        
        return [
            'user_id' => User::factory(),
            'date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'category' => $this->faker->randomElement($categories),
            'status' => $this->faker->randomElement($statuses),
            'description' => $this->faker->sentence(6),
            'vendor' => $this->faker->company(),
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'receipt_number' => $this->faker->bothify('REC-#####'),
            'notes' => $this->faker->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the expense is pending.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }

    /**
     * Indicate that the expense is paid.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paid',
            ];
        });
    }

    /**
     * Indicate that the expense is overdue.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function overdue()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'overdue',
            ];
        });
    }

    /**
     * Indicate the expense category.
     *
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function category(string $category)
    {
        return $this->state(function (array $attributes) use ($category) {
            return [
                'category' => $category,
            ];
        });
    }

    /**
     * Indicate the expense amount range.
     *
     * @param float $min
     * @param float $max
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function amountRange(float $min, float $max)
    {
        return $this->state(function (array $attributes) use ($min, $max) {
            return [
                'amount' => $this->faker->randomFloat(2, $min, $max),
            ];
        });
    }
}