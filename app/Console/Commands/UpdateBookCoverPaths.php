<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdateBookCoverPaths extends Command
{
    protected $signature = 'books:update-cover-paths';
    protected $description = 'Update book cover paths to remove full URLs and store only relative paths';

    public function handle()
    {
        $this->info('Starting to update book cover paths...');
        
        $books = Book::whereNotNull('cover_image')->get();
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($books as $book) {
            try {
                $currentPath = $book->cover_image;

                // Skip if it's already a relative path
                if (!Str::startsWith($currentPath, ['http://', 'https://', '/'])) {
                    $skipped++;
                    continue;
                }

                // Extract filename from URL or path
                $filename = basename(parse_url($currentPath, PHP_URL_PATH));
                
                // Construct the new relative path
                $newPath = "covers/{$filename}";

                // Update the book
                $book->update(['cover_image' => $newPath]);
                $updated++;

                $this->info("Updated cover path for book: {$book->title}");
                $this->line("  From: {$currentPath}");
                $this->line("  To: {$newPath}");

            } catch (\Exception $e) {
                $errors++;
                $this->error("Error updating book {$book->title}: {$e->getMessage()}");
            }
        }

        $this->info("\nUpdate completed:");
        $this->info("- Updated: {$updated}");
        $this->info("- Skipped (already relative): {$skipped}");
        $this->info("- Errors: {$errors}");
        $this->info("- Total processed: " . $books->count());
    }
} 