<x-mail::message>
# Booking Confirmed

Your booking has been successfully created.

**Booking Number:** {{ $payload['booking_number'] ?? '-' }}  
**Provider:** {{ $payload['provider_name'] ?? '-' }}  
**Rental Dates:** {{ $payload['start_date'] ?? '-' }} to {{ $payload['end_date'] ?? '-' }}  
**Total Amount:** {{ $payload['total_amount'] ?? '-' }}

<x-mail::button :url="config('app.url')">
View Booking
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
