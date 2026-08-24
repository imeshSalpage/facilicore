<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Resource;
use App\Models\Facility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * AnalyticsController — Aggregates booking statistics, peak times, and resource utilization metrics.
 */
class AnalyticsController extends Controller
{
    /**
     * Get aggregate analytics reports.
     * Accessible by admin and supervisor.
     */
    public function report(Request $request): JsonResponse
    {
        $days = $request->integer('days', 30);
        $startDate = Carbon::now()->subDays($days);

        // 1. Booking volume by date (last N days)
        $bookingVolume = Booking::select(DB::raw("DATE(start_at) as date"), DB::raw("COUNT(*) as count"))
            ->where('start_at', '>=', $startDate)
            ->groupBy(DB::raw("DATE(start_at)"))
            ->orderBy('date')
            ->get();

        // 2. Bookings per Resource
        $resourceBookings = Booking::select('resources.name', DB::raw("COUNT(bookings.id) as count"))
            ->join('resources', 'bookings.resource_id', '=', 'resources.id')
            ->where('bookings.start_at', '>=', $startDate)
            ->groupBy('resources.id', 'resources.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // 3. Peak usage hours (07:00 to 21:00)
        $peakHours = Booking::select(DB::raw("strftime('%H', start_at) as hour"), DB::raw("COUNT(*) as count"))
            ->where('start_at', '>=', $startDate)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();

        // Fill missing hours to ensure complete 24h or 07-21 dataset
        $hourlyData = [];
        for ($h = 7; $h <= 21; $h++) {
            $formattedHour = str_pad($h, 2, '0', STR_PAD_LEFT);
            $hourlyData[$formattedHour] = $peakHours[$formattedHour] ?? 0;
        }

        // 4. Resource categories breakdown
        $categoryBreakdown = Booking::select('resource_categories.name', DB::raw("COUNT(bookings.id) as count"))
            ->join('resources', 'bookings.resource_id', '=', 'resources.id')
            ->join('resource_categories', 'resources.category_id', '=', 'resource_categories.id')
            ->where('bookings.start_at', '>=', $startDate)
            ->groupBy('resource_categories.id', 'resource_categories.name')
            ->orderByDesc('count')
            ->get();

        // 5. Overall statistics
        $totalBookings = Booking::where('start_at', '>=', $startDate)->count();
        $confirmedCount = Booking::where('start_at', '>=', $startDate)->where('status', 'confirmed')->count();
        $cancelledCount = Booking::where('start_at', '>=', $startDate)->where('status', 'cancelled')->count();
        
        $totalResources = Resource::count();
        $underMaintenance = Resource::where('status', 'maintenance')->count();

        // 6. Utilization rates (hours booked vs hours available - mock calculation based on 8h/day availability)
        // We find total minutes booked in last N days
        $totalBookedMinutes = Booking::where('start_at', '>=', $startDate)
            ->where('status', 'confirmed')
            ->get()
            ->sum(function($b) {
                return max(0, Carbon::parse($b->end_at)->diffInMinutes(Carbon::parse($b->start_at)));
            });
            
        $totalAvailableMinutes = $totalResources * $days * 8 * 60; // 8 hours per day
        $utilizationRate = $totalAvailableMinutes > 0 
            ? round(($totalBookedMinutes / $totalAvailableMinutes) * 100, 1) 
            : 0;

        return response()->json([
            'summary' => [
                'total_bookings'    => $totalBookings,
                'confirmed_count'   => $confirmedCount,
                'cancelled_count'   => $cancelledCount,
                'total_resources'   => $totalResources,
                'under_maintenance' => $underMaintenance,
                'utilization_rate'  => $utilizationRate,
            ],
            'charts' => [
                'booking_volume'     => $bookingVolume,
                'resource_bookings'  => $resourceBookings,
                'hourly_distribution'=> $hourlyData,
                'category_breakdown' => $categoryBreakdown,
            ]
        ]);
    }
}
