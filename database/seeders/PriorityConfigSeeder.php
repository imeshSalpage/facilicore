<?php

namespace Database\Seeders;

use App\Models\PriorityConfig;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class PriorityConfigSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        $weights = [
            ['factor' => 'role_admin',      'weight' => 100.00],
            ['factor' => 'role_supervisor', 'weight' => 50.00],
            ['factor' => 'role_end_user',   'weight' => 10.00],
            ['factor' => 'urgency_flag',    'weight' => 30.00],
            ['factor' => 'resource_demand', 'weight' => 2.00],
        ];

        foreach ($tenants as $tenant) {
            foreach ($weights as $w) {
                PriorityConfig::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'factor'    => $w['factor']
                    ],
                    [
                        'weight'    => $w['weight']
                    ]
                );
            }
        }
    }
}
