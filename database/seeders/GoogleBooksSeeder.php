<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GoogleBooksSeeder extends Seeder
{
    private function getHttpClient()
    {
        $client = Http::timeout(30);

        // Check if proxy is configured
        $proxyUrl = env('HTTP_PROXY');
        if ($proxyUrl) {
            $this->command->info("Using proxy: {$proxyUrl}");
            
            // Configure proxy
            $client = $client->withOptions([
                'proxy' => $proxyUrl,
                'verify' => env('PROXY_VERIFY_SSL', true)
            ]);

            // Add proxy authentication if provided
            $proxyAuth = env('HTTP_PROXY_AUTH');
            if ($proxyAuth) {
                $client = $client->withHeaders([
                    'Proxy-Authorization' => 'Basic ' . base64_encode($proxyAuth)
                ]);
            }
        }

        return $client;
    }

    private function downloadAndSaveImage($imageUrl, $isbn): ?string
    {
        if (empty($imageUrl)) {
            return null;
        }

        try {
            // Get the highest quality image by replacing zoom level
            $highQualityUrl = str_replace('zoom=1', 'zoom=3', $imageUrl);
            $highQualityUrl = str_replace('&edge=curl', '', $highQualityUrl);

            // Download the image
            $response = $this->getHttpClient()->get($highQualityUrl);
            
            if (!$response->successful()) {
                Log::warning("Failed to download high-quality image, falling back to original URL", [
                    'isbn' => $isbn,
                    'url' => $highQualityUrl
                ]);
                
                // Try original URL as fallback
                $response = $this->getHttpClient()->get($imageUrl);
                if (!$response->successful()) {
                    return null;
                }
            }

            // Generate a unique filename
            $extension = 'jpg';  // Google Books typically serves JPG images
            $filename = "covers/{$isbn}.{$extension}";

            // Save the image to storage
            Storage::disk('public')->put($filename, $response->body());

            // Return only the path
            return $filename;

        } catch (\Exception $e) {
            Log::error("Failed to process cover image", [
                'isbn' => $isbn,
                'error' => $e->getMessage(),
                'url' => $imageUrl
            ]);
            return null;
        }
    }

    public function run(): void
    {
        $this->command->info('Starting Google Books seeder...');
        
        // Ensure the covers directory exists
        Storage::disk('public')->makeDirectory('covers');
        
        $queries = ['fiction', 'fantasy', 'science fiction', 'mystery', 'thriller'];
        $totalBooksAdded = 0;
        $totalErrors = 0;

        // Get HTTP client with proxy configuration if set
        $http = $this->getHttpClient();

        foreach ($queries as $query) {
            $this->command->info("Fetching books for query: {$query}");
            
            try {
                $response = $http->get('https://www.googleapis.com/books/v1/volumes', [
                    'q' => $query,
                    'maxResults' => 20,
                    'key' => env('GOOGLE_BOOKS_API_KEY')
                ]);

                if (!$response->successful()) {
                    $error = $response->json()['error']['message'] ?? 'Unknown error';
                    $this->command->error("API request failed for query '{$query}': {$error}");
                    Log::error("Google Books API request failed", [
                        'query' => $query,
                        'status' => $response->status(),
                        'response' => $response->json(),
                        'using_proxy' => !empty(env('HTTP_PROXY'))
                    ]);
                    continue;
                }

                $books = $response->json()['items'] ?? [];
                $this->command->info("Found " . count($books) . " books for query: {$query}");

                foreach ($books as $book) {
                    $volumeInfo = $book['volumeInfo'] ?? [];
                    $isbn = $this->getIsbn($volumeInfo['industryIdentifiers'] ?? []);

                    if (empty($volumeInfo['title'])) {
                        $this->command->warn("Skipping book: Missing title");
                        continue;
                    }

                    if (empty($isbn)) {
                        $this->command->warn("Skipping book '{$volumeInfo['title']}': Missing ISBN");
                        continue;
                    }

                    if (Book::where('isbn', $isbn)->exists()) {
                        $this->command->warn("Skipping book '{$volumeInfo['title']}': ISBN already exists");
                        continue;
                    }

                    try {
                        // Download and save the cover image
                        $coverUrl = $volumeInfo['imageLinks']['thumbnail'] ?? null;
                        $savedImageUrl = $this->downloadAndSaveImage($coverUrl, $isbn);
                        
                        $bookData = [
                            'title' => Str::limit($volumeInfo['title'], 255),
                            'author' => $this->getAuthor($volumeInfo['authors'] ?? []),
                            'isbn' => $isbn,
                            'description' => $volumeInfo['description'] ?? null,
                            'total_pages' => $volumeInfo['pageCount'] ?? 1,
                            'cover_image' => $savedImageUrl,
                            'publisher' => $volumeInfo['publisher'] ?? null,
                            'publication_date' => $this->parseDate($volumeInfo['publishedDate'] ?? null),
                            'language' => $volumeInfo['language'] ?? 'en',
                        ];

                        Book::create($bookData);
                        $totalBooksAdded++;
                        
                        $this->command->info("Added book: {$volumeInfo['title']} (ISBN: {$isbn})" . ($savedImageUrl ? " with cover image" : ""));
                        Log::info("Added book from Google Books API", [
                            'title' => $volumeInfo['title'],
                            'isbn' => $isbn,
                            'has_cover' => !empty($savedImageUrl)
                        ]);
                    } catch (\Exception $e) {
                        $totalErrors++;
                        $this->command->error("Failed to add book '{$volumeInfo['title']}': {$e->getMessage()}");
                        Log::error("Failed to add book from Google Books API", [
                            'title' => $volumeInfo['title'],
                            'error' => $e->getMessage(),
                            'data' => $bookData ?? []
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $totalErrors++;
                $this->command->error("Error processing query '{$query}': {$e->getMessage()}");
                Log::error("Error in Google Books seeder", [
                    'query' => $query,
                    'error' => $e->getMessage(),
                    'using_proxy' => !empty(env('HTTP_PROXY'))
                ]);
            }

            // Add a small delay to avoid hitting rate limits
            sleep(1);
        }

        $this->command->info("Google Books seeder completed:");
        $this->command->info("- Total books added: {$totalBooksAdded}");
        $this->command->info("- Total errors: {$totalErrors}");
    }

    private function getIsbn(array $identifiers): ?string
    {
        foreach ($identifiers as $identifier) {
            if (in_array($identifier['type'] ?? '', ['ISBN_13', 'ISBN_10'])) {
                return $identifier['identifier'];
            }
        }

        return null;
    }

    private function getAuthor(array $authors): string
    {
        return !empty($authors) ? Str::limit($authors[0], 255) : 'Unknown Author';
    }

    private function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        // Handle partial dates (YYYY or YYYY-MM)
        if (strlen($date) === 4) {
            $date .= '-01-01';
        } elseif (strlen($date) === 7) {
            $date .= '-01';
        }

        try {
            return date('Y-m-d', strtotime($date));
        } catch (\Exception $e) {
            Log::warning("Failed to parse date", [
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
