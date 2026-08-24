<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceOrder extends Model
{
    protected $fillable = [
        'tenant_id',
        'resource_id',
        'assigned_to',
        'type',
        'status',
        'scheduled_at',
        'completed_at',
        'notes',
        'cost',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected function casts(): array
    {
        return [
            'scheduled_at'  => 'datetime',
            'completed_at'  => 'datetime',
            'cost'          => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    /** The technician/user assigned to carry out this maintenance. */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
