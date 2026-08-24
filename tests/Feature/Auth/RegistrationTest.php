<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tenant = Tenant::create([
            'name'      => 'Eastbridge University',
            'subdomain' => 'university',
            'sector'    => 'university',
        ]);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('http://university.localhost/api/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertNoContent();
    }
}
