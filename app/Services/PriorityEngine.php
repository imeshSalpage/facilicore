<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\PriorityConfig;
use App\Models\Resource;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * PriorityEngine — SRP-compliant service for context-aware priority scoring and conflict overrides.
 */
class PriorityEngine
{
    public function __construct(private readonly AuditLogService $auditService)
    {
    }

    /**
     * Compute a priority score for a booking based on configurable weights and context.
     */
    public function calculateScore(User $user, int $resourceId, bool $urgency): float
    {
        // 1. Fetch factor configurations
        $configs = PriorityConfig::all()->pluck('weight', 'factor');

        // Role Weight
        $roleFactor = match ($user->role) {
            'admin'      => 'role_admin',
            'supervisor' => 'role_supervisor',
            default      => 'role_end_user',
        };
        $roleWeight = $configs->get($roleFactor) ?? match ($user->role) {
            'admin'      => 100.00,
            'supervisor' => 50.00,
            default      => 10.00,
        };

        // Urgency Weight
        $urgencyAddon = 0.00;
        if ($urgency) {
            $urgencyAddon = $configs->get('urgency_flag') ?? 30.00;
        }

        // Resource Demand Weight
        // Calculate dynamic demand based on bookings count for this resource in last 30 days
        $demandCount = Booking::where('resource_id', $resourceId)
            ->where('start_at', '>=', Carbon::now()->subDays(30))
            ->whereIn('status', ['confirmed', 'pending'])
            ->count();
        
        $demandWeightFactor = $configs->get('resource_demand') ?? 1.00;
        $demandAddon = $demandCount * $demandWeightFactor;

        // Final Priority Score calculation
        return (float) ($roleWeight + $urgencyAddon + $demandAddon);
    }

    /**
     * Resolve conflict using priority scores.
     * If the new booking has a higher score than ALL conflicting bookings:
     *   - Automatically cancels conflicting bookings.
     *   - Writes audit trail override logs.
     *   - Returns true (conflict resolved/overridden).
     * Otherwise, returns false.
     */
    public function resolveConflict(Booking $newBooking, Collection $conflictingBookings): bool
    {
        if ($conflictingBookings->isEmpty()) {
            return true;
        }

        // If new booking's score is <= ANY conflicting booking's score, it cannot override
        foreach ($conflictingBookings as $conflict) {
            if ($newBooking->priority_score <= $conflict->priority_score) {
                return false;
            }
        }

        // Override allowed! Automatically cancel lower-priority bookings
        foreach ($conflictingBookings as $conflict) {
            $conflict->update([
                'status' => 'cancelled',
                'notes'  => '[Auto-cancelled] Overridden by higher priority booking #' . $newBooking->id . '. ' . ($conflict->notes ?? '')
            ]);

            // Notify user of override
            $conflict->user->notify(new \App\Notifications\BookingNotification($conflict, 'overridden'));

            // Log the override action in the audit logs
            $this->auditService->log(
                'booking.overridden',
                Booking::class,
                $conflict->id,
                [
                    'overridden_by'   => $newBooking->id,
                    'previous_status' => $conflict->status,
                    'resource_id'     => $conflict->resource_id,
                ]
            );
        }

        return true;
    }
}
