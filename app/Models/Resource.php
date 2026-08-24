<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    protected $fillable = [
        'facility_id',
        'category_id',
        'name',
        'description',
        'specifications',
        'capacity',
        'status',
        'tenant_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected function casts(): array
    {
        return [
            'specifications' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ResourceCategory::class, 'category_id');
    }
}
