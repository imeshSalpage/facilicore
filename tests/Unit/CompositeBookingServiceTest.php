<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\CompositeBooking;
use App\Models\Facility;
use App\Models\PriorityConfig;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CompositeBookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompositeBookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Resource $res1;
    private Resource $res2;
    private User $user;
    private CompositeBookingService $compositeService;

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

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'end_user',
        ]);

        $this->compositeService = app(CompositeBookingService::class);
    }

    public function test_create_atomic_booking_success(): void
    {
        $start = Carbon::now()->addDay()->setTime(10, 0);
        $end   = $start->copy()->addHours(2);

        $composite = $this->compositeService->create([
            'resource_ids' => [$this->res1->id, $this->res2->id],
            'start_at'     => $start->toDateTimeString(),
            'end_at'       => $end->toDateTimeString(),
            'notes'        => 'Joint Seminar',
            'urgency'      => false,
        ], $this->user);

        $this->assertInstanceOf(CompositeBooking::class, $composite);
        $this->assertEquals(2, $composite->bookings()->count());
        $this->assertEquals('confirmed', $composite->status);
    }

    public function test_create_atomic_booking_fails_on_conflict_and_rolls_back(): void
    {
        $start = Carbon::now()->addDay()->setTime(10, 0);
        $end   = $start->copy()->addHours(2);

        // Pre-book res2 for the same slot
        Booking::create([
            'tenant_id'      => $this->tenant->id,
            'user_id'        => $this->user->id,
            'resource_id'    => $this->res2->id,
            'start_at'       => $start,
            'end_at'         => $end,
            'status'         => 'confirmed',
            'priority_score' => 999, // Un-overridable high priority
        ]);

        $this->expectException(\Exception::class);

        try {
            $this->compositeService->create([
                'resource_ids' => [$this->res1->id, $this->res2->id],
                'start_at'     => $start->toDateTimeString(),
                'end_at'       => $end->toDateTimeString(),
                'notes'        => 'Joint Seminar',
            ], $this->user);
        } finally {
            // Assert that res1 was NOT booked because of atomic rollback safety!
            $res1Bookings = Booking::where('resource_id', $this->res1->id)
                ->where('start_at', $start)
                ->count();
            $this->assertEquals(0, $res1Bookings);
        }
    }

    public function test_suggests_alternatives_when_conflicted(): void
    {
        $start = Carbon::now()->addDay()->setTime(10, 0);
        $end   = $start->copy()->addHours(2);

        // Conflict booking
        Booking::create([
            'tenant_id'      => $this->tenant->id,
            'user_id'        => $this->user->id,
            'resource_id'    => $this->res2->id,
            'start_at'       => $start,
            'end_at'         => $end,
            'status'         => 'confirmed',
            'priority_score' => 999,
        ]);

        $alternatives = $this->compositeService->suggestAlternatives(
            [$this->res1->id, $this->res2->id],
            $start,
            120 // 120 minutes duration
        );

        $this->assertNotEmpty($alternatives);
        // None of the alternatives should conflict
        foreach ($alternatives as $alt) {
            $altStart = Carbon::parse($alt['start_at']);
            $altEnd   = Carbon::parse($alt['end_at']);

            $this->assertFalse(Booking::where('resource_id', $this->res2->id)
                ->where('status', 'confirmed')
                ->where('start_at', '<', $altEnd)
                ->where('end_at', '>', $altStart)
                ->exists()
            );
        }
    }
}
