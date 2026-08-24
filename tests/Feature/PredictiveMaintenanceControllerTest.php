<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictiveMaintenanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'      => 'Eastbridge University',
            'subdomain' => 'university',
            'sector'    => 'university',
        ]);

        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'admin',
        ]);
    }

    public function test_run_predictive_anomaly_scan(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('http://university.localhost/api/predictive-maintenance/scan');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'flagged',
        ]);
    }
}
