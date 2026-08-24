<?php

namespace Tests\Unit;

use App\Models\Facility;
use App\Models\MaintenanceOrder;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MaintenanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Resource $resource;
    private User $supervisor;
    private MaintenanceService $maintenanceService;

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
            'name'        => 'Theatre A',
            'status'      => 'active',
            'capacity'    => 100,
        ]);

        $this->supervisor = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'supervisor',
        ]);

        $this->maintenanceService = app(MaintenanceService::class);
    }

    public function test_maintenance_order_lifecycle(): void
    {
        // 1. Create scheduled maintenance
        $order = $this->maintenanceService->create([
            'resource_id'  => $this->resource->id,
            'assigned_to'  => $this->supervisor->id,
            'type'         => 'corrective',
            'scheduled_at' => Carbon::now()->addDay()->setTime(9, 0),
            'notes'        => 'Broken seats',
        ]);

        $this->assertInstanceOf(MaintenanceOrder::class, $order);
        $this->assertEquals('scheduled', $order->status);
        $this->assertEquals('maintenance', $this->resource->fresh()->status); // Resource blocked!

        // 2. Transition: Start Work
        $order = $this->maintenanceService->startWork($order);
        $this->assertEquals('in_progress', $order->status);
        $this->assertEquals('maintenance', $this->resource->fresh()->status);

        // 3. Transition: Complete Work
        $order = $this->maintenanceService->complete($order, 'Fixed 5 chairs.', 120.50);
        $this->assertEquals('completed', $order->status);
        $this->assertEquals('active', $this->resource->fresh()->status); // Resource restored!
        $this->assertEquals(120.50, $order->cost);
    }
}
