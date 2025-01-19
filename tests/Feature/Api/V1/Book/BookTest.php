<?php

use App\Models\Book;
use App\Models\User;
use function Pest\Laravel\{getJson, postJson, putJson, deleteJson};

beforeEach(function () {
    $this->artisan('migrate:fresh');
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('auth_token')->plainTextToken;
    $this->headers = ['Authorization' => 'Bearer ' . $this->token];
});

test('can list books', function () {
    $books = Book::factory()->count(3)->create();

    $response = getJson(route('api.v1.books.index'), $this->headers);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'isbn',
                    'description',
                    'total_pages',
                    'cover_image',
                    'publisher',
                    'publication_date',
                    'language',
                    'created_at',
                    'updated_at'
                ]
            ]
        ]);

    expect($response->json('data'))->toHaveCount(3);
});

test('can search books', function () {
    Book::factory()->create([
        'title' => 'The Great Gatsby',
        'author' => 'F. Scott Fitzgerald',
        'isbn' => '9780743273565',
        'total_pages' => 180,
        'publication_date' => '1925-04-10',
        'language' => 'en'
    ]);

    Book::factory()->create([
        'title' => 'Pride and Prejudice',
        'author' => 'Jane Austen',
        'isbn' => '9780141439518',
        'total_pages' => 432,
        'publication_date' => '1813-01-28',
        'language' => 'en'
    ]);

    $response = getJson(route('api.v1.books.search', ['search' => 'gatsby']), $this->headers);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'isbn',
                    'description',
                    'total_pages',
                    'cover_image',
                    'publisher',
                    'publication_date',
                    'language',
                    'created_at',
                    'updated_at'
                ]
            ],
            'links',
            'meta'
        ]);

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.title'))->toBe('The Great Gatsby');
});

test('can create a book', function () {
    $bookData = [
        'title' => 'Test Book',
        'author' => 'Test Author',
        'isbn' => '9780743273565',
        'description' => 'Test description',
        'total_pages' => 200,
        'cover_image' => 'https://example.com/cover.jpg',
        'publisher' => 'Test Publisher',
        'publication_date' => '2023-01-01',
        'language' => 'en'
    ];

    $response = postJson(route('api.v1.books.store'), $bookData, $this->headers);

    $response->assertStatus(201)
        ->assertJson([
            'title' => 'Test Book',
            'author' => 'Test Author',
            'isbn' => '9780743273565'
        ]);

    expect(Book::count())->toBe(1);
});

test('cannot create book with invalid data', function () {
    $response = postJson(route('api.v1.books.store'), [
        'title' => '',
        'author' => ''
    ], $this->headers);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'author']);
});

test('cannot create book with invalid isbn', function () {
    $bookData = [
        'title' => 'Test Book',
        'author' => 'Test Author',
        'isbn' => '1234567890', // Invalid ISBN
        'description' => 'Test description',
        'total_pages' => 200,
        'language' => 'en'
    ];

    $response = postJson(route('api.v1.books.store'), $bookData, $this->headers);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['isbn']);
});

test('cannot create book with duplicate isbn', function () {
    // Create first book
    Book::factory()->create([
        'isbn' => '9780141439518'
    ]);

    // Try to create another book with same ISBN
    $bookData = [
        'title' => 'Test Book',
        'author' => 'Test Author',
        'isbn' => '9780141439518',
        'description' => 'Test description',
        'total_pages' => 200,
        'language' => 'en'
    ];

    $response = postJson(route('api.v1.books.store'), $bookData, $this->headers);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['isbn'])
        ->assertJson([
            'errors' => [
                'isbn' => ['This ISBN already exists.']
            ]
        ]);
});

test('can create book with valid isbn-10', function () {
    $bookData = [
        'title' => 'Test Book',
        'author' => 'Test Author',
        'isbn' => '0743273567', // Valid ISBN-10 for The Great Gatsby
        'description' => 'Test description',
        'total_pages' => 200,
        'language' => 'en'
    ];

    $response = postJson(route('api.v1.books.store'), $bookData, $this->headers);

    $response->assertStatus(201)
        ->assertJson([
            'isbn' => '0743273567'
        ]);
});

test('can view a book', function () {
    $book = Book::factory()->create();

    $response = getJson(route('api.v1.books.show', $book), $this->headers);

    $response->assertOk()
        ->assertJson([
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author
        ]);
});

test('can update a book', function () {
    $book = Book::factory()->create();

    $response = putJson(route('api.v1.books.update', $book), [
        'title' => 'Updated Title',
        'author' => 'Updated Author',
        'isbn' => '9780141439518',
        'total_pages' => 200,
        'cover_image' => 'https://example.com/cover.jpg',
        'publisher' => 'Updated Publisher',
        'publication_date' => '2023-01-01',
        'language' => 'en'
    ], $this->headers);

    $response->assertOk()
        ->assertJson([
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'isbn' => '9780141439518'
        ]);

    expect(Book::first())
        ->title->toBe('Updated Title')
        ->author->toBe('Updated Author');
});

test('cannot update book with invalid isbn', function () {
    $book = Book::factory()->create();

    $response = putJson(route('api.v1.books.update', $book), [
        'title' => 'Updated Title',
        'author' => 'Updated Author',
        'isbn' => '1234567890', // Invalid ISBN
        'total_pages' => 200,
        'language' => 'en'
    ], $this->headers);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['isbn']);
});

test('can delete a book', function () {
    $book = Book::factory()->create();

    $response = deleteJson(route('api.v1.books.destroy', $book), [], $this->headers);

    $response->assertStatus(204);
    expect(Book::count())->toBe(0);
});
