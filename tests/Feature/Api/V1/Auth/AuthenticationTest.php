<?php

use App\Models\User;
use function Pest\Laravel\{postJson, getJson};

beforeEach(function () {
    // Clear database before each test
    $this->artisan('migrate:fresh');
});

test('user can register', function () {
    $response = postJson(route('api.v1.auth.register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'user' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at'
            ]
        ]);

    expect(User::count())->toBe(1)
        ->and(User::first()->email)->toBe('test@example.com');
});

test('user cannot register with invalid data', function () {
    $response = postJson(route('api.v1.auth.register'), [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'short'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);

    expect(User::count())->toBe(0);
});

test('user can login with correct credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = postJson(route('api.v1.auth.login'), [
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'user'
        ]);
});

test('user cannot login with incorrect credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = postJson(route('api.v1.auth.login'), [
        'email' => 'test@example.com',
        'password' => 'wrong_password'
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Invalid login credentials']);
});

test('user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = postJson(route('api.v1.auth.logout'), [], [
        'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'Successfully logged out']);

    expect($user->tokens()->count())->toBe(0);
});

test('authenticated user can get their profile', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = getJson(route('api.v1.auth.user'), [
        'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertOk()
        ->assertJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
}); 