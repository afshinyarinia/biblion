<?php

namespace App\Repositories\Book;

use App\Models\Book;
use App\Repositories\Book\Contracts\BookRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class BookRepository implements BookRepositoryInterface
{
    public function __construct(
        private readonly Book $model
    ) {}

    public function findById(int $id): ?Book
    {
        return $this->model->find($id);
    }

    public function findByIsbn(string $isbn): ?Book
    {
        return $this->model->where('isbn', $isbn)->first();
    }

    public function create(array $data): Book
    {
        return $this->model->create($data);
    }

    public function update(Book $book, array $data): Book
    {
        $book->update($data);
        return $book->fresh();
    }

    public function delete(Book $book): bool
    {
        return $book->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }

    public function search(array $filters): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->with(['categories', 'reviews'])
            ->withCount(['reviews', 'shelves'])
            ->withAvg('reviews', 'rating');

        $this->applySearchFilters($query, $filters);

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function getByCategory(int $categoryId): LengthAwarePaginator
    {
        return $this->model->whereHas('categories', function ($query) use ($categoryId) {
            $query->where('categories.id', $categoryId);
        })->latest()->paginate();
    }

    public function getRecommended(int $userId, int $limit = 10): LengthAwarePaginator
    {
        return $this->model->whereHas('categories', function ($query) use ($userId) {
            $query->whereIn('categories.id', function ($subQuery) use ($userId) {
                $subQuery->select('book_category.category_id')
                    ->from('book_category')
                    ->join('reading_progress', 'book_category.book_id', '=', 'reading_progress.book_id')
                    ->where('reading_progress.user_id', $userId);
            });
        })->latest()->paginate($limit);
    }

    private function applySearchFilters(Builder $query, array $filters): void
    {
        if ($search = $filters['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categories = $filters['categories'] ?? null) {
            $categoryIds = explode(',', $categories);
            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });
        }

        if ($fromDate = $filters['from_date'] ?? null) {
            $query->where('publication_date', '>=', $fromDate);
        }

        if ($toDate = $filters['to_date'] ?? null) {
            $query->where('publication_date', '<=', $toDate);
        }

        if ($language = $filters['language'] ?? null) {
            $query->where('language', $language);
        }

        if ($minRating = $filters['min_rating'] ?? null) {
            $query->having('reviews_avg_rating', '>=', $minRating);
        }

        if ($maxRating = $filters['max_rating'] ?? null) {
            $query->having('reviews_avg_rating', '<=', $maxRating);
        }

        if ($minPages = $filters['min_pages'] ?? null) {
            $query->where('total_pages', '>=', $minPages);
        }

        if ($maxPages = $filters['max_pages'] ?? null) {
            $query->where('total_pages', '<=', $maxPages);
        }

        if ($publisher = $filters['publisher'] ?? null) {
            $query->where('publisher', 'like', "%{$publisher}%");
        }

        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $allowedSortFields = [
            'title',
            'author',
            'publication_date',
            'created_at',
            'reviews_count',
            'shelves_count',
            'reviews_avg_rating'
        ];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }
    }
} 