<?php

namespace App\Http\Controllers\Web\Renter;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function show(Item $item): Response
    {
        return Inertia::render('ItemShow', [
            'slug' => $item->slug,
        ]);
    }
}

