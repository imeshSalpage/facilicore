<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CompositeBooking;
use App\Models\Tenant;
use App\Services\BookingManager;
use App\Services\PriorityEngine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * CompositeBookingService — SRP-compliant service for transactional multi-resource bookings and alternative matching.
 */
class CompositeBookingService
{
    public function __construct(
        private readonly BookingManager $bookingManager,
        private readonly PriorityEngine $priorityEngine
    ) {
    }

    /**
     * Create multiple bookings atomically.
     * Returns the parent CompositeBooking if successful, or throws an exception on conflict.
     */
    public function create(array $data, object $user): CompositeBooking
    {
        $resourceIds = $data['resource_ids'];
        $startAt     = Carbon::parse($data['start_at']);
        $endAt       = Carbon::parse($data['end_at']);
        $notes       = $data['notes'] ?? null;
        $urgency     = (bool) ($data['urgency'] ?? false);

        return DB::transaction(function () use ($resourceIds, $startAt, $endAt, $notes, $urgency, $user) {
            $tenantId = app(Tenant::class)->id;

            // 1. Create parent record
            $composite = CompositeBooking::create([
                'tenant_id' => $tenantId,
                'user_id'   => $user->id,
                'start_at'  => $startAt,
                'end_at'    => $endAt,
                'notes'     => $notes,
                'status'    => 'confirmed',
            ]);

            // 2. Build individual bookings (with priority scoring and overrides check)
            foreach ($resourceIds as $resourceId) {
                // Reject if resource is not in active state
                if (!$this->bookingManager->isResourceBookable((int) $resourceId)) {
                    throw new \Exception("Resource #{$resourceId} is currently under maintenance or retired.");
                }

                // Calculate priority score for this user + resource combination
                $priorityScore = $this->priorityEngine->calculateScore($user, (int) $resourceId, $urgency);

                $conflicts = $this->bookingManager->getConflicts((int) $resourceId, $startAt, $endAt);

                if ($conflicts->isNotEmpty()) {
                    $tempBooking = new Booking([
                        'resource_id'    => $resourceId,
                        'user_id'        => $user->id,
                        'priority_score' => $priorityScore,
                    ]);

                    // Attempt priority override
                    if (!$this->priorityEngine->resolveConflict($tempBooking, $conflicts)) {
                        throw new \Exception("Time slot conflicts with an existing higher-priority booking on resource #{$resourceId}.");
                    }
                }

                // Create the child booking
                Booking::create([
                    'tenant_id'            => $tenantId,
                    'composite_booking_id' => $composite->id,
                    'resource_id'          => $resourceId,
                    'user_id'              => $user->id,
                    'start_at'             => $startAt,
                    'end_at'               => $endAt,
                    'status'               => 'confirmed',
                    'notes'                => $notes,
                    'priority'             => 5,
                    'urgency'              => $urgency,
                    'priority_score'       => $priorityScore,
                ]);
            }

            return $composite->load('bookings.resource');
        });
    }

    /**
     * Cancel an entire composite booking atomically.
     */
    public function cancel(CompositeBooking $composite): CompositeBooking
    {
        DB::transaction(function () use ($composite) {
            $composite->update(['status' => 'cancelled']);
            $composite->bookings()->update([
                'status' => 'cancelled',
                'notes'  => '[Auto-cancelled] Cancelled via parent composite booking #' . $composite->id
            ]);
        });

        return $composite->load('bookings.resource');
    }

    /**
     * Suggest alternative time slots where ALL requested resources are simultaneously free.
     */
    public function suggestAlternatives(array $resourceIds, Carbon $preferredStart, int $durationMinutes): array
    {
        $alternatives = [];
        $checkDate = $preferredStart->copy()->startOfDay();

        // Check slots over the next 7 days
        for ($day = 0; $day < 7; $day++) {
            $date = $checkDate->copy()->addDays($day);

            // Working hours: 08:00 to 18:00
            for ($hour = 8; $hour < 18; $hour++) {
                $start = $date->copy()->setTime($hour, 0);
                $end   = $start->copy()->addMinutes($durationMinutes);

                // Check if this slot is free for ALL resources
                $allAvailable = true;
                foreach ($resourceIds as $resourceId) {
                    if ($this->bookingManager->hasConflict((int) $resourceId, $start, $end)) {
                        $allAvailable = false;
                        break;
                    }
                }

                if ($allAvailable) {
                    $alternatives[] = [
                        'start_at' => $start->toIso8601String(),
                        'end_at'   => $end->toIso8601String(),
                    ];
                    
                    // Limit suggestions to 5 slots
                    if (count($alternatives) >= 5) {
                        return $alternatives;
                    }
                }
            }
        }

        return $alternatives;
    }
}
