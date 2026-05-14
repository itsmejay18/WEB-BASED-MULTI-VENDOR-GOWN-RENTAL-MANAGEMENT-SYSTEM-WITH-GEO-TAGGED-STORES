<?php

namespace App\Http\Controllers\Web\Provider;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $provider = $request->user()?->provider;
        if (! $provider) {
            return redirect()->route('provider.dashboard')->with('error', 'Provider profile not found.');
        }

        $bookings = Booking::query()
            ->where('provider_id', $provider->id)
            ->with(['user:id,first_name,last_name,email', 'variant.item:id,name,slug'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(function (Booking $booking): array {
                return [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status' => $booking->status,
                    'payment_status' => $booking->payment_status,
                    'start_date' => $booking->start_date?->toDateString(),
                    'end_date' => $booking->end_date?->toDateString(),
                    'total_amount' => (float) $booking->total_amount,
                    'renter_name' => trim(($booking->user?->first_name ?? '').' '.($booking->user?->last_name ?? '')),
                    'renter_email' => $booking->user?->email,
                    'item_name' => $booking->variant?->item?->name,
                    'item_slug' => $booking->variant?->item?->slug,
                ];
            });

        return Inertia::render('Provider/Bookings/Index', [
            'bookings' => $bookings,
            'status' => $request->string('status')->toString(),
            'statusOptions' => [
                'pending',
                'approved',
                'rejected',
                'ready_for_pickup',
                'picked_up',
                'returned',
                'completed',
                'cancelled',
            ],
        ]);
    }

    public function approve(Request $request, Booking $booking): RedirectResponse
    {
        $this->assertProviderOwnsBooking($request, $booking);
        $this->bookingService->updateStatus($booking, 'approved', 'Booking approved by provider.', $request->user()->id);

        return back()->with('success', 'Booking approved.');
    }

    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        $this->assertProviderOwnsBooking($request, $booking);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $booking->update(['rejection_reason' => $data['reason']]);
        $this->bookingService->updateStatus($booking, 'rejected', 'Booking rejected: '.$data['reason'], $request->user()->id);

        return back()->with('success', 'Booking rejected.');
    }

    public function ready(Request $request, Booking $booking): RedirectResponse
    {
        $this->assertProviderOwnsBooking($request, $booking);
        $this->bookingService->updateStatus($booking, 'ready_for_pickup', 'Marked ready for pickup.', $request->user()->id);

        return back()->with('success', 'Booking marked ready for pickup.');
    }

    public function pickedUp(Request $request, Booking $booking): RedirectResponse
    {
        $this->assertProviderOwnsBooking($request, $booking);
        $this->bookingService->updateStatus($booking, 'picked_up', 'Item picked up by renter.', $request->user()->id);

        return back()->with('success', 'Booking marked picked up.');
    }

    public function returned(Request $request, Booking $booking): RedirectResponse
    {
        $this->assertProviderOwnsBooking($request, $booking);
        $this->bookingService->updateStatus($booking, 'returned', 'Item returned by renter.', $request->user()->id);

        return back()->with('success', 'Booking marked returned.');
    }

    private function assertProviderOwnsBooking(Request $request, Booking $booking): void
    {
        $providerId = $request->user()?->provider?->id;
        abort_if((int) $booking->provider_id !== (int) $providerId, 403, 'Unauthorized booking action.');
    }
}

