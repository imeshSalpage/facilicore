<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceOrder;
use App\Models\Tenant;
use App\Services\MaintenanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function __construct(private readonly MaintenanceService $maintenanceService)
    {
    }

    /**
     * List all maintenance orders. Admins/supervisors see all; end users see none.
     */
    public function index(): JsonResponse
    {
        $orders = MaintenanceOrder::with(['resource.category', 'resource.facility', 'assignedTo'])
            ->orderByRaw("CASE status
                WHEN 'in_progress' THEN 1
                WHEN 'scheduled'   THEN 2
                WHEN 'completed'   THEN 3
                WHEN 'cancelled'   THEN 4
                ELSE 5 END")
            ->orderBy('scheduled_at')
            ->get();

        return response()->json($orders);
    }

    /**
     * Create a new maintenance order (admin only — enforced via route middleware).
     */
    public function store(Request $request): JsonResponse
    {
        if (!app()->bound(Tenant::class)) {
            abort(403, 'A tenant context is required.');
        }

        $validated = $request->validate([
            'resource_id'  => 'required|integer|exists:resources,id',
            'assigned_to'  => 'nullable|integer|exists:users,id',
            'type'         => 'required|in:preventive,corrective,inspection,emergency',
            'scheduled_at' => 'required|date',
            'notes'        => 'nullable|string|max:2000',
            'cost'         => 'nullable|numeric|min:0',
        ]);

        $order = $this->maintenanceService->create($validated);

        return response()->json($order, 201);
    }

    /**
     * Show a single maintenance order.
     */
    public function show(MaintenanceOrder $maintenanceOrder): JsonResponse
    {
        return response()->json(
            $maintenanceOrder->load(['resource.category', 'resource.facility', 'assignedTo'])
        );
    }

    /**
     * Update a scheduled maintenance order (change date, notes, technician).
     */
    public function update(Request $request, MaintenanceOrder $maintenanceOrder): JsonResponse
    {
        if ($maintenanceOrder->status === 'completed') {
            return response()->json(['message' => 'Cannot edit a completed maintenance order.'], 422);
        }

        $validated = $request->validate([
            'assigned_to'  => 'nullable|integer|exists:users,id',
            'type'         => 'nullable|in:preventive,corrective,inspection,emergency',
            'scheduled_at' => 'nullable|date',
            'notes'        => 'nullable|string|max:2000',
            'cost'         => 'nullable|numeric|min:0',
        ]);

        $maintenanceOrder->update($validated);

        return response()->json(
            $maintenanceOrder->fresh(['resource.category', 'resource.facility', 'assignedTo'])
        );
    }

    /**
     * Mark a maintenance order as in-progress (work has begun).
     */
    public function startWork(MaintenanceOrder $maintenanceOrder): JsonResponse
    {
        if ($maintenanceOrder->status !== 'scheduled') {
            return response()->json(['message' => 'Order must be in scheduled state to start work.'], 422);
        }

        $order = $this->maintenanceService->startWork($maintenanceOrder);

        return response()->json($order);
    }

    /**
     * Complete a maintenance order — restores the resource to active.
     */
    public function complete(Request $request, MaintenanceOrder $maintenanceOrder): JsonResponse
    {
        if ($maintenanceOrder->status === 'completed') {
            return response()->json(['message' => 'Already completed.'], 422);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:2000',
            'cost'  => 'nullable|numeric|min:0',
        ]);

        $order = $this->maintenanceService->complete(
            $maintenanceOrder,
            $validated['notes'] ?? null,
            isset($validated['cost']) ? (float) $validated['cost'] : null
        );

        return response()->json($order);
    }

    /**
     * Cancel a maintenance order — restores the resource if no other active orders exist.
     */
    public function cancel(MaintenanceOrder $maintenanceOrder): JsonResponse
    {
        if (in_array($maintenanceOrder->status, ['completed', 'cancelled'])) {
            return response()->json(['message' => 'Cannot cancel a ' . $maintenanceOrder->status . ' order.'], 422);
        }

        $order = $this->maintenanceService->cancel($maintenanceOrder);

        return response()->json($order);
    }
}
