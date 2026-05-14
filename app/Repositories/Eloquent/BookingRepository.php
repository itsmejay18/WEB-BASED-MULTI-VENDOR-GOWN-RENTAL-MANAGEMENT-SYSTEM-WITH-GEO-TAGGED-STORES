<?php

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BookingRepository implements BookingRepositoryInterface
{
    public function forUser(int $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Booking::query()
            ->with(['variant.item', 'provider.mainLocation', 'pickupLocation', 'deliveryAddress'])
            ->where('user_id', $userId)
            ->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function findForUser(int $userId, Booking $booking): ?Booking
    {
        return Booking::query()
            ->where('id', $booking->id)
            ->where('user_id', $userId)
            ->first();
    }
}
