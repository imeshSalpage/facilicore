<?php

namespace App\Strategies;

class UniversityStrategy implements SectorStrategyInterface
{
    public function getTerminology(): array
    {
        return [
            'facility'   => 'Campus Building',
            'facilities' => 'Campus Buildings',
            'resource'   => 'Asset / Room',
            'resources'  => 'Assets & Rooms',
            'supervisor' => 'Professor',
            'end_user'   => 'Student',
        ];
    }

    public function getPriorityRules(): array
    {
        return [
            'exam_priority_boost' => true,
            'research_multiplier' => 1.5,
        ];
    }

    public function getDefaultCategories(): array
    {
        return [
            'Lecture Halls',
            'Seminar Rooms',
            'Computer Labs',
            'Research Laboratories',
            'Study Rooms',
            'Sports Facilities',
            'AV & Media Equipment',
            'Meeting Rooms',
        ];
    }
}
