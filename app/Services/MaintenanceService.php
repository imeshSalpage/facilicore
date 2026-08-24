<?php

namespace App\Services;

use App\Models\MaintenanceOrder;
use App\Models\Resource;
use App\Models\Tenant;
use App\Notifications\MaintenanceNotification;
use Carbon\Carbon;

/**
 * MaintenanceService — SRP-compliant service for maintenance lifecycle management with notifications.
 * Responsible for resource status transitions triggered by maintenance events.
 */
class MaintenanceService
{
    /**
     * Create a new maintenance order and set the resource to 'maintenance' status.
     */
    public function create(array $data): MaintenanceOrder
    {
        $order = MaintenanceOrder::create([
            'tenant_id'    => app(Tenant::class)->id,
            'resource_id'  => $data['resource_id'],
            'assigned_to'  => $data['assigned_to'] ?? null,
            'type'         => $data['type'] ?? 'corrective',
            'status'       => 'scheduled',
            'scheduled_at' => Carbon::parse($data['scheduled_at']),
            'notes'        => $data['notes'] ?? null,
            'cost'         => $data['cost'] ?? null,
        ]);

        // Block the resource from being booked while under maintenance
        Resource::where('id', $data['resource_id'])->update(['status' => 'maintenance']);

        // Notify assigned technician
        if ($order->assigned_to && $order->assignedTo) {
            $order->assignedTo->notify(new MaintenanceNotification($order, 'scheduled'));
        }

        return $order->load(['resource.category', 'resource.facility', 'assignedTo']);
    }

    /**
     * Mark a maintenance order as completed and restore the resource to active.
     */
    public function complete(MaintenanceOrder $order, ?string $notes = null, ?float $cost = null): MaintenanceOrder
    {
        $order->update([
            'status'       => 'completed',
            'completed_at' => Carbon::now(),
            'notes'        => $notes ?? $order->notes,
            'cost'         => $cost ?? $order->cost,
        ]);

        // Restore resource availability — only if no other active maintenance order exists
        $otherActive = MaintenanceOrder::where('resource_id', $order->resource_id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->where('id', '!=', $order->id)
            ->exists();

        if (!$otherActive) {
            Resource::where('id', $order->resource_id)->update(['status' => 'active']);
        }

        // Notify assigned technician
        if ($order->assigned_to && $order->assignedTo) {
            $order->assignedTo->notify(new MaintenanceNotification($order, 'completed'));
        }

        return $order->fresh(['resource.category', 'resource.facility', 'assignedTo']);
    }

    /**
     * Cancel a maintenance order and restore the resource if no other orders are active.
     */
    public function cancel(MaintenanceOrder $order): MaintenanceOrder
    {
        $order->update(['status' => 'cancelled']);

        $otherActive = MaintenanceOrder::where('resource_id', $order->resource_id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->where('id', '!=', $order->id)
            ->exists();

        if (!$otherActive) {
            Resource::where('id', $order->resource_id)->update(['status' => 'active']);
        }

        // Notify assigned technician
        if ($order->assigned_to && $order->assignedTo) {
            $order->assignedTo->notify(new MaintenanceNotification($order, 'cancelled'));
        }

        return $order->fresh(['resource.category', 'resource.facility', 'assignedTo']);
    }

    /**
     * Mark an order as in-progress (technician has started work).
     */
    public function startWork(MaintenanceOrder $order): MaintenanceOrder
    {
        $order->update(['status' => 'in_progress']);

        return $order->fresh(['resource.category', 'resource.facility', 'assignedTo']);
    }
}
