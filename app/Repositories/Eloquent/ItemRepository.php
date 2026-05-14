<?php

namespace App\Repositories\Eloquent;

use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ItemRepository implements ItemRepositoryInterface
{
    public function paginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Item::query()
            ->with(['provider.mainLocation', 'category', 'primaryPhoto', 'pricingTiers'])
            ->active();

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        if (! empty($filters['size'])) {
            $query->bySize($filters['size']);
        }

        if (! empty($filters['brand'])) {
            $query->byBrand($filters['brand']);
        }

        if (! empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($inner) use ($keyword) {
                $inner
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('brand', 'like', "%{$keyword}%")
                    ->orWhere('designer', 'like', "%{$keyword}%");
            });
        }

        return $query->paginate($perPage);
    }
}
