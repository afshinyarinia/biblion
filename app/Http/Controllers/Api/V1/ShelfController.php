<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shelf\AddBookRequest;
use App\Http\Requests\Api\V1\Shelf\StoreShelfRequest;
use App\Http\Requests\Api\V1\Shelf\UpdateShelfRequest;
use App\Models\Book;
use App\Models\Shelf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

final class ShelfController extends Controller
{
    /**
     * Display a listing of the user's shelves.
     */
    public function index(): JsonResponse
    {
        $shelves = Auth::user()
            ->shelves()
            ->withCount('books')
            ->paginate(15);

        return response()->json($shelves);
    }

    /**
     * Store a newly created shelf.
     */
    public function store(StoreShelfRequest $request): JsonResponse
    {
        $shelf = Auth::user()->shelves()->create($request->validated());

        return response()->json($shelf, 201);
    }

    /**
     * Display the specified shelf.
     */
    public function show(Request $request, Shelf $shelf): JsonResponse
    {
        if (!$shelf->is_public && (!$request->user() || $request->user()->id !== $shelf->user_id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($shelf->load('books'));
    }

    /**
     * Update the specified shelf.
     */
    public function update(UpdateShelfRequest $request, Shelf $shelf): JsonResponse
    {
        $shelf->update($request->validated());

        return response()->json($shelf);
    }

    /**
     * Remove the specified shelf.
     */
    public function destroy(Shelf $shelf): JsonResponse
    {
        if ($shelf->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $shelf->delete();

        return response()->json(null, 204);
    }

    /**
     * Add a book to the shelf.
     */
    public function addBook(AddBookRequest $request, Shelf $shelf): JsonResponse
    {
        $book = Book::findOrFail($request->book_id);
        
        if (!$shelf->books()->where('book_id', $book->id)->exists()) {
            $shelf->books()->attach($book);
            return response()->json(['message' => 'Book added to shelf']);
        }

        return response()->json(['message' => 'Book already in shelf'], 422);
    }

    /**
     * Remove a book from the shelf.
     */
    public function removeBook(Shelf $shelf, Book $book): JsonResponse
    {
        if ($shelf->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $shelf->books()->detach($book);

        return response()->json(['message' => 'Book removed from shelf']);
    }
} 