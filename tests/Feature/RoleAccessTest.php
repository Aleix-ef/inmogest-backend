<?php

namespace Tests\Feature;

use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_access_owners_endpoint(): void
    {
        Owner::factory()->count(2)->create();
        Sanctum::actingAs(User::factory()->create(['role' => 'manager']));

        $this->getJson('/api/owners')
            ->assertOk()
            ->assertJsonCount(2);
    }

    public function test_owner_cannot_access_owners_endpoint(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'owner']));

        $this->getJson('/api/owners')
            ->assertForbidden();
    }

    public function test_owner_only_sees_assigned_properties(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        $owner = Owner::factory()->create(['user_id' => $user->id]);
        $visibleProperty = Property::factory()->create(['title' => 'Visible']);
        Property::factory()->create(['title' => 'Hidden']);
        $owner->properties()->sync([$visibleProperty->id]);

        Sanctum::actingAs($user);

        $this->getJson('/api/properties')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'Visible');
    }
}
