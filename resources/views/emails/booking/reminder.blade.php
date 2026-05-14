<x-mail::message>
# Upcoming Rental Reminder

This is a reminder for your upcoming rental.

**Booking Number:** {{ $payload['booking_number'] ?? '-' }}  
**Start Date:** {{ $payload['start_date'] ?? '-' }}  
**End Date:** {{ $payload['end_date'] ?? '-' }}

<x-mail::button :url="config('app.url')">
View Booking
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
