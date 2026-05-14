<?php

namespace App\Services;

use App\Models\ItemVariant;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AvailabilityService
{
    public function isVariantAvailable(ItemVariant $variant, string $startDate, string $endDate, int $cleaningDays = 0): bool
    {
        if (! $variant->is_active || $variant->quantity_available < 1) {
            return false;
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($start->gt($end)) {
            return false;
        }

        $blockedDayCount = $variant->availability()
            ->whereBetween('available_date', [$start->toDateString(), $end->toDateString()])
            ->where('is_available', false)
            ->count();

        if ($blockedDayCount > 0) {
            return false;
        }

        $bookingStart = $start->copy()->subDays($cleaningDays)->toDateString();
        $bookingEnd = $end->copy()->addDays($cleaningDays)->toDateString();

        $overlapping = $variant->bookings()
            ->whereIn('status', ['pending', 'approved', 'ready_for_pickup', 'picked_up'])
            ->where(function ($query) use ($bookingStart, $bookingEnd) {
                $query
                    ->whereBetween('start_date', [$bookingStart, $bookingEnd])
                    ->orWhereBetween('end_date', [$bookingStart, $bookingEnd])
                    ->orWhere(function ($nested) use ($bookingStart, $bookingEnd) {
                        $nested->where('start_date', '<=', $bookingStart)->where('end_date', '>=', $bookingEnd);
                    });
            })
            ->count();

        return $overlapping < $variant->quantity_available;
    }

    public function blockAvailability(ItemVariant $variant, string $startDate, string $endDate, bool $isAvailable = false, ?string $notes = null): void
    {
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $variant->availability()->updateOrCreate(
                ['available_date' => $date->format('Y-m-d')],
                ['is_available' => $isAvailable, 'notes' => $notes]
            );
        }
    }
}
