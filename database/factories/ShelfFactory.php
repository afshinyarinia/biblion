<?php

namespace Database\Factories;

use App\Models\Shelf;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShelfFactory extends Factory
{
    protected $model = Shelf::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'is_public' => fake()->boolean(),
            'user_id' => User::factory(),
        ];
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }
} 