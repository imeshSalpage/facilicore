<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Database\Seeders\TenantDemoSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
    {
        if (app()->bound(\App\Models\Tenant::class)) {
            $tenant = app(\App\Models\Tenant::class);

            $request->validate([
                'name'                => ['required', 'string', 'max:255'],
                'email'               => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'registration_number' => ['required', 'string', 'max:100'],
                'department'          => ['nullable', 'string', 'max:255'],
                'phone'               => ['nullable', 'string', 'max:50'],
                'password'            => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $user = User::create([
                'tenant_id'           => $tenant->id,
                'name'                => $request->name,
                'email'               => $request->email,
                'registration_number' => $request->registration_number,
                'department'          => $request->department,
                'phone'               => $request->phone,
                'password'            => Hash::make($request->string('password')),
                'role'                => 'end_user',
                'approval_status'     => 'pending',
            ]);

            // Log registration request into AuditLog
            \App\Models\AuditLog::create([
                'tenant_id'   => $tenant->id,
                'user_id'     => $user->id,
                'action'      => 'USER_REGISTERED_PENDING_APPROVAL',
                'model_type'  => User::class,
                'model_id'    => $user->id,
                'payload'     => [
                    'name'                => $user->name,
                    'email'               => $user->email,
                    'registration_number' => $user->registration_number,
                    'department'          => $user->department,
                    'phone'               => $user->phone,
                    'approval_status'     => 'pending',
                ],
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);

            return response()->json([
                'message' => 'Registration submitted successfully! Your account is currently pending administrator approval.',
                'status'  => 'pending_approval',
                'user'    => [
                    'id'                  => $user->id,
                    'name'                => $user->name,
                    'email'               => $user->email,
                    'registration_number' => $user->registration_number,
                    'department'          => $user->department,
                    'approval_status'     => $user->approval_status,
                ],
            ], 201);
        } else {
            $request->validate([
                'company_name' => ['required', 'string', 'max:255'],
                'subdomain'    => ['required', 'string', 'alpha_num', 'lowercase', 'max:255', 'unique:tenants,subdomain'],
                'sector'       => ['required', 'string', 'in:university,healthcare,corporate,government'],
                'name'         => ['required', 'string', 'max:255'],
                'email'        => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password'     => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $tenant = \App\Models\Tenant::create([
                'name'      => $request->company_name,
                'subdomain' => $request->subdomain,
                'sector'    => $request->sector,
            ]);

            $user = User::create([
                'tenant_id'       => $tenant->id,
                'name'            => $request->name,
                'email'           => $request->email,
                'password'        => Hash::make($request->string('password')),
                'role'            => 'admin', // Tenant founder is always the administrator
                'approval_status' => 'approved',
                'approved_at'     => now(),
            ]);

            event(new Registered($user));

            // Optionally populate the new tenant with demo data
            if ($request->boolean('seed_demo_data')) {
                (new TenantDemoSeeder)->seedForTenant($tenant, $user);
            }

            Auth::login($user);

            return response()->json([
                'tenant'    => $tenant,
                'subdomain' => $tenant->subdomain,
            ]);
        }
    }
}
