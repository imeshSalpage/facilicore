<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $facilities = Facility::all();
        return response()->json($facilities);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->user() && $request->user()->role === 'end_user') {
            abort(403, 'You do not have administrative permission to create facilities.');
        }

        if (!app()->bound(Tenant::class)) {
            abort(403, 'A tenant context is required.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
        ]);

        $validated['tenant_id'] = app(Tenant::class)->id;

        $facility = Facility::create($validated);

        return response()->json($facility, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Facility $facility): JsonResponse
    {
        return response()->json($facility);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Facility $facility): JsonResponse
    {
        if ($request->user() && $request->user()->role === 'end_user') {
            abort(403, 'You do not have administrative permission to modify facilities.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
        ]);

        $facility->update($validated);

        return response()->json($facility);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Facility $facility): JsonResponse
    {
        if ($request->user() && $request->user()->role === 'end_user') {
            abort(403, 'You do not have administrative permission to delete facilities.');
        }

        $facility->delete();
        return response()->json(null, 204);
    }
}
