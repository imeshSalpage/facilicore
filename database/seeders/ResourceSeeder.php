<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'university')->firstOrFail();

        // Helper closures to fetch by name without global scope
        $facility = fn($name) => Facility::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)->where('name', $name)->first();
        $category = fn($name) => ResourceCategory::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)->where('name', $name)->first();

        $resources = [
            // ─── Main Library ───────────────────────────────────────────────
            [
                'name'         => 'Group Study Room A',
                'description'  => '6-person study room with whiteboard, TV screen, and HDMI connection.',
                'status'       => 'active',
                'capacity'     => 6,
                'specifications' => ['floor' => '2nd', 'whiteboard' => true, 'screen' => true],
                'facility'     => 'Main Library',
                'category'     => 'Study Rooms',
            ],
            [
                'name'         => 'Group Study Room B',
                'description'  => '6-person study room with whiteboard and natural light.',
                'status'       => 'active',
                'capacity'     => 6,
                'specifications' => ['floor' => '2nd', 'whiteboard' => true],
                'facility'     => 'Main Library',
                'category'     => 'Study Rooms',
            ],
            [
                'name'         => 'Individual Pod 1',
                'description'  => 'Silent individual study pod with power outlets and USB charging.',
                'status'       => 'active',
                'capacity'     => 1,
                'specifications' => ['floor' => '3rd', 'silent_zone' => true],
                'facility'     => 'Main Library',
                'category'     => 'Study Rooms',
            ],
            [
                'name'         => 'Individual Pod 2',
                'description'  => 'Silent individual study pod with power outlets and USB charging.',
                'status'       => 'active',
                'capacity'     => 1,
                'specifications' => ['floor' => '3rd', 'silent_zone' => true],
                'facility'     => 'Main Library',
                'category'     => 'Study Rooms',
            ],
            [
                'name'         => 'Library Seminar Room L1',
                'description'  => 'Seminar room with projector, 20 seats, and group discussion layout.',
                'status'       => 'active',
                'capacity'     => 20,
                'specifications' => ['floor' => '1st', 'projector' => true, 'ac' => true],
                'facility'     => 'Main Library',
                'category'     => 'Seminar Rooms',
            ],

            // ─── Engineering Block ──────────────────────────────────────────
            [
                'name'         => 'Lecture Theatre E-LT1',
                'description'  => '200-seat tiered lecture theatre with dual projector screens and PA system.',
                'status'       => 'active',
                'capacity'     => 200,
                'specifications' => ['floor' => 'G', 'dual_projector' => true, 'pa_system' => true, 'live_streaming' => true],
                'facility'     => 'Engineering & Technology Block',
                'category'     => 'Lecture Halls',
            ],
            [
                'name'         => 'Lecture Theatre E-LT2',
                'description'  => '120-seat lecture theatre with single projector and hearing loop.',
                'status'       => 'active',
                'capacity'     => 120,
                'specifications' => ['floor' => '1st', 'projector' => true, 'hearing_loop' => true],
                'facility'     => 'Engineering & Technology Block',
                'category'     => 'Lecture Halls',
            ],
            [
                'name'         => 'Computer Lab E-CL1',
                'description'  => '40-station Windows lab with CAD, MATLAB, and engineering simulation software.',
                'status'       => 'active',
                'capacity'     => 40,
                'specifications' => ['floor' => '2nd', 'software' => ['AutoCAD', 'MATLAB', 'SolidWorks'], 'dual_monitor' => true],
                'facility'     => 'Engineering & Technology Block',
                'category'     => 'Computer Labs',
            ],
            [
                'name'         => 'Computer Lab E-CL2',
                'description'  => '30-station Linux/Windows dual-boot lab for programming and software engineering.',
                'status'       => 'active',
                'capacity'     => 30,
                'specifications' => ['floor' => '3rd', 'os' => 'Dual Boot Linux/Windows', 'software' => ['VS Code', 'IntelliJ', 'Eclipse']],
                'facility'     => 'Engineering & Technology Block',
                'category'     => 'Computer Labs',
            ],
            [
                'name'         => 'Electronics Workshop E-WS1',
                'description'  => 'Hands-on electronics lab with oscilloscopes, soldering stations, and PCB printers.',
                'status'       => 'active',
                'capacity'     => 24,
                'specifications' => ['floor' => '4th', 'oscilloscopes' => 12, 'soldering_stations' => 12, 'pcb_printer' => true],
                'facility'     => 'Engineering & Technology Block',
                'category'     => 'Research Laboratories',
            ],
            [
                'name'         => 'Engineering Seminar Room E-SR1',
                'description'  => '30-seat seminar room with interactive whiteboard and video conferencing.',
                'status'       => 'active',
                'capacity'     => 30,
                'specifications' => ['floor' => '5th', 'interactive_board' => true, 'video_conference' => true],
                'facility'     => 'Engineering & Technology Block',
                'category'     => 'Seminar Rooms',
            ],

            // ─── Science Research Complex ────────────────────────────────────
            [
                'name'         => 'Chemistry Lab S-CL1',
                'description'  => 'Full wet chemistry lab with 20 individual benches, fume hoods, and chemical storage.',
                'status'       => 'active',
                'capacity'     => 20,
                'specifications' => ['floor' => 'G', 'fume_hoods' => 5, 'safety_showers' => 2],
                'facility'     => 'Science Research Complex',
                'category'     => 'Research Laboratories',
            ],
            [
                'name'         => 'Biology Lab S-BL1',
                'description'  => 'Microbiology lab with autoclave, microscopes, and biosafety cabinet (BSL-2).',
                'status'       => 'active',
                'capacity'     => 16,
                'specifications' => ['floor' => '1st', 'bsl_level' => 2, 'microscopes' => 16, 'autoclave' => true],
                'facility'     => 'Science Research Complex',
                'category'     => 'Research Laboratories',
            ],
            [
                'name'         => 'Physics Lab S-PL1',
                'description'  => 'Optics and mechanics lab with laser equipment, spectrometers, and oscilloscopes.',
                'status'       => 'maintenance',
                'capacity'     => 18,
                'specifications' => ['floor' => '2nd', 'laser_class' => '3B', 'spectrometers' => 4],
                'facility'     => 'Science Research Complex',
                'category'     => 'Research Laboratories',
            ],
            [
                'name'         => 'Science Lecture Hall S-LH1',
                'description'  => '80-seat lecture hall with demonstration bench and integrated recording system.',
                'status'       => 'active',
                'capacity'     => 80,
                'specifications' => ['floor' => 'G', 'demo_bench' => true, 'recording' => true],
                'facility'     => 'Science Research Complex',
                'category'     => 'Lecture Halls',
            ],

            // ─── Humanities & Arts Centre ────────────────────────────────────
            [
                'name'         => 'Arts Seminar Room H-SR1',
                'description'  => '25-seat seminar room with movable furniture for flexible discussion layouts.',
                'status'       => 'active',
                'capacity'     => 25,
                'specifications' => ['floor' => '1st', 'flexible_furniture' => true, 'projector' => true],
                'facility'     => 'Humanities & Arts Centre',
                'category'     => 'Seminar Rooms',
            ],
            [
                'name'         => 'Recording Studio H-RS1',
                'description'  => 'Professional recording studio with mixing desk, iso booth, and podcast setup.',
                'status'       => 'active',
                'capacity'     => 8,
                'specifications' => ['floor' => 'B1', 'mixing_desk' => true, 'iso_booth' => true, 'podcast_ready' => true],
                'facility'     => 'Humanities & Arts Centre',
                'category'     => 'AV & Media Equipment',
            ],
            [
                'name'         => 'Portable Projector Kit A',
                'description'  => 'Epson EB-2265U portable projector with tripod screen, HDMI & wireless adapter.',
                'status'       => 'active',
                'capacity'     => null,
                'specifications' => ['lumens' => 5500, 'resolution' => 'WUXGA', 'wireless' => true],
                'facility'     => 'Humanities & Arts Centre',
                'category'     => 'AV & Media Equipment',
            ],
            [
                'name'         => 'Portable Projector Kit B',
                'description'  => 'Epson EB-2265U portable projector with tripod screen, HDMI & wireless adapter.',
                'status'       => 'active',
                'capacity'     => null,
                'specifications' => ['lumens' => 5500, 'resolution' => 'WUXGA', 'wireless' => true],
                'facility'     => 'Humanities & Arts Centre',
                'category'     => 'AV & Media Equipment',
            ],

            // ─── Sports Complex ──────────────────────────────────────────────
            [
                'name'         => 'Basketball Court 1',
                'description'  => 'Full-size indoor basketball court with scoreboard and spectator seating.',
                'status'       => 'active',
                'capacity'     => 10,
                'specifications' => ['surface' => 'Hardwood', 'lighting' => 'LED', 'scoreboard' => true],
                'facility'     => 'Sports & Recreation Complex',
                'category'     => 'Sports Facilities',
            ],
            [
                'name'         => 'Badminton Court 1',
                'description'  => 'Regulation badminton court with net and equipment storage.',
                'status'       => 'active',
                'capacity'     => 4,
                'specifications' => ['surface' => 'Synthetic', 'nets_provided' => true],
                'facility'     => 'Sports & Recreation Complex',
                'category'     => 'Sports Facilities',
            ],
            [
                'name'         => 'Badminton Court 2',
                'description'  => 'Regulation badminton court with net and equipment storage.',
                'status'       => 'active',
                'capacity'     => 4,
                'specifications' => ['surface' => 'Synthetic', 'nets_provided' => true],
                'facility'     => 'Sports & Recreation Complex',
                'category'     => 'Sports Facilities',
            ],
            [
                'name'         => 'Fitness Gym — Zone A',
                'description'  => 'Cardio zone with 12 treadmills, rowing machines, and spin bikes.',
                'status'       => 'active',
                'capacity'     => 25,
                'specifications' => ['treadmills' => 12, 'rowing_machines' => 4, 'spin_bikes' => 8],
                'facility'     => 'Sports & Recreation Complex',
                'category'     => 'Sports Facilities',
            ],

            // ─── Student Union ────────────────────────────────────────────────
            [
                'name'         => 'Main Event Hall',
                'description'  => '300-capacity event hall with stage, PA system, and retractable seating.',
                'status'       => 'active',
                'capacity'     => 300,
                'specifications' => ['stage' => true, 'pa_system' => true, 'retractable_seating' => true, 'catering_kitchen' => true],
                'facility'     => 'Student Union Building',
                'category'     => 'Lecture Halls',
            ],
            [
                'name'         => 'Board Room SU-BR1',
                'description'  => '16-seat executive boardroom with video conferencing, 75" display, and whiteboard.',
                'status'       => 'active',
                'capacity'     => 16,
                'specifications' => ['screen_size' => '75"', 'video_conference' => true, 'whiteboard' => true],
                'facility'     => 'Student Union Building',
                'category'     => 'Meeting & Board Rooms',
            ],
            [
                'name'         => 'Committee Room SU-CR1',
                'description'  => '10-seat meeting room for student committee sessions.',
                'status'       => 'active',
                'capacity'     => 10,
                'specifications' => ['projector' => true, 'whiteboard' => true],
                'facility'     => 'Student Union Building',
                'category'     => 'Meeting & Board Rooms',
            ],
            [
                'name'         => 'Committee Room SU-CR2',
                'description'  => '10-seat meeting room for student committee sessions.',
                'status'       => 'active',
                'capacity'     => 10,
                'specifications' => ['projector' => true, 'whiteboard' => true],
                'facility'     => 'Student Union Building',
                'category'     => 'Meeting & Board Rooms',
            ],
        ];

        foreach ($resources as $data) {
            $facilityModel  = $facility($data['facility']);
            $categoryModel  = $category($data['category']);

            if (!$facilityModel || !$categoryModel) {
                $this->command->warn("Skipping {$data['name']}: facility or category not found.");
                continue;
            }

            Resource::withoutGlobalScopes()->firstOrCreate(
                ['name' => $data['name'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id'      => $tenant->id,
                    'facility_id'    => $facilityModel->id,
                    'category_id'    => $categoryModel->id,
                    'name'           => $data['name'],
                    'description'    => $data['description'],
                    'status'         => $data['status'],
                    'capacity'       => $data['capacity'] ?? null,
                    'specifications' => $data['specifications'] ?? null,
                ]
            );
        }

        $this->command->info('Seeded ' . count($resources) . ' university resources.');

        // 4. Seed Healthcare Resources
        $hosp = Tenant::where('subdomain', 'hospital')->first();
        if ($hosp) {
            $hospFacility = fn($name) => Facility::withoutGlobalScopes()
                ->where('tenant_id', $hosp->id)->where('name', $name)->first();
            $hospCategory = fn($name) => ResourceCategory::withoutGlobalScopes()
                ->where('tenant_id', $hosp->id)->where('name', $name)->first();

            $hospResources = [
                [
                    'name'        => 'ICU Suite A',
                    'description' => 'Intensive Care Unit equipped with life-support and monitors.',
                    'facility'    => 'Surgery & ICU Center',
                    'category'    => 'ICU Wards',
                    'capacity'    => 1,
                ],
                [
                    'name'        => 'MRI Scanning Chamber 1',
                    'description' => 'High-resolution Siemens MRI machine for neural and clinical imaging.',
                    'facility'    => 'Diagnostics Department',
                    'category'    => 'Diagnostic Imaging (MRI/X-Ray)',
                    'capacity'    => 1,
                ],
                [
                    'name'        => 'Operating Theatre 1',
                    'description' => 'Advanced surgical suite with laminar air flow.',
                    'facility'    => 'Surgery & ICU Center',
                    'category'    => 'Operating Theatres',
                    'capacity'    => 8,
                ]
            ];

            foreach ($hospResources as $data) {
                $fac = $hospFacility($data['facility']);
                $cat = $hospCategory($data['category']);
                if ($fac && $cat) {
                    Resource::withoutGlobalScopes()->firstOrCreate(
                        ['name' => $data['name'], 'tenant_id' => $hosp->id],
                        [
                            'tenant_id'   => $hosp->id,
                            'facility_id' => $fac->id,
                            'category_id' => $cat->id,
                            'name'        => $data['name'],
                            'description' => $data['description'],
                            'status'      => 'active',
                            'capacity'    => $data['capacity'],
                        ]
                    );
                }
            }
        }

        // 5. Seed Corporate Resources
        $corp = Tenant::where('subdomain', 'corporate')->first();
        if ($corp) {
            $corpFacility = fn($name) => Facility::withoutGlobalScopes()
                ->where('tenant_id', $corp->id)->where('name', $name)->first();
            $corpCategory = fn($name) => ResourceCategory::withoutGlobalScopes()
                ->where('tenant_id', $corp->id)->where('name', $name)->first();

            $corpResources = [
                [
                    'name'        => 'Executive Board Room 12A',
                    'description' => 'Premium 16-seat board room with telepresence facilities.',
                    'facility'    => 'North Tower HQ',
                    'category'    => 'Board Rooms',
                    'capacity'    => 16,
                ],
                [
                    'name'        => 'Hot Desk North-04',
                    'description' => 'Ergonomic hotdesk in the shared workspace center.',
                    'facility'    => 'North Tower HQ',
                    'category'    => 'Shared Desks (Hotdesking)',
                    'capacity'    => 1,
                ]
            ];

            foreach ($corpResources as $data) {
                $fac = $corpFacility($data['facility']);
                $cat = $corpCategory($data['category']);
                if ($fac && $cat) {
                    Resource::withoutGlobalScopes()->firstOrCreate(
                        ['name' => $data['name'], 'tenant_id' => $corp->id],
                        [
                            'tenant_id'   => $corp->id,
                            'facility_id' => $fac->id,
                            'category_id' => $cat->id,
                            'name'        => $data['name'],
                            'description' => $data['description'],
                            'status'      => 'active',
                            'capacity'    => $data['capacity'],
                        ]
                    );
                }
            }
        }
    }
}
