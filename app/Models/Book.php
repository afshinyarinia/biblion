<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

final class Book extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = ['average_rating', 'reviews_count'];

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'description',
        'total_pages',
        'cover_image',
        'publisher',
        'publication_date',
        'language',
    ];

    protected $casts = [
        'total_pages' => 'integer',
        'publication_date' => 'date',
    ];

    /**
     * Get the shelves that contain this book.
     */
    public function shelves(): BelongsToMany
    {
        return $this->belongsToMany(Shelf::class)
            ->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BookReview::class);
    }

    public function getAverageRatingAttribute(): ?float
    {
        return $this->reviews()->avg('rating');
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    /**
     * Get the cover image attribute.
     * If it's a relative path, convert it to a full URL.
     */
    public function getCoverImageAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Convert relative path to full URL
        return Storage::disk('public')->url($value);
    }

    /**
     * Set the cover image attribute.
     * If it's a full URL, convert it to a relative path.
     */
    public function setCoverImageAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['cover_image'] = null;
            return;
        }
        $this->attributes['cover_image'] = $value;
        return;

    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->withTimestamps();
    }
}
