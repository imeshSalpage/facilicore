<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectorConfigControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_university_terminology(): void
    {
        $tenant = Tenant::create([
            'name'      => 'Eastbridge University',
            'subdomain' => 'university',
            'sector'    => 'university',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role'      => 'end_user',
        ]);

        $response = $this->actingAs($user)
            ->get('http://university.localhost/api/sector/config');

        $response->assertStatus(200);
        $this->assertEquals('Campus Buildings', $response->json('terminology.facilities'));
        $this->assertEquals('Student', $response->json('terminology.end_user'));
    }

    public function test_get_healthcare_terminology(): void
    {
        $tenant = Tenant::create([
            'name'      => 'St. Jude Hospital',
            'subdomain' => 'hospital',
            'sector'    => 'healthcare',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role'      => 'end_user',
        ]);

        $response = $this->actingAs($user)
            ->get('http://hospital.localhost/api/sector/config');

        $response->assertStatus(200);
        $this->assertEquals('Clinics & Wards', $response->json('terminology.facilities'));
        $this->assertEquals('Nurse / Practitioner', $response->json('terminology.end_user'));
    }
}
