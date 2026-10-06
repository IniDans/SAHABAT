<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AkunSayaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_their_name_and_email(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.akun-saya.edit'))->assertOk();

        $this->put(route('admin.akun-saya.update'), ['name' => 'budi', 'email' => 'budi@example.com'])
            ->assertRedirect(route('admin.akun-saya.edit'))
            ->assertSessionHas('status');

        $user->refresh();
        $this->assertSame('budi', $user->name);
        $this->assertSame('budi@example.com', $user->email);
    }

    public function test_email_of_another_account_is_rejected(): void
    {
        User::factory()->create(['email' => 'dipakai@example.com']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.akun-saya.update'), ['name' => $user->name, 'email' => 'dipakai@example.com'])
            ->assertSessionHasErrorsIn('profil', 'email');

        $this->assertNotSame('dipakai@example.com', $user->fresh()->email);
    }

    public function test_user_cannot_change_role_through_their_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.akun-saya.update'), ['name' => $user->name, 'email' => $user->email, 'role' => 'admin', 'is_active' => '1']);

        $this->assertFalse($user->fresh()->isAdmin());
    }

    public function test_password_change_requires_the_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.akun-saya.password'), [
                'current_password' => 'salah',
                'password' => 'Password-Baru-1',
                'password_confirmation' => 'Password-Baru-1',
            ])
            ->assertSessionHasErrorsIn('password', ['current_password' => 'Password saat ini salah.']);

        $this->assertTrue(password_verify('password', $user->fresh()->password));
    }

    public function test_password_change_signs_out_other_devices(): void
    {
        $user = User::factory()->create();
        $user->createToken('android');

        $this->actingAs($user)
            ->put(route('admin.akun-saya.password'), [
                'current_password' => 'password',
                'password' => 'Password-Baru-1',
                'password_confirmation' => 'Password-Baru-1',
            ])
            ->assertRedirect(route('admin.akun-saya.edit'))
            ->assertSessionHas('status', 'Password berhasil diganti. Perangkat lain harus login ulang.');

        $user->refresh();
        $this->assertTrue(password_verify('Password-Baru-1', $user->password));
        $this->assertSame(0, $user->tokens()->count());
        $this->assertAuthenticatedAs($user);
    }
}
