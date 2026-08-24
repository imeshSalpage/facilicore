<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompositeBooking extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'status',
        'notes',
        'start_at',
        'end_at',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at'   => 'datetime',
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

    /** Single bookings linked together in this composite transaction. */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
