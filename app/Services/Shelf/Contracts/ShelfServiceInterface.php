<?php

namespace App\Services\Shelf\Contracts;

use App\Models\Book;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface ShelfServiceInterface
{
    public function getUserShelves(User $user, int $perPage = 15): LengthAwarePaginator;
    public function getShelf(int $id): ?Shelf;
    public function createShelf(User $user, array $data): Shelf;
    public function updateShelf(Shelf $shelf, array $data): Shelf;
    public function deleteShelf(Shelf $shelf): bool;
    public function addBookToShelf(Shelf $shelf, Book $book): void;
    public function removeBookFromShelf(Shelf $shelf, Book $book): void;
    public function canAccessShelf(User $user, Shelf $shelf): bool;
    public function canManageShelf(User $user, Shelf $shelf): bool;
}
