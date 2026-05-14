<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityCalendar extends Model
{
    use HasFactory;

    protected $table = 'availability_calendar';

    protected $fillable = [
        'variant_id',
        'available_date',
        'is_available',
        'notes',
    ];

    protected $casts = [
        'available_date' => 'date',
        'is_available' => 'boolean',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ItemVariant::class, 'variant_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}
