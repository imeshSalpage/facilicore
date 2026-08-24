<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. University
        $uni = Tenant::where('subdomain', 'university')->firstOrFail();
        $uniUsers = [
            [
                'name'              => 'Dr. Admin Clarke',
                'email'             => 'admin@eastbridge.edu',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'email_verified_at' => now(),
                'tenant_id'         => $uni->id,
            ],
            [
                'name'              => 'Prof. Sarah Morgan',
                'email'             => 'supervisor@eastbridge.edu',
                'password'          => Hash::make('password'),
                'role'              => 'supervisor',
                'email_verified_at' => now(),
                'tenant_id'         => $uni->id,
            ],
            [
                'name'              => 'Alex Thompson',
                'email'             => 'student@eastbridge.edu',
                'password'          => Hash::make('password'),
                'role'              => 'end_user',
                'email_verified_at' => now(),
                'tenant_id'         => $uni->id,
            ],
            [
                'name'              => 'Maya Patel',
                'email'             => 'student2@eastbridge.edu',
                'password'          => Hash::make('password'),
                'role'              => 'end_user',
                'email_verified_at' => now(),
                'tenant_id'         => $uni->id,
            ],
        ];

        foreach ($uniUsers as $data) {
            User::withoutGlobalScopes()->firstOrCreate(['email' => $data['email']], $data);
        }

        // 2. Healthcare (Hospital)
        $hosp = Tenant::where('subdomain', 'hospital')->firstOrFail();
        $hospUsers = [
            [
                'name'              => 'St. Jude Admin',
                'email'             => 'admin@stjude.org',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'email_verified_at' => now(),
                'tenant_id'         => $hosp->id,
            ],
            [
                'name'              => 'Dr. Robert Carter',
                'email'             => 'doctor@stjude.org',
                'password'          => Hash::make('password'),
                'role'              => 'supervisor',
                'email_verified_at' => now(),
                'tenant_id'         => $hosp->id,
            ],
            [
                'name'              => 'Nurse Brenda',
                'email'             => 'nurse@stjude.org',
                'password'          => Hash::make('password'),
                'role'              => 'end_user',
                'email_verified_at' => now(),
                'tenant_id'         => $hosp->id,
            ],
        ];

        foreach ($hospUsers as $data) {
            User::withoutGlobalScopes()->firstOrCreate(['email' => $data['email']], $data);
        }

        // 3. Corporate (Office)
        $corp = Tenant::where('subdomain', 'corporate')->firstOrFail();
        $corpUsers = [
            [
                'name'              => 'Nexus Admin',
                'email'             => 'admin@nexus.com',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'email_verified_at' => now(),
                'tenant_id'         => $corp->id,
            ],
            [
                'name'              => 'Manager Keith',
                'email'             => 'manager@nexus.com',
                'password'          => Hash::make('password'),
                'role'              => 'supervisor',
                'email_verified_at' => now(),
                'tenant_id'         => $corp->id,
            ],
            [
                'name'              => 'Employee David',
                'email'             => 'employee@nexus.com',
                'password'          => Hash::make('password'),
                'role'              => 'end_user',
                'email_verified_at' => now(),
                'tenant_id'         => $corp->id,
            ],
        ];

        foreach ($corpUsers as $data) {
            User::withoutGlobalScopes()->firstOrCreate(['email' => $data['email']], $data);
        }
    }
}
