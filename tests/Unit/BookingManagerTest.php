<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BookingManager;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingManagerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Resource $resource;
    private BookingManager $bookingManager;

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

        $this->bookingManager = app(BookingManager::class);
    }

    public function test_resource_is_bookable_when_active(): void
    {
        $this->assertTrue($this->bookingManager->isResourceBookable($this->resource->id));

        $this->resource->update(['status' => 'maintenance']);
        $this->assertFalse($this->bookingManager->isResourceBookable($this->resource->id));
    }

    public function test_detects_booking_conflict(): void
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        Booking::create([
            'tenant_id'      => $this->tenant->id,
            'user_id'        => $user->id,
            'resource_id'    => $this->resource->id,
            'start_at'       => Carbon::now()->addHours(2),
            'end_at'         => Carbon::now()->addHours(4),
            'status'         => 'confirmed',
            'priority_score' => 10,
        ]);

        // Overlapping slot
        $this->assertTrue($this->bookingManager->hasConflict(
            $this->resource->id,
            Carbon::now()->addHours(3),
            Carbon::now()->addHours(5)
        ));

        // Non-overlapping slot
        $this->assertFalse($this->bookingManager->hasConflict(
            $this->resource->id,
            Carbon::now()->addHours(5),
            Carbon::now()->addHours(6)
        ));
    }
}
