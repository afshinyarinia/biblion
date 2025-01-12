<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingChallengeBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reading_challenge_id',
        'book_id',
        'requirement_key',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(ReadingChallenge::class, 'reading_challenge_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
} 