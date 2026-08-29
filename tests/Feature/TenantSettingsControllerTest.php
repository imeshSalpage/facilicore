<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $admin;
    private User $supervisor;
    private User $endUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'      => 'Test University',
            'subdomain' => 'testuni',
            'sector'    => 'university',
        ]);

        app()->instance(Tenant::class, $this->tenant);

        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'admin',
        ]);

        $this->supervisor = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'supervisor',
        ]);

        $this->endUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'end_user',
        ]);
    }

    public function test_admin_can_view_tenant_settings_and_stats(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/tenant/settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tenant' => ['id', 'name', 'subdomain', 'sector', 'settings', 'created_at'],
                'sector_info' => ['terminology', 'rules'],
                'stats' => [
                    'facilities_count',
                    'resources_count',
                    'active_resources_count',
                    'bookings_count',
                    'pending_approvals_count',
                    'maintenance_orders_count',
                    'users_total',
                    'users_by_role' => ['admin', 'supervisor', 'end_user'],
                ],
            ]);
    }

    public function test_non_admin_cannot_access_tenant_settings(): void
    {
        Sanctum::actingAs($this->supervisor);
        $this->getJson('/api/tenant/settings')->assertStatus(403);

        Sanctum::actingAs($this->endUser);
        $this->getJson('/api/tenant/settings')->assertStatus(403);
    }

    public function test_admin_can_update_tenant_name_and_settings(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/tenant/settings', [
            'name'     => 'Eastbridge Institute of Tech',
            'settings' => [
                'auto_approve_standard_bookings' => true,
                'max_advance_booking_days'        => 90,
                'allow_urgency_override'          => false,
                'conflict_mode'                   => 'first_come',
                'maintenance_auto_schedule'       => false,
                'anomaly_sensitivity'             => 80,
                'notification_email'              => 'alerts@eastbridge.edu',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('tenant.name', 'Eastbridge Institute of Tech')
            ->assertJsonPath('tenant.sector', 'university') // Sector remains unchanged
            ->assertJsonPath('tenant.settings.auto_approve_standard_bookings', true)
            ->assertJsonPath('tenant.settings.max_advance_booking_days', 90)
            ->assertJsonPath('tenant.settings.notification_email', 'alerts@eastbridge.edu');

        $this->assertDatabaseHas('tenants', [
            'id'     => $this->tenant->id,
            'name'   => 'Eastbridge Institute of Tech',
            'sector' => 'university',
        ]);
    }

    public function test_admin_can_list_users(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/tenant/users');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_admin_can_promote_user_role(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->patchJson("/api/tenant/users/{$this->endUser->id}/role", [
            'role' => 'supervisor',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.role', 'supervisor');

        $this->assertDatabaseHas('users', [
            'id'   => $this->endUser->id,
            'role' => 'supervisor',
        ]);
    }

    public function test_cannot_demote_the_sole_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->patchJson("/api/tenant/users/{$this->admin->id}/role", [
            'role' => 'end_user',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Cannot demote the only administrator. Please promote another team member to admin first.',
            ]);

        $this->assertDatabaseHas('users', [
            'id'   => $this->admin->id,
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_approve_pending_user(): void
    {
        $pendingUser = User::factory()->pending()->create([
            'tenant_id'           => $this->tenant->id,
            'registration_number' => 'STU-9901',
            'department'          => 'Physics',
            'phone'               => '555-0182',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->patchJson("/api/tenant/users/{$pendingUser->id}/approve", [
            'role' => 'end_user',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.approval_status', 'approved')
            ->assertJsonPath('user.role', 'end_user');

        $this->assertDatabaseHas('users', [
            'id'              => $pendingUser->id,
            'approval_status' => 'approved',
            'approved_by'     => $this->admin->id,
            'role'            => 'end_user',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $this->tenant->id,
            'user_id'   => $this->admin->id,
            'action'    => 'USER_REGISTRATION_APPROVED',
        ]);
    }

    public function test_admin_can_reject_user_with_reason(): void
    {
        $pendingUser = User::factory()->pending()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->patchJson("/api/tenant/users/{$pendingUser->id}/reject", [
            'reason' => 'Invalid institutional identity card.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.approval_status', 'rejected')
            ->assertJsonPath('user.rejection_reason', 'Invalid institutional identity card.');

        $this->assertDatabaseHas('users', [
            'id'               => $pendingUser->id,
            'approval_status'  => 'rejected',
            'rejection_reason' => 'Invalid institutional identity card.',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $this->tenant->id,
            'user_id'   => $this->admin->id,
            'action'    => 'USER_REGISTRATION_REJECTED',
        ]);
    }

    public function test_non_admin_cannot_approve_or_reject_users(): void
    {
        $pendingUser = User::factory()->pending()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        Sanctum::actingAs($this->supervisor);

        $this->patchJson("/api/tenant/users/{$pendingUser->id}/approve")->assertStatus(403);
        $this->patchJson("/api/tenant/users/{$pendingUser->id}/reject")->assertStatus(403);
    }
}
