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

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get('/admin')->assertOk();
        $this->get('/login')->assertRedirect(route('admin.dashboard'));

        $this->post('/logout')->assertRedirect(route('beranda'));
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
