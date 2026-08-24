<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\PriorityConfig;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Resource $resource;
    private User $admin;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'      => 'Eastbridge University',
            'subdomain' => 'university',
            'sector'    => 'university',
        ]);

        // Seed default config factors weights
        PriorityConfig::create(['tenant_id' => $this->tenant->id, 'factor' => 'role_admin',      'weight' => 100.00]);
        PriorityConfig::create(['tenant_id' => $this->tenant->id, 'factor' => 'role_supervisor', 'weight' => 50.00]);
        PriorityConfig::create(['tenant_id' => $this->tenant->id, 'factor' => 'role_end_user',   'weight' => 10.00]);
        PriorityConfig::create(['tenant_id' => $this->tenant->id, 'factor' => 'urgency_flag',    'weight' => 30.00]);
        PriorityConfig::create(['tenant_id' => $this->tenant->id, 'factor' => 'resource_demand', 'weight' => 2.00]);

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

        $this->student = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'end_user',
        ]);
    }

    public function test_list_bookings(): void
    {
        $response = $this->actingAs($this->student)
            ->get('http://university.localhost/api/bookings');

        $response->assertStatus(200);
    }

    public function test_create_booking_without_conflict(): void
    {
        $response = $this->actingAs($this->student)
            ->post('http://university.localhost/api/bookings', [
                'resource_id' => $this->resource->id,
                'start_at'    => Carbon::now()->addHours(2)->toDateTimeString(),
                'end_at'      => Carbon::now()->addHours(4)->toDateTimeString(),
                'notes'       => 'Study session',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bookings', [
            'resource_id' => $this->resource->id,
            'notes'       => 'Study session',
        ]);
    }

    public function test_priority_override_of_conflicting_booking(): void
    {
        $start = Carbon::now()->addHours(2);
        $end   = Carbon::now()->addHours(4);

        // 1. Create a low-priority booking for the student (score = 10)
        $studentBooking = Booking::create([
            'tenant_id'      => $this->tenant->id,
            'user_id'        => $this->student->id,
            'resource_id'    => $this->resource->id,
            'start_at'       => $start,
            'end_at'         => $end,
            'status'         => 'confirmed',
            'priority_score' => 10,
        ]);

        // 2. High-priority admin requests the same slot (score = 100)
        $response = $this->actingAs($this->admin)
            ->post('http://university.localhost/api/bookings', [
                'resource_id' => $this->resource->id,
                'start_at'    => $start->toDateTimeString(),
                'end_at'      => $end->toDateTimeString(),
                'notes'       => 'Emergency Lab inspection',
                'urgency'     => false,
            ]);

        $response->assertStatus(201);

        // Verify low-priority student booking is cancelled, and admin booking is confirmed!
        $this->assertEquals('cancelled', $studentBooking->fresh()->status);
        $this->assertDatabaseHas('bookings', [
            'user_id'     => $this->admin->id,
            'resource_id' => $this->resource->id,
            'status'      => 'confirmed',
        ]);
    }
}
