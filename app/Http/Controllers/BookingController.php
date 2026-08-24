<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tenant;
use App\Notifications\BookingNotification;
use App\Services\BookingManager;
use App\Services\PriorityEngine;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingManager $bookingManager,
        private readonly PriorityEngine $priorityEngine
    ) {
    }

    /**
     * List bookings. Admins/supervisors see all tenant bookings;
     * end users see only their own.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with(['resource.category', 'resource.facility', 'user'])
            ->orderBy('start_at', 'desc');

        if ($request->user()->role === 'end_user') {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json($query->get());
    }

    /**
     * Retrieve booked slots for a resource within a date range (for calendar).
     * GET /api/bookings/slots?resource_id=1&from=2026-08-01&to=2026-08-31
     */
    public function slots(Request $request): JsonResponse
    {
        $request->validate([
            'resource_id' => 'required|integer|exists:resources,id',
            'from'        => 'required|date',
            'to'          => 'required|date|after:from',
        ]);

        $slots = $this->bookingManager->getBookedSlots(
            (int) $request->resource_id,
            Carbon::parse($request->from),
            Carbon::parse($request->to)
        );

        return response()->json($slots);
    }

    /**
     * Create a new booking with conflict detection and priority-aware overrides.
     */
    public function store(Request $request): JsonResponse
    {
        if (!app()->bound(Tenant::class)) {
            abort(403, 'A tenant context is required.');
        }

        $validated = $request->validate([
            'resource_id' => 'required|integer|exists:resources,id',
            'start_at'    => 'required|date|after:now',
            'end_at'      => 'required|date|after:start_at',
            'notes'       => 'nullable|string|max:1000',
            'priority'    => 'nullable|integer|min:1|max:10',
            'urgency'     => 'nullable|boolean',
        ]);

        $startAt = Carbon::parse($validated['start_at']);
        $endAt   = Carbon::parse($validated['end_at']);
        $urgency = (bool) ($validated['urgency'] ?? false);

        // Reject if resource is not in active state
        if (!$this->bookingManager->isResourceBookable((int) $validated['resource_id'])) {
            return response()->json([
                'message' => 'This resource is not available for booking (maintenance or retired).',
            ], 422);
        }

        // Calculate priority score
        $priorityScore = $this->priorityEngine->calculateScore(
            $request->user(),
            (int) $validated['resource_id'],
            $urgency
        );

        // Get conflicting bookings
        $conflicts = $this->bookingManager->getConflicts((int) $validated['resource_id'], $startAt, $endAt);

        if ($conflicts->isNotEmpty()) {
            $tempBooking = new Booking([
                'resource_id'    => $validated['resource_id'],
                'user_id'        => $request->user()->id,
                'priority_score' => $priorityScore,
            ]);

            // Attempt priority override
            if ($this->priorityEngine->resolveConflict($tempBooking, $conflicts)) {
                // Succeeded in overriding lower priority bookings!
            } else {
                $alternatives = $this->bookingManager->suggestAlternatives(
                    (int) $validated['resource_id'],
                    $startAt,
                    (int) $startAt->diffInMinutes($endAt)
                );

                return response()->json([
                    'message'      => 'This time slot conflicts with an existing booking.',
                    'alternatives' => $alternatives,
                ], 409);
            }
        }

        // Set status. Regular users requests are confirmed directly unless supervisor approval is added.
        // For university context, let's auto-confirm but keep the path open.
        $status = 'confirmed';

        $booking = Booking::create([
            'tenant_id'      => app(Tenant::class)->id,
            'resource_id'    => $validated['resource_id'],
            'user_id'        => $request->user()->id,
            'start_at'       => $startAt,
            'end_at'         => $endAt,
            'status'         => $status,
            'notes'          => $validated['notes'] ?? null,
            'priority'       => $validated['priority'] ?? 5,
            'urgency'        => $urgency,
            'priority_score' => $priorityScore,
        ]);

        // Send confirmation notification
        $booking->user->notify(new BookingNotification($booking, $status));

        return response()->json($booking->load(['resource.category', 'resource.facility', 'user']), 201);
    }

    /**
     * Show a single booking.
     */
    public function show(Booking $booking): JsonResponse
    {
        return response()->json($booking->load(['resource.category', 'resource.facility', 'user']));
    }

    /**
     * Update a booking (change time window or notes). Re-validates conflicts and scores.
     */
    public function update(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'start_at' => 'required|date',
            'end_at'   => 'required|date|after:start_at',
            'notes'    => 'nullable|string|max:1000',
            'priority' => 'nullable|integer|min:1|max:10',
            'urgency'  => 'nullable|boolean',
        ]);

        $startAt = Carbon::parse($validated['start_at']);
        $endAt   = Carbon::parse($validated['end_at']);
        $urgency = (bool) ($validated['urgency'] ?? $booking->urgency);

        // Recalculate priority score
        $priorityScore = $this->priorityEngine->calculateScore(
            $request->user(),
            (int) $booking->resource_id,
            $urgency
        );

        $conflicts = $this->bookingManager->getConflicts((int) $booking->resource_id, $startAt, $endAt, $booking->id);

        if ($conflicts->isNotEmpty()) {
            $tempBooking = new Booking([
                'id'             => $booking->id,
                'resource_id'    => $booking->resource_id,
                'user_id'        => $request->user()->id,
                'priority_score' => $priorityScore,
            ]);

            // Attempt priority override
            if ($this->priorityEngine->resolveConflict($tempBooking, $conflicts)) {
                // Succeeded in overriding lower priority bookings!
            } else {
                $alternatives = $this->bookingManager->suggestAlternatives(
                    (int) $booking->resource_id,
                    $startAt,
                    (int) $startAt->diffInMinutes($endAt)
                );

                return response()->json([
                    'message'      => 'The new time slot conflicts with an existing booking.',
                    'alternatives' => $alternatives,
                ], 409);
            }
        }

        $booking->update([
            'start_at'       => $startAt,
            'end_at'         => $endAt,
            'notes'          => $validated['notes'] ?? $booking->notes,
            'priority'       => $validated['priority'] ?? $booking->priority,
            'urgency'        => $urgency,
            'priority_score' => $priorityScore,
        ]);

        // Send update notification
        $booking->user->notify(new BookingNotification($booking, 'confirmed'));

        return response()->json($booking->load(['resource.category', 'resource.facility', 'user']));
    }

    /**
     * Cancel a booking (soft status update, keeps the record).
     */
    public function cancel(Booking $booking, Request $request): JsonResponse
    {
        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking is already cancelled.'], 422);
        }

        $booking->update(['status' => 'cancelled']);

        // Send cancel notification
        $booking->user->notify(new BookingNotification($booking, 'cancelled', 'Cancelled by user request.'));

        return response()->json($booking->load(['resource.category', 'resource.facility', 'user']));
    }
}
