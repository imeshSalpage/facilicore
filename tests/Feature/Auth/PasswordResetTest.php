<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tenant = Tenant::create([
            'name'      => 'Eastbridge University',
            'subdomain' => 'university',
            'sector'    => 'university',
        ]);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->post('http://university.localhost/api/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->post('http://university.localhost/api/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {
            $response = $this->post('http://university.localhost/api/reset-password', [
                'token'                 => $notification->token,
                'email'                 => $user->email,
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]);

            $response->assertStatus(200);

            return true;
        });
    }
}
