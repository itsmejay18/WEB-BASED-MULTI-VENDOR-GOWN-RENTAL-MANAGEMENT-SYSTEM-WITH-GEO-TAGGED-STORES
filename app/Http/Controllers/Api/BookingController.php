<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly BookingRepositoryInterface $bookingRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $bookings = $this->bookingRepository->forUser(
            $request->user()->id,
            $request->only(['status']),
            $request->integer('per_page', 20)
        );

        return BookingResource::collection($bookings)->response();
    }

    public function store(CreateBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->createBooking($request->user(), $request->validated());

        return response()->json([
            'booking' => new BookingResource($booking),
        ], 201);
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeBookingAccess($request, $booking);

        $booking->load(['user', 'provider', 'variant.item', 'pickupLocation', 'deliveryAddress', 'payments', 'review', 'timeline']);

        return response()->json([
            'booking' => new BookingResource($booking),
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking): JsonResponse
    {
        $this->authorizeBookingAccess($request, $booking);

        $data = $request->validated();

        if (isset($data['status']) && $data['status'] !== $booking->status) {
            $booking = $this->bookingService->updateStatus(
                $booking,
                $data['status'],
                $data['rejection_reason'] ?? null,
                $request->user()->id
            );
        }

        $booking->fill(collect($data)->except('status')->all())->save();

        return response()->json([
            'booking' => new BookingResource($booking->fresh()),
        ]);
    }

    public function destroy(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeBookingAccess($request, $booking);
        $booking->delete();

        return response()->json(['message' => 'Booking deleted.']);
    }

    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeBookingAccess($request, $booking);

        $isProvider = $request->user()?->provider?->id === $booking->provider_id;
        if ($isProvider && ! $booking->canBeCancelledByProvider()) {
            return response()->json(['message' => 'Booking cannot be cancelled by provider.'], 422);
        }

        if (! $isProvider && ! $booking->canBeCancelledByUser()) {
            return response()->json(['message' => 'Booking cannot be cancelled by user.'], 422);
        }

        $cancelledBy = $isProvider ? 'provider' : 'user';
        $booking = $this->bookingService->updateStatus($booking, 'cancelled', 'Booking cancelled.', $request->user()->id);
        $booking->update(['cancelled_by' => $cancelledBy]);

        return response()->json([
            'booking' => new BookingResource($booking),
        ]);
    }

    private function authorizeBookingAccess(Request $request, Booking $booking): void
    {
        $user = $request->user();
        $isOwner = $booking->user_id === $user->id;
        $isProviderOwner = $user->provider && $booking->provider_id === $user->provider->id;
        $isAdmin = $user->role === 'admin';

        abort_unless($isOwner || $isProviderOwner || $isAdmin, 403, 'Unauthorized booking access.');
    }
}
