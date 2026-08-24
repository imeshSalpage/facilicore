<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * AuditLogService — Centralized logging utility for administrative and user booking activities.
 */
class AuditLogService
{
    /**
     * Log a tenant action into the audit_logs table.
     */
    public function log(string $action, ?string $modelType = null, ?int $modelId = null, ?array $payload = null): AuditLog
    {
        $tenantId = app()->bound(Tenant::class) ? app(Tenant::class)->id : null;
        $userId   = Auth::id();

        // Fallback for tenant if not set in container but payload or model exists
        if (!$tenantId && $modelType && $modelId) {
            $instance = $modelType::find($modelId);
            if ($instance && isset($instance->tenant_id)) {
                $tenantId = $instance->tenant_id;
            }
        }

        return AuditLog::create([
            'tenant_id'  => $tenantId,
            'user_id'    => $userId,
            'action'     => $action,
            'model_type' => $modelType,
            'model_id'   => $modelId,
            'payload'    => $payload,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
