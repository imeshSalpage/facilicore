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

    public function test_new_tenant_users_register_with_pending_approval(): void
    {
        $response = $this->post('http://university.localhost/api/register', [
            'name'                  => 'John Student',
            'email'                 => 'student@example.com',
            'registration_number'   => 'STU-2026-001',
            'department'            => 'Computer Science',
            'phone'                 => '+1 555-0199',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'pending_approval')
            ->assertJsonPath('user.registration_number', 'STU-2026-001')
            ->assertJsonPath('user.department', 'Computer Science')
            ->assertJsonPath('user.approval_status', 'pending');

        $this->assertGuest();

        $this->assertDatabaseHas('users', [
            'email'               => 'student@example.com',
            'registration_number' => 'STU-2026-001',
            'department'          => 'Computer Science',
            'approval_status'     => 'pending',
            'role'                => 'end_user',
        ]);
    }

    public function test_central_registration_creates_approved_admin(): void
    {
        $response = $this->post('http://localhost/api/register', [
            'company_name'          => 'Apex Health',
            'subdomain'             => 'apexhealth',
            'sector'                => 'healthcare',
            'name'                  => 'Dr. Alice Apex',
            'email'                 => 'admin@apexhealth.org',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('subdomain', 'apexhealth');

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email'           => 'admin@apexhealth.org',
            'role'            => 'admin',
            'approval_status' => 'approved',
        ]);
    }
}
