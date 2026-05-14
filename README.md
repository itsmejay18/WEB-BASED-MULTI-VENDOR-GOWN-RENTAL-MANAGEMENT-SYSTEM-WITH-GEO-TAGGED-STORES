# RentFit API (Laravel 10)

RentFit is a multi-vendor, geo-tagged clothing rental marketplace backend.

## Stack

- Laravel 10.x / PHP 8.1+
- MySQL 8.0+ (recommended for production)
- Sanctum (API auth)
- Scout (search)
- Spatie Permission (roles/permissions)
- Cashier (payments/subscriptions support)
- Horizon + Redis (queues/monitoring)
- Intervention Image (image pipeline)
- MatanYadaev Eloquent Spatial (geo tooling package installed)

## Domain Coverage

Implemented migrations and models for:

- Users/auth profile data: `users`, `user_addresses`, `user_measurements`
- Provider system: `providers`, `provider_locations`, `business_hours`
- Catalog: `categories`, `tags`, `items`, `item_tags`, `item_variants`, `item_photos`, `pricing_tiers`, `availability_calendar`
- Booking flow: `bookings`, `booking_timeline`, `payments`, `reviews`
- User features: `wishlist`, `saved_searches`
- Messaging: `conversations`, `messages`
- Promotions/notifications: `promotions`, `promotion_usage`, `notifications`

Also includes package migrations for Sanctum, Cashier, and Spatie Permission.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure `.env` for MySQL and Redis:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rentfit
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_CLIENT=predis
```

Run migrations:

```bash
php artisan migrate
```

Run local server:

```bash
php artisan serve
```

## API

- `GET /api/v1/search` (items/providers/all)
- `GET /api/v1/user` (Sanctum protected)

## Notes

- Horizon is installed. On Windows, workers may require WSL/Linux for full process supervision support.
- Booking `total_days` is implemented as a stored generated column at the database level.
