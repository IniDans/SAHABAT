<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AkunTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengurus_cannot_open_account_management(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.akun.index'))
            ->assertForbidden();
    }

    public function test_index_searches_and_filters_accounts(): void
    {
        User::factory()->create(['name' => 'Budi']);
        User::factory()->inactive()->create(['name' => 'Sari']);

        $this->actingAs(User::factory()->admin()->create(['name' => 'Admin']))
            ->get(route('admin.akun.index', ['search' => 'bud']))
            ->assertOk()
            ->assertSee('Budi')
            ->assertDontSee('Sari');

        $this->get(route('admin.akun.index', ['status' => 'nonaktif']))
            ->assertSee('Sari')
            ->assertDontSee('Budi');
    }

    public function test_admin_can_create_an_account(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.akun.create'))
            ->assertOk();

        $this->post(route('admin.akun.store'), $this->payload())
            ->assertRedirect(route('admin.akun.index'))
            ->assertSessionHas('status');

        $akun = User::where('email', 'siti@example.com')->sole();
        $this->assertSame('siti', $akun->name);
        $this->assertSame(Role::Pengurus, $akun->role);
        $this->assertTrue($akun->is_active);
        $this->assertSame('argon2id', password_get_info($akun->password)['algoName']);
        $this->assertTrue(password_verify('Rahasia-123', $akun->password));
    }

    public function test_weak_or_unconfirmed_password_is_rejected(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.akun.store'), $this->payload(['password' => 'rahasia123', 'password_confirmation' => 'rahasia123']))
            ->assertSessionHasErrors(['password' => 'The password field must contain at least one uppercase and one lowercase letter.']);

        $this->post(route('admin.akun.store'), $this->payload(['password_confirmation' => 'Lain-123']))
            ->assertSessionHasErrors('password');

        $this->assertSame(1, User::count());
    }

    public function test_username_and_email_must_be_unique(): void
    {
        User::factory()->create(['name' => 'siti', 'email' => 'siti@example.com']);

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.akun.store'), $this->payload())
            ->assertSessionHasErrors(['name', 'email']);
    }

    public function test_blank_password_on_update_keeps_the_old_password(): void
    {
        $akun = User::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.akun.update', $akun), $this->payload(['password' => '', 'password_confirmation' => '']))
            ->assertRedirect(route('admin.akun.index'));

        $akun->refresh();
        $this->assertSame('siti', $akun->name);
        $this->assertTrue(password_verify('password', $akun->password));
    }

    public function test_deactivating_an_account_revokes_its_api_tokens(): void
    {
        $akun = User::factory()->create();
        $akun->createToken('android');

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.akun.update', $akun), $this->payload(['password' => '', 'password_confirmation' => '', 'is_active' => '0']))
            ->assertRedirect(route('admin.akun.index'));

        $this->assertFalse($akun->fresh()->is_active);
        $this->assertSame(0, $akun->tokens()->count());
    }

    public function test_admin_cannot_demote_or_deactivate_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.akun.update', $admin), $this->payload(['role' => 'pengurus', 'password' => '', 'password_confirmation' => '']))
            ->assertSessionHasErrors(['role' => 'Anda tidak bisa menurunkan role akun sendiri.']);

        $this->put(route('admin.akun.update', $admin), $this->payload(['role' => 'admin', 'is_active' => '0', 'password' => '', 'password_confirmation' => '']))
            ->assertSessionHasErrors(['is_active' => 'Anda tidak bisa menonaktifkan akun sendiri.']);

        $admin->refresh();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->is_active);
    }

    public function test_admin_can_delete_another_account_but_not_their_own(): void
    {
        $admin = User::factory()->admin()->create();
        $akun = User::factory()->create();
        $akun->createToken('android');

        $this->actingAs($admin)
            ->delete(route('admin.akun.destroy', $admin))
            ->assertSessionHasErrors('akun');
        $this->assertModelExists($admin);

        $this->delete(route('admin.akun.destroy', $akun))->assertRedirect(route('admin.akun.index'));
        $this->assertModelMissing($akun);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $akun->id]);
    }

    /**
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    private function payload(array $override = []): array
    {
        return [
            'name' => 'siti',
            'email' => 'siti@example.com',
            'password' => 'Rahasia-123',
            'password_confirmation' => 'Rahasia-123',
            'role' => 'pengurus',
            'is_active' => '1',
            ...$override,
        ];
    }
}
