
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('guests cannot create tasks', function () {
    $this->postJson('/api/tasks', [
        'title' => 'Prepare API tests',
    ])->assertUnauthorized();
});

test('an authenticated user can create their own task', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/tasks', [
        'title' => 'Prepare API tests',
        'description' => 'Write feature tests first.',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('task.title', 'Prepare API tests')
        ->assertJsonPath('task.status', 'pending');

    $this->assertDatabaseHas('tasks', [
        'user_id' => $user->id,
        'title' => 'Prepare API tests',
        'status' => 'pending',
    ]);
});

test('task creation requires a title', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/tasks', [
        'description' => 'A task without a title.',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});


test('guests cannot list tasks', function () {
    $this->getJson('/api/tasks')
        ->assertUnauthorized();
});

test('users can list only their own tasks', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    $user->tasks()->create([
        'title' => 'My first task',
    ]);

    $user->tasks()->create([
        'title' => 'My second task',
    ]);

    $anotherUser->tasks()->create([
        'title' => 'Another users private task',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/tasks');

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('current_page', 1)
        ->assertJsonPath('per_page', 10)
        ->assertJsonPath('total', 2)
        ->assertJsonFragment([
            'title' => 'My first task',
        ])
        ->assertJsonFragment([
            'title' => 'My second task',
        ])
        ->assertJsonMissing([
            'title' => 'Another users private task',
        ]);
});

test('users can filter their tasks by status', function () {
    $user = User::factory()->create();

    $user->tasks()->create([
        'title' => 'Finish documentation',
        'status' => 'pending',
    ]);

    $user->tasks()->create([
        'title' => 'Build API',
        'status' => 'in_progress',
    ]);

    $user->tasks()->create([
        'title' => 'Write tests',
        'status' => 'completed',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/tasks?status=completed')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment([
            'title' => 'Write tests',
            'status' => 'completed',
        ])
        ->assertJsonMissing([
            'title' => 'Finish documentation',
        ]);
});

test('users can search their tasks by title', function () {
    $user = User::factory()->create();

    $user->tasks()->create([
        'title' => 'Prepare Laravel interview',
    ]);

    $user->tasks()->create([
        'title' => 'Buy groceries',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/tasks?search=Laravel')
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.title', 'Prepare Laravel interview');
});

test('users cannot find another users tasks through search', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    $user->tasks()->create([
        'title' => 'My Laravel project',
    ]);

    $anotherUser->tasks()->create([
        'title' => 'Laravel administrator password',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/tasks?search=Laravel')
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.title', 'My Laravel project')
        ->assertJsonMissing([
            'title' => 'Laravel administrator password',
        ]);
});

test('task search rejects search terms longer than 100 characters', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $search = str_repeat('a', 101);

    $this->getJson('/api/tasks?search=' . $search)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['search']);
});

test('users can sort their tasks oldest first', function () {
    $user = User::factory()->create();

    $user->tasks()->create([
        'title' => 'First task',
    ]);

    $user->tasks()->create([
        'title' => 'Second task',
    ]);

    $user->tasks()->create([
        'title' => 'Third task',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/tasks?sort=oldest')
        ->assertOk()
        ->assertJsonPath('data.0.title', 'First task')
        ->assertJsonPath('data.1.title', 'Second task')
        ->assertJsonPath('data.2.title', 'Third task');
});

test('task sorting rejects an invalid sort option', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/tasks?sort=random')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['sort']);
});

test('task filtering rejects an invalid status', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/tasks?status=archived')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('users can paginate their tasks across multiple pages', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    // Create 12 tasks belonging to this user.
    foreach (range(1, 12) as $number) {
        $user->tasks()->create([
            'title' => "Task {$number}",
        ]);
    }

    // Page 1 should contain 10 tasks.
    $this->getJson('/api/tasks?page=1')
        ->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonPath('current_page', 1)
        ->assertJsonPath('per_page', 10)
        ->assertJsonPath('total', 12)
        ->assertJsonPath('last_page', 2);

    // Page 2 should contain the remaining 2 tasks.
    $this->getJson('/api/tasks?page=2')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('current_page', 2)
        ->assertJsonPath('total', 12)
        ->assertJsonPath('last_page', 2);
});

test('users can combine search status sorting and pagination', function () {
    $user = User::factory()->create();

    $olderTask = $user->tasks()->create([
        'title' => 'Laravel API planning',
        'status' => 'completed',
    ]);

    $olderTask->forceFill([
        'created_at' => now()->subDays(2),
    ])->save();

    $user->tasks()->create([
        'title' => 'Laravel API implementation',
        'status' => 'completed',
    ]);

    $user->tasks()->create([
        'title' => 'Laravel API testing',
        'status' => 'pending',
    ]);

    Sanctum::actingAs($user);

    $this->getJson(
        '/api/tasks?search=Laravel&status=completed&sort=oldest&per_page=1'
    )
        ->assertOk()
        ->assertJsonPath('total', 2)
        ->assertJsonPath('current_page', 1)
        ->assertJsonPath('data.0.title', 'Laravel API planning');
});

test('guests cannot update tasks', function () {
    $user = User::factory()->create();

    $task = $user->tasks()->create([
        'title' => 'Original task',
    ]);

    $this->putJson("/api/tasks/{$task->id}", [
        'title' => 'Updated task',
    ])->assertUnauthorized();
});

test('users can choose a custom page size', function () {
    $user = User::factory()->create();

    foreach (range(1, 12) as $number) {
        $user->tasks()->create([
            'title' => "Task {$number}",
        ]);
    }

    Sanctum::actingAs($user);

    $this->getJson('/api/tasks?per_page=5')
        ->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('per_page', 5)
        ->assertJsonPath('total', 12)
        ->assertJsonPath('last_page', 3);
});

test('task pagination rejects invalid page sizes', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/tasks?per_page=100')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['per_page']);
});

test('users can update their own tasks', function () {
    $user = User::factory()->create();

    $task = $user->tasks()->create([
        'title' => 'Original task',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/tasks/{$task->id}", [
        'title' => 'Updated task',
        'status' => 'completed',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('task.title', 'Updated task')
        ->assertJsonPath('task.status', 'completed');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'user_id' => $user->id,
        'title' => 'Updated task',
        'status' => 'completed',
    ]);
});

test('users cannot update another users tasks', function () {
    $owner = User::factory()->create();
    $anotherUser = User::factory()->create();

    $task = $owner->tasks()->create([
        'title' => 'Private task',
    ]);

    Sanctum::actingAs($anotherUser);

    $this->putJson("/api/tasks/{$task->id}", [
        'title' => 'Stolen task',
    ])->assertNotFound();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'user_id' => $owner->id,
        'title' => 'Private task',
    ]);
});

