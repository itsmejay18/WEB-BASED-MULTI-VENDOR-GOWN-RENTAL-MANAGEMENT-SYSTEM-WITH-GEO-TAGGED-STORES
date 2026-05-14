<?php

namespace App\Services;

use App\Events\PaymentProcessed;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentService
{
    public function initiate(Booking $booking, array $payload = []): Payment
    {
        return Payment::create([
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'payment_intent_id' => 'pi_'.Str::uuid()->toString(),
            'amount' => $booking->total_amount,
            'currency' => $payload['currency'] ?? 'PHP',
            'payment_method' => $payload['payment_method'] ?? $booking->payment_method,
            'status' => 'pending',
            'metadata' => $payload['metadata'] ?? [],
        ]);
    }

    public function confirm(Payment $payment, ?string $transactionId = null): Payment
    {
        $payment->status = 'succeeded';
        $payment->transaction_id = $transactionId ?? 'txn_'.Str::uuid()->toString();
        $payment->save();

        $booking = $payment->booking;
        $booking->payment_status = 'paid';
        $booking->save();

        event(new PaymentProcessed($payment));

        return $payment->fresh('booking');
    }

    public function refund(Payment $payment, float $amount, ?string $reason = null): Payment
    {
        $payment->status = 'refunded';
        $payment->refund_amount = $amount;
        $payment->refund_reason = $reason;
        $payment->save();

        $booking = $payment->booking;
        $booking->payment_status = $amount < (float) $payment->amount ? 'partially_refunded' : 'refunded';
        $booking->save();

        return $payment->fresh('booking');
    }

    public function handleWebhook(array $payload): void
    {
        $paymentIntentId = data_get($payload, 'data.object.id');
        if (! $paymentIntentId) {
            return;
        }

        $payment = Payment::where('payment_intent_id', $paymentIntentId)->first();
        if (! $payment) {
            return;
        }

        if (data_get($payload, 'type') === 'payment_intent.succeeded') {
            $this->confirm($payment, data_get($payload, 'data.object.latest_charge'));
        }
    }
}
