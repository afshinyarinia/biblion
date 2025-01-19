<?php

namespace App\Services\Shelf;

use App\Models\Book;
use App\Models\Shelf;
use App\Models\User;
use App\Repositories\Shelf\Contracts\ShelfRepositoryInterface;
use App\Services\Shelf\Contracts\ShelfServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ShelfService implements ShelfServiceInterface
{
    public function __construct(
        private readonly ShelfRepositoryInterface $shelfRepository
    ) {}

    public function getUserShelves(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->shelfRepository->getUserShelves($user, $perPage);
    }

    public function getShelf(int $id): ?Shelf
    {
        return $this->shelfRepository->findById($id);
    }

    public function createShelf(User $user, array $data): Shelf
    {
        return $this->shelfRepository->create($user, $data);
    }

    public function updateShelf(Shelf $shelf, array $data): Shelf
    {
        if (!$this->canManageShelf(auth()->user(), $shelf)) {
            throw ValidationException::withMessages([
                'shelf' => ['You do not have permission to update this shelf.'],
            ]);
        }

        return $this->shelfRepository->update($shelf, $data);
    }

    public function deleteShelf(Shelf $shelf): bool
    {
        if (!$this->canManageShelf(auth()->user(), $shelf)) {
            throw ValidationException::withMessages([
                'shelf' => ['You do not have permission to delete this shelf.'],
            ]);
        }

        return $this->shelfRepository->delete($shelf);
    }

    public function addBookToShelf(Shelf $shelf, Book $book): void
    {
        if (!$this->canManageShelf(auth()->user(), $shelf)) {
            throw ValidationException::withMessages([
                'shelf' => ['You do not have permission to add books to this shelf.'],
            ]);
        }

        if ($this->shelfRepository->hasBook($shelf, $book)) {
            throw ValidationException::withMessages([
                'book' => ['This book is already in the shelf.'],
            ]);
        }

        $this->shelfRepository->addBook($shelf, $book);
    }

    public function removeBookFromShelf(Shelf $shelf, Book $book): void
    {
        if (!$this->canManageShelf(auth()->user(), $shelf)) {
            throw ValidationException::withMessages([
                'shelf' => ['You do not have permission to remove books from this shelf.'],
            ]);
        }

        $this->shelfRepository->removeBook($shelf, $book);
    }

    public function canAccessShelf(?User $user, Shelf $shelf): bool
    {
        return $shelf->is_public || ($user && $user->id === $shelf->user_id);
    }

    public function canManageShelf(?User $user, Shelf $shelf): bool
    {
        return $user && $user->id === $shelf->user_id;
    }
}
