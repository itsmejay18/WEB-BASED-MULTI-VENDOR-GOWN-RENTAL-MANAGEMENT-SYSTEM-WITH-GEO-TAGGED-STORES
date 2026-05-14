<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;

class Provider extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'user_id',
        'business_name',
        'business_registration',
        'description',
        'logo',
        'cover_photo',
        'rating',
        'total_reviews',
        'verification_status',
        'commission_rate',
        'verified_at',
        'verified_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'is_suspended',
        'suspended_at',
        'suspended_until',
        'suspension_reason',
        'suspended_by',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'verified_at' => 'datetime',
        'rejected_at' => 'datetime',
        'is_suspended' => 'boolean',
        'suspended_at' => 'datetime',
        'suspended_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ProviderLocation::class);
    }

    public function mainLocation(): HasOne
    {
        return $this->hasOne(ProviderLocation::class)->where('is_main', true);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function verificationLogs(): HasMany
    {
        return $this->hasMany(ProviderVerificationLog::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeNotSuspended($query)
    {
        return $query->where('is_suspended', false);
    }

    public function scopeTopRated($query, float $minRating = 4.0)
    {
        return $query->where('rating', '>=', $minRating);
    }

    public function updateRating(): void
    {
        $this->rating = (float) ($this->reviews()->avg('rating') ?? 0);
        $this->total_reviews = $this->reviews()->count();
        $this->save();
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function searchableAs(): string
    {
        return 'providers_index';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'business_name' => $this->business_name,
            'description' => $this->description,
            'rating' => (float) $this->rating,
            'verification_status' => $this->verification_status,
        ];
    }
}
