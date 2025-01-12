<?php

use App\Models\Book;
use App\Models\ReadingChallenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\delete;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->book = Book::factory()->create();
});

test('user can list reading challenges', function () {
    $challenge = ReadingChallenge::factory()->create([
        'is_public' => true,
    ]);

    get(route('reading-challenges.index'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'start_date',
                    'end_date',
                    'requirements',
                    'is_public',
                    'is_featured',
                    'created_by',
                    'participants_count',
                    'creator' => ['id', 'name'],
                ],
            ],
            'meta',
            'links',
        ]);
});

test('user can create a reading challenge', function () {
    $data = [
        'title' => 'Summer Reading Challenge',
        'description' => 'Read 10 books during summer',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addMonths(3)->format('Y-m-d'),
        'requirements' => [
            'fiction' => 5,
            'non_fiction' => 5,
        ],
        'is_public' => true,
    ];

    actingAs($this->user)
        ->post(route('reading-challenges.store'), $data)
        ->assertCreated()
        ->assertJsonFragment([
            'title' => $data['title'],
            'description' => $data['description'],
            'created_by' => $this->user->id,
        ]);
});

test('user cannot create challenge with invalid dates', function () {
    $data = [
        'title' => 'Invalid Challenge',
        'description' => 'Test description',
        'start_date' => now()->subDay()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
        'requirements' => ['any' => 5],
    ];

    actingAs($this->user)
        ->post(route('reading-challenges.store'), $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date', 'end_date']);
});

test('user can view a reading challenge', function () {
    $challenge = ReadingChallenge::factory()->create([
        'is_public' => true,
    ]);

    get(route('reading-challenges.show', $challenge))
        ->assertOk()
        ->assertJsonStructure([
            'id',
            'title',
            'description',
            'start_date',
            'end_date',
            'requirements',
            'is_public',
            'is_featured',
            'created_by',
            'creator' => ['id', 'name'],
            'participants' => [],
        ]);
});

test('user cannot view private challenge of another user', function () {
    $challenge = ReadingChallenge::factory()->create([
        'is_public' => false,
    ]);

    get(route('reading-challenges.show', $challenge))
        ->assertForbidden();
});

test('user can update their reading challenge', function () {
    $challenge = ReadingChallenge::factory()->create([
        'created_by' => $this->user->id,
    ]);

    $data = [
        'title' => 'Updated Challenge',
        'description' => 'Updated description',
    ];

    actingAs($this->user)
        ->put(route('reading-challenges.update', $challenge), $data)
        ->assertOk()
        ->assertJsonFragment($data);
});

test('user cannot update challenge with participants', function () {
    $challenge = ReadingChallenge::factory()->create([
        'created_by' => $this->user->id,
    ]);

    $participant = User::factory()->create();
    $challenge->addParticipant($participant);

    $data = [
        'requirements' => ['new' => 10],
    ];

    actingAs($this->user)
        ->put(route('reading-challenges.update', $challenge), $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['requirements']);
});

test('user can join a reading challenge', function () {
    $challenge = ReadingChallenge::factory()->create([
        'is_public' => true,
        'start_date' => now()->addDay(),
        'end_date' => now()->addMonths(3),
    ]);

    actingAs($this->user)
        ->post(route('reading-challenges.join', $challenge))
        ->assertOk();

    expect($challenge->participants)->toHaveCount(1)
        ->and($challenge->participants->first()->id)->toBe($this->user->id);
});

test('user cannot join inactive challenge', function () {
    $challenge = ReadingChallenge::factory()->create([
        'is_public' => true,
        'start_date' => now()->subMonths(4),
        'end_date' => now()->subMonth(),
    ]);

    actingAs($this->user)
        ->post(route('reading-challenges.join', $challenge))
        ->assertStatus(409);
});

test('user can add book to challenge', function () {
    $challenge = ReadingChallenge::factory()->create([
        'requirements' => ['fiction' => 5],
    ]);

    $challenge->addParticipant($this->user);

    actingAs($this->user)
        ->post(route('reading-challenges.books.add', [$challenge, $this->book]), [
            'requirement_key' => 'fiction',
        ])
        ->assertOk();

    expect($challenge->books)->toHaveCount(1)
        ->and($challenge->books->first()->book_id)->toBe($this->book->id);
});

test('user cannot add same book twice to challenge', function () {
    $challenge = ReadingChallenge::factory()->create([
        'requirements' => ['fiction' => 5],
    ]);

    $challenge->addParticipant($this->user);
    $challenge->books()->create([
        'user_id' => $this->user->id,
        'book_id' => $this->book->id,
        'requirement_key' => 'fiction',
    ]);

    actingAs($this->user)
        ->post(route('reading-challenges.books.add', [$challenge, $this->book]), [
            'requirement_key' => 'fiction',
        ])
        ->assertStatus(409);
});

test('user can remove book from challenge', function () {
    $challenge = ReadingChallenge::factory()->create([
        'requirements' => ['fiction' => 5],
    ]);

    $challenge->addParticipant($this->user);
    $challenge->books()->create([
        'user_id' => $this->user->id,
        'book_id' => $this->book->id,
        'requirement_key' => 'fiction',
    ]);

    actingAs($this->user)
        ->delete(route('reading-challenges.books.remove', [$challenge, $this->book]))
        ->assertOk();

    expect($challenge->books)->toHaveCount(0);
});

test('user can list their participating challenges', function () {
    $challenge = ReadingChallenge::factory()->create();
    $challenge->addParticipant($this->user);

    actingAs($this->user)
        ->get(route('user.reading-challenges'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'participants_count',
                    'creator' => ['id', 'name'],
                ],
            ],
            'meta',
            'links',
        ]);
});