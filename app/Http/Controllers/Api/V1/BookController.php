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

final class BookController extends Controller
{
    /**
     * Display a listing of books.
     */
    public function index(Request $request): JsonResponse
    {
        $books = $this->getFilteredBooks($request->get('search'));

        return response()->json($books);
    }

    /**
     * Search books by title, author, or ISBN.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('search');
        $books = Book::query()
            ->where('title', 'like', "%{$query}%")
            ->orWhere('author', 'like', "%{$query}%")
            ->orWhere('isbn', 'like', "%{$query}%")
            ->paginate();

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
     * Get filtered books query.
     */
    private function getFilteredBooks(?string $search): mixed
    {
        return Book::query()
            ->when(
                $search,
                fn (Builder $query) => $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
            )
            ->paginate(15);
    }
} 