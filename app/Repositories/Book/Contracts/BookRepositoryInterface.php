<?php

namespace App\Repositories\Book\Contracts;

use App\Models\Book;
use Illuminate\Pagination\LengthAwarePaginator;

interface BookRepositoryInterface
{
    public function findById(int $id): ?Book;
    public function findByIsbn(string $isbn): ?Book;
    public function create(array $data): Book;
    public function update(Book $book, array $data): Book;
    public function delete(Book $book): bool;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function search(array $filters): LengthAwarePaginator;
    public function getByCategory(int $categoryId): LengthAwarePaginator;
    public function getRecommended(int $userId, int $limit = 10): LengthAwarePaginator;
} 