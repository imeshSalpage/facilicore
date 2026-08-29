<?php

namespace App\Strategies;

class GovernmentStrategy implements SectorStrategyInterface
{
    public function getTerminology(): array
    {
        return [
            'facility'    => 'Agency Office',
            'facilities'  => 'Agency Offices',
            'resource'    => 'Public Asset',
            'resources'   => 'Public Assets & Wards',
            'supervisor'  => 'Director / Supervisor',
            'end_user'    => 'Public Officer / Citizen',
            'reg_number'  => 'Civil Service / Staff ID',
            'department'  => 'Ministry / Directorate',
        ];
    }

    public function getPriorityRules(): array
    {
        return [
            'official_assembly_boost' => true,
            'security_multiplier'     => 1.8,
        ];
    }

    public function getDefaultCategories(): array
    {
        return [
            'Council Chambers',
            'Press Briefing Rooms',
            'Public Service Offices',
            'Municipal Vehicles',
            'Community Centers',
            'Exhibition Halls',
        ];
    }
}
