<?php

use App\Models\Activity;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->followedUser = User::factory()->create();
    $this->otherUser = User::factory()->create();

    // User follows followedUser but not otherUser
    $this->user->following()->attach($this->followedUser);
});

test('user can view their activity feed', function () {
    $book = Book::factory()->create();

    // Create activity for followed user
    Activity::log(
        $this->followedUser,
        Activity::TYPE_STARTED_READING,
        $book,
        ['current_page' => 1]
    );

    // Create activity for non-followed user (shouldn't appear in feed)
    Activity::log(
        $this->otherUser,
        Activity::TYPE_STARTED_READING,
        $book,
        ['current_page' => 1]
    );

    $response = $this->actingAs($this->user)
        ->getJson(route('api.v1.feed.index'));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'type',
                    'user' => [
                        'id',
                        'name',
                    ],
                    'subject' => [
                        'id',
                        'title',
                    ],
                    'metadata',
                    'created_at',
                ],
            ],
        ]);

    expect($response->json('data.0.user.id'))->toBe($this->followedUser->id);
});

test('user can view their own activities', function () {
    $book = Book::factory()->create();

    // Create multiple activities for the user
    Activity::log(
        $this->user,
        Activity::TYPE_STARTED_READING,
        $book,
        ['current_page' => 1]
    );

    Activity::log(
        $this->user,
        Activity::TYPE_FINISHED_READING,
        $book,
        ['current_page' => $book->total_pages]
    );

    $response = $this->actingAs($this->user)
        ->getJson(route('api.v1.activities.index'));

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'type',
                    'subject' => [
                        'id',
                        'title',
                    ],
                    'metadata',
                    'created_at',
                ],
            ],
        ]);
});
