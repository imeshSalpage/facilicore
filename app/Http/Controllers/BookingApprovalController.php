<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Notifications\BookingNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * BookingApprovalController — Supervisor-level booking approval and rejection with notifications.
 * Routes protected by role:supervisor middleware.
 */
class BookingApprovalController extends Controller
{
    /**
     * List all pending bookings that need supervisor review.
     */
    public function pending(): JsonResponse
    {
        $bookings = Booking::with(['resource.category', 'resource.facility', 'user'])
            ->where('status', 'pending')
            ->orderBy('priority')
            ->orderBy('start_at')
            ->get();

        return response()->json($bookings);
    }

    /**
     * Approve a pending booking — changes status to confirmed and sends notification.
     */
    public function approve(Booking $booking): JsonResponse
    {
        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Only pending bookings can be approved.'], 422);
        }

        $booking->update(['status' => 'confirmed']);

        // Send approval notice
        $booking->user->notify(new BookingNotification($booking, 'confirmed'));

        return response()->json($booking->load(['resource.category', 'resource.facility', 'user']));
    }

    /**
     * Reject a pending or confirmed booking with an optional reason and sends notification.
     */
    public function reject(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking is already cancelled.'], 422);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $booking->update([
            'status' => 'cancelled',
            'notes'  => $validated['reason'] ?? $booking->notes,
        ]);

        // Send rejection notice
        $booking->user->notify(new BookingNotification($booking, 'cancelled', $validated['reason'] ?? null));

        return response()->json($booking->load(['resource.category', 'resource.facility', 'user']));
    }
}
