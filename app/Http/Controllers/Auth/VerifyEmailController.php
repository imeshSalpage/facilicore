<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        $frontendUrl = config('app.frontend_url');
        if ($tenant) {
            $parsedUrl = parse_url($frontendUrl);
            $host = $parsedUrl['host'] ?? 'lvh.me';
            $scheme = $parsedUrl['scheme'] ?? 'http';
            $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
            $frontendUrl = "{$scheme}://{$tenant->subdomain}.{$host}{$port}";
        }

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(
                $frontendUrl.'/dashboard?verified=1'
            );
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(
            $frontendUrl.'/dashboard?verified=1'
        );
    }
}
