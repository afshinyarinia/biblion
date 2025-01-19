<?php

namespace App\Repositories\Shelf\Contracts;

use App\Models\Book;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface ShelfRepositoryInterface
{
    public function getUserShelves(User $user, int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?Shelf;
    public function create(User $user, array $data): Shelf;
    public function update(Shelf $shelf, array $data): Shelf;
    public function delete(Shelf $shelf): bool;
    public function addBook(Shelf $shelf, Book $book): void;
    public function removeBook(Shelf $shelf, Book $book): void;
    public function hasBook(Shelf $shelf, Book $book): bool;
} 