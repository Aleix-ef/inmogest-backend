<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_manager_user(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'manager']));

        $this->postJson('/api/users', [
            'name' => 'Nueva Manager',
            'email' => 'nueva.manager@test.com',
            'password' => 'password123',
            'role' => 'manager',
        ])
            ->assertCreated()
            ->assertJsonPath('email', 'nueva.manager@test.com')
            ->assertJsonPath('role', 'manager');

        $this->assertDatabaseHas('users', [
            'email' => 'nueva.manager@test.com',
            'role' => 'manager',
        ]);
    }

    public function test_manager_creating_owner_user_also_creates_owner_profile(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'manager']));

        $this->postJson('/api/users', [
            'name' => 'Nuevo Propietario',
            'email' => 'nuevo.owner@test.com',
            'password' => 'password123',
            'role' => 'owner',
        ])->assertCreated();

        $user = User::where('email', 'nuevo.owner@test.com')->firstOrFail();

        $this->assertDatabaseHas('owners', [
            'user_id' => $user->id,
            'email' => 'nuevo.owner@test.com',
        ]);
    }

    public function test_owner_cannot_manage_users(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'owner']));

        $this->getJson('/api/users')
            ->assertForbidden();
    }

    public function test_manager_cannot_delete_own_user(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        Sanctum::actingAs($manager);

        $this->deleteJson("/api/users/{$manager->id}")
            ->assertUnprocessable();
    }

    public function test_manager_cannot_remove_own_manager_role(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        Sanctum::actingAs($manager);

        $this->putJson("/api/users/{$manager->id}", [
            'name' => $manager->name,
            'email' => $manager->email,
            'role' => 'owner',
        ])
            ->assertUnprocessable();
    }
}
