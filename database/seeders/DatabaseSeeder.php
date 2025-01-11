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

        // Create some books
        $books = [
            [
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'isbn' => '9780743273565',
                'description' => 'The story of the mysteriously wealthy Jay Gatsby and his love for the beautiful Daisy Buchanan.',
                'total_pages' => 180,
                'publisher' => "Charles Scribner's Sons",
                'publication_date' => '1925-04-10',
                'language' => 'en',
            ],
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'isbn' => '9780451524935',
                'description' => 'A dystopian social science fiction novel that follows Winston Smith in a totalitarian superstate.',
                'total_pages' => 328,
                'publisher' => 'Secker and Warburg',
                'publication_date' => '1949-06-08',
                'language' => 'en',
            ],
            [
                'title' => 'To Kill a Mockingbird',
                'author' => 'Harper Lee',
                'isbn' => '9780446310789',
                'description' => 'The story of racial injustice and the loss of innocence in the American South.',
                'total_pages' => 281,
                'publisher' => 'J. B. Lippincott & Co.',
                'publication_date' => '1960-07-11',
                'language' => 'en',
            ],
        ];

        foreach ($books as $bookData) {
            Book::create($bookData);
        }

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
