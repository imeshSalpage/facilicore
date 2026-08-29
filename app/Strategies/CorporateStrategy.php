<?php

namespace App\Strategies;

class CorporateStrategy implements SectorStrategyInterface
{
    public function getTerminology(): array
    {
        return [
            'facility'    => 'Office Branch',
            'facilities'  => 'Office Branches',
            'resource'    => 'Meeting Room / Desk',
            'resources'   => 'Rooms & Desks',
            'supervisor'  => 'Team Manager',
            'end_user'    => 'Employee',
            'reg_number'  => 'Employee / Badge ID',
            'department'  => 'Department / Business Unit',
        ];
    }

    public function getPriorityRules(): array
    {
        return [
            'board_meeting_priority_boost' => true,
            'client_multiplier'            => 1.25,
        ];
    }

    public function getDefaultCategories(): array
    {
        return [
            'Board Rooms',
            'Huddle Rooms',
            'Shared Desks (Hotdesking)',
            'Conference Rooms',
            'Training Rooms',
            'Video Conferencing Gear',
            'Company Vehicles',
        ];
    }
}
