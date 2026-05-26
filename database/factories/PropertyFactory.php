<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->streetName() . ' Apartment',
            'address' => fake()->address(),
            'price' => fake()->randomFloat(2, 600, 2500),
            'size' => fake()->numberBetween(45, 180),
            'rooms' => fake()->numberBetween(1, 5),
            'bathrooms' => fake()->numberBetween(1, 3),
            'status' => fake()->randomElement(['available', 'rented', 'maintenance']),
            'description' => fake()->optional()->paragraph(),
        ];
    }
}
