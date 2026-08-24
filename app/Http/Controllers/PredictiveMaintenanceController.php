<?php

namespace App\Http\Controllers;

use App\Services\PredictiveMaintenanceService;
use Illuminate\Http\JsonResponse;

class PredictiveMaintenanceController extends Controller
{
    public function __construct(private readonly PredictiveMaintenanceService $predictiveService)
    {
    }

    /**
     * Run scan to detect usage outliers and trigger auto-created maintenance orders.
     */
    public function scan(): JsonResponse
    {
        $flagged = $this->predictiveService->runScan();

        return response()->json([
            'message' => count($flagged) . ' resource usage anomalies detected and scheduled for maintenance.',
            'flagged' => $flagged,
        ]);
    }
}
