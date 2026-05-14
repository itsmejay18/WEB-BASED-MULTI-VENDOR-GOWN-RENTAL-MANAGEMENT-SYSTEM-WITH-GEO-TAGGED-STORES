<?php

namespace App\Http\Controllers\Web\Renter;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ItemVariant;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService) {}

    public function index(Request $request): Response
    {
        $bookings = Booking::query()
            ->where('user_id', $request->user()->id)
            ->with(['provider:id,business_name', 'variant.item:id,name,slug', 'pickupLocation:id,city', 'deliveryAddress:id,address_line1,city'])
            ->latest()
            ->paginate(15)
            ->through(function (Booking $booking): array {
                return [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status' => $booking->status,
                    'payment_status' => $booking->payment_status,
                    'start_date' => $booking->start_date?->toDateString(),
                    'end_date' => $booking->end_date?->toDateString(),
                    'total_amount' => (float) $booking->total_amount,
                    'provider_name' => $booking->provider?->business_name,
                    'item_name' => $booking->variant?->item?->name,
                    'item_slug' => $booking->variant?->item?->slug,
                ];
            });

        return Inertia::render('Renter/Bookings/Index', [
            'bookings' => $bookings,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:item_variants,id'],
            'pickup_location_id' => ['nullable', 'integer', 'exists:provider_locations,id'],
            'delivery_address_id' => ['nullable', 'integer', 'exists:user_addresses,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'discount_code' => ['nullable', 'string', 'max:50'],
        ]);

        ItemVariant::query()->whereKey($data['variant_id'])->firstOrFail();

        $booking = $this->bookingService->createBooking($request->user(), $data);

        return redirect()->route('renter.bookings.show', $booking)->with('success', 'Booking created successfully.');
    }

    public function show(Request $request, Booking $booking): Response
    {
        $this->assertBookingOwner($request, $booking);

        $booking->load([
            'provider:id,business_name',
            'variant.item:id,name,slug,brand,condition_rating',
            'pickupLocation',
            'deliveryAddress',
            'payments',
            'timeline',
        ]);

        return Inertia::render('Renter/Bookings/Show', [
            'booking' => $booking,
        ]);
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->assertBookingOwner($request, $booking);

        if (! $booking->canBeCancelledByUser()) {
            return back()->with('error', 'This booking can no longer be cancelled.');
        }

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $updated = $this->bookingService->updateStatus(
            $booking,
            'cancelled',
            $data['reason'] ?? 'Booking cancelled by renter.',
            $request->user()->id
        );

        $updated->update(['cancelled_by' => 'user']);

        return redirect()->route('renter.bookings.index')->with('success', 'Booking cancelled.');
    }

    private function assertBookingOwner(Request $request, Booking $booking): void
    {
        abort_if((int) $booking->user_id !== (int) $request->user()->id, 403, 'Unauthorized booking access.');
    }
}

