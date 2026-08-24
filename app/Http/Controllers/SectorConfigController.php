<?php

namespace App\Http\Controllers;

use App\Strategies\SectorStrategyInterface;
use Illuminate\Http\JsonResponse;

/**
 * SectorConfigController — Resolves active sector configuration strategy and returns its terminology mapping.
 */
class SectorConfigController extends Controller
{
    public function __construct(private readonly SectorStrategyInterface $sectorStrategy)
    {
    }

    /**
     * Get terminology vocabulary mapping and rules for the active tenant.
     */
    public function terminology(): JsonResponse
    {
        return response()->json([
            'terminology' => $this->sectorStrategy->getTerminology(),
            'rules'       => $this->sectorStrategy->getPriorityRules(),
        ]);
    }
}
