<x-mail::message>
# Payment Received

We have received your payment.

**Booking Number:** {{ $payload['booking_number'] ?? '-' }}  
**Amount:** {{ $payload['amount'] ?? '-' }} {{ $payload['currency'] ?? '' }}  
**Transaction ID:** {{ $payload['transaction_id'] ?? '-' }}

<x-mail::button :url="config('app.url')">
View Receipt
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
