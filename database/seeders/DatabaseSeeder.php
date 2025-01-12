<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Seed books from Google Books API
        $this->call(GoogleBooksSeeder::class);

        // Create some shelves for the test user
        $shelves = [
            [
                'name' => 'Favorites',
                'description' => 'My favorite books',
                'is_public' => true,
            ],
            [
                'name' => 'Want to Read',
                'description' => 'Books I want to read',
                'is_public' => false,
            ],
            [
                'name' => 'Currently Reading',
                'description' => 'Books I am currently reading',
                'is_public' => true,
            ],
        ];

        foreach ($shelves as $shelfData) {
            $shelf = $user->shelves()->create($shelfData);
            
            // Add some random books to each shelf
            $shelf->books()->attach(
                Book::inRandomOrder()->take(rand(1, 2))->pluck('id')
            );
        }
    }
}
