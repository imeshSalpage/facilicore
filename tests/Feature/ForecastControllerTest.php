<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Resource $resource;
    private User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'      => 'Eastbridge University',
            'subdomain' => 'university',
            'sector'    => 'university',
        ]);

        $facility = Facility::create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Science Block',
        ]);

        $category = ResourceCategory::create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Lecture Halls',
        ]);

        $this->resource = Resource::create([
            'tenant_id'   => $this->tenant->id,
            'facility_id' => $facility->id,
            'category_id' => $category->id,
            'name'        => 'Theatre A',
            'status'      => 'active',
            'capacity'    => 100,
        ]);

        $this->supervisor = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'supervisor',
        ]);
    }

    public function test_get_resource_occupancy_forecast_details(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->get("http://university.localhost/api/forecast/resource/{$this->resource->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'resource' => ['id', 'name', 'category', 'location'],
            'forecast' => ['mode', 'predictions', 'peak_hours', 'confidence'],
        ]);
    }
}
