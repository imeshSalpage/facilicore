<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Order matters — dependencies must be seeded before dependants.
     */
    public function run(): void
    {
        $this->call([
            TenantSeeder::class,           // 1. Create tenant: Eastbridge University
            UserSeeder::class,             // 2. Create users (admin, supervisors, end users)
            FacilitySeeder::class,         // 3. Create facilities (6 university buildings)
            ResourceCategorySeeder::class, // 4. Create resource categories (8 types)
            ResourceSeeder::class,         // 5. Create 27 university resources
            PriorityConfigSeeder::class,   // 6. Create default priority configs
            BookingTemplateSeeder::class,  // 7. Create booking templates
            BookingSeeder::class,          // 8. Create sample bookings (today → next week)
        ]);
    }
}
