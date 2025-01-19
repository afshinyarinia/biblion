<?php

namespace Database\Factories;

use App\Models\ReadingChallenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingChallengeFactory extends Factory
{
    protected $model = ReadingChallenge::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate, '+6 months');

        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'requirements' => [
                'total_books' => $this->faker->numberBetween(5, 20),
                'genres' => ['fiction', 'non-fiction'],
                'min_pages' => $this->faker->numberBetween(100, 200),
            ],
            'created_by' => User::factory(),
            'is_public' => $this->faker->boolean(80), // 80% chance of being public
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
        ];
    }

    /**
     * Indicate that the challenge is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the challenge is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the challenge is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the challenge is active (current date between start and end dates).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(1),
        ]);
    }

    /**
     * Indicate that the challenge is completed (end date in the past).
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subDays(5),
        ]);
    }

    /**
     * Indicate that the challenge is upcoming (start date in the future).
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->addDays(5),
            'end_date' => now()->addMonths(2),
        ]);
    }
} 