<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function home(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            return redirect()->route(match ($user->role) {
                'admin' => 'admin.dashboard',
                'provider' => 'provider.dashboard',
                default => 'dashboard',
            });
        }

        return Inertia::render('Home', [
            'initialCoords' => [
                'latitude' => (float) $request->query('latitude', config('geolocation.default_latitude', 14.5995)),
                'longitude' => (float) $request->query('longitude', config('geolocation.default_longitude', 120.9842)),
            ],
        ]);
    }

    public function browse(Request $request): Response
    {
        return Inertia::render('Browse', [
            'pageTitle' => 'Browse Items',
            'filters' => [
                'keyword' => $request->query('keyword'),
                'category_id' => $request->query('category_id'),
                'size' => $request->query('size'),
                'color' => $request->query('color'),
                'min_price' => $request->query('min_price'),
                'max_price' => $request->query('max_price'),
                'sort_by' => $request->query('sort_by', 'relevance'),
            ],
        ]);
    }

    public function item(Item $item): Response
    {
        return Inertia::render('ItemShow', [
            'slug' => $item->slug,
        ]);
    }

    public function providers(Request $request): Response
    {
        return Inertia::render('Providers', [
            'initialFilters' => [
                'keyword' => $request->query('keyword'),
                'radius_km' => (int) $request->query('radius_km', 20),
                'latitude' => (float) $request->query('latitude', config('geolocation.default_latitude', 14.5995)),
                'longitude' => (float) $request->query('longitude', config('geolocation.default_longitude', 120.9842)),
            ],
        ]);
    }

    public function apiDocs(Request $request): Response
    {
        return Inertia::render('ApiDocs', [
            'apiBase' => url('/api/v1'),
            'currentDate' => now()->toFormattedDateString(),
            'appUrl' => config('app.url'),
            'isAuthenticated' => $request->user() !== null,
        ]);
    }
}
