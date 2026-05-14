<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Item extends Model
{
    use HasFactory;
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'provider_id',
        'category_id',
        'name',
        'slug',
        'description',
        'brand',
        'designer',
        'condition_rating',
        'cleaning_policy',
        'security_deposit',
        'late_fee_per_day',
        'total_rentals',
        'is_active',
        'requires_approval',
    ];

    protected $casts = [
        'security_deposit' => 'decimal:2',
        'late_fee_per_day' => 'decimal:2',
        'total_rentals' => 'integer',
        'is_active' => 'boolean',
        'requires_approval' => 'boolean',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'item_tags')->withTimestamps();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ItemVariant::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ItemPhoto::class);
    }

    public function primaryPhoto(): HasOne
    {
        return $this->hasOne(ItemPhoto::class)->where('is_primary', true);
    }

    public function pricingTiers(): HasMany
    {
        return $this->hasMany(PricingTier::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function bookings(): HasManyThrough
    {
        return $this->hasManyThrough(Booking::class, ItemVariant::class, 'item_id', 'variant_id', 'id', 'id');
    }

    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlist')
            ->withPivot('notes')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailableForDates($query, string $startDate, string $endDate)
    {
        return $query->whereHas('variants', function ($variantQuery) use ($startDate, $endDate) {
            $variantQuery->availableForDates($startDate, $endDate);
        });
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByBrand($query, string $brand)
    {
        return $query->where('brand', $brand);
    }

    public function scopeBySize($query, string $size)
    {
        return $query->whereHas('variants', fn ($variantQuery) => $variantQuery->where('size_label', $size));
    }

    public function scopeByPriceRange($query, float $min, float $max)
    {
        return $query->whereHas('pricingTiers', fn ($priceQuery) => $priceQuery->whereBetween('price', [$min, $max]));
    }

    public function scopeByOccasion($query, string $occasionTag)
    {
        return $query->whereHas('tags', function ($tagQuery) use ($occasionTag) {
            $tagQuery->where('type', 'occasion')->where('name', 'like', "%{$occasionTag}%");
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getLowestPriceAttribute(): float
    {
        return (float) ($this->pricingTiers()->min('price') ?? 0);
    }

    public function getAverageRatingAttribute(): float
    {
        return (float) ($this->reviews()->avg('rating') ?? 0);
    }

    public function isAvailableForDates(string $startDate, string $endDate): bool
    {
        foreach ($this->variants as $variant) {
            if ($variant->isAvailableForDates($startDate, $endDate)) {
                return true;
            }
        }

        return false;
    }

    public function searchableAs(): string
    {
        return 'items_index';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'brand' => $this->brand,
            'designer' => $this->designer,
            'condition_rating' => $this->condition_rating,
            'is_active' => $this->is_active,
        ];
    }
}
