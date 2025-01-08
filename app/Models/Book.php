<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Book extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'description',
        'publication_year',
        'publisher',
        'language',
        'page_count',
        'cover_image',
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'page_count' => 'integer',
    ];

    /**
     * Get the shelves that contain this book.
     */
    public function shelves(): BelongsToMany
    {
        return $this->belongsToMany(Shelf::class)
            ->withTimestamps();
    }
} 