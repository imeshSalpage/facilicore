<?php

namespace Database\Seeders;

use App\Models\ResourceCategory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ResourceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $strategy = match ($tenant->sector) {
                'healthcare' => new \App\Strategies\HealthcareStrategy(),
                'corporate'  => new \App\Strategies\CorporateStrategy(),
                'government' => new \App\Strategies\GovernmentStrategy(),
                default      => new \App\Strategies\UniversityStrategy(),
            };

            $categories = $strategy->getDefaultCategories();

            foreach ($categories as $catName) {
                ResourceCategory::withoutGlobalScopes()->firstOrCreate(
                    [
                        'name'      => $catName,
                        'tenant_id' => $tenant->id
                    ],
                    [
                        'name'        => $catName,
                        'description' => "Standard resource category for " . ($tenant->sector ?? 'university') . " settings.",
                        'tenant_id'   => $tenant->id,
                    ]
                );
            }
        }
    }
}
