<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriorityConfig extends Model
{
    protected $fillable = [
        'tenant_id',
        'factor',
        'weight',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
