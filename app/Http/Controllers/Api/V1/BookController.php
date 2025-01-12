<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Book\StoreBookRequest;
use App\Http\Requests\Api\V1\Book\UpdateBookRequest;
use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class BookController extends Controller
{
    /**
     * Display a listing of books with advanced filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $books = $this->getFilteredBooks($request);

        return response()->json([
            'data' => $books->items(),
            'meta' => [
                'current_page' => $books->currentPage(),
                'from' => $books->firstItem(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'to' => $books->lastItem(),
                'total' => $books->total(),
            ],
            'links' => [
                'first' => $books->url(1),
                'last' => $books->url($books->lastPage()),
                'prev' => $books->previousPageUrl(),
                'next' => $books->nextPageUrl(),
            ]
        ]);
    }

    /**
     * Search books with advanced filtering.
     */
    public function search(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    /**
     * Store a newly created book.
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = Book::create($request->validated());

        return response()->json($book, 201);
    }

    /**
     * Display the specified book.
     */
    public function show(Book $book): JsonResponse
    {
        return response()->json($book);
    }

    /**
     * Update the specified book.
     */
    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        $book->update($request->validated());

        return response()->json($book);
    }

    /**
     * Remove the specified book.
     */
    public function destroy(Book $book): JsonResponse
    {
        $book->delete();

        return response()->json(null, 204);
    }

    /**
     * Get filtered books query with advanced filtering options.
     */
    private function getFilteredBooks(Request $request): mixed
    {
        $query = Book::query()
            ->with(['categories', 'reviews'])
            ->withCount(['reviews', 'shelves'])
            ->withAvg('reviews', 'rating');

        // Basic search
        if ($search = $request->get('search')) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($categories = $request->get('categories')) {
            $categoryIds = explode(',', $categories);
            $query->whereHas('categories', function (Builder $q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });
        }

        // Publication date range
        if ($fromDate = $request->get('from_date')) {
            $query->where('publication_date', '>=', $fromDate);
        }
        if ($toDate = $request->get('to_date')) {
            $query->where('publication_date', '<=', $toDate);
        }

        // Language filter
        if ($language = $request->get('language')) {
            $query->where('language', $language);
        }

        // Rating range
        if ($minRating = $request->get('min_rating')) {
            $query->having('reviews_avg_rating', '>=', $minRating);
        }
        if ($maxRating = $request->get('max_rating')) {
            $query->having('reviews_avg_rating', '<=', $maxRating);
        }

        // Page count range
        if ($minPages = $request->get('min_pages')) {
            $query->where('total_pages', '>=', $minPages);
        }
        if ($maxPages = $request->get('max_pages')) {
            $query->where('total_pages', '<=', $maxPages);
        }

        // Publisher filter
        if ($publisher = $request->get('publisher')) {
            $query->where('publisher', 'like', "%{$publisher}%");
        }

        // Sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSortFields = [
            'title',
            'author',
            'publication_date',
            'created_at',
            'reviews_count',
            'shelves_count',
            'reviews_avg_rating'
        ];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        // Recommendations based on user's history
        if ($request->boolean('recommended') && auth()->check()) {
            $user = auth()->user();
            
            // Get categories from user's reading history
            $userCategories = DB::table('book_category')
                ->join('reading_progress', 'book_category.book_id', '=', 'reading_progress.book_id')
                ->where('reading_progress.user_id', $user->id)
                ->pluck('category_id');

            if ($userCategories->isNotEmpty()) {
                $query->orWhereHas('categories', function (Builder $q) use ($userCategories) {
                    $q->whereIn('categories.id', $userCategories);
                });
            }

            // Consider books from similar users
            $similarUsers = DB::table('reading_progress')
                ->select('user_id')
                ->whereIn('book_id', function ($q) use ($user) {
                    $q->select('book_id')
                        ->from('reading_progress')
                        ->where('user_id', $user->id);
                })
                ->where('user_id', '!=', $user->id)
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) >= 3')
                ->pluck('user_id');

            if ($similarUsers->isNotEmpty()) {
                $query->orWhereExists(function ($q) use ($similarUsers) {
                    $q->select(DB::raw(1))
                        ->from('reading_progress')
                        ->whereColumn('reading_progress.book_id', 'books.id')
                        ->whereIn('reading_progress.user_id', $similarUsers);
                });
            }
        }

        return $query->paginate($request->get('per_page', 15));
    }
} 