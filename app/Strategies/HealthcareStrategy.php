<?php

namespace App\Strategies;

class HealthcareStrategy implements SectorStrategyInterface
{
    public function getTerminology(): array
    {
        return [
            'facility'   => 'Clinic / Ward',
            'facilities' => 'Clinics & Wards',
            'resource'   => 'Medical Equipment',
            'resources'  => 'Medical Equipment / Rooms',
            'supervisor' => 'Chief Doctor',
            'end_user'   => 'Nurse / Practitioner',
        ];
    }

    public function getPriorityRules(): array
    {
        return [
            'emergency_priority_boost' => true,
            'clinical_multiplier'      => 2.0,
        ];
    }

    public function getDefaultCategories(): array
    {
        return [
            'Operating Theatres',
            'ICU Wards',
            'Consultation Rooms',
            'Diagnostic Imaging (MRI/X-Ray)',
            'Specialist Medical Gear',
            'Ambulance Fleet',
            'Laboratory equipment',
        ];
    }
}
