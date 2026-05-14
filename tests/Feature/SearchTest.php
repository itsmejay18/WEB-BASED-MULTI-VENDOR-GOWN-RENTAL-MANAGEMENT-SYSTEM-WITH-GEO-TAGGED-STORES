<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SearchTest extends TestCase
{
    public function test_search_route_is_registered(): void
    {
        $route = Route::getRoutes()->match(Request::create('/api/v1/search', 'GET'));
        $this->assertSame('api/v1/search', $route->uri());
    }
}
