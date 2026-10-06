<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Env;
use Tests\TestCase;

class AdminDomainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        $this->setAdminDomain('admin.sahabat.test');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->setAdminDomain(null);
    }

    public function test_admin_panel_is_served_from_the_configured_subdomain(): void
    {
        $this->get('http://admin.sahabat.test/login')->assertOk();

        $this->actingAs(User::factory()->create())
            ->get('http://admin.sahabat.test/')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_admin_panel_is_not_served_from_the_main_domain(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('http://sahabat.test/admin')
            ->assertNotFound();

        $this->get('http://sahabat.test/login')->assertNotFound();
    }

    private function setAdminDomain(?string $domain): void
    {
        // Sama seperti ApiDomainTest: repository dotenv statis perlu ditimpa langsung.
        Env::enablePutenv();

        if ($domain === null) {
            unset($_ENV['ADMIN_DOMAIN'], $_SERVER['ADMIN_DOMAIN']);
            putenv('ADMIN_DOMAIN');

            return;
        }

        $_ENV['ADMIN_DOMAIN'] = $_SERVER['ADMIN_DOMAIN'] = $domain;
        putenv("ADMIN_DOMAIN={$domain}");
    }
}
