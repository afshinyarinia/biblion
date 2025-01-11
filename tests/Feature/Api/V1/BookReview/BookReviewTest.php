<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\BookReview;

use App\Models\Book;
use App\Models\BookReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
    }

    public function test_user_can_list_book_reviews(): void
    {
        BookReview::factory()->count(3)->create([
            'book_id' => $this->book->id,
            'contains_spoilers' => false
        ]);

        BookReview::factory()->create([
            'book_id' => $this->book->id,
            'contains_spoilers' => true
        ]);

        $response = $this->getJson("/api/v1/books/{$this->book->id}/reviews");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'rating',
                        'review',
                        'contains_spoilers',
                        'created_at',
                        'updated_at',
                        'user' => [
                            'id',
                            'name'
                        ]
                    ]
                ],
                'meta',
                'links'
            ])
            ->assertJsonCount(4, 'data');

        // Test filtering out spoilers
        $response = $this->getJson("/api/v1/books/{$this->book->id}/reviews?spoilers=false");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_book_review(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson("/api/v1/books/{$this->book->id}/reviews", [
            'rating' => 5,
            'review' => 'Great book!',
            'contains_spoilers' => false
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'rating',
                'review',
                'contains_spoilers',
                'created_at',
                'updated_at'
            ]);

        $this->assertDatabaseHas('book_reviews', [
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
            'rating' => 5,
            'review' => 'Great book!',
            'contains_spoilers' => false
        ]);
    }

    public function test_user_cannot_review_same_book_twice(): void
    {
        $this->actingAs($this->user);

        BookReview::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->postJson("/api/v1/books/{$this->book->id}/reviews", [
            'rating' => 5,
            'review' => 'Great book!',
            'contains_spoilers' => false
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'You have already reviewed this book');
    }

    public function test_user_can_update_their_review(): void
    {
        $this->actingAs($this->user);

        $review = BookReview::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
            'rating' => 3,
            'review' => 'Good book',
            'contains_spoilers' => false
        ]);

        $response = $this->putJson("/api/v1/books/{$this->book->id}/reviews/{$review->id}", [
            'rating' => 5,
            'review' => 'Amazing book!',
            'contains_spoilers' => true
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'rating',
                'review',
                'contains_spoilers',
                'created_at',
                'updated_at'
            ]);

        $this->assertDatabaseHas('book_reviews', [
            'id' => $review->id,
            'rating' => 5,
            'review' => 'Amazing book!',
            'contains_spoilers' => true
        ]);
    }

    public function test_user_cannot_update_others_review(): void
    {
        $this->actingAs($this->user);

        $otherUser = User::factory()->create();
        $review = BookReview::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $otherUser->id
        ]);

        $response = $this->putJson("/api/v1/books/{$this->book->id}/reviews/{$review->id}", [
            'rating' => 5,
            'review' => 'Amazing book!',
            'contains_spoilers' => true
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_their_review(): void
    {
        $this->actingAs($this->user);

        $review = BookReview::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->deleteJson("/api/v1/books/{$this->book->id}/reviews/{$review->id}");

        $response->assertNoContent();

        $this->assertSoftDeleted('book_reviews', [
            'id' => $review->id
        ]);
    }

    public function test_user_cannot_delete_others_review(): void
    {
        $this->actingAs($this->user);

        $otherUser = User::factory()->create();
        $review = BookReview::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $otherUser->id
        ]);

        $response = $this->deleteJson("/api/v1/books/{$this->book->id}/reviews/{$review->id}");

        $response->assertForbidden();
    }

    public function test_user_can_list_their_reviews(): void
    {
        $this->actingAs($this->user);

        BookReview::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson(route('api.v1.user.reviews'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'rating',
                        'review',
                        'contains_spoilers',
                        'created_at',
                        'updated_at',
                        'book' => [
                            'id',
                            'title',
                            'author'
                        ]
                    ]
                ],
                'links'
            ])
            ->assertJsonCount(3, 'data');
    }
}
