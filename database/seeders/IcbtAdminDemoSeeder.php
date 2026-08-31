<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingTemplate;
use App\Models\CompositeBooking;
use App\Models\Facility;
use App\Models\MaintenanceOrder;
use App\Models\PriorityConfig;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class IcbtAdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Tenant ICBT exists
        $tenant = Tenant::firstOrCreate(
            ['subdomain' => 'icbt'],
            [
                'name'     => 'ICBT Campus',
                'sector'   => 'university',
                'settings' => [
                    'auto_approve_bookings'       => false,
                    'require_user_approval'       => true,
                    'allow_guest_registrations'   => true,
                    'allow_conflicts'             => false,
                    'max_advance_booking_days'    => 90,
                    'operating_hours'             => [
                        'start' => '08:00',
                        'end'   => '21:00',
                    ],
                    'maintenance_alert_threshold' => 80,
                ],
            ]
        );

        // Update settings if not present
        if (empty($tenant->settings)) {
            $tenant->update([
                'settings' => [
                    'auto_approve_bookings'       => false,
                    'require_user_approval'       => true,
                    'allow_guest_registrations'   => true,
                    'allow_conflicts'             => false,
                    'max_advance_booking_days'    => 90,
                    'operating_hours'             => [
                        'start' => '08:00',
                        'end'   => '21:00',
                    ],
                    'maintenance_alert_threshold' => 80,
                ],
            ]);
        }

        // Set active tenant in container for scope consistency
        app()->instance(Tenant::class, $tenant);

        // 2. Users
        $usersData = [
            // Admin
            [
                'name'                => 'Dr. Gamini Wickramasinghe',
                'email'               => 'icbt@gmail.com',
                'password'            => Hash::make('password'),
                'role'                => 'admin',
                'registration_number' => 'ICBT-ADM-001',
                'department'          => 'Executive Campus Administration',
                'phone'               => '+94 11 477 7888',
                'approval_status'     => 'approved',
                'approved_at'         => now()->subMonths(6),
                'email_verified_at'   => now()->subMonths(6),
            ],
            // Supervisors
            [
                'name'                => 'Prof. Dhammika Silva',
                'email'               => 'supervisor@icbt.demo',
                'password'            => Hash::make('password'),
                'role'                => 'supervisor',
                'registration_number' => 'FAC-SOC-101',
                'department'          => 'School of Computing & IT',
                'phone'               => '+94 77 123 4567',
                'approval_status'     => 'approved',
                'approved_at'         => now()->subMonths(5),
                'email_verified_at'   => now()->subMonths(5),
            ],
            [
                'name'                => 'Eng. Kavinda Perera',
                'email'               => 'kavinda.sup@icbt.lk',
                'password'            => Hash::make('password'),
                'role'                => 'supervisor',
                'registration_number' => 'FAC-ENG-204',
                'department'          => 'Department of Mechatronics',
                'phone'               => '+94 71 987 6543',
                'approval_status'     => 'approved',
                'approved_at'         => now()->subMonths(4),
                'email_verified_at'   => now()->subMonths(4),
            ],
            [
                'name'                => 'Dr. Anuruddha Bandara',
                'email'               => 'academic.affairs@icbt.lk',
                'password'            => Hash::make('password'),
                'role'                => 'supervisor',
                'registration_number' => 'FAC-DIR-002',
                'department'          => 'Academic Affairs & Quality Assurance',
                'phone'               => '+94 76 555 1212',
                'approval_status'     => 'approved',
                'approved_at'         => now()->subMonths(4),
                'email_verified_at'   => now()->subMonths(4),
            ],
            // End Users (Faculty & Students)
            [
                'name'                => 'Imesh Salpage',
                'email'               => 'imesh@gmail.com',
                'password'            => Hash::make('password'),
                'role'                => 'end_user',
                'registration_number' => 'STU-BSE-2024-001',
                'department'          => 'School of Computing (Software Eng)',
                'phone'               => '+94 70 234 5678',
                'approval_status'     => 'approved',
                'approved_at'         => now()->subMonths(3),
                'email_verified_at'   => now()->subMonths(3),
            ],
            [
                'name'                => 'Kasun Karunaratne',
                'email'               => 'kasun.k@icbt.lk',
                'password'            => Hash::make('password'),
                'role'                => 'end_user',
                'registration_number' => 'STU-BSE-2024-045',
                'department'          => 'School of Computing',
                'phone'               => '+94 77 444 3322',
                'approval_status'     => 'approved',
                'approved_at'         => now()->subMonths(3),
                'email_verified_at'   => now()->subMonths(3),
            ],
            [
                'name'                => 'Sanduni Fernando',
                'email'               => 'sanduni.f@icbt.lk',
                'password'            => Hash::make('password'),
                'role'                => 'end_user',
                'registration_number' => 'STU-BMS-2024-012',
                'department'          => 'Biomedical Science Institute',
                'phone'               => '+94 78 333 2211',
                'approval_status'     => 'approved',
                'approved_at'         => now()->subMonths(2),
                'email_verified_at'   => now()->subMonths(2),
            ],
            [
                'name'                => 'Ravindu Mendis',
                'email'               => 'ravindu.m@icbt.lk',
                'password'            => Hash::make('password'),
                'role'                => 'end_user',
                'registration_number' => 'STU-CIV-2024-089',
                'department'          => 'Civil & Structural Engineering',
                'phone'               => '+94 75 222 1100',
                'approval_status'     => 'approved',
                'approved_at'         => now()->subMonths(2),
                'email_verified_at'   => now()->subMonths(2),
            ],
            [
                'name'                => 'Nimasha Perera',
                'email'               => 'nimasha.p@icbt.lk',
                'password'            => Hash::make('password'),
                'role'                => 'end_user',
                'registration_number' => 'PST-MBA-2025-003',
                'department'          => 'Postgraduate Business School',
                'phone'               => '+94 71 888 9900',
                'approval_status'     => 'approved',
                'approved_at'         => now()->subMonths(1),
                'email_verified_at'   => now()->subMonths(1),
            ],
            [
                'name'                => 'Tharindu Wickramasinghe',
                'email'               => 'tharindu.w@icbt.lk',
                'password'            => Hash::make('password'),
                'role'                => 'end_user',
                'registration_number' => 'STU-NET-2024-022',
                'department'          => 'Network & Cyber Security',
                'phone'               => '+94 72 111 4455',
                'approval_status'     => 'approved',
                'approved_at'         => now()->subDays(20),
                'email_verified_at'   => now()->subDays(20),
            ],
            // Pending Approvals
            [
                'name'                => 'Dinesh Jayawardena',
                'email'               => 'dinesh.jay@icbt.lk',
                'password'            => Hash::make('password'),
                'role'                => 'end_user',
                'registration_number' => 'VIS-AI-2026-004',
                'department'          => 'Visiting Faculty - AI & Data Science',
                'phone'               => '+94 77 999 1234',
                'approval_status'     => 'pending',
                'approved_at'         => null,
                'email_verified_at'   => now()->subDays(2),
            ],
            [
                'name'                => 'Kaveesha Maduranga',
                'email'               => 'kaveesha.m@icbt.lk',
                'password'            => Hash::make('password'),
                'role'                => 'end_user',
                'registration_number' => 'RES-ROB-2026-009',
                'department'          => 'Robotics Research Centre',
                'phone'               => '+94 76 888 4321',
                'approval_status'     => 'pending',
                'approved_at'         => null,
                'email_verified_at'   => now()->subDay(),
            ],
            [
                'name'                => 'Chathuri Wickrama',
                'email'               => 'chathuri.w@icbt.lk',
                'password'            => Hash::make('password'),
                'role'                => 'end_user',
                'registration_number' => 'PST-MSC-2026-015',
                'department'          => 'MSc Information Technology',
                'phone'               => '+94 70 777 6543',
                'approval_status'     => 'pending',
                'approved_at'         => null,
                'email_verified_at'   => now()->subHours(6),
            ],
        ];

        $users = [];
        foreach ($usersData as $u) {
            $user = User::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('email', $u['email'])->first();
            if ($user) {
                $user->update($u);
            } else {
                $user = User::create(array_merge($u, ['tenant_id' => $tenant->id]));
            }
            $users[$u['email']] = $user;
        }

        $adminUser      = $users['icbt@gmail.com'];
        $supervisorUser = $users['supervisor@icbt.demo'];
        $supervisor2    = $users['kavinda.sup@icbt.lk'];
        $imeshUser      = $users['imesh@gmail.com'];
        $kasunUser      = $users['kasun.k@icbt.lk'];
        $sanduniUser    = $users['sanduni.f@icbt.lk'];
        $nimashaUser    = $users['nimasha.p@icbt.lk'];
        $tharinduUser   = $users['tharindu.w@icbt.lk'];

        // 3. Priority Configs
        $priorityFactors = [
            ['factor' => 'role_admin',      'weight' => 100.00],
            ['factor' => 'role_supervisor', 'weight' => 50.00],
            ['factor' => 'role_end_user',   'weight' => 10.00],
            ['factor' => 'urgency_flag',    'weight' => 30.00],
            ['factor' => 'resource_demand', 'weight' => 2.50],
        ];
        foreach ($priorityFactors as $pf) {
            PriorityConfig::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'factor'    => $pf['factor'],
                ],
                [
                    'weight'    => $pf['weight'],
                ]
            );
        }

        // 4. Facilities
        $facilitiesData = [
            ['name' => 'Computing & Cyber Tower (Block A)',      'location' => 'ICBT Colombo Campus, Block A',    'description' => 'Main 7-storey IT faculty housing high-spec software labs, AI clusters, and tech lecture halls.'],
            ['name' => 'Engineering & Mechatronics Hub (Block B)','location' => 'ICBT Colombo Campus, Block B',    'description' => 'Specialized engineering complex with robotics arenas, CAD suites, and embedded hardware benches.'],
            ['name' => 'Grand Auditorium Complex (Block C)',     'location' => 'ICBT Colombo Campus, Block C',    'description' => '500-seat multi-tier auditorium equipped with 4K laser projection and line-array acoustics.'],
            ['name' => 'Postgraduate & Research Institute (D)',   'location' => 'ICBT Colombo Campus, Block D',    'description' => 'Dedicated executive suites, MBA conference rooms, PhD research booths, and video suites.'],
            ['name' => 'Digital Learning & Library (Block E)',   'location' => 'ICBT Colombo Campus, Block E',    'description' => 'Collaborative open study pods, silent research terminals, and digital reference access.'],
            ['name' => 'Multimedia & Broadcast Studio (Block F)', 'location' => 'ICBT Colombo Campus, Block F',    'description' => 'Acoustically isolated audio recording booths, podcast studios, and 4K virtual editing suites.'],
        ];

        $facilities = [];
        foreach ($facilitiesData as $f) {
            $facilities[$f['name']] = Facility::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $f['name']],
                $f
            );
        }

        // 5. Resource Categories
        $categoriesData = [
            'Smart Lecture Theatres',
            'High-Spec Computer Labs',
            'AI & Cloud Compute Clusters',
            'Robotics & Embedded Systems Labs',
            'Collaborative Study Pods',
            'Executive Boardrooms & Suites',
            'Digital Multimedia & Studios',
            'Portable AV & Presentation Rigs',
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c] = ResourceCategory::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $c],
                ['tenant_id' => $tenant->id, 'name' => $c]
            );
        }

        // 6. Resources
        $resourcesData = [
            // Block A: Computing
            [
                'fac' => 'Computing & Cyber Tower (Block A)',
                'cat' => 'Smart Lecture Theatres',
                'name' => 'Lecture Theatre A-101 (Computing)',
                'cap'  => 140,
                'status' => 'active',
                'desc' => '140-seat tiered theatre with dual 4K laser projectors, surround sound, and live recording rig.',
            ],
            [
                'fac' => 'Computing & Cyber Tower (Block A)',
                'cat' => 'Smart Lecture Theatres',
                'name' => 'Lecture Theatre A-102 (Software Eng)',
                'cap'  => 85,
                'status' => 'active',
                'desc' => '85-seat collaborative lecture hall with interactive podium and smart annotation displays.',
            ],
            [
                'fac' => 'Computing & Cyber Tower (Block A)',
                'cat' => 'High-Spec Computer Labs',
                'name' => 'Cyber Security & Ethical Hacking Lab',
                'cap'  => 36,
                'status' => 'active',
                'desc' => '36 workstations on isolated VLAN with hardware packet sniffers, Wireshark, and Kali Linux VMs.',
            ],
            [
                'fac' => 'Computing & Cyber Tower (Block A)',
                'cat' => 'AI & Cloud Compute Clusters',
                'name' => 'AI & Deep Learning GPU Lab',
                'cap'  => 30,
                'status' => 'active',
                'desc' => '30 nodes powered by NVIDIA RTX 4080s with CUDA, PyTorch, TensorFlow, and JupyterHub cluster.',
            ],
            [
                'fac' => 'Computing & Cyber Tower (Block A)',
                'cat' => 'High-Spec Computer Labs',
                'name' => 'Software Engineering Lab 1',
                'cap'  => 40,
                'status' => 'active',
                'desc' => '40 dual-monitor workstations equipped with IntelliJ, Docker, VS Code, and full-stack dev stacks.',
            ],
            [
                'fac' => 'Computing & Cyber Tower (Block A)',
                'cat' => 'High-Spec Computer Labs',
                'name' => 'Software Engineering Lab 2',
                'cap'  => 35,
                'status' => 'active',
                'desc' => '35 high-performance desktop terminals configured for database management and mobile app dev.',
            ],
            [
                'fac' => 'Computing & Cyber Tower (Block A)',
                'cat' => 'AI & Cloud Compute Clusters',
                'name' => 'Network Rack Server 4B',
                'cap'  => 1,
                'status' => 'maintenance',
                'desc' => 'Enterprise Cisco Nexus switch rack undergoing scheduled 10GbE SFP+ fiber loop diagnostics.',
            ],

            // Block B: Engineering & Mechatronics
            [
                'fac' => 'Engineering & Mechatronics Hub (Block B)',
                'cat' => 'Robotics & Embedded Systems Labs',
                'name' => 'Robotics & Mechatronics Arena',
                'cap'  => 24,
                'status' => 'active',
                'desc' => 'Autonomous navigation test floor, ABB 6-axis robotic arms, inverted pendulum rigs, and ROS2 stations.',
            ],
            [
                'fac' => 'Engineering & Mechatronics Hub (Block B)',
                'cat' => 'Robotics & Embedded Systems Labs',
                'name' => 'Embedded Systems & IoT Workbench',
                'cap'  => 28,
                'status' => 'active',
                'desc' => '28 bench stations equipped with Rigol oscilloscopes, logic analyzers, ESP32, STM32, and Arduino kits.',
            ],
            [
                'fac' => 'Engineering & Mechatronics Hub (Block B)',
                'cat' => 'Robotics & Embedded Systems Labs',
                'name' => '3D Prototyping & Laser Fab Lab',
                'cap'  => 15,
                'status' => 'active',
                'desc' => '8 Bambu Lab X1-Carbon 3D printers, CO2 laser engraver cutter, and PCB milling machine.',
            ],
            [
                'fac' => 'Engineering & Mechatronics Hub (Block B)',
                'cat' => 'Smart Lecture Theatres',
                'name' => 'Engineering Lecture Hall B-201',
                'cap'  => 100,
                'status' => 'active',
                'desc' => '100-seat theatre with CAD modeling live projection and digital whiteboard integration.',
            ],

            // Block C: Auditorium Complex
            [
                'fac' => 'Grand Auditorium Complex (Block C)',
                'cat' => 'Smart Lecture Theatres',
                'name' => 'ICBT Grand Auditorium C-Main',
                'cap'  => 480,
                'status' => 'active',
                'desc' => 'Premier 480-seat auditorium with dual Christie 4K laser projectors, Bose line-array audio, and motorized screen.',
            ],
            [
                'fac' => 'Grand Auditorium Complex (Block C)',
                'cat' => 'Smart Lecture Theatres',
                'name' => 'Laser Projector Rig C-Main',
                'cap'  => 1,
                'status' => 'maintenance',
                'desc' => 'High-lumen stage projector module undergoing optical lens realignment and cooling overhaul.',
            ],

            // Block D: Postgraduate
            [
                'fac' => 'Postgraduate & Research Institute (D)',
                'cat' => 'Executive Boardrooms & Suites',
                'name' => 'Executive Boardroom D-101',
                'cap'  => 20,
                'status' => 'active',
                'desc' => '20-person executive boardroom with Logitech Rally 4K conference bar and automated acoustic blinds.',
            ],
            [
                'fac' => 'Postgraduate & Research Institute (D)',
                'cat' => 'Executive Boardrooms & Suites',
                'name' => 'MBA Seminar Suite D-201',
                'cap'  => 45,
                'status' => 'active',
                'desc' => '45-seat horseshoe executive classroom with personalized microphone stations and dual side monitors.',
            ],
            [
                'fac' => 'Postgraduate & Research Institute (D)',
                'cat' => 'Collaborative Study Pods',
                'name' => 'PhD Research Pod Alpha',
                'cap'  => 8,
                'status' => 'active',
                'desc' => 'Soundproof collaborative room with 65" touch presentation display and digital whiteboard.',
            ],

            // Block E: Library & Study
            [
                'fac' => 'Digital Learning & Library (Block E)',
                'cat' => 'Collaborative Study Pods',
                'name' => 'Library Collaborative Pod 1',
                'cap'  => 6,
                'status' => 'active',
                'desc' => '6-person glass enclosed discussion pod with wireless Apple TV & Miracast casting display.',
            ],
            [
                'fac' => 'Digital Learning & Library (Block E)',
                'cat' => 'Collaborative Study Pods',
                'name' => 'Library Collaborative Pod 2',
                'cap'  => 6,
                'status' => 'active',
                'desc' => '6-person collaboration pod with acoustic felt panels and USB-C fast charging stations.',
            ],
            [
                'fac' => 'Digital Learning & Library (Block E)',
                'cat' => 'Collaborative Study Pods',
                'name' => 'Silent Study Cubicle E-01',
                'cap'  => 1,
                'status' => 'active',
                'desc' => 'Individual sound-dampened study booth with adjustable LED task lighting and ergonomic chair.',
            ],
            [
                'fac' => 'Digital Learning & Library (Block E)',
                'cat' => 'High-Spec Computer Labs',
                'name' => 'Library Digital Reference PC Suite',
                'cap'  => 30,
                'status' => 'active',
                'desc' => '30 all-in-one desktop terminals with access to IEEE Xplore, ScienceDirect, and ACM Digital Library.',
            ],

            // Block F: Media & Portable
            [
                'fac' => 'Multimedia & Broadcast Studio (Block F)',
                'cat' => 'Digital Multimedia & Studios',
                'name' => '4K Virtual Broadcast Studio F-01',
                'cap'  => 10,
                'status' => 'active',
                'desc' => 'Green screen infinity studio with Blackmagic Pocket 6K rigs, teleprompters, and ATEM Mini Extreme switcher.',
            ],
            [
                'fac' => 'Multimedia & Broadcast Studio (Block F)',
                'cat' => 'Digital Multimedia & Studios',
                'name' => 'Podcast & Audio Studio F-02',
                'cap'  => 4,
                'status' => 'active',
                'desc' => 'Professional acoustic booth with 4x Shure SM7B microphones, Cloudlifters, and RØDECaster Pro II.',
            ],
            [
                'fac' => 'Computing & Cyber Tower (Block A)',
                'cat' => 'Portable AV & Presentation Rigs',
                'name' => 'Portable 4K Presentation Rig 1',
                'cap'  => 1,
                'status' => 'active',
                'desc' => 'Optoma 4K UHD portable projector kit with wireless HDMI transmitter and motorized roll-up screen.',
            ],
            [
                'fac' => 'Computing & Cyber Tower (Block A)',
                'cat' => 'Portable AV & Presentation Rigs',
                'name' => 'Portable Wireless PA Audio Kit A',
                'cap'  => 1,
                'status' => 'active',
                'desc' => 'JBL EON ONE active PA column with 2x Sennheiser wireless handheld mics and Bluetooth mixer.',
            ],
        ];

        $resources = [];
        foreach ($resourcesData as $r) {
            $fac = $facilities[$r['fac']];
            $cat = $categories[$r['cat']];

            $res = Resource::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name'      => $r['name'],
                ],
                [
                    'facility_id' => $fac->id,
                    'category_id' => $cat->id,
                    'capacity'    => $r['cap'],
                    'status'      => $r['status'],
                    'description' => $r['desc'],
                ]
            );
            $resources[$r['name']] = $res;
        }

        // 7. Booking Templates
        $templatesData = [
            [
                'name' => 'Final Year Dissertation Viva Suite',
                'desc' => 'Bundles a Smart Lecture Theatre, Portable 4K Presentation Rig, and Collaborative Study Pod for degree defenses.',
                'items' => [
                    'Smart Lecture Theatres' => 1,
                    'Portable AV & Presentation Rigs' => 1,
                    'Collaborative Study Pods' => 1,
                ],
            ],
            [
                'name' => 'AI & Data Science Hackathon Lab Bundle',
                'desc' => 'Reserves the AI GPU Cluster alongside a Software Lab and Wireless PA Audio Kit for campus-wide hackathons.',
                'items' => [
                    'AI & Cloud Compute Clusters' => 1,
                    'High-Spec Computer Labs' => 1,
                    'Portable AV & Presentation Rigs' => 1,
                ],
            ],
            [
                'name' => 'International Research Keynote & Symposium',
                'desc' => 'Allocates the Grand Auditorium, Executive Boardroom, and Broadcast Studio for visiting professors and conferences.',
                'items' => [
                    'Smart Lecture Theatres' => 1,
                    'Executive Boardrooms & Suites' => 1,
                    'Digital Multimedia & Studios' => 1,
                ],
            ],
            [
                'name' => 'Robotics & Embedded Systems Workshop Kit',
                'desc' => 'Coordinates the Robotics Arena with IoT Workbenches and 3D Prototyping Fab Lab for practical student workshops.',
                'items' => [
                    'Robotics & Embedded Systems Labs' => 2,
                    'High-Spec Computer Labs' => 1,
                ],
            ],
        ];

        $templates = [];
        foreach ($templatesData as $t) {
            $tpl = BookingTemplate::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name'      => $t['name'],
                ],
                [
                    'description' => $t['desc'],
                ]
            );

            // Create template items
            foreach ($t['items'] as $catName => $qty) {
                if (isset($categories[$catName])) {
                    $tpl->items()->updateOrCreate(
                        ['category_id' => $categories[$catName]->id],
                        ['quantity' => $qty]
                    );
                }
            }
            $templates[$t['name']] = $tpl;
        }

        // 8. Bookings (Historical + Today + Future + Pending Approvals)
        $now = Carbon::now();
        $today = Carbon::today();

        // A. Historical Bookings (Past 30 Days) — gives analytics rich curves
        $historicalRecords = [
            // Week -4
            ['res' => 'Lecture Theatre A-101 (Computing)', 'user' => $supervisorUser, 'days_ago' => 28, 'h_start' => 9, 'h_end' => 12, 'notes' => 'CS301 Software Architecture Lecture', 'status' => 'confirmed', 'p' => 88.5],
            ['res' => 'Software Engineering Lab 1',         'user' => $kasunUser,      'days_ago' => 28, 'h_start' => 13, 'h_end' => 16, 'notes' => 'Full-Stack React & Node Practical', 'status' => 'confirmed', 'p' => 76.0],
            ['res' => 'AI & Deep Learning GPU Lab',         'user' => $imeshUser,      'days_ago' => 27, 'h_start' => 10, 'h_end' => 14, 'notes' => 'Computer Vision CNN Model Training', 'status' => 'confirmed', 'p' => 92.0],
            ['res' => 'Cyber Security & Ethical Hacking Lab','user' => $tharinduUser,  'days_ago' => 26, 'h_start' => 14, 'h_end' => 17, 'notes' => 'Penetration Testing CTF Lab', 'status' => 'confirmed', 'p' => 81.5],
            ['res' => 'Robotics & Mechatronics Arena',      'user' => $supervisor2,   'days_ago' => 25, 'h_start' => 9, 'h_end' => 13, 'notes' => 'Kinematics & Robot Path Planning Practical', 'status' => 'confirmed', 'p' => 89.0],
            ['res' => 'Executive Boardroom D-101',          'user' => $adminUser,      'days_ago' => 24, 'h_start' => 14, 'h_end' => 16, 'notes' => 'Faculty Quality Review Board Meeting', 'status' => 'confirmed', 'p' => 95.0],
            ['res' => 'Library Collaborative Pod 1',        'user' => $sanduniUser,    'days_ago' => 24, 'h_start' => 11, 'h_end' => 13, 'notes' => 'Biomedical Group Literature Review', 'status' => 'confirmed', 'p' => 70.0],

            // Week -3
            ['res' => 'Lecture Theatre A-102 (Software Eng)', 'user' => $supervisorUser, 'days_ago' => 21, 'h_start' => 10, 'h_end' => 12, 'notes' => 'Cloud Computing & Microservices Session', 'status' => 'confirmed', 'p' => 84.0],
            ['res' => 'Software Engineering Lab 2',           'user' => $imeshUser,      'days_ago' => 21, 'h_start' => 14, 'h_end' => 17, 'notes' => 'DevOps Docker & CI/CD Pipelines Practical', 'status' => 'confirmed', 'p' => 87.5],
            ['res' => 'AI & Deep Learning GPU Lab',           'user' => $kasunUser,      'days_ago' => 20, 'h_start' => 9, 'h_end' => 13, 'notes' => 'LLM Fine-Tuning with LoRA Experiments', 'status' => 'confirmed', 'p' => 91.0],
            ['res' => 'ICBT Grand Auditorium C-Main',         'user' => $adminUser,      'days_ago' => 19, 'h_start' => 13, 'h_end' => 17, 'notes' => 'Industry Tech Summit & Graduate Orientation', 'status' => 'confirmed', 'p' => 98.0],
            ['res' => 'Embedded Systems & IoT Workbench',     'user' => $supervisor2,   'days_ago' => 18, 'h_start' => 10, 'h_end' => 14, 'notes' => 'ESP32 MQTT Sensor Telemetry Lab', 'status' => 'confirmed', 'p' => 85.0],
            ['res' => 'Podcast & Audio Studio F-02',          'user' => $nimashaUser,    'days_ago' => 17, 'h_start' => 15, 'h_end' => 17, 'notes' => 'ICBT Student Tech Voices Podcast Episode 4', 'status' => 'confirmed', 'p' => 72.0],

            // Week -2
            ['res' => 'Lecture Theatre A-101 (Computing)', 'user' => $supervisorUser, 'days_ago' => 14, 'h_start' => 9, 'h_end' => 12, 'notes' => 'Distributed Systems & Consensus Algorithms', 'status' => 'confirmed', 'p' => 88.0],
            ['res' => 'Cyber Security & Ethical Hacking Lab','user' => $tharinduUser,  'days_ago' => 14, 'h_start' => 13, 'h_end' => 16, 'notes' => 'Buffer Overflow & Binary Exploitation Demo', 'status' => 'confirmed', 'p' => 83.0],
            ['res' => 'Software Engineering Lab 1',         'user' => $imeshUser,      'days_ago' => 13, 'h_start' => 10, 'h_end' => 13, 'notes' => 'Agile Sprint Planning & Code Review Clinic', 'status' => 'confirmed', 'p' => 86.0],
            ['res' => 'MBA Seminar Suite D-201',            'user' => $nimashaUser,    'days_ago' => 12, 'h_start' => 17, 'h_end' => 20, 'notes' => 'Strategic Management Executive Case Study', 'status' => 'confirmed', 'p' => 89.5],
            ['res' => '3D Prototyping & Laser Fab Lab',     'user' => $kasunUser,      'days_ago' => 11, 'h_start' => 14, 'h_end' => 17, 'notes' => 'Robotic Chassis Rapid Prototyping', 'status' => 'confirmed', 'p' => 78.0],
            ['res' => '4K Virtual Broadcast Studio F-01',   'user' => $adminUser,      'days_ago' => 10, 'h_start' => 11, 'h_end' => 13, 'notes' => 'Recording Online Lecture Series: AI for Everyone', 'status' => 'confirmed', 'p' => 90.0],
            ['res' => 'PhD Research Pod Alpha',             'user' => $sanduniUser,    'days_ago' => 9,  'h_start' => 13, 'h_end' => 16, 'notes' => 'Bioinformatics Gene Sequencing Data Analysis', 'status' => 'confirmed', 'p' => 82.0],

            // Week -1
            ['res' => 'Lecture Theatre A-101 (Computing)', 'user' => $supervisorUser, 'days_ago' => 7,  'h_start' => 9, 'h_end' => 12, 'notes' => 'Advanced Database Query Optimization', 'status' => 'confirmed', 'p' => 87.0],
            ['res' => 'AI & Deep Learning GPU Lab',         'user' => $imeshUser,      'days_ago' => 6,  'h_start' => 10, 'h_end' => 15, 'notes' => 'Reinforcement Learning Multi-Agent Simulation', 'status' => 'confirmed', 'p' => 94.0],
            ['res' => 'Software Engineering Lab 1',         'user' => $kasunUser,      'days_ago' => 5,  'h_start' => 13, 'h_end' => 16, 'notes' => 'Automated Unit Testing & Mocking Frameworks', 'status' => 'confirmed', 'p' => 79.0],
            ['res' => 'Robotics & Mechatronics Arena',      'user' => $supervisor2,   'days_ago' => 4,  'h_start' => 9, 'h_end' => 13, 'notes' => 'PID Controller Tuning on Servo Inverters', 'status' => 'confirmed', 'p' => 88.0],
            ['res' => 'Library Collaborative Pod 2',        'user' => $tharinduUser,  'days_ago' => 3,  'h_start' => 14, 'h_end' => 17, 'notes' => 'Final Year Dissertation Technical Discussion', 'status' => 'confirmed', 'p' => 75.0],
            ['res' => 'Executive Boardroom D-101',          'user' => $adminUser,      'days_ago' => 2,  'h_start' => 10, 'h_end' => 12, 'notes' => 'Academic Senate Curriculum Revision Session', 'status' => 'confirmed', 'p' => 96.0],
            ['res' => 'Embedded Systems & IoT Workbench',     'user' => $kasunUser,      'days_ago' => 1,  'h_start' => 14, 'h_end' => 18, 'notes' => 'LoRaWAN Long-Range Gateways Setup', 'status' => 'confirmed', 'p' => 84.0],
        ];

        foreach ($historicalRecords as $h) {
            if (!isset($resources[$h['res']])) continue;
            $resModel = $resources[$h['res']];
            $startDate = $today->copy()->subDays($h['days_ago'])->setTime($h['h_start'], 0);
            $endDate   = $today->copy()->subDays($h['days_ago'])->setTime($h['h_end'], 0);

            Booking::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id'   => $tenant->id,
                    'resource_id' => $resModel->id,
                    'start_at'    => $startDate,
                ],
                [
                    'user_id'        => $h['user']->id,
                    'end_at'         => $endDate,
                    'status'         => $h['status'],
                    'notes'          => $h['notes'],
                    'priority'       => 2,
                    'urgency'        => false,
                    'priority_score' => $h['p'],
                ]
            );
        }

        // B. Today's Bookings (Active & Upcoming Slots)
        $todayBookings = [
            [
                'res' => 'Lecture Theatre A-101 (Computing)',
                'user' => $supervisorUser,
                'start' => $today->copy()->setTime(9, 0),
                'end'   => $today->copy()->setTime(11, 30),
                'status'=> 'confirmed',
                'notes' => 'BSE-301: Formal Methods & High Integrity Software Design',
                'score' => 91.5,
            ],
            [
                'res' => 'Software Engineering Lab 1',
                'user' => $imeshUser,
                'start' => $today->copy()->setTime(10, 0),
                'end'   => $today->copy()->setTime(13, 0),
                'status'=> 'confirmed',
                'notes' => 'Final Year Capstone Project Development & Mentorship',
                'score' => 93.0,
            ],
            [
                'res' => 'AI & Deep Learning GPU Lab',
                'user' => $kasunUser,
                'start' => $today->copy()->setTime(14, 0),
                'end'   => $today->copy()->setTime(17, 30),
                'status'=> 'confirmed',
                'notes' => 'Object Detection YOLOv9 Inference Optimization Workshop',
                'score' => 88.0,
            ],
            [
                'res' => 'Library Collaborative Pod 1',
                'user' => $sanduniUser,
                'start' => $today->copy()->setTime(11, 0),
                'end'   => $today->copy()->setTime(13, 0),
                'status'=> 'confirmed',
                'notes' => 'Medical Genomics Project Sprint 3 Sync',
                'score' => 74.0,
            ],
            [
                'res' => 'Executive Boardroom D-101',
                'user' => $adminUser,
                'start' => $today->copy()->setTime(15, 0),
                'end'   => $today->copy()->setTime(17, 0),
                'status'=> 'confirmed',
                'notes' => 'Cardiff Met Partnership Quality Inspection Briefing',
                'score' => 97.0,
            ],
            [
                'res' => 'Robotics & Mechatronics Arena',
                'user' => $supervisor2,
                'start' => $today->copy()->setTime(14, 30),
                'end'   => $today->copy()->setTime(17, 0),
                'status'=> 'confirmed',
                'notes' => 'Autonomous Mobile Robot (AMR) SLAM LiDAR Mapping Tests',
                'score' => 89.5,
            ],
        ];

        foreach ($todayBookings as $tb) {
            if (!isset($resources[$tb['res']])) continue;
            $resModel = $resources[$tb['res']];

            Booking::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id'   => $tenant->id,
                    'resource_id' => $resModel->id,
                    'start_at'    => $tb['start'],
                ],
                [
                    'user_id'        => $tb['user']->id,
                    'end_at'         => $tb['end'],
                    'status'         => $tb['status'],
                    'notes'          => $tb['notes'],
                    'priority'       => 2,
                    'urgency'        => false,
                    'priority_score' => $tb['score'],
                ]
            );
        }

        // C. Upcoming Bookings (Next 14 Days)
        $futureBookings = [
            // Tomorrow
            ['res' => 'Lecture Theatre A-102 (Software Eng)', 'user' => $supervisorUser, 'days_ahead' => 1, 'h_start' => 9, 'h_end' => 11, 'notes' => 'Software Security & Threat Modeling Lecture', 'status' => 'confirmed', 'p' => 88.0],
            ['res' => 'Software Engineering Lab 2',           'user' => $imeshUser,      'days_ahead' => 1, 'h_start' => 11, 'h_end' => 14, 'notes' => 'Kubernetes Helm Charts Hands-on Lab', 'status' => 'confirmed', 'p' => 92.5],
            ['res' => 'Cyber Security & Ethical Hacking Lab',  'user' => $tharinduUser,  'days_ahead' => 1, 'h_start' => 14, 'h_end' => 17, 'notes' => 'Defensive SOC Log Analysis & SIEM Practicals', 'status' => 'confirmed', 'p' => 84.0],
            ['res' => 'Library Collaborative Pod 2',          'user' => $kasunUser,      'days_ahead' => 1, 'h_start' => 15, 'h_end' => 17, 'notes' => 'Algorithms & Data Structures Revision', 'status' => 'confirmed', 'p' => 73.0],

            // Day +2
            ['res' => 'AI & Deep Learning GPU Lab',         'user' => $imeshUser,      'days_ahead' => 2, 'h_start' => 10, 'h_end' => 14, 'notes' => 'Multimodal Foundation Models Demo', 'status' => 'confirmed', 'p' => 94.0],
            ['res' => 'Embedded Systems & IoT Workbench',     'user' => $supervisor2,   'days_ahead' => 2, 'h_start' => 13, 'h_end' => 16, 'notes' => 'Real-Time FreeRTOS Kernel Scheduling Practicals', 'status' => 'confirmed', 'p' => 87.0],
            ['res' => 'MBA Seminar Suite D-201',            'user' => $nimashaUser,    'days_ahead' => 2, 'h_start' => 17, 'h_end' => 20, 'notes' => 'Corporate Financial Accounting Seminar', 'status' => 'confirmed', 'p' => 86.5],

            // Day +4
            ['res' => 'ICBT Grand Auditorium C-Main',       'user' => $adminUser,      'days_ahead' => 4, 'h_start' => 13, 'h_end' => 18, 'notes' => 'Annual National Research Conference 2026 Opening', 'status' => 'confirmed', 'p' => 99.0],
            ['res' => 'Executive Boardroom D-101',          'user' => $adminUser,      'days_ahead' => 4, 'h_start' => 10, 'h_end' => 12, 'notes' => 'Keynote Speakers VIP Reception', 'status' => 'confirmed', 'p' => 95.0],

            // Day +7
            ['res' => 'Lecture Theatre A-101 (Computing)', 'user' => $supervisorUser, 'days_ahead' => 7, 'h_start' => 9, 'h_end' => 12, 'notes' => 'BSE Final Year Viva Stage 1', 'status' => 'confirmed', 'p' => 95.0],
            ['res' => 'Software Engineering Lab 1',         'user' => $kasunUser,      'days_ahead' => 7, 'h_start' => 13, 'h_end' => 16, 'notes' => 'Viva Live Demonstration Rig Setup', 'status' => 'confirmed', 'p' => 89.0],
        ];

        foreach ($futureBookings as $fb) {
            if (!isset($resources[$fb['res']])) continue;
            $resModel = $resources[$fb['res']];
            $startDate = $today->copy()->addDays($fb['days_ahead'])->setTime($fb['h_start'], 0);
            $endDate   = $today->copy()->addDays($fb['days_ahead'])->setTime($fb['h_end'], 0);

            Booking::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id'   => $tenant->id,
                    'resource_id' => $resModel->id,
                    'start_at'    => $startDate,
                ],
                [
                    'user_id'        => $fb['user']->id,
                    'end_at'         => $endDate,
                    'status'         => $fb['status'],
                    'notes'          => $fb['notes'],
                    'priority'       => 2,
                    'urgency'        => false,
                    'priority_score' => $fb['p'],
                ]
            );
        }

        // D. Pending Approvals Queue (So Approvals page & Dashboard badge are filled!)
        $pendingApprovals = [
            [
                'res'     => 'AI & Deep Learning GPU Lab',
                'user'    => $kasunUser,
                'start'   => $today->copy()->addDays(2)->setTime(15, 0),
                'end'     => $today->copy()->addDays(2)->setTime(18, 0),
                'notes'   => 'Urgent: IEEE Student Branch 24h AI Hackathon Preliminary Benchmark Run',
                'urgency' => true,
                'score'   => 92.5,
            ],
            [
                'res'     => '4K Virtual Broadcast Studio F-01',
                'user'    => $sanduniUser,
                'start'   => $today->copy()->addDays(3)->setTime(10, 0),
                'end'     => $today->copy()->addDays(3)->setTime(12, 30),
                'notes'   => 'Recording 3-Minute Thesis (3MT) Research Pitch for Global Competition',
                'urgency' => true,
                'score'   => 88.0,
            ],
            [
                'res'     => 'Robotics & Mechatronics Arena',
                'user'    => $tharinduUser,
                'start'   => $today->copy()->addDays(4)->setTime(14, 0),
                'end'     => $today->copy()->addDays(4)->setTime(17, 0),
                'notes'   => 'Inter-University Robocon Autonomous Rover Final Calibration Session',
                'urgency' => false,
                'score'   => 85.0,
            ],
            [
                'res'     => 'MBA Seminar Suite D-201',
                'user'    => $nimashaUser,
                'start'   => $today->copy()->addDays(5)->setTime(18, 0),
                'end'     => $today->copy()->addDays(5)->setTime(21, 0),
                'notes'   => 'Postgraduate Alumni Career Networking & Mentorship Mixer',
                'urgency' => false,
                'score'   => 81.0,
            ],
        ];

        foreach ($pendingApprovals as $pa) {
            if (!isset($resources[$pa['res']])) continue;
            $resModel = $resources[$pa['res']];

            Booking::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id'   => $tenant->id,
                    'resource_id' => $resModel->id,
                    'start_at'    => $pa['start'],
                ],
                [
                    'user_id'        => $pa['user']->id,
                    'end_at'         => $pa['end'],
                    'status'         => 'pending',
                    'notes'          => $pa['notes'],
                    'priority'       => 1,
                    'urgency'        => $pa['urgency'],
                    'priority_score' => $pa['score'],
                ]
            );
        }

        // 9. Composite Bookings
        $compBookingsData = [
            [
                'user'     => $supervisorUser,
                'start'    => $today->copy()->addDays(3)->setTime(9, 0),
                'end'      => $today->copy()->addDays(3)->setTime(13, 0),
                'status'   => 'confirmed',
                'notes'    => 'Composite Booking: Final Year Software Engineering Degree Viva',
                'resources'=> [
                    'Lecture Theatre A-101 (Computing)',
                    'Portable 4K Presentation Rig 1',
                    'Library Collaborative Pod 1',
                ],
            ],
            [
                'user'     => $imeshUser,
                'start'    => $today->copy()->addDays(6)->setTime(10, 0),
                'end'      => $today->copy()->addDays(6)->setTime(16, 0),
                'status'   => 'confirmed',
                'notes'    => 'Composite Booking: National Datathon & Generative AI Workshop',
                'resources'=> [
                    'AI & Deep Learning GPU Lab',
                    'Software Engineering Lab 1',
                    'Portable Wireless PA Audio Kit A',
                ],
            ],
        ];

        foreach ($compBookingsData as $cbd) {
            $cb = CompositeBooking::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'user_id'   => $cbd['user']->id,
                    'start_at'  => $cbd['start'],
                ],
                [
                    'end_at'   => $cbd['end'],
                    'status'   => $cbd['status'],
                    'notes'    => $cbd['notes'],
                ]
            );

            // Create individual linked child bookings
            foreach ($cbd['resources'] as $resName) {
                if (isset($resources[$resName])) {
                    $resM = $resources[$resName];
                    Booking::withoutGlobalScopes()->updateOrCreate(
                        [
                            'tenant_id'            => $tenant->id,
                            'resource_id'          => $resM->id,
                            'start_at'             => $cbd['start'],
                        ],
                        [
                            'composite_booking_id' => $cb->id,
                            'user_id'              => $cbd['user']->id,
                            'end_at'               => $cbd['end'],
                            'status'               => $cbd['status'],
                            'notes'                => "[Part of Composite Booking: {$cb->id}] {$cbd['notes']}",
                            'priority'             => 1,
                            'urgency'              => false,
                            'priority_score'       => 94.0,
                        ]
                    );
                }
            }
        }

        // 10. Maintenance Orders
        $maintenanceOrders = [
            [
                'res'          => 'Network Rack Server 4B',
                'assigned'     => $supervisorUser,
                'type'         => 'corrective',
                'status'       => 'in_progress',
                'scheduled_at' => $today->copy()->subDays(1)->setTime(10, 0),
                'completed_at' => null,
                'cost'         => 350.00,
                'notes'        => 'Replacing Cisco SFP+ 10GbE fiber transceivers and running SAN loopback test.',
            ],
            [
                'res'          => 'Laser Projector Rig C-Main',
                'assigned'     => $supervisor2,
                'type'         => 'preventive',
                'status'       => 'scheduled',
                'scheduled_at' => $today->copy()->addDays(2)->setTime(8, 30),
                'completed_at' => null,
                'cost'         => 220.00,
                'notes'        => 'Annual optical lens cleaning, internal blower filter replacement, and color calibration.',
            ],
            [
                'res'          => 'Robotics & Mechatronics Arena',
                'assigned'     => $supervisor2,
                'type'         => 'preventive',
                'status'       => 'scheduled',
                'scheduled_at' => $today->copy()->addDays(5)->setTime(9, 0),
                'completed_at' => null,
                'cost'         => 180.00,
                'notes'        => 'Servo motor lubrication and safety perimeter laser interlock verification.',
            ],
            [
                'res'          => 'Cyber Security & Ethical Hacking Lab',
                'assigned'     => $supervisorUser,
                'type'         => 'corrective',
                'status'       => 'completed',
                'scheduled_at' => $today->copy()->subDays(12)->setTime(9, 0),
                'completed_at' => $today->copy()->subDays(12)->setTime(14, 0),
                'cost'         => 450.00,
                'notes'        => 'Upgraded edge firewall firmware to patched CVE-2026 branch and re-tested packet filters.',
            ],
            [
                'res'          => '3D Prototyping & Laser Fab Lab',
                'assigned'     => $supervisor2,
                'type'         => 'preventive',
                'status'       => 'completed',
                'scheduled_at' => $today->copy()->subDays(18)->setTime(13, 0),
                'completed_at' => $today->copy()->subDays(18)->setTime(17, 30),
                'cost'         => 120.00,
                'notes'        => 'Replaced hardened steel 0.4mm hotends on Bambu Lab 3D printers and calibrated bed leveling.',
            ],
            [
                'res'          => 'Podcast & Audio Studio F-02',
                'assigned'     => $supervisorUser,
                'type'         => 'inspection',
                'status'       => 'completed',
                'scheduled_at' => $today->copy()->subDays(22)->setTime(11, 0),
                'completed_at' => $today->copy()->subDays(22)->setTime(13, 0),
                'cost'         => 60.00,
                'notes'        => 'Acoustic seal inspection on studio double door and XLR cable line impedance testing.',
            ],
        ];

        foreach ($maintenanceOrders as $mo) {
            if (!isset($resources[$mo['res']])) continue;
            $resM = $resources[$mo['res']];

            MaintenanceOrder::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id'    => $tenant->id,
                    'resource_id'  => $resM->id,
                    'scheduled_at' => $mo['scheduled_at'],
                ],
                [
                    'assigned_to'  => $mo['assigned']->id,
                    'type'         => $mo['type'],
                    'status'       => $mo['status'],
                    'completed_at' => $mo['completed_at'],
                    'cost'         => $mo['cost'],
                    'notes'        => $mo['notes'],
                ]
            );
        }

        // 11. Audit Logs (Rich historical timeline)
        $auditTrail = [
            ['user' => $adminUser,      'action' => 'tenant.settings_updated',      'model_type' => Tenant::class,          'model_id' => $tenant->id, 'days_ago' => 30, 'payload' => ['changed' => ['operating_hours' => '08:00 - 21:00', 'max_advance_days' => 90]]],
            ['user' => $adminUser,      'action' => 'priority_config.updated',      'model_type' => PriorityConfig::class,  'model_id' => 1,           'days_ago' => 28, 'payload' => ['weights' => ['role' => 35, 'academic' => 20, 'urgency' => 15]]],
            ['user' => $adminUser,      'action' => 'user.approved',                'model_type' => User::class,            'model_id' => $imeshUser->id, 'days_ago' => 25, 'payload' => ['approved_by' => $adminUser->name, 'role' => 'end_user']],
            ['user' => $supervisorUser, 'action' => 'booking.created',              'model_type' => Booking::class,         'model_id' => 1,           'days_ago' => 21, 'payload' => ['resource' => 'Lecture Theatre A-101 (Computing)', 'status' => 'confirmed']],
            ['user' => $supervisorUser, 'action' => 'booking.approved',             'model_type' => Booking::class,         'model_id' => 3,           'days_ago' => 20, 'payload' => ['approved_by' => $supervisorUser->name, 'priority_score' => 91.0]],
            ['user' => $supervisor2,    'action' => 'maintenance.completed',        'model_type' => MaintenanceOrder::class,'model_id' => 5,           'days_ago' => 18, 'payload' => ['cost' => 120.00, 'resource' => '3D Prototyping & Laser Fab Lab']],
            ['user' => $adminUser,      'action' => 'template.created',             'model_type' => BookingTemplate::class, 'model_id' => 1,           'days_ago' => 15, 'payload' => ['name' => 'Final Year Dissertation Viva Suite']],
            ['user' => $supervisorUser, 'action' => 'maintenance.completed',        'model_type' => MaintenanceOrder::class,'model_id' => 4,           'days_ago' => 12, 'payload' => ['cost' => 450.00, 'resource' => 'Cyber Security & Ethical Hacking Lab']],
            ['user' => $imeshUser,      'action' => 'composite_booking.created',    'model_type' => CompositeBooking::class,'model_id' => 1,           'days_ago' => 6,  'payload' => ['resources_count' => 3, 'purpose' => 'National Datathon & AI Workshop']],
            ['user' => $adminUser,      'action' => 'user.role_updated',            'model_type' => User::class,            'model_id' => $supervisorUser->id, 'days_ago' => 4, 'payload' => ['old_role' => 'end_user', 'new_role' => 'supervisor']],
            ['user' => $kasunUser,      'action' => 'booking.pending_approval',     'model_type' => Booking::class,         'model_id' => 25,          'days_ago' => 2,  'payload' => ['resource' => 'AI & Deep Learning GPU Lab', 'priority_score' => 92.5, 'urgency' => true]],
            ['user' => $adminUser,      'action' => 'predictive_maintenance.scan',  'model_type' => Resource::class,        'model_id' => 4,           'days_ago' => 1,  'payload' => ['anomalies_detected' => 1, 'resource' => 'AI & Deep Learning GPU Lab']],
            ['user' => $adminUser,      'action' => 'maintenance.started',          'model_type' => MaintenanceOrder::class,'model_id' => 1,           'days_ago' => 1,  'payload' => ['status' => 'in_progress', 'resource' => 'Network Rack Server 4B']],
        ];

        foreach ($auditTrail as $at) {
            AuditLog::withoutGlobalScopes()->create([
                'tenant_id'  => $tenant->id,
                'user_id'    => $at['user']->id,
                'action'     => $at['action'],
                'model_type' => $at['model_type'],
                'model_id'   => $at['model_id'],
                'payload'    => $at['payload'],
                'ip_address' => '192.168.10.12',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                'created_at' => $today->copy()->subDays($at['days_ago'])->setTime(11, 24),
            ]);
        }

        // 12. Notifications (For top navbar notification bell badge)
        $adminNotificationData = [
            [
                'type' => 'App\Notifications\BookingApprovalRequested',
                'data' => json_encode([
                    'title'       => 'Urgent Booking Approval Requested',
                    'message'     => 'Kasun Karunaratne submitted an urgent booking for AI & Deep Learning GPU Lab (Score: 92.5).',
                    'booking_id'  => 101,
                    'created_at'  => now()->subHours(2)->toIso8601String(),
                ]),
                'read_at' => null,
            ],
            [
                'type' => 'App\Notifications\UserRegistrationPending',
                'data' => json_encode([
                    'title'       => 'New Member Registration',
                    'message'     => 'Dinesh Jayawardena (Visiting Faculty - AI) requested workspace access.',
                    'user_id'     => $users['dinesh.jay@icbt.lk']->id,
                    'created_at'  => now()->subHours(5)->toIso8601String(),
                ]),
                'read_at' => null,
            ],
            [
                'type' => 'App\Notifications\MaintenanceAlert',
                'data' => json_encode([
                    'title'       => 'Predictive Maintenance Alert',
                    'message'     => 'AI GPU Lab usage hours exceeded category anomaly threshold. Auto-check scheduled.',
                    'resource_id' => $resources['AI & Deep Learning GPU Lab']->id,
                    'created_at'  => now()->subDay()->toIso8601String(),
                ]),
                'read_at' => now()->subHours(12),
            ],
            [
                'type' => 'App\Notifications\MaintenanceCompleted',
                'data' => json_encode([
                    'title'       => 'Maintenance Completed',
                    'message'     => 'Cyber Security Lab firewall security patches successfully deployed ($450.00).',
                    'resource_id' => $resources['Cyber Security & Ethical Hacking Lab']->id,
                    'created_at'  => now()->subDays(3)->toIso8601String(),
                ]),
                'read_at' => now()->subDays(2),
            ],
        ];

        foreach ($adminNotificationData as $nd) {
            DB::table('notifications')->insert([
                'id'              => (string) Str::uuid(),
                'type'            => $nd['type'],
                'notifiable_type' => User::class,
                'notifiable_id'   => $adminUser->id,
                'data'            => $nd['data'],
                'read_at'         => $nd['read_at'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}
