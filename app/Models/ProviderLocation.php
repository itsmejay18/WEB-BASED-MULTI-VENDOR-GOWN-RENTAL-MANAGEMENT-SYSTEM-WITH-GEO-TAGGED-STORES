<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class ProviderLocation extends Model
{
    use HasFactory;
    use HasSpatial;

    protected $fillable = [
        'provider_id',
        'location_name',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'location',
        'service_radius_km',
        'is_main',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'location' => Point::class,
        'is_main' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProviderLocation $location): void {
            if ($location->latitude !== null && $location->longitude !== null) {
                $location->location = new Point((float) $location->latitude, (float) $location->longitude);
            }
        });
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHour::class);
    }

    public function pickupBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'pickup_location_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithinRadius($query, float $latitude, float $longitude, float $radiusKm)
    {
        $distanceSql = '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))';

        return $query
            ->selectRaw("provider_locations.*, {$distanceSql} as distance", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radiusKm);
    }

    public function scopeWithinDistance($query, float $latitude, float $longitude, float $distanceKm)
    {
        $point = new Point($latitude, $longitude);

        return $query->whereDistanceSphere('location', $point, '<=', $distanceKm * 1000);
    }

    public function scopeOrderByDistance($query, float $latitude, float $longitude)
    {
        $point = new Point($latitude, $longitude);

        return $query->orderByDistanceSphere('location', $point);
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ])->filter()->implode(', ');
    }

    public function isOpenNow(): bool
    {
        $now = now();
        $dayOfWeek = $now->dayOfWeekIso - 1;
        $hours = $this->businessHours()->where('day_of_week', $dayOfWeek)->first();

        if (! $hours || $hours->is_closed || ! $hours->open_time || ! $hours->close_time) {
            return false;
        }

        $currentTime = $now->format('H:i:s');

        return $currentTime >= $hours->open_time && $currentTime <= $hours->close_time;
    }
}
