<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingTemplateItem extends Model
{
    protected $fillable = [
        'booking_template_id',
        'category_id',
        'quantity',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(BookingTemplate::class, 'booking_template_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ResourceCategory::class, 'category_id');
    }
}
