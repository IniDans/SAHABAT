<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_only_reachable_by_typing_the_url(): void
    {
        $this->get('/login')->assertOk()->assertSee('Login Admin');

        $this->get(route('beranda'))->assertOk()->assertDontSee(url('/login'));
    }

    public function test_guests_get_not_found_on_the_admin_panel(): void
    {
        $this->get('/admin')->assertNotFound();
    }

    public function test_user_can_log_in_and_out(): void
    {
        $user = User::factory()->admin()->create();

        $this->post('/login', ['login' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get('/admin')->assertOk();
        $this->get('/login')->assertRedirect(route('admin.dashboard'));

        $this->post('/logout')->assertRedirect(route('beranda'));
        $this->assertGuest();
    }

    public function test_user_can_log_in_with_username(): void
    {
        $user = User::factory()->create(['name' => 'pengurus']);

        $this->post('/login', ['login' => 'pengurus', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->from('/login')
            ->post('/login', ['login' => $user->email, 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['login' => 'Email/username atau password salah.']);

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post('/login', ['login' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_login_is_locked_after_five_wrong_passwords(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $percobaan) {
            $this->post('/login', ['login' => $user->email, 'password' => 'salah']);
        }

        $this->post('/login', ['login' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['login' => 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.']);

        $this->assertGuest();
    }

    public function test_passwords_are_stored_as_salted_argon2id_hashes(): void
    {
        $pertama = User::factory()->create(['password' => 'Rahasia-123']);
        $kedua = User::factory()->create(['password' => 'Rahasia-123']);

        $this->assertSame('argon2id', password_get_info($pertama->password)['algoName']);
        $this->assertNotSame($pertama->password, $kedua->password);
        $this->assertStringNotContainsString('Rahasia-123', $pertama->password);
    }

    public function test_old_bcrypt_hash_is_upgraded_to_argon2id_on_login(): void
    {
        $user = User::factory()->create();
        // Hash lama sudah ada di database, jadi ditulis langsung tanpa cast "hashed".
        User::whereKey($user->id)->toBase()->update(['password' => password_hash('password', PASSWORD_BCRYPT)]);

        $this->post('/login', ['login' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $hashBaru = $user->fresh()->password;
        $this->assertSame('argon2id', password_get_info($hashBaru)['algoName']);
        $this->assertTrue(password_verify('password', $hashBaru));
    }

    public function test_open_session_of_a_deactivated_account_is_ended(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin')->assertOk();

        $user->update(['is_active' => false]);

        $this->get('/admin')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['login' => 'Akun ini sudah dinonaktifkan. Hubungi admin.']);
        $this->assertGuest();
    }
}
