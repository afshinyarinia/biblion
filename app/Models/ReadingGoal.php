<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadingGoal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'year',
        'target_books',
        'target_pages',
        'is_completed',
    ];

    protected $casts = [
        'year' => 'integer',
        'target_books' => 'integer',
        'target_pages' => 'integer',
        'is_completed' => 'boolean',
    ];

    protected $appends = [
        'books_read',
        'pages_read',
        'books_progress',
        'pages_progress',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getBooksReadAttribute(): int
    {
        return $this->user->readingProgress()
            ->whereYear('completed_at', $this->year)
            ->where('status', 'completed')
            ->count();
    }

    public function getPagesReadAttribute(): int
    {
        return $this->user->readingProgress()
            ->whereYear('completed_at', $this->year)
            ->where('status', 'completed')
            ->join('books', 'reading_progress.book_id', '=', 'books.id')
            ->sum('books.total_pages');
    }

    public function getBooksProgressAttribute(): float
    {
        if ($this->target_books === 0) {
            return 0;
        }
        return min(100, round(($this->books_read / $this->target_books) * 100, 2));
    }

    public function getPagesProgressAttribute(): float
    {
        if ($this->target_pages === 0) {
            return 0;
        }
        return min(100, round(($this->pages_read / $this->target_pages) * 100, 2));
    }
} 