<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'subdomain',
        'sector',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function getResolvedSettings(): array
    {
        $defaults = [
            'auto_approve_standard_bookings' => false,
            'max_advance_booking_days'        => 60,
            'allow_urgency_override'          => true,
            'conflict_mode'                   => 'priority_override', // 'priority_override' | 'first_come'
            'maintenance_auto_schedule'       => true,
            'anomaly_sensitivity'             => 75,
            'notification_email'              => null,
        ];

        return array_merge($defaults, $this->settings ?? []);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
