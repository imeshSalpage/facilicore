<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        $subdomain = null;
        if (count($parts) === 3 && $parts[1] === 'facilicore' && $parts[2] === 'me') {
            $subdomain = $parts[0];
        } elseif (count($parts) === 3 && $parts[1] === 'lvh' && $parts[2] === 'me') {
            $subdomain = $parts[0];
        } elseif (count($parts) === 2 && $parts[1] === 'localhost') {
            $subdomain = $parts[0];
        }
        
        if ($subdomain && $subdomain !== 'www' && $subdomain !== 'api' && $subdomain !== 'superadmin') {
            $tenant = Tenant::where('subdomain', $subdomain)->first();
            
            if (!$tenant) {
                abort(404, 'Tenant not found.');
            }
            
            app()->instance(Tenant::class, $tenant);
        }

        return $next($request);
    }
}
