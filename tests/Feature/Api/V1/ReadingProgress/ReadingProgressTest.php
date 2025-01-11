<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\ReadingProgress;

use App\Models\Book;
use App\Models\ReadingProgress;
use App\Models\User;
use function Pest\Laravel\{getJson, putJson};

beforeEach(function () {
    $this->artisan('migrate:fresh');
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('auth_token')->plainTextToken;
    $this->headers = ['Authorization' => 'Bearer ' . $this->token];
    $this->book = Book::factory()->create();
});

test('user can list their reading progress', function () {
    ReadingProgress::factory()->count(3)->create([
        'user_id' => $this->user->id
    ]);

    $response = getJson(route('api.v1.reading-progress.index'), $this->headers);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'book_id',
                    'status',
                    'current_page',
                    'reading_time_minutes',
                    'started_at',
                    'completed_at',
                    'notes'
                ]
            ]
        ]);

    expect($response->json('data'))->toHaveCount(3);
});

test('user can filter reading progress by status', function () {
    ReadingProgress::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'completed'
    ]);
    ReadingProgress::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'in_progress'
    ]);

    $response = getJson(route('api.v1.reading-progress.index', ['status' => 'completed']), $this->headers);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.status'))->toBe('completed');
});

test('user can get reading progress for a specific book', function () {
    $progress = ReadingProgress::factory()->create([
        'user_id' => $this->user->id,
        'book_id' => $this->book->id
    ]);

    $response = getJson(route('api.v1.reading-progress.show', $this->book), $this->headers);

    $response->assertOk()
        ->assertJson([
            'id' => $progress->id,
            'book_id' => $this->book->id,
            'status' => $progress->status
        ]);
});

test('user can update reading progress', function () {
    $progress = ReadingProgress::factory()->create([
        'user_id' => $this->user->id,
        'book_id' => $this->book->id
    ]);

    $response = putJson(route('api.v1.reading-progress.update', $this->book), [
        'status' => 'in_progress',
        'current_page' => 50,
        'reading_time_minutes' => 30,
        'notes' => 'Great book so far!'
    ], $this->headers);

    $response->assertOk()
        ->assertJson([
            'status' => 'in_progress',
            'current_page' => 50,
            'reading_time_minutes' => 30,
            'notes' => 'Great book so far!'
        ]);
});

test('user cannot set current page higher than book page count', function () {
    ReadingProgress::factory()->create([
        'user_id' => $this->user->id,
        'book_id' => $this->book->id
    ]);

    $response = putJson(route('api.v1.reading-progress.update', $this->book), [
        'current_page' => $this->book->total_pages + 1
    ], $this->headers);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['current_page']);
});

test('marking book as completed sets current page to total pages', function () {
    $progress = ReadingProgress::factory()->create([
        'user_id' => $this->user->id,
        'book_id' => $this->book->id,
        'current_page' => 50
    ]);

    $response = putJson(route('api.v1.reading-progress.update', $this->book), [
        'status' => 'completed'
    ], $this->headers);

    $response->assertOk();
    expect($response->json('current_page'))->toBe($this->book->total_pages);
});

test('user can get reading statistics', function () {
    ReadingProgress::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'status' => 'completed'
    ]);
    ReadingProgress::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'status' => 'in_progress'
    ]);

    $response = getJson(route('api.v1.reading-progress.statistics'), $this->headers);

    $response->assertOk()
        ->assertJsonStructure([
            'total_books',
            'completed_books',
            'in_progress_books',
            'total_pages_read',
            'total_reading_time'
        ]);

    expect($response->json('completed_books'))->toBe(3)
        ->and($response->json('in_progress_books'))->toBe(2);
}); 