<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\PriorityConfig;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PriorityEngine;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriorityEngineTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Resource $resource;
    private PriorityEngine $priorityEngine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'      => 'Test University',
            'subdomain' => 'university',
        ]);

        app()->instance(Tenant::class, $this->tenant);

        // Seed default config factors weights
        PriorityConfig::create(['tenant_id' => $this->tenant->id, 'factor' => 'role_admin',      'weight' => 100.00]);
        PriorityConfig::create(['tenant_id' => $this->tenant->id, 'factor' => 'role_supervisor', 'weight' => 50.00]);
        PriorityConfig::create(['tenant_id' => $this->tenant->id, 'factor' => 'role_end_user',   'weight' => 10.00]);
        PriorityConfig::create(['tenant_id' => $this->tenant->id, 'factor' => 'urgency_flag',    'weight' => 30.00]);
        PriorityConfig::create(['tenant_id' => $this->tenant->id, 'factor' => 'resource_demand', 'weight' => 5.00]); // 5.00 points per booking

        $facility = Facility::create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Science Block',
        ]);

        $category = ResourceCategory::create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Labs',
        ]);

        $this->resource = Resource::create([
            'tenant_id'   => $this->tenant->id,
            'facility_id' => $facility->id,
            'category_id' => $category->id,
            'name'        => 'Physics Lab A',
            'status'      => 'active',
            'capacity'    => 10,
        ]);

        $this->priorityEngine = app(PriorityEngine::class);
    }

    public function test_calculate_score_for_end_user_with_no_demand(): void
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'end_user',
        ]);

        $score = $this->priorityEngine->calculateScore($user, $this->resource->id, false);

        // Expected: 10.00 (role_end_user) + 0 (urgency) + 0 (demand)
        $this->assertEquals(10.00, $score);
    }

    public function test_calculate_score_includes_urgency_boost_and_demand_points(): void
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'supervisor',
        ]);

        // Seed 2 existing bookings in the last 30 days to check demand density calculations
        Booking::create([
            'tenant_id'      => $this->tenant->id,
            'user_id'        => $user->id,
            'resource_id'    => $this->resource->id,
            'start_at'       => Carbon::now()->subDays(5),
            'end_at'         => Carbon::now()->subDays(5)->addHours(2),
            'status'         => 'confirmed',
            'priority_score' => 50,
        ]);

        Booking::create([
            'tenant_id'      => $this->tenant->id,
            'user_id'        => $user->id,
            'resource_id'    => $this->resource->id,
            'start_at'       => Carbon::now()->subDays(10),
            'end_at'         => Carbon::now()->subDays(10)->addHours(2),
            'status'         => 'confirmed',
            'priority_score' => 50,
        ]);

        $score = $this->priorityEngine->calculateScore($user, $this->resource->id, true);

        // Expected: 50.00 (role_supervisor) + 30.00 (urgency) + (2 bookings * 5.00 weight) = 90.00
        $this->assertEquals(90.00, $score);
    }
}
