<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'      => 'Eastbridge University',
            'subdomain' => 'university',
            'sector'    => 'university',
        ]);

        $this->supervisor = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'supervisor',
        ]);
    }

    public function test_get_analytics_report_denied_for_student(): void
    {
        $student = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'end_user',
        ]);

        $response = $this->actingAs($student)
            ->get('http://university.localhost/api/analytics/report');

        $response->assertStatus(403);
    }

    public function test_get_analytics_report_allowed_for_supervisor(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->get('http://university.localhost/api/analytics/report');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'summary' => [
                'total_bookings',
                'confirmed_count',
                'cancelled_count',
                'total_resources',
                'under_maintenance',
                'utilization_rate',
            ],
            'charts' => [
                'booking_volume',
                'resource_bookings',
                'hourly_distribution',
                'category_breakdown',
            ],
        ]);
    }
}
