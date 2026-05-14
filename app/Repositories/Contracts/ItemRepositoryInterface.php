<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ItemRepositoryInterface
{
    public function paginated(array $filters = [], int $perPage = 20): LengthAwarePaginator;
}
