<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(config('app.frontend_url').'/dashboard?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_admin_is_blocked_by_verified_middleware(): void
    {
        $admin = User::factory()->unverified()->create([
            'role' => 'admin',
        ]);

        $middleware = new \App\Http\Middleware\EnsureEmailIsVerified();
        $request = \Illuminate\Http\Request::create('/api/dashboard', 'GET');
        $request->setUserResolver(fn () => $admin);

        $response = $middleware->handle($request, fn () => response()->json(['status' => 'ok']));

        $this->assertEquals(409, $response->getStatusCode());
    }

    public function test_unverified_regular_user_passes_verified_middleware_without_conflict(): void
    {
        $endUser = User::factory()->unverified()->create([
            'role' => 'end_user',
        ]);

        $middleware = new \App\Http\Middleware\EnsureEmailIsVerified();
        $request = \Illuminate\Http\Request::create('/api/dashboard', 'GET');
        $request->setUserResolver(fn () => $endUser);

        $response = $middleware->handle($request, fn () => response()->json(['status' => 'ok']));

        $this->assertEquals(200, $response->getStatusCode());
    }
}
