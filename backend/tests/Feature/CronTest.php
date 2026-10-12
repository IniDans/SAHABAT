<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CronTest extends TestCase
{
    use RefreshDatabase;

    public function test_cron_prunes_expired_tokens_with_the_right_secret(): void
    {
        config(['services.cron.secret' => 'rahasia-cron']);

        $user = User::factory()->create();
        $user->createToken('lama', expiresAt: now()->subDays(2));
        $user->createToken('aktif', expiresAt: now()->addDay());

        $this->withHeader('Authorization', 'Bearer rahasia-cron')
            ->get(route('cron.bersihkan'))
            ->assertNoContent();

        $this->assertSame(['aktif'], $user->tokens()->pluck('name')->all());
    }

    public function test_cron_is_hidden_without_the_right_secret(): void
    {
        config(['services.cron.secret' => 'rahasia-cron']);

        $this->get(route('cron.bersihkan'))->assertNotFound();
        $this->withHeader('Authorization', 'Bearer salah')->get(route('cron.bersihkan'))->assertNotFound();
    }

    public function test_cron_is_hidden_when_no_secret_is_configured(): void
    {
        config(['services.cron.secret' => null]);

        $this->withHeader('Authorization', 'Bearer ')->get(route('cron.bersihkan'))->assertNotFound();
    }
}
