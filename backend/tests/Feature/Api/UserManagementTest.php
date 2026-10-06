<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_pengurus_account(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/users', [
            'name' => 'Siti',
            'email' => 'siti@example.com',
            'password' => 'Rahasia-123',
            'role' => 'pengurus',
        ])->assertCreated()
            ->assertJsonPath('data.role', 'pengurus')
            ->assertJsonMissingPath('data.password');

        $this->postJson('/api/auth/login', [
            'email' => 'siti@example.com',
            'password' => 'Rahasia-123',
        ])->assertOk();
    }

    public function test_pengurus_cannot_manage_accounts(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/users')->assertForbidden();
        $this->postJson('/api/users', [])->assertForbidden();
    }

    public function test_deactivating_a_user_revokes_their_tokens(): void
    {
        $pengurus = User::factory()->create();
        $pengurus->createToken('hp');
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->patchJson("/api/users/{$pengurus->id}", ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertCount(0, $pengurus->tokens()->get());
    }

    public function test_admin_cannot_demote_deactivate_or_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->patchJson("/api/users/{$admin->id}", ['role' => 'pengurus'])
            ->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->patchJson("/api/users/{$admin->id}", ['is_active' => false])
            ->assertUnprocessable()->assertJsonValidationErrors('is_active');
        $this->deleteJson("/api/users/{$admin->id}")
            ->assertUnprocessable();

        $this->patchJson("/api/users/{$admin->id}", ['name' => 'Nama Baru'])
            ->assertOk()->assertJsonPath('data.name', 'Nama Baru');
    }

    public function test_admin_can_delete_another_user(): void
    {
        $pengurus = User::factory()->create();
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->deleteJson("/api/users/{$pengurus->id}")->assertNoContent();
        $this->assertModelMissing($pengurus);
    }
}
