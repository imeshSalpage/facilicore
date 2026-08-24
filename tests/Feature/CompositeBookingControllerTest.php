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

class CompositeBookingControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Resource $res1;
    private Resource $res2;
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

        $this->res1 = Resource::create([
            'tenant_id'   => $this->tenant->id,
            'facility_id' => $facility->id,
            'category_id' => $category->id,
            'name'        => 'Physics Lab A',
            'status'      => 'active',
            'capacity'    => 10,
        ]);

        $this->res2 = Resource::create([
            'tenant_id'   => $this->tenant->id,
            'facility_id' => $facility->id,
            'category_id' => $category->id,
            'name'        => 'Chemistry Lab B',
            'status'      => 'active',
            'capacity'    => 10,
        ]);

        $this->student = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'end_user',
        ]);
    }

    public function test_create_composite_booking_successfully(): void
    {
        $start = Carbon::now()->addDay()->setTime(10, 0);
        $end   = $start->copy()->addHours(2);

        $response = $this->actingAs($this->student)
            ->post('http://university.localhost/api/composite-bookings', [
                'resource_ids' => [$this->res1->id, $this->res2->id],
                'start_at'     => $start->toDateTimeString(),
                'end_at'       => $end->toDateTimeString(),
                'notes'        => 'Joint Lab Workshop',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'status',
            'bookings',
        ]);
    }

    public function test_create_composite_booking_returns_409_and_suggestions_on_conflict(): void
    {
        $start = Carbon::now()->addDay()->setTime(10, 0);
        $end   = $start->copy()->addHours(2);

        // Pre-book res1 with un-overridable high priority
        Booking::create([
            'tenant_id'      => $this->tenant->id,
            'user_id'        => $this->student->id,
            'resource_id'    => $this->res1->id,
            'start_at'       => $start,
            'end_at'         => $end,
            'status'         => 'confirmed',
            'priority_score' => 999,
        ]);

        $response = $this->actingAs($this->student)
            ->post('http://university.localhost/api/composite-bookings', [
                'resource_ids' => [$this->res1->id, $this->res2->id],
                'start_at'     => $start->toDateTimeString(),
                'end_at'       => $end->toDateTimeString(),
                'notes'        => 'Joint Lab Workshop',
            ]);

        $response->assertStatus(409);
        $response->assertJsonStructure([
            'message',
            'alternatives',
        ]);
        $this->assertNotEmpty($response->json('alternatives'));
    }
}
