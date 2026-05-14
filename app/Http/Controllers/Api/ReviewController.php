<?php

namespace App\Http\Controllers\Api;

use App\Events\ReviewSubmitted;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::query()->with(['user', 'provider', 'item'])->latest();

        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->integer('provider_id'));
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->integer('item_id'));
        }

        if (! $request->boolean('include_private')) {
            $query->where('is_public', true);
        }

        $reviews = $query->paginate($request->integer('per_page', 20));

        return ReviewResource::collection($reviews)->response();
    }

    public function store(CreateReviewRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $review = Review::create($data);
        event(new ReviewSubmitted($review));

        return response()->json([
            'review' => new ReviewResource($review->load(['user', 'provider', 'item'])),
        ], 201);
    }

    public function show(Review $review): JsonResponse
    {
        return response()->json([
            'review' => new ReviewResource($review->load(['user', 'provider', 'item'])),
        ]);
    }

    public function update(Request $request, Review $review): JsonResponse
    {
        abort_unless($review->user_id === $request->user()->id || $request->user()->role === 'admin', 403);

        $validated = $request->validate([
            'rating' => ['sometimes', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string'],
            'item_condition_rating' => ['nullable', 'integer', 'between:1,5'],
            'communication_rating' => ['nullable', 'integer', 'between:1,5'],
            'photos' => ['nullable', 'array'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $review->update($validated);

        return response()->json([
            'review' => new ReviewResource($review->fresh(['user', 'provider', 'item'])),
        ]);
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        abort_unless($review->user_id === $request->user()->id || $request->user()->role === 'admin', 403);
        $review->delete();

        return response()->json(['message' => 'Review deleted.']);
    }
}
