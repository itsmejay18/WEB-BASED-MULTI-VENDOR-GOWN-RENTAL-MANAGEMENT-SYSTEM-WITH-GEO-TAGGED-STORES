<?php

namespace App\Services;

use App\Mail\BookingStatusChangedMail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendInApp(User $user, string $type, string $title, ?string $body = null, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    public function sendBookingStatusEmail(User $user, array $payload): void
    {
        try {
            Mail::to($user->email)->send(new BookingStatusChangedMail($payload));
        } catch (\Throwable $exception) {
            Log::warning('booking_status_email_failed', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function sendSmsStub(User $user, string $message): void
    {
        Log::info('sms_notification_stub', [
            'user_id' => $user->id,
            'phone' => $user->phone,
            'message' => $message,
        ]);
    }
}
