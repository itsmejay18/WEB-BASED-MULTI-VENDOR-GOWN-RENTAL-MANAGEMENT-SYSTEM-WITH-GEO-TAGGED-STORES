<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        abort_unless($booking->user_id === $request->user()->id, 403, 'Unauthorized payment access.');

        $payment = $this->paymentService->initiate($booking, $validated);

        return response()->json(['payment' => $payment], 201);
    }

    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
        ]);

        $payment = Payment::findOrFail($validated['payment_id']);
        abort_unless($payment->user_id === $request->user()->id || $request->user()->role === 'admin', 403);

        $payment = $this->paymentService->confirm($payment, $validated['transaction_id'] ?? null);

        return response()->json(['payment' => $payment]);
    }

    public function history(Request $request): JsonResponse
    {
        $payments = Payment::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json($payments);
    }

    public function webhook(Request $request): JsonResponse
    {
        $this->paymentService->handleWebhook($request->all());

        return response()->json(['message' => 'Webhook processed.']);
    }
}
