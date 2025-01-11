<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'rating',
        'review',
        'contains_spoilers',
    ];

    protected $casts = [
        'rating' => 'integer',
        'contains_spoilers' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
} 