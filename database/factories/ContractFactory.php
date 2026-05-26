<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    protected $model = Contract::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', '+1 month');

        return [
            'property_id' => PropertyFactory::new(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => fake()->optional()->dateTimeBetween($startDate, '+2 years')?->format('Y-m-d'),
            'rent_price' => fake()->randomFloat(2, 600, 2500),
            'deposit' => fake()->optional()->randomFloat(2, 600, 2500),
            'status' => fake()->randomElement(['active', 'finished', 'cancelled']),
        ];
    }
}
