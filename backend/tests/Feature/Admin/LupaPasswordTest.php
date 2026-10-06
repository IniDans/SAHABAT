<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class LupaPasswordTest extends TestCase
{
    use RefreshDatabase;

    private const PESAN_TERKIRIM = 'Jika email tersebut terdaftar, tautan reset password sudah kami kirim. Periksa kotak masuk atau folder spam.';

    public function test_reset_link_is_sent_to_a_registered_email(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->get(route('password.request'))->assertOk();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status', self::PESAN_TERKIRIM);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_gets_the_same_message_without_a_link(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'tidak-ada@example.com'])
            ->assertSessionHas('status', self::PESAN_TERKIRIM);

        Notification::assertNothingSent();
    }

    public function test_deactivated_account_gets_no_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->inactive()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status', self::PESAN_TERKIRIM);

        Notification::assertNothingSent();
    }

    public function test_valid_token_sets_a_new_password_and_signs_out_other_devices(): void
    {
        $user = User::factory()->create();
        $user->createToken('android');
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))->assertOk();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Password-Baru-1',
            'password_confirmation' => 'Password-Baru-1',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Password berhasil diubah. Silakan login dengan password baru.');

        $user->refresh();
        $this->assertTrue(password_verify('Password-Baru-1', $user->password));
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->from(route('password.reset', ['token' => 'palsu']))
            ->post(route('password.update'), [
                'token' => 'palsu',
                'email' => $user->email,
                'password' => 'Password-Baru-1',
                'password_confirmation' => 'Password-Baru-1',
            ])
            ->assertSessionHasErrors(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.']);

        $this->assertTrue(password_verify('password', $user->fresh()->password));
    }

    public function test_weak_new_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.update'), [
            'token' => Password::createToken($user),
            'email' => $user->email,
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(password_verify('password', $user->fresh()->password));
    }
}
