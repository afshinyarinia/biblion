<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'subject_type',
        'subject_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Activity types
    public const TYPE_STARTED_READING = 'started_reading';
    public const TYPE_FINISHED_READING = 'finished_reading';
    public const TYPE_REVIEWED = 'reviewed';
    public const TYPE_ADDED_TO_SHELF = 'added_to_shelf';
    public const TYPE_CREATED_SHELF = 'created_shelf';
    public const TYPE_SET_READING_GOAL = 'set_reading_goal';
    public const TYPE_ACHIEVED_READING_GOAL = 'achieved_reading_goal';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log(User $user, string $type, Model $subject, array $metadata = []): self
    {
        return static::create([
            'user_id' => $user->id,
            'type' => $type,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'metadata' => $metadata,
        ]);
    }

    public function getDescriptionAttribute(): string
    {
        return match($this->type) {
            self::TYPE_STARTED_READING => "started reading a book",
            self::TYPE_FINISHED_READING => "finished reading a book",
            self::TYPE_REVIEWED => "wrote a review",
            self::TYPE_ADDED_TO_SHELF => "added a book to shelf",
            self::TYPE_CREATED_SHELF => "created a new shelf",
            self::TYPE_SET_READING_GOAL => "set a new reading goal",
            self::TYPE_ACHIEVED_READING_GOAL => "achieved their reading goal",
            default => "performed an action",
        };
    }
} 