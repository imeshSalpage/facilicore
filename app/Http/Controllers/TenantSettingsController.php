<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\MaintenanceOrder;
use App\Models\Resource;
use App\Models\Tenant;
use App\Models\User;
use App\Strategies\SectorStrategyInterface;
use Database\Seeders\TenantDemoSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * TenantSettingsController — Manages workspace profile, sector strategies,
 * booking & AI policies, telemetry diagnostics, and user role management.
 */
class TenantSettingsController extends Controller
{
    public function __construct(private readonly SectorStrategyInterface $sectorStrategy)
    {
    }

    /**
     * Get workspace settings, sector configuration, and system telemetry stats.
     */
    public function getSettings(): JsonResponse
    {
        $tenant = app(Tenant::class);

        // Calculate workspace stats
        $stats = [
            'facilities_count'              => Facility::count(),
            'resources_count'               => Resource::count(),
            'active_resources_count'        => Resource::where('status', 'active')->count(),
            'bookings_count'                => Booking::count(),
            'pending_approvals_count'       => Booking::where('status', 'pending')->count(),
            'pending_user_approvals_count'  => User::where('approval_status', 'pending')->count(),
            'maintenance_orders_count'      => MaintenanceOrder::whereIn('status', ['open', 'in_progress'])->count(),
            'users_total'                   => User::count(),
            'users_by_role'                 => [
                'admin'      => User::where('role', 'admin')->count(),
                'supervisor' => User::where('role', 'supervisor')->count(),
                'end_user'   => User::where('role', 'end_user')->count(),
            ],
            'users_by_approval_status'      => [
                'approved' => User::where('approval_status', 'approved')->count(),
                'pending'  => User::where('approval_status', 'pending')->count(),
                'rejected' => User::where('approval_status', 'rejected')->count(),
            ],
        ];

        return response()->json([
            'tenant' => [
                'id'         => $tenant->id,
                'name'       => $tenant->name,
                'subdomain'  => $tenant->subdomain,
                'sector'     => $tenant->sector ?? 'university',
                'settings'   => $tenant->getResolvedSettings(),
                'created_at' => $tenant->created_at,
            ],
            'sector_info' => [
                'terminology' => $this->sectorStrategy->getTerminology(),
                'rules'       => $this->sectorStrategy->getPriorityRules(),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Update workspace settings and organization name (sector remains immutable).
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $tenant = app(Tenant::class);

        $validated = $request->validate([
            'name'                                       => ['required', 'string', 'max:255'],
            'settings'                                   => ['nullable', 'array'],
            'settings.auto_approve_standard_bookings'    => ['nullable', 'boolean'],
            'settings.max_advance_booking_days'          => ['nullable', 'integer', 'min:1', 'max:365'],
            'settings.allow_urgency_override'            => ['nullable', 'boolean'],
            'settings.conflict_mode'                     => ['nullable', 'string', Rule::in(['priority_override', 'first_come'])],
            'settings.maintenance_auto_schedule'         => ['nullable', 'boolean'],
            'settings.anomaly_sensitivity'               => ['nullable', 'integer', 'min:1', 'max:100'],
            'settings.notification_email'                => ['nullable', 'email', 'max:255'],
        ]);

        $currentSettings = $tenant->getResolvedSettings();
        $newSettings = array_merge($currentSettings, $validated['settings'] ?? []);

        $tenant->update([
            'name'     => $validated['name'],
            'settings' => $newSettings,
        ]);

        return response()->json([
            'message'  => 'Workspace settings updated successfully.',
            'tenant'   => [
                'id'        => $tenant->id,
                'name'      => $tenant->name,
                'subdomain' => $tenant->subdomain,
                'sector'    => $tenant->sector,
                'settings'  => $tenant->getResolvedSettings(),
            ],
        ]);
    }

    /**
     * List all team members and pending applicants in the tenant.
     */
    public function getUsers(): JsonResponse
    {
        $users = User::with('approver:id,name,email')
            ->orderByRaw("CASE WHEN approval_status = 'pending' THEN 0 WHEN approval_status = 'approved' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get([
                'id',
                'name',
                'email',
                'role',
                'registration_number',
                'department',
                'phone',
                'approval_status',
                'approved_at',
                'approved_by',
                'rejection_reason',
                'email_verified_at',
                'created_at',
            ]);

        return response()->json($users);
    }

    /**
     * Approve a pending user registration and optionally assign their role.
     */
    public function approveUser(Request $request, User $user): JsonResponse
    {
        $tenant = app(Tenant::class);

        if ($user->tenant_id !== $tenant->id) {
            abort(404, 'User not found in this workspace.');
        }

        $validated = $request->validate([
            'role' => ['nullable', 'string', Rule::in(['admin', 'supervisor', 'end_user'])],
        ]);

        $assignedRole = $validated['role'] ?? ($user->role ?? 'end_user');

        $user->update([
            'approval_status'   => 'approved',
            'approved_at'       => now(),
            'approved_by'       => auth()->id(),
            'role'              => $assignedRole,
            'rejection_reason'  => null,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        \App\Models\AuditLog::create([
            'tenant_id'   => $tenant->id,
            'user_id'     => auth()->id(),
            'action'      => 'USER_REGISTRATION_APPROVED',
            'model_type'  => User::class,
            'model_id'    => $user->id,
            'payload'     => [
                'approved_user_id'    => $user->id,
                'approved_user_name'  => $user->name,
                'approved_user_email' => $user->email,
                'assigned_role'       => $assignedRole,
            ],
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        return response()->json([
            'message' => "User {$user->name} has been approved successfully.",
            'user'    => $user->fresh()->load('approver:id,name,email'),
        ]);
    }

    /**
     * Reject a user registration request.
     */
    public function rejectUser(Request $request, User $user): JsonResponse
    {
        $tenant = app(Tenant::class);

        if ($user->tenant_id !== $tenant->id) {
            abort(404, 'User not found in this workspace.');
        }

        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot reject your own administrator account.',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->update([
            'approval_status'  => 'rejected',
            'rejection_reason' => $validated['reason'] ?? null,
        ]);

        \App\Models\AuditLog::create([
            'tenant_id'   => $tenant->id,
            'user_id'     => auth()->id(),
            'action'      => 'USER_REGISTRATION_REJECTED',
            'model_type'  => User::class,
            'model_id'    => $user->id,
            'payload'     => [
                'rejected_user_id'    => $user->id,
                'rejected_user_name'  => $user->name,
                'rejected_user_email' => $user->email,
                'reason'              => $validated['reason'] ?? null,
            ],
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        return response()->json([
            'message' => "User {$user->name} registration has been rejected.",
            'user'    => $user->fresh(),
        ]);
    }

    /**
     * Promote / demote a team member role with safety guard.
     */
    public function updateUserRole(Request $request, User $user): JsonResponse
    {
        $tenant = app(Tenant::class);

        // Scope check
        if ($user->tenant_id !== $tenant->id) {
            abort(404, 'User not found in this workspace.');
        }

        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(['admin', 'supervisor', 'end_user'])],
        ]);

        $newRole = $validated['role'];

        // Safety guard: if demoting an admin, make sure there is at least one other admin remaining
        if ($user->role === 'admin' && $newRole !== 'admin') {
            $otherAdminCount = User::where('id', '!=', $user->id)
                ->where('role', 'admin')
                ->count();

            if ($otherAdminCount === 0) {
                return response()->json([
                    'message' => 'Cannot demote the only administrator. Please promote another team member to admin first.',
                ], 422);
            }
        }

        $user->update(['role' => $newRole]);

        return response()->json([
            'message' => "User {$user->name} role updated to {$newRole}.",
            'user'    => $user->fresh(),
        ]);
    }

    /**
     * Sync and generate sector-specific composite templates for this workspace.
     */
    public function syncTemplates(): JsonResponse
    {
        $tenant = app(Tenant::class);
        $admin = auth()->user();

        $seeder = new TenantDemoSeeder();
        $seeder->seedForTenant($tenant, $admin);

        return response()->json([
            'message' => "Templates and sector resources synced successfully for {$tenant->sector} sector.",
        ]);
    }
}
