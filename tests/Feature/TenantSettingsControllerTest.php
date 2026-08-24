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

    public function test_admin_can_update_tenant_name_sector_and_settings(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/tenant/settings', [
            'name'     => 'St Jude Hospital Care',
            'sector'   => 'healthcare',
            'settings' => [
                'auto_approve_standard_bookings' => true,
                'max_advance_booking_days'        => 90,
                'allow_urgency_override'          => false,
                'conflict_mode'                   => 'first_come',
                'maintenance_auto_schedule'       => false,
                'anomaly_sensitivity'             => 80,
                'notification_email'              => 'alerts@stjude.org',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('tenant.name', 'St Jude Hospital Care')
            ->assertJsonPath('tenant.sector', 'healthcare')
            ->assertJsonPath('tenant.settings.auto_approve_standard_bookings', true)
            ->assertJsonPath('tenant.settings.max_advance_booking_days', 90)
            ->assertJsonPath('tenant.settings.notification_email', 'alerts@stjude.org');

        $this->assertDatabaseHas('tenants', [
            'id'     => $this->tenant->id,
            'name'   => 'St Jude Hospital Care',
            'sector' => 'healthcare',
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
}
