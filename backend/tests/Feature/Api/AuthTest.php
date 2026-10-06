<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_and_receive_a_token(): void
    {
        $user = User::factory()->create(['email' => 'pengurus@example.com']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'pengurus@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role', 'is_active']])
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.role', 'pengurus');

        $this->getJson('/api/auth/me', ['Authorization' => 'Bearer '.$response->json('token')])
            ->assertOk()
            ->assertJsonPath('data.email', 'pengurus@example.com');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'pengurus@example.com']);

        $this->postJson('/api/auth/login', [
            'email' => 'pengurus@example.com',
            'password' => 'salah',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        User::factory()->inactive()->create(['email' => 'nonaktif@example.com']);

        $this->postJson('/api/auth/login', [
            'email' => 'nonaktif@example.com',
            'password' => 'password',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_guest_cannot_access_protected_routes(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
        $this->getJson('/api/anak-panti')->assertUnauthorized();
    }

    public function test_user_can_log_out(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->postJson('/api/auth/logout', [], ['Authorization' => 'Bearer '.$token])
            ->assertNoContent();

        $this->assertCount(0, $user->tokens()->get());
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/auth/password', [
            'current_password' => 'salah',
            'password' => 'Password-baru-123',
            'password_confirmation' => 'Password-baru-123',
        ])->assertUnprocessable()->assertJsonValidationErrors('current_password');

        $this->putJson('/api/auth/password', [
            'current_password' => 'password',
            'password' => 'Password-baru-123',
            'password_confirmation' => 'Password-baru-123',
        ])->assertNoContent();

        $this->assertTrue(password_verify('Password-baru-123', $user->fresh()->password));
    }
}
