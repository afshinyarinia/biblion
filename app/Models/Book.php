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
} 