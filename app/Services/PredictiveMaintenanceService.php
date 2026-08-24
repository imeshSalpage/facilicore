<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Resource;
use App\Models\User;
use App\Models\Tenant;
use App\Models\MaintenanceOrder;
use App\Services\MaintenanceService;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * PredictiveMaintenanceService — Detects high usage stress anomalies and auto-schedules preventive blocks.
 */
class PredictiveMaintenanceService
{
    public function __construct(
        private readonly MaintenanceService $maintenanceService,
        private readonly AuditLogService $auditService
    ) {
    }

    /**
     * Scan active resources for usage anomalies and auto-create maintenance orders for outliers.
     * Returns an array of resources flagged and blocked during the scan.
     */
    public function runScan(): array
    {
        $tenantId = app(Tenant::class)->id;
        $cutoffDate = Carbon::now()->subDays(14);

        // 1. Fetch total booking hours per resource in the last 14 days
        $bookings = Booking::where('start_at', '>=', $cutoffDate)
            ->where('status', 'confirmed')
            ->get();

        $resourceDurations = [];
        foreach ($bookings as $b) {
            $durationHours = max(0.5, Carbon::parse($b->end_at)->diffInMinutes(Carbon::parse($b->start_at)) / 60.0);
            $resourceDurations[$b->resource_id] = ($resourceDurations[$b->resource_id] ?? 0) + $durationHours;
        }

        // 2. Fetch all resources with categories
        $resources = Resource::with('category')->where('status', 'active')->get();

        // Group usage hours by category
        $categoryGroups = [];
        foreach ($resources as $r) {
            $hours = $resourceDurations[$r->id] ?? 0.0;
            $categoryGroups[$r->category_id][] = [
                'resource' => $r,
                'hours'    => $hours,
            ];
        }

        $flagged = [];
        $technician = User::where('role', 'supervisor')->first(); // Assign to first supervisor as fallback technician

        // 3. Detect outliers per category
        foreach ($categoryGroups as $catId => $items) {
            $count = count($items);
            if ($count === 0) continue;

            $hoursList = array_column($items, 'hours');
            $average   = array_sum($hoursList) / $count;

            // Calculate Standard Deviation
            $varianceSum = 0;
            foreach ($hoursList as $h) {
                $varianceSum += pow($h - $average, 2);
            }
            $stdDev = sqrt($varianceSum / $count);

            // Anomaly threshold: average + 1.5 * standard deviation
            // Static threshold fallback (e.g. 40 hours) if standard deviation is too small
            $threshold = $stdDev > 1.0 ? ($average + (1.5 * $stdDev)) : 40.0;

            foreach ($items as $item) {
                $res = $item['resource'];
                $hours = $item['hours'];

                if ($hours > $threshold) {
                    // Outlier detected!
                    // Check if it already has an active scheduled maintenance order to prevent duplicates
                    $hasActiveOrder = MaintenanceOrder::where('resource_id', $res->id)
                        ->whereIn('status', ['scheduled', 'in_progress'])
                        ->exists();

                    if (!$hasActiveOrder) {
                        // Automatically schedule a maintenance order
                        $order = $this->maintenanceService->create([
                            'resource_id'  => $res->id,
                            'assigned_to'  => $technician?->id,
                            'type'         => 'preventive',
                            'scheduled_at' => Carbon::now()->addDay()->setTime(9, 0), // Tomorrow at 9am
                            'notes'        => sprintf(
                                "[Auto-Scheduled Predictive Maintenance] High usage anomaly detected. Booked hours (%s hrs) exceeded statistical threshold for category %s (%s hrs).",
                                round($hours, 1),
                                $res->category?->name ?? 'N/A',
                                round($threshold, 1)
                            )
                        ]);

                        // Log in audit logs
                        $this->auditService->log(
                            'maintenance.auto_scheduled',
                            Resource::class,
                            $res->id,
                            [
                                'hours_used' => $hours,
                                'threshold'  => $threshold,
                                'order_id'   => $order->id,
                            ]
                        );

                        $flagged[] = [
                            'resource'    => $res->name,
                            'hours'       => round($hours, 1),
                            'threshold'   => round($threshold, 1),
                            'category'    => $res->category?->name,
                            'location'    => $res->facility?->name ?? 'Global',
                            'order_notes' => $order->notes,
                        ];
                    }
                }
            }
        }

        return $flagged;
    }
}
