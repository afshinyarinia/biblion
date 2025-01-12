<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingGoalFactory extends Factory
{
    private static array $usedYears = [];

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'year' => function (array $attributes) {
                $userId = $attributes['user_id'];
                if (!isset(self::$usedYears[$userId])) {
                    self::$usedYears[$userId] = [];
                }

                $year = fake()->unique()->numberBetween(date('Y'), date('Y') + 5);
                while (in_array($year, self::$usedYears[$userId])) {
                    $year = fake()->numberBetween(date('Y'), date('Y') + 5);
                }

                self::$usedYears[$userId][] = $year;
                return $year;
            },
            'target_books' => fake()->numberBetween(1, 100),
            'target_pages' => fake()->numberBetween(1000, 30000),
            'is_completed' => fake()->boolean(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => true,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => false,
        ]);
    }

    public function forYear(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => $year,
        ]);
    }
} 