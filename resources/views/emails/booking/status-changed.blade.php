<x-mail::message>
# Booking Status Updated

Your booking status has changed.

**Booking Number:** {{ $payload['booking_number'] ?? '-' }}  
**Previous Status:** {{ $payload['old_status'] ?? '-' }}  
**Current Status:** {{ $payload['new_status'] ?? '-' }}

<x-mail::button :url="config('app.url')">
View Booking
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
