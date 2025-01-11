<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ReadingProgress extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'reading_progress';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        'current_page',
        'reading_time_minutes',
        'started_at',
        'completed_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'date',
        'completed_at' => 'date',
        'current_page' => 'integer',
        'reading_time_minutes' => 'integer',
    ];

    /**
     * Get the user that owns the reading progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book that the reading progress is for.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Calculate the reading progress percentage.
     */
    public function getProgressPercentageAttribute(): float
    {
        if (!$this->book?->page_count) {
            return 0;
        }

        return round(($this->current_page / $this->book->page_count) * 100, 2);
    }

    /**
     * Get formatted reading time.
     */
    public function getReadingTimeFormattedAttribute(): string
    {
        $hours = floor($this->reading_time_minutes / 60);
        $minutes = $this->reading_time_minutes % 60;

        if ($hours > 0) {
            return sprintf('%d hours %d minutes', $hours, $minutes);
        }

        return sprintf('%d minutes', $minutes);
    }
} 