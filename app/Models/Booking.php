<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'user_id',
        'provider_id',
        'variant_id',
        'pickup_location_id',
        'delivery_address_id',
        'start_date',
        'end_date',
        'rental_price',
        'security_deposit',
        'delivery_fee',
        'platform_fee',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'special_requests',
        'rejection_reason',
        'cancelled_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'rental_price' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking): void {
            if (! $booking->booking_number) {
                $prefix = config('rentfit.booking.booking_prefix', 'BK');
                $booking->booking_number = sprintf(
                    '%s-%s-%s',
                    $prefix,
                    now()->format('Ym'),
                    strtoupper(Str::random(6))
                );
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ItemVariant::class, 'variant_id');
    }

    public function pickupLocation(): BelongsTo
    {
        return $this->belongsTo(ProviderLocation::class, 'pickup_location_id');
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'delivery_address_id');
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(BookingTimeline::class, 'booking_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'booking_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'booking_id');
    }

    public function promotionUsage(): HasOne
    {
        return $this->hasOne(PromotionUsage::class, 'booking_id');
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class, 'booking_id');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByProvider($query, int $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    public function scopeUpcoming($query)
    {
        return $query
            ->where('start_date', '>', now()->toDateString())
            ->whereIn('status', ['approved', 'pending']);
    }

    public function scopeActive($query)
    {
        return $query
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->whereIn('status', ['approved', 'ready_for_pickup', 'picked_up']);
    }

    public function scopePast($query)
    {
        return $query
            ->whereDate('end_date', '<', now()->toDateString())
            ->whereIn('status', ['completed', 'returned']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeNeedsAction($query)
    {
        return $query->where(function ($innerQuery) {
            $innerQuery
                ->whereIn('status', ['pending', 'ready_for_pickup'])
                ->orWhere('payment_status', 'pending');
        });
    }

    public function canBeCancelledByUser(): bool
    {
        return in_array($this->status, ['pending', 'approved'], true)
            && now()->diffInDays($this->start_date, false) > 2;
    }

    public function canBeCancelledByProvider(): bool
    {
        return in_array($this->status, ['pending', 'approved'], true);
    }

    public function markAsPaid(): void
    {
        $this->payment_status = 'paid';
        $this->save();

        $this->timeline()->create([
            'status' => 'payment_received',
            'notes' => 'Payment has been received.',
            'created_by' => auth()->id(),
        ]);
    }

    public function updateStatus(string $newStatus, ?string $notes = null): void
    {
        $oldStatus = $this->status;
        $this->status = $newStatus;
        $this->save();

        $this->timeline()->create([
            'status' => $newStatus,
            'notes' => $notes ?? "Status changed from {$oldStatus} to {$newStatus}",
            'created_by' => auth()->id(),
        ]);
    }
}
