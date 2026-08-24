<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ForecastService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastServiceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Resource $resource;
    private ForecastService $forecastService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'      => 'Test University',
            'subdomain' => 'university',
        ]);

        app()->instance(Tenant::class, $this->tenant);

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
            'name'        => 'Theatre 1',
            'status'      => 'active',
            'capacity'    => 100,
        ]);

        $this->forecastService = app(ForecastService::class);
    }

    public function test_forecast_cold_start_fallback(): void
    {
        $forecast = $this->forecastService->getForecastForResource($this->resource);

        $this->assertEquals('cold_start_fallback', $forecast['mode']);
        $this->assertCount(7, $forecast['predictions']);
        $this->assertNotEmpty($forecast['peak_hours']);
    }

    public function test_forecast_dynamic_regression_when_data_exists(): void
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        // Seed 6 bookings to exceed cold-start threshold of 5
        for ($i = 0; $i < 6; $i++) {
            Booking::create([
                'tenant_id'      => $this->tenant->id,
                'user_id'        => $user->id,
                'resource_id'    => $this->resource->id,
                'start_at'       => Carbon::now()->subDays($i + 1)->setTime(10, 0),
                'end_at'         => Carbon::now()->subDays($i + 1)->setTime(12, 0),
                'status'         => 'confirmed',
                'priority_score' => 10,
            ]);
        }

        $forecast = $this->forecastService->getForecastForResource($this->resource);

        $this->assertEquals('machine_learning', $forecast['mode']);
        $this->assertStringContainsString('High', $forecast['confidence']);
        $this->assertCount(7, $forecast['predictions']);
    }
}
