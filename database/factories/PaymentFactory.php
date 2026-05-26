<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contract_id' => ContractFactory::new(),
            'amount' => fake()->randomFloat(2, 500, 2500),
            'payment_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'method' => fake()->randomElement(['cash', 'transfer', 'card', 'bizum']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
