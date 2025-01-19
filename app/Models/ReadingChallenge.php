<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadingChallenge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'requirements',
        'created_by',
        'is_public',
        'is_featured',
    ];

    protected $casts = [
        'requirements' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected $appends = [
        'participants_count',
        'is_active',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'reading_challenge_participants')
            ->withPivot(['progress', 'is_completed', 'completed_at'])
            ->withTimestamps();
    }

    public function books(): HasMany
    {
        return $this->hasMany(ReadingChallengeBook::class);
    }

    public function getParticipantsCountAttribute(): int
    {
        return $this->participants()->count();
    }

    public function getIsActiveAttribute(): bool
    {
        $now = now();
        return $this->start_date <= $now && $this->end_date >= $now;
    }

    public function scopeActive($query)
    {
        return $query->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function userProgress(User $user): ?array
    {
        return $this->participants()
            ->where('user_id', $user->id)
            ->first()
            ?->pivot
            ->progress;
    }

    public function isCompletedByUser(User $user): bool
    {
        return $this->participants()
            ->where('user_id', $user->id)
            ->first()
            ?->pivot
            ->is_completed ?? false;
    }

    public function addParticipant(User $user): void
    {
        $initialProgress = array_map(fn() => 0, $this->requirements);
        
        $this->participants()->attach($user->id, [
            'progress' => json_encode($initialProgress),
            'is_completed' => false,
        ]);
    }

    public function updateProgress(User $user, array $progress): void
    {
        $isCompleted = $this->checkCompletion($progress);
        
        $this->participants()->updateExistingPivot($user->id, [
            'progress' => json_encode($progress),
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? now() : null,
        ]);

        if ($isCompleted) {
            Activity::log($user, Activity::TYPE_COMPLETED_CHALLENGE, $this);
        }
    }

    private function checkCompletion(array $progress): bool
    {
        foreach ($this->requirements as $key => $required) {
            if (($progress[$key] ?? 0) < $required) {
                return false;
            }
        }
        return true;
    }
} 