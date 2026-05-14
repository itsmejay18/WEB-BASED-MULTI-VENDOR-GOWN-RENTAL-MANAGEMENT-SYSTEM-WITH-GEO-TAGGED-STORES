<?php

namespace App\Services;

use App\Events\BookingCreated;
use App\Events\BookingStatusChanged;
use App\Models\Booking;
use App\Models\ItemVariant;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private readonly AvailabilityService $availabilityService,
        private readonly PricingService $pricingService
    ) {}

    public function createBooking(User $user, array $payload): Booking
    {
        $variant = ItemVariant::with(['item', 'item.provider'])->findOrFail($payload['variant_id']);
        $startDate = $payload['start_date'];
        $endDate = $payload['end_date'];
        $cleaningDays = (int) config('pricing.cleaning_days', 1);

        if (! $this->availabilityService->isVariantAvailable($variant, $startDate, $endDate, $cleaningDays)) {
            throw ValidationException::withMessages([
                'availability' => 'Selected variant is not available for the requested dates.',
            ]);
        }

        $days = $this->pricingService->rentalDays($startDate, $endDate);
        $tierPrice = $variant->getPriceForDuration($days);
        $rentalPrice = $tierPrice ?? (float) ($variant->item->pricingTiers()->active()->min('price') ?? 0);

        $totals = $this->pricingService->calculateTotal(
            $days,
            $rentalPrice,
            (float) $variant->item->security_deposit,
            0,
            (float) config('pricing.platform_fee_rate', 0.05)
        );

        $promotion = null;
        if (! empty($payload['discount_code'])) {
            $promotion = Promotion::activeNow()->where('code', $payload['discount_code'])->first();
            $totals = $this->pricingService->applyPromotion($totals, $promotion);
        }

        return DB::transaction(function () use ($user, $variant, $payload, $startDate, $endDate, $totals, $promotion) {
            $booking = Booking::create([
                'user_id' => $user->id,
                'provider_id' => $variant->item->provider_id,
                'variant_id' => $variant->id,
                'pickup_location_id' => $payload['pickup_location_id'] ?? null,
                'delivery_address_id' => $payload['delivery_address_id'] ?? null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'rental_price' => $totals['rental_price'],
                'security_deposit' => $totals['security_deposit'],
                'delivery_fee' => $totals['delivery_fee'],
                'platform_fee' => $totals['platform_fee'],
                'total_amount' => $totals['total_amount'],
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $payload['payment_method'] ?? null,
                'special_requests' => $payload['special_requests'] ?? null,
            ]);

            $booking->timeline()->create([
                'status' => 'pending',
                'notes' => 'Booking created.',
                'created_by' => $user->id,
            ]);

            if ($promotion && ($totals['discount_amount'] ?? 0) > 0) {
                PromotionUsage::create([
                    'promotion_id' => $promotion->id,
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'discount_amount' => $totals['discount_amount'],
                ]);

                $promotion->increment('used_count');
            }

            event(new BookingCreated($booking));

            return $booking->fresh(['user', 'provider', 'variant', 'pickupLocation', 'deliveryAddress']);
        });
    }

    public function updateStatus(Booking $booking, string $status, ?string $notes = null, ?int $actorId = null): Booking
    {
        $oldStatus = $booking->status;
        $booking->status = $status;
        $booking->save();

        $booking->timeline()->create([
            'status' => $status,
            'notes' => $notes ?? "Status changed from {$oldStatus} to {$status}.",
            'created_by' => $actorId,
        ]);

        event(new BookingStatusChanged($booking, $oldStatus, $status));

        return $booking->fresh();
    }
}
