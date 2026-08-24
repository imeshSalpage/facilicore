<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\MaintenanceOrder;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PredictiveMaintenanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictiveMaintenanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Resource $normalRes;
    private Resource $stressedRes;
    private PredictiveMaintenanceService $predictiveService;

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

        // Create a normal and a heavily loaded resource in the same category
        $this->normalRes = Resource::create([
            'tenant_id'   => $this->tenant->id,
            'facility_id' => $facility->id,
            'category_id' => $category->id,
            'name'        => 'Theatre A',
            'status'      => 'active',
            'capacity'    => 100,
        ]);

        // Add extra control assets in the same category to normalize mean/stddev calculations
        Resource::create([
            'tenant_id'   => $this->tenant->id,
            'facility_id' => $facility->id,
            'category_id' => $category->id,
            'name'        => 'Theatre C',
            'status'      => 'active',
            'capacity'    => 100,
        ]);

        Resource::create([
            'tenant_id'   => $this->tenant->id,
            'facility_id' => $facility->id,
            'category_id' => $category->id,
            'name'        => 'Theatre D',
            'status'      => 'active',
            'capacity'    => 100,
        ]);

        Resource::create([
            'tenant_id'   => $this->tenant->id,
            'facility_id' => $facility->id,
            'category_id' => $category->id,
            'name'        => 'Theatre E',
            'status'      => 'active',
            'capacity'    => 100,
        ]);

        $this->stressedRes = Resource::create([
            'tenant_id'   => $this->tenant->id,
            'facility_id' => $facility->id,
            'category_id' => $category->id,
            'name'        => 'Theatre B',
            'status'      => 'active',
            'capacity'    => 100,
        ]);

        $this->predictiveService = app(PredictiveMaintenanceService::class);
    }

    public function test_detects_stressed_resource_and_schedules_order(): void
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        // Create standard supervisor to act as technician
        User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'supervisor',
        ]);

        // Stressed Resource: Book 10 bookings of 5 hours each in the last 14 days (total 50 hours)
        for ($i = 0; $i < 10; $i++) {
            Booking::create([
                'tenant_id'      => $this->tenant->id,
                'user_id'        => $user->id,
                'resource_id'    => $this->stressedRes->id,
                'start_at'       => Carbon::now()->subDays($i)->setTime(8, 0),
                'end_at'         => Carbon::now()->subDays($i)->setTime(13, 0),
                'status'         => 'confirmed',
                'priority_score' => 10,
            ]);
        }

        // Normal Resource: Book only 1 booking of 2 hours
        Booking::create([
            'tenant_id'      => $this->tenant->id,
            'user_id'        => $user->id,
            'resource_id'    => $this->normalRes->id,
            'start_at'       => Carbon::now()->subDays(1)->setTime(10, 0),
            'end_at'         => Carbon::now()->subDays(1)->setTime(12, 0),
            'status'         => 'confirmed',
            'priority_score' => 10,
        ]);

        // Run scan
        $flagged = $this->predictiveService->runScan();

        $this->assertNotEmpty($flagged);
        $this->assertEquals($this->stressedRes->name, $flagged[0]['resource']);

        // Verify maintenance order is created and resource is blocked (status = maintenance)
        $order = MaintenanceOrder::where('resource_id', $this->stressedRes->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('scheduled', $order->status);
        $this->assertEquals('preventive', $order->type);

        $this->assertEquals('maintenance', $this->stressedRes->fresh()->status);
    }
}
