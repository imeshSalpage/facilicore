<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Services\ForecastService;
use Illuminate\Http\JsonResponse;

class ForecastController extends Controller
{
    public function __construct(private readonly ForecastService $forecastService)
    {
    }

    /**
     * Return 7-day occupancy forecast for a specific resource.
     */
    public function show(Resource $resource): JsonResponse
    {
        // Load category to ensure correct cold-start calculations
        $resource->load('category');
        
        $forecast = $this->forecastService->getForecastForResource($resource);

        return response()->json([
            'resource' => [
                'id'       => $resource->id,
                'name'     => $resource->name,
                'category' => $resource->category?->name,
                'location' => $resource->facility?->name ?? 'Global',
            ],
            'forecast' => $forecast,
        ]);
    }
}
