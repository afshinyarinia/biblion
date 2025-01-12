<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Fiction', 'description' => 'Fictional literature and stories'],
            ['name' => 'Non-Fiction', 'description' => 'Factual and informative books'],
            ['name' => 'Mystery', 'description' => 'Mystery and detective stories'],
            ['name' => 'Science Fiction', 'description' => 'Science fiction and futuristic stories'],
            ['name' => 'Fantasy', 'description' => 'Fantasy and magical stories'],
            ['name' => 'Romance', 'description' => 'Romance and love stories'],
            ['name' => 'Thriller', 'description' => 'Suspense and thriller stories'],
            ['name' => 'Horror', 'description' => 'Horror and scary stories'],
            ['name' => 'Biography', 'description' => 'Biographical works'],
            ['name' => 'History', 'description' => 'Historical works'],
            ['name' => 'Science', 'description' => 'Scientific works'],
            ['name' => 'Technology', 'description' => 'Technology-related books'],
            ['name' => 'Business', 'description' => 'Business and economics books'],
            ['name' => 'Self-Help', 'description' => 'Self-improvement and personal development'],
            ['name' => 'Poetry', 'description' => 'Poetic works'],
            ['name' => 'Drama', 'description' => 'Dramatic works'],
            ['name' => 'Children', 'description' => 'Books for children'],
            ['name' => 'Young Adult', 'description' => 'Books for young adults'],
            ['name' => 'Art', 'description' => 'Art and design books'],
            ['name' => 'Travel', 'description' => 'Travel and geography books'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
} 