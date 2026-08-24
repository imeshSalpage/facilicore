<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    // Disable default timestamps since we only use created_at
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'payload',
        'ip_address',
        'user_agent',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected function casts(): array
    {
        return [
            'payload'    => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
