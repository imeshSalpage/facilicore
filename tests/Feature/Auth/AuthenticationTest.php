<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
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

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->post('http://university.localhost/api/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertNoContent();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->post('http://university.localhost/api/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($user)->post('http://university.localhost/api/logout');

        $this->assertGuest();
        $response->assertNoContent();
    }

    public function test_pending_users_cannot_authenticate(): void
    {
        $pendingUser = User::factory()->pending()->create([
            'tenant_id' => $this->tenant->id,
            'password'  => bcrypt('password'),
        ]);

        $response = $this->post('http://university.localhost/api/login', [
            'email'    => $pendingUser->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_rejected_users_cannot_authenticate(): void
    {
        $rejectedUser = User::factory()->rejected('Invalid identification')->create([
            'tenant_id' => $this->tenant->id,
            'password'  => bcrypt('password'),
        ]);

        $response = $this->post('http://university.localhost/api/login', [
            'email'    => $rejectedUser->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
