<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'isbn' => fake()->unique()->isbn13(),
            'description' => fake()->paragraph(),
            'total_pages' => fake()->numberBetween(50, 1000),
            'cover_image' => fake()->imageUrl(),
            'publisher' => fake()->company(),
            'publication_date' => fake()->dateTimeBetween('-100 years', 'now'),
            'language' => 'en',
        ];
    }
} 