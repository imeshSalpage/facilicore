<?php

namespace Database\Seeders;

use App\Models\BookingTemplate;
use App\Models\ResourceCategory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class BookingTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'university')->firstOrFail();

        $cat = fn($name) => ResourceCategory::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('name', $name)
            ->first();

        // 1. Lecture with AV Kit
        $lectureAV = BookingTemplate::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Lecture with AV Kit'
            ],
            [
                'description' => 'Book a tiered Lecture Hall along with a Portable Projector and audio equipment bundle.'
            ]
        );

        $lhCat = $cat('Lecture Halls');
        $avCat = $cat('AV & Media Equipment');

        if ($lhCat) {
            $lectureAV->items()->firstOrCreate(['category_id' => $lhCat->id], ['quantity' => 1]);
        }
        if ($avCat) {
            $lectureAV->items()->firstOrCreate(['category_id' => $avCat->id], ['quantity' => 1]);
        }

        // 2. Science Research Seminar Setup
        $researchSetup = BookingTemplate::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Science Research Seminar Setup'
            ],
            [
                'description' => 'Book a Research Laboratory alongside a Seminar Room and AV recording equipment.'
            ]
        );

        $rlCat = $cat('Research Laboratories');
        $srCat = $cat('Seminar Rooms');

        if ($rlCat) {
            $researchSetup->items()->firstOrCreate(['category_id' => $rlCat->id], ['quantity' => 1]);
        }
        if ($srCat) {
            $researchSetup->items()->firstOrCreate(['category_id' => $srCat->id], ['quantity' => 1]);
        }
        if ($avCat) {
            $researchSetup->items()->firstOrCreate(['category_id' => $avCat->id], ['quantity' => 1]);
        }
    }
}
