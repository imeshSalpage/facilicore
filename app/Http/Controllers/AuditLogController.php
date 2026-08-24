<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AuditLogController — Fetches paginated audit logs for administrators.
 */
class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs.
     * Accessible by admin and supervisor.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::with('user:id,name,email')
            ->orderBy('created_at', 'desc');

        // Filter by action if provided
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        // Filter by user if provided
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // General search across action, model type or user name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Paginate logs
        $logs = $query->paginate($request->integer('per_page', 25));

        return response()->json($logs);
    }
}
