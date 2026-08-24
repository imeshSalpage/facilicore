<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::firstOrCreate(
            ['subdomain' => 'university'],
            [
                'name'   => 'Eastbridge University',
                'sector' => 'university'
            ]
        );

        Tenant::firstOrCreate(
            ['subdomain' => 'hospital'],
            [
                'name'   => 'St. Jude Clinical Care',
                'sector' => 'healthcare'
            ]
        );

        Tenant::firstOrCreate(
            ['subdomain' => 'corporate'],
            [
                'name'   => 'Global Nexus HQ',
                'sector' => 'corporate'
            ]
        );

        Tenant::firstOrCreate(
            ['subdomain' => 'imesh'],
            [
                'name'   => 'Imesh Technology Core',
                'sector' => 'university'
            ]
        );

        Tenant::firstOrCreate(
            ['subdomain' => 'government'],
            [
                'name'   => 'Metro City Council',
                'sector' => 'government'
            ]
        );
    }
}
