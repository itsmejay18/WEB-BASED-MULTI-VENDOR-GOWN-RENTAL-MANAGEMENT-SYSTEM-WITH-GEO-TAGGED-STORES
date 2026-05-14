<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'provider_id',
        'item_id',
        'rating',
        'comment',
        'item_condition_rating',
        'communication_rating',
        'photos',
        'is_public',
    ];

    protected $casts = [
        'rating' => 'integer',
        'item_condition_rating' => 'integer',
        'communication_rating' => 'integer',
        'photos' => 'array',
        'is_public' => 'boolean',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}