test('guests cannot delete tasks', function () {
    $user = \App\Models\User::factory()->create();

    $task = $user->tasks()->create([
        'title' => 'Protected task',
    ]);

    $this->deleteJson("/api/tasks/{$task->id}")
        ->assertUnauthorized();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
    ]);
});

test('users can delete their own tasks', function () {
    $user = \App\Models\User::factory()->create();

    $task = $user->tasks()->create([
        'title' => 'Task to delete',
    ]);

    \Laravel\Sanctum\Sanctum::actingAs($user);

    $this->deleteJson("/api/tasks/{$task->id}")
        ->assertOk()
        ->assertJson([
            'message' => 'Task deleted successfully.',
        ]);

    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
    ]);
});

test('users cannot delete another users tasks', function () {
    $owner = \App\Models\User::factory()->create();
    $anotherUser = \App\Models\User::factory()->create();

    $task = $owner->tasks()->create([
        'title' => 'Another users task',
    ]);

    \Laravel\Sanctum\Sanctum::actingAs($anotherUser);

    $this->deleteJson("/api/tasks/{$task->id}")
        ->assertNotFound();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'user_id' => $owner->id,
    ]);
});

test('task search is case insensitive and supports partial matches', function () {
    $user = User::factory()->create();

    $user->tasks()->create([
        'title' => 'Prepare Laravel Documentation',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/tasks?search=laravel')
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.title', 'Prepare Laravel Documentation');

    $this->getJson('/api/tasks?search=DOCUM')
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.title', 'Prepare Laravel Documentation');
});

test('task search also matches descriptions', function () {
    $user = User::factory()->create();

    $user->tasks()->create([
        'title' => 'Prepare project',
        'description' => 'Review the AUTHENTICATION workflow',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/tasks?search=authentication')
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.title', 'Prepare project');
});
