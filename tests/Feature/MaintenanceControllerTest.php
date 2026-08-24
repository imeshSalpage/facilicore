<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\MaintenanceOrder;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Resource $resource;
    private User $admin;
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

        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'admin',
        ]);

        $this->supervisor = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'supervisor',
        ]);
    }

    public function test_admin_can_schedule_maintenance(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('http://university.localhost/api/maintenance', [
                'resource_id'  => $this->resource->id,
                'assigned_to'  => $this->supervisor->id,
                'type'         => 'preventive',
                'scheduled_at' => Carbon::now()->addDay()->setTime(9, 0)->toDateTimeString(),
                'notes'        => 'Routine filter replacement',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('maintenance_orders', [
            'resource_id' => $this->resource->id,
            'notes'       => 'Routine filter replacement',
        ]);
        $this->assertEquals('maintenance', $this->resource->fresh()->status);
    }

    public function test_complete_work_order(): void
    {
        $order = MaintenanceOrder::create([
            'tenant_id'    => $this->tenant->id,
            'resource_id'  => $this->resource->id,
            'assigned_to'  => $this->supervisor->id,
            'status'       => 'in_progress',
            'type'         => 'corrective',
            'scheduled_at' => Carbon::now()->subDay(),
        ]);

        $this->resource->update(['status' => 'maintenance']);

        $response = $this->actingAs($this->admin)
            ->patch("http://university.localhost/api/maintenance/{$order->id}/complete", [
                'notes' => 'Chairs repaired',
                'cost'  => 50.00,
            ]);

        $response->assertStatus(200);
        $this->assertEquals('completed', $order->fresh()->status);
        $this->assertEquals('active', $this->resource->fresh()->status); // Restored!
    }
}
