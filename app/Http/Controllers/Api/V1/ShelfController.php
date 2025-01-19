<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shelf\AddBookRequest;
use App\Http\Requests\Api\V1\Shelf\StoreShelfRequest;
use App\Http\Requests\Api\V1\Shelf\UpdateShelfRequest;
use App\Models\Book;
use App\Models\Shelf;
use App\Services\Shelf\Contracts\ShelfServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ShelfController extends Controller
{
    public function __construct(
        private readonly ShelfServiceInterface $shelfService
    ) {}

    /**
     * Display a listing of the user's shelves.
     */
    public function index(Request $request): JsonResponse
    {
        $shelves = $this->shelfService->getUserShelves($request->user());

        return response()->json($shelves);
    }

    /**
     * Store a newly created shelf.
     */
    public function store(StoreShelfRequest $request): JsonResponse
    {
        $shelf = $this->shelfService->createShelf($request->user(), $request->validated());

        return response()->json($shelf, Response::HTTP_CREATED);
    }

    /**
     * Display the specified shelf.
     */
    public function show(Request $request, Shelf $shelf): JsonResponse
    {
        if (!$this->shelfService->canAccessShelf($request->user(), $shelf)) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        return response()->json($shelf->loadCount('books'));
    }

    /**
     * Update the specified shelf.
     */
    public function update(UpdateShelfRequest $request, Shelf $shelf): JsonResponse
    {
        $shelf = $this->shelfService->updateShelf($shelf, $request->validated());

        return response()->json($shelf);
    }

    /**
     * Remove the specified shelf.
     */
    public function destroy(Shelf $shelf): JsonResponse
    {
        $this->shelfService->deleteShelf($shelf);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Add a book to the shelf.
     */
    public function addBook(AddBookRequest $request, Shelf $shelf): JsonResponse
    {
        $book = Book::findOrFail($request->book_id);
        $this->shelfService->addBookToShelf($shelf, $book);

        return response()->json(['message' => 'Book added to shelf']);
    }

    /**
     * Remove a book from the shelf.
     */
    public function removeBook(Shelf $shelf, Book $book): JsonResponse
    {
        $this->shelfService->removeBookFromShelf($shelf, $book);

        return response()->json(['message' => 'Book removed from shelf']);
    }
} 