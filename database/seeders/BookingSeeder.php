<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Resource;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $tenant     = Tenant::where('subdomain', 'university')->firstOrFail();
        $admin      = User::withoutGlobalScopes()->where('email', 'admin@eastbridge.edu')->firstOrFail();
        $supervisor = User::withoutGlobalScopes()->where('email', 'supervisor@eastbridge.edu')->firstOrFail();
        $student1   = User::withoutGlobalScopes()->where('email', 'student@eastbridge.edu')->firstOrFail();
        $student2   = User::withoutGlobalScopes()->where('email', 'student2@eastbridge.edu')->firstOrFail();

        $resource = fn($name) => Resource::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)->where('name', $name)->first();

        $today = Carbon::today();

        $bookings = [
            // Today's confirmed bookings
            [
                'resource'   => 'Lecture Theatre E-LT1',
                'user_id'    => $supervisor->id,
                'start_at'   => $today->copy()->setTime(9, 0),
                'end_at'     => $today->copy()->setTime(11, 0),
                'status'     => 'confirmed',
                'notes'      => 'COMP301 — Operating Systems lecture',
                'priority'   => 2,
            ],
            [
                'resource'   => 'Computer Lab E-CL1',
                'user_id'    => $supervisor->id,
                'start_at'   => $today->copy()->setTime(14, 0),
                'end_at'     => $today->copy()->setTime(16, 0),
                'status'     => 'confirmed',
                'notes'      => 'COMP401 CAD lab session — SolidWorks practicals',
                'priority'   => 2,
            ],
            [
                'resource'   => 'Group Study Room A',
                'user_id'    => $student1->id,
                'start_at'   => $today->copy()->setTime(10, 0),
                'end_at'     => $today->copy()->setTime(12, 0),
                'status'     => 'confirmed',
                'notes'      => 'Final year project group meeting',
                'priority'   => 5,
            ],
            [
                'resource'   => 'Board Room SU-BR1',
                'user_id'    => $admin->id,
                'start_at'   => $today->copy()->setTime(13, 0),
                'end_at'     => $today->copy()->setTime(14, 0),
                'status'     => 'confirmed',
                'notes'      => 'Faculty board meeting',
                'priority'   => 1,
            ],
            [
                'resource'   => 'Badminton Court 1',
                'user_id'    => $student2->id,
                'start_at'   => $today->copy()->setTime(17, 0),
                'end_at'     => $today->copy()->setTime(18, 0),
                'status'     => 'confirmed',
                'notes'      => 'Practice session',
                'priority'   => 8,
            ],

            // Tomorrow's bookings
            [
                'resource'   => 'Science Lecture Hall S-LH1',
                'user_id'    => $supervisor->id,
                'start_at'   => $today->copy()->addDay()->setTime(9, 0),
                'end_at'     => $today->copy()->addDay()->setTime(10, 30),
                'status'     => 'confirmed',
                'notes'      => 'CHEM201 — Organic Chemistry lecture',
                'priority'   => 2,
            ],
            [
                'resource'   => 'Group Study Room B',
                'user_id'    => $student1->id,
                'start_at'   => $today->copy()->addDay()->setTime(13, 0),
                'end_at'     => $today->copy()->addDay()->setTime(15, 0),
                'status'     => 'confirmed',
                'notes'      => 'Exam revision — Algorithms',
                'priority'   => 5,
            ],
            [
                'resource'   => 'Recording Studio H-RS1',
                'user_id'    => $student2->id,
                'start_at'   => $today->copy()->addDay()->setTime(14, 0),
                'end_at'     => $today->copy()->addDay()->setTime(16, 0),
                'status'     => 'pending',
                'notes'      => 'Student podcast project recording',
                'priority'   => 6,
            ],

            // Day after tomorrow
            [
                'resource'   => 'Computer Lab E-CL2',
                'user_id'    => $supervisor->id,
                'start_at'   => $today->copy()->addDays(2)->setTime(10, 0),
                'end_at'     => $today->copy()->addDays(2)->setTime(12, 0),
                'status'     => 'confirmed',
                'notes'      => 'SOFT302 — Database practicals',
                'priority'   => 2,
            ],
            [
                'resource'   => 'Committee Room SU-CR1',
                'user_id'    => $student1->id,
                'start_at'   => $today->copy()->addDays(2)->setTime(15, 0),
                'end_at'     => $today->copy()->addDays(2)->setTime(17, 0),
                'status'     => 'pending',
                'notes'      => 'Student society committee meeting',
                'priority'   => 7,
            ],
            [
                'resource'   => 'Fitness Gym — Zone A',
                'user_id'    => $student2->id,
                'start_at'   => $today->copy()->addDays(2)->setTime(7, 0),
                'end_at'     => $today->copy()->addDays(2)->setTime(8, 0),
                'status'     => 'confirmed',
                'notes'      => 'Early morning workout',
                'priority'   => 9,
            ],

            // Next week
            [
                'resource'   => 'Main Event Hall',
                'user_id'    => $admin->id,
                'start_at'   => $today->copy()->addDays(7)->setTime(10, 0),
                'end_at'     => $today->copy()->addDays(7)->setTime(17, 0),
                'status'     => 'confirmed',
                'notes'      => 'Annual University Open Day — all-day event',
                'priority'   => 1,
            ],
            [
                'resource'   => 'Portable Projector Kit A',
                'user_id'    => $student1->id,
                'start_at'   => $today->copy()->addDays(5)->setTime(9, 0),
                'end_at'     => $today->copy()->addDays(5)->setTime(13, 0),
                'status'     => 'confirmed',
                'notes'      => 'Final year dissertation presentation',
                'priority'   => 3,
            ],
        ];

        foreach ($bookings as $data) {
            $resourceModel = $resource($data['resource']);
            if (!$resourceModel || $resourceModel->status === 'maintenance') {
                $this->command->warn("Skipping booking for {$data['resource']}: not available.");
                continue;
            }

            Booking::withoutGlobalScopes()->firstOrCreate(
                [
                    'resource_id' => $resourceModel->id,
                    'user_id'     => $data['user_id'],
                    'start_at'    => $data['start_at'],
                ],
                [
                    'tenant_id'   => $tenant->id,
                    'resource_id' => $resourceModel->id,
                    'user_id'     => $data['user_id'],
                    'start_at'    => $data['start_at'],
                    'end_at'      => $data['end_at'],
                    'status'      => $data['status'],
                    'notes'       => $data['notes'],
                    'priority'    => $data['priority'],
                ]
            );
        }

        $this->command->info('Seeded ' . count($bookings) . ' university bookings.');
    }
}
