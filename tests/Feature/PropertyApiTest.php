<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PropertyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_list_properties(): void
    {
        Property::factory()->count(2)->create();
        Sanctum::actingAs(User::factory()->create(['role' => 'manager']));

        $response = $this->getJson('/api/properties');

        $response
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => ['id', 'title', 'address', 'price', 'status'],
            ]);
    }

    public function test_property_creation_is_validated(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'manager']));

        $response = $this->postJson('/api/properties', [
            'title' => '',
            'address' => '',
            'price' => 'not-a-number',
            'status' => 'wrong',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'address', 'price', 'status']);
    }
}
