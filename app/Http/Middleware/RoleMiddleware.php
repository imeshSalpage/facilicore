<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware — Enforces role-based access on API routes.
 * Usage in routes: ->middleware('role:admin') or ->middleware('role:supervisor')
 * Supervisors can also access routes restricted to supervisors (admin is a superset).
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // Admin is always a superset of all roles
        if ($user->role === 'admin') {
            return $next($request);
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
