<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'file_path' => 'documents/' . fake()->uuid() . '.pdf',
            'type' => fake()->optional()->randomElement(['contract', 'invoice', 'dni', 'other']),
            'property_id' => null,
            'tenant_id' => null,
            'contract_id' => null,
        ];
    }
}
