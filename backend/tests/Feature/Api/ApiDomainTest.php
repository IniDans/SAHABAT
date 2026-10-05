<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Env;
use Tests\TestCase;

class ApiDomainTest extends TestCase
{
    protected function setUp(): void
    {
        $this->setApiDomain('api.sahabat.test');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->setApiDomain(null);
    }

    public function test_api_is_served_from_the_configured_subdomain(): void
    {
        $this->getJson('http://api.sahabat.test/auth/me')->assertUnauthorized();
        $this->postJson('http://api.sahabat.test/auth/login')->assertUnprocessable();
    }

    public function test_api_is_not_served_from_the_main_domain(): void
    {
        $this->getJson('http://sahabat.test/auth/me')->assertNotFound();
        $this->getJson('http://sahabat.test/api/auth/me')->assertNotFound();
    }

    public function test_errors_on_the_api_subdomain_are_json(): void
    {
        $this->get('http://api.sahabat.test/tidak-ada')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/json');
    }

    private function setApiDomain(?string $domain): void
    {
        // Repository dotenv bersifat statis dan boleh menimpa variabel yang pernah ia
        // muat sendiri dari .env (mis. API_DOMAIN= kosong), jadi di-reset dulu.
        Env::enablePutenv();

        if ($domain === null) {
            unset($_ENV['API_DOMAIN'], $_SERVER['API_DOMAIN']);
            putenv('API_DOMAIN');

            return;
        }

        $_ENV['API_DOMAIN'] = $_SERVER['API_DOMAIN'] = $domain;
        putenv("API_DOMAIN={$domain}");
    }
}
