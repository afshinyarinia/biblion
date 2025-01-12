<?php

namespace Tests\Feature\Api\V1\ReadingGoal;

use App\Models\Book;
use App\Models\ReadingGoal;
use App\Models\ReadingProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingGoalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->book = Book::factory()->create(['total_pages' => 200]);
    }

    public function test_user_can_create_reading_goal(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('api.v1.reading-goals.store'), [
            'year' => date('Y'),
            'target_books' => 12,
            'target_pages' => 3600,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'year',
                'target_books',
                'target_pages',
            ]);

        $this->assertDatabaseHas('reading_goals', [
            'user_id' => $this->user->id,
            'year' => date('Y'),
            'target_books' => 12,
            'target_pages' => 3600,
        ]);
    }

    public function test_user_cannot_create_duplicate_goal_for_same_year(): void
    {
        ReadingGoal::factory()->create([
            'user_id' => $this->user->id,
            'year' => date('Y'),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('api.v1.reading-goals.store'), [
            'year' => date('Y'),
            'target_books' => 12,
            'target_pages' => 3600,
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_update_reading_goal(): void
    {
        $goal = ReadingGoal::factory()->create([
            'user_id' => $this->user->id,
            'year' => date('Y'),
            'target_books' => 12,
            'target_pages' => 3600,
        ]);

        $response = $this->actingAs($this->user)->putJson(route('api.v1.reading-goals.update', $goal), [
            'target_books' => 24,
            'target_pages' => 7200,
        ]);

        $response->assertOk()
            ->assertJson([
                'target_books' => 24,
                'target_pages' => 7200,
            ]);
    }

    public function test_goal_is_marked_completed_when_targets_are_met(): void
    {
        $goal = ReadingGoal::factory()->create([
            'user_id' => $this->user->id,
            'year' => date('Y'),
            'target_books' => 1,
            'target_pages' => 200,
            'is_completed' => false,
        ]);

        // Create a completed reading progress
        ReadingProgress::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->putJson(route('api.v1.reading-goals.update', $goal), [
            'target_books' => 1,
            'target_pages' => 200,
        ]);

        $response->assertOk()
            ->assertJson([
                'is_completed' => true,
                'books_read' => 1,
                'pages_read' => 200,
                'books_progress' => 100,
                'pages_progress' => 100,
            ]);
    }

    public function test_user_can_view_current_year_goal(): void
    {
        $goal = ReadingGoal::factory()->create([
            'user_id' => $this->user->id,
            'year' => date('Y'),
        ]);

        $response = $this->actingAs($this->user)->getJson(route('api.v1.reading-goals.current'));

        $response->assertOk()
            ->assertJson([
                'id' => $goal->id,
                'year' => date('Y'),
            ]);
    }

    public function test_user_can_list_all_reading_goals(): void
    {
        // Create goals for three consecutive years
        $currentYear = (int) date('Y');
        ReadingGoal::factory()->forYear($currentYear)->create(['user_id' => $this->user->id]);
        ReadingGoal::factory()->forYear($currentYear + 1)->create(['user_id' => $this->user->id]);
        ReadingGoal::factory()->forYear($currentYear + 2)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson(route('api.v1.reading-goals.index'));

        $response->assertOk()
            ->assertJsonCount(3);
    }

    public function test_user_can_delete_reading_goal(): void
    {
        $goal = ReadingGoal::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('api.v1.reading-goals.destroy', $goal));

        $response->assertStatus(204);
        $this->assertSoftDeleted('reading_goals', ['id' => $goal->id]);
    }

    public function test_user_cannot_access_other_users_goals(): void
    {
        $otherUser = User::factory()->create();
        $goal = ReadingGoal::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('api.v1.reading-goals.show', $goal));

        $response->assertStatus(403);
    }
}
