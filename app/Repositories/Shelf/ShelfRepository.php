<?php

namespace App\Repositories\Shelf;

use App\Models\Book;
use App\Models\Shelf;
use App\Models\User;
use App\Repositories\Shelf\Contracts\ShelfRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ShelfRepository implements ShelfRepositoryInterface
{
    public function __construct(
        private readonly Shelf $model
    ) {}

    public function getUserShelves(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->shelves()
            ->withCount('books')
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Shelf
    {
        return $this->model->find($id);
    }

    public function create(User $user, array $data): Shelf
    {
        return $user->shelves()->create($data);
    }

    public function update(Shelf $shelf, array $data): Shelf
    {
        $shelf->update($data);
        return $shelf->fresh();
    }

    public function delete(Shelf $shelf): bool
    {
        return $shelf->delete();
    }

    public function addBook(Shelf $shelf, Book $book): void
    {
        $shelf->books()->attach($book);
    }

    public function removeBook(Shelf $shelf, Book $book): void
    {
        $shelf->books()->detach($book);
    }

    public function hasBook(Shelf $shelf, Book $book): bool
    {
        return $shelf->books()->where('book_id', $book->id)->exists();
    }
}
