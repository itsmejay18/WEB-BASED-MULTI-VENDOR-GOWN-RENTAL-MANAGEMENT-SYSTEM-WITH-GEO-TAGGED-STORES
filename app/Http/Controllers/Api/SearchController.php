<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ProviderResource;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function __construct(private readonly SearchService $searchService) {}

    public function __invoke(SearchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $type = $validated['type'] ?? 'all';
        $results = [];

        if ($type === 'all' || $type === 'items') {
            $items = $this->searchService->searchItems($validated);
            $results['items'] = ItemResource::collection($items)->response()->getData(true);
        }

        if ($type === 'all' || $type === 'providers') {
            $providers = $this->searchService->searchProviders($validated);
            $results['providers'] = ProviderResource::collection($providers)->response()->getData(true);
        }

        if ($request->user() !== null && $request->boolean('save_search')) {
            $request->user()->savedSearches()->create([
                'search_name' => $request->input('search_name', 'Saved Search'),
                'search_params' => $validated,
                'notify_on_new' => $request->boolean('notify_on_new', false),
            ]);
        }

        return response()->json($results);
    }
}
