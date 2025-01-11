<?php

use App\Models\Book;
use App\Models\Shelf;
use App\Models\User;
use function Pest\Laravel\{getJson, postJson, putJson, deleteJson};

beforeEach(function () {
    $this->artisan('migrate:fresh');
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('auth_token')->plainTextToken;
    $this->headers = ['Authorization' => 'Bearer ' . $this->token];
});

test('user can list their shelves', function () {
    Shelf::factory()->count(3)->create([
        'user_id' => $this->user->id
    ]);

    $response = getJson(route('api.v1.shelves.index'), $this->headers);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'is_public',
                    'books_count',
                    'created_at',
                    'updated_at'
                ]
            ],
            'current_page',
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'links',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total'
        ]);

    expect($response->json('data'))->toHaveCount(3);
});

test('user can create a shelf', function () {
    $shelfData = [
        'name' => 'My Reading List',
        'description' => 'Books I want to read',
        'is_public' => true
    ];

    $response = postJson(route('api.v1.shelves.store'), $shelfData, $this->headers);

    $response->assertStatus(201)
        ->assertJson([
            'name' => 'My Reading List',
            'description' => 'Books I want to read',
            'is_public' => true,
            'user_id' => $this->user->id
        ]);

    expect(Shelf::count())->toBe(1);
});

test('user cannot create shelf with invalid data', function () {
    $response = postJson(route('api.v1.shelves.store'), [
        'name' => '',
        'is_public' => 'invalid'
    ], $this->headers);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'is_public']);
});

test('user can view their private shelf', function () {
    $shelf = Shelf::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Shelf',
        'description' => 'Test Description',
        'is_public' => false
    ]);

    $response = getJson(route('api.v1.shelves.show', $shelf), $this->headers);

    $response->assertOk()
        ->assertJson([
            'id' => $shelf->id,
            'name' => $shelf->name,
            'is_public' => false
        ]);
});

test('user cannot view other users private shelf', function () {
    $otherUser = User::factory()->create();
    $shelf = Shelf::factory()->create([
        'user_id' => $otherUser->id,
        'name' => 'Private Shelf',
        'description' => 'Private Description',
        'is_public' => false
    ]);

    $response = getJson(route('api.v1.shelves.show', $shelf), $this->headers);

    $response->assertStatus(403);
});

test('anyone can view public shelf', function () {
    $otherUser = User::factory()->create();
    $shelf = Shelf::factory()->create([
        'user_id' => $otherUser->id,
        'is_public' => true
    ]);

    $response = getJson(route('api.v1.shelves.show', $shelf));

    $response->assertOk()
        ->assertJson([
            'id' => $shelf->id,
            'name' => $shelf->name,
            'is_public' => true
        ]);
});

test('user can update their shelf', function () {
    $shelf = Shelf::factory()->create([
        'user_id' => $this->user->id
    ]);

    $response = putJson(route('api.v1.shelves.update', $shelf), [
        'name' => 'Updated Name',
        'description' => 'Updated Description',
        'is_public' => false
    ], $this->headers);

    $response->assertOk()
        ->assertJson([
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'is_public' => false
        ]);
});

test('user can delete their shelf', function () {
    $shelf = Shelf::factory()->create([
        'user_id' => $this->user->id
    ]);

    $response = deleteJson(route('api.v1.shelves.destroy', $shelf), [], $this->headers);

    $response->assertStatus(204);
    expect(Shelf::count())->toBe(0);
});

test('user can add book to shelf', function () {
    $shelf = Shelf::factory()->create([
        'user_id' => $this->user->id
    ]);
    $book = Book::factory()->create();

    $response = postJson(route('api.v1.shelves.books.add', $shelf), [
        'book_id' => $book->id
    ], $this->headers);

    $response->assertOk()
        ->assertJson(['message' => 'Book added to shelf']);

    expect($shelf->books()->count())->toBe(1)
        ->and($shelf->books()->first()->id)->toBe($book->id);
});

test('user cannot add same book twice to shelf', function () {
    $shelf = Shelf::factory()->create([
        'user_id' => $this->user->id
    ]);
    $book = Book::factory()->create();
    $shelf->books()->attach($book);

    $response = postJson(route('api.v1.shelves.books.add', $shelf), [
        'book_id' => $book->id
    ], $this->headers);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Book already in shelf']);
});

test('user can remove book from shelf', function () {
    $shelf = Shelf::factory()->create([
        'user_id' => $this->user->id
    ]);
    $book = Book::factory()->create();
    $shelf->books()->attach($book);

    $response = deleteJson(route('api.v1.shelves.books.remove', ['shelf' => $shelf, 'book' => $book]), [], $this->headers);

    $response->assertOk()
        ->assertJson(['message' => 'Book removed from shelf']);

    expect($shelf->books()->count())->toBe(0);
}); 