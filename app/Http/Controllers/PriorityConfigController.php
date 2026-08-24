<?php

namespace App\Http\Controllers;

use App\Models\PriorityConfig;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PriorityConfigController — Handles priority factor weight settings for administrators.
 */
class PriorityConfigController extends Controller
{
    /**
     * Display the current priority config weights.
     */
    public function index(): JsonResponse
    {
        $configs = PriorityConfig::all();

        // If empty, return standard system defaults structure
        if ($configs->isEmpty()) {
            return response()->json([
                ['factor' => 'role_admin',      'weight' => 100.00],
                ['factor' => 'role_supervisor', 'weight' => 50.00],
                ['factor' => 'role_end_user',   'weight' => 10.00],
                ['factor' => 'urgency_flag',    'weight' => 30.00],
                ['factor' => 'resource_demand', 'weight' => 1.00],
            ]);
        }

        return response()->json($configs);
    }

    /**
     * Update/Upsert settings weights.
     */
    public function update(Request $request): JsonResponse
    {
        if (!app()->bound(Tenant::class)) {
            abort(403, 'A tenant context is required.');
        }

        $validated = $request->validate([
            'weights'          => 'required|array',
            'weights.*.factor' => 'required|string|in:role_admin,role_supervisor,role_end_user,urgency_flag,resource_demand',
            'weights.*.weight' => 'required|numeric|min:0|max:1000',
        ]);

        $tenantId = app(Tenant::class)->id;
        $saved = [];

        foreach ($validated['weights'] as $item) {
            $config = PriorityConfig::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'factor'    => $item['factor'],
                ],
                [
                    'weight'    => $item['weight'],
                ]
            );
            $saved[] = $config;
        }

        return response()->json($saved);
    }
}
