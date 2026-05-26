<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_tenant_with_multiple_properties(): void
    {
        $properties = Property::factory()->count(2)->create();
        Sanctum::actingAs(User::factory()->create(['role' => 'manager']));

        $response = $this->postJson('/api/tenants', [
            'name' => 'Inquilino Demo',
            'email' => 'inquilino@example.com',
            'phone' => '+34622222222',
            'dni' => '11223344A',
            'notes' => 'Contrato compartido',
            'property_ids' => $properties->pluck('id')->all(),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('name', 'Inquilino Demo')
            ->assertJsonCount(2, 'properties');

        $tenant = Tenant::where('email', 'inquilino@example.com')->firstOrFail();
        $this->assertSame(
            $properties->pluck('id')->sort()->values()->all(),
            $tenant->properties()->pluck('properties.id')->sort()->values()->all(),
        );
    }
}
