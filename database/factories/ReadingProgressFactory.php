<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\ReadingProgress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingProgressFactory extends Factory
{
    protected $model = ReadingProgress::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'status' => fake()->randomElement(['not_started', 'in_progress', 'completed']),
            'current_page' => fake()->numberBetween(0, 500),
            'reading_time_minutes' => fake()->numberBetween(0, 1000),
            'started_at' => fake()->optional()->date(),
            'completed_at' => fake()->optional()->date(),
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    public function notStarted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'not_started',
            'current_page' => 0,
            'reading_time_minutes' => 0,
            'started_at' => null,
            'completed_at' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'started_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'started_at' => now()->subDays(fake()->numberBetween(2, 60)),
            'completed_at' => now()->subDays(fake()->numberBetween(0, 1)),
            'current_page' => function (array $attributes) {
                return Book::find($attributes['book_id'])?->page_count ?? 500;
            },
        ]);
    }
} 