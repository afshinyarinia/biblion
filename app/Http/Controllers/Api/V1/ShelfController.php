<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Shelf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_public' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $shelf = Auth::user()->shelves()->create($validator->validated());

        return response()->json($shelf, 201);
    }

    /**
     * Display the specified shelf.
     */
    public function show(Shelf $shelf): JsonResponse
    {
        if (!$shelf->is_public && $shelf->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($shelf->load('books'));
    }

    /**
     * Update the specified shelf.
     */
    public function update(Request $request, Shelf $shelf): JsonResponse
    {
        if ($shelf->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_public' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $shelf->update($validator->validated());

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
    public function addBook(Request $request, Shelf $shelf): JsonResponse
    {
        if ($shelf->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'book_id' => ['required', 'exists:books,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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