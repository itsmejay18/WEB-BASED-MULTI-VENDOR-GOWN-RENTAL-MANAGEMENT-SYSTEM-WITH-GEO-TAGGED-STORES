<?php

namespace App\Repositories\Contracts;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BookingRepositoryInterface
{
    public function forUser(int $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function findForUser(int $userId, Booking $booking): ?Booking;
}
