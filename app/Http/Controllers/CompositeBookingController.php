<?php

namespace App\Http\Controllers;

use App\Models\CompositeBooking;
use App\Services\CompositeBookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompositeBookingController extends Controller
{
    public function __construct(private readonly CompositeBookingService $compositeService)
    {
    }

    /**
     * List composite bookings (role-scoped).
     */
    public function index(Request $request): JsonResponse
    {
        $query = CompositeBooking::with('bookings.resource.category')
            ->orderBy('start_at', 'desc');

        if ($request->user()->role === 'end_user') {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json($query->get());
    }

    /**
     * Create a new composite booking.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'resource_ids'   => 'required|array|min:1',
            'resource_ids.*' => 'required|integer|exists:resources,id',
            'start_at'       => 'required|date|after:now',
            'end_at'         => 'required|date|after:start_at',
            'notes'          => 'nullable|string|max:1000',
            'urgency'        => 'nullable|boolean',
        ]);

        $durationMinutes = Carbon::parse($validated['start_at'])
            ->diffInMinutes(Carbon::parse($validated['end_at']));

        try {
            $composite = $this->compositeService->create($validated, $request->user());
            
            return response()->json($composite, 201);
        } catch (\Exception $e) {
            // Suggest alternative slot options where ALL selected resources are simultaneously free
            $alternatives = $this->compositeService->suggestAlternatives(
                $validated['resource_ids'],
                Carbon::parse($validated['start_at']),
                $durationMinutes
            );

            return response()->json([
                'message'      => $e->getMessage(),
                'alternatives' => $alternatives,
            ], 409);
        }
    }

    /**
     * Show a composite booking with detailed listings of sub-bookings.
     */
    public function show(CompositeBooking $compositeBooking): JsonResponse
    {
        return response()->json($compositeBooking->load('bookings.resource.category', 'bookings.resource.facility', 'user'));
    }

    /**
     * Cancel the entire composite group atomically.
     */
    public function cancel(CompositeBooking $compositeBooking): JsonResponse
    {
        if ($compositeBooking->status === 'cancelled') {
            return response()->json(['message' => 'Already cancelled.'], 422);
        }

        $composite = $this->compositeService->cancel($compositeBooking);

        return response()->json($composite);
    }
}
