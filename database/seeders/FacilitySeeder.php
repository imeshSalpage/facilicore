<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        // 1. University
        $uni = Tenant::where('subdomain', 'university')->firstOrFail();
        $uniFacilities = [
            [
                'name'        => 'Main Library',
                'description' => 'Central academic library with study rooms and computer terminals.',
                'location'    => 'Central Campus, Building A',
                'tenant_id'   => $uni->id,
            ],
            [
                'name'        => 'Engineering & Technology Block',
                'description' => 'Modern engineering faculty building housing labs and lecture theatres.',
                'location'    => 'North Campus, Building B',
                'tenant_id'   => $uni->id,
            ],
            [
                'name'        => 'Science Research Complex',
                'description' => 'Research facility with chemistry, biology, and physics laboratories.',
                'location'    => 'East Campus, Building C',
                'tenant_id'   => $uni->id,
            ],
        ];

        foreach ($uniFacilities as $data) {
            Facility::withoutGlobalScopes()->firstOrCreate(['name' => $data['name'], 'tenant_id' => $uni->id], $data);
        }

        // 2. Healthcare
        $hosp = Tenant::where('subdomain', 'hospital')->firstOrFail();
        $hospFacilities = [
            [
                'name'        => 'Outpatient Wing',
                'description' => 'Clinics and consultation suites for non-admitted patients.',
                'location'    => 'Building East, Ground Floor',
                'tenant_id'   => $hosp->id,
            ],
            [
                'name'        => 'Surgery & ICU Center',
                'description' => 'Highly sterile operating theatres and intensive care suites.',
                'location'    => 'Building North, 2nd Floor',
                'tenant_id'   => $hosp->id,
            ],
            [
                'name'        => 'Diagnostics Department',
                'description' => 'Medical imaging and path labs.',
                'location'    => 'Building West, Basement',
                'tenant_id'   => $hosp->id,
            ],
        ];

        foreach ($hospFacilities as $data) {
            Facility::withoutGlobalScopes()->firstOrCreate(['name' => $data['name'], 'tenant_id' => $hosp->id], $data);
        }

        // 3. Corporate
        $corp = Tenant::where('subdomain', 'corporate')->firstOrFail();
        $corpFacilities = [
            [
                'name'        => 'North Tower HQ',
                'description' => 'Primary corporate corporate branch with executive board rooms and desks.',
                'location'    => 'District 4, Floor 12',
                'tenant_id'   => $corp->id,
            ],
            [
                'name'        => 'South Innovation Lab',
                'description' => 'Research and development creative center.',
                'location'    => 'Tech Park, Building 2',
                'tenant_id'   => $corp->id,
            ],
        ];

        foreach ($corpFacilities as $data) {
            Facility::withoutGlobalScopes()->firstOrCreate(['name' => $data['name'], 'tenant_id' => $corp->id], $data);
        }
    }
}
