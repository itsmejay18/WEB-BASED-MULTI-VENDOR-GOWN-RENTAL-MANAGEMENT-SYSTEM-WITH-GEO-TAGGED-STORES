<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Provider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    public function searchItems(array $filters): LengthAwarePaginator
    {
        $query = Item::query()
            ->with(['provider.mainLocation', 'category', 'primaryPhoto', 'pricingTiers', 'tags'])
            ->where('items.is_active', true);

        $this->applyItemFilters($query, $filters);
        $this->applyItemSort($query, $filters);

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function searchProviders(array $filters): LengthAwarePaginator
    {
        $query = Provider::query()
            ->with(['mainLocation'])
            ->verified();

        if (! empty($filters['keyword'])) {
            $query->where('business_name', 'like', '%'.$filters['keyword'].'%');
        }

        if (! empty($filters['category_id'])) {
            $query->whereHas('items', fn (Builder $builder) => $builder->where('category_id', $filters['category_id']));
        }

        if (! empty($filters['latitude']) && ! empty($filters['longitude'])) {
            $this->applyProviderDistanceSelect($query, (float) $filters['latitude'], (float) $filters['longitude']);
            $query->having('distance', '<=', $filters['radius_km'] ?? 10)->orderBy('distance');
        }

        if (($filters['sort_by'] ?? null) === 'rating') {
            $query->orderByDesc('rating');
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    private function applyItemFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['keyword'])) {
            $query->where(function (Builder $keywordQuery) use ($filters) {
                $keywordQuery
                    ->where('items.name', 'like', '%'.$filters['keyword'].'%')
                    ->orWhere('items.description', 'like', '%'.$filters['keyword'].'%')
                    ->orWhere('items.brand', 'like', '%'.$filters['keyword'].'%')
                    ->orWhere('items.designer', 'like', '%'.$filters['keyword'].'%');
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('items.category_id', $filters['category_id']);
        }

        if (! empty($filters['occasion'])) {
            $query->whereHas('tags', function (Builder $tagQuery) use ($filters) {
                $tagQuery
                    ->where('type', 'occasion')
                    ->where('name', 'like', '%'.$filters['occasion'].'%');
            });
        }

        if (! empty($filters['tag'])) {
            $query->whereHas('tags', fn (Builder $tagQuery) => $tagQuery->where('slug', $filters['tag']));
        }

        if (! empty($filters['size'])) {
            $query->whereHas('variants', fn (Builder $builder) => $builder->where('size_label', $filters['size']));
        }

        if (! empty($filters['color'])) {
            $query->whereHas('variants', fn (Builder $builder) => $builder->where('color', $filters['color']));
        }

        if (! empty($filters['brand'])) {
            $query->where('items.brand', $filters['brand']);
        }

        if (! empty($filters['min_price']) || ! empty($filters['max_price'])) {
            $query->whereHas('pricingTiers', function (Builder $priceQuery) use ($filters) {
                if (! empty($filters['min_price'])) {
                    $priceQuery->where('price', '>=', $filters['min_price']);
                }

                if (! empty($filters['max_price'])) {
                    $priceQuery->where('price', '<=', $filters['max_price']);
                }
            });
        }

        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->availableForDates($filters['start_date'], $filters['end_date']);
        }

        if (! empty($filters['latitude']) && ! empty($filters['longitude'])) {
            $this->applyItemDistanceSelect($query, (float) $filters['latitude'], (float) $filters['longitude']);
            $query->having('distance', '<=', (float) ($filters['radius_km'] ?? config('geolocation.default_radius_km', 10)));
        }
    }

    private function applyItemSort(Builder $query, array $filters): void
    {
        $hasLocation = ! empty($filters['latitude']) && ! empty($filters['longitude']);
        $sortBy = $filters['sort_by'] ?? ($hasLocation ? 'distance' : 'newest');

        switch ($sortBy) {
            case 'distance':
                if ($hasLocation) {
                    $query->orderBy('distance');
                } else {
                    $query->orderByDesc('items.created_at');
                }
                break;
            case 'price_low':
                $query->withMin('pricingTiers', 'price')->orderBy('pricing_tiers_min_price');
                break;
            case 'price_high':
                $query->withMax('pricingTiers', 'price')->orderByDesc('pricing_tiers_max_price');
                break;
            case 'rating':
                $query->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating');
                break;
            case 'newest':
                $query->orderByDesc('items.created_at');
                break;
            case 'relevance':
            default:
                if ($hasLocation) {
                    $query->orderBy('distance');
                } else {
                    $query->orderByDesc('items.created_at');
                }
                break;
        }
    }

    private function applyItemDistanceSelect(Builder $query, float $latitude, float $longitude): void
    {
        $sql = '(6371 * acos(cos(radians(?)) * cos(radians(provider_locations.latitude)) * cos(radians(provider_locations.longitude) - radians(?)) + sin(radians(?)) * sin(radians(provider_locations.latitude))))';

        $query
            ->select('items.*')
            ->selectRaw("{$sql} as distance", [$latitude, $longitude, $latitude])
            ->join('providers', 'items.provider_id', '=', 'providers.id')
            ->join('provider_locations', 'providers.id', '=', 'provider_locations.provider_id')
            ->where('provider_locations.is_main', true);
    }

    private function applyProviderDistanceSelect(Builder $query, float $latitude, float $longitude): void
    {
        $sql = '(6371 * acos(cos(radians(?)) * cos(radians(provider_locations.latitude)) * cos(radians(provider_locations.longitude) - radians(?)) + sin(radians(?)) * sin(radians(provider_locations.latitude))))';

        $query
            ->select('providers.*')
            ->selectRaw("{$sql} as distance", [$latitude, $longitude, $latitude])
            ->join('provider_locations', 'providers.id', '=', 'provider_locations.provider_id')
            ->where('provider_locations.is_main', true);
    }
}
