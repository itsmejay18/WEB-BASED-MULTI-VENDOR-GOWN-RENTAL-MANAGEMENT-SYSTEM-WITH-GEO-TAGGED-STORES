<x-mail::message>
# Welcome to RentFit

Hi {{ $payload['name'] ?? 'there' }},

Thanks for joining RentFit. You can now search outfits, compare nearby providers, and book in minutes.

<x-mail::button :url="config('app.url')">
Start Exploring
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
