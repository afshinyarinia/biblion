<?php

namespace App\Services\Book\Contracts;

use App\Models\Book;
use Illuminate\Pagination\LengthAwarePaginator;

interface BookServiceInterface
{
    public function getAllBooks(int $perPage = 15): LengthAwarePaginator;
    public function searchBooks(array $filters): LengthAwarePaginator;
    public function getBookById(int $id): ?Book;
    public function createBook(array $data): Book;
    public function updateBook(Book $book, array $data): Book;
    public function deleteBook(Book $book): bool;
    public function getBooksByCategory(int $categoryId): LengthAwarePaginator;
    public function getRecommendedBooks(int $userId, int $limit = 10): LengthAwarePaginator;
    public function validateIsbn(string $isbn): bool;
} 