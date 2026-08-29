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
                'name'                => 'Dr. Admin Clarke',
                'email'               => 'admin@eastbridge.edu',
                'password'            => Hash::make('password'),
                'role'                => 'admin',
                'registration_number' => 'ADM-1001',
                'department'          => 'Campus Administration',
                'phone'               => '+1 (555) 019-2831',
                'approval_status'     => 'approved',
                'approved_at'         => now(),
                'email_verified_at'   => now(),
                'tenant_id'           => $uni->id,
            ],
            [
                'name'                => 'Prof. Sarah Morgan',
                'email'               => 'supervisor@eastbridge.edu',
                'password'            => Hash::make('password'),
                'role'                => 'supervisor',
                'registration_number' => 'FAC-2045',
                'department'          => 'Faculty of Engineering',
                'phone'               => '+1 (555) 019-4829',
                'approval_status'     => 'approved',
                'approved_at'         => now(),
                'email_verified_at'   => now(),
                'tenant_id'           => $uni->id,
            ],
            [
                'name'                => 'Alex Thompson',
                'email'               => 'student@eastbridge.edu',
                'password'            => Hash::make('password'),
                'role'                => 'end_user',
                'registration_number' => 'STU-90812',
                'department'          => 'Computer Science Dept',
                'phone'               => '+1 (555) 019-3321',
                'approval_status'     => 'approved',
                'approved_at'         => now(),
                'email_verified_at'   => now(),
                'tenant_id'           => $uni->id,
            ],
            [
                'name'                => 'Maya Patel',
                'email'               => 'student2@eastbridge.edu',
                'password'            => Hash::make('password'),
                'role'                => 'end_user',
                'registration_number' => 'STU-90844',
                'department'          => 'Biotechnology Dept',
                'phone'               => '+1 (555) 019-7744',
                'approval_status'     => 'approved',
                'approved_at'         => now(),
                'email_verified_at'   => now(),
                'tenant_id'           => $uni->id,
            ],
            [
                'name'                => 'Jordan Lee (New Applicant)',
                'email'               => 'applicant@eastbridge.edu',
                'password'            => Hash::make('password'),
                'role'                => 'end_user',
                'registration_number' => 'STU-91002',
                'department'          => 'Robotics & Automation',
                'phone'               => '+1 (555) 019-8833',
                'approval_status'     => 'pending',
                'email_verified_at'   => now(),
                'tenant_id'           => $uni->id,
            ],
        ];

        foreach ($uniUsers as $data) {
            User::withoutGlobalScopes()->firstOrCreate(['email' => $data['email']], $data);
        }

        // 2. Healthcare (Hospital)
        $hosp = Tenant::where('subdomain', 'hospital')->firstOrFail();
        $hospUsers = [
            [
                'name'                => 'St. Jude Admin',
                'email'               => 'admin@stjude.org',
                'password'            => Hash::make('password'),
                'role'                => 'admin',
                'registration_number' => 'MED-ADM-01',
                'department'          => 'Hospital Executive Office',
                'phone'               => '+1 (555) 012-3401',
                'approval_status'     => 'approved',
                'approved_at'         => now(),
                'email_verified_at'   => now(),
                'tenant_id'           => $hosp->id,
            ],
            [
                'name'                => 'Dr. Robert Carter',
                'email'               => 'doctor@stjude.org',
                'password'            => Hash::make('password'),
                'role'                => 'supervisor',
                'registration_number' => 'MED-DOC-409',
                'department'          => 'Department of Surgery',
                'phone'               => '+1 (555) 012-3402',
                'approval_status'     => 'approved',
                'approved_at'         => now(),
                'email_verified_at'   => now(),
                'tenant_id'           => $hosp->id,
            ],
            [
                'name'                => 'Nurse Brenda',
                'email'               => 'nurse@stjude.org',
                'password'            => Hash::make('password'),
                'role'                => 'end_user',
                'registration_number' => 'MED-NUR-112',
                'department'          => 'Emergency Care Unit',
                'phone'               => '+1 (555) 012-3403',
                'approval_status'     => 'approved',
                'approved_at'         => now(),
                'email_verified_at'   => now(),
                'tenant_id'           => $hosp->id,
            ],
        ];

        foreach ($hospUsers as $data) {
            User::withoutGlobalScopes()->firstOrCreate(['email' => $data['email']], $data);
        }

        // 3. Corporate (Office)
        $corp = Tenant::where('subdomain', 'corporate')->firstOrFail();
        $corpUsers = [
            [
                'name'                => 'Nexus Admin',
                'email'               => 'admin@nexus.com',
                'password'            => Hash::make('password'),
                'role'                => 'admin',
                'registration_number' => 'CORP-EXEC-01',
                'department'          => 'Executive Suite',
                'phone'               => '+1 (555) 014-9901',
                'approval_status'     => 'approved',
                'approved_at'         => now(),
                'email_verified_at'   => now(),
                'tenant_id'           => $corp->id,
            ],
            [
                'name'                => 'Manager Keith',
                'email'               => 'manager@nexus.com',
                'password'            => Hash::make('password'),
                'role'                => 'supervisor',
                'registration_number' => 'CORP-MGR-310',
                'department'          => 'Product & Operations',
                'phone'               => '+1 (555) 014-9902',
                'approval_status'     => 'approved',
                'approved_at'         => now(),
                'email_verified_at'   => now(),
                'tenant_id'           => $corp->id,
            ],
            [
                'name'                => 'Employee David',
                'email'               => 'employee@nexus.com',
                'password'            => Hash::make('password'),
                'role'                => 'end_user',
                'registration_number' => 'CORP-EMP-882',
                'department'          => 'Engineering Team',
                'phone'               => '+1 (555) 014-9903',
                'approval_status'     => 'approved',
                'approved_at'         => now(),
                'email_verified_at'   => now(),
                'tenant_id'           => $corp->id,
            ],
        ];

        // 4. Imesh Tenant
        $imesh = Tenant::where('subdomain', 'imesh')->first();
        if ($imesh) {
            $imeshUsers = [
                [
                    'name'                => 'Imesh Admin',
                    'email'               => 'imesh@facilicore.me',
                    'password'            => Hash::make('password'),
                    'role'                => 'admin',
                    'registration_number' => 'ADM-001',
                    'department'          => 'Workspace Admin',
                    'phone'               => '+1 (555) 017-0001',
                    'approval_status'     => 'approved',
                    'approved_at'         => now(),
                    'email_verified_at'   => now(),
                    'tenant_id'           => $imesh->id,
                ],
                [
                    'name'                => 'Imesh Supervisor',
                    'email'               => 'supervisor@imesh.demo',
                    'password'            => Hash::make('password'),
                    'role'                => 'supervisor',
                    'registration_number' => 'SUP-002',
                    'department'          => 'Operations Lead',
                    'phone'               => '+1 (555) 017-0002',
                    'approval_status'     => 'approved',
                    'approved_at'         => now(),
                    'email_verified_at'   => now(),
                    'tenant_id'           => $imesh->id,
                ],
                [
                    'name'                => 'Imesh Student',
                    'email'               => 'student@imesh.demo',
                    'password'            => Hash::make('password'),
                    'role'                => 'end_user',
                    'registration_number' => 'STU-003',
                    'department'          => 'Software Engineering',
                    'phone'               => '+1 (555) 017-0003',
                    'approval_status'     => 'approved',
                    'approved_at'         => now(),
                    'email_verified_at'   => now(),
                    'tenant_id'           => $imesh->id,
                ],
            ];

            foreach ($imeshUsers as $data) {
                User::withoutGlobalScopes()->firstOrCreate(['email' => $data['email']], $data);
            }
        }

        // 5. Government
        $gov = Tenant::where('subdomain', 'government')->first();
        if ($gov) {
            $govUsers = [
                [
                    'name'                => 'Council Admin',
                    'email'               => 'admin@metro.gov',
                    'password'            => Hash::make('password'),
                    'role'                => 'admin',
                    'registration_number' => 'GOV-ADM-01',
                    'department'          => 'City Council Secretariat',
                    'phone'               => '+1 (555) 018-7001',
                    'approval_status'     => 'approved',
                    'approved_at'         => now(),
                    'email_verified_at'   => now(),
                    'tenant_id'           => $gov->id,
                ],
            ];

            foreach ($govUsers as $data) {
                User::withoutGlobalScopes()->firstOrCreate(['email' => $data['email']], $data);
            }
        }
    }
}
