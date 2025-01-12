<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

test('user can follow another user', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('api.v1.users.follow', $this->otherUser));

    $response->assertOk()
        ->assertJson([
            'message' => 'Successfully followed user',
            'user' => [
                'id' => $this->otherUser->id,
                'followers_count' => 1,
                'following_count' => 0,
            ],
        ]);

    expect($this->user->isFollowing($this->otherUser))->toBeTrue();
});

test('user cannot follow themselves', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('api.v1.users.follow', $this->user));

    $response->assertStatus(422)
        ->assertJson([
            'message' => 'You cannot follow yourself',
        ]);
});

test('user cannot follow same user twice', function () {
    // First follow
    $this->actingAs($this->user)
        ->postJson(route('api.v1.users.follow', $this->otherUser));

    // Try to follow again
    $response = $this->actingAs($this->user)
        ->postJson(route('api.v1.users.follow', $this->otherUser));

    $response->assertStatus(422)
        ->assertJson([
            'message' => 'You are already following this user',
        ]);
});

test('user can unfollow another user', function () {
    // First follow the user
    $this->user->following()->attach($this->otherUser);

    $response = $this->actingAs($this->user)
        ->deleteJson(route('api.v1.users.unfollow', $this->otherUser));

    $response->assertOk()
        ->assertJson([
            'message' => 'Successfully unfollowed user',
            'user' => [
                'id' => $this->otherUser->id,
                'followers_count' => 0,
                'following_count' => 0,
            ],
        ]);

    expect($this->user->isFollowing($this->otherUser))->toBeFalse();
});

test('user cannot unfollow user they are not following', function () {
    $response = $this->actingAs($this->user)
        ->deleteJson(route('api.v1.users.unfollow', $this->otherUser));

    $response->assertStatus(422)
        ->assertJson([
            'message' => 'You are not following this user',
        ]);
});

test('user can list their followers', function () {
    // Create some followers
    User::factory()->count(3)->create()->each(function ($follower) {
        $follower->following()->attach($this->user);
    });

    $response = $this->actingAs($this->user)
        ->getJson(route('api.v1.followers.index'));

    $response->assertOk()
        ->assertJsonCount(3)
        ->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'email',
                'followers_count',
                'following_count',
            ],
        ]);
});

test('user can list users they are following', function () {
    // Follow some users
    User::factory()->count(3)->create()->each(function ($followed) {
        $this->user->following()->attach($followed);
    });

    $response = $this->actingAs($this->user)
        ->getJson(route('api.v1.following.index'));

    $response->assertOk()
        ->assertJsonCount(3)
        ->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'email',
                'followers_count',
                'following_count',
            ],
        ]);
}); 