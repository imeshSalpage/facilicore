<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $resources = Resource::with(['facility', 'category'])->get();
        return response()->json($resources);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        if (!app()->bound(Tenant::class)) {
            abort(403, 'A tenant context is required.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'facility_id' => 'nullable|exists:facilities,id',
            'category_id' => 'required|exists:resource_categories,id',
            'specifications' => 'nullable|array',
            'capacity' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:active,maintenance,retired',
            'image_url' => 'nullable|string|max:1000',
        ]);

        $validated['tenant_id'] = app(Tenant::class)->id;
        if (!isset($validated['status'])) {
            $validated['status'] = 'active';
        }

        $resource = Resource::create($validated);

        return response()->json($resource->load(['facility', 'category']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Resource $resource): JsonResponse
    {
        return response()->json($resource->load(['facility', 'category']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Resource $resource): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'facility_id' => 'nullable|exists:facilities,id',
            'category_id' => 'required|exists:resource_categories,id',
            'specifications' => 'nullable|array',
            'capacity' => 'nullable|integer|min:0',
            'status' => 'required|string|in:active,maintenance,retired',
            'image_url' => 'nullable|string|max:1000',
        ]);

        $resource->update($validated);

        return response()->json($resource->load(['facility', 'category']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resource $resource): JsonResponse
    {
        $resource->delete();
        return response()->json(null, 204);
    }
}
