<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Book\StoreBookRequest;
use App\Http\Requests\Api\V1\Book\UpdateBookRequest;
use App\Models\Book;
use App\Services\Book\Contracts\BookServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class BookController extends Controller
{
    public function __construct(
        private readonly BookServiceInterface $bookService
    ) {}

    /**
     * Display a listing of books with advanced filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $books = $this->bookService->getAllBooks($request->get('per_page', 15));

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
        $books = $this->bookService->searchBooks($request->all());

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
        $book = $this->bookService->createBook($request->validated());

        return response()->json($book, Response::HTTP_CREATED);
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
        $book = $this->bookService->updateBook($book, $request->validated());

        return response()->json($book);
    }

    /**
     * Remove the specified book.
     */
    public function destroy(Book $book): JsonResponse
    {
        $this->bookService->deleteBook($book);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
} 