<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    protected $appends = [
        'followers_count',
        'following_count',
    ];

    /**
     * Get the shelves that belong to the user.
     */
    public function shelves(): HasMany
    {
        return $this->hasMany(Shelf::class);
    }

    public function readingProgress(): HasMany
    {
        return $this->hasMany(ReadingProgress::class);
    }

    public function readingGoals(): HasMany
    {
        return $this->hasMany(ReadingGoal::class);
    }

    public function createdChallenges(): HasMany
    {
        return $this->hasMany(ReadingChallenge::class, 'created_by');
    }

    public function participatingChallenges(): BelongsToMany
    {
        return $this->belongsToMany(ReadingChallenge::class, 'reading_challenge_participants')
            ->withPivot(['progress', 'is_completed', 'completed_at'])
            ->withTimestamps();
    }

    public function challengeBooks(): HasMany
    {
        return $this->hasMany(ReadingChallengeBook::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function activityFeed()
    {
        $followingIds = $this->following()->pluck('users.id');
        
        return Activity::whereIn('user_id', $followingIds)
            ->with(['user', 'subject'])
            ->latest()
            ->paginate();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    public function getFollowersCountAttribute(): int
    {
        return $this->followers()->count();
    }

    public function getFollowingCountAttribute(): int
    {
        return $this->following()->count();
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    public function isFollowedBy(User $user): bool
    {
        return $this->followers()->where('follower_id', $user->id)->exists();
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }
}
