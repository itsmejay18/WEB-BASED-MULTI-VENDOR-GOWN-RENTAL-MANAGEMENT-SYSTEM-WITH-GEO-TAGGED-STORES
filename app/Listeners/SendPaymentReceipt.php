<?php

namespace App\Listeners;

use App\Events\PaymentProcessed;
use App\Mail\PaymentReceivedMail;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendPaymentReceipt implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private readonly NotificationService $notificationService) {}

    public function handle(PaymentProcessed $event): void
    {
        $payment = $event->payment->loadMissing('user', 'booking');

        if (! $payment->user) {
            return;
        }

        Mail::to($payment->user->email)->send(new PaymentReceivedMail([
            'booking_number' => $payment->booking?->booking_number,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'transaction_id' => $payment->transaction_id,
        ]));

        $this->notificationService->sendInApp(
            $payment->user,
            'payment_received',
            'Payment Received',
            'Your payment was received successfully.',
            ['payment_id' => $payment->id]
        );
    }
}
