<?php

namespace App\Services;

use App\Models\Promotion;
use Carbon\Carbon;

class PricingService
{
    public function calculateTotal(int $days, float $rentalPrice, float $securityDeposit = 0, float $deliveryFee = 0, float $platformFeeRate = 0.05): array
    {
        $baseRental = $rentalPrice;
        $platformFee = round($baseRental * $platformFeeRate, 2);
        $subtotal = round($baseRental + $securityDeposit + $deliveryFee + $platformFee, 2);

        return [
            'days' => $days,
            'rental_price' => round($baseRental, 2),
            'security_deposit' => round($securityDeposit, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'platform_fee' => $platformFee,
            'discount_amount' => 0.0,
            'total_amount' => $subtotal,
        ];
    }

    public function applyPromotion(array $totals, ?Promotion $promotion): array
    {
        if (! $promotion) {
            return $totals;
        }

        $total = (float) $totals['total_amount'];

        if (! $promotion->isValidForAmount($total)) {
            return $totals;
        }

        $discount = 0.0;

        if ($promotion->discount_type === 'percentage') {
            $discount = round($total * ((float) $promotion->discount_value / 100), 2);
        } else {
            $discount = min($total, (float) $promotion->discount_value);
        }

        if ($promotion->max_discount_amount !== null) {
            $discount = min($discount, (float) $promotion->max_discount_amount);
        }

        $totals['discount_amount'] = $discount;
        $totals['total_amount'] = round(max(0, $total - $discount), 2);

        return $totals;
    }

    public function rentalDays(string $startDate, string $endDate): int
    {
        return Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
    }
}
