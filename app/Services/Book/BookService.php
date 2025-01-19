<?php

namespace App\Services\Book;

use App\Models\Book;
use App\Repositories\Book\Contracts\BookRepositoryInterface;
use App\Services\Book\Contracts\BookServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class BookService implements BookServiceInterface
{
    public function __construct(
        private readonly BookRepositoryInterface $bookRepository
    ) {}

    public function getAllBooks(int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookRepository->paginate($perPage);
    }

    public function searchBooks(array $filters): LengthAwarePaginator
    {
        return $this->bookRepository->search($filters);
    }

    public function getBookById(int $id): ?Book
    {
        return $this->bookRepository->findById($id);
    }

    public function createBook(array $data): Book
    {
        // Validate ISBN if provided
        if (isset($data['isbn'])) {
            if (!$this->validateIsbn($data['isbn'])) {
                throw new \InvalidArgumentException('Invalid ISBN format');
            }

            // Check for duplicate ISBN
            if ($this->bookRepository->findByIsbn($data['isbn'])) {
                throw new \InvalidArgumentException('ISBN already exists');
            }
        }

        // Process cover image if provided
        if (isset($data['cover_image']) && Str::startsWith($data['cover_image'], ['http://', 'https://'])) {
            // Here you might want to download and store the image
            // For now, we'll just use the URL
        }

        return $this->bookRepository->create($data);
    }

    public function updateBook(Book $book, array $data): Book
    {
        // Validate ISBN if being updated
        if (isset($data['isbn']) && $data['isbn'] !== $book->isbn) {
            if (!$this->validateIsbn($data['isbn'])) {
                throw new \InvalidArgumentException('Invalid ISBN format');
            }

            // Check for duplicate ISBN
            if ($this->bookRepository->findByIsbn($data['isbn'])) {
                throw new \InvalidArgumentException('ISBN already exists');
            }
        }

        // Process cover image if being updated
        if (isset($data['cover_image']) && Str::startsWith($data['cover_image'], ['http://', 'https://'])) {
            // Here you might want to download and store the image
            // For now, we'll just use the URL
        }

        return $this->bookRepository->update($book, $data);
    }

    public function deleteBook(Book $book): bool
    {
        // Add any additional logic before deletion (e.g., check for related records)
        return $this->bookRepository->delete($book);
    }

    public function getBooksByCategory(int $categoryId): LengthAwarePaginator
    {
        return $this->bookRepository->getByCategory($categoryId);
    }

    public function getRecommendedBooks(int $userId, int $limit = 10): LengthAwarePaginator
    {
        return $this->bookRepository->getRecommended($userId, $limit);
    }

    public function validateIsbn(string $isbn): bool
    {
        // Remove any hyphens or spaces from the ISBN
        $isbn = str_replace(['-', ' '], '', $isbn);

        // Check if it's ISBN-13 (most common nowadays)
        if (strlen($isbn) === 13) {
            return $this->validateIsbn13($isbn);
        }

        // Check if it's ISBN-10
        if (strlen($isbn) === 10) {
            return $this->validateIsbn10($isbn);
        }

        return false;
    }

    private function validateIsbn13(string $isbn): bool
    {
        if (!preg_match('/^[0-9]{13}$/', $isbn)) {
            return false;
        }

        $check = 0;
        for ($i = 0; $i < 13; $i += 2) {
            $check += (int)$isbn[$i];
        }
        for ($i = 1; $i < 12; $i += 2) {
            $check += 3 * (int)$isbn[$i];
        }

        return $check % 10 === 0;
    }

    private function validateIsbn10(string $isbn): bool
    {
        if (!preg_match('/^[0-9]{9}[0-9X]$/', $isbn)) {
            return false;
        }

        $check = 0;
        for ($i = 0; $i < 9; $i++) {
            $check += (10 - $i) * (int)$isbn[$i];
        }

        $last = strtoupper($isbn[9]);
        $check += ($last === 'X') ? 10 : (int)$last;

        return $check % 11 === 0;
    }
} 