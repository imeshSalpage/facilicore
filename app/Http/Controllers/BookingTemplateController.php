<?php

namespace App\Http\Controllers;

use App\Models\BookingTemplate;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingTemplateController extends Controller
{
    /**
     * List all templates for this tenant.
     */
    public function index(): JsonResponse
    {
        $templates = BookingTemplate::with('items.category')->get();
        return response()->json($templates);
    }

    /**
     * Create a new template (admin only — middleware in routes).
     */
    public function store(Request $request): JsonResponse
    {
        if (!app()->bound(Tenant::class)) {
            abort(403, 'A tenant context is required.');
        }

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string|max:1000',
            'items'            => 'required|array|min:1',
            'items.*.category_id'=> 'required|integer|exists:resource_categories,id',
            'items.*.quantity' => 'required|integer|min:1|max:5',
        ]);

        $template = BookingTemplate::create([
            'tenant_id'   => app(Tenant::class)->id,
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $template->items()->create([
                'category_id' => $item['category_id'],
                'quantity'    => $item['quantity'],
            ]);
        }

        return response()->json($template->load('items.category'), 201);
    }

    /**
     * Show a template.
     */
    public function show(BookingTemplate $bookingTemplate): JsonResponse
    {
        return response()->json($bookingTemplate->load('items.category'));
    }
}
