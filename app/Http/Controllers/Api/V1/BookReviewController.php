<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookReview\StoreBookReviewRequest;
use App\Http\Requests\Api\V1\BookReview\UpdateBookReviewRequest;
use App\Models\Book;
use App\Models\BookReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class BookReviewController extends Controller
{
    /**
     * List reviews for a book.
     */
    public function index(Request $request, Book $book): JsonResponse
    {
        $reviews = $book->reviews()
            ->with('user:id,name')
            ->when(
                $request->get('spoilers') === 'false',
                fn ($query) => $query->where('contains_spoilers', false)
            )
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'from' => $reviews->firstItem(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'to' => $reviews->lastItem(),
                'total' => $reviews->total(),
            ],
            'links' => [
                'first' => $reviews->url(1),
                'last' => $reviews->url($reviews->lastPage()),
                'prev' => $reviews->previousPageUrl(),
                'next' => $reviews->nextPageUrl(),
            ]
        ]);
    }

    /**
     * Store a new review.
     */
    public function store(StoreBookReviewRequest $request, Book $book): JsonResponse
    {
        $existingReview = $book->reviews()
            ->where('user_id', Auth::id())
            ->exists();

        if ($existingReview) {
            return response()->json(['message' => 'You have already reviewed this book'], 422);
        }

        $review = $book->reviews()->create([
            'user_id' => Auth::id(),
            ...$request->validated()
        ]);

        return response()->json($review, 201);
    }

    /**
     * Update a review.
     */
    public function update(UpdateBookReviewRequest $request, Book $book, BookReview $review): JsonResponse
    {
        $review->update($request->validated());

        return response()->json($review);
    }

    /**
     * Delete a review.
     */
    public function destroy(Book $book, BookReview $review): JsonResponse
    {
        if ($review->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json(null, 204);
    }

    /**
     * Get reviews by a specific user.
     */
    public function userReviews(Request $request): JsonResponse
    {
        $reviews = BookReview::query()
            ->with(['book:id,title,author', 'user:id,name'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return response()->json($reviews);
    }
} 