<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

uses()->beforeEach(function () {
    $this->withoutMiddleware(
        \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class
    );
});

test('authenticated users can create their own task', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(
        route('dashboard.tasks.store'),
        [
            'title' => 'Prepare project documentation',
            'description' => 'Document the TaskFlow API.',
            'status' => 'pending',
        ]
    );

    $response->assertCreated()
        ->assertJsonPath('message', 'Task created successfully.')
        ->assertJsonPath('data.title', 'Prepare project documentation');

    $this->assertDatabaseHas('tasks', [
        'user_id' => $user->id,
        'title' => 'Prepare project documentation',
        'status' => 'pending',
    ]);
});

test('task creation rejects invalid input', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(
        route('dashboard.tasks.store'),
        [
            'title' => '',
            'status' => 'invalid',
        ]
    );

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'status']);

    expect(Task::count())->toBe(0);
});

test('guests cannot create tasks', function () {
    $response = $this->postJson(
        route('dashboard.tasks.store'),
        ['title' => 'Unauthorized task']
    );

    $response->assertUnauthorized();

    expect(Task::count())->toBe(0);
});

test('task creation uses the authenticated user as owner', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    $response = $this->actingAs($user)->postJson(
        route('dashboard.tasks.store'),
        [
            'title' => 'My task',
            'user_id' => $anotherUser->id,
        ]
    );

    $response->assertCreated();

    $this->assertDatabaseHas('tasks', [
        'title' => 'My task',
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseMissing('tasks', [
        'title' => 'My task',
        'user_id' => $anotherUser->id,
    ]);
});

test('a visitor can view the registration form', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Create your account')
        ->assertSee('password_confirmation');
});

test('a visitor can register and is signed in', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Alex Developer',
        'email' => 'alex@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
    ]);

    $response->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'name' => 'Alex Developer',
        'email' => 'alex@example.com',
    ]);
});

test('registration rejects duplicate email addresses', function () {
    User::factory()->create([
        'email' => 'alex@example.com',
    ]);

    $this->from(route('register'))
        ->post(route('register.store'), [
            'name' => 'Another Alex',
            'email' => 'alex@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])
        ->assertSessionHasErrors('email');
});

test('registration requires matching passwords', function () {
    $this->from(route('register'))
        ->post(route('register.store'), [
            'name' => 'Alex Developer',
            'email' => 'alex@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'DifferentPass123!',
        ])
        ->assertSessionHasErrors('password');

    $this->assertDatabaseMissing('users', [
        'email' => 'alex@example.com',
    ]);
});
