<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'      => 'Eastbridge University',
            'subdomain' => 'university',
            'sector'    => 'university',
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role'      => 'end_user',
        ]);
    }

    public function test_get_notifications_list(): void
    {
        $response = $this->actingAs($this->user)
            ->get('http://university.localhost/api/notifications');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'notifications',
            'unread_count',
        ]);
    }

    public function test_mark_all_notifications_read(): void
    {
        $response = $this->actingAs($this->user)
            ->patch('http://university.localhost/api/notifications/read-all');

        $response->assertStatus(200);
    }
}
