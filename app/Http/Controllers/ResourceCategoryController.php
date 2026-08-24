<?php

namespace App\Http\Controllers;

use App\Models\ResourceCategory;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $categories = ResourceCategory::all();
        return response()->json($categories);
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
        ]);

        $validated['tenant_id'] = app(Tenant::class)->id;

        $category = ResourceCategory::create($validated);

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ResourceCategory $resourceCategory): JsonResponse
    {
        return response()->json($resourceCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ResourceCategory $resourceCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $resourceCategory->update($validated);

        return response()->json($resourceCategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ResourceCategory $resourceCategory): JsonResponse
    {
        $resourceCategory->delete();
        return response()->json(null, 204);
    }
}
