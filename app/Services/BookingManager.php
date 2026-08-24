<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * BookingManager — SRP-compliant service for all booking business logic.
 * Handles conflict detection, availability checking, and alternative slot suggestions.
 */
class BookingManager
{
    /**
     * Check if a resource has a conflicting booking in the given time window.
     * Overlap condition: existing.start_at < new.end_at AND existing.end_at > new.start_at
     */
    public function hasConflict(
        int $resourceId,
        Carbon $startAt,
        Carbon $endAt,
        ?int $excludeBookingId = null
    ): bool {
        return $this->getConflicts($resourceId, $startAt, $endAt, $excludeBookingId)->isNotEmpty();
    }

    /**
     * Return all conflicting bookings for a resource in the given window.
     */
    public function getConflicts(
        int $resourceId,
        Carbon $startAt,
        Carbon $endAt,
        ?int $excludeBookingId = null
    ): Collection {
        $query = Booking::where('resource_id', $resourceId)
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt);

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->with('user')->get();
    }

    /**
     * Return booked slots for a resource within a date range (for calendar rendering).
     */
    public function getBookedSlots(int $resourceId, Carbon $from, Carbon $to): Collection
    {
        return Booking::where('resource_id', $resourceId)
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('start_at', '<', $to)
            ->where('end_at', '>', $from)
            ->with('user:id,name')
            ->get(['id', 'start_at', 'end_at', 'status', 'user_id']);
    }

    /**
     * Suggest the next N available time slots of the given duration,
     * starting from the requested start time.
     */
    public function suggestAlternatives(
        int $resourceId,
        Carbon $preferredStart,
        int $durationMinutes,
        int $count = 3
    ): array {
        $suggestions = [];
        $cursor = $preferredStart->copy()->addMinutes($durationMinutes); // Start probing after the requested slot
        $maxProbe = $preferredStart->copy()->addDays(7); // Look within next 7 days

        while (count($suggestions) < $count && $cursor->lt($maxProbe)) {
            $end = $cursor->copy()->addMinutes($durationMinutes);

            if (!$this->hasConflict($resourceId, $cursor, $end)) {
                $suggestions[] = [
                    'start_at' => $cursor->toIso8601String(),
                    'end_at'   => $end->toIso8601String(),
                ];
            }

            $cursor->addMinutes(30); // Probe in 30-minute increments
        }

        return $suggestions;
    }

    /**
     * Check whether the resource is in an active/bookable state.
     */
    public function isResourceBookable(int $resourceId): bool
    {
        $resource = Resource::find($resourceId);
        return $resource && $resource->status === 'active';
    }
}
