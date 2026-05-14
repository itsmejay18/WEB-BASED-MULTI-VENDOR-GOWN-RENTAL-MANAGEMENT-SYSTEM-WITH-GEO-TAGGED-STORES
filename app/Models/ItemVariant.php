<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'sku',
        'size_label',
        'color',
        'material',
        'chest_cm',
        'waist_cm',
        'length_cm',
        'inseam_cm',
        'shoulder_cm',
        'quantity_available',
        'additional_notes',
        'is_active',
    ];

    protected $casts = [
        'chest_cm' => 'decimal:2',
        'waist_cm' => 'decimal:2',
        'length_cm' => 'decimal:2',
        'inseam_cm' => 'decimal:2',
        'shoulder_cm' => 'decimal:2',
        'quantity_available' => 'integer',
        'is_active' => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ItemPhoto::class, 'variant_id');
    }

    public function availability(): HasMany
    {
        return $this->hasMany(AvailabilityCalendar::class, 'variant_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'variant_id');
    }

    public function activeBookings(): HasMany
    {
        return $this->bookings()
            ->whereIn('status', ['approved', 'ready_for_pickup', 'picked_up'])
            ->where('end_date', '>=', now()->toDateString());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity_available', '>', 0);
    }

    public function scopeAvailableForDates($query, string $startDate, string $endDate)
    {
        return $query->whereDoesntHave('bookings', function ($bookingQuery) use ($startDate, $endDate) {
            $bookingQuery
                ->whereIn('status', ['approved', 'ready_for_pickup', 'picked_up'])
                ->where(function ($overlapQuery) use ($startDate, $endDate) {
                    $overlapQuery
                        ->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($insideQuery) use ($startDate, $endDate) {
                            $insideQuery
                                ->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                });
        });
    }

    public function scopeBySize($query, string $size)
    {
        return $query->where('size_label', $size);
    }

    public function scopeByColor($query, string $color)
    {
        return $query->where('color', $color);
    }

    public function getFullNameAttribute(): string
    {
        $parts = [$this->item->name];

        if ($this->color) {
            $parts[] = $this->color;
        }

        $parts[] = "Size {$this->size_label}";

        return implode(' - ', $parts);
    }

    public function isAvailableForDates(string $startDate, string $endDate): bool
    {
        if ($this->quantity_available <= 0) {
            return false;
        }

        $overlappingBookings = $this->bookings()
            ->whereIn('status', ['approved', 'ready_for_pickup', 'picked_up'])
            ->where(function ($bookingQuery) use ($startDate, $endDate) {
                $bookingQuery
                    ->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($insideQuery) use ($startDate, $endDate) {
                        $insideQuery
                            ->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->count();

        return $overlappingBookings < $this->quantity_available;
    }

    public function getPriceForDuration(int $days): ?float
    {
        $pricing = $this->item->pricingTiers()
            ->where('duration_days', $days)
            ->where('is_active', true)
            ->first();

        return $pricing ? (float) $pricing->price : null;
    }
}
