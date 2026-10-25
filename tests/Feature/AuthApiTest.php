<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

test('a visitor can register an account', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Alex Developer',
        'email' => 'alex@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Registration successful.')
        ->assertJsonPath('user.name', 'Alex Developer')
        ->assertJsonPath('user.email', 'alex@example.com')
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
                'created_at',
            ],
            'token',
        ])
        ->assertJsonMissingPath('user.password')
        ->assertJsonMissingPath('user.remember_token');

    $this->assertDatabaseHas('users', [
        'email' => 'alex@example.com',
    ]);

    $this->assertDatabaseCount('personal_access_tokens', 1);

    $user = User::where('email', 'alex@example.com')->firstOrFail();

    expect($user->password)->not->toBe('SecurePass123!');
    expect(PersonalAccessToken::count())->toBe(1);
});

test('registration rejects an email address that already exists', function () {
    User::factory()->create([
        'email' => 'alex@example.com',
    ]);

    $this->postJson('/api/register', [
        'name' => 'Another Alex',
        'email' => 'alex@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('registration requires matching confirmed passwords', function () {
    $this->postJson('/api/register', [
        'name' => 'Alex Developer',
        'email' => 'alex@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'DifferentPass123!',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

test('a registered user can log in', function () {
    $user = User::factory()->create([
        'email' => 'alex@example.com',
        'password' => 'SecurePass123!',
    ]);

    $this->postJson('/api/login', [
        'email' => 'alex@example.com',
        'password' => 'SecurePass123!',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Login successful.')
        ->assertJsonPath('user.email', 'alex@example.com')
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
                'created_at',
            ],
            'token',
        ])
        ->assertJsonMissingPath('user.password')
        ->assertJsonMissingPath('user.remember_token');

    $this->assertDatabaseCount('personal_access_tokens', 1);
});

test('login rejects an incorrect password', function () {
    User::factory()->create([
        'email' => 'alex@example.com',
        'password' => 'SecurePass123!',
    ]);

    $this->postJson('/api/login', [
        'email' => 'alex@example.com',
        'password' => 'WrongPassword123!',
    ])
        ->assertUnauthorized()
        ->assertJsonPath(
            'message',
            'The provided credentials are incorrect.'
        );

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('login rejects an email address that does not exist', function () {
    $this->postJson('/api/login', [
        'email' => 'unknown@example.com',
        'password' => 'SecurePass123!',
    ])
        ->assertUnauthorized()
        ->assertJsonPath(
            'message',
            'The provided credentials are incorrect.'
        );

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('an authenticated user can retrieve their safe user profile', function () {
    $user = User::factory()->create([
        'name' => 'Alex Developer',
        'email' => 'alex@example.com',
    ]);

    $token = $user->createToken('test-token')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('name', 'Alex Developer')
        ->assertJsonPath('email', 'alex@example.com')
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'created_at',
        ])
        ->assertJsonMissingPath('password')
        ->assertJsonMissingPath('remember_token');
});

test('a user can log out and revoke their current token', function () {
    $user = User::factory()->create();

    $token = $user->createToken('test-token')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logout successful.');

    $this->assertDatabaseCount('personal_access_tokens', 0);

    $this->app['auth']->forgetGuards();

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertUnauthorized();
});

test('login is rate limited after five attempts per minute', function () {
    $payload = [
        'email' => 'unknown@example.com',
        'password' => 'WrongPassword123!',
    ];

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->postJson('/api/login', $payload)
            ->assertUnauthorized();
    }

    $this->postJson('/api/login', $payload)
        ->assertStatus(429);
});
